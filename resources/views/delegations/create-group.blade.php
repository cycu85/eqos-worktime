<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Nowa delegacja grupowa
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Utwórz delegację dla wielu pracowników jednocześnie
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
            @if($errors->any())
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Nowa delegacja grupowa</h3>
                </div>
                <div class="kt-card-body">
                    <form method="POST" action="{{ route('delegations.store-group') }}" class="space-y-6" onsubmit="debugFormSubmit(event)">
                        @csrf

                        <!-- Wybór zespołu i pracowników -->
                        <div class="space-y-4">
                            <!-- Team selection -->
                            <div>
                                <label for="team_id" class="form-kt-label">Zespół (opcjonalnie)</label>
                                <select id="team_id" 
                                        name="team_id" 
                                        class="form-kt-select"
                                        onchange="selectTeam()">
                                    <option value="">Wybierz zespół aby automatycznie zaznaczyć członków</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->id }}"
                                                data-members="{{ implode(',', $team->members ?? []) }}"
                                                data-members-names="{{ $team->members_names ?? '' }}"
                                                data-vehicle="{{ $team->vehicle?->registration ?? '' }}"
                                                {{ old('team_id') == $team->id ? 'selected' : '' }}>
                                            {{ $team->name }} 
                                            @if($team->leader)
                                                - {{ $team->leader->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Wybór zespołu automatycznie zaznaczy jego członków i przypisze pojazd.
                                </p>
                            </div>

                            <!-- Employee selection -->
                            <div>
                                <label class="form-kt-label">Pracownicy <span class="text-red-500">*</span></label>
                                <div class="flex items-center gap-4">
                                    <div class="flex-grow">
                                        <div id="selected-employees" class="min-h-[42px] p-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md">
                                            <div id="selected-employees-display" class="text-gray-500 dark:text-gray-400">
                                                Kliknij przycisk, aby wybrać pracowników
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" onclick="openEmployeesModal()" class="btn-kt-secondary">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                                        </svg>
                                        Wybierz pracowników
                                    </button>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Wybierz pracowników, dla których zostaną utworzone delegacje.
                                </p>
                                @error('selected_employees')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Daty -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="order_date" class="form-kt-label">Data polecenia wyjazdu <span class="text-red-500">*</span></label>
                                <input type="date" class="form-kt-control" id="order_date" name="order_date" 
                                       value="{{ old('order_date', date('Y-m-d')) }}" required>
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
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" {{ old('country', $defaults['country']) == $country ? 'selected' : '' }}>
                                            {{ $country }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Transport -->
                        <div>
                            <label for="vehicle_registration" class="form-kt-label">Pojazd</label>
                            <select class="form-kt-select" id="vehicle_registration" name="vehicle_registration">
                                <option value="">Wybierz pojazd (opcjonalnie)</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->registration }}" {{ old('vehicle_registration') == $vehicle->registration ? 'selected' : '' }}>
                                        {{ $vehicle->name }} ({{ $vehicle->registration }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Noclegi i posiłki -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="accommodation_limit" class="form-kt-label">Limit za nocleg (PLN)</label>
                                <input type="number" class="form-kt-control" id="accommodation_limit" name="accommodation_limit" 
                                       value="{{ old('accommodation_limit', 0) }}" step="0.01" min="0">
                            </div>
                            <div>
                                <label for="nights_count" class="form-kt-label">Ilość noclegów</label>
                                <input type="number" class="form-kt-control" id="nights_count" name="nights_count" 
                                       value="{{ old('nights_count', 0) }}" min="0">
                            </div>
                        </div>

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

                        <!-- Wydatki -->
                        <div>
                            <label for="total_expenses" class="form-kt-label">Suma poniesionych wydatków (PLN)</label>
                            <input type="number" class="form-kt-control" id="total_expenses" name="total_expenses" 
                                   value="{{ old('total_expenses', 0) }}" step="0.01" min="0">
                        </div>

                        <!-- Hidden inputs container for selected employees -->
                        <div id="selected-employees-inputs">
                            @if(old('selected_employees'))
                                @foreach(old('selected_employees') as $employeeId)
                                    <input type="hidden" name="selected_employees[]" value="{{ $employeeId }}" class="employee-hidden-input">
                                @endforeach
                            @endif
                        </div>

                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('delegations.index') }}" class="btn-kt-secondary">Anuluj</a>
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Utwórz delegacje grupowe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Selection Modal -->
    <div id="employees-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Wybierz pracowników</h3>
                </div>
                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    <div class="space-y-2">
                        @foreach($users as $user)
                            <label class="flex items-center p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                <input type="checkbox" 
                                       value="{{ $user->id }}"
                                       data-name="{{ $user->name }}"
                                       class="employee-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:focus:ring-blue-600 mr-3">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->role ?? 'Pracownik' }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                    <button type="button" onclick="closeEmployeesModal()" class="btn-kt-secondary">Anuluj</button>
                    <button type="button" onclick="confirmEmployeesSelection()" class="btn-kt-primary">Zatwierdź wybór</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedEmployees = [];

        function selectTeam() {
            const teamSelect = document.getElementById('team_id');
            const selectedOption = teamSelect.selectedOptions[0];
            
            if (!selectedOption || !selectedOption.value) {
                return;
            }
            
            // Get team members
            const membersString = selectedOption.getAttribute('data-members');
            const membersNamesString = selectedOption.getAttribute('data-members-names');
            const vehicleRegistration = selectedOption.getAttribute('data-vehicle');
            
            if (membersString && membersNamesString) {
                const memberIds = membersString.split(',').filter(id => id.trim());
                const memberNames = membersNamesString.split(',').filter(name => name.trim());
                
                // Update selected employees
                selectedEmployees = memberIds.map((id, index) => ({
                    id: parseInt(id),
                    name: memberNames[index] || 'Unknown'
                }));
                
                updateEmployeesDisplay();
            }
            
            // Set vehicle if available
            if (vehicleRegistration) {
                const vehicleSelect = document.getElementById('vehicle_registration');
                Array.from(vehicleSelect.options).forEach(option => {
                    if (option.text.includes(vehicleRegistration)) {
                        option.selected = true;
                    }
                });
            }
        }

        function openEmployeesModal() {
            // Update modal checkboxes based on current selection
            const checkboxes = document.querySelectorAll('.employee-checkbox');
            checkboxes.forEach(checkbox => {
                const userId = parseInt(checkbox.value);
                checkbox.checked = selectedEmployees.some(emp => emp.id === userId);
            });
            
            document.getElementById('employees-modal').classList.remove('hidden');
        }

        function closeEmployeesModal() {
            document.getElementById('employees-modal').classList.add('hidden');
        }

        function confirmEmployeesSelection() {
            const checkboxes = document.querySelectorAll('.employee-checkbox:checked');
            
            selectedEmployees = Array.from(checkboxes).map(cb => ({
                id: parseInt(cb.value),
                name: cb.dataset.name
            }));
            
            console.log('Selected employees:', selectedEmployees); // Debug log
            
            updateEmployeesDisplay();
            closeEmployeesModal();
        }

        function updateEmployeesDisplay() {
            const displayDiv = document.getElementById('selected-employees-display');
            
            if (selectedEmployees.length === 0) {
                displayDiv.innerHTML = '<span class="text-gray-500 dark:text-gray-400">Kliknij przycisk, aby wybrać pracowników</span>';
                return;
            }
            
            const employeeTags = selectedEmployees.map(employee => 
                `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mr-2 mb-2">
                    ${employee.name}
                    <button type="button" onclick="removeEmployee(${employee.id})" class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full text-blue-600 hover:bg-blue-200 hover:text-blue-800 dark:text-blue-400 dark:hover:bg-blue-800 dark:hover:text-blue-200">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </span>`
            ).join('');
            
            displayDiv.innerHTML = employeeTags;
            
            // Update hidden form inputs
            updateEmployeesInputs();
        }

        function updateEmployeesInputs() {
            // Use the dedicated container for inputs  
            const inputsContainer = document.getElementById('selected-employees-inputs');
            console.log('Inputs container found:', inputsContainer); // Debug log
            
            // Clear existing inputs in container
            inputsContainer.innerHTML = '';
            
            console.log('Creating JSON input for:', selectedEmployees); // Debug log
            
            // Create a single JSON input instead of multiple array inputs
            if (selectedEmployees.length > 0) {
                const jsonInput = document.createElement('input');
                jsonInput.type = 'hidden';
                jsonInput.name = 'selected_employees_json';
                jsonInput.value = JSON.stringify(selectedEmployees.map(emp => emp.id));
                inputsContainer.appendChild(jsonInput);
                console.log('Added JSON input:', jsonInput.name, jsonInput.value);
                
                // Also keep the array approach as backup
                selectedEmployees.forEach(employee => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_employees[]';
                    input.value = employee.id;
                    input.className = 'employee-hidden-input';
                    inputsContainer.appendChild(input);
                });
            }
            
            // Debug: Count all inputs with this name in the form
            const form = document.querySelector('form');
            const inputsInForm = form.querySelectorAll('input[name="selected_employees[]"]');
            const jsonInput = form.querySelector('input[name="selected_employees_json"]');
            console.log('Total selected_employees[] inputs in form:', inputsInForm.length);
            console.log('JSON input in form:', jsonInput ? 'YES' : 'NO');
        }

        function removeEmployee(employeeId) {
            selectedEmployees = selectedEmployees.filter(emp => emp.id !== employeeId);
            updateEmployeesDisplay();
        }

        function debugFormSubmit(event) {
            console.log('Form submission - selected employees array:', selectedEmployees);
            
            // First, make sure we have the latest inputs
            if (selectedEmployees.length > 0) {
                updateEmployeesInputs();
            }
            
            // Debug DOM before creating FormData
            const form = event.target;
            const hiddenInputs = form.querySelectorAll('input[name="selected_employees[]"]');
            console.log('DOM hidden inputs found:', hiddenInputs.length);
            hiddenInputs.forEach((input, index) => {
                console.log(`Hidden input ${index}:`, input.name, input.value, 'in form:', form.contains(input));
            });
            
            // Check container content
            const container = document.getElementById('selected-employees-inputs');
            console.log('Container innerHTML:', container.innerHTML);
            console.log('Container children:', container.children.length);
            
            // Create FormData and check
            const formData = new FormData(event.target);
            const selectedEmployeesFromForm = formData.getAll('selected_employees[]');
            const jsonData = formData.get('selected_employees_json');
            console.log('Form data selected_employees[]:', selectedEmployeesFromForm);
            console.log('Form data selected_employees_json:', jsonData);
            
            // If neither approach works, try to fix it
            if (selectedEmployeesFromForm.length === 0 && !jsonData && selectedEmployees.length > 0) {
                console.error('No selected employees found in form data!');
                console.log('All form data:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
                }
                
                console.log('Attempting to fix by re-creating inputs...');
                event.preventDefault();
                
                // Clear and recreate inputs with both approaches
                const inputsContainer = document.getElementById('selected-employees-inputs');
                inputsContainer.innerHTML = '';
                
                // JSON approach
                const jsonInput = document.createElement('input');
                jsonInput.type = 'hidden';
                jsonInput.name = 'selected_employees_json';
                jsonInput.value = JSON.stringify(selectedEmployees.map(emp => emp.id));
                inputsContainer.appendChild(jsonInput);
                
                // Array approach
                selectedEmployees.forEach(employee => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_employees[]';
                    input.value = employee.id;
                    inputsContainer.appendChild(input);
                });
                
                console.log('Recreated inputs, trying again...');
                
                // Try again after a brief delay
                setTimeout(() => {
                    form.submit();
                }, 100);
                
                return false;
            }
            
            // Continue with normal submission
            return true;
        }

        // Initialize form with old values if validation failed
        document.addEventListener('DOMContentLoaded', function() {
            @if(old('selected_employees'))
                console.log('Old selected employees found:', {!! json_encode(old('selected_employees')) !!});
                const oldEmployeeIds = {!! json_encode(old('selected_employees')) !!};
                const allEmployees = {!! $users->map(function($user) { return ['id' => $user->id, 'name' => $user->name]; })->toJson() !!};
                
                selectedEmployees = allEmployees.filter(emp => oldEmployeeIds.includes(emp.id.toString()));
                console.log('Restored selected employees:', selectedEmployees);
                updateEmployeesDisplay();
            @else
                console.log('No old selected employees found');
            @endif
        });
    </script>
</x-app-layout>