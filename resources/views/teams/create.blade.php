<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    {{ __('Dodaj zespół') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Utwórz nowy zespół pracowników
                </p>
            </div>
            
            <div class="mt-3 sm:mt-0">
                <a href="{{ route('teams.index') }}" class="btn-kt-secondary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót do listy
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Informacje o zespole</h3>
                </div>
                <div class="kt-card-body">
                    <form method="POST" action="{{ route('teams.store') }}" class="space-y-6">
                        @csrf

                        <!-- Team Name -->
                        <div>
                            <label for="name" class="form-kt-label">Nazwa zespołu *</label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   class="form-kt-control @error('name') border-red-500 @enderror" 
                                   value="{{ old('name') }}"
                                   required>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="form-kt-label">Opis zespołu</label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="3" 
                                      class="form-kt-control @error('description') border-red-500 @enderror"
                                      placeholder="Opis zadań i odpowiedzialności zespołu...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Team Leader -->
                        <div>
                            <label for="leader_id" class="form-kt-label">Lider zespołu *</label>
                            <select id="leader_id" 
                                    name="leader_id" 
                                    class="form-kt-select @error('leader_id') border-red-500 @enderror" 
                                    required>
                                <option value="">Wybierz lidera</option>
                                @foreach($leaders as $leader)
                                    <option value="{{ $leader->id }}" {{ old('leader_id') == $leader->id ? 'selected' : '' }}>
                                        {{ $leader->name }} ({{ $leader->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('leader_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Vehicle Assignment -->
                        <div>
                            <label class="form-kt-label">Przypisane pojazdy</label>
                            <div class="flex items-center space-x-3 mt-1">
                                <div id="vehicles-inputs">
                                    <!-- Hidden inputs for vehicles will be generated here -->
                                </div>

                                <div class="flex-1">
                                    <div id="selected-vehicles" class="min-h-[42px] p-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md">
                                        <div id="selected-vehicles-display" class="text-gray-500 dark:text-gray-400">
                                            Nie wybrano żadnych pojazdów
                                        </div>
                                    </div>
                                </div>
                                <button type="button" onclick="openVehiclesModal()" class="btn-kt-secondary">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                                        <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"></path>
                                    </svg>
                                    Wybierz pojazdy
                                </button>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Wybierz pojazdy przypisane do zespołu (opcjonalnie).
                            </p>
                            @error('vehicles')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Team Workers -->
                        <div>
                            <label class="form-kt-label">Pracownicy zespołu</label>
                            <div class="mt-2">
                                <button type="button" 
                                        onclick="openMembersModal()" 
                                        class="btn-kt-secondary">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                                    </svg>
                                    Wybierz pracowników
                                </button>
                                
                                <div id="members-inputs">
                                    <!-- Hidden inputs for members will be generated here -->
                                </div>
                                
                                <div id="selected-members-display" class="mt-3 space-y-2">
                                    <!-- Selected members will be displayed here -->
                                </div>
                                
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Pracownicy w zespole (opcjonalnie). Lider zostanie automatycznie dodany do zespołu.
                                </p>
                                
                                @error('members')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('teams.index') }}" class="btn-kt-light">Anuluj</a>
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Utwórz zespół
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Members Selection Modal -->
    <div id="members-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeMembersModal()"></div>
            
            <div class="inline-block overflow-hidden text-left align-bottom bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                Wybierz pracowników zespołu
                            </h3>
                            <div class="mt-4 max-h-64 overflow-y-auto">
                                @foreach($workers as $worker)
                                    <div class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded">
                                        <input type="checkbox" 
                                               id="member-{{ $worker->id }}" 
                                               class="member-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" 
                                               value="{{ $worker->id }}"
                                               data-name="{{ $worker->name }}"
                                               data-role="{{ $worker->role }}">
                                        <label for="member-{{ $worker->id }}" class="ml-3 flex-1 cursor-pointer">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $worker->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ ucfirst($worker->role) }} • {{ $worker->email }}
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            onclick="saveMembersSelection()" 
                            class="btn-kt-primary w-full sm:w-auto sm:ml-3">
                        Zapisz wybór
                    </button>
                    <button type="button" 
                            onclick="closeMembersModal()" 
                            class="btn-kt-light w-full sm:w-auto mt-3 sm:mt-0">
                        Anuluj
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicles Selection Modal -->
    <div id="vehicles-modal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="closeVehiclesModal()"></div>

            <div class="inline-block overflow-hidden text-left align-bottom bg-white dark:bg-gray-800 rounded-lg shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="px-4 pt-5 pb-4 bg-white dark:bg-gray-800 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                Wybierz pojazdy zespołu
                            </h3>
                            <div class="mt-4 max-h-64 overflow-y-auto">
                                @foreach($vehicles as $vehicle)
                                    <div class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded">
                                        <input type="checkbox"
                                               id="vehicle-{{ $vehicle->id }}"
                                               class="vehicle-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                               value="{{ $vehicle->id }}"
                                               data-name="{{ $vehicle->name }}"
                                               data-registration="{{ $vehicle->registration }}">
                                        <label for="vehicle-{{ $vehicle->id }}" class="ml-3 flex-1 cursor-pointer">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $vehicle->name }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $vehicle->registration }}
                                                @if($vehicle->description)
                                                    • {{ $vehicle->description }}
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                                @if($vehicles->isEmpty())
                                    <p class="mt-2">Brak dostępnych pojazdów w systemie</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button"
                            onclick="confirmVehicles()"
                            class="btn-kt-primary w-full sm:w-auto sm:ml-3">
                        Zapisz wybór
                    </button>
                    <button type="button"
                            onclick="closeVehiclesModal()"
                            class="btn-kt-light w-full sm:w-auto mt-3 sm:mt-0">
                        Anuluj
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedMembers = @json(old('members', []));

        function openMembersModal() {
            document.getElementById('members-modal').classList.remove('hidden');
            
            // Restore previous selections
            document.querySelectorAll('.member-checkbox').forEach(checkbox => {
                checkbox.checked = selectedMembers.includes(parseInt(checkbox.value));
            });
        }

        function closeMembersModal() {
            document.getElementById('members-modal').classList.add('hidden');
        }

        function saveMembersSelection() {
            const checkboxes = document.querySelectorAll('.member-checkbox:checked');
            selectedMembers = Array.from(checkboxes).map(cb => parseInt(cb.value));
            
            // Update hidden inputs
            updateMembersInputs();
            
            // Update display
            updateMembersDisplay();
            
            closeMembersModal();
        }

        function updateMembersInputs() {
            const inputsContainer = document.getElementById('members-inputs');
            inputsContainer.innerHTML = '';
            
            selectedMembers.forEach(memberId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'members[]';
                input.value = memberId;
                inputsContainer.appendChild(input);
            });
        }

        function updateMembersDisplay() {
            const displayContainer = document.getElementById('selected-members-display');
            
            if (selectedMembers.length === 0) {
                displayContainer.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-sm">Nie wybrano żadnych członków</p>';
                return;
            }
            
            let html = '<div class="flex flex-wrap gap-2">';
            selectedMembers.forEach(memberId => {
                const checkbox = document.querySelector(`input[value="${memberId}"]`);
                if (checkbox) {
                    const name = checkbox.dataset.name;
                    const role = checkbox.dataset.role;
                    html += `
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                            <span>${name}</span>
                            <span class="ml-1 text-xs opacity-75">(${role})</span>
                            <button type="button" onclick="removeMember(${memberId})" class="ml-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    `;
                }
            });
            html += '</div>';
            
            displayContainer.innerHTML = html;
        }

        function removeMember(memberId) {
            selectedMembers = selectedMembers.filter(id => id !== memberId);
            updateMembersInputs();
            updateMembersDisplay();
        }

        // Initialize display on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateMembersInputs();
            updateMembersDisplay();
            updateVehiclesInputs();
            updateVehiclesDisplay();
        });

        // Vehicles modal functionality
        let selectedVehicles = [];

        function openVehiclesModal() {
            document.getElementById('vehicles-modal').classList.remove('hidden');
        }

        function closeVehiclesModal() {
            document.getElementById('vehicles-modal').classList.add('hidden');
        }

        function confirmVehicles() {
            const checkboxes = document.querySelectorAll('#vehicles-modal input[type="checkbox"]:checked');
            selectedVehicles = Array.from(checkboxes).map(cb => parseInt(cb.value));
            updateVehiclesInputs();
            updateVehiclesDisplay();
            closeVehiclesModal();
        }

        function updateVehiclesInputs() {
            const inputsContainer = document.getElementById('vehicles-inputs');
            inputsContainer.innerHTML = '';

            selectedVehicles.forEach(vehicleId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'vehicles[]';
                input.value = vehicleId;
                inputsContainer.appendChild(input);
            });
        }

        function updateVehiclesDisplay() {
            const displayContainer = document.getElementById('selected-vehicles-display');

            if (selectedVehicles.length === 0) {
                displayContainer.innerHTML = '<p class="text-gray-500 dark:text-gray-400 text-sm">Nie wybrano żadnych pojazdów</p>';
                return;
            }

            let html = '<div class="flex flex-wrap gap-2">';
            selectedVehicles.forEach(vehicleId => {
                const checkbox = document.querySelector(`#vehicles-modal input[value="${vehicleId}"]`);
                if (checkbox) {
                    const name = checkbox.dataset.name;
                    const registration = checkbox.dataset.registration;
                    html += `
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                            <span>${name}</span>
                            <span class="ml-1 text-xs opacity-75">(${registration})</span>
                            <button type="button" onclick="removeVehicle(${vehicleId})" class="ml-2 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    `;
                }
            });
            html += '</div>';

            displayContainer.innerHTML = html;
        }

        function removeVehicle(vehicleId) {
            selectedVehicles = selectedVehicles.filter(id => id !== vehicleId);
            updateVehiclesInputs();
            updateVehiclesDisplay();
        }
    </script>
</x-app-layout>