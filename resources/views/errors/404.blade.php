<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('Błąd 404 - Nie znaleziono') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="kt-card">
                <div class="kt-card-body text-center">
                    <!-- Error Icon -->
                    <div class="mb-8">
                        <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-yellow-100 dark:bg-yellow-900">
                            <svg class="h-12 w-12 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m6-8a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div class="mb-8">
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-gray-100 mb-4">404</h1>
                        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-4">Strona nie istnieje</h2>
                        <p class="text-lg text-gray-600 dark:text-gray-400 mb-2">
                            Nie można znaleźć strony, której szukasz.
                        </p>
                        <p class="text-base text-gray-500 dark:text-gray-500">
                            Sprawdź adres URL lub wróć do strony głównej.
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
                        
                        <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition duration-200">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Zadania
                        </a>
                    </div>

                    <!-- Search Section -->
                    <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <div class="max-w-md mx-auto">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Czego szukasz?</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <a href="{{ route('tasks.index') }}" class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition duration-200">
                                    <div class="text-gray-900 dark:text-gray-100 font-medium">Zadania</div>
                                    <div class="text-gray-500 dark:text-gray-400">Przeglądaj zadania</div>
                                </a>
                                @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isKierownik() || auth()->user()->isLider()))
                                    <a href="{{ route('tasks.create') }}" class="p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition duration-200">
                                        <div class="text-gray-900 dark:text-gray-100 font-medium">Nowe zadanie</div>
                                        <div class="text-gray-500 dark:text-gray-400">Utwórz zadanie</div>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>