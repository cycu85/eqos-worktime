<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Obecności
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Lista obecności pracowników na podstawie zadań
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Filtry -->
            <div class="kt-card mb-6">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Filtrowanie</h3>
                </div>
                <div class="kt-card-body">
                    <form method="GET" action="{{ route('attendance.index') }}" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4 flex-wrap gap-y-4">
                        <input type="hidden" name="sort" value="{{ $sort }}">
                        <input type="hidden" name="direction" value="{{ $direction }}">

                        <div>
                            <label for="date_from" class="form-kt-label">Od daty</label>
                            <input type="date" id="date_from" name="date_from"
                                   class="form-kt-control"
                                   value="{{ $dateFrom }}">
                        </div>

                        <div>
                            <label for="date_to" class="form-kt-label">Do daty</label>
                            <input type="date" id="date_to" name="date_to"
                                   class="form-kt-control"
                                   value="{{ $dateTo }}">
                        </div>

                        <div class="sm:w-56">
                            <label for="user_id" class="form-kt-label">Pracownik</label>
                            <select id="user_id" name="user_id" class="form-kt-select">
                                <option value="">Wszyscy</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected($userId == $user->id)>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex space-x-2">
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-.293.707L13 8.414V15a1 1 0 01-.553.894l-4 2A1 1 0 017 17v-6.586L3.293 5.707A1 1 0 013 5V3z" clip-rule="evenodd"/>
                                </svg>
                                Filtruj
                            </button>
                            <a href="{{ route('attendance.index') }}" class="btn-kt-secondary">
                                Wyczyść
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Podsumowanie -->
            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                <span class="text-blue-700 dark:text-blue-300 font-medium text-sm">
                    Znaleziono {{ $total }} {{ $total === 1 ? 'rekord' : ($total < 5 ? 'rekordy' : 'rekordów') }} obecności
                </span>
            </div>

            <!-- Tabela -->
            <div class="kt-card">
                <div class="kt-card-body">
                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>
                                        @php
                                            $nameDir = ($sort === 'name' && $direction === 'asc') ? 'desc' : 'asc';
                                        @endphp
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => $nameDir, 'page' => 1]) }}"
                                           class="flex items-center space-x-1 hover:text-blue-600 dark:hover:text-blue-400">
                                            <span>Imię i Nazwisko</span>
                                            @if($sort === 'name')
                                                @if($direction === 'asc')
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th>
                                        @php
                                            $dateDir = ($sort === 'date' && $direction === 'asc') ? 'desc' : 'asc';
                                        @endphp
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'date', 'direction' => $dateDir, 'page' => 1]) }}"
                                           class="flex items-center space-x-1 hover:text-blue-600 dark:hover:text-blue-400">
                                            <span>Data</span>
                                            @if($sort === 'date' || $sort === '')
                                                @if($direction === 'asc')
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paginator as $row)
                                    <tr>
                                        <td class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $row['user_name'] }}
                                        </td>
                                        <td class="text-gray-600 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($row['work_date'])->format('d.m.Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-gray-500 dark:text-gray-400 py-8">
                                            Brak rekordów obecności dla wybranych parametrów.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($paginator->hasPages())
                        <div class="mt-4">
                            {{ $paginator->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
