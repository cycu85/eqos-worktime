# EQOS WorkTime - System Zarządzania Czasem Pracy

System zarządzania czasem pracy dla zespołów, umożliwiający śledzenie zadań, pojazdów i logowanie czasu pracy z kontrolą dostępu opartą na rolach.

## Funkcjonalności

- **Zarządzanie zadaniami** - Tworzenie, edycja i śledzenie postępu zadań
- **Zarządzanie zespołami** - Tworzenie zespołów z liderami i członkami  
- **Zarządzanie pojazdami** - Rejestr pojazdów/sprzętu firmy
- **Zarządzanie użytkownikami** - System ról (Admin, Kierownik, Lider, Pracownik)
- **Responsywny design** - Zoptymalizowany pod urządzenia mobilne z motywem Metronic
- **System raportowania** - Filtrowanie i wyszukiwanie danych z eksportem do Excel
- **Export danych** - Eksport listy zadań do formatu Excel (.xlsx) z obliczaniem czasu trwania
- **Bezpieczeństwo** - Kontrola dostępu oparta na rolach
- **Interface w języku polskim** - Pełne tłumaczenie aplikacji

## Wymagania systemowe

### Wymagania sprzętowe (minimalne)
- **RAM**: 1 GB (zalecane 2 GB lub więcej)
- **Dysk**: 5 GB wolnego miejsca (zalecane 10 GB)
- **CPU**: 1 core (zalecane 2 core lub więcej)
- **Przepustowość**: Połączenie internetowe (do pobierania pakietów)

### Wymagania sprzętowe (zalecane dla produkcji)
- **RAM**: 4 GB lub więcej
- **Dysk**: 20 GB SSD
- **CPU**: 2-4 core
- **Backup**: Regularnie tworzone kopie zapasowe

### Wymagania oprogramowania
- **System operacyjny**: Ubuntu 24.04 LTS (lub nowszy)
- **PHP** 8.3 lub nowszy
- **MySQL** 8.0 lub nowszy  
- **Node.js** 18 lub nowszy
- **Composer** (menedżer pakietów PHP)
- **Web server** (Nginx zalecany, Apache obsługiwany)

## Przygotowanie serwera Ubuntu 24.04

### Konfiguracja podstawowa serwera

Przed instalacją aplikacji zaleca się wykonanie podstawowej konfiguracji serwera:

#### 1. Aktualizacja użytkownika root i tworzenie użytkownika

```bash
# Zaloguj się jako root
sudo su -

# Zaktualizuj system
apt update && apt upgrade -y

# Utwórz użytkownika dla aplikacji (opcjonalnie)
adduser eqos
usermod -aG sudo eqos

# Przełącz się na nowego użytkownika
su - eqos
```

#### 2. Konfiguracja SSH (jeśli serwer zdalny)

```bash
# Edytuj konfigurację SSH
sudo nano /etc/ssh/sshd_config

# Zalecane ustawienia bezpieczeństwa:
# Port 22 (lub zmień na inny)
# PermitRootLogin no
# PasswordAuthentication yes (lub no jeśli używasz kluczy)
# PubkeyAuthentication yes

# Zrestartuj SSH
sudo systemctl restart sshd
```

#### 3. Konfiguracja strefy czasowej

```bash
# Ustaw strefę czasową
sudo timedatectl set-timezone Europe/Warsaw

# Sprawdź ustawienia
timedatectl status
```

#### 4. Konfiguracja hostname

```bash
# Ustaw nazwę serwera
sudo hostnamectl set-hostname eqos-worktime

# Edytuj plik hosts
sudo nano /etc/hosts

# Dodaj linię:
# 127.0.0.1 eqos-worktime
```

#### 5. Podstawowa konfiguracja firewall

```bash
# Sprawdź status UFW
sudo ufw status

# Jeśli nieaktywny, skonfiguruj podstawowe reguły
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Włącz firewall (zostanie włączony później w procesie)
# sudo ufw enable
```

#### 6. Instalacja podstawowych narzędzi

```bash
# Zainstaluj przydatne narzędzia
sudo apt install -y curl wget git unzip vim htop tree
```

#### 7. Konfiguracja swapfile (dla serwerów z małą ilością RAM)

```bash
# Sprawdź obecny swap
sudo swapon --show
free -h

# Jeśli brak swap i masz <2GB RAM, utwórz swapfile 1GB
sudo fallocate -l 1G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile

# Dodaj do fstab aby swap był stały
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

#### 8. Sprawdzenie zasobów systemowych

```bash
# Sprawdź dostępne zasoby
df -h          # Miejsce na dysku
free -h        # Pamięć RAM
nproc          # Liczba procesorów
lscpu          # Informacje o CPU
```

## Instalacja na Ubuntu 24.04

### 1. Aktualizacja systemu

```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Instalacja PHP 8.3 i rozszerzeń

```bash
# Dodaj repozytorium PHP
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Zainstaluj PHP i wymagane rozszerzenia (włącznie z php8.3-zip dla Laravel Excel)
sudo apt install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-xml \
    php8.3-curl php8.3-mbstring php8.3-zip php8.3-gd php8.3-intl \
    php8.3-bcmath php8.3-soap php8.3-readline
```

### 3. Instalacja MySQL

```bash
sudo apt install -y mysql-server

# Zabezpiecz instalację MySQL
sudo mysql_secure_installation
```

### 4. Konfiguracja bazy danych

```bash
# Zaloguj się do MySQL
sudo mysql -u root -p

# Utwórz bazę danych i użytkownika
CREATE DATABASE eqos_worktime CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'eqos_user'@'localhost' IDENTIFIED BY 'twoje_bezpieczne_haslo';
GRANT ALL PRIVILEGES ON eqos_worktime.* TO 'eqos_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Instalacja Composera

```bash
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

### 6. Instalacja Node.js i npm

```bash
# Zainstaluj Node.js z NodeSource
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 7. Instalacja Nginx

```bash
sudo apt install -y nginx
```

### 8. Klonowanie i konfiguracja aplikacji

```bash
# Przejdź do katalogu web serwera
cd /var/www

# Sklonuj repozytorium (publiczne - bez autoryzacji)
sudo git clone https://github.com/cycu85/eqos-worktime.git

# ALTERNATYWNIE - jeśli masz dostęp przez SSH:
# sudo git clone git@github.com:cycu85/eqos-worktime.git

# ALTERNATYWNIE - pobierz ZIP i rozpakuj:
# wget https://github.com/cycu85/eqos-worktime/archive/refs/heads/master.zip
# unzip master.zip
# sudo mv eqos-worktime-master eqos-worktime

sudo chown -R $USER:www-data eqos-worktime
cd eqos-worktime

# Zainstaluj zależności PHP
composer install --no-dev --optimize-autoloader

# Zainstaluj zależności Node.js
npm install

# Skopiuj plik konfiguracyjny
cp .env.example .env
```

### 9. Konfiguracja pliku .env

```bash
nano .env
```

Edytuj plik .env:

```env
APP_NAME="EQOS WorkTime"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Europe/Warsaw
APP_URL=https://twoja-domena.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eqos_worktime
DB_USERNAME=eqos_user
DB_PASSWORD=twoje_bezpieczne_haslo

# Pozostałe ustawienia...
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### 10. Finalizacja instalacji

```bash
# Wygeneruj klucz aplikacji
php artisan key:generate

# Uruchom migracje bazy danych
php artisan migrate --force

# Zoptymalizuj aplikację dla produkcji
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Zbuduj assety
npm run build

# Ustaw uprawnienia
sudo chown -R www-data:www-data /var/www/eqos-worktime/storage
sudo chown -R www-data:www-data /var/www/eqos-worktime/bootstrap/cache
sudo chmod -R 775 /var/www/eqos-worktime/storage
sudo chmod -R 775 /var/www/eqos-worktime/bootstrap/cache
```

### 11. Konfiguracja Nginx

```bash
sudo nano /etc/nginx/sites-available/eqos-worktime
```

Dodaj konfigurację:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name twoja-domena.com www.twoja-domena.com;
    root /var/www/eqos-worktime/public;
    
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    
    index index.php;
    charset utf-8;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    
    error_page 404 /index.php;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Włącz stronę i restartuj Nginx
sudo ln -s /etc/nginx/sites-available/eqos-worktime /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
```

### 12. Konfiguracja SSL (opcjonalnie)

```bash
# Zainstaluj Certbot
sudo apt install -y certbot python3-certbot-nginx

# Uzyskaj certyfikat SSL
sudo certbot --nginx -d twoja-domena.com -d www.twoja-domena.com
```

### 13. Konfiguracja SSL Proxy (jeśli za proxy menedżerem)

Jeśli aplikacja działa za SSL proxy (np. Nginx Proxy Manager, Cloudflare), zaktualizuj plik `.env`:

```bash
nano .env

# Dodaj/zaktualizuj te zmienne:
APP_URL=https://twoja-domena.com
FORCE_HTTPS=true
TRUSTED_PROXIES=*

# Wyczyść cache po zmianach
php artisan config:clear
php artisan config:cache
```

### 14. Konfiguracja Firewall

```bash
sudo ufw enable
sudo ufw allow 'Nginx Full'
sudo ufw allow OpenSSH
```

## Pierwsze uruchomienie

### Tworzenie pierwszego użytkownika Admin

```bash
cd /var/www/eqos-worktime
php artisan tinker
```

W konsoli Tinker:

```php
$admin = new App\Models\User();
$admin->name = 'Administrator';
$admin->email = 'admin@twojafirma.pl';
$admin->password = Hash::make('bezpieczne_haslo_123');
$admin->role = 'admin';
$admin->email_verified_at = now();
$admin->save();
exit
```

## Instalacja deweloperska

### 1. Sklonuj repozytorium
```bash
git clone https://github.com/cycu85/eqos-worktime.git
cd eqos-worktime
```

### 2. Zainstaluj zależności
```bash
composer install
npm install
```

### 3. Konfiguracja środowiska
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfiguracja bazy danych
Edytuj plik `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eqos_worktime
DB_USERNAME=root
DB_PASSWORD=twoje_haslo
```

### 5. Utwórz bazę danych
```bash
mysql -u root -p -e "CREATE DATABASE eqos_worktime CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 6. Uruchom migracje
```bash
php artisan migrate --seed
```

### 7. Kompiluj zasoby frontend
```bash
npm run build
```

## Uruchomienie

### Środowisko deweloperskie (wszystkie usługi naraz)
```bash
composer dev
```
Aplikacja będzie dostępna pod adresem: http://localhost:8000

### Uruchomienie oddzielne
```bash
# Serwer Laravel
php artisan serve

# Vite (development)
npm run dev

# Kolejki (w osobnym terminalu)
php artisan queue:listen
```

## Testowanie

```bash
# Wszystkie testy
composer test
# lub
php artisan test

# Konkretny test
php artisan test --filter NazwaTestu
```

## Struktura projektu

```
app/
├── Http/Controllers/     # Kontrolery aplikacji
├── Models/              # Modele Eloquent
└── ...

database/
├── migrations/          # Migracje bazy danych
└── seeders/            # Dane testowe

resources/
├── views/              # Szablony Blade
├── js/                 # JavaScript
└── css/               # Style CSS

routes/
└── web.php            # Definicje tras
```

## Struktura ról

- **Admin** - Pełny dostęp do wszystkich funkcji systemu
- **Kierownik** - Dostęp do wszystkich zadań i raportów (odczyt)
- **Lider** - Zarządzanie własnymi zespołami i zadaniami
- **Pracownik** - Dostęp do przypisanych zadań

## Rozwiązywanie problemów

### Problem z uprawnieniami plików
```bash
sudo chown -R www-data:www-data /var/www/eqos-worktime/storage
sudo chown -R www-data:www-data /var/www/eqos-worktime/bootstrap/cache
sudo chmod -R 775 /var/www/eqos-worktime/storage
sudo chmod -R 775 /var/www/eqos-worktime/bootstrap/cache
```

### Problem z bazą danych
```bash
# Sprawdź status migracji
php artisan migrate:status

# Reset migracji (UWAGA: usuwa wszystkie dane!)
php artisan migrate:fresh
```

### Problem z cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear  
php artisan route:clear
```

### Sprawdzenie logów błędów
```bash
# Laravel logi
tail -f /var/www/eqos-worktime/storage/logs/laravel.log

# Nginx logi
sudo tail -f /var/log/nginx/error.log

# PHP-FPM logi
sudo tail -f /var/log/php8.3-fpm.log
```

### Problem z Composer
```bash
# Zainstaluj ponownie bez dev zależności
composer install --no-dev --no-scripts --optimize-autoloader

# Jeśli problem z pamięcią
php -d memory_limit=-1 /usr/local/bin/composer install --no-dev
```

### Problem z autoryzacją Git
```bash
# Jeśli repozytorium wymaga autoryzacji, użyj jednej z metod:

# Metoda 1: Pobierz ZIP (bez git)
cd /var/www
sudo wget https://github.com/cycu85/eqos-worktime/archive/refs/heads/master.zip
sudo unzip master.zip
sudo mv eqos-worktime-master eqos-worktime
sudo rm master.zip

# Metoda 2: Konfiguruj SSH key (zalecane)
ssh-keygen -t ed25519 -C "your_email@example.com"
cat ~/.ssh/id_ed25519.pub
# Dodaj klucz do GitHub Settings > SSH Keys
git clone git@github.com:cycu85/eqos-worktime.git

# Metoda 3: Personal Access Token
# Wygeneruj token w GitHub Settings > Developer settings > Personal access tokens
git clone https://username:TOKEN@github.com/cycu85/eqos-worktime.git
```

### Problem z mieszanymi treściami (Mixed Content) za SSL proxy
```bash
# Jeśli aplikacja działa za SSL proxy i masz błędy "Mixed Content":

# 1. Zaktualizuj plik .env
nano /var/www/eqos-worktime/.env

# Dodaj/zmień te wartości:
APP_URL=https://twoja-domena.com
FORCE_HTTPS=true
TRUSTED_PROXIES=*

# 2. Wyczyść cache Laravel
cd /var/www/eqos-worktime
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Zrestartuj usługi
sudo systemctl restart php8.3-fpm nginx

# 4. Sprawdź czy proxy przekazuje nagłówki SSL
# W konfiguracji proxy menedżera sprawdź:
# - X-Forwarded-Proto: https
# - X-Forwarded-For: $remote_addr
# - X-Forwarded-Host: $host
```

## Aktualizacja aplikacji

```bash
cd /var/www/eqos-worktime

# Pobierz najnowsze zmiany
git pull origin main

# Zaktualizuj zależności
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Uruchom migracje
php artisan migrate --force

# Wyczyść i przebuduj cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Zrestartuj usługi
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
```

## Monitorowanie i konserwacja serwera

### Sprawdzenie statusu usług

```bash
# Status wszystkich kluczowych usług
sudo systemctl status nginx php8.3-fpm mysql

# Sprawdzenie logów w czasie rzeczywistym
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/mysql/error.log
sudo tail -f /var/www/eqos-worktime/storage/logs/laravel.log
```

### Monitorowanie zasobów

```bash
# Sprawdzenie obciążenia serwera
htop
# lub
top

# Sprawdzenie miejsca na dysku
df -h

# Sprawdzenie pamięci
free -h

# Sprawdzenie aktywnych połączeń
ss -tulpn
```

### Automatyzacja kopii zapasowych

```bash
# Utwórz skrypt backupu
sudo nano /usr/local/bin/eqos-backup.sh
```

Zawartość skryptu backup:
```bash
#!/bin/bash
BACKUP_DIR="/home/backups/eqos-worktime"
DATE=$(date +%Y%m%d_%H%M%S)
APP_DIR="/var/www/eqos-worktime"

# Utwórz katalog backupu
mkdir -p $BACKUP_DIR

# Backup bazy danych
mysqldump -u eqos_user -p'twoje_bezpieczne_haslo' eqos_worktime > $BACKUP_DIR/db_backup_$DATE.sql

# Backup plików aplikacji (bez node_modules i vendor)
tar -czf $BACKUP_DIR/app_backup_$DATE.tar.gz -C $APP_DIR \
    --exclude=node_modules \
    --exclude=vendor \
    --exclude=storage/logs \
    .

# Usuń stare backupy (starsze niż 7 dni)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
# Ustaw uprawnienia
sudo chmod +x /usr/local/bin/eqos-backup.sh

# Dodaj do crontab (codziennie o 2:00)
sudo crontab -e
# Dodaj linię:
# 0 2 * * * /usr/local/bin/eqos-backup.sh >> /var/log/eqos-backup.log 2>&1
```

### Optymalizacja wydajności

```bash
# Optymalizacja MySQL
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Dodaj/modyfikuj sekcje:
# [mysqld]
# innodb_buffer_pool_size = 128M  # 70-80% dostępnej RAM dla MySQL
# query_cache_size = 16M
# query_cache_limit = 1M

# Optymalizacja PHP-FPM
sudo nano /etc/php/8.3/fpm/pool.d/www.conf

# Modyfikuj:
# pm.max_children = 10
# pm.start_servers = 3
# pm.min_spare_servers = 2
# pm.max_spare_servers = 4

# Zrestartuj usługi po zmianach
sudo systemctl restart mysql php8.3-fpm nginx
```

## Deployment

### Shared hosting
1. Upload plików na serwer
2. Ustaw `public/` jako root directory
3. Zaimportuj bazę danych
4. Skonfiguruj `.env`
5. Uruchom `composer install --no-dev`
6. Uruchom `npm run build`

### VPS/Dedicated
Użyj skryptu deployment:
```bash
./scripts/deploy.sh
```

## Licencja

MIT License - see [LICENSE](LICENSE) file for details.

## Support

W przypadku problemów, utwórz issue w repozytorium GitHub.