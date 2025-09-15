<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    {{ __('Dashboard') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Witaj, {{ auth()->user()->name }}
                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 ml-2">
                        {{ ucfirst(auth()->user()->role) }}
                    </span>
                </p>
            </div>
            <div class="mt-3 sm:mt-0">
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ now()->format('d.m.Y H:i') }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Stats Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
                @if(auth()->user()->isLider())
                    <!-- Lider Stats -->
                    <div class="stats-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center mb-2">
                                    <div class="p-2 bg-blue-600 rounded-lg mr-3">
                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-medium text-blue-600 dark:text-blue-400">Aktywne zadania</h3>
                                </div>
                                <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">
                                    {{ auth()->user()->allAccessibleTasks()->active()->count() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card-success">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center mb-2">
                                    <div class="p-2 bg-green-600 rounded-lg mr-3">
                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-medium text-green-600 dark:text-green-400">Ukończone zadania</h3>
                                </div>
                                <p class="text-3xl font-bold text-green-900 dark:text-green-100">
                                    {{ auth()->user()->allAccessibleTasks()->completed()->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Manager/Admin Stats -->
                    <div class="stats-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center mb-2">
                                    <div class="p-2 bg-blue-600 rounded-lg mr-3">
                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-medium text-blue-600 dark:text-blue-400">Wszystkie aktywne zadania</h3>
                                </div>
                                <p class="text-3xl font-bold text-blue-900 dark:text-blue-100">
                                    {{ \App\Models\Task::active()->count() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card-success">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center mb-2">
                                    <div class="p-2 bg-green-600 rounded-lg mr-3">
                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-medium text-green-600 dark:text-green-400">Ukończone zadania</h3>
                                </div>
                                <p class="text-3xl font-bold text-green-900 dark:text-green-100">
                                    {{ \App\Models\Task::completed()->count() }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Quick Actions Card -->
            <div class="kt-card mb-6 sm:mb-8">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Szybkie akcje</h3>
                </div>
                <div class="kt-card-body">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <a href="{{ route('tasks.index') }}" class="flex items-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-200 group">
                            <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg mr-4 group-hover:bg-blue-200 dark:group-hover:bg-blue-800 transition duration-200">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100">Zadania</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Zarządzaj zadaniami</p>
                            </div>
                        </a>

                        <a href="{{ route('delegations.index') }}" class="flex items-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-200 group">
                            <div class="p-3 bg-amber-100 dark:bg-amber-900 rounded-lg mr-4 group-hover:bg-amber-200 dark:group-hover:bg-amber-800 transition duration-200">
                                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100">Delegacje</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Zarządzaj delegacjami</p>
                            </div>
                        </a>

                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('vehicles.index') }}" class="flex items-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-200 group">
                                <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg mr-4 group-hover:bg-purple-200 dark:group-hover:bg-purple-800 transition duration-200">
                                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">Pojazdy</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Zarządzaj flotą</p>
                                </div>
                            </a>

                            <a href="{{ route('users.index') }}" class="flex items-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-200 group">
                                <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-lg mr-4 group-hover:bg-orange-200 dark:group-hover:bg-orange-800 transition duration-200">
                                    <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">Użytkownicy</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Zarządzaj kontami</p>
                                </div>
                            </a>

                            <a href="{{ route('teams.index') }}" class="flex items-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-200 group">
                                <div class="p-3 bg-teal-100 dark:bg-teal-900 rounded-lg mr-4 group-hover:bg-teal-200 dark:group-hover:bg-teal-800 transition duration-200">
                                    <svg class="w-6 h-6 text-teal-600 dark:text-teal-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">Zespoły</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Zarządzaj zespołami</p>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Activity Card (if user has tasks) -->
            @if(auth()->user()->isLider() && auth()->user()->allAccessibleTasks()->count() > 0)
                <div class="kt-card">
                    <div class="kt-card-header">
                        <h3 class="kt-card-title">Ostatnie zadania</h3>
                    </div>
                    <div class="kt-card-body">
                        <div class="space-y-4">
                            @foreach(auth()->user()->allAccessibleTasks()->with('vehicles')->limit(5)->get() as $task)
                                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="p-2 rounded-lg mr-3
                                            @if($task->status === 'completed') bg-green-100 dark:bg-green-900
                                            @elseif($task->status === 'in_progress') bg-blue-100 dark:bg-blue-900
                                            @elseif($task->status === 'cancelled') bg-red-100 dark:bg-red-900
                                            @else bg-yellow-100 dark:bg-yellow-900 @endif">
                                            <svg class="w-4 h-4
                                                @if($task->status === 'completed') text-green-600 dark:text-green-400
                                                @elseif($task->status === 'in_progress') text-blue-600 dark:text-blue-400
                                                @elseif($task->status === 'cancelled') text-red-600 dark:text-red-400
                                                @else text-yellow-600 dark:text-yellow-400 @endif" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $task->title }}</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                @if($task->vehicles->count() > 0)
                                                    {{ $task->vehicles->pluck('name')->join(', ') }} • 
                                                @endif
                                                {{ $task->start_date->format('d.m.Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="badge-kt-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'primary' : ($task->status === 'cancelled' ? 'danger' : 'warning')) }}">
                                        @if($task->status === 'completed') Ukończone
                                        @elseif($task->status === 'in_progress') W trakcie
                                        @elseif($task->status === 'cancelled') Anulowane
                                        @else Planowane @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>