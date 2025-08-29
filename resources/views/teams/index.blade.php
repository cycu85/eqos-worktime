<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    {{ __('Zespoły') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Zarządzanie zespołami (Admin tylko)
                </p>
            </div>
            
            @can('create', App\Models\Team::class)
                <div class="mt-3 sm:mt-0">
                    <a href="{{ route('teams.create') }}" class="btn-kt-primary">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        Dodaj zespół
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
                    <form method="GET" action="{{ route('teams.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Search -->
                            <div>
                                <label class="form-kt-label">Wyszukaj</label>
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       class="form-kt-control" 
                                       placeholder="Nazwa zespołu, opis...">
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label class="form-kt-label">Status</label>
                                <select name="status" class="form-kt-select">
                                    <option value="">Wszystkie zespoły</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                                        Aktywne
                                    </option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                                        Nieaktywne
                                    </option>
                                </select>
                            </div>

                            <!-- Sort -->
                            <div>
                                <label class="form-kt-label">Sortuj według</label>
                                <select name="sort" class="form-kt-select">
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>
                                        Nazwa
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
                            
                            <input type="hidden" name="order" value="{{ request('order', 'asc') }}">
                            
                            <a href="{{ route('teams.index') }}" class="btn-kt-light">
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
                @if($teams->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>Zespół</th>
                                    <th>Lider</th>
                                    <th>Członkowie</th>
                                    <th>Utworzono</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teams as $team)
                                    <tr>
                                        <td>
                                            <a href="{{ route('teams.show', $team) }}" class="block hover:bg-gray-50 dark:hover:bg-gray-800/50 -m-3 p-3 rounded-lg transition-colors duration-150">
                                                <div class="font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400">
                                                    {{ $team->name }}
                                                </div>
                                                @if($team->description)
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ Str::limit($team->description, 50) }}
                                                    </div>
                                                @endif
                                            </a>
                                        </td>
                                        <td>
                                            @if($team->leader)
                                                <div class="text-gray-900 dark:text-gray-100">
                                                    {{ $team->leader->name }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $team->leader->email }}
                                                </div>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">Brak lidera</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-gray-900 dark:text-gray-100">
                                                {{ $team->members_names }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ count($team->members ?? []) }} członków
                                            </div>
                                        </td>
                                        <td>
                                            {{ $team->created_at->format('d.m.Y') }}
                                        </td>
                                        <td>
                                            <span class="badge-kt-{{ $team->active ? 'success' : 'danger' }}">
                                                {{ $team->active ? 'Aktywny' : 'Nieaktywny' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="kt-card-footer">
                        {{ $teams->links() }}
                    </div>
                @else
                    <div class="kt-card-body text-center">
                        <div class="text-gray-500 dark:text-gray-400 mb-4">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Brak zespołów</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">W systemie nie ma jeszcze żadnych zespołów.</p>
                        @can('create', App\Models\Team::class)
                            <a href="{{ route('teams.create') }}" class="btn-kt-primary">
                                Dodaj pierwszy zespół
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