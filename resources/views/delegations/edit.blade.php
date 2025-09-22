<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Edycja delegacji: {{ $delegation->full_name }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $delegation->destination_city }}, {{ $delegation->country }}
                </p>
            </div>
            <div class="mt-3 sm:mt-0 flex space-x-3">
                <a href="{{ route('delegations.show', $delegation) }}" class="btn-kt-secondary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót do delegacji
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg" role="alert">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="kt-card">
                <div class="kt-card-body">
                    <form method="POST" action="{{ route('delegations.update', $delegation) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Dane pracownika -->
                        <div>
                            <label for="employee_full_name" class="form-kt-label">Pracownik <span class="text-red-500">*</span></label>
                            @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                                <select class="form-kt-select" id="employee_full_name" name="employee_full_name" required>
                                    <option value="">Wybierz pracownika</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->name }}" {{ old('employee_full_name', $delegation->full_name) == $user->name ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-kt-control bg-gray-100 cursor-not-allowed" 
                                       id="employee_full_name" name="employee_full_name" 
                                       value="{{ $delegation->full_name }}" 
                                       readonly required>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Jako pracownik/lider możesz edytować tylko swoje delegacje
                                </p>
                            @endif
                        </div>

                        <!-- Daty -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="order_date" class="form-kt-label">Data polecenia wyjazdu <span class="text-red-500">*</span></label>
                                @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                                    <input type="date" class="form-kt-control" id="order_date" name="order_date" 
                                           value="{{ old('order_date', $delegation->order_date->format('Y-m-d')) }}" required>
                                @else
                                    <input type="date" class="form-kt-control bg-gray-100 cursor-not-allowed" id="order_date" name="order_date" 
                                           value="{{ old('order_date', $delegation->order_date->format('Y-m-d')) }}" readonly required>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Tylko administrator i kierownik mogą edytować datę polecenia wyjazdu
                                    </p>
                                @endif
                            </div>
                            <div>
                                <label for="departure_date" class="form-kt-label">Data wyjazdu</label>
                                <input type="date" class="form-kt-control" id="departure_date" name="departure_date" 
                                       value="{{ old('departure_date', $delegation->departure_date?->format('Y-m-d')) }}"
                                       max="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div>
                                <label for="arrival_date" class="form-kt-label">Data przyjazdu</label>
                                <input type="date" class="form-kt-control" id="arrival_date" name="arrival_date" 
                                       value="{{ old('arrival_date', $delegation->arrival_date?->format('Y-m-d')) }}"
                                       max="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>

                        <!-- Godziny -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="departure_time" class="form-kt-label">Godzina wyjazdu</label>
                                <input type="time" class="form-kt-control" id="departure_time" name="departure_time" 
                                       value="{{ old('departure_time', $delegation->departure_time) }}">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Nie można wprowadzić daty i godziny w przyszłości
                                </p>
                            </div>
                            <div>
                                <label for="arrival_time" class="form-kt-label">Godzina przyjazdu</label>
                                <input type="time" class="form-kt-control" id="arrival_time" name="arrival_time" 
                                       value="{{ old('arrival_time', $delegation->arrival_time) }}">
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Nie można wprowadzić daty i godziny w przyszłości
                                </p>
                            </div>
                        </div>

                        <!-- Cel podróży i projekt -->
                        <div>
                            <label for="travel_purpose" class="form-kt-label">Cel podróży <span class="text-red-500">*</span></label>
                            <textarea class="form-kt-control" id="travel_purpose" name="travel_purpose" rows="3" 
                                      required>{{ old('travel_purpose', $delegation->travel_purpose) }}</textarea>
                        </div>

                        <div>
                            <label for="project" class="form-kt-label">Projekt</label>
                            <input type="text" class="form-kt-control" id="project" name="project" 
                                   value="{{ old('project', $delegation->project) }}" maxlength="255">
                        </div>

                        <!-- Miejsce docelowe -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="destination_city" class="form-kt-label">Miejscowość <span class="text-red-500">*</span></label>
                                <input type="text" class="form-kt-control" id="destination_city" name="destination_city" 
                                       value="{{ old('destination_city', $delegation->destination_city) }}" required maxlength="255">
                            </div>
                            <div>
                                <label for="country" class="form-kt-label">Kraj <span class="text-red-500">*</span></label>
                                <select class="form-kt-select" id="country" name="country" required>
                                    <option value="">Wybierz kraj</option>
                                    @foreach($countries as $countryOption)
                                        <option value="{{ $countryOption }}" {{ old('country', $delegation->country) == $countryOption ? 'selected' : '' }}>
                                            {{ $countryOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Transport -->
                        <div>
                            <label for="vehicle_registration" class="form-kt-label">Środek lokomocji</label>
                            <select class="form-kt-select" id="vehicle_registration" name="vehicle_registration">
                                <option value="">Wybierz pojazd</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->registration }}" {{ old('vehicle_registration', $delegation->vehicle_registration) == $vehicle->registration ? 'selected' : '' }}>
                                        {{ $vehicle->registration }} - {{ $vehicle->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Noclegi i posiłki -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nights_count" class="form-kt-label">Ilość noclegów</label>
                                <input type="number" class="form-kt-control" id="nights_count" name="nights_count" 
                                       value="{{ old('nights_count', $delegation->nights_count) }}" min="0">
                            </div>
                            <div>
                                <label for="accommodation_limit" class="form-kt-label">Kwota limitu za nocleg (PLN)</label>
                                <input type="number" class="form-kt-control" id="accommodation_limit" name="accommodation_limit" 
                                       value="{{ old('accommodation_limit', $delegation->accommodation_limit) }}" min="0" step="0.01">
                            </div>
                        </div>

                        <!-- Posiłki -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="breakfasts" class="form-kt-label">Śniadania</label>
                                <input type="number" class="form-kt-control" id="breakfasts" name="breakfasts" 
                                       value="{{ old('breakfasts', $delegation->breakfasts) }}" min="0">
                            </div>
                            <div>
                                <label for="lunches" class="form-kt-label">Obiady</label>
                                <input type="number" class="form-kt-control" id="lunches" name="lunches" 
                                       value="{{ old('lunches', $delegation->lunches) }}" min="0">
                            </div>
                            <div>
                                <label for="dinners" class="form-kt-label">Kolacje</label>
                                <input type="number" class="form-kt-control" id="dinners" name="dinners" 
                                       value="{{ old('dinners', $delegation->dinners) }}" min="0">
                            </div>
                        </div>

                        <!-- Koszty -->
                        <div>
                            <label for="total_expenses" class="form-kt-label">Suma kwot poniesionych (PLN)</label>
                            <input type="number" class="form-kt-control" id="total_expenses" name="total_expenses" 
                                   value="{{ old('total_expenses', $delegation->total_expenses) }}" min="0" step="0.01">
                        </div>

                        <!-- Status (tylko dla administratorów i kierowników) -->
                        @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                        <div>
                            <label for="delegation_status" class="form-kt-label">Status delegacji</label>
                            <select class="form-kt-select" id="delegation_status" name="delegation_status">
                                <option value="draft" {{ old('delegation_status', $delegation->delegation_status) == 'draft' ? 'selected' : '' }}>Szkic</option>
                                <option value="approved" {{ old('delegation_status', $delegation->delegation_status) == 'approved' ? 'selected' : '' }}>Zatwierdzona</option>
                                <option value="completed" {{ old('delegation_status', $delegation->delegation_status) == 'completed' ? 'selected' : '' }}>Zakończona</option>
                                <option value="cancelled" {{ old('delegation_status', $delegation->delegation_status) == 'cancelled' ? 'selected' : '' }}>Anulowana</option>
                            </select>
                        </div>
                        @else
                        <div>
                            <label class="form-kt-label">Status delegacji</label>
                            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-md">
                                @if($delegation->delegation_status === 'draft')
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                        Szkic
                                    </span>
                                @elseif($delegation->delegation_status === 'employee_approved')
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                        Zaakceptowana przez pracownika
                                    </span>
                                @elseif($delegation->delegation_status === 'approved')
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        Zatwierdzona
                                    </span>
                                @elseif($delegation->delegation_status === 'completed')
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        Zakończona
                                    </span>
                                @elseif($delegation->delegation_status === 'cancelled')
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                        Anulowana
                                    </span>
                                @endif
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                    Tylko administratorzy i kierownicy mogą zmieniać status delegacji.
                                </p>
                            </div>
                            <!-- Hidden field to preserve current status -->
                            <input type="hidden" name="delegation_status" value="{{ $delegation->delegation_status }}">
                        </div>
                        @endif

                        <!-- Przyciski -->
                        <div class="flex justify-end space-x-3 pt-6">
                            <a href="{{ route('delegations.show', $delegation) }}" class="btn-kt-secondary">Anuluj</a>
                            <button type="submit" class="btn-kt-primary">Zapisz zmiany</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-calculate nights based on dates
        document.addEventListener('DOMContentLoaded', function() {
            const departureDate = document.getElementById('departure_date');
            const arrivalDate = document.getElementById('arrival_date');
            const departureTime = document.getElementById('departure_time');
            const arrivalTime = document.getElementById('arrival_time');
            const nightsCount = document.getElementById('nights_count');

            function calculateNights() {
                if (departureDate.value && arrivalDate.value) {
                    const departure = new Date(departureDate.value);
                    const arrival = new Date(arrivalDate.value);
                    const diffTime = Math.abs(arrival - departure);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    
                    if (diffDays > 0) {
                        nightsCount.value = diffDays;
                    }
                }
            }

            function validateDateTime() {
                const now = new Date();
                const today = now.toISOString().split('T')[0];
                
                // Sprawdź datę i godzinę wyjazdu
                if (departureDate.value && departureTime.value) {
                    const departureDateTime = new Date(departureDate.value + 'T' + departureTime.value);
                    if (departureDateTime > now) {
                        alert('Data i godzina wyjazdu nie może być w przyszłości!');
                        departureTime.value = '';
                    }
                }
                
                // Sprawdź datę i godzinę przyjazdu
                if (arrivalDate.value && arrivalTime.value) {
                    const arrivalDateTime = new Date(arrivalDate.value + 'T' + arrivalTime.value);
                    if (arrivalDateTime > now) {
                        alert('Data i godzina przyjazdu nie może być w przyszłości!');
                        arrivalTime.value = '';
                    }
                }
            }

            departureDate.addEventListener('change', calculateNights);
            arrivalDate.addEventListener('change', calculateNights);
            departureTime.addEventListener('change', validateDateTime);
            arrivalTime.addEventListener('change', validateDateTime);
        });
    </script>
</x-app-layout>