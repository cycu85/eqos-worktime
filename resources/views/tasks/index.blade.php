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
        function toggleSortOrder() {
            const orderInput = document.querySelector('input[name="order"]');
            const currentOrder = orderInput.value;
            orderInput.value = currentOrder === 'desc' ? 'asc' : 'desc';
            orderInput.form.submit();
        }
    </script>
</x-app-layout>