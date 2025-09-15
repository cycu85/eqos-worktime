<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('status') === 'no-permission')
                <div class="p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-300 rounded">
                    <strong>Brak uprawnień:</strong> Tylko administrator może edytować dane profilu. Możesz jedynie zmienić hasło.
                </div>
            @endif

            @if(!auth()->user()->isAdmin())
                <div class="p-4 bg-blue-100 dark:bg-blue-900 border border-blue-400 dark:border-blue-600 text-blue-700 dark:text-blue-300 rounded">
                    <strong>Informacja:</strong> Twoja rola pozwala jedynie na zmianę hasła. Aby zmienić inne dane profilu, skontaktuj się z administratorem.
                </div>
            @endif

            @if(auth()->user()->isAdmin())
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
