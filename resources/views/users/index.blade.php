<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    {{ __('Użytkownicy') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Zarządzanie kontami użytkowników (Admin tylko)
                </p>
            </div>
            
            @can('create', App\Models\User::class)
                <div class="mt-3 sm:mt-0">
                    <a href="{{ route('users.create') }}" class="btn-kt-primary">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        Dodaj użytkownika
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

            @if(session('error'))
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Search and Filter Card -->
            <div class="kt-card mb-6">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Wyszukiwanie i filtrowanie</h3>
                </div>
                <div class="kt-card-body">
                    <form method="GET" action="{{ route('users.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div>
                                <label class="form-kt-label">Wyszukaj</label>
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       class="form-kt-control" 
                                       placeholder="Imię, nazwisko, email...">
                            </div>

                            <!-- Role Filter -->
                            <div>
                                <label class="form-kt-label">Rola</label>
                                <select name="role" class="form-kt-select">
                                    <option value="">Wszystkie role</option>
                                    @foreach($roles as $key => $label)
                                        <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Sort -->
                            <div>
                                <label class="form-kt-label">Sortuj według</label>
                                <select name="sort" class="form-kt-select">
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>
                                        Nazwa
                                    </option>
                                    <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>
                                        Email
                                    </option>
                                    <option value="role" {{ request('sort') == 'role' ? 'selected' : '' }}>
                                        Rola
                                    </option>
                                    <option value="tasks_count" {{ request('sort') == 'tasks_count' ? 'selected' : '' }}>
                                        Liczba zadań
                                    </option>
                                    <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>
                                        Data utworzenia
                                    </option>
                                </select>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-end space-x-2">
                                <button type="submit" class="btn-kt-primary">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                    </svg>
                                    Szukaj
                                </button>
                                
                                <input type="hidden" name="order" value="{{ request('order', 'asc') }}">
                                
                                <button type="button" onclick="toggleSortOrder()" class="btn-kt-secondary">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 3a1 1 0 000 2h11a1 1 0 100-2H3zM3 7a1 1 0 000 2h5a1 1 0 000-2H3zM3 11a1 1 0 100 2h4a1 1 0 100-2H3zM13 16a1 1 0 102 0v-5.586l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 101.414 1.414L13 10.414V16z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ request('order') == 'desc' ? 'Malejąco' : 'Rosnąco' }}
                                </button>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('users.index') }}" class="btn-kt-light">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                </svg>
                                Wyczyść filtry
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="kt-card">
                @if($users->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>Użytkownik</th>
                                    <th>Email</th>
                                    <th>Rola</th>
                                    <th>Zadania</th>
                                    <th>Utworzono</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <a href="{{ route('users.show', $user) }}" class="flex items-center hover:bg-gray-50 dark:hover:bg-gray-700/50 -mx-4 px-4 py-2 rounded transition-colors">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                                                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                                        {{ $user->name }}
                                                        @if($user->id === auth()->id())
                                                            <span class="text-xs text-blue-600 dark:text-blue-400">(Ty)</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="text-gray-900 dark:text-gray-100">{{ $user->email }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $roleClass = match($user->role) {
                                                    'admin' => 'badge-kt-danger',
                                                    'kierownik' => 'badge-kt-warning',
                                                    'lider' => 'badge-kt-primary',
                                                    'pracownik' => 'badge-kt-info',
                                                    default => 'badge-kt-light'
                                                };
                                                $roleLabels = [
                                                    'admin' => 'Administrator',
                                                    'kierownik' => 'Kierownik',
                                                    'lider' => 'Lider',
                                                    'pracownik' => 'Pracownik'
                                                ];
                                            @endphp
                                            <span class="{{ $roleClass }}">
                                                {{ $roleLabels[$user->role] ?? $user->role }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($user->tasks_count > 0)
                                                <span class="badge-kt-info">
                                                    {{ $user->tasks_count }} {{ Str::plural('zadanie', $user->tasks_count) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">Brak zadań</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $user->created_at->format('d.m.Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="kt-card-footer">
                        {{ $users->links() }}
                    </div>
                @else
                    <div class="kt-card-body text-center">
                        <div class="text-gray-500 dark:text-gray-400 mb-4">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Brak użytkowników</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">W systemie nie ma jeszcze żadnych użytkowników.</p>
                        @can('create', App\Models\User::class)
                            <a href="{{ route('users.create') }}" class="btn-kt-primary">
                                Dodaj pierwszego użytkownika
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