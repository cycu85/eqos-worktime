#!/bin/bash

################################################################################
# Skrypt testowy do diagnozowania problemów z montowaniem zasobu Windows
################################################################################

# Kolory dla czytelności
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "=========================================="
echo "Test montowania zasobu sieciowego Windows"
echo "=========================================="
echo ""

# Wczytaj konfigurację
if [ ! -f ".backup.env" ]; then
    echo -e "${RED}BŁĄD: Brak pliku .backup.env${NC}"
    echo "Skopiuj .backup.env.example do .backup.env i uzupełnij danymi"
    exit 1
fi

source .backup.env

# Wyświetl konfigurację (bez hasła)
echo -e "${YELLOW}Konfiguracja:${NC}"
echo "  Zasób SMB: $SMB_SHARE"
echo "  Użytkownik: $SMB_USERNAME"
echo "  Domena: ${SMB_DOMAIN:-[brak]}"
echo ""

# Informacje o użytkowniku
echo -e "${YELLOW}Użytkownik lokalny:${NC}"
echo "  UID: $(id -u)"
echo "  GID: $(id -g)"
echo "  Nazwa: $(whoami)"
echo ""

# Katalog testowy
TEST_MOUNT_DIR="/tmp/backup_test_$$"
mkdir -p "$TEST_MOUNT_DIR"

echo -e "${YELLOW}Katalog montowania:${NC} $TEST_MOUNT_DIR"
echo ""

# Funkcja czyszcząca
cleanup() {
    if mountpoint -q "$TEST_MOUNT_DIR" 2>/dev/null; then
        echo "Odmontowywanie..."
        sudo umount "$TEST_MOUNT_DIR" 2>/dev/null
    fi
    rmdir "$TEST_MOUNT_DIR" 2>/dev/null
}

trap cleanup EXIT

# Test 1: Podstawowe montowanie z uid/gid
echo "=========================================="
echo -e "${YELLOW}TEST 1: Montowanie z uid/gid${NC}"
echo "=========================================="

MOUNT_OPTS="username=$SMB_USERNAME,password=$SMB_PASSWORD,vers=3.0"
MOUNT_OPTS="$MOUNT_OPTS,uid=$(id -u),gid=$(id -g)"
MOUNT_OPTS="$MOUNT_OPTS,file_mode=0755,dir_mode=0755"

if [ -n "$SMB_DOMAIN" ]; then
    MOUNT_OPTS="$MOUNT_OPTS,domain=$SMB_DOMAIN"
fi

echo "Opcje montowania: username=***, uid=$(id -u), gid=$(id -g), vers=3.0"
echo ""

if sudo mount -t cifs "$SMB_SHARE" "$TEST_MOUNT_DIR" -o "$MOUNT_OPTS" 2>/dev/null; then
    echo -e "${GREEN}✓ Montowanie zakończone sukcesem${NC}"
    echo ""

    # Sprawdź uprawnienia
    echo "Uprawnienia katalogu:"
    ls -la "$TEST_MOUNT_DIR"
    echo ""

    # Test zapisu
    echo -e "${YELLOW}Test zapisu pliku...${NC}"
    TEST_FILE="$TEST_MOUNT_DIR/test_backup_$$_$(date +%s).txt"

    if echo "Test backup $(date)" > "$TEST_FILE" 2>/dev/null; then
        echo -e "${GREEN}✓ Zapis pliku udany${NC}"
        echo "  Plik: $(basename $TEST_FILE)"

        # Sprawdź czy plik istnieje
        if [ -f "$TEST_FILE" ]; then
            echo -e "${GREEN}✓ Plik istnieje i jest czytelny${NC}"
            cat "$TEST_FILE"

            # Usuń testowy plik
            rm "$TEST_FILE" 2>/dev/null
            echo -e "${GREEN}✓ Plik testowy usunięty${NC}"
        fi
    else
        echo -e "${RED}✗ Błąd zapisu pliku${NC}"
        echo "  Kod błędu: $?"
    fi

    echo ""
    sudo umount "$TEST_MOUNT_DIR"
    echo -e "${GREEN}✓ Zasób odmontowany${NC}"
else
    echo -e "${RED}✗ Nie można zamontować zasobu${NC}"
    echo ""
    echo "Możliwe przyczyny:"
    echo "  1. Nieprawidłowe dane uwierzytelniające"
    echo "  2. Zasób nie istnieje lub jest niedostępny"
    echo "  3. Firewall blokuje port 445"
    echo "  4. Brak pakietu cifs-utils"
    echo ""
    echo "Sprawdź dostępność serwera:"
    echo "  ping ${SMB_SHARE//\/\//} | head -5"
fi

echo ""
echo "=========================================="
echo -e "${YELLOW}TEST 2: Próba alternatywnych wersji SMB${NC}"
echo "=========================================="

for VERSION in "3.1.1" "3.0" "2.1" "2.0"; do
    echo -n "Testowanie SMB $VERSION... "

    MOUNT_OPTS="username=$SMB_USERNAME,password=$SMB_PASSWORD,vers=$VERSION"
    MOUNT_OPTS="$MOUNT_OPTS,uid=$(id -u),gid=$(id -g)"

    if [ -n "$SMB_DOMAIN" ]; then
        MOUNT_OPTS="$MOUNT_OPTS,domain=$SMB_DOMAIN"
    fi

    if sudo mount -t cifs "$SMB_SHARE" "$TEST_MOUNT_DIR" -o "$MOUNT_OPTS" 2>/dev/null; then
        echo -e "${GREEN}✓ Działa${NC}"
        sudo umount "$TEST_MOUNT_DIR" 2>/dev/null
        break
    else
        echo -e "${RED}✗ Nie działa${NC}"
    fi
done

echo ""
echo "=========================================="
echo -e "${YELLOW}Zalecenia:${NC}"
echo "=========================================="
echo ""
echo "1. Sprawdź uprawnienia folderu na serwerze Windows:"
echo "   - Folder musi mieć uprawnienia zapisu dla użytkownika $SMB_USERNAME"
echo "   - Sprawdź właściwości folderu → Udostępnianie → Zaawansowane udostępnianie"
echo ""
echo "2. Sprawdź czy użytkownik ma uprawnienia:"
echo "   - Administrator powinien mieć pełny dostęp"
echo "   - Sprawdź czy konto nie jest zablokowane"
echo ""
echo "3. Sprawdź firewall na Windows:"
echo "   - Port 445 (SMB) musi być otwarty"
echo "   - Udostępnianie plików i drukarek musi być włączone"
echo ""
echo "4. Na serwerze Windows uruchom jako Administrator:"
echo "   net share"
echo "   - Sprawdź czy folder jest prawidłowo udostępniony"
echo ""
