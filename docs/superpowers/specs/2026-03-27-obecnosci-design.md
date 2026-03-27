# Specyfikacja: Moduł Obecności

**Data:** 2026-03-27
**Status:** Zatwierdzony

---

## Cel

Dodanie zakładki „Obecności" w menu „Zasoby" pokazującej listę obecności pracowników na podstawie zadań. Pracownik jest uznany za obecnego danego dnia, jeśli jest przypisany do zadania (jako lider lub członek zespołu), które posiada work_log z tym dniem o statusie `in_progress` lub `completed`.

---

## Dostęp

Tylko użytkownicy z rolą **admin** lub **kierownik** — zgodnie z istniejącymi regułami menu „Zasoby".

---

## Architektura

### Nowe pliki

| Plik | Opis |
|---|---|
| `app/Http/Controllers/AttendanceController.php` | Kontroler listy obecności |
| `resources/views/attendance/index.blade.php` | Widok listy obecności |

### Modyfikowane pliki

| Plik | Zmiana |
|---|---|
| `routes/web.php` | Nowa trasa `GET /zasoby/obecnosci` |
| `resources/views/layouts/navigation.blade.php` | Dodanie „Obecności" do dropdownu „Zasoby" (desktop + mobile) |

---

## Logika danych (AttendanceController)

### Źródło danych

Tabela `task_work_logs` z warunkiem `status IN ('in_progress', 'completed')`, joinowana z `tasks`.

### Dwa źródła obecności

1. **Lider zadania** — `tasks.leader_id` → bezpośredni JOIN z `users`
2. **Członkowie zespołu** — pole `tasks.team` (string `"Jan Kowalski, Anna Nowak"`) → split po `,` → trim → dopasowanie do `users.name`

### Kroki algorytmu

1. Pobierz wszystkie pasujące work_logi (z filtrem dat) z joinami do `tasks` i `users` (liderzy)
2. Załaduj wszystkich aktywnych użytkowników (mapa `name → id`)
3. Dla każdego work_loga zbierz do kolekcji: `[user_id, user_name, work_date]`
   - Dodaj lidera (z JOIN)
   - Parsuj `tasks.team`, dodaj każdego dopasowanego użytkownika
4. Zdeduplikuj po `(user_id, work_date)` — jedna obecność na dzień na osobę
5. Zastosuj filtr pracownika (jeśli wybrany)
6. Zastosuj sortowanie (domyślnie: `work_date DESC`)
7. Paginacja: 20 rekordów na stronę

### Filtry

| Parametr | Typ | Domyślna wartość |
|---|---|---|
| `date_from` | date | Pierwszy dzień bieżącego miesiąca |
| `date_to` | date | Dzisiaj |
| `user_id` | int\|null | null (wszyscy) |
| `sort` | string | `date` |
| `direction` | `asc`\|`desc` | `desc` |

---

## Widok (attendance/index.blade.php)

### Karta filtrów

- Input `Od daty` / `Do daty` (type=date)
- Select `Pracownik` (opcja „Wszyscy" + lista użytkowników)
- Przycisk „Filtruj" + link „Wyczyść"

### Tabela

| Kolumna | Sortowalna | Uwagi |
|---|---|---|
| Imię i Nazwisko | Tak | sort=name |
| Data | Tak | sort=date (domyślna) |

- Nagłówki klikalne ze strzałkami kierunku (↑/↓)
- Podsumowanie: „Znaleziono X rekordów obecności"
- Paginacja z zachowaniem wszystkich parametrów GET

### Klasy CSS

Zgodne z istniejącym stylem projektu: `kt-card`, `kt-card-body`, `table-kt`, `form-kt-control`, `form-kt-select`, `btn-kt-primary`, `btn-kt-secondary`.

---

## Nawigacja

Zakładka „Obecności" dodana do dropdownu „Zasoby" w `navigation.blade.php`:
- Desktop: nowy `<a>` w `<div class="py-1">` obok Pojazdy, Użytkownicy, Zespoły
- Mobile: nowy `<x-responsive-nav-link>` w sekcji Zasoby
- Aktywny stan: `request()->routeIs('attendance.*')`
- Warunek widoczności: `isAdmin() || isKierownik()`
- Zaktualizować warunek aktywnego stanu dropdownu Zasoby aby obejmował `attendance.*`

---

## Trasa

```php
Route::get('/zasoby/obecnosci', [AttendanceController::class, 'index'])
    ->name('attendance.index')
    ->middleware(['auth', 'role:admin,kierownik']);
```

---

## Ograniczenia i decyzje

- **Brak nowej tabeli:** Obecności są wyliczane dynamicznie z `task_work_logs` — nie ma persystencji.
- **Dopasowanie po nazwisku:** Pole `tasks.team` zawiera imiona/nazwiska jako string. Dopasowanie do `users.name` może nie trafić w przypadku niespójnych danych (literówki, różne formaty). Jest to świadome ograniczenie istniejącej struktury danych.
- **Deduplication w PHP:** Ze względu na niemożność wykonania UNION z parsowaniem stringa w SQL, deduplication odbywa się po stronie PHP.
