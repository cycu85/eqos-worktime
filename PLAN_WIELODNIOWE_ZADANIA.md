# PLAN IMPLEMENTACJI WIELODNIOWYCH ZADAŃ Z INDYWIDUALNYMI GODZINAMI

## 📋 ZAKTUALIZOWANY PLAN - WIELODNIOWE ZADANIA Z DZIENNIKAMI PRACY

### 🎯 **REKOMENDOWANA OPCJA: Zadania wielodniowe z dziennikami pracy**

Każde zadanie wielodniowe to **jeden główny rekord + tabela dzienników pracy** dla każdego dnia.

---

## 🔧 **ZMIANY W BAZIE DANYCH** (wysoka złożoność)

**Nowa tabela - kluczowa zmiana:**
```sql
CREATE TABLE task_work_logs (
    id BIGINT PRIMARY KEY,
    task_id BIGINT REFERENCES tasks(id) ON DELETE CASCADE,
    work_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    actual_start_datetime DATETIME, -- pełna data+czas
    actual_end_datetime DATETIME,   -- pełna data+czas
    notes TEXT,
    status ENUM('planned', 'in_progress', 'completed', 'cancelled'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_task_date (task_id, work_date),
    INDEX idx_work_date (work_date),
    INDEX idx_task_status (task_id, status)
);
```

**Modyfikacje tabeli tasks:**
```sql
ALTER TABLE tasks ADD COLUMN is_multi_day BOOLEAN DEFAULT FALSE;
ALTER TABLE tasks ADD COLUMN multi_day_end_date DATE;
ALTER TABLE tasks ADD COLUMN daily_start_time TIME; -- domyślny czas rozpoczęcia
ALTER TABLE tasks ADD COLUMN daily_end_time TIME;   -- domyślny czas zakończenia
```

---

## 📝 **NOWE MODELE** (średnia złożoność)

**app/Models/TaskWorkLog.php** - całkowicie nowy model:
```php
class TaskWorkLog extends Model {
    protected $fillable = [
        'task_id', 'work_date', 'start_time', 'end_time',
        'actual_start_datetime', 'actual_end_datetime', 
        'notes', 'status'
    ];

    protected $casts = [
        'work_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'actual_start_datetime' => 'datetime',
        'actual_end_datetime' => 'datetime',
    ];

    public function task() {
        return $this->belongsTo(Task::class);
    }

    public function getDurationInMinutes() {
        if ($this->actual_start_datetime && $this->actual_end_datetime) {
            return $this->actual_start_datetime->diffInMinutes($this->actual_end_datetime);
        }
        return null;
    }

    public function getDurationHours() {
        $minutes = $this->getDurationInMinutes();
        return $minutes ? round($minutes / 60, 2) : null;
    }
}
```

**Zmiany w app/Models/Task.php:**
```php
class Task extends Model {
    // Dodane pola
    protected $fillable = [
        // ... istniejące ...
        'is_multi_day', 'multi_day_end_date', 
        'daily_start_time', 'daily_end_time'
    ];

    // Nowa relacja
    public function workLogs() {
        return $this->hasMany(TaskWorkLog::class)->orderBy('work_date');
    }

    // Nowe metody
    public function generateWorkLogs() {
        // Generuje wpisy pracy dla każdego dnia zadania
        if (!$this->is_multi_day) return;
        
        $startDate = $this->start_datetime->toDateString();
        $endDate = $this->multi_day_end_date->toDateString();
        
        $period = new DatePeriod(
            new DateTime($startDate),
            new DateInterval('P1D'),
            new DateTime($endDate . ' +1 day')
        );

        foreach ($period as $date) {
            TaskWorkLog::firstOrCreate([
                'task_id' => $this->id,
                'work_date' => $date->format('Y-m-d')
            ], [
                'start_time' => $this->daily_start_time,
                'end_time' => $this->daily_end_time,
                'status' => 'planned'
            ]);
        }
    }

    public function getTotalWorkHours() {
        if ($this->is_multi_day) {
            return $this->workLogs()->sum(function($log) {
                return $log->getDurationHours() ?? 0;
            });
        } else {
            // Stara logika dla zadań jednodniowych
            return $this->getDurationHoursAttribute();
        }
    }

    public function getTotalRoboczogodziny() {
        $totalHours = $this->getTotalWorkHours();
        $teamSize = $this->getTeamSize(); // liczba pracowników
        return $totalHours * $teamSize;
    }
}
```

---

## 🎨 **ZMIANY W FORMULARZACH** (wysoka złożoność)

**resources/views/tasks/create.blade.php:**
```html
<!-- Checkbox wielodniowe -->
<div class="form-group">
    <label class="flex items-center">
        <input type="checkbox" name="is_multi_day" id="is_multi_day" class="mr-2">
        Zadanie wielodniowe
    </label>
</div>

<!-- Pola wielodniowe (ukryte domyślnie) -->
<div id="multi-day-fields" class="hidden space-y-4">
    <div>
        <label>Data końcowa zadania *</label>
        <input type="date" name="multi_day_end_date" class="form-kt-control">
    </div>
    
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label>Domyślny czas rozpoczęcia *</label>
            <input type="time" name="daily_start_time" value="08:00" class="form-kt-control">
        </div>
        <div>
            <label>Domyślny czas zakończenia *</label>
            <input type="time" name="daily_end_time" value="16:00" class="form-kt-control">
        </div>
    </div>
    
    <div class="bg-blue-50 p-4 rounded">
        <p class="text-sm text-blue-700">
            ℹ️ Po utworzeniu zadania będzie można dostosować godziny pracy dla każdego dnia osobno.
        </p>
    </div>
</div>

<script>
document.getElementById('is_multi_day').addEventListener('change', function(e) {
    const fields = document.getElementById('multi-day-fields');
    const startField = document.querySelector('input[name="start_datetime"]');
    const endField = document.querySelector('input[name="end_datetime"]');
    
    if (e.target.checked) {
        fields.classList.remove('hidden');
        // Ukryj standardowe pola czasu końcowego
        endField.closest('.form-group').style.display = 'none';
        // Zmień label na "Data i czas rozpoczęcia pierwszego dnia"
        startField.previousElementSibling.textContent = 'Data i czas rozpoczęcia pierwszego dnia *';
    } else {
        fields.classList.add('hidden');
        endField.closest('.form-group').style.display = 'block';
        startField.previousElementSibling.textContent = 'Data i czas rozpoczęcia *';
    }
});
</script>
```

---

## 🔄 **ZMIANY W KONTROLERACH** (bardzo wysoka złożoność)

**app/Http/Controllers/TaskController.php - store():**
```php
public function store(Request $request) {
    $validated = $request->validate([
        // ... obecne pola ...
        'is_multi_day' => 'boolean',
        'multi_day_end_date' => 'required_if:is_multi_day,true|date|after:start_datetime',
        'daily_start_time' => 'required_if:is_multi_day,true|date_format:H:i',
        'daily_end_time' => 'required_if:is_multi_day,true|date_format:H:i|after:daily_start_time',
    ]);

    // Jeśli to zadanie wielodniowe, nie wymagaj end_datetime
    if ($validated['is_multi_day']) {
        unset($validated['end_datetime']);
    }

    $task = Task::create($validated);

    // Generuj wpisy pracy dla zadania wielodniowego
    if ($task->is_multi_day) {
        $task->generateWorkLogs();
    }

    return redirect()->route('tasks.show', $task);
}
```

**Nowy kontroler - app/Http/Controllers/TaskWorkLogController.php:**
```php
class TaskWorkLogController extends Controller {
    public function update(Request $request, Task $task, TaskWorkLog $workLog) {
        $this->authorize('update', $task);
        
        $validated = $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'actual_start_datetime' => 'nullable|date',
            'actual_end_datetime' => 'nullable|date|after:actual_start_datetime',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'notes' => 'nullable|string'
        ]);

        $workLog->update($validated);
        
        return back()->with('success', 'Dziennik pracy został zaktualizowany.');
    }

    public function bulkUpdate(Request $request, Task $task) {
        // Aktualizacja wielu dni naraz
    }
}
```

---

## 🎯 **NOWY WIDOK - ZARZĄDZANIE DNIAMI PRACY**

**resources/views/tasks/work-logs.blade.php** - całkowicie nowy widok:
```html
<div class="kt-card">
    <div class="kt-card-header">
        <h3 class="kt-card-title">Harmonogram pracy - {{ $task->title }}</h3>
        <span class="badge">{{ $task->start_datetime->format('d.m.Y') }} - {{ $task->multi_day_end_date->format('d.m.Y') }}</span>
    </div>
    <div class="kt-card-body">
        <form method="POST" action="{{ route('tasks.work-logs.bulk-update', $task) }}">
            @csrf
            <div class="space-y-4">
                @foreach($task->workLogs as $log)
                <div class="flex items-center space-x-4 p-4 border rounded">
                    <div class="w-32">
                        <strong>{{ $log->work_date->format('d.m.Y') }}</strong><br>
                        <small class="text-gray-500">{{ $log->work_date->format('l') }}</small>
                    </div>
                    
                    <div class="flex-1 grid grid-cols-4 gap-4">
                        <div>
                            <label class="form-kt-label">Początek</label>
                            <input type="time" 
                                   name="logs[{{ $log->id }}][start_time]" 
                                   value="{{ $log->start_time?->format('H:i') }}"
                                   class="form-kt-control">
                        </div>
                        <div>
                            <label class="form-kt-label">Koniec</label>
                            <input type="time" 
                                   name="logs[{{ $log->id }}][end_time]" 
                                   value="{{ $log->end_time?->format('H:i') }}"
                                   class="form-kt-control">
                        </div>
                        <div>
                            <label class="form-kt-label">Status</label>
                            <select name="logs[{{ $log->id }}][status]" class="form-kt-select">
                                <option value="planned" {{ $log->status === 'planned' ? 'selected' : '' }}>Planowane</option>
                                <option value="in_progress" {{ $log->status === 'in_progress' ? 'selected' : '' }}>W trakcie</option>
                                <option value="completed" {{ $log->status === 'completed' ? 'selected' : '' }}>Ukończone</option>
                                <option value="cancelled" {{ $log->status === 'cancelled' ? 'selected' : '' }}>Anulowane</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-kt-label">Godziny</label>
                            <span class="form-kt-control bg-gray-50" id="duration-{{ $log->id }}">
                                {{ $log->getDurationHours() ?? '---' }}h
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="mt-6 flex justify-between items-center">
                <div class="text-lg font-semibold">
                    Łącznie: {{ $task->getTotalWorkHours() }}h | 
                    Roboczogodziny: {{ $task->getTotalRoboczogodziny() }}h
                </div>
                <button type="submit" class="btn-kt-primary">Zapisz wszystkie zmiany</button>
            </div>
        </form>
    </div>
</div>
```

---

## 📊 **ZMIANY W KALENDARZU** (bardzo wysoka złożoność)

**JavaScript - nowa logika renderowania:**
```javascript
function getTasksForDate(date) {
    const singleDayTasks = tasksData.filter(task => 
        !task.is_multi_day && isSameDay(date, task.start_datetime)
    );
    
    const multiDayTasks = tasksData.filter(task => 
        task.is_multi_day && isDateInRange(date, task.start_datetime, task.multi_day_end_date)
    );
    
    // Dla zadań wielodniowych pobierz informacje o pracy tego dnia
    const enrichedMultiDayTasks = multiDayTasks.map(task => ({
        ...task,
        workLog: getWorkLogForDate(task.id, date),
        isFirstDay: isSameDay(date, task.start_datetime),
        isLastDay: isSameDay(date, task.multi_day_end_date)
    }));
    
    return [...singleDayTasks, ...enrichedMultiDayTasks];
}

function renderMultiDayTaskInCalendar(task, date) {
    const taskEl = document.createElement('div');
    let cssClasses = 'text-xs p-1 mb-1 rounded cursor-pointer ';
    
    // Różne style dla pierwszego/środkowego/ostatniego dnia
    if (task.isFirstDay) {
        cssClasses += 'rounded-r-none border-r-2 border-dashed ';
    } else if (task.isLastDay) {
        cssClasses += 'rounded-l-none border-l-2 border-dashed ';
    } else {
        cssClasses += 'rounded-none border-x-2 border-dashed ';
    }
    
    // Kolor na podstawie statusu dnia pracy
    if (task.workLog) {
        cssClasses += getWorkLogStatusClass(task.workLog.status);
        taskEl.title = `${task.title}\n${task.workLog.start_time} - ${task.workLog.end_time}\nStatus: ${task.workLog.status}`;
    } else {
        cssClasses += 'bg-gray-200 text-gray-600 ';
        taskEl.title = `${task.title}\nBrak harmonogramu na ten dzień`;
    }
    
    taskEl.className = cssClasses;
    taskEl.textContent = task.isFirstDay ? task.title : '↔ ' + task.title.substring(0, 8) + '...';
    taskEl.onclick = () => window.location.href = task.work_logs_url || task.url;
    
    return taskEl;
}
```

---

## 📈 **ZMIANY W EKSPORCIE** (wysoka złożoność)

**app/Exports/TaskExport.php - całkowita przebudowa:**
```php
public function query() {
    $baseQuery = /* ... obecna logika ... */;
    
    // Dla zadań wielodniowych, eksportuj każdy dzień osobno
    $tasks = $baseQuery->get();
    $exportData = collect();
    
    foreach ($tasks as $task) {
        if ($task->is_multi_day) {
            // Dodaj jeden wiersz dla każdego dnia pracy
            foreach ($task->workLogs as $workLog) {
                $exportData->push((object)[
                    'id' => $task->id,
                    'title' => $task->title,
                    'work_date' => $workLog->work_date,
                    'start_datetime' => $workLog->actual_start_datetime ?? 
                        $workLog->work_date->format('Y-m-d') . ' ' . $workLog->start_time,
                    'end_datetime' => $workLog->actual_end_datetime ?? 
                        $workLog->work_date->format('Y-m-d') . ' ' . $workLog->end_time,
                    'daily_status' => $workLog->status,
                    'daily_notes' => $workLog->notes,
                    'is_multi_day_entry' => true,
                    // ... inne pola ...
                ]);
            }
        } else {
            // Zadanie jednodniowe - bez zmian
            $exportData->push($task);
        }
    }
    
    return $exportData;
}

public function headings(): array {
    return [
        'ID', 'Tytuł', 'Opis', 'Data pracy', 'Typ zadania',
        'Początek dnia', 'Koniec dnia', 'Status dnia',
        'Godziny dnia', 'Roboczogodziny dnia',
        'Pojazd', 'Lider', 'Zespół', 'Notatki dnia'
    ];
}
```

---

## 🗂️ **NOWE MIGRACJE POTRZEBNE**

1. **2025_XX_XX_create_task_work_logs_table.php**
2. **2025_XX_XX_add_multi_day_fields_to_tasks_table.php**

---

## 🛣️ **NOWE ROUTING**

```php
// routes/web.php
Route::resource('tasks.work-logs', TaskWorkLogController::class)
    ->only(['update', 'destroy'])
    ->shallow();

Route::post('tasks/{task}/work-logs/bulk-update', [TaskWorkLogController::class, 'bulkUpdate'])
    ->name('tasks.work-logs.bulk-update');

Route::get('tasks/{task}/work-logs', [TaskController::class, 'workLogs'])
    ->name('tasks.work-logs');
```

---

## ⏱️ **ZAKTUALIZOWANE OSZACOWANIE CZASU**

### **Wielodniowe zadania z indywidualnymi godzinami:**
- **Czas:** 4-6 dni pracy (znacznie więcej!)
- **Pliki do modyfikacji:** ~20 plików
- **Nowe pliki:** ~8 plików
- **Nowe linie kodu:** ~1500-2000 linii

### **Rozkład złożoności:**
- 🔴 **Bardzo wysoka złożoność:** Kontrolery, kalendarz, eksport
- 🟡 **Wysoka złożoność:** Baza danych, widoki, formularze  
- 🟢 **Średnia złożoność:** Modele, testy

---

## 🚨 **NOWE WYZWANIA Z INDYWIDUALNYMI GODZINAMI**

1. **UX/UI** - jak intuicyjnie zarządzać harmonogramem wielu dni
2. **Wydajność** - tabela work_logs może szybko rosnąć 
3. **Raportowanie** - jak agregować roboczogodziny z wielu dni
4. **Edycja** - czy zmiany w zadaniu głównym mają wpływać na wszystkie dni
5. **Kalendarz** - jak pokazać status każdego dnia w zadaniu wielodniowym

**Ale korzyść jest ogromna** - pełna kontrola nad roboczogodzinami każdego dnia! 📊

---

## 📂 **KOLEJNOŚĆ IMPLEMENTACJI**

### **Faza 1: Fundament (1-2 dni)**
1. Migracje bazy danych
2. Nowe modele (TaskWorkLog)
3. Relacje w Task model
4. Podstawowe testy

### **Faza 2: CRUD (1-2 dni)**  
5. Formularz tworzenia z checkboxem wielodniowym
6. TaskWorkLogController 
7. Widok zarządzania dniami pracy
8. Walidacja i logika biznesowa

### **Faza 3: Interfejs (1-2 dni)**
9. Integracja z widokiem szczegółów zadania
10. Aktualizacja listy zadań (ikony, etykiety)
11. JavaScript dla formularzy

### **Faza 4: Kalendarz & Eksport (1-2 dni)**
12. Logika kalendarza dla zadań wielodniowych
13. Aktualizacja eksportu Excel
14. Testy integracyjne

---

## 🎯 **KLUCZOWE DECYZJE DO PODJĘCIA**

1. **Domyślne godziny pracy** - jakie ustawić (8:00-16:00)?
2. **Weekendy** - czy generować dni pracy w weekendy?
3. **Status zadania głównego** - jak określać na podstawie statusów dni?
4. **Edycja grupowa** - możliwość zmiany wszystkich dni naraz?
5. **Kopiowanie zadań** - czy kopiować też work_logs?

---

*Plan zapisany: {{ date('Y-m-d H:i:s') }}*