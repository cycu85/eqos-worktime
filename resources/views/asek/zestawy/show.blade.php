<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center space-x-2 mb-1">
                    <a href="{{ route('asek.zestawy.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </a>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {{ $zestaw->name }}
                    </h2>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Zestaw #{{ $zestaw->id }} &bull; {{ $tickets->count() }} elementów
                </p>
            </div>
            <div class="mt-3 sm:mt-0">
                <a href="{{ route('asek.zestawy.index') }}" class="btn-kt-light">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót do listy
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Zestaw Info Card -->
            <div class="kt-card mb-6">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Informacje o zestawie</h3>
                </div>
                <div class="kt-card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label class="form-kt-label">Nazwa</label>
                            <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $zestaw->name }}</p>
                        </div>
                        <div>
                            <label class="form-kt-label">Użytkownik</label>
                            <p class="text-gray-900 dark:text-gray-100">{{ $zestaw->who_use ?: '-' }}</p>
                        </div>
                        <div>
                            <label class="form-kt-label">Status</label>
                            <p>
                                @if(strtolower($zestaw->status) == 'aktywny')
                                    <span class="badge-kt-success">Aktywny</span>
                                @else
                                    <span class="badge-kt-secondary">{{ $zestaw->status ?: 'Brak' }}</span>
                                @endif
                            </p>
                        </div>
                        @if($zestaw->description)
                            <div class="md:col-span-2 lg:col-span-3">
                                <label class="form-kt-label">Opis</label>
                                <p class="text-gray-900 dark:text-gray-100">{{ $zestaw->description }}</p>
                            </div>
                        @endif
                        <div>
                            <label class="form-kt-label">Status przeglądu</label>
                            <p class="text-gray-900 dark:text-gray-100">{{ $zestaw->status_przegladu ?: '-' }}</p>
                        </div>
                        <div>
                            <label class="form-kt-label">Ostatnia modyfikacja</label>
                            <p class="text-gray-900 dark:text-gray-100">
                                @if($zestaw->date_mod)
                                    {{ $zestaw->date_mod->format('d.m.Y H:i') }}
                                    @if($zestaw->who_mod)
                                        <span class="text-gray-500 dark:text-gray-400">({{ $zestaw->who_mod }})</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets List -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Elementy zestawu ({{ $tickets->count() }})</h3>
                </div>

                @if($tickets->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>Nazwa</th>
                                    <th>Typ</th>
                                    <th>Producent / Model</th>
                                    <th>Numer seryjny</th>
                                    <th>Kalibracja</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tickets as $ticket)
                                    <tr>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center">
                                                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $ticket->name }}</div>
                                                    @if($ticket->description)
                                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($ticket->description, 40) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-gray-900 dark:text-gray-100">{{ $ticket->type ?: '-' }}</span>
                                        </td>
                                        <td>
                                            <div class="text-gray-900 dark:text-gray-100">{{ $ticket->producent ?: '-' }}</div>
                                            @if($ticket->model)
                                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $ticket->model }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ticket->sn_nr)
                                                <span class="font-mono text-sm bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $ticket->sn_nr }}</span>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($ticket->date_next_calib)
                                                @if($ticket->calibration_overdue)
                                                    <span class="badge-kt-danger">
                                                        Przeterminowana: {{ $ticket->date_next_calib->format('d.m.Y') }}
                                                    </span>
                                                @elseif($ticket->requires_calibration)
                                                    <span class="badge-kt-warning">
                                                        {{ $ticket->date_next_calib->format('d.m.Y') }}
                                                    </span>
                                                @else
                                                    <span class="badge-kt-success">
                                                        {{ $ticket->date_next_calib->format('d.m.Y') }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(strtolower($ticket->status) == 'aktywny')
                                                <span class="badge-kt-success">Aktywny</span>
                                            @elseif(strtolower($ticket->status) == 'nieaktywny')
                                                <span class="badge-kt-danger">Nieaktywny</span>
                                            @else
                                                <span class="badge-kt-secondary">{{ $ticket->status ?: '-' }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="kt-card-body text-center">
                        <div class="text-gray-500 dark:text-gray-400 mb-4">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Brak elementów</h3>
                        <p class="text-gray-500 dark:text-gray-400">Ten zestaw nie zawiera żadnych elementów.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
