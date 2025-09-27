<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Nieobecności
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Zarządzanie nieobecnościami pracowników
                </p>
            </div>

            <div class="mt-3 sm:mt-0 flex space-x-3">
                @can('create', App\Models\UserAbsence::class)
                    <a href="{{ route('absences.create') }}" class="btn-kt-primary">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        Dodaj nieobecność
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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

            <!-- Search and Filter Card -->
            <div class="kt-card mb-6">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Wyszukiwanie i filtrowanie</h3>
                </div>
                <div class="kt-card-body">
                    <form method="GET" action="{{ route('absences.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- User Filter -->
                            @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                            <div>
                                <label class="form-kt-label">Użytkownik</label>
                                <select name="user_id" class="form-kt-select">
                                    <option value="">Wszyscy użytkownicy</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <!-- Type Filter -->
                            <div>
                                <label class="form-kt-label">Typ</label>
                                <select name="type" class="form-kt-select">
                                    <option value="">Wszystkie typy</option>
                                    @foreach($types as $value => $label)
                                        <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label class="form-kt-label">Status</label>
                                <select name="status" class="form-kt-select">
                                    <option value="">Wszystkie statusy</option>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date From -->
                            <div>
                                <label class="form-kt-label">Data od</label>
                                <input type="date"
                                       name="date_from"
                                       value="{{ request('date_from') }}"
                                       class="form-kt-control">
                            </div>

                            <!-- Date To -->
                            <div>
                                <label class="form-kt-label">Data do</label>
                                <input type="date"
                                       name="date_to"
                                       value="{{ request('date_to') }}"
                                       class="form-kt-control">
                            </div>

                        <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                </svg>
                                Szukaj
                            </button>

                            <a href="{{ route('absences.index') }}" class="btn-kt-light">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                </svg>
                                Wyczyść filtry
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Calendar View -->
            <div class="kt-card mb-6" id="calendar-section">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Kalendarz nieobecności</h3>
                    <div class="flex items-center justify-between w-full">
                        <!-- Navigation Left -->
                        <div class="flex items-center space-x-2">
                            <button id="prev-period" class="btn-kt-light btn-sm">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                            <span id="current-period" class="text-lg font-semibold text-gray-900 dark:text-gray-100 px-4 min-w-[200px] text-left"></span>
                            <button id="next-period" class="btn-kt-light btn-sm">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- View Toggle Center -->
                        <div class="calendar-view-toggle">
                            <button id="month-view" class="btn-kt-primary btn-sm">Miesiąc</button>
                            <button id="week-view" class="btn-kt-light btn-sm">Tydzień</button>
                            <button id="day-view" class="btn-kt-light btn-sm">Dzień</button>
                        </div>

                        <!-- Today Button Right -->
                        <div>
                            <button id="today-btn" class="btn-kt-secondary btn-sm">Dziś</button>
                        </div>
                    </div>
                </div>
                <div class="kt-card-body">
                    <!-- Month View -->
                    <div id="month-view-container">
                        <!-- Combined calendar with week numbers -->
                        <div class="grid grid-cols-8 gap-1 mb-2" id="month-headers">
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Tyd.</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Pon</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Wt</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Śr</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Czw</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Pt</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Sob</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Nie</div>
                        </div>
                        <div id="calendar-grid" class="grid grid-cols-8 gap-1">
                            <!-- Calendar will be generated by JavaScript -->
                        </div>
                    </div>

                    <!-- Week View -->
                    <div id="week-view-container" class="hidden">
                        <div class="grid grid-cols-8 gap-1 mb-2">
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Godz.</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Pon</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Wt</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Śr</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Czw</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Pt</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Sob</div>
                            <div class="p-2 text-center text-sm font-medium text-gray-600 dark:text-gray-400">Nie</div>
                        </div>
                        <div id="week-grid" class="grid grid-cols-8 gap-1">
                            <!-- Week view will be generated by JavaScript -->
                        </div>
                    </div>

                    <!-- Day View -->
                    <div id="day-view-container" class="hidden">
                        <div id="day-grid">
                            <!-- Day view will be generated by JavaScript -->
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Typy nieobecności:</h4>
                                <div class="flex flex-wrap gap-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-4 h-4 bg-blue-500 rounded"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Urlop</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-4 h-4 bg-red-500 rounded"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Choroba</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-4 h-4 bg-purple-500 rounded"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Szkolenie</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-4 h-4 bg-gray-500 rounded"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Inne</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Statusy nieobecności:</h4>
                                <div class="flex flex-wrap gap-4">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-4 h-4 bg-blue-500 rounded"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Zatwierdzona</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-4 h-4 bg-blue-500 bg-opacity-50 rounded"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Oczekująca</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-4 h-4 bg-gray-400 rounded"></div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Odrzucona</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista nieobecności -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Lista nieobecności</h3>
                </div>
                <div class="kt-card-body">
                    @if($absences->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Użytkownik
                                        </th>
                                        @endif
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Okres
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Typ
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Dni
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Zatwierdzona przez
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Akcje</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($absences as $absence)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $absence->user->name }}
                                            </td>
                                            @endif
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $absence->start_date->format('d.m.Y') }} - {{ $absence->end_date->format('d.m.Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($absence->type === 'urlop') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                    @elseif($absence->type === 'choroba') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                    @elseif($absence->type === 'delegacja') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                    @elseif($absence->type === 'szkolenie') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                    {{ $types[$absence->type] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    @if($absence->status === 'oczekujaca') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                    @elseif($absence->status === 'zatwierdzona') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                    @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                                    {{ $statuses[$absence->status] }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                {{ $absence->getDaysCount() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                                @if($absence->approver)
                                                    {{ $absence->approver->name }}
                                                    <br>
                                                    <span class="text-xs text-gray-400 dark:text-gray-500">
                                                        {{ $absence->approved_at->format('d.m.Y H:i') }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex items-center justify-end space-x-2">
                                                    @can('view', $absence)
                                                        <a href="{{ route('absences.show', $absence) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="Zobacz szczegóły">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                            </svg>
                                                        </a>
                                                    @endcan

                                                    @can('update', $absence)
                                                        <a href="{{ route('absences.edit', $absence) }}" class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300" title="Edytuj">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                                            </svg>
                                                        </a>
                                                    @endcan

                                                    @can('approve', $absence)
                                                        @if($absence->status === 'oczekujaca')
                                                            <form method="POST" action="{{ route('absences.approve', $absence) }}" class="inline">
                                                                @csrf
                                                                <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="Zatwierdź" onclick="return confirm('Czy na pewno chcesz zatwierdzić tę nieobecność?')">
                                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                            <form method="POST" action="{{ route('absences.reject', $absence) }}" class="inline">
                                                                @csrf
                                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Odrzuć" onclick="return confirm('Czy na pewno chcesz odrzucić tę nieobecność?')">
                                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endcan

                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginacja -->
                        <div class="mt-6">
                            {{ $absences->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0v1m6-1v1m-6 0H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V10a2 2 0 00-2-2h-6m-6 0V7"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Brak nieobecności</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Nie znaleziono żadnych nieobecności spełniających kryteria wyszukiwania.</p>
                            @can('create', App\Models\UserAbsence::class)
                                <div class="mt-6">
                                    <a href="{{ route('absences.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Dodaj pierwszą nieobecność
                                    </a>
                                </div>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Absences data for calendar - prepared in controller
        const absencesData = @json($calendarAbsences);

        let currentDate = new Date();
        let currentView = 'month'; // month, week, day

        // Function to get ISO week number
        function getWeekNumber(date) {
            const d = new Date(date.getTime());
            d.setHours(0, 0, 0, 0);
            d.setDate(d.getDate() + 4 - (d.getDay() || 7));
            const yearStart = new Date(d.getFullYear(), 0, 1);
            const weekNo = Math.ceil(((d - yearStart) / 86400000 + 1) / 7);
            return weekNo;
        }

        // Function to update period display
        function updatePeriodDisplay() {
            const periodElement = document.getElementById('current-period');

            if (currentView === 'month') {
                const monthNames = ['Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec',
                                  'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'];
                periodElement.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
            } else if (currentView === 'week') {
                const weekNum = getWeekNumber(currentDate);
                const startOfWeek = new Date(currentDate);
                startOfWeek.setDate(currentDate.getDate() - currentDate.getDay() + 1);
                const endOfWeek = new Date(startOfWeek);
                endOfWeek.setDate(startOfWeek.getDate() + 6);

                periodElement.textContent = `Tydzień ${weekNum} (${startOfWeek.getDate()}.${startOfWeek.getMonth()+1} - ${endOfWeek.getDate()}.${endOfWeek.getMonth()+1}.${endOfWeek.getFullYear()})`;
            } else if (currentView === 'day') {
                const dayNames = ['Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota'];
                periodElement.textContent = `${dayNames[currentDate.getDay()]}, ${currentDate.getDate()}.${currentDate.getMonth()+1}.${currentDate.getFullYear()}`;
            }
        }

        // Function to get absences for a specific date
        function getAbsencesForDate(date) {
            // Formatuj datę w lokalnej strefie czasowej, nie UTC
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const dateStr = `${year}-${month}-${day}`;

            return absencesData.filter(absence => {
                // Uwzględnij, że end_date ma już dodany jeden dzień w kontrolerze
                return dateStr >= absence.start && dateStr < absence.end;
            });
        }

        // Function to render month view
        function renderMonthView() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDay = (firstDay.getDay() + 6) % 7; // Monday = 0

            const grid = document.getElementById('calendar-grid');
            grid.innerHTML = '';

            // Calculate total rows needed
            const totalCells = startingDay + daysInMonth;
            const rows = Math.ceil(totalCells / 7);

            // Generate calendar with week numbers
            let currentWeekStart = new Date(year, month, 1 - startingDay);
            let dayIndex = 0;

            for (let row = 0; row < rows; row++) {
                // Add week number cell
                const weekCell = document.createElement('div');
                weekCell.className = 'p-2 text-center text-xs text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-700 rounded';
                weekCell.textContent = getWeekNumber(currentWeekStart);
                grid.appendChild(weekCell);

                // Add 7 day cells
                for (let col = 0; col < 7; col++) {
                    const cellDate = new Date(year, month, dayIndex - startingDay + 1);
                    const cell = document.createElement('div');
                    cell.className = 'h-[120px] p-1 border border-gray-100 dark:border-gray-700 rounded-lg overflow-y-auto';

                    if (dayIndex < startingDay || dayIndex >= startingDay + daysInMonth) {
                        // Previous/next month days
                        cell.className += ' bg-gray-50 dark:bg-gray-800 text-gray-400';
                        cell.innerHTML = `<div class="text-sm">${cellDate.getDate()}</div>`;
                    } else {
                        // Current month days
                        const day = dayIndex - startingDay + 1;
                        const isToday = cellDate.toDateString() === new Date().toDateString();
                        const dayAbsences = getAbsencesForDate(cellDate);

                        if (isToday) {
                            cell.className += ' bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800';
                        } else {
                            cell.className += ' bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700';
                        }

                        let cellContent = `<div class="text-sm font-medium mb-1 ${isToday ? 'text-blue-600 dark:text-blue-400' : ''}">${day}</div>`;

                        // Add absences
                        dayAbsences.forEach(absence => {
                            const color = absence.color;
                            cellContent += `
                                <div class="text-xs p-1 mb-1 rounded truncate text-white" style="background-color: ${color};" title="${absence.title}">
                                    ${absence.user} - ${absence.type}
                                </div>
                            `;
                        });

                        cell.innerHTML = cellContent;
                    }

                    grid.appendChild(cell);
                    dayIndex++;
                }

                // Move to next week
                currentWeekStart.setDate(currentWeekStart.getDate() + 7);
            }
        }

        // Function to switch views
        function switchView(view) {
            currentView = view;

            // Update button states
            document.querySelectorAll('.calendar-view-toggle button').forEach(btn => {
                btn.classList.remove('btn-kt-primary');
                btn.classList.add('btn-kt-light');
            });
            document.getElementById(view + '-view').classList.remove('btn-kt-light');
            document.getElementById(view + '-view').classList.add('btn-kt-primary');

            // Show/hide containers
            document.getElementById('month-view-container').classList.toggle('hidden', view !== 'month');
            document.getElementById('week-view-container').classList.toggle('hidden', view !== 'week');
            document.getElementById('day-view-container').classList.toggle('hidden', view !== 'day');

            updatePeriodDisplay();

            if (view === 'month') {
                renderMonthView();
            }
        }

        // Function to navigate periods
        function navigatePeriod(direction) {
            if (currentView === 'month') {
                currentDate.setMonth(currentDate.getMonth() + direction);
            } else if (currentView === 'week') {
                currentDate.setDate(currentDate.getDate() + (direction * 7));
            } else if (currentView === 'day') {
                currentDate.setDate(currentDate.getDate() + direction);
            }

            updatePeriodDisplay();

            if (currentView === 'month') {
                renderMonthView();
            }
        }

        // Event listeners
        document.getElementById('prev-period').addEventListener('click', () => navigatePeriod(-1));
        document.getElementById('next-period').addEventListener('click', () => navigatePeriod(1));

        document.getElementById('today-btn').addEventListener('click', () => {
            currentDate = new Date();
            updatePeriodDisplay();
            if (currentView === 'month') {
                renderMonthView();
            }
        });

        document.getElementById('month-view').addEventListener('click', () => switchView('month'));
        document.getElementById('week-view').addEventListener('click', () => switchView('week'));
        document.getElementById('day-view').addEventListener('click', () => switchView('day'));

        // Initialize calendar
        document.addEventListener('DOMContentLoaded', function() {
            switchView('month');
        });
    </script>
</x-app-layout>