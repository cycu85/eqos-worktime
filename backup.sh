#!/bin/bash

################################################################################
# Skrypt kopii zapasowej dla EQOS WorkTime
# Tworzy backup plików aplikacji i bazy danych MySQL
# Montuje zasób sieciowy Windows (CIFS) przed backupem i odmontowuje po zakończeniu
################################################################################

# Ścieżka do katalogu ze skryptem
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Wczytaj konfigurację z pliku .backup.env
if [ -f "$SCRIPT_DIR/.backup.env" ]; then
    source "$SCRIPT_DIR/.backup.env"
else
    echo "BŁĄD: Brak pliku konfiguracyjnego .backup.env"
    exit 1
fi

# Zmienne pomocnicze
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
DATE=$(date +"%Y-%m-%d %H:%M:%S")
LOG_FILE="$LOG_DIR/backup_$TIMESTAMP.log"
TEMP_MOUNT_DIR="/tmp/backup_mount_$$"
BACKUP_BASENAME="eqos_worktime_$TIMESTAMP"
BACKUP_FILES_ARCHIVE="$BACKUP_BASENAME.tar.gz"
BACKUP_DB_FILE="$BACKUP_BASENAME.sql.gz"

################################################################################
# Funkcje pomocnicze
################################################################################

log() {
    echo "[$DATE] $1" | tee -a "$LOG_FILE"
}

error_exit() {
    log "BŁĄD: $1"
    cleanup
    exit 1
}

cleanup() {
    log "Czyszczenie zasobów..."

    # Odmontuj zasób sieciowy jeśli jest zamontowany
    if mountpoint -q "$TEMP_MOUNT_DIR" 2>/dev/null; then
        log "Odmontowywanie zasobu sieciowego..."
        sudo umount "$TEMP_MOUNT_DIR" 2>/dev/null || log "OSTRZEŻENIE: Nie można odmontować zasobu"
    fi

    # Usuń tymczasowy katalog montowania
    if [ -d "$TEMP_MOUNT_DIR" ]; then
        rmdir "$TEMP_MOUNT_DIR" 2>/dev/null
    fi
}

check_dependencies() {
    log "Sprawdzanie wymaganych narzędzi..."

    local deps=("mysqldump" "tar" "gzip" "mount.cifs")
    for dep in "${deps[@]}"; do
        if ! command -v "$dep" &> /dev/null; then
            error_exit "Brak wymaganego narzędzia: $dep"
        fi
    done
}

create_directories() {
    log "Tworzenie katalogów..."

    # Katalog na logi
    if [ ! -d "$LOG_DIR" ]; then
        mkdir -p "$LOG_DIR" || error_exit "Nie można utworzyć katalogu logów: $LOG_DIR"
    fi

    # Tymczasowy katalog montowania
    mkdir -p "$TEMP_MOUNT_DIR" || error_exit "Nie można utworzyć katalogu montowania: $TEMP_MOUNT_DIR"
}

mount_network_share() {
    log "Montowanie zasobu sieciowego Windows: $SMB_SHARE"

    # Sprawdź czy zasób nie jest już zamontowany
    if mountpoint -q "$TEMP_MOUNT_DIR" 2>/dev/null; then
        log "OSTRZEŻENIE: Zasób już zamontowany, odmontowywanie..."
        sudo umount "$TEMP_MOUNT_DIR" || error_exit "Nie można odmontować istniejącego zasobu"
    fi

    # Pobierz UID i GID aktualnego użytkownika
    local current_uid=$(id -u)
    local current_gid=$(id -g)

    # Opcje montowania CIFS z uprawnieniami dla bieżącego użytkownika
    local mount_opts="username=$SMB_USERNAME,password=$SMB_PASSWORD,vers=3.0"
    mount_opts="$mount_opts,uid=$current_uid,gid=$current_gid"
    mount_opts="$mount_opts,file_mode=0755,dir_mode=0755"

    # Dodaj opcjonalną domenę jeśli jest ustawiona
    if [ -n "$SMB_DOMAIN" ]; then
        mount_opts="$mount_opts,domain=$SMB_DOMAIN"
    fi

    # Montuj zasób sieciowy
    log "Montowanie z opcjami: uid=$current_uid, gid=$current_gid"
    sudo mount -t cifs "$SMB_SHARE" "$TEMP_MOUNT_DIR" -o "$mount_opts" || \
        error_exit "Nie można zamontować zasobu sieciowego"

    log "Zasób sieciowy zamontowany pomyślnie"

    # Sprawdź czy można zapisywać
    if [ ! -w "$TEMP_MOUNT_DIR" ]; then
        log "OSTRZEŻENIE: Sprawdzanie uprawnień katalogu..."
        ls -la "$TEMP_MOUNT_DIR" >> "$LOG_FILE" 2>&1
        error_exit "Brak uprawnień do zapisu na zasobie sieciowym"
    fi

    log "Uprawnienia zapisu potwierdzone"
}

backup_database() {
    log "Tworzenie kopii zapasowej bazy danych: $DB_DATABASE"

    local temp_sql_file="/tmp/$BACKUP_DB_FILE"

    # Eksportuj bazę danych
    mysqldump \
        --host="$DB_HOST" \
        --port="$DB_PORT" \
        --user="$DB_USERNAME" \
        --password="$DB_PASSWORD" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        "$DB_DATABASE" | gzip > "$temp_sql_file" || \
        error_exit "Nie można utworzyć kopii bazy danych"

    # Przenieś do zasobu sieciowego
    mv "$temp_sql_file" "$TEMP_MOUNT_DIR/$BACKUP_DB_FILE" || \
        error_exit "Nie można przenieść backupu bazy danych na zasób sieciowy"

    local db_size=$(du -h "$TEMP_MOUNT_DIR/$BACKUP_DB_FILE" | cut -f1)
    log "Backup bazy danych utworzony: $BACKUP_DB_FILE ($db_size)"
}

backup_files() {
    log "Tworzenie kopii zapasowej plików aplikacji..."

    local temp_archive="/tmp/$BACKUP_FILES_ARCHIVE"

    # Lista wykluczeń
    local exclude_patterns=(
        "--exclude=node_modules"
        "--exclude=vendor"
        "--exclude=storage/logs/*"
        "--exclude=storage/framework/cache/*"
        "--exclude=storage/framework/sessions/*"
        "--exclude=storage/framework/views/*"
        "--exclude=.git"
        "--exclude=.env"
    )

    # Utwórz archiwum tar.gz
    tar -czf "$temp_archive" \
        -C "$(dirname "$APP_PATH")" \
        "${exclude_patterns[@]}" \
        "$(basename "$APP_PATH")" || \
        error_exit "Nie można utworzyć archiwum plików"

    # Przenieś do zasobu sieciowego
    mv "$temp_archive" "$TEMP_MOUNT_DIR/$BACKUP_FILES_ARCHIVE" || \
        error_exit "Nie można przenieść archiwum na zasób sieciowy"

    local files_size=$(du -h "$TEMP_MOUNT_DIR/$BACKUP_FILES_ARCHIVE" | cut -f1)
    log "Backup plików utworzony: $BACKUP_FILES_ARCHIVE ($files_size)"
}

rotate_old_backups() {
    log "Usuwanie starych kopii zapasowych (starszych niż $RETENTION_DAYS dni)..."

    # Usuń stare pliki backupu
    find "$TEMP_MOUNT_DIR" -name "eqos_worktime_*.tar.gz" -mtime +$RETENTION_DAYS -delete 2>/dev/null
    find "$TEMP_MOUNT_DIR" -name "eqos_worktime_*.sql.gz" -mtime +$RETENTION_DAYS -delete 2>/dev/null

    log "Rotacja kopii zapasowych zakończona"
}

create_backup_info() {
    log "Tworzenie pliku informacyjnego..."

    local info_file="$TEMP_MOUNT_DIR/${BACKUP_BASENAME}_info.txt"

    cat > "$info_file" << EOF
EQOS WorkTime - Informacje o kopii zapasowej
============================================

Data utworzenia: $DATE
Hostname: $(hostname)
Użytkownik: $(whoami)

Pliki backupu:
- Baza danych: $BACKUP_DB_FILE
- Pliki aplikacji: $BACKUP_FILES_ARCHIVE

Baza danych: $DB_DATABASE
Ścieżka aplikacji: $APP_PATH

Rozmiary:
- Baza danych: $(du -h "$TEMP_MOUNT_DIR/$BACKUP_DB_FILE" 2>/dev/null | cut -f1)
- Pliki: $(du -h "$TEMP_MOUNT_DIR/$BACKUP_FILES_ARCHIVE" 2>/dev/null | cut -f1)

EOF

    log "Plik informacyjny utworzony: ${BACKUP_BASENAME}_info.txt"
}

################################################################################
# Główny proces backupu
################################################################################

main() {
    log "========================================="
    log "Start procesu kopii zapasowej EQOS WorkTime"
    log "========================================="

    # Rejestracja funkcji czyszczącej przy wyjściu
    trap cleanup EXIT

    # Sprawdzenia wstępne
    check_dependencies
    create_directories

    # Montowanie zasobu sieciowego
    mount_network_share

    # Tworzenie kopii zapasowych
    backup_database
    backup_files

    # Dodatkowe operacje
    create_backup_info
    rotate_old_backups

    log "========================================="
    log "Backup zakończony pomyślnie!"
    log "Lokalizacja: $SMB_SHARE"
    log "Pliki: $BACKUP_FILES_ARCHIVE, $BACKUP_DB_FILE"
    log "========================================="
}

# Uruchom główną funkcję
main
