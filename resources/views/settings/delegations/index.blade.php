<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Ustawienia delegacji
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Konfiguracja stawek diet i domyślnych wartości dla delegacji
                </p>
            </div>
            <div class="mt-3 sm:mt-0">
                <a href="{{ route('settings.index') }}" class="btn-kt-secondary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót do ustawień
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Diet Rates Settings -->
            <div class="kt-card mb-6">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Stawki diet</h3>
                </div>
                <div class="kt-card-body">
                    <form method="POST" action="{{ route('settings.delegations.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="delegation_rate_poland" class="form-kt-label">
                                    Stawka diety krajowej (PLN) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="delegation_rate_poland" 
                                       id="delegation_rate_poland" 
                                       class="form-kt-control @error('delegation_rate_poland') border-red-500 @enderror" 
                                       value="{{ old('delegation_rate_poland', $settings['delegation_rate_poland']) }}"
                                       step="0.01"
                                       min="0"
                                       required>
                                @error('delegation_rate_poland')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Dzienna stawka diety dla delegacji w Polsce
                                </p>
                            </div>

                            <div>
                                <label for="delegation_rate_abroad" class="form-kt-label">
                                    Stawka diety zagranicznej (EUR) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="delegation_rate_abroad" 
                                       id="delegation_rate_abroad" 
                                       class="form-kt-control @error('delegation_rate_abroad') border-red-500 @enderror" 
                                       value="{{ old('delegation_rate_abroad', $settings['delegation_rate_abroad']) }}"
                                       step="0.01"
                                       min="0"
                                       required>
                                @error('delegation_rate_abroad')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Dzienna stawka diety dla delegacji zagranicznych (w EUR, przeliczana po kursie NBP)
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Zapisz stawki
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Default Values Settings -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Domyślne wartości</h3>
                </div>
                <div class="kt-card-body">
                    <form method="POST" action="{{ route('settings.delegations.update-defaults') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="default_project" class="form-kt-label">Domyślny projekt</label>
                                <input type="text" 
                                       name="default_project" 
                                       id="default_project" 
                                       class="form-kt-control @error('default_project') border-red-500 @enderror" 
                                       value="{{ old('default_project', $settings['default_project']) }}"
                                       maxlength="255">
                                @error('default_project')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Automatycznie wypełniane pole projekt w nowych delegacjach
                                </p>
                            </div>

                            <div>
                                <label for="default_city" class="form-kt-label">Domyślna miejscowość</label>
                                <input type="text" 
                                       name="default_city" 
                                       id="default_city" 
                                       class="form-kt-control @error('default_city') border-red-500 @enderror" 
                                       value="{{ old('default_city', $settings['default_city']) }}"
                                       maxlength="255">
                                @error('default_city')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Automatycznie wypełniane pole miejscowość w nowych delegacjach
                                </p>
                            </div>

                            <div>
                                <label for="default_country" class="form-kt-label">Domyślny kraj</label>
                                <select name="default_country" 
                                        id="default_country" 
                                        class="form-kt-select @error('default_country') border-red-500 @enderror">
                                    <option value="">Wybierz domyślny kraj</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" {{ old('default_country', $settings['default_country']) == $country ? 'selected' : '' }}>
                                            {{ $country }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('default_country')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Automatycznie wybierany kraj w nowych delegacjach
                                </p>
                            </div>

                            <div class="md:col-span-2">
                                <label for="default_travel_purpose" class="form-kt-label">Domyślny cel podróży</label>
                                <textarea name="default_travel_purpose" 
                                          id="default_travel_purpose" 
                                          rows="3"
                                          class="form-kt-control @error('default_travel_purpose') border-red-500 @enderror">{{ old('default_travel_purpose', $settings['default_travel_purpose']) }}</textarea>
                                @error('default_travel_purpose')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Automatycznie wypełniane pole cel podróży w nowych delegacjach
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Zapisz domyślne wartości
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>