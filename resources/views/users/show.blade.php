<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    {{ $user->name }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $user->email }}
                    @php
                        $roleLabels = [
                            'admin' => 'Administrator',
                            'kierownik' => 'Kierownik', 
                            'lider' => 'Lider',
                            'pracownik' => 'Pracownik'
                        ];
                    @endphp
                    • {{ $roleLabels[$user->role] ?? $user->role }}
                    @if(!$user->is_active)
                        <span class="badge-kt-danger ml-2">Nieaktywny</span>
                    @endif
                </p>
            </div>
            <div class="mt-3 sm:mt-0 flex space-x-3">
                @can('update', $user)
                    <a href="{{ route('users.edit', $user) }}" class="btn-kt-warning">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                        Edytuj
                    </a>
                @endcan
                @can('update', $user)
                    <form method="POST" action="{{ route('users.toggle-active', $user) }}" class="inline" onsubmit="return confirm('{{ $user->is_active ? 'Czy na pewno chcesz dezaktywować tego użytkownika?' : 'Czy na pewno chcesz aktywować tego użytkownika?' }}')">
                        @csrf
                        @method('PATCH')
                        @if($user->is_active)
                            <button type="submit" class="btn-kt-warning">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                </svg>
                                Dezaktywuj
                            </button>
                        @else
                            <button type="submit" class="btn-kt-success">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                Aktywuj
                            </button>
                        @endif
                    </form>
                @endcan
                @can('delete', $user)
                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Czy na pewno chcesz usunąć tego użytkownika? Ta operacja jest nieodwracalna.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-kt-danger">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3l1.5 1.5a1 1 0 01-1.414 1.414L10 10.414l-1.086 1.086a1 1 0 01-1.414-1.414L9 8.586V6a1 1 0 011-1z" clip-rule="evenodd"></path>
                                <path fill-rule="evenodd" d="M3 6a1 1 0 011-1h12a1 1 0 110 2h-1v9a2 2 0 01-2 2H7a2 2 0 01-2-2V7H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Usuń
                        </button>
                    </form>
                @endcan
                <a href="{{ route('users.index') }}" class="btn-kt-secondary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- User Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="stats-card">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-600 rounded-lg mr-4">
                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-blue-600 dark:text-blue-400">Wszystkie zadania</div>
                            <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $user->tasks->count() }}</div>
                        </div>
                    </div>
                </div>

                <div class="stats-card-success">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-600 rounded-lg mr-4">
                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-green-600 dark:text-green-400">Ukończone</div>
                            <div class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $user->tasks->where('status', 'completed')->count() }}</div>
                        </div>
                    </div>
                </div>

                <div class="stats-card">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-600 rounded-lg mr-4">
                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-blue-600 dark:text-blue-400">W trakcie</div>
                            <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $user->tasks->where('status', 'in_progress')->count() }}</div>
                        </div>
                    </div>
                </div>

                <div class="stats-card-warning">
                    <div class="flex items-center">
                        <div class="p-3 bg-yellow-600 rounded-lg mr-4">
                            <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Planowane</div>
                            <div class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ $user->tasks->where('status', 'planned')->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Info Card -->
            <div class="kt-card mb-6">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Informacje o użytkowniku</h3>
                </div>
                <div class="kt-card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Imię i nazwisko</dt>
                                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $user->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $user->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Rola</dt>
                                    <dd>
                                        @php
                                            $roleClass = match($user->role) {
                                                'admin' => 'badge-kt-danger',
                                                'kierownik' => 'badge-kt-warning',
                                                'lider' => 'badge-kt-primary',
                                                'pracownik' => 'badge-kt-info',
                                                default => 'badge-kt-light'
                                            };
                                        @endphp
                                        <span class="{{ $roleClass }}">{{ $roleLabels[$user->role] ?? $user->role }}</span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                    <dd>
                                        @if($user->is_active)
                                            <span class="badge-kt-success">Aktywny</span>
                                        @else
                                            <span class="badge-kt-danger">Nieaktywny</span>
                                        @endif
                                    </dd>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Utworzono</dt>
                                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $user->created_at->format('d.m.Y H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ostatnia aktualizacja</dt>
                                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $user->updated_at->format('d.m.Y H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Przypisane zadania</dt>
                                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $user->tasks->count() }} zadań</dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ASEK Zestawy -->
            @if($asekZestawy->count() > 0)
                <div class="kt-card mb-6">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">
                            <svg class="w-5 h-5 inline-block mr-2 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path>
                            </svg>
                            Zestawy asekuracyjne ({{ $asekZestawy->count() }})
                        </h3>
                    </div>
                    <div class="kt-card-body">
                        <div class="overflow-x-auto">
                            <table class="table-kt">
                                <thead>
                                    <tr>
                                        <th>Nazwa zestawu</th>
                                        <th>Opis</th>
                                        <th>Status</th>
                                        <th>Status przeglądu</th>
                                        <th>Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($asekZestawy as $zestaw)
                                        <tr>
                                            <td>
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-indigo-100 dark:bg-indigo-900/20 flex items-center justify-center">
                                                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $zestaw->name }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($zestaw->description)
                                                    <span class="text-gray-600 dark:text-gray-400">{{ Str::limit($zestaw->description, 50) }}</span>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(strtolower($zestaw->status) == 'aktywny')
                                                    <span class="badge-kt-success">Aktywny</span>
                                                @else
                                                    <span class="badge-kt-secondary">{{ $zestaw->status ?: 'Brak' }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($zestaw->status_przegladu)
                                                    <span class="text-gray-900 dark:text-gray-100">{{ $zestaw->status_przegladu }}</span>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('asek.zestawy.show', $zestaw->id) }}" class="btn-kt-sm btn-kt-light">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Szczegóły
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Zakładki: Zadania / Delegacje -->
            @if($user->tasks->count() > 0 || $delegations->count() > 0)
                <div class="kt-card">
                    <!-- Tab Navigation -->
                    <div class="flex border-b border-gray-200 dark:border-gray-700">
                        <button onclick="showTab('tasks')" id="tabBtn-tasks"
                                class="px-6 py-3 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 dark:text-blue-400">
                            Zadania ({{ $user->tasks->count() }})
                        </button>
                        <button onclick="showTab('delegations')" id="tabBtn-delegations"
                                class="px-6 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            Delegacje ({{ $delegations->count() }})
                        </button>
                    </div>

                    <!-- Tab: Zadania -->
                    <div id="tabPanel-tasks" class="kt-card-body">
                        <div class="overflow-x-auto">
                            <table class="table-kt">
                                <thead>
                                    <tr>
                                        <th>Zadanie</th>
                                        <th>Pojazd</th>
                                        <th>Start</th>
                                        <th>Status</th>
                                        <th>Czas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($user->tasks as $task)
                                        <tr>
                                            <td>
                                                <div class="font-medium">{{ $task->title }}</div>
                                                @if($task->description)
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($task->description, 60) }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($task->vehicles->count() > 0)
                                                    <div>{{ $task->vehicles->pluck('name')->join(', ') }}</div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $task->vehicles->pluck('registration')->join(', ') }}</div>
                                                @else
                                                    <div>Brak pojazdu</div>
                                                @endif
                                            </td>
                                            <td>{{ $task->start_date->format('d.m.Y H:i') }}</td>
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
                                            <td>
                                                @if($task->duration_hours)
                                                    {{ $task->duration_hours }}h
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-gray-400 dark:text-gray-500 py-4">
                                                Brak zadań do wyświetlenia
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab: Delegacje -->
                    <div id="tabPanel-delegations" class="kt-card-body hidden">
                        <div class="overflow-x-auto">
                            <table class="table-kt">
                                <thead>
                                    <tr>
                                        <th>NR</th>
                                        <th>Cel podróży</th>
                                        <th>Miejsce</th>
                                        <th>Wyjazd</th>
                                        <th>Pojazd</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($delegations as $delegation)
                                        <tr>
                                            <td>
                                                <a href="{{ route('delegations.show', $delegation) }}"
                                                   class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                                    #{{ $delegation->id }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="text-gray-700 dark:text-gray-300">{{ Str::limit($delegation->travel_purpose, 50) }}</div>
                                            </td>
                                            <td class="text-sm">
                                                {{ $delegation->destination_city }}, {{ $delegation->country }}
                                            </td>
                                            <td class="text-sm">
                                                @if($delegation->departure_date)
                                                    {{ $delegation->departure_date->format('d.m.Y') }}
                                                    @if($delegation->departure_time)
                                                        <br><small class="text-gray-400 dark:text-gray-500">{{ $delegation->departure_time }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500 italic text-xs">Brak</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($delegation->vehicle_registration)
                                                    @php
                                                        $vehicles = array_filter(array_map('trim', explode(',', $delegation->vehicle_registration)));
                                                    @endphp
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($vehicles as $vehicle)
                                                            <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                                {{ $vehicle }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-500 italic text-xs">Brak</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $statusConfig = match($delegation->delegation_status) {
                                                        'draft' => ['label' => 'Szkic', 'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'],
                                                        'employee_approved' => ['label' => 'Zaakceptowana', 'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'],
                                                        'approved' => ['label' => 'Zatwierdzona', 'class' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'],
                                                        'completed' => ['label' => 'Zakończona', 'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'],
                                                        'cancelled' => ['label' => 'Anulowana', 'class' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'],
                                                        default => ['label' => $delegation->delegation_status ?? 'Brak', 'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'],
                                                    };
                                                @endphp
                                                <div class="text-xs font-medium px-2 py-1 rounded {{ $statusConfig['class'] }} inline-block">
                                                    {{ $statusConfig['label'] }}
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-gray-400 dark:text-gray-500 py-4">
                                                Brak delegacji do wyświetlenia
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <script>
                function showTab(tab) {
                    document.querySelectorAll('[id^="tabPanel-"]').forEach(function(el) {
                        el.classList.add('hidden');
                    });
                    document.getElementById('tabPanel-' + tab).classList.remove('hidden');

                    document.querySelectorAll('[id^="tabBtn-"]').forEach(function(btn) {
                        btn.className = 'px-6 py-3 text-sm font-semibold border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300';
                    });
                    document.getElementById('tabBtn-' + tab).className = 'px-6 py-3 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 dark:text-blue-400';
                }
            </script>
        </div>
    </div>
</x-app-layout>
