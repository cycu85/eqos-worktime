<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    {{ __('Zadania') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    @if(auth()->user()->isLider())
                        Moje zadania
                    @elseif(auth()->user()->isKierownik())
                        Wszystkie zadania - tylko odczyt
                    @elseif(auth()->user()->isPracownik())
                        Zadania zespołowe
                    @else
                        Wszystkie zadania
                    @endif
                </p>
            </div>
            
            <div class="mt-3 sm:mt-0 flex space-x-3">
                @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                    <a href="{{ route('tasks.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn-kt-secondary">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        Eksport do Excel
                    </a>
                @endif
                @can('create', App\Models\Task::class)
                    <a href="{{ route('tasks.create') }}" class="btn-kt-primary">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        Nowe zadanie
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Search and Filter Card -->
            <div class="kt-card mb-6">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Wyszukiwanie i filtrowanie</h3>
                </div>
                <div class="kt-card-body">
                    <form method="GET" action="{{ route('tasks.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Search -->
                            <div>
                                <label class="form-kt-label">Wyszukaj</label>
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       class="form-kt-control" 
                                       placeholder="Tytuł, opis, zespół...">
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label class="form-kt-label">Status</label>
                                <select name="status" class="form-kt-select">
                                    <option value="">Wszystkie statusy</option>
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Vehicle Filter -->
                            <div>
                                <label class="form-kt-label">Pojazd</label>
                                <select name="vehicle_id" class="form-kt-select">
                                    <option value="">Wszystkie pojazdy</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->name }} ({{ $vehicle->registration }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date From -->
                            <div>
                                <label class="form-kt-label">Data od</label>
                                <input type="date" 
                                       name="date_from" 
                                       value="{{ request('date_from') }}"
                                       class="form-kt-control">
                            </div>

                            <!-- Date To -->
                            <div>
                                <label class="form-kt-label">Data do</label>
                                <input type="date" 
                                       name="date_to" 
                                       value="{{ request('date_to') }}"
                                       class="form-kt-control">
                            </div>

                            <!-- User Filter -->
                            <div>
                                <label class="form-kt-label">Użytkownik</label>
                                <select name="user_id" class="form-kt-select">
                                    <option value="">Wszyscy użytkownicy</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                </svg>
                                Szukaj
                            </button>
                            
                            <input type="hidden" name="order" value="{{ request('order', 'desc') }}">
                            
                            <a href="{{ route('tasks.index') }}" class="btn-kt-light">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                </svg>
                                Wyczyść filtry
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Info -->
            @if(request()->hasAny(['search', 'status', 'vehicle_id', 'date_from', 'date_to', 'user_id', 'sort']))
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4 text-sm">
                            <span class="text-blue-700 dark:text-blue-300 font-medium">
                                Znaleziono: {{ $tasks->total() }} {{ Str::plural('zadanie', $tasks->total()) }}
                            </span>
                            @if(request('search'))
                                <span class="text-blue-600 dark:text-blue-400">
                                    Szukano: "{{ request('search') }}"
                                </span>
                            @endif
                            @if(request('status'))
                                <span class="text-blue-600 dark:text-blue-400">
                                    Status: {{ request('status') == 'planned' ? 'Planowane' : (request('status') == 'in_progress' ? 'W trakcie' : (request('status') == 'completed' ? 'Ukończone' : 'Anulowane')) }}
                                </span>
                            @endif
                            @if(request('user_id'))
                                <span class="text-blue-600 dark:text-blue-400">
                                    Użytkownik: {{ $users->where('id', request('user_id'))->first()->name ?? 'Nieznany' }}
                                </span>
                            @endif
                        </div>
                        <a href="{{ route('tasks.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                            Wyczyść filtry
                        </a>
                    </div>
                </div>
            @endif

            <!-- Calendar View -->
            <div class="kt-card mb-6" id="calendar-section">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Kalendarz zadań</h3>
                    <div class="flex items-center space-x-2">
                        <!-- View Toggle -->
                        <div class="flex items-center space-x-1 mr-4">
                            <button id="month-view" class="btn-kt-primary btn-sm">Miesiąc</button>
                            <button id="week-view" class="btn-kt-light btn-sm">Tydzień</button>
                            <button id="day-view" class="btn-kt-light btn-sm">Dzień</button>
                        </div>
                        
                        <!-- Navigation -->
                        <button id="prev-period" class="btn-kt-light btn-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <span id="current-period" class="text-lg font-semibold text-gray-900 dark:text-gray-100 px-4 min-w-[200px] text-center"></span>
                        <button id="next-period" class="btn-kt-light btn-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <button id="today-btn" class="btn-kt-secondary btn-sm ml-2">Dziś</button>
                    </div>
                </div>
                <div class="kt-card-body">
                    <!-- Month View -->
                    <div id="month-view-container">
                        <div class="grid grid-cols-7 gap-1 mb-2" id="month-headers">
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Pon</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Wt</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Śr</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Czw</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Pt</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Sob</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Nie</div>
                        </div>
                        <div id="calendar-grid" class="grid grid-cols-7 gap-1">
                            <!-- Calendar days will be generated by JavaScript -->
                        </div>
                    </div>

                    <!-- Week View -->
                    <div id="week-view-container" class="hidden">
                        <div class="grid grid-cols-8 gap-1 mb-2" id="week-headers">
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Czas</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Pon</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Wt</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Śr</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Czw</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Pt</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Sob</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Nie</div>
                        </div>
                        <div id="week-grid" class="grid grid-cols-8 gap-1">
                            <!-- Week view will be generated by JavaScript -->
                        </div>
                    </div>

                    <!-- Day View -->
                    <div id="day-view-container" class="hidden">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Harmonogram godzinowy</h4>
                                <div id="day-grid" class="space-y-1">
                                    <!-- Day view will be generated by JavaScript -->
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Zadania na dzień</h4>
                                <div id="day-tasks" class="space-y-2">
                                    <!-- Day tasks will be listed here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-card">
                @if($tasks->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'title', 'direction' => request('sort') == 'title' && request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="flex items-center space-x-1 hover:text-blue-600 dark:hover:text-blue-400">
                                            <span>Zadanie</span>
                                            @if(request('sort', 'title') == 'title')
                                                @if(request('direction', 'asc') == 'asc')
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>Pojazd</th>
                                    <th>Lider</th>
                                    <th>Zespół</th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'start_datetime', 'direction' => request('sort') == 'start_datetime' && request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="flex items-center space-x-1 hover:text-blue-600 dark:hover:text-blue-400">
                                            <span>Start</span>
                                            @if(request('sort') == 'start_datetime')
                                                @if(request('direction', 'asc') == 'asc')
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}" 
                                           class="flex items-center space-x-1 hover:text-blue-600 dark:hover:text-blue-400">
                                            <span>Status</span>
                                            @if(request('sort') == 'status')
                                                @if(request('direction', 'asc') == 'asc')
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                                        <th>Czas</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tasks as $task)
                                    <tr>
                                        <td>
                                            <a href="{{ route('tasks.show', $task) }}" class="block hover:bg-gray-50 dark:hover:bg-gray-700/50 -mx-4 px-4 py-2 rounded transition-colors">
                                                <div class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                                    {{ $task->title }}
                                                </div>
                                                @if($task->description)
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ Str::limit($task->description, 50) }}
                                                    </div>
                                                @endif
                                            </a>
                                        </td>
                                        <td>
                                            @if($task->vehicles->count() > 0)
                                                @foreach($task->vehicles as $vehicle)
                                                    <div class="text-gray-900 dark:text-gray-100">{{ $vehicle->name }}</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $vehicle->registration }}</div>
                                                    @if(!$loop->last)<hr class="my-1 border-gray-300 dark:border-gray-600">@endif
                                                @endforeach
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">Brak pojazdów</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $task->leader->name }}
                                        </td>
                                        <td>
                                            @php
                                                $teamString = $task->getAttributes()['team'] ?? '';
                                            @endphp
                                            
                                            @if($teamString)
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $teamString }}</div>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $task->start_datetime->format('d.m.Y H:i') }}
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = match($task->status) {
                                                    'planned' => 'badge-kt-warning',
                                                    'in_progress' => 'badge-kt-primary',
                                                    'completed' => 'badge-kt-success',
                                                    'cancelled' => 'badge-kt-danger',
                                                    'accepted' => 'badge-kt-info',
                                                    default => 'badge-kt-light'
                                                };
                                                $statusLabels = [
                                                    'planned' => 'Planowane',
                                                    'in_progress' => 'W trakcie',
                                                    'completed' => 'Ukończone',
                                                    'cancelled' => 'Anulowane',
                                                    'accepted' => 'Zaakceptowane'
                                                ];
                                            @endphp
                                            <span class="{{ $badgeClass }}">
                                                {{ $statusLabels[$task->status] ?? $task->status }}
                                            </span>
                                        </td>
                                        @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                                            <td>
                                                @if($task->duration_hours)
                                                    {{ $task->duration_hours }}h
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="kt-card-footer">
                        {{ $tasks->links() }}
                    </div>
                @else
                    <div class="kt-card-body text-center">
                        <div class="text-gray-500 dark:text-gray-400 mb-4">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Brak zadań</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">
                            @if(auth()->user()->isLider())
                                Nie masz jeszcze żadnych zadań.
                            @else
                                W systemie nie ma jeszcze żadnych zadań.
                            @endif
                        </p>
                        @can('create', App\Models\Task::class)
                            <a href="{{ route('tasks.create') }}" class="btn-kt-primary">
                                Utwórz pierwsze zadanie
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Tasks data for calendar - prepared in controller
        const tasksData = @json($calendarTasks);

        let currentDate = new Date();
        let currentView = 'month'; // month, week, day
        
        function toggleSortOrder() {
            const orderInput = document.querySelector('input[name="order"]');
            const currentOrder = orderInput.value;
            orderInput.value = currentOrder === 'desc' ? 'asc' : 'desc';
            orderInput.form.submit();
        }

        function formatMonth(date) {
            const months = [
                'Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec',
                'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'
            ];
            return months[date.getMonth()] + ' ' + date.getFullYear();
        }

        function formatWeek(date) {
            const startOfWeek = new Date(date);
            startOfWeek.setDate(date.getDate() - (date.getDay() + 6) % 7);
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6);
            
            const months = [
                'Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze',
                'Lip', 'Sie', 'Wrz', 'Paź', 'Lis', 'Gru'
            ];
            
            return `${startOfWeek.getDate()} ${months[startOfWeek.getMonth()]} - ${endOfWeek.getDate()} ${months[endOfWeek.getMonth()]} ${endOfWeek.getFullYear()}`;
        }

        function formatDay(date) {
            const days = ['Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota'];
            const months = [
                'stycznia', 'lutego', 'marca', 'kwietnia', 'maja', 'czerwca',
                'lipca', 'sierpnia', 'września', 'października', 'listopada', 'grudnia'
            ];
            return `${days[date.getDay()]}, ${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
        }

        function updatePeriodDisplay() {
            const periodEl = document.getElementById('current-period');
            switch(currentView) {
                case 'month':
                    periodEl.textContent = formatMonth(currentDate);
                    break;
                case 'week':
                    periodEl.textContent = formatWeek(currentDate);
                    break;
                case 'day':
                    periodEl.textContent = formatDay(currentDate);
                    break;
            }
        }

        function switchView(view) {
            currentView = view;
            
            // Update button styles
            document.querySelectorAll('#month-view, #week-view, #day-view').forEach(btn => {
                btn.className = btn.className.replace('btn-kt-primary', 'btn-kt-light');
            });
            document.getElementById(`${view}-view`).className = document.getElementById(`${view}-view`).className.replace('btn-kt-light', 'btn-kt-primary');
            
            // Show/hide containers
            document.getElementById('month-view-container').classList.toggle('hidden', view !== 'month');
            document.getElementById('week-view-container').classList.toggle('hidden', view !== 'week');
            document.getElementById('day-view-container').classList.toggle('hidden', view !== 'day');
            
            updatePeriodDisplay();
            renderCalendar();
        }

        function getStatusBadgeClass(status) {
            switch(status) {
                case 'planned': return 'badge-kt-warning';
                case 'in_progress': return 'badge-kt-primary';
                case 'completed': return 'badge-kt-success';
                case 'cancelled': return 'badge-kt-danger';
                case 'accepted': return 'badge-kt-info';
                default: return 'badge-kt-light';
            }
        }

        function getStatusLabel(status) {
            const labels = {
                'planned': 'Planowane',
                'in_progress': 'W trakcie',
                'completed': 'Ukończone',
                'cancelled': 'Anulowane',
                'accepted': 'Zaakceptowane'
            };
            return labels[status] || status;
        }

        function renderCalendar() {
            updatePeriodDisplay();
            
            switch(currentView) {
                case 'month':
                    renderMonthView();
                    break;
                case 'week':
                    renderWeekView();
                    break;
                case 'day':
                    renderDayView();
                    break;
            }
        }

        function renderMonthView() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            // Get first day of month and number of days
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDay = (firstDay.getDay() + 6) % 7; // Monday = 0
            
            const grid = document.getElementById('calendar-grid');
            grid.innerHTML = '';
            
            // Add empty cells for days before month starts
            for (let i = 0; i < startingDay; i++) {
                const cell = document.createElement('div');
                cell.className = 'h-32 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50';
                grid.appendChild(cell);
            }
            
            // Add cells for each day of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const cell = document.createElement('div');
                const today = new Date();
                const isToday = today.getDate() === day && today.getMonth() === month && today.getFullYear() === year;
                
                cell.className = `h-32 border border-gray-200 dark:border-gray-700 p-1 overflow-y-auto transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50 ${
                    isToday ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-600' : 'bg-white dark:bg-gray-800'
                }`;
                
                // Day number
                const dayNumber = document.createElement('div');
                dayNumber.className = `text-xs font-medium mb-1 ${
                    isToday ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-gray-900 dark:text-gray-100'
                }`;
                dayNumber.textContent = day;
                cell.appendChild(dayNumber);
                
                // Find tasks for this day
                const dayDate = new Date(year, month, day);
                const dayTasks = tasksData.filter(task => {
                    const taskDate = new Date(task.start_datetime);
                    return taskDate.getDate() === day && 
                           taskDate.getMonth() === month && 
                           taskDate.getFullYear() === year;
                });
                
                // Add tasks (show max 2, then indicate more)
                dayTasks.slice(0, 2).forEach(task => {
                    const taskEl = document.createElement('div');
                    taskEl.className = 'text-xs p-1 mb-1 rounded cursor-pointer hover:opacity-80 transition-opacity shadow-sm ' + getStatusBadgeClass(task.status);
                    taskEl.textContent = task.title.length > 12 ? task.title.substring(0, 12) + '...' : task.title;
                    taskEl.title = `${task.title}\nLider: ${task.leader}\nStatus: ${getStatusLabel(task.status)}\nPojazdy: ${task.vehicles || 'Brak'}`;
                    taskEl.onclick = () => window.location.href = task.url;
                    cell.appendChild(taskEl);
                });
                
                // Show more indicator if there are more than 2 tasks
                if (dayTasks.length > 2) {
                    const moreIndicator = document.createElement('div');
                    moreIndicator.className = 'text-xs text-gray-500 dark:text-gray-400 font-medium';
                    moreIndicator.textContent = `+${dayTasks.length - 2} więcej`;
                    cell.appendChild(moreIndicator);
                }
                
                grid.appendChild(cell);
            }
        }

        function renderWeekView() {
            const startOfWeek = new Date(currentDate);
            startOfWeek.setDate(currentDate.getDate() - (currentDate.getDay() + 6) % 7);
            
            const grid = document.getElementById('week-grid');
            grid.innerHTML = '';
            
            // Time slots (6:00 - 22:00)
            for (let hour = 6; hour <= 22; hour++) {
                // Time column
                const timeCell = document.createElement('div');
                timeCell.className = 'p-2 text-xs font-medium text-gray-600 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700';
                timeCell.textContent = `${hour}:00`;
                grid.appendChild(timeCell);
                
                // Day columns
                for (let day = 0; day < 7; day++) {
                    const cellDate = new Date(startOfWeek);
                    cellDate.setDate(startOfWeek.getDate() + day);
                    
                    const cell = document.createElement('div');
                    const today = new Date();
                    const isToday = cellDate.toDateString() === today.toDateString();
                    
                    cell.className = `h-12 border border-gray-200 dark:border-gray-700 p-1 text-xs ${
                        isToday ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-white dark:bg-gray-800'
                    } hover:bg-gray-50 dark:hover:bg-gray-700/50`;
                    
                    // Find tasks for this hour and day
                    const hourTasks = tasksData.filter(task => {
                        const taskDate = new Date(task.start_datetime);
                        return taskDate.toDateString() === cellDate.toDateString() && 
                               taskDate.getHours() === hour;
                    });
                    
                    // Add tasks
                    hourTasks.forEach(task => {
                        const taskEl = document.createElement('div');
                        taskEl.className = 'text-xs p-1 rounded cursor-pointer mb-1 ' + getStatusBadgeClass(task.status);
                        taskEl.textContent = task.title.length > 8 ? task.title.substring(0, 8) + '...' : task.title;
                        taskEl.title = `${task.title}\nLider: ${task.leader}\nStatus: ${getStatusLabel(task.status)}`;
                        taskEl.onclick = () => window.location.href = task.url;
                        cell.appendChild(taskEl);
                    });
                    
                    grid.appendChild(cell);
                }
            }
        }

        function renderDayView() {
            const grid = document.getElementById('day-grid');
            const tasksList = document.getElementById('day-tasks');
            
            grid.innerHTML = '';
            tasksList.innerHTML = '';
            
            // Time slots
            for (let hour = 6; hour <= 22; hour++) {
                const timeSlot = document.createElement('div');
                timeSlot.className = 'flex items-center p-2 border border-gray-200 dark:border-gray-700 rounded';
                
                const time = document.createElement('div');
                time.className = 'w-16 text-sm font-medium text-gray-600 dark:text-gray-400';
                time.textContent = `${hour}:00`;
                
                const tasks = document.createElement('div');
                tasks.className = 'flex-1 ml-4 space-y-1';
                
                // Find tasks for this hour
                const hourTasks = tasksData.filter(task => {
                    const taskDate = new Date(task.start_datetime);
                    return taskDate.toDateString() === currentDate.toDateString() && 
                           taskDate.getHours() === hour;
                });
                
                if (hourTasks.length > 0) {
                    hourTasks.forEach(task => {
                        const taskEl = document.createElement('div');
                        taskEl.className = 'text-xs p-2 rounded cursor-pointer ' + getStatusBadgeClass(task.status);
                        taskEl.textContent = task.title;
                        taskEl.onclick = () => window.location.href = task.url;
                        tasks.appendChild(taskEl);
                    });
                } else {
                    tasks.innerHTML = '<div class="text-xs text-gray-400">Brak zadań</div>';
                }
                
                timeSlot.appendChild(time);
                timeSlot.appendChild(tasks);
                grid.appendChild(timeSlot);
            }
            
            // All tasks for the day
            const dayTasks = tasksData.filter(task => {
                const taskDate = new Date(task.start_datetime);
                return taskDate.toDateString() === currentDate.toDateString();
            });
            
            if (dayTasks.length > 0) {
                dayTasks.forEach(task => {
                    const taskEl = document.createElement('div');
                    taskEl.className = 'p-3 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50';
                    
                    const taskTime = new Date(task.start_datetime);
                    taskEl.innerHTML = `
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-gray-900 dark:text-gray-100">${task.title}</span>
                            <span class="${getStatusBadgeClass(task.status)}">${getStatusLabel(task.status)}</span>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <div>Lider: ${task.leader}</div>
                            <div>Czas: ${taskTime.getHours()}:${taskTime.getMinutes().toString().padStart(2, '0')}</div>
                            ${task.vehicles ? `<div>Pojazdy: ${task.vehicles}</div>` : ''}
                        </div>
                    `;
                    
                    taskEl.onclick = () => window.location.href = task.url;
                    tasksList.appendChild(taskEl);
                });
            } else {
                tasksList.innerHTML = '<div class="text-center text-gray-500 dark:text-gray-400 py-4">Brak zadań na wybrany dzień</div>';
            }
        }

        function navigatePeriod(direction) {
            switch(currentView) {
                case 'month':
                    currentDate.setMonth(currentDate.getMonth() + direction);
                    break;
                case 'week':
                    currentDate.setDate(currentDate.getDate() + (direction * 7));
                    break;
                case 'day':
                    currentDate.setDate(currentDate.getDate() + direction);
                    break;
            }
            renderCalendar();
        }

        function goToToday() {
            currentDate = new Date();
            renderCalendar();
        }

        // Event listeners
        document.getElementById('month-view').addEventListener('click', () => switchView('month'));
        document.getElementById('week-view').addEventListener('click', () => switchView('week'));
        document.getElementById('day-view').addEventListener('click', () => switchView('day'));
        
        document.getElementById('prev-period').addEventListener('click', () => navigatePeriod(-1));
        document.getElementById('next-period').addEventListener('click', () => navigatePeriod(1));
        document.getElementById('today-btn').addEventListener('click', goToToday);

        // Initial render
        document.addEventListener('DOMContentLoaded', () => {
            renderCalendar();
        });
    </script>
</x-app-layout>