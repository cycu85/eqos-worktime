<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Nowa delegacja
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Utwórz nową delegację służbową
                </p>
            </div>
            <div class="mt-3 sm:mt-0">
                <a href="{{ route('delegations.index') }}" class="btn-kt-secondary">
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
                    <form method="POST" action="{{ route('delegations.store') }}" class="space-y-6">
                        @csrf

                        <!-- Dane pracownika -->
                        <div>
                            <label for="employee_full_name" class="form-kt-label">Pracownik <span class="text-red-500">*</span></label>
                            @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                                <select class="form-kt-select" id="employee_full_name" name="employee_full_name" required>
                                    <option value="">Wybierz pracownika</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->name }}" {{ old('employee_full_name') == $user->name ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" class="form-kt-control bg-gray-100 cursor-not-allowed" 
                                       id="employee_full_name" name="employee_full_name" 
                                       value="{{ auth()->user()->name }}" 
                                       readonly required>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Jako pracownik/lider możesz tworzyć delegacje tylko dla siebie
                                </p>
                            @endif
                        </div>

                        <!-- Daty -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="order_date" class="form-kt-label">Data polecenia wyjazdu <span class="text-red-500">*</span></label>
                                <input type="date" class="form-kt-control" id="order_date" name="order_date" 
                                       value="{{ old('order_date', now()->format('Y-m-d')) }}" required>
                            </div>
                            <div>
                                <label for="departure_date" class="form-kt-label">Data wyjazdu</label>
                                <input type="date" class="form-kt-control" id="departure_date" name="departure_date" 
                                       value="{{ old('departure_date') }}">
                            </div>
                            <div>
                                <label for="arrival_date" class="form-kt-label">Data przyjazdu</label>
                                <input type="date" class="form-kt-control" id="arrival_date" name="arrival_date" 
                                       value="{{ old('arrival_date') }}">
                            </div>
                        </div>

                        <!-- Godziny -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="departure_time" class="form-kt-label">Godzina wyjazdu</label>
                                <input type="time" class="form-kt-control" id="departure_time" name="departure_time" 
                                       value="{{ old('departure_time') }}">
                            </div>
                            <div>
                                <label for="arrival_time" class="form-kt-label">Godzina przyjazdu</label>
                                <input type="time" class="form-kt-control" id="arrival_time" name="arrival_time" 
                                       value="{{ old('arrival_time') }}">
                            </div>
                        </div>

                        <!-- Cel podróży i projekt -->
                        <div>
                            <label for="travel_purpose" class="form-kt-label">Cel podróży <span class="text-red-500">*</span></label>
                            <textarea class="form-kt-control" id="travel_purpose" name="travel_purpose" rows="3" 
                                      required>{{ old('travel_purpose', $defaults['travel_purpose']) }}</textarea>
                        </div>

                        <div>
                            <label for="project" class="form-kt-label">Projekt</label>
                            <input type="text" class="form-kt-control" id="project" name="project" 
                                   value="{{ old('project', $defaults['project']) }}" maxlength="255">
                        </div>

                        <!-- Miejsce docelowe -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="destination_city" class="form-kt-label">Miejscowość <span class="text-red-500">*</span></label>
                                <input type="text" class="form-kt-control" id="destination_city" name="destination_city" 
                                       value="{{ old('destination_city', $defaults['destination_city']) }}" required maxlength="255">
                            </div>
                            <div>
                                <label for="country" class="form-kt-label">Kraj <span class="text-red-500">*</span></label>
                                <select class="form-kt-select" id="country" name="country" required>
                                    <option value="">Wybierz kraj</option>
                                    @foreach($countries as $countryOption)
                                        <option value="{{ $countryOption }}" {{ old('country', $defaults['country']) == $countryOption ? 'selected' : '' }}>
                                            {{ $countryOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Transport -->
                        <div>
                            <label class="form-kt-label">Pojazdy</label>
                            <div class="flex items-center gap-4">
                                <div class="flex-grow">
                                    <div id="selected-vehicles" class="min-h-[42px] p-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md">
                                        <div id="selected-vehicles-display" class="text-gray-500 dark:text-gray-400">
                                            Kliknij przycisk, aby wybrać pojazdy
                                        </div>
                                    </div>
                                </div>
                                <button type="button" onclick="openVehiclesModal()" class="btn-kt-secondary">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"></path>
                                    </svg>
                                    Wybierz pojazdy
                                </button>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Wybierz pojazdy które będą używane w delegacji.
                            </p>
                        </div>

                        <!-- Noclegi i posiłki -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nights_count" class="form-kt-label">Ilość noclegów</label>
                                <input type="number" class="form-kt-control" id="nights_count" name="nights_count" 
                                       value="{{ old('nights_count', 0) }}" min="0">
                            </div>
                            <div>
                                <label for="accommodation_limit" class="form-kt-label">Kwota limitu za nocleg (PLN)</label>
                                <input type="number" class="form-kt-control" id="accommodation_limit" name="accommodation_limit" 
                                       value="{{ old('accommodation_limit') }}" min="0" step="0.01">
                            </div>
                        </div>

                        <!-- Posiłki -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="breakfasts" class="form-kt-label">Śniadania</label>
                                <input type="number" class="form-kt-control" id="breakfasts" name="breakfasts" 
                                       value="{{ old('breakfasts', 0) }}" min="0">
                            </div>
                            <div>
                                <label for="lunches" class="form-kt-label">Obiady</label>
                                <input type="number" class="form-kt-control" id="lunches" name="lunches" 
                                       value="{{ old('lunches', 0) }}" min="0">
                            </div>
                            <div>
                                <label for="dinners" class="form-kt-label">Kolacje</label>
                                <input type="number" class="form-kt-control" id="dinners" name="dinners" 
                                       value="{{ old('dinners', 0) }}" min="0">
                            </div>
                        </div>

                        <!-- Koszty -->
                        <div>
                            <label for="total_expenses" class="form-kt-label">Suma kwot poniesionych (PLN)</label>
                            <input type="number" class="form-kt-control" id="total_expenses" name="total_expenses" 
                                   value="{{ old('total_expenses', 0) }}" min="0" step="0.01">
                        </div>

                        <!-- Hidden inputs container for selected vehicles -->
                        <div id="selected-vehicles-inputs">
                            @if(old('vehicles'))
                                @foreach(old('vehicles') as $vehicleRegistration)
                                    <input type="hidden" name="vehicles[]" value="{{ $vehicleRegistration }}" class="vehicle-hidden-input">
                                @endforeach
                            @endif
                        </div>

                        <!-- Przyciski -->
                        <div class="flex justify-end space-x-3 pt-6">
                            <a href="{{ route('delegations.index') }}" class="btn-kt-secondary">Anuluj</a>
                            <button type="submit" class="btn-kt-primary">Utwórz delegację</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicle Selection Modal -->
    <div id="vehicles-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Wybierz pojazdy</h3>
                </div>
                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    <div class="space-y-2">
                        @foreach($vehicles as $vehicle)
                            <label class="flex items-center p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                <input type="checkbox"
                                       value="{{ $vehicle->registration }}"
                                       data-name="{{ $vehicle->name }}"
                                       data-registration="{{ $vehicle->registration }}"
                                       class="vehicle-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-700 dark:focus:ring-green-600 mr-3">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $vehicle->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $vehicle->registration }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                    <button type="button" onclick="closeVehiclesModal()" class="btn-kt-secondary">Anuluj</button>
                    <button type="button" onclick="confirmVehiclesSelection()" class="btn-kt-primary">Zatwierdź wybór</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedVehicles = [];
        // Auto-calculate nights based on dates
        document.addEventListener('DOMContentLoaded', function() {
            const departureDate = document.getElementById('departure_date');
            const arrivalDate = document.getElementById('arrival_date');
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

            departureDate.addEventListener('change', calculateNights);
            arrivalDate.addEventListener('change', calculateNights);
        });

        // Vehicle selection functions
        function openVehiclesModal() {
            const checkboxes = document.querySelectorAll('.vehicle-checkbox');
            checkboxes.forEach(checkbox => {
                const registration = checkbox.value;
                checkbox.checked = selectedVehicles.some(vehicle => vehicle.registration === registration);
            });

            document.getElementById('vehicles-modal').classList.remove('hidden');
        }

        function closeVehiclesModal() {
            document.getElementById('vehicles-modal').classList.add('hidden');
        }

        function confirmVehiclesSelection() {
            const checkboxes = document.querySelectorAll('.vehicle-checkbox:checked');

            selectedVehicles = Array.from(checkboxes).map(cb => ({
                registration: cb.value,
                name: cb.dataset.name
            }));

            updateVehiclesDisplay();
            closeVehiclesModal();
        }

        function updateVehiclesDisplay() {
            const displayDiv = document.getElementById('selected-vehicles-display');

            if (selectedVehicles.length === 0) {
                displayDiv.innerHTML = '<span class="text-gray-500 dark:text-gray-400">Kliknij przycisk, aby wybrać pojazdy</span>';
                return;
            }

            const vehicleTags = selectedVehicles.map(vehicle =>
                `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 mr-2 mb-2">
                    ${vehicle.name} (${vehicle.registration})
                    <button type="button" onclick="removeVehicle('${vehicle.registration}')" class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full text-green-600 hover:bg-green-200 hover:text-green-800 dark:text-green-400 dark:hover:bg-green-800 dark:hover:text-green-200">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </span>`
            ).join('');

            displayDiv.innerHTML = vehicleTags;

            updateVehiclesInputs();
        }

        function updateVehiclesInputs() {
            const inputsContainer = document.getElementById('selected-vehicles-inputs');

            inputsContainer.innerHTML = '';

            selectedVehicles.forEach(vehicle => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'vehicles[]';
                input.value = vehicle.registration;
                input.className = 'vehicle-hidden-input';
                inputsContainer.appendChild(input);
            });
        }

        function removeVehicle(registration) {
            selectedVehicles = selectedVehicles.filter(vehicle => vehicle.registration !== registration);
            updateVehiclesDisplay();
        }

        // Initialize form with old values if validation failed
        document.addEventListener('DOMContentLoaded', function() {
            @if(old('vehicles'))
                const oldVehicleRegistrations = {!! json_encode(old('vehicles')) !!};
                const allVehicles = {!! $vehicles->map(function($vehicle) { return ['registration' => $vehicle->registration, 'name' => $vehicle->name]; })->toJson() !!};

                selectedVehicles = allVehicles.filter(vehicle => oldVehicleRegistrations.includes(vehicle.registration));
                updateVehiclesDisplay();
            @endif
        });
    </script>
</x-app-layout>