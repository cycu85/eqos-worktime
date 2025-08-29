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
            
            @can('create', App\Models\Task::class)
                <div class="mt-3 sm:mt-0">
                    <a href="{{ route('tasks.create') }}" class="btn-kt-primary">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        Nowe zadanie
                    </a>
                </div>
            @endcan
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

                            <!-- Sort -->
                            <div>
                                <label class="form-kt-label">Sortuj według</label>
                                <select name="sort" class="form-kt-select">
                                    <option value="start_datetime" {{ request('sort') == 'start_datetime' ? 'selected' : '' }}>
                                        Data rozpoczęcia
                                    </option>
                                    <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>
                                        Tytuł
                                    </option>
                                    <option value="status" {{ request('sort') == 'status' ? 'selected' : '' }}>
                                        Status
                                    </option>
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>
                                        Data utworzenia
                                    </option>
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
                            
                            <button type="button" onclick="toggleSortOrder()" class="btn-kt-secondary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z" clip-rule="evenodd"></path>
                                </svg>
                                {{ request('order') == 'desc' ? 'Malejąco' : 'Rosnąco' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="kt-card">
                @if($tasks->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>Zadanie</th>
                                    <th>Pojazd</th>
                                    <th>Lider</th>
                                    <th>Zespół</th>
                                    <th>Start</th>
                                    <th>Status</th>
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
                                            <div class="text-gray-900 dark:text-gray-100">{{ $task->vehicle->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $task->vehicle->registration }}</div>
                                        </td>
                                        <td>
                                            {{ $task->leader->name }}
                                        </td>
                                        <td>
                                            @if($task->team_id && $task->team)
                                                <div class="text-gray-900 dark:text-gray-100">{{ $task->team->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $task->team->members_names }}</div>
                                            @elseif($task->team)
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $task->team }}</div>
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
                                                    default => 'badge-kt-light'
                                                };
                                                $statusLabels = [
                                                    'planned' => 'Planowane',
                                                    'in_progress' => 'W trakcie',
                                                    'completed' => 'Ukończone',
                                                    'cancelled' => 'Anulowane'
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
        function toggleSortOrder() {
            const orderInput = document.querySelector('input[name="order"]');
            const currentOrder = orderInput.value;
            orderInput.value = currentOrder === 'desc' ? 'asc' : 'desc';
            orderInput.form.submit();
        }
    </script>
</x-app-layout>