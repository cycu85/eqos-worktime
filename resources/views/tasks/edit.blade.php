<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Edytuj zadanie
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $task->title }}
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('tasks.show', $task) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót do szczegółów
                </a>
                <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    Lista zadań
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('tasks.update', $task) }}">
                        @csrf
                        @method('PATCH')

                        <!-- Title -->
                        <div class="mb-6">
                            <label for="title" class="form-kt-label">
                                Tytuł zadania <span class="text-red-500">*</span>
                            </label>
                            <input id="title" 
                                   class="form-kt-control @error('title') border-red-500 @enderror" 
                                   type="text" 
                                   name="title" 
                                   value="{{ old('title', $task->title) }}" 
                                   required 
                                   autofocus />
                            @error('title')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <label for="description" class="form-kt-label">
                                Opis zadania
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="3" 
                                      class="form-kt-control @error('description') border-red-500 @enderror"
                                      placeholder="Opisz szczegóły zadania...">{{ old('description', $task->description) }}</textarea>
                            @error('description')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Start DateTime -->
                            <div>
                                <label for="start_datetime" class="form-kt-label">
                                    Data i godzina rozpoczęcia <span class="text-red-500">*</span>
                                </label>
                                <div class="datetime-container">
                                    <input id="start_datetime" 
                                           class="form-kt-control @error('start_datetime') border-red-500 @enderror" 
                                           type="datetime-local" 
                                           name="start_datetime" 
                                           value="{{ old('start_datetime', $task->start_datetime?->format('Y-m-d\TH:i')) }}" 
                                           required />
                                    <!-- Fallback for browsers that don't support datetime-local properly -->
                                    <div id="start_datetime_fallback" class="datetime-fallback hidden grid grid-cols-2 gap-2">
                                        <input type="date" 
                                               id="start_date_fallback" 
                                               class="form-kt-control @error('start_datetime') border-red-500 @enderror" 
                                               required />
                                        <input type="time" 
                                               id="start_time_fallback" 
                                               class="form-kt-control @error('start_datetime') border-red-500 @enderror" 
                                               required />
                                    </div>
                                </div>
                                @error('start_datetime')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- End DateTime -->
                            <div>
                                <label for="end_datetime" class="form-kt-label">
                                    Data i godzina zakończenia <span class="text-gray-500">(opcjonalna)</span>
                                </label>
                                <div class="datetime-container">
                                    <input id="end_datetime" 
                                           class="form-kt-control @error('end_datetime') border-red-500 @enderror" 
                                           type="datetime-local" 
                                           name="end_datetime" 
                                           value="{{ old('end_datetime', $task->end_datetime?->format('Y-m-d\TH:i')) }}" />
                                    <!-- Fallback for browsers that don't support datetime-local properly -->
                                    <div id="end_datetime_fallback" class="datetime-fallback hidden grid grid-cols-2 gap-2">
                                        <input type="date" 
                                               id="end_date_fallback" 
                                               class="form-kt-control @error('end_datetime') border-red-500 @enderror" />
                                        <input type="time" 
                                               id="end_time_fallback" 
                                               class="form-kt-control @error('end_datetime') border-red-500 @enderror" />
                                    </div>
                                </div>
                                @error('end_datetime')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Vehicle -->
                            <div>
                                <label for="vehicle_id" class="form-kt-label">
                                    Pojazd <span class="text-red-500">*</span>
                                </label>
                                <select id="vehicle_id" 
                                        name="vehicle_id" 
                                        class="form-kt-select @error('vehicle_id') border-red-500 @enderror" 
                                        required>
                                    <option value="">Wybierz pojazd</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $task->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->name }} ({{ $vehicle->registration }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="form-kt-label">
                                    Status
                                </label>
                                <select id="status" 
                                        name="status" 
                                        class="form-kt-select @error('status') border-red-500 @enderror">
                                    <option value="planned" {{ old('status', $task->status) == 'planned' ? 'selected' : '' }}>Planowane</option>
                                    <option value="in_progress" {{ old('status', $task->status) == 'in_progress' ? 'selected' : '' }}>W trakcie</option>
                                    <option value="completed" {{ old('status', $task->status) == 'completed' ? 'selected' : '' }}>Ukończone</option>
                                    <option value="cancelled" {{ old('status', $task->status) == 'cancelled' ? 'selected' : '' }}>Anulowane</option>
                                </select>
                                @error('status')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Members -->
                        <div class="mb-6">
                            <label for="team" class="form-kt-label">
                                Członkowie zespołu <span class="text-gray-500">(opcjonalnie)</span>
                            </label>
                            <div class="flex items-center space-x-3 mt-1">
                                <input type="hidden" id="team" name="team" value="{{ old('team', $task->team) }}" />
                                <div class="flex-1">
                                    <div id="selected-team" class="min-h-[42px] p-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md">
                                        <div id="team-display" class="text-gray-500 dark:text-gray-400">
                                            @if(old('team', $task->team))
                                                <span class="text-gray-900 dark:text-gray-100">{{ old('team', $task->team) }}</span>
                                            @elseif(!empty($leaderTeamMembers))
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($leaderTeamMembers as $memberName)
                                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            {{ $memberName }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @else
                                                Kliknij przycisk, aby wybrać członków zespołu
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <button type="button" onclick="openTeamModal()" class="btn-kt-secondary">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                    </svg>
                                    Wybierz członków
                                </button>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Wybierz członków, którzy będą pracować nad tym zadaniem.
                                @if(!empty($leaderTeamMembers))
                                    <br><strong>Jako lider zespołu, automatycznie załadowano członków Twojego zespołu.</strong>
                                @endif
                            </p>
                            @error('team')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <label for="notes" class="form-kt-label">
                                Notatki <span class="text-gray-500">(opcjonalnie)</span>
                            </label>
                            <textarea id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      class="form-kt-control @error('notes') border-red-500 @enderror" 
                                      placeholder="Dodatkowe informacje, uwagi...">{{ old('notes', $task->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Current Info -->
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Informacje o zadaniu</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
                                <div>
                                    <span class="font-medium">Utworzone:</span> {{ $task->created_at->format('d.m.Y H:i') }}
                                </div>
                                <div>
                                    <span class="font-medium">Ostatnia aktualizacja:</span> {{ $task->updated_at->format('d.m.Y H:i') }}
                                </div>
                                <div>
                                    <span class="font-medium">Lider:</span> {{ $task->leader->name }}
                                </div>
                                @if($task->duration_hours)
                                    <div>
                                        <span class="font-medium">Czas trwania:</span> {{ $task->duration_hours }}h
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex space-x-3">
                                <a href="{{ route('tasks.show', $task) }}" class="btn-kt-light">
                                    Anuluj
                                </a>
                                <button type="submit" class="btn-kt-primary">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Zapisz zmiany
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Selection Modal -->
    <div id="team-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeTeamModal()"></div>

            <!-- Center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4" id="modal-title">
                            Wybierz członków zespołu
                        </h3>
                        
                        <div class="mt-4 space-y-3 max-h-64 overflow-y-auto">
                            @foreach($users as $user)
                                <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <input type="checkbox" 
                                           value="{{ $user->id }}" 
                                           data-name="{{ $user->name }}"
                                           class="team-member-checkbox h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:bg-gray-700" />
                                    <div class="ml-3 flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900/20 flex items-center justify-center mr-3">
                                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        @if($users->isEmpty())
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                </svg>
                                <p class="mt-2">Brak dostępnych pracowników w systemie</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="saveTeamSelection()" class="btn-kt-success w-full sm:w-auto sm:ml-3">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Zapisz wybór
                    </button>
                    <button type="button" onclick="closeTeamModal()" class="btn-kt-light w-full sm:w-auto mt-3 sm:mt-0">
                        Anuluj
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedTeamMembers = [];

        // Initialize team display on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Check datetime-local support and setup fallback if needed
            setupDateTimeFallback();
            
            const leaderTeamMembers = @json($leaderTeamMembers ?? []);
            const existingTeam = document.getElementById('team').value;
            
            if (existingTeam) {
                // If there's existing team data, parse it and display as tags
                const teamNames = existingTeam.split(', ').filter(name => name.trim() !== '');
                if (teamNames.length > 0) {
                    document.getElementById('team-display').innerHTML = 
                        '<div class="flex flex-wrap gap-2">' + 
                        teamNames.map(name => 
                            '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">' +
                            '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">' +
                            '<path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>' +
                            '</svg>' + name + '</span>'
                        ).join('') + 
                        '</div>';
                }
            } else if (leaderTeamMembers.length > 0) {
                // Auto-populate team members for leaders
                const teamString = leaderTeamMembers.join(', ');
                document.getElementById('team').value = teamString;
            }
        });

        function openTeamModal() {
            document.getElementById('team-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Pre-select checkboxes based on current team value
            const currentTeam = document.getElementById('team').value;
            const checkboxes = document.querySelectorAll('.team-member-checkbox');
            
            // Clear all checkboxes first
            checkboxes.forEach(checkbox => checkbox.checked = false);
            
            if (currentTeam) {
                const teamNames = currentTeam.split(', ');
                checkboxes.forEach(checkbox => {
                    if (teamNames.includes(checkbox.dataset.name)) {
                        checkbox.checked = true;
                    }
                });
            }
        }

        function closeTeamModal() {
            document.getElementById('team-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function saveTeamSelection() {
            const checkboxes = document.querySelectorAll('.team-member-checkbox:checked');
            const selectedNames = Array.from(checkboxes).map(cb => cb.dataset.name);
            const teamString = selectedNames.join(', ');
            
            // Update hidden input
            document.getElementById('team').value = teamString;
            
            // Update display
            const displayElement = document.getElementById('team-display');
            if (selectedNames.length > 0) {
                displayElement.innerHTML = 
                    '<div class="flex flex-wrap gap-2">' + 
                    selectedNames.map(name => 
                        '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">' +
                        '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">' +
                        '<path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>' +
                        '</svg>' + name + '</span>'
                    ).join('') + 
                    '</div>';
            } else {
                displayElement.innerHTML = '<span class="text-gray-500 dark:text-gray-400">Kliknij przycisk, aby wybrać członków zespołu</span>';
            }
            
            closeTeamModal();
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeTeamModal();
            }
        });

        // Function to test datetime-local support and setup fallback
        function setupDateTimeFallback() {
            // Force fallback for desktop browsers to ensure time picker is always available
            const isDesktop = window.innerWidth > 768 && !('ontouchstart' in window);
            const userAgent = navigator.userAgent.toLowerCase();
            
            // Use fallback for desktop Chrome, Safari, Edge, and Firefox
            const needsFallback = isDesktop && (
                userAgent.includes('chrome') || 
                userAgent.includes('safari') || 
                userAgent.includes('edge') ||
                userAgent.includes('firefox')
            );
            
            // Debug logging
            console.log('Desktop detected:', isDesktop);
            console.log('User agent:', userAgent);
            console.log('Needs fallback:', needsFallback);
            
            if (needsFallback) {
                console.log('Activating datetime fallback');
                // Use fallback inputs
                document.getElementById('start_datetime').style.display = 'none';
                document.getElementById('start_datetime_fallback').classList.remove('hidden');
                document.getElementById('end_datetime').style.display = 'none';
                document.getElementById('end_datetime_fallback').classList.remove('hidden');
                
                // Setup event listeners to sync fallback inputs with main inputs
                setupFallbackSync('start');
                setupFallbackSync('end');
            }
        }

        // Function to setup synchronization between fallback inputs and main datetime input
        function setupFallbackSync(prefix) {
            const datetimeInput = document.getElementById(prefix + '_datetime');
            const dateInput = document.getElementById(prefix + '_date_fallback');
            const timeInput = document.getElementById(prefix + '_time_fallback');
            
            function updateDateTime() {
                const date = dateInput.value;
                const time = timeInput.value;
                if (date && time) {
                    datetimeInput.value = date + 'T' + time;
                } else if (date) {
                    datetimeInput.value = date + 'T00:00';
                } else {
                    datetimeInput.value = '';
                }
            }
            
            function updateFallbacks() {
                const datetime = datetimeInput.value;
                if (datetime) {
                    const [date, time] = datetime.split('T');
                    dateInput.value = date || '';
                    timeInput.value = time || '';
                }
            }
            
            // Sync fallback -> main
            dateInput.addEventListener('change', updateDateTime);
            timeInput.addEventListener('change', updateDateTime);
            
            // Sync main -> fallback (for initial values)
            updateFallbacks();
        }
    </script>
</x-app-layout>