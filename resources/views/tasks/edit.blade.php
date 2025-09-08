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
                    <form method="POST" action="{{ route('tasks.update', $task) }}" enctype="multipart/form-data">
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

                        @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                            <!-- Date fields - only for Admin/Kierownik -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Start Date -->
                                <div>
                                    <label for="start_date" class="form-kt-label">
                                        Data rozpoczęcia <span class="text-red-500">*</span>
                                    </label>
                                    <input id="start_date" 
                                           class="form-kt-control @error('start_date') border-red-500 @enderror" 
                                           type="date" 
                                           name="start_date" 
                                           value="{{ old('start_date', $task->start_date?->format('Y-m-d')) }}" 
                                           required />
                                    @error('start_date')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- End Date -->
                                <div>
                                    <label for="end_date" class="form-kt-label">
                                        Data zakończenia <span class="text-red-500">*</span>
                                    </label>
                                    <input id="end_date" 
                                           class="form-kt-control @error('end_date') border-red-500 @enderror" 
                                           type="date" 
                                           name="end_date" 
                                           value="{{ old('end_date', $task->end_date?->format('Y-m-d')) }}" 
                                           required />
                                    @error('end_date')
                                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div>
                                            <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-1">Edycja dat zadania</h4>
                                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                                Zmiana dat spowoduje automatyczną regenerację harmonogramu pracy. Istniejące wpisy mogą zostać usunięte lub dodane nowe dni.
                                            </p>
                                        </div>
                                    </div>
                                    <a href="{{ route('tasks.work-logs', $task) }}" 
                                       class="inline-flex items-center px-3 py-2 text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-800 dark:text-blue-100 dark:hover:bg-blue-700 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4m-3 9v-7m-6 7h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v9a2 2 0 002 2z"></path>
                                        </svg>
                                        Edytuj harmonogram
                                    </a>
                                </div>
                            </div>
                        @else
                            <!-- Read-only date display for other users -->
                            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Okres realizacji</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
                                            <div>
                                                <span class="font-medium">Data rozpoczęcia:</span> {{ $task->start_date?->format('d.m.Y') ?? 'Nie określona' }}
                                            </div>
                                            <div>
                                                <span class="font-medium">Data zakończenia:</span> {{ $task->end_date?->format('d.m.Y') ?? 'Nie określona' }}
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                            Tylko administratorzy i kierownicy mogą edytować daty zadania.
                                        </p>
                                    </div>
                                    <a href="{{ route('tasks.work-logs', $task) }}" 
                                       class="inline-flex items-center px-3 py-2 text-xs font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-500 transition-colors ml-4">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4m-3 9v-7m-6 7h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v9a2 2 0 002 2z"></path>
                                        </svg>
                                        Zobacz harmonogram
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                            <!-- Team Selection for Admin/Kierownik -->
                            <div class="mb-6">
                                <label for="team_id" class="form-kt-label">
                                    Wybierz zespół <span class="text-gray-500">(opcjonalnie)</span>
                                </label>
                                <select id="team_id" 
                                        name="team_id" 
                                        class="form-kt-select @error('team_id') border-red-500 @enderror"
                                        onchange="selectTeam()">
                                    <option value="">-- Wybierz zespół --</option>
                                    @foreach($teams as $team)
                                        <option value="{{ $team->id }}" 
                                                data-leader="{{ $team->leader->name ?? '' }}"
                                                data-leader-id="{{ $team->leader_id ?? '' }}"
                                                data-vehicle="{{ $team->vehicle_id ?? '' }}"
                                                data-members="{{ implode(',', $team->members ?? []) }}"
                                                data-members-names="{{ $team->members_names ?? '' }}"
                                                {{ old('team_id', $task->team_id ?? '') == $team->id ? 'selected' : '' }}>
                                            {{ $team->name }} 
                                            @if($team->leader)
                                                - {{ $team->leader->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Wybierz zespół aby automatycznie załadować pojazdy, lidera i członków zespołu.
                                </p>
                                @error('team_id')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <!-- Vehicle -->
                        <div class="mb-6">
                            <label class="form-kt-label">
                                Pojazdy <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center space-x-3 mt-1">
                                <div id="vehicles-inputs">
                                    <!-- Hidden inputs for vehicles will be generated here -->
                                </div>
                                
                                <div class="flex-1">
                                    <div id="selected-vehicles" class="min-h-[42px] p-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 rounded-md">
                                        <div id="selected-vehicles-display" class="text-gray-500 dark:text-gray-400">
                                            <!-- Selected vehicles will be displayed here -->
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
                                Wybierz pojazdy potrzebne do wykonania zadania.
                            </p>
                            @error('vehicles')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-6">
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
                                @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                                    <option value="accepted" {{ old('status', $task->status) == 'accepted' ? 'selected' : '' }}>Zaakceptowane</option>
                                @endif
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
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

                        <!-- Images -->
                        <div class="mb-6">
                            <label class="form-kt-label">
                                Zdjęcia <span class="text-gray-500">(opcjonalnie)</span>
                            </label>
                            
                            <!-- Current Images -->
                            @if($task->images && count($task->images) > 0)
                                <div class="mb-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Aktualnie załączone zdjęcia:</p>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3" id="current-images">
                                        @foreach($task->images as $index => $image)
                                            <div class="relative group current-image" data-index="{{ $index }}">
                                                <img src="{{ asset('storage/' . $image['path']) }}" 
                                                     alt="{{ $image['original_name'] }}" 
                                                     class="w-full h-24 object-cover rounded-lg cursor-pointer hover:opacity-75 transition-opacity"
                                                     onclick="showImagePreview('{{ asset('storage/' . $image['path']) }}', '{{ $image['original_name'] }}')">
                                                <button type="button" 
                                                        onclick="removeCurrentImage({{ $index }})"
                                                        class="absolute top-1 right-1 bg-red-500 hover:bg-red-700 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                                    ×
                                                </button>
                                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 rounded-b-lg truncate">
                                                    {{ $image['original_name'] }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Upload New Images -->
                            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4">
                                <input type="file" 
                                       id="images" 
                                       name="images[]" 
                                       multiple 
                                       accept="image/*"
                                       class="hidden"
                                       onchange="previewNewImages(this)">
                                <label for="images" class="flex flex-col items-center justify-center cursor-pointer">
                                    <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Kliknij aby wybrać zdjęcia</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-500 mt-1">JPEG, PNG, GIF, WEBP (max 10MB każde)</span>
                                </label>
                            </div>
                            
                            <!-- Preview New Images -->
                            <div id="new-images-preview" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mt-3 hidden">
                                <!-- New images will be previewed here -->
                            </div>
                            
                            @error('images')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            @error('images.*')
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

    <!-- Vehicle Selection Modal -->
    <div id="vehicles-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="vehicles-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeVehiclesModal()"></div>

            <!-- Center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4" id="vehicles-modal-title">
                            Wybierz pojazdy
                        </h3>
                        
                        <div class="mt-4 space-y-3 max-h-64 overflow-y-auto">
                            @foreach($vehicles as $vehicle)
                                <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer">
                                    <input type="checkbox" 
                                           value="{{ $vehicle->id }}" 
                                           data-name="{{ $vehicle->name }}"
                                           data-registration="{{ $vehicle->registration }}"
                                           class="vehicle-checkbox h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:bg-gray-700" />
                                    <div class="ml-3 flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center mr-3">
                                            <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $vehicle->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $vehicle->registration }}</div>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        @if($vehicles->isEmpty())
                            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <p class="mt-2">Brak dostępnych pojazdów w systemie</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="saveVehiclesSelection()" class="btn-kt-success w-full sm:w-auto sm:ml-3">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Zapisz wybór
                    </button>
                    <button type="button" onclick="closeVehiclesModal()" class="btn-kt-light w-full sm:w-auto mt-3 sm:mt-0">
                        Anuluj
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div id="image-preview-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="image-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" onclick="closeImagePreview()"></div>

            <!-- Center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="image-modal-title">
                            Podgląd zdjęcia
                        </h3>
                        <button type="button" onclick="closeImagePreview()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="text-center">
                        <img id="preview-image" src="" alt="" class="max-w-full max-h-96 mx-auto rounded-lg">
                        <p id="preview-filename" class="mt-2 text-sm text-gray-500 dark:text-gray-400"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedTeamMembers = [];
        let selectedVehicles = [];

        // Initialize team display on page load
        document.addEventListener('DOMContentLoaded', function() {
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
            
            // Initialize vehicle selection with current task vehicles
            const taskVehicles = @json($task->vehicles->pluck('id') ?? []);
            selectedVehicles = taskVehicles;
            updateVehiclesInputs();
            updateVehiclesDisplay();
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

        // Team selection function for Admin/Kierownik
        function selectTeam() {
            const teamSelect = document.getElementById('team_id');
            const selectedOption = teamSelect.selectedOptions[0];
            
            if (!selectedOption || !selectedOption.value) {
                // Don't clear existing data when deselecting team
                return;
            }
            
            // Get team data from option attributes
            const vehicleId = selectedOption.dataset.vehicle;
            const membersNames = selectedOption.dataset.membersNames;
            const leaderName = selectedOption.dataset.leader;
            
            // Auto-select vehicle if team has one
            if (vehicleId) {
                selectedVehicles = [parseInt(vehicleId)];
                updateVehiclesInputs();
                updateVehiclesDisplay();
            }
            
            // Auto-populate team members
            if (membersNames) {
                document.getElementById('team').value = membersNames;
                // Update display with tags
                const memberNames = membersNames.split(', ');
                document.getElementById('team-display').innerHTML = 
                    '<div class="flex flex-wrap gap-2">' + 
                    memberNames.map(name => 
                        '<span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">' +
                        '<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">' +
                        '<path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>' +
                        '</svg>' + name + '</span>'
                    ).join('') + 
                    '</div>';
            }
        }

        // Vehicle selection functions
        function openVehiclesModal() {
            document.getElementById('vehicles-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Restore previous selections
            const checkboxes = document.querySelectorAll('.vehicle-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectedVehicles.includes(parseInt(checkbox.value));
            });
        }

        function closeVehiclesModal() {
            document.getElementById('vehicles-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function saveVehiclesSelection() {
            const checkboxes = document.querySelectorAll('.vehicle-checkbox:checked');
            selectedVehicles = Array.from(checkboxes).map(cb => parseInt(cb.value));
            
            // Update hidden inputs
            updateVehiclesInputs();
            
            // Update display
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
                const checkbox = document.querySelector(`input.vehicle-checkbox[value="${vehicleId}"]`);
                if (checkbox) {
                    const name = checkbox.dataset.name;
                    const registration = checkbox.dataset.registration;
                    html += `
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"></path>
                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1V8a1 1 0 00-1-1h-3z"></path>
                            </svg>
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

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeTeamModal();
                closeVehiclesModal();
                closeImagePreview();
            }
        });

        // Image handling functions
        function showImagePreview(imageSrc, filename) {
            document.getElementById('preview-image').src = imageSrc;
            document.getElementById('preview-filename').textContent = filename;
            document.getElementById('image-preview-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImagePreview() {
            document.getElementById('image-preview-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function previewNewImages(input) {
            const previewContainer = document.getElementById('new-images-preview');
            previewContainer.innerHTML = '';
            
            if (input.files && input.files.length > 0) {
                previewContainer.classList.remove('hidden');
                
                Array.from(input.files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const div = document.createElement('div');
                            div.className = 'relative group';
                            div.innerHTML = `
                                <img src="${e.target.result}" 
                                     alt="${file.name}" 
                                     class="w-full h-24 object-cover rounded-lg cursor-pointer hover:opacity-75 transition-opacity"
                                     onclick="showImagePreview('${e.target.result}', '${file.name}')">
                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 rounded-b-lg truncate">
                                    ${file.name}
                                </div>
                            `;
                            previewContainer.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                previewContainer.classList.add('hidden');
            }
        }

        function removeCurrentImage(index) {
            if (confirm('Czy na pewno chcesz usunąć to zdjęcie?')) {
                fetch(`/tasks/{{ $task->id }}/images/${index}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the image element from DOM
                        const imageElement = document.querySelector(`.current-image[data-index="${index}"]`);
                        if (imageElement) {
                            imageElement.remove();
                        }
                        
                        // Update indices for remaining images
                        const remainingImages = document.querySelectorAll('.current-image');
                        remainingImages.forEach((img, newIndex) => {
                            if (parseInt(img.dataset.index) > index) {
                                img.dataset.index = parseInt(img.dataset.index) - 1;
                                const removeButton = img.querySelector('button');
                                removeButton.setAttribute('onclick', `removeCurrentImage(${parseInt(img.dataset.index)})`);
                            }
                        });
                        
                        // Hide current images section if no images left
                        if (remainingImages.length === 0) {
                            const currentImagesSection = document.getElementById('current-images').closest('.mb-4');
                            if (currentImagesSection) {
                                currentImagesSection.style.display = 'none';
                            }
                        }
                    } else {
                        alert('Błąd podczas usuwania zdjęcia.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Błąd podczas usuwania zdjęcia.');
                });
            }
        }

    </script>
</x-app-layout>