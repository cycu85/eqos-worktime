<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Finanse
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Przegląd wartości zadań
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Formularz filtrowania -->
            <div class="kt-card mb-6">
                <div class="kt-card-body">
                    <form method="GET" action="{{ route('finanse.index') }}">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="date_from" class="form-kt-label">Od daty</label>
                                <input type="date" class="form-kt-control" id="date_from" name="date_from" value="{{ $dateFrom }}">
                            </div>
                            <div>
                                <label for="date_to" class="form-kt-label">Do daty</label>
                                <input type="date" class="form-kt-control" id="date_to" name="date_to" value="{{ $dateTo }}">
                            </div>
                            <div>
                                <label for="task_type_id" class="form-kt-label">Rodzaj zadania</label>
                                <select class="form-kt-select" id="task_type_id" name="task_type_id">
                                    <option value="">Wszystkie</option>
                                    @foreach($taskTypes as $type)
                                        <option value="{{ $type->id }}" @selected($taskTypeId == $type->id)>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="team_id" class="form-kt-label">Zespół</label>
                                <select class="form-kt-select" id="team_id" name="team_id">
                                    <option value="">Wszystkie</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->id }}" @selected($teamId == $team->id)>{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center space-x-3">
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-.293.707L13 8.414V15a1 1 0 01-.553.894l-4 2A1 1 0 017 17v-6.586L3.293 5.707A1 1 0 013 5V3z" clip-rule="evenodd"></path>
                                </svg>
                                Filtruj
                            </button>
                            <a href="{{ route('finanse.index') }}" class="btn-kt-secondary">Wyczyść</a>
                            <a href="{{ route('finanse.export', request()->query()) }}" class="btn-kt-secondary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                Export do Excel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Karty podsumowania -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="kt-card">
                    <div class="kt-card-body">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33M2.53 17c.046-.327.07-.66.07-1C2.6 13.2 5.33 11 8.6 11s6 2.2 6 5c0 .34.024.673.07 1"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Łączna ilość zadań</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($grandTotalCount) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="kt-card">
                    <div class="kt-card-body">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm2 6a2 2 0 114 0 2 2 0 01-4 0zM2 8a2 2 0 012-2h12a2 2 0 012 2v.01A2.01 2.01 0 0116 8v.01A1.99 1.99 0 0114 6.01V6H6v.01A1.99 1.99 0 014 8.01V8z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Łączna wartość</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($grandTotalValue, 2, ',', ' ') }} zł</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabelka danych -->
            <div class="kt-card">
                <div class="kt-card-body">
                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Zespół</th>
                                    <th class="text-right">Ilość zadań</th>
                                    <th>Rodzaj zadania</th>
                                    <th class="text-right">Wartość za szt. (zł)</th>
                                    <th class="text-right">Suma (zł)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                <tr>
                                    <td>
                                        {{ \Carbon\Carbon::parse($row->work_date)->format('d.m.Y') }}
                                    </td>
                                    <td>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $row->team_name }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ number_format($row->total_count) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-gray-600 dark:text-gray-400">{{ $row->task_type_name }}</span>
                                    </td>
                                    <td class="text-right">
                                        {{ number_format($row->unit_value, 2, ',', ' ') }}
                                    </td>
                                    <td class="text-right">
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($row->total_value, 2, ',', ' ') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-8">
                                        Brak danych finansowych dla wybranych parametrów.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($data->count() > 0)
                            <tfoot>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <td colspan="2" class="font-bold text-gray-900 dark:text-gray-100">Razem</td>
                                    <td class="text-right font-bold text-gray-900 dark:text-gray-100">{{ number_format($grandTotalCount) }}</td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-right font-bold text-green-600 dark:text-green-400">{{ number_format($grandTotalValue, 2, ',', ' ') }}</td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
