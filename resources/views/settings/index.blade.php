<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    {{ __('Ustawienia aplikacji') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Konfiguracja nazwy i logo aplikacji (tylko Admin)
                </p>
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

            <!-- Quick Actions -->
            <div class="kt-card mb-6">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Zarządzanie systemem</h3>
                </div>
                <div class="kt-card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="{{ route('settings.task-types.index') }}" 
                           class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors duration-200 group">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                    Typy zadań
                                </h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Zarządzaj rodzajami zadań
                                </p>
                            </div>
                        </a>

                        <a href="{{ route('settings.delegations.index') }}" 
                           class="flex items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors duration-200 group">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center group-hover:bg-green-200 dark:group-hover:bg-green-900/50 transition-colors duration-200">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-green-600 dark:group-hover:text-green-400">
                                    Ustawienia delegacji
                                </h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Stawki diet i domyślne wartości
                                </p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Settings Form -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Podstawowe ustawienia</h3>
                </div>
                <div class="kt-card-body">
                    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- App Name -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="lg:col-span-2">
                                <label for="app_name" class="form-kt-label">
                                    Nazwa aplikacji <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="app_name" 
                                       id="app_name" 
                                       class="form-kt-control @error('app_name') border-red-500 @enderror" 
                                       value="{{ old('app_name', $settings['app_name']) }}"
                                       required>
                                @error('app_name')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Ta nazwa będzie wyświetlana w pasku tytułu przeglądarki i w nagłówkach aplikacji.
                                </p>
                            </div>
                        </div>

                        <!-- Current Logo -->
                        @if($settings['logo_path'])
                            <div>
                                <label class="form-kt-label">Aktualne logo</label>
                                <div class="mt-2 flex flex-col sm:flex-row sm:items-start sm:space-x-4 space-y-4 sm:space-y-0">
                                    <div class="flex-shrink-0">
                                        <img src="{{ Storage::url($settings['logo_path']) }}" 
                                             alt="Current Logo" 
                                             class="h-16 w-auto max-w-xs object-contain bg-gray-50 dark:bg-gray-800 rounded-lg p-2 border border-gray-200 dark:border-gray-700">
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <input type="checkbox" 
                                                   id="remove_logo" 
                                                   name="remove_logo" 
                                                   value="1"
                                                   class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600 rounded">
                                            <label for="remove_logo" class="text-sm font-medium text-red-600 dark:text-red-400">
                                                Usuń obecne logo
                                            </label>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            Zaznacz to pole, aby usunąć aktualne logo i wrócić do domyślnego.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Logo Upload -->
                        <div>
                            <label for="logo" class="form-kt-label">
                                {{ $settings['logo_path'] ? 'Zmień logo' : 'Wgraj logo' }}
                            </label>
                            <div class="mt-2">
                                <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg hover:border-gray-400 dark:hover:border-gray-500 transition-colors duration-200">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                            <label for="logo" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 px-2">
                                                <span>Wgraj plik</span>
                                                <input id="logo" 
                                                       name="logo" 
                                                       type="file" 
                                                       class="sr-only"
                                                       accept="image/jpeg,image/png,image/jpg,image/gif,image/svg+xml">
                                            </label>
                                            <p class="pl-1">lub przeciągnij i upuść</p>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            PNG, JPG, GIF, SVG do 2MB
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @error('logo')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Zalecane wymiary: 200x50px lub podobny stosunek szerokości do wysokości. Logo będzie automatycznie skalowane.
                            </p>
                        </div>

                        <!-- Preview uploaded file -->
                        <div id="file-preview" class="hidden">
                            <label class="form-kt-label">Podgląd nowego logo</label>
                            <div class="mt-2">
                                <img id="preview-image" 
                                     src="" 
                                     alt="Preview" 
                                     class="h-16 w-auto max-w-xs object-contain bg-gray-50 dark:bg-gray-800 rounded-lg p-2 border border-gray-200 dark:border-gray-700">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Zapisz ustawienia
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SMTP Settings -->
            <div class="kt-card mt-6">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Ustawienia poczty elektronicznej (SMTP)</h3>
                </div>
                <div class="kt-card-body">
                    <form method="POST" action="{{ route('settings.smtp.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                        Konfiguracja SMTP
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                        <p>Skonfiguruj ustawienia serwera SMTP do wysyłania wiadomości email z aplikacji. Te ustawienia są wymagane do funkcji powiadomień email.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- SMTP Host -->
                            <div>
                                <label for="mail_host" class="form-kt-label">
                                    Serwer SMTP <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="mail_host" 
                                       id="mail_host" 
                                       class="form-kt-control @error('mail_host') border-red-500 @enderror" 
                                       value="{{ old('mail_host', $smtpSettings['mail_host']) }}"
                                       placeholder="smtp.gmail.com"
                                       required>
                                @error('mail_host')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- SMTP Port -->
                            <div>
                                <label for="mail_port" class="form-kt-label">
                                    Port SMTP <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="mail_port" 
                                       id="mail_port" 
                                       class="form-kt-control @error('mail_port') border-red-500 @enderror" 
                                       value="{{ old('mail_port', $smtpSettings['mail_port']) }}"
                                       placeholder="587"
                                       min="1" 
                                       max="65535"
                                       required>
                                @error('mail_port')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- SMTP Username -->
                            <div>
                                <label for="mail_username" class="form-kt-label">
                                    Nazwa użytkownika SMTP
                                </label>
                                <input type="text" 
                                       name="mail_username" 
                                       id="mail_username" 
                                       class="form-kt-control @error('mail_username') border-red-500 @enderror" 
                                       value="{{ old('mail_username', $smtpSettings['mail_username']) }}"
                                       placeholder="twoj-email@gmail.com">
                                @error('mail_username')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- SMTP Password -->
                            <div>
                                <label for="mail_password" class="form-kt-label">
                                    Hasło SMTP
                                </label>
                                <input type="password" 
                                       name="mail_password" 
                                       id="mail_password" 
                                       class="form-kt-control @error('mail_password') border-red-500 @enderror" 
                                       value="{{ old('mail_password', $smtpSettings['mail_password']) }}"
                                       placeholder="hasło lub klucz aplikacji">
                                @error('mail_password')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Encryption -->
                            <div>
                                <label for="mail_encryption" class="form-kt-label">
                                    Szyfrowanie
                                </label>
                                <select name="mail_encryption" 
                                        id="mail_encryption" 
                                        class="form-kt-select @error('mail_encryption') border-red-500 @enderror">
                                    <option value="tls" {{ old('mail_encryption', $smtpSettings['mail_encryption']) == 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ old('mail_encryption', $smtpSettings['mail_encryption']) == 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="" {{ old('mail_encryption', $smtpSettings['mail_encryption']) == '' ? 'selected' : '' }}>Brak</option>
                                </select>
                                @error('mail_encryption')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- From Address -->
                            <div>
                                <label for="mail_from_address" class="form-kt-label">
                                    Adres nadawcy <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                       name="mail_from_address" 
                                       id="mail_from_address" 
                                       class="form-kt-control @error('mail_from_address') border-red-500 @enderror" 
                                       value="{{ old('mail_from_address', $smtpSettings['mail_from_address']) }}"
                                       placeholder="noreply@twoja-domena.com"
                                       required>
                                @error('mail_from_address')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- From Name -->
                            <div>
                                <label for="mail_from_name" class="form-kt-label">
                                    Nazwa nadawcy <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="mail_from_name" 
                                       id="mail_from_name" 
                                       class="form-kt-control @error('mail_from_name') border-red-500 @enderror" 
                                       value="{{ old('mail_from_name', $smtpSettings['mail_from_name']) }}"
                                       placeholder="EQOS WorkTime"
                                       required>
                                @error('mail_from_name')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Test Email -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">Test połączenia</h4>
                            <div class="flex items-end space-x-4">
                                <div class="flex-1">
                                    <label for="test_email" class="form-kt-label">
                                        Adres testowy
                                    </label>
                                    <input type="email" 
                                           name="test_email" 
                                           id="test_email" 
                                           class="form-kt-control" 
                                           placeholder="test@example.com">
                                </div>
                                <button type="button" 
                                        onclick="sendTestEmail()" 
                                        class="btn-kt-secondary">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                    </svg>
                                    Wyślij test
                                </button>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Wyślij testową wiadomość aby sprawdzić konfigurację SMTP.
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-6 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Zapisz ustawienia SMTP
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // File preview functionality
        document.getElementById('logo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('file-preview');
            const previewImage = document.getElementById('preview-image');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                preview.classList.add('hidden');
            }
        });

        // Remove logo checkbox functionality
        document.getElementById('remove_logo')?.addEventListener('change', function(e) {
            const logoInput = document.getElementById('logo');
            const preview = document.getElementById('file-preview');
            
            if (e.target.checked) {
                logoInput.disabled = true;
                logoInput.value = '';
                preview.classList.add('hidden');
            } else {
                logoInput.disabled = false;
            }
        });

        // Test email functionality
        function sendTestEmail() {
            const testEmail = document.getElementById('test_email').value;
            const button = event.target;
            
            if (!testEmail) {
                alert('Podaj adres email do testu');
                return;
            }

            // Disable button
            button.disabled = true;
            button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"></path></svg>Wysyłanie...';

            fetch('{{ route("settings.smtp.test") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    test_email: testEmail,
                    // Include current form values for testing
                    mail_host: document.getElementById('mail_host').value,
                    mail_port: document.getElementById('mail_port').value,
                    mail_username: document.getElementById('mail_username').value,
                    mail_password: document.getElementById('mail_password').value,
                    mail_encryption: document.getElementById('mail_encryption').value,
                    mail_from_address: document.getElementById('mail_from_address').value,
                    mail_from_name: document.getElementById('mail_from_name').value,
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Email testowy został wysłany pomyślnie!');
                } else {
                    alert('Błąd wysyłania emaila: ' + (data.message || 'Nieznany błąd'));
                }
            })
            .catch(error => {
                alert('Błąd połączenia: ' + error.message);
            })
            .finally(() => {
                // Re-enable button
                button.disabled = false;
                button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path></svg>Wyślij test';
            });
        }
    </script>
</x-app-layout>