# EQOS WorkTime

System zarządzania czasem pracy pracowników EQOS - aplikacja Laravel do śledzenia zadań, pojazdów i czasu pracy.

## Funkcjonalności

- **Role użytkowników**: Admin, Kierownik, Lider
- **Zarządzanie zadaniami**: Tworzenie, edycja i śledzenie zadań
- **Zarządzanie pojazdami**: Rejestr pojazdów/sprzętu
- **Logowanie czasu pracy**: Automatyczne śledzenie czasu zadań
- **Raporty i eksport**: Export do Excel/PDF
- **Audyt**: Śledzenie wszystkich akcji użytkowników

## Wymagania

- PHP 8.3+
- MySQL 8.0+
- Node.js 18+
- Composer

## Instalacja

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

## Role użytkowników

- **Admin**: Pełny dostęp, zarządzanie użytkownikami i pojazdami
- **Kierownik**: Podgląd wszystkich zadań (tylko odczyt)
- **Lider**: Tworzenie i zarządzanie własnymi zadaniami

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