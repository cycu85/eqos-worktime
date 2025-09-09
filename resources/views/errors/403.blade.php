<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('Błąd 403 - Brak dostępu') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="kt-card">
                <div class="kt-card-body text-center">
                    <!-- Error Icon -->
                    <div class="mb-8">
                        <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-red-100 dark:bg-red-900">
                            <svg class="h-12 w-12 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div class="mb-8">
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-4">403</h1>
                        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">Brak uprawnień</h2>
                        <p class="text-lg text-gray-600 dark:text-gray-400 mb-2">
                            Nie masz uprawnień do przeglądania tej strony.
                        </p>
                        <p class="text-base text-gray-500 dark:text-gray-500">
                            Skontaktuj się z administratorem jeśli uważasz, że to błąd.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-4 sm:space-y-0 sm:space-x-4 sm:flex sm:justify-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition duration-200">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Strona główna
                        </a>
                        
                        <button onclick="history.back()" class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition duration-200">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Wróć
                        </button>
                    </div>

                    <!-- Help Section -->
                    <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <div class="max-w-md mx-auto">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Potrzebujesz pomocy?</h3>
                            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex items-center justify-center">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span>Twoja rola: {{ ucfirst(auth()->user()->role ?? 'niezalogowany') }}</span>
                                </div>
                                @if(auth()->check())
                                    <div class="flex items-center justify-center">
                                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Zalogowany jako: {{ auth()->user()->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>