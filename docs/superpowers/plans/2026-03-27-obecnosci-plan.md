# Obecności Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Dodanie zakładki „Obecności" w menu „Zasoby" pokazującej listę obecności pracowników wyprowadzoną z `task_work_logs`.

**Architecture:** `AttendanceController` pobiera `task_work_logs` ze statusem `in_progress`/`completed`, joinuje z `tasks`, następnie w PHP zbiera liderów (przez `leader_id`) i członków zespołu (przez parsowanie stringa `tasks.team`), deduplikuje po `(user_id, work_date)` i zwraca posortowaną, paginowaną kolekcję.

**Tech Stack:** Laravel 11, PHP 8.3, Blade, TailwindCSS v4 / Metronic CSS, Alpine.js (nawigacja)

---

## Mapa plików

| Akcja | Plik | Odpowiedzialność |
|---|---|---|
| Utwórz | `app/Http/Controllers/AttendanceController.php` | Logika pobierania i filtrowania obecności |
| Utwórz | `resources/views/attendance/index.blade.php` | Widok listy z filtrami, tabelą, paginacją |
| Utwórz | `tests/Feature/AttendanceControllerTest.php` | Testy feature kontrolera |
| Modyfikuj | `routes/web.php` | Nowa trasa GET /zasoby/obecnosci |
| Modyfikuj | `resources/views/layouts/navigation.blade.php` | Pozycja „Obecności" w dropdownie Zasoby |

---

## Task 1: Trasa i szkielet kontrolera

**Files:**
- Create: `app/Http/Controllers/AttendanceController.php`
- Modify: `routes/web.php`

- [ ] **Krok 1: Dodaj trasę do routes/web.php**

Znajdź blok `Route::middleware('role:admin,kierownik')->group(function ()` (ok. linia 118) i dodaj trasę wewnątrz grupy, po istniejących trasach:

```php
// Attendance
Route::get('zasoby/obecnosci', [AttendanceController::class, 'index'])->name('attendance.index');
```

Oraz dodaj import na górze pliku (po innych `use` deklaracjach):

```php
use App\Http\Controllers\AttendanceController;
```

- [ ] **Krok 2: Utwórz kontroler ze szkieletem**

Utwórz plik `app/Http/Controllers/AttendanceController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $userId   = $request->get('user_id');
        $sort      = $request->get('sort', 'date');
        $direction = in_array($request->get('direction'), ['asc', 'desc'])
            ? $request->get('direction')
            : 'desc';

        // Pobierz work_logi z joinami
        $workLogs = DB::table('task_work_logs')
            ->select(
                'task_work_logs.work_date',
                'tasks.leader_id',
                'tasks.team as team_string',
                'users.name as leader_name'
            )
            ->join('tasks', 'task_work_logs.task_id', '=', 'tasks.id')
            ->join('users', 'tasks.leader_id', '=', 'users.id')
            ->whereIn('task_work_logs.status', ['in_progress', 'completed'])
            ->where('task_work_logs.work_date', '>=', $dateFrom)
            ->where('task_work_logs.work_date', '<=', $dateTo)
            ->get();

        // Mapa name => id dla wszystkich aktywnych użytkowników
        $usersByName = User::where('is_active', true)
            ->pluck('id', 'name');

        // Zbierz unikalne pary (user_id, work_date)
        $attendanceSet = collect();

        foreach ($workLogs as $log) {
            // Dodaj lidera
            $attendanceSet->push([
                'user_id'   => $log->leader_id,
                'user_name' => $log->leader_name,
                'work_date' => $log->work_date,
            ]);

            // Dodaj członków zespołu z pola team (string)
            if (!empty($log->team_string)) {
                $members = array_map('trim', explode(',', $log->team_string));
                foreach ($members as $memberName) {
                    if ($memberName === '') {
                        continue;
                    }
                    $memberId = $usersByName->get($memberName);
                    if ($memberId) {
                        $attendanceSet->push([
                            'user_id'   => $memberId,
                            'user_name' => $memberName,
                            'work_date' => $log->work_date,
                        ]);
                    }
                }
            }
        }

        // Deduplikacja po (user_id, work_date)
        $attendance = $attendanceSet
            ->unique(fn($row) => $row['user_id'] . '_' . $row['work_date']);

        // Filtr pracownika
        if ($userId) {
            $attendance = $attendance->filter(fn($row) => $row['user_id'] == $userId);
        }

        // Sortowanie
        if ($sort === 'name') {
            $attendance = $direction === 'asc'
                ? $attendance->sortBy('user_name')
                : $attendance->sortByDesc('user_name');
        } else {
            $attendance = $direction === 'asc'
                ? $attendance->sortBy('work_date')
                : $attendance->sortByDesc('work_date');
        }

        // Paginacja ręczna (kolekcja PHP, nie Eloquent)
        $perPage  = 20;
        $page     = $request->get('page', 1);
        $total    = $attendance->count();
        $items    = $attendance->values()->forPage($page, $perPage);

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('attendance.index', compact(
            'paginator', 'dateFrom', 'dateTo', 'userId',
            'sort', 'direction', 'users', 'total'
        ));
    }
}
```

- [ ] **Krok 3: Sprawdź czy trasa jest dostępna**

```bash
php artisan route:list --name=attendance
```

Oczekiwany wynik:
```
GET|HEAD  zasoby/obecnosci  attendance.index  App\Http\Controllers\AttendanceController@index
```

- [ ] **Krok 4: Commit**

```bash
git add app/Http/Controllers/AttendanceController.php routes/web.php
git commit -m "feat: Dodaj trasę i kontroler listy obecności"
```

---

## Task 2: Test feature kontrolera

**Files:**
- Create: `tests/Feature/AttendanceControllerTest.php`

- [ ] **Krok 1: Utwórz plik testu**

```php
<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskWorkLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_guests_cannot_access_attendance(): void
    {
        $response = $this->get(route('attendance.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_pracownik_cannot_access_attendance(): void
    {
        $pracownik = User::factory()->create(['role' => 'pracownik', 'is_active' => true]);
        $response = $this->actingAs($pracownik)->get(route('attendance.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_attendance(): void
    {
        $response = $this->actingAs($this->admin)->get(route('attendance.index'));
        $response->assertOk();
        $response->assertViewIs('attendance.index');
    }

    public function test_leader_appears_in_attendance_when_work_log_completed(): void
    {
        $leader = User::factory()->create(['name' => 'Jan Kowalski', 'role' => 'lider', 'is_active' => true]);
        $task = Task::factory()->create(['leader_id' => $leader->id, 'team' => null]);
        TaskWorkLog::factory()->create([
            'task_id'   => $task->id,
            'work_date' => '2026-03-15',
            'status'    => 'completed',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('attendance.index', ['date_from' => '2026-03-01', 'date_to' => '2026-03-31']));

        $response->assertOk();
        $response->assertSee('Jan Kowalski');
        $response->assertSee('15.03.2026');
    }

    public function test_team_member_appears_in_attendance(): void
    {
        $leader = User::factory()->create(['name' => 'Anna Nowak', 'role' => 'lider', 'is_active' => true]);
        $member = User::factory()->create(['name' => 'Piotr Wiśniewski', 'role' => 'pracownik', 'is_active' => true]);
        $task = Task::factory()->create([
            'leader_id' => $leader->id,
            'team'      => 'Piotr Wiśniewski',
        ]);
        TaskWorkLog::factory()->create([
            'task_id'   => $task->id,
            'work_date' => '2026-03-20',
            'status'    => 'in_progress',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('attendance.index', ['date_from' => '2026-03-01', 'date_to' => '2026-03-31']));

        $response->assertOk();
        $response->assertSee('Piotr Wiśniewski');
    }

    public function test_planned_work_log_does_not_create_attendance(): void
    {
        $leader = User::factory()->create(['name' => 'Marek Zielony', 'role' => 'lider', 'is_active' => true]);
        $task = Task::factory()->create(['leader_id' => $leader->id, 'team' => null]);
        TaskWorkLog::factory()->create([
            'task_id'   => $task->id,
            'work_date' => '2026-03-10',
            'status'    => 'planned',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('attendance.index', ['date_from' => '2026-03-01', 'date_to' => '2026-03-31']));

        $response->assertOk();
        $response->assertDontSee('Marek Zielony');
    }

    public function test_duplicate_attendance_deduplicated(): void
    {
        $leader = User::factory()->create(['name' => 'Tomasz Lis', 'role' => 'lider', 'is_active' => true]);
        $task1 = Task::factory()->create(['leader_id' => $leader->id, 'team' => null]);
        $task2 = Task::factory()->create(['leader_id' => $leader->id, 'team' => null]);
        TaskWorkLog::factory()->create(['task_id' => $task1->id, 'work_date' => '2026-03-05', 'status' => 'completed']);
        TaskWorkLog::factory()->create(['task_id' => $task2->id, 'work_date' => '2026-03-05', 'status' => 'completed']);

        $response = $this->actingAs($this->admin)
            ->get(route('attendance.index', ['date_from' => '2026-03-01', 'date_to' => '2026-03-31']));

        $response->assertOk();
        // Powinien pojawić się dokładnie raz - liczymy wystąpienia
        $content = $response->getContent();
        $this->assertEquals(1, substr_count($content, 'Tomasz Lis'));
    }

    public function test_filter_by_user_id(): void
    {
        $leader1 = User::factory()->create(['name' => 'Adam Pierwszy', 'role' => 'lider', 'is_active' => true]);
        $leader2 = User::factory()->create(['name' => 'Ewa Druga', 'role' => 'lider', 'is_active' => true]);
        $task1 = Task::factory()->create(['leader_id' => $leader1->id, 'team' => null]);
        $task2 = Task::factory()->create(['leader_id' => $leader2->id, 'team' => null]);
        TaskWorkLog::factory()->create(['task_id' => $task1->id, 'work_date' => '2026-03-12', 'status' => 'completed']);
        TaskWorkLog::factory()->create(['task_id' => $task2->id, 'work_date' => '2026-03-12', 'status' => 'completed']);

        $response = $this->actingAs($this->admin)
            ->get(route('attendance.index', [
                'date_from' => '2026-03-01',
                'date_to'   => '2026-03-31',
                'user_id'   => $leader1->id,
            ]));

        $response->assertOk();
        $response->assertSee('Adam Pierwszy');
        $response->assertDontSee('Ewa Druga');
    }
}
```

- [ ] **Krok 2: Uruchom testy i sprawdź czy są wymagane fabryki**

```bash
php artisan test tests/Feature/AttendanceControllerTest.php --stop-on-failure
```

Jeśli fabryki `Task::factory()` lub `TaskWorkLog::factory()` nie istnieją, sprawdź w `database/factories/`. Jeśli brakuje — dodaj brakujące pola do istniejących fabryk lub stwórz nowe.

Oczekiwany wynik: testy powinny przechodzić (lub failować z powodu brakujących fabryk — patrz krok 3).

- [ ] **Krok 3: Sprawdź fabryki (jeśli testy failują)**

```bash
ls database/factories/
```

Jeśli brakuje `TaskWorkLogFactory.php`, utwórz:

```php
<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\TaskType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskWorkLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'task_id'   => Task::factory(),
            'work_date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'start_time' => '07:00:00',
            'end_time'   => '15:00:00',
            'status'     => 'completed',
            'completed_tasks_count' => 0,
        ];
    }
}
```

- [ ] **Krok 4: Uruchom testy ponownie — wszystkie muszą przejść**

```bash
php artisan test tests/Feature/AttendanceControllerTest.php -v
```

Oczekiwany wynik: `PASS  Tests\Feature\AttendanceControllerTest` — wszystkie 7 testów zielone.

- [ ] **Krok 5: Commit**

```bash
git add tests/Feature/AttendanceControllerTest.php database/factories/
git commit -m "test: Dodaj testy feature dla AttendanceController"
```

---

## Task 3: Widok listy obecności

**Files:**
- Create: `resources/views/attendance/index.blade.php`

- [ ] **Krok 1: Utwórz katalog i plik widoku**

```bash
mkdir -p resources/views/attendance
```

Utwórz `resources/views/attendance/index.blade.php`:

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Obecności
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Lista obecności pracowników na podstawie zadań
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Filtry -->
            <div class="kt-card mb-6">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Filtrowanie</h3>
                </div>
                <div class="kt-card-body">
                    <form method="GET" action="{{ route('attendance.index') }}" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4 flex-wrap gap-y-4">
                        <input type="hidden" name="sort" value="{{ $sort }}">
                        <input type="hidden" name="direction" value="{{ $direction }}">

                        <div>
                            <label for="date_from" class="form-kt-label">Od daty</label>
                            <input type="date" id="date_from" name="date_from"
                                   class="form-kt-control"
                                   value="{{ $dateFrom }}">
                        </div>

                        <div>
                            <label for="date_to" class="form-kt-label">Do daty</label>
                            <input type="date" id="date_to" name="date_to"
                                   class="form-kt-control"
                                   value="{{ $dateTo }}">
                        </div>

                        <div class="sm:w-56">
                            <label for="user_id" class="form-kt-label">Pracownik</label>
                            <select id="user_id" name="user_id" class="form-kt-select">
                                <option value="">Wszyscy</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected($userId == $user->id)>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex space-x-2">
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-.293.707L13 8.414V15a1 1 0 01-.553.894l-4 2A1 1 0 017 17v-6.586L3.293 5.707A1 1 0 013 5V3z" clip-rule="evenodd"/>
                                </svg>
                                Filtruj
                            </button>
                            <a href="{{ route('attendance.index') }}" class="btn-kt-secondary">
                                Wyczyść
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Podsumowanie -->
            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <span class="text-blue-700 dark:text-blue-300 font-medium text-sm">
                    Znaleziono {{ $total }} {{ $total === 1 ? 'rekord' : ($total < 5 ? 'rekordy' : 'rekordów') }} obecności
                </span>
            </div>

            <!-- Tabela -->
            <div class="kt-card">
                <div class="kt-card-body">
                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>
                                        @php
                                            $nameDir = ($sort === 'name' && $direction === 'asc') ? 'desc' : 'asc';
                                        @endphp
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => $nameDir, 'page' => 1]) }}"
                                           class="flex items-center space-x-1 hover:text-blue-600 dark:hover:text-blue-400">
                                            <span>Imię i Nazwisko</span>
                                            @if($sort === 'name')
                                                @if($direction === 'asc')
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        @php
                                            $dateDir = ($sort === 'date' && $direction === 'asc') ? 'desc' : 'asc';
                                        @endphp
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'date', 'direction' => $dateDir, 'page' => 1]) }}"
                                           class="flex items-center space-x-1 hover:text-blue-600 dark:hover:text-blue-400">
                                            <span>Data</span>
                                            @if($sort === 'date' || $sort === '')
                                                @if($direction === 'asc')
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paginator as $row)
                                    <tr>
                                        <td class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $row['user_name'] }}
                                        </td>
                                        <td class="text-gray-600 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($row['work_date'])->format('d.m.Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-gray-500 dark:text-gray-400 py-8">
                                            Brak rekordów obecności dla wybranych parametrów.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($paginator->hasPages())
                        <div class="mt-4">
                            {{ $paginator->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
```

- [ ] **Krok 2: Sprawdź widok w przeglądarce**

```bash
php artisan serve
```

Otwórz `http://localhost:8000/zasoby/obecnosci` zalogowany jako admin. Sprawdź:
- Formularz filtrów wyświetla się poprawnie
- Tabela z dwiema kolumnami działa
- Kliknięcie nagłówka „Data" zmienia kierunek sortowania (strzałka się odwraca)
- Kliknięcie nagłówka „Imię i Nazwisko" działa analogicznie

- [ ] **Krok 3: Commit**

```bash
git add resources/views/attendance/
git commit -m "feat: Dodaj widok listy obecności"
```

---

## Task 4: Nawigacja — dodanie pozycji „Obecności" do menu Zasoby

**Files:**
- Modify: `resources/views/layouts/navigation.blade.php`

- [ ] **Krok 1: Aktualizuj warunek aktywnego stanu dropdownu Zasoby (desktop)**

W pliku `resources/views/layouts/navigation.blade.php` znajdź (ok. linia 56) warunek:

```blade
{{ request()->routeIs('vehicles.*') || request()->routeIs('users.*') || request()->routeIs('teams.*') ? 'border-blue-400 ...
```

Zmień na:

```blade
{{ request()->routeIs('vehicles.*') || request()->routeIs('users.*') || request()->routeIs('teams.*') || request()->routeIs('attendance.*') ? 'border-blue-400 dark:border-blue-600 text-gray-900 dark:text-gray-100 focus:border-blue-700' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700' }}
```

- [ ] **Krok 2: Dodaj pozycję „Obecności" do dropdownu desktop**

Znajdź blok `<div class="py-1">` wewnątrz dropdownu Zasoby (ok. linia 63) i dodaj link po „Zespoły":

```blade
<a href="{{ route('attendance.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ request()->routeIs('attendance.*') ? 'bg-gray-100 dark:bg-gray-700 font-medium' : '' }}">
    Obecności
</a>
```

- [ ] **Krok 3: Dodaj pozycję „Obecności" do menu mobile**

Znajdź sekcję mobilną Zasoby (ok. linia 190) i dodaj po `<x-responsive-nav-link>` dla Zespołów:

```blade
<x-responsive-nav-link :href="route('attendance.index')" :active="request()->routeIs('attendance.*')">
    Obecności
</x-responsive-nav-link>
```

- [ ] **Krok 4: Sprawdź nawigację**

```bash
php artisan serve
```

Otwórz aplikację i sprawdź:
- Dropdown „Zasoby" zawiera pozycję „Obecności"
- Kliknięcie przenosi na `/zasoby/obecnosci`
- Dropdown podświetla się gdy jesteś na stronie obecności
- Na mobile zakładka „Obecności" jest widoczna w sekcji Zasoby

- [ ] **Krok 5: Uruchom wszystkie testy**

```bash
php artisan test
```

Oczekiwany wynik: wszystkie testy zielone.

- [ ] **Krok 6: Commit końcowy**

```bash
git add resources/views/layouts/navigation.blade.php
git commit -m "feat: Dodaj zakładkę Obecności do menu Zasoby"
```

---

## Weryfikacja końcowa

- [ ] Otwórz `/zasoby/obecnosci` jako admin — lista się ładuje
- [ ] Otwórz jako pracownik/lider — otrzymujesz 403
- [ ] Ustaw filtr daty i pracownika — tabela filtruje poprawnie
- [ ] Kliknij nagłówek „Data" dwukrotnie — kierunek strzałki zmienia się ↓/↑
- [ ] Kliknij nagłówek „Imię i Nazwisko" — sortuje alphabetycznie
- [ ] Paginacja działa i zachowuje parametry filtrów

```bash
php artisan test tests/Feature/AttendanceControllerTest.php -v
```

Oczekiwany wynik: 7/7 PASS.
