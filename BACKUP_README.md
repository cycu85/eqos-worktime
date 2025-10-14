# System Kopii Zapasowych EQOS WorkTime

Automatyczny system tworzenia kopii zapasowych aplikacji i bazy danych z montowaniem zasobu sieciowego Windows.

## Funkcje

- ✅ Backup bazy danych MySQL (z kompresją gzip)
- ✅ Backup plików aplikacji (archiwum tar.gz)
- ✅ Automatyczne montowanie/odmontowanie zasobu sieciowego Windows (CIFS/SMB)
- ✅ Logowanie operacji
- ✅ Automatyczna rotacja starych kopii zapasowych
- ✅ Obsługa błędów i czyszczenie zasobów
- ✅ Plik informacyjny z metadanymi backupu

## Wymagania

### System

- Linux (Ubuntu/Debian lub podobny)
- Bash 4.0+
- sudo (dla montowania zasobów sieciowych)

### Pakiety

```bash
# Ubuntu/Debian
sudo apt-get install cifs-utils mysql-client

# CentOS/RHEL
sudo yum install cifs-utils mysql
```

## Instalacja i Konfiguracja

### 1. Uprawnienia

Nadaj uprawnienia wykonywania skryptowi:

```bash
chmod +x backup.sh
```

### 2. Konfiguracja

Skopiuj przykładowy plik konfiguracyjny i dostosuj do swoich potrzeb:

```bash
cp .backup.env.example .backup.env
nano .backup.env
```

**WAŻNE**: Plik `.backup.env` zawiera hasła i jest automatycznie ignorowany przez git.

### Parametry konfiguracyjne:

#### Aplikacja
- `APP_PATH` - Ścieżka do katalogu aplikacji
- `LOG_DIR` - Katalog na logi backupu

#### Baza danych
- `DB_HOST` - Host bazy danych (domyślnie: localhost)
- `DB_PORT` - Port bazy danych (domyślnie: 3306)
- `DB_DATABASE` - Nazwa bazy danych
- `DB_USERNAME` - Użytkownik MySQL
- `DB_PASSWORD` - Hasło do MySQL

#### Zasób sieciowy Windows
- `SMB_SHARE` - Ścieżka do zasobu (format: //serwer/udział)
- `SMB_USERNAME` - Nazwa użytkownika Windows
- `SMB_PASSWORD` - Hasło użytkownika Windows
- `SMB_DOMAIN` - Domena Windows (opcjonalne)

#### Retencja
- `RETENTION_DAYS` - Liczba dni przechowywania kopii (domyślnie: 30)

### 3. Konfiguracja sudo (WAŻNE!)

Skrypt wymaga uprawnień sudo do montowania zasobów sieciowych. Dodaj wpis do sudoers:

```bash
sudo visudo
```

Dodaj na końcu pliku (zastąp `your_username` swoją nazwą użytkownika):

```
your_username ALL=(ALL) NOPASSWD: /bin/mount, /bin/umount
```

Lub bardziej restrykcyjnie:

```
your_username ALL=(ALL) NOPASSWD: /bin/mount -t cifs *, /bin/umount /tmp/backup_mount_*
```

### 4. Test montowania (WAŻNE!)

Przed uruchomieniem backupu, przetestuj montowanie zasobu sieciowego:

```bash
./test_mount.sh
```

Ten skrypt zdiagnozuje problemy z:
- Montowaniem zasobu Windows
- Uprawnieniami zapisu
- Dostępnością różnych wersji SMB
- Konfiguracją sieci

### 5. Test skryptu backupu

Po pomyślnym teście montowania, przetestuj pełny backup:

```bash
./backup.sh
```

Sprawdź logi w katalogu `storage/logs/backup/`.

## Automatyzacja (Cron)

### Przykładowe konfiguracje cron

#### Backup codziennie o 2:00 w nocy

```bash
crontab -e
```

Dodaj:

```cron
0 2 * * * /home/waldi/Projekty/PM_EQOS/eqos-worktime/backup.sh
```

#### Backup co 6 godzin

```cron
0 */6 * * * /home/waldi/Projekty/PM_EQOS/eqos-worktime/backup.sh
```

#### Backup w dni robocze o 23:00

```cron
0 23 * * 1-5 /home/waldi/Projekty/PM_EQOS/eqos-worktime/backup.sh
```

#### Backup co tydzień w niedzielę o 3:00

```cron
0 3 * * 0 /home/waldi/Projekty/PM_EQOS/eqos-worktime/backup.sh
```

### Sprawdzenie działania crona

Sprawdź logi systemowe:

```bash
grep CRON /var/log/syslog
```

Lub sprawdź logi backupu:

```bash
ls -lah storage/logs/backup/
tail -f storage/logs/backup/backup_*.log
```

## Struktura Backupu

Każdy backup tworzy następujące pliki na zasobie sieciowym:

```
//serwer/udział/
├── eqos_worktime_20250114_020000.tar.gz        # Pliki aplikacji
├── eqos_worktime_20250114_020000.sql.gz        # Baza danych
└── eqos_worktime_20250114_020000_info.txt      # Informacje o backupie
```

### Wykluczenia z backupu plików

Następujące katalogi są automatycznie wykluczane z backupu:

- `node_modules/` - Zależności Node.js
- `vendor/` - Zależności Composer
- `storage/logs/*` - Logi aplikacji
- `storage/framework/cache/*` - Cache
- `storage/framework/sessions/*` - Sesje
- `storage/framework/views/*` - Skompilowane widoki
- `.git/` - Repozytorium Git
- `.env` - Plik środowiskowy (zawiera hasła)

## Przywracanie z Backupu

### 1. Przywracanie plików

```bash
# Pobierz archiwum z zasobu sieciowego
# Rozpakuj do tymczasowego katalogu
tar -xzf eqos_worktime_20250114_020000.tar.gz -C /tmp/

# Skopiuj pliki do lokalizacji aplikacji
cp -r /tmp/eqos-worktime/* /home/waldi/Projekty/PM_EQOS/eqos-worktime/

# Zainstaluj zależności
cd /home/waldi/Projekty/PM_EQOS/eqos-worktime
composer install
npm install

# Skopiuj i skonfiguruj .env
cp .env.example .env
nano .env

# Wygeneruj klucz aplikacji
php artisan key:generate
```

### 2. Przywracanie bazy danych

```bash
# Rozpakuj i zaimportuj bazę danych
gunzip -c eqos_worktime_20250114_020000.sql.gz | mysql -u root -p eqos_worktime
```

Lub krok po kroku:

```bash
# Rozpakuj plik SQL
gunzip eqos_worktime_20250114_020000.sql.gz

# Utwórz bazę danych jeśli nie istnieje
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS eqos_worktime;"

# Zaimportuj dane
mysql -u root -p eqos_worktime < eqos_worktime_20250114_020000.sql
```

### 3. Finalizacja po przywróceniu

```bash
# Uprawnienia
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Czyszczenie cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Migracje (jeśli potrzebne)
php artisan migrate

# Build assetów
npm run build
```

## Rozwiązywanie Problemów

### Błąd: "Brak uprawnień do zapisu na zasobie sieciowym"

**Najczęstszy problem!** Mimo prawidłowego montowania, lokalny użytkownik nie ma uprawnień zapisu.

**Rozwiązanie (już zaimplementowane w skrypcie):**
Skrypt automatycznie dodaje opcje `uid`, `gid` i `file_mode` podczas montowania, aby lokalny użytkownik miał uprawnienia zapisu:

```bash
uid=$(id -u),gid=$(id -g),file_mode=0755,dir_mode=0755
```

**Sprawdź na serwerze Windows:**

1. **Uprawnienia folderu udziału:**
   - Kliknij prawym na folder → Właściwości → Udostępnianie → Zaawansowane udostępnianie
   - Upewnij się, że użytkownik Administrator (lub twoje konto) ma uprawnienia "Pełna kontrola"

2. **Uprawnienia NTFS:**
   - Właściwości → Zabezpieczenia → Edytuj
   - Użytkownik Administrator powinien mieć "Pełna kontrola"

3. **Sprawdź udostępnienie (jako Administrator na Windows):**
   ```cmd
   net share
   ```
   Upewnij się, że folder jest widoczny na liście

4. **Uprawnienia w SMB (PowerShell jako Administrator):**
   ```powershell
   Get-SmbShare
   Get-SmbShareAccess -Name "NazwaUdzialu"
   ```

**Test ręczny:**
```bash
# Użyj skryptu testowego
./test_mount.sh
```

### Błąd: "Nie można zamontować zasobu sieciowego"

**Przyczyny:**
- Nieprawidłowe dane uwierzytelniające (username/password)
- Błędna ścieżka do zasobu SMB
- Firewall blokuje port 445
- Zasób sieciowy niedostępny
- Nieprawidłowa wersja protokołu SMB

**Rozwiązanie:**
```bash
# Test ręcznego montowania
sudo mount -t cifs //serwer/udział /mnt/test \
  -o username=user,password=pass,vers=3.0,uid=$(id -u),gid=$(id -g)

# Sprawdź dostępność serwera
ping serwer

# Sprawdź dostępność portu SMB
nc -zv serwer 445

# Użyj skryptu testowego (testuje różne wersje SMB)
./test_mount.sh
```

### Błąd: "mysqldump: command not found"

**Rozwiązanie:**
```bash
sudo apt-get install mysql-client
```

### Błąd: "Permission denied" podczas montowania

**Rozwiązanie:**
Sprawdź konfigurację sudoers (patrz punkt 3 w Instalacji)

### Brak miejsca na dysku

**Rozwiązanie:**
- Zmniejsz `RETENTION_DAYS` w `.backup.env`
- Ręcznie usuń stare backupy
- Zwiększ przestrzeń na zasobie sieciowym

### Backup trwa bardzo długo

**Optymalizacja:**
- Wykluczenie większej liczby katalogów w `backup_files()`
- Kompresja z niższym poziomem: `gzip -1` zamiast domyślnego
- Sprawdź prędkość połączenia sieciowego

## Monitoring

### Sprawdzanie logów

```bash
# Ostatni log
tail -f storage/logs/backup/backup_*.log | tail -n 1

# Wszystkie logi
ls -lah storage/logs/backup/

# Błędy w logach
grep "BŁĄD" storage/logs/backup/backup_*.log
```

### Rozmiar backupów

```bash
# Lista backupów na zasobie sieciowym (po zamontowaniu)
sudo mount -t cifs //serwer/udział /mnt/backup -o username=user,password=pass
ls -lah /mnt/backup/eqos_worktime_*
sudo umount /mnt/backup
```

## Bezpieczeństwo

### Rekomendacje

1. **Plik .backup.env**
   - NIGDY nie commituj do git
   - Ustaw uprawnienia: `chmod 600 .backup.env`
   - Przechowuj kopię w bezpiecznym miejscu

2. **Hasła**
   - Używaj silnych haseł dla SMB i MySQL
   - Regularnie zmieniaj hasła
   - Rozważ użycie vault/secret manager

3. **Zasób sieciowy**
   - Ogranicz dostęp tylko dla konta backupowego
   - Używaj dedykowanego konta z minimalnymi uprawnieniami
   - Regularnie sprawdzaj logi dostępu

4. **Szyfrowanie**
   - Rozważ szyfrowanie archiwów przed przesłaniem
   - Użyj VPN dla przesyłu danych
   - Włącz szyfrowanie SMB 3.0+

## Kontakt i Wsparcie

W przypadku problemów:
1. Sprawdź logi w `storage/logs/backup/`
2. Przetestuj skrypt ręcznie: `./backup.sh`
3. Sprawdź konfigurację w `.backup.env`
4. Zweryfikuj dostęp do zasobu sieciowego

## Licencja

Ten skrypt jest częścią projektu EQOS WorkTime.
