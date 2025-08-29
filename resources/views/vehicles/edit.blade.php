<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Edytuj pojazd
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $vehicle->name }} ({{ $vehicle->registration }})
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('vehicles.show', $vehicle) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót do szczegółów
                </a>
                <a href="{{ route('vehicles.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    Lista pojazdów
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('vehicles.update', $vehicle) }}">
                        @csrf
                        @method('PATCH')

                        <!-- Registration -->
                        <div class="mb-6">
                            <x-input-label for="registration" :value="__('Numer rejestracyjny')" />
                            <x-text-input id="registration" class="block mt-1 w-full" type="text" name="registration" :value="old('registration', $vehicle->registration)" required autofocus />
                            <x-input-error :messages="$errors->get('registration')" class="mt-2" />
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Numer musi być unikalny w systemie</p>
                        </div>

                        <!-- Name -->
                        <div class="mb-6">
                            <x-input-label for="name" :value="__('Nazwa pojazdu')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $vehicle->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <x-input-label for="description" :value="__('Opis pojazdu (opcjonalnie)')" />
                            <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $vehicle->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Is Active -->
                        <div class="mb-6">
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $vehicle->is_active) ? 'checked' : '' }} class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-indigo-600">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="is_active" class="font-medium text-gray-700 dark:text-gray-300">Pojazd aktywny</label>
                                    <p class="text-gray-500 dark:text-gray-400">Tylko aktywne pojazdy są dostępne przy tworzeniu zadań</p>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                        </div>

                        <!-- Current Info -->
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Informacje o pojeździe</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
                                <div>
                                    <span class="font-medium">Utworzony:</span> {{ $vehicle->created_at->format('d.m.Y H:i') }}
                                </div>
                                <div>
                                    <span class="font-medium">Ostatnia aktualizacja:</span> {{ $vehicle->updated_at->format('d.m.Y H:i') }}
                                </div>
                                <div>
                                    <span class="font-medium">Wszystkich zadań:</span> {{ $vehicle->tasks->count() }}
                                </div>
                                <div>
                                    <span class="font-medium">Aktywnych zadań:</span> {{ $vehicle->tasks->where('status', 'in_progress')->count() }}
                                </div>
                            </div>
                        </div>

                        <!-- Warning for tasks -->
                        @if($vehicle->tasks->count() > 0)
                            <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3 text-sm text-yellow-700 dark:text-yellow-300">
                                        <p><strong>Uwaga:</strong> Ten pojazd ma {{ $vehicle->tasks->count() }} {{ Str::plural('przypisane zadanie', $vehicle->tasks->count()) }}. Zmiana statusu na "nieaktywny" ukryje pojazd z listy przy tworzeniu nowych zadań, ale nie wpłynie na istniejące zadania.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center justify-end">
                            <a href="{{ route('vehicles.show', $vehicle) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-700 focus:bg-gray-400 dark:focus:bg-gray-700 active:bg-gray-500 dark:active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 mr-4">
                                Anuluj
                            </a>

                            <x-primary-button class="ms-4">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('Zapisz zmiany') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>