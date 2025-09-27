<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Szczegóły nieobecności
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $absence->user->name }} - {{ $absence->start_date->format('d.m.Y') }} do {{ $absence->end_date->format('d.m.Y') }}
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('absences.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót do listy
                </a>

                @can('update', $absence)
                    <a href="{{ route('absences.edit', $absence) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                        Edytuj
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Główne informacje -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Podstawowe informacje</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Użytkownik</h4>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $absence->user->name }}
                                <span class="text-gray-500 dark:text-gray-400">({{ $absence->user->role }})</span>
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Typ nieobecności</h4>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                                    @if($absence->type === 'urlop') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                    @elseif($absence->type === 'choroba') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                    @elseif($absence->type === 'delegacja') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @elseif($absence->type === 'szkolenie') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                    @if($absence->type === 'urlop') Urlop
                                    @elseif($absence->type === 'choroba') Choroba
                                    @elseif($absence->type === 'delegacja') Delegacja
                                    @elseif($absence->type === 'szkolenie') Szkolenie
                                    @else Inne @endif
                                </span>
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Okres nieobecności</h4>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                {{ $absence->start_date->format('d.m.Y') }} - {{ $absence->end_date->format('d.m.Y') }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $absence->getDaysCount() }} {{ $absence->getDaysCount() === 1 ? 'dzień' : ($absence->getDaysCount() < 5 ? 'dni' : 'dni') }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</h4>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                                    @if($absence->status === 'oczekujaca') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                    @elseif($absence->status === 'zatwierdzona') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                    @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                    @if($absence->status === 'oczekujaca') Oczekująca
                                    @elseif($absence->status === 'zatwierdzona') Zatwierdzona
                                    @else Odrzucona @endif
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Opis -->
                @if($absence->description)
                    <div class="px-6 py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Opis/Powód</h4>
                        <p class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $absence->description }}</p>
                    </div>
                @endif

                <!-- Informacje o zatwierdzeniu -->
                @if($absence->approver)
                    <div class="px-6 py-4 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
                            {{ $absence->status === 'zatwierdzona' ? 'Zatwierdzenie' : 'Odrzucenie' }}
                        </h4>
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                            <p><strong>Przez:</strong> {{ $absence->approver->name }}</p>
                            <p><strong>Data:</strong> {{ $absence->approved_at->format('d.m.Y H:i') }}</p>
                        </div>
                    </div>
                @endif

                <!-- Meta informacje -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700">
                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Informacje systemowe</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
                        <div>
                            <p><strong>Utworzono:</strong> {{ $absence->created_at->format('d.m.Y H:i') }}</p>
                            <p><strong>Ostatnia modyfikacja:</strong> {{ $absence->updated_at->format('d.m.Y H:i') }}</p>
                        </div>
                        <div>
                            <p><strong>ID nieobecności:</strong> #{{ $absence->id }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Akcje zatwierdzania dla kierowników/adminów -->
            @can('approve', $absence)
                @if($absence->status === 'oczekujaca')
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white dark:bg-gray-800">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Akcje zatwierdzania</h3>
                            <div class="flex space-x-4">
                                <form method="POST" action="{{ route('absences.approve', $absence) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" onclick="return confirm('Czy na pewno chcesz zatwierdzić tę nieobecność?')">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Zatwierdź nieobecność
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('absences.reject', $absence) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" onclick="return confirm('Czy na pewno chcesz odrzucić tę nieobecność?')">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                        Odrzuć nieobecność
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endcan
        </div>
    </div>
</x-app-layout>