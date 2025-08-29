<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Dodaj użytkownika
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Utworz nowe konto użytkownika
                </p>
            </div>
            <div class="mt-3 sm:mt-0">
                <a href="{{ route('users.index') }}" class="btn-kt-secondary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="kt-card">
                <div class="kt-card-body">
                    <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Imię i nazwisko -->
                            <div>
                                <label for="name" class="form-kt-label">
                                    Imię i nazwisko <span class="text-red-500">*</span>
                                </label>
                                <input id="name" 
                                       class="form-kt-control @error('name') border-red-500 @enderror" 
                                       type="text" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       required 
                                       autofocus />
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="form-kt-label">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input id="email" 
                                       class="form-kt-control @error('email') border-red-500 @enderror" 
                                       type="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required />
                                @error('email')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Rola -->
                            <div>
                                <label for="role" class="form-kt-label">
                                    Rola <span class="text-red-500">*</span>
                                </label>
                                <select id="role" 
                                        name="role" 
                                        class="form-kt-select @error('role') border-red-500 @enderror" 
                                        required>
                                    <option value="">Wybierz rolę</option>
                                    <option value="pracownik" {{ old('role') === 'pracownik' ? 'selected' : '' }}>Pracownik</option>
                                    <option value="lider" {{ old('role') === 'lider' ? 'selected' : '' }}>Lider</option>
                                    <option value="kierownik" {{ old('role') === 'kierownik' ? 'selected' : '' }}>Kierownik</option>
                                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                                </select>
                                @error('role')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Pracownik - widzi zadania zespołowe • Lider - zarządza zadaniami • Kierownik - przegląda wszystko • Admin - pełne uprawnienia
                                </p>
                            </div>

                            <div></div> <!-- Empty div for grid spacing -->

                            <!-- Hasło -->
                            <div>
                                <label for="password" class="form-kt-label">
                                    Hasło <span class="text-red-500">*</span>
                                </label>
                                <input id="password" 
                                       class="form-kt-control @error('password') border-red-500 @enderror"
                                       type="password"
                                       name="password"
                                       required />
                                @error('password')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Hasło musi mieć minimum 8 znaków
                                </p>
                            </div>

                            <!-- Potwierdź hasło -->
                            <div>
                                <label for="password_confirmation" class="form-kt-label">
                                    Potwierdź hasło <span class="text-red-500">*</span>
                                </label>
                                <input id="password_confirmation" 
                                       class="form-kt-control @error('password_confirmation') border-red-500 @enderror"
                                       type="password"
                                       name="password_confirmation"
                                       required />
                                @error('password_confirmation')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex space-x-3">
                                <a href="{{ route('users.index') }}" class="btn-kt-light">
                                    Anuluj
                                </a>
                                <button type="submit" class="btn-kt-primary">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Utwórz użytkownika
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>