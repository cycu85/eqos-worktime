<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    {{ $team->name }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Szczegóły zespołu
                </p>
            </div>
            
            <div class="mt-3 sm:mt-0 flex space-x-2">
                @can('update', $team)
                    <a href="{{ route('teams.edit', $team) }}" class="btn-kt-secondary">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                        Edytuj
                    </a>
                @endcan
                
                <a href="{{ route('teams.index') }}" class="btn-kt-light">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót do listy
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Team Information -->
            <div class="kt-card mb-6">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Informacje podstawowe</h3>
                    <span class="badge-kt-{{ $team->active ? 'success' : 'danger' }}">
                        {{ $team->active ? 'Aktywny' : 'Nieaktywny' }}
                    </span>
                </div>
                <div class="kt-card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Nazwa zespołu</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $team->name }}</p>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Lider zespołu</h4>
                            @if($team->leader)
                                <p class="text-gray-600 dark:text-gray-400">{{ $team->leader->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $team->leader->email }}</p>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">Brak przypisanego lidera</p>
                            @endif
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Przypisany pojazd</h4>
                            @if($team->vehicle)
                                <p class="text-gray-600 dark:text-gray-400">{{ $team->vehicle->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $team->vehicle->registration }}</p>
                            @else
                                <p class="text-gray-500 dark:text-gray-400">Brak przypisanego pojazdu</p>
                            @endif
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Utworzył</h4>
                            <p class="text-gray-600 dark:text-gray-400">{{ $team->creator->name }} • {{ $team->created_at->format('d.m.Y H:i') }}</p>
                        </div>
                        
                        @if($team->description)
                            <div class="md:col-span-2">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Opis</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ $team->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Team Members -->
            <div class="kt-card mb-6">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Członkowie zespołu</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ count($team->members) }} {{ Str::plural('członek', count($team->members)) }}
                    </span>
                </div>
                <div class="kt-card-body">
                    @if(count($team->members) > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @php
                                $members = \App\Models\User::whereIn('id', $team->members)->get();
                            @endphp
                            @foreach($members as $member)
                                <div class="flex items-center p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $member->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            @php
                                                $roleLabels = [
                                                    'admin' => 'Administrator',
                                                    'kierownik' => 'Kierownik',
                                                    'lider' => 'Lider',
                                                    'pracownik' => 'Pracownik'
                                                ];
                                            @endphp
                                            {{ $roleLabels[$member->role] ?? $member->role }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Brak członków</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ten zespół nie ma jeszcze żadnych członków.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Team Tasks -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Zadania zespołu</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $team->tasks->count() }} {{ Str::plural('zadanie', $team->tasks->count()) }}
                    </span>
                </div>
                <div class="kt-card-body">
                    @if($team->tasks->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="table-kt">
                                <thead>
                                    <tr>
                                        <th>Zadanie</th>
                                        <th>Pojazd</th>
                                        <th>Status</th>
                                        <th>Start</th>
                                        <th class="text-right">Akcje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($team->tasks->take(10) as $task)
                                        <tr>
                                            <td>
                                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $task->title }}</div>
                                                @if($task->description)
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($task->description, 50) }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="text-gray-900 dark:text-gray-100">{{ $task->vehicle->name }}</div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $task->vehicle->registration }}</div>
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
                                            <td>{{ $task->start_datetime->format('d.m.Y H:i') }}</td>
                                            <td class="text-right">
                                                @can('view', $task)
                                                    <a href="{{ route('tasks.show', $task) }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 text-sm font-medium">Zobacz</a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($team->tasks->count() > 10)
                            <div class="mt-4 text-center">
                                <a href="{{ route('tasks.index', ['search' => $team->name]) }}" class="btn-kt-light">
                                    Zobacz wszystkie zadania zespołu
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Brak zadań</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ten zespół nie ma jeszcze przypisanych żadnych zadań.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>