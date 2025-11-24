<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    {{ __('Zestawy ASEK') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Lista zestawów narzędzi i sprzętu
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Filters -->
            <div class="kt-card mb-6">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Filtrowanie i sortowanie</h3>
                </div>
                <div class="kt-card-body">
                    <form method="GET" action="{{ route('asek.zestawy.index') }}" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
                        <!-- Search -->
                        <div class="flex-1">
                            <label for="search" class="form-kt-label">Szukaj</label>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   class="form-kt-control"
                                   value="{{ request('search') }}"
                                   placeholder="Nazwa, opis lub użytkownik...">
                        </div>

                        <!-- Status Filter -->
                        <div class="sm:w-48">
                            <label for="status" class="form-kt-label">Status</label>
                            <select id="status" name="status" class="form-kt-select">
                                <option value="">Wszystkie</option>
                                <option value="aktywny" {{ request('status') == 'aktywny' ? 'selected' : '' }}>Aktywny</option>
                                <option value="nieaktywny" {{ request('status') == 'nieaktywny' ? 'selected' : '' }}>Nieaktywny</option>
                            </select>
                        </div>

                        <!-- Filter Buttons -->
                        <div class="flex space-x-2">
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                </svg>
                                Filtruj
                            </button>
                            <a href="{{ route('asek.zestawy.index') }}" class="btn-kt-light">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                </svg>
                                Resetuj
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Info -->
            @if(request()->hasAny(['search', 'status', 'sort']))
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4 text-sm">
                            <span class="text-blue-700 dark:text-blue-300 font-medium">
                                Znaleziono: {{ $zestawy->total() }} {{ trans_choice('zestaw|zestawy|zestawów', $zestawy->total()) }}
                            </span>
                            @if(request('search'))
                                <span class="text-blue-600 dark:text-blue-400">
                                    Szukano: "{{ request('search') }}"
                                </span>
                            @endif
                            @if(request('status'))
                                <span class="text-blue-600 dark:text-blue-400">
                                    Status: {{ request('status') }}
                                </span>
                            @endif
                        </div>
                        <a href="{{ route('asek.zestawy.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                            Wyczyść filtry
                        </a>
                    </div>
                </div>
            @endif

            <div class="kt-card">
                @if($zestawy->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center space-x-1 hover:text-blue-600 dark:hover:text-blue-400">
                                            <span>ID</span>
                                            @if(request('sort') == 'id')
                                                @if(request('direction', 'asc') == 'asc')
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort', 'name') == 'name' && request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center space-x-1 hover:text-blue-600 dark:hover:text-blue-400">
                                            <span>Nazwa</span>
                                            @if(request('sort', 'name') == 'name')
                                                @if(request('direction', 'asc') == 'asc')
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'who_use', 'direction' => request('sort') == 'who_use' && request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center space-x-1 hover:text-blue-600 dark:hover:text-blue-400">
                                            <span>Użytkownik</span>
                                            @if(request('sort') == 'who_use')
                                                @if(request('direction', 'asc') == 'asc')
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
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
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'date_mod', 'direction' => request('sort') == 'date_mod' && request('direction', 'asc') == 'asc' ? 'desc' : 'asc']) }}"
                                           class="flex items-center space-x-1 hover:text-blue-600 dark:hover:text-blue-400">
                                            <span>Modyfikacja</span>
                                            @if(request('sort') == 'date_mod')
                                                @if(request('direction', 'asc') == 'asc')
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($zestawy as $zestaw)
                                    <tr>
                                        <td>
                                            <span class="font-mono text-sm text-gray-500 dark:text-gray-400">{{ $zestaw->id }}</span>
                                        </td>
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
                                                    @if($zestaw->description)
                                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($zestaw->description, 50) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($zestaw->who_use)
                                                <span class="text-gray-900 dark:text-gray-100">{{ $zestaw->who_use }}</span>
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
                                            @if($zestaw->date_mod)
                                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $zestaw->date_mod->format('d.m.Y H:i') }}</span>
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

                    <div class="kt-card-footer">
                        {{ $zestawy->links() }}
                    </div>
                @else
                    <div class="kt-card-body text-center">
                        <div class="text-gray-500 dark:text-gray-400 mb-4">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Brak zestawów</h3>
                        <p class="text-gray-500 dark:text-gray-400">Nie znaleziono żadnych zestawów spełniających kryteria.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
