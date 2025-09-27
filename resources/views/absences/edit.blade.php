<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Edytuj nieobecność
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $absence->user->name }} - {{ $absence->start_date->format('d.m.Y') }} do {{ $absence->end_date->format('d.m.Y') }}
                </p>
            </div>
            <a href="{{ route('absences.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                </svg>
                Powrót do listy
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">Wystąpiły błędy:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Informacja o statusie -->
            @if($absence->status !== 'oczekujaca')
                <div class="mb-6 bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Nieobecność została {{ $absence->status === 'zatwierdzona' ? 'zatwierdzona' : 'odrzucona' }}
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <p>
                                    @if($absence->status === 'zatwierdzona')
                                        Zatwierdzona przez {{ $absence->approver->name }} dnia {{ $absence->approved_at->format('d.m.Y H:i') }}
                                    @else
                                        Odrzucona przez {{ $absence->approver->name }} dnia {{ $absence->approved_at->format('d.m.Y H:i') }}
                                    @endif
                                </p>
                                @if(!auth()->user()->isAdmin() && !auth()->user()->isKierownik())
                                    <p class="mt-1">Możliwość edycji może być ograniczona.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">
                    <form method="POST" action="{{ route('absences.update', $absence) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Użytkownik -->
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Użytkownik <span class="text-red-500">*</span>
                            </label>
                            @if($users->count() > 1 && (auth()->user()->isAdmin() || auth()->user()->isKierownik()))
                                <select name="user_id" id="user_id" class="form-kt-select" required>
                                    <option value="">Wybierz użytkownika</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id', $absence->user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->role }})
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="hidden" name="user_id" value="{{ $absence->user_id }}">
                                <div class="form-kt-control bg-gray-100 dark:bg-gray-700">
                                    {{ $absence->user->name }} ({{ $absence->user->role }})
                                </div>
                            @endif
                            @error('user_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Daty -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Data rozpoczęcia -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Data rozpoczęcia <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $absence->start_date->format('Y-m-d')) }}" class="form-kt-control" required>
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Data zakończenia -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Data zakończenia <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $absence->end_date->format('Y-m-d')) }}" class="form-kt-control" required>
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Typ nieobecności -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Typ nieobecności <span class="text-red-500">*</span>
                            </label>
                            <select name="type" id="type" class="form-kt-select" required>
                                <option value="">Wybierz typ nieobecności</option>
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}" {{ old('type', $absence->type) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Opis -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Opis/Powód nieobecności
                            </label>
                            <textarea name="description" id="description" rows="4" class="form-kt-control" placeholder="Opcjonalny opis lub powód nieobecności">{{ old('description', $absence->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Maksymalnie 1000 znaków</p>
                        </div>

                        <!-- Informacje o statusie -->
                        <div class="bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md p-4">
                            <h3 class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-2">Informacje o statusie</h3>
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <p><strong>Aktualny status:</strong>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($absence->status === 'oczekujaca') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @elseif($absence->status === 'zatwierdzona') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                        @if($absence->status === 'oczekujaca') Oczekująca
                                        @elseif($absence->status === 'zatwierdzona') Zatwierdzona
                                        @else Odrzucona @endif
                                    </span>
                                </p>
                                @if($absence->approver)
                                    <p class="mt-1"><strong>Zatwierdziła:</strong> {{ $absence->approver->name }}</p>
                                    <p><strong>Data zatwierdzenia:</strong> {{ $absence->approved_at->format('d.m.Y H:i') }}</p>
                                @endif
                                <p class="mt-1"><strong>Liczba dni:</strong> {{ $absence->getDaysCount() }}</p>
                                <p><strong>Utworzone:</strong> {{ $absence->created_at->format('d.m.Y H:i') }}</p>
                                <p><strong>Ostatnia modyfikacja:</strong> {{ $absence->updated_at->format('d.m.Y H:i') }}</p>
                            </div>
                        </div>

                        <!-- Przyciski akcji -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('absences.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Anuluj
                            </a>

                            @can('delete', $absence)
                                <button type="button" onclick="if(confirm('Czy na pewno chcesz usunąć tę nieobecność?')) { document.getElementById('delete-form').submit(); }" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414L8.586 12l-2.293 2.293a1 1 0 101.414 1.414L10 13.414l2.293 2.293a1 1 0 001.414-1.414L11.414 12l2.293-2.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    Usuń
                                </button>
                            @endcan

                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293z"></path>
                                </svg>
                                Zapisz zmiany
                            </button>
                        </div>
                    </form>

                    @can('delete', $absence)
                        <form id="delete-form" method="POST" action="{{ route('absences.destroy', $absence) }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <script>
        // Walidacja dat po stronie klienta
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = this.value;
            const endDateInput = document.getElementById('end_date');

            // Ustaw minimalną datę zakończenia na datę rozpoczęcia
            endDateInput.min = startDate;

            // Sprawdź czy data zakończenia nie jest wcześniejsza
            if (endDateInput.value && endDateInput.value < startDate) {
                endDateInput.value = startDate;
            }
        });

        document.getElementById('end_date').addEventListener('change', function() {
            const startDate = document.getElementById('start_date').value;
            const endDate = this.value;

            if (startDate && endDate && endDate < startDate) {
                alert('Data zakończenia nie może być wcześniejsza niż data rozpoczęcia');
                this.value = startDate;
            }
        });

        // Ustaw początkową minimalną datę
        document.addEventListener('DOMContentLoaded', function() {
            const startDate = document.getElementById('start_date').value;
            if (startDate) {
                document.getElementById('end_date').min = startDate;
            }
        });
    </script>
</x-app-layout>