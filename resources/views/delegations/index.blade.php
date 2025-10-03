<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Delegacje
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                        Zarządzanie delegacjami służbowymi - wszystkie delegacje
                    @else
                        Moje delegacje służbowe
                    @endif
                </p>
            </div>
            <div class="mt-3 sm:mt-0 flex flex-wrap gap-3">
                <a href="{{ route('delegations.create') }}" class="btn-kt-primary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    Nowa delegacja
                </a>
                
                @if(auth()->user()->isAdmin() || auth()->user()->isKierownik())
                    <a href="{{ route('delegations.create-group') }}" class="btn-kt-info">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                        </svg>
                        Nowa delegacja grupowa
                    </a>
                @endif
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
                    <form method="GET" action="{{ route('delegations.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Search -->
                            <div>
                                <label class="form-kt-label">Wyszukaj</label>
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}"
                                       class="form-kt-control" 
                                       placeholder="Pracownik, cel, miejsce...">
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label class="form-kt-label">Status</label>
                                <select name="status" class="form-kt-select">
                                    <option value="">Wszystkie statusy</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Szkic</option>
                                    <option value="employee_approved" {{ request('status') === 'employee_approved' ? 'selected' : '' }}>Zaakceptowana przez pracownika</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Zatwierdzona</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Zakończona</option>
                                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Anulowana</option>
                                </select>
                            </div>

                            <!-- Country Filter -->
                            <div>
                                <label class="form-kt-label">Kraj</label>
                                <select name="country" class="form-kt-select">
                                    <option value="">Wszystkie kraje</option>
                                    @foreach($countries as $countryOption)
                                        <option value="{{ $countryOption }}" {{ request('country') === $countryOption ? 'selected' : '' }}>
                                            {{ $countryOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date From -->
                            <div>
                                <label class="form-kt-label">Data wyjazdu od</label>
                                <input type="date" 
                                       name="date_from" 
                                       value="{{ request('date_from') }}"
                                       class="form-kt-control">
                            </div>

                            <!-- Date To -->
                            <div>
                                <label class="form-kt-label">Data wyjazdu do</label>
                                <input type="date" 
                                       name="date_to" 
                                       value="{{ request('date_to') }}"
                                       class="form-kt-control">
                            </div>

                        </div>

                        <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                </svg>
                                Szukaj
                            </button>
                            
                            <a href="{{ route('delegations.index') }}" class="btn-kt-light">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                                </svg>
                                Wyczyść filtry
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results info -->
            @if(request()->hasAny(['search', 'status', 'country', 'date_from', 'date_to']))
                <div class="mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400 px-4 py-3 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span>
                            Znaleziono {{ $delegations->total() }} {{ trans_choice('delegacji|delegację|delegacje', $delegations->total()) }}
                            @if(request('search'))
                                dla frazy "{{ request('search') }}"
                            @endif
                        </span>
                        <a href="{{ route('delegations.index') }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline">
                            Wyczyść filtry
                        </a>
                    </div>
                </div>
            @endif

            <div class="kt-card">
                <div class="kt-card-body">
                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th class="w-16">
                                        <a href="{{ route('delegations.index', array_merge(request()->query(), ['sort' => 'id', 'direction' => request('sort') === 'id' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                                           class="flex items-center hover:text-blue-600 dark:hover:text-blue-400">
                                            NR
                                            @if(request('sort') === 'id')
                                                @if(request('direction') === 'asc')
                                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            @else
                                                <svg class="w-4 h-4 ml-1 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="w-40">
                                        <a href="{{ route('delegations.index', array_merge(request()->query(), ['sort' => 'first_name', 'direction' => request('sort') === 'first_name' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                                           class="flex items-center hover:text-blue-600 dark:hover:text-blue-400">
                                            Pracownik
                                            @if(request('sort') === 'first_name')
                                                @if(request('direction') === 'asc')
                                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            @else
                                                <svg class="w-4 h-4 ml-1 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="w-24">Cel podróży</th>
                                    <th class="w-32">Miejsce</th>
                                    <th class="w-28">
                                        <a href="{{ route('delegations.index', array_merge(request()->query(), ['sort' => 'departure_date', 'direction' => request('sort') === 'departure_date' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                                           class="flex items-center hover:text-blue-600 dark:hover:text-blue-400">
                                            Wyjazd
                                            @if(request('sort') === 'departure_date')
                                                @if(request('direction') === 'asc')
                                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            @else
                                                <svg class="w-4 h-4 ml-1 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="w-28">
                                        <a href="{{ route('delegations.index', array_merge(request()->query(), ['sort' => 'arrival_date', 'direction' => request('sort') === 'arrival_date' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                                           class="flex items-center hover:text-blue-600 dark:hover:text-blue-400">
                                            Przyjazd
                                            @if(request('sort') === 'arrival_date')
                                                @if(request('direction') === 'asc')
                                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            @else
                                                <svg class="w-4 h-4 ml-1 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="w-32">Pojazdy</th>
                                    <th class="w-24">
                                        <a href="{{ route('delegations.index', array_merge(request()->query(), ['sort' => 'delegation_status', 'direction' => request('sort') === 'delegation_status' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                                           class="flex items-center hover:text-blue-600 dark:hover:text-blue-400">
                                            Status
                                            @if(request('sort') === 'delegation_status')
                                                @if(request('direction') === 'asc')
                                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                @endif
                                            @else
                                                <svg class="w-4 h-4 ml-1 opacity-50" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($delegations as $delegation)
                                <tr>
                                    <td>
                                        <a href="{{ route('delegations.show', $delegation) }}" 
                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                            #{{ $delegation->id }}
                                        </a>
                                    </td>
                                    <td class="w-40">
                                        <a href="{{ route('delegations.show', $delegation) }}"
                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium text-sm">
                                            {{ $delegation->full_name }}
                                        </a>
                                    </td>
                                    <td class="w-24" style="white-space: normal;">
                                        <div class="text-muted break-words">{{ Str::limit($delegation->travel_purpose, 40) }}</div>
                                    </td>
                                    <td class="w-32 text-sm">
                                        <div class="break-words whitespace-normal">{{ $delegation->destination_city }}, {{ $delegation->country }}</div>
                                    </td>
                                    <td class="w-28 text-sm">
                                        @if($delegation->departure_date)
                                            {{ $delegation->departure_date->format('d.m.Y') }}
                                            @if($delegation->departure_time)
                                                <br><small class="text-muted">{{ $delegation->departure_time }}</small>
                                            @endif
                                        @else
                                            <span class="text-gray-400 italic text-xs">Brak</span>
                                        @endif
                                    </td>
                                    <td class="w-28 text-sm">
                                        @if($delegation->arrival_date)
                                            {{ $delegation->arrival_date->format('d.m.Y') }}
                                            @if($delegation->arrival_time)
                                                <br><small class="text-muted">{{ $delegation->arrival_time }}</small>
                                            @endif
                                        @else
                                            <span class="text-gray-400 italic text-xs">Brak</span>
                                        @endif
                                    </td>
                                    <td class="w-32">
                                        @if($delegation->vehicle_registration)
                                            @php
                                                $vehicles = array_filter(array_map('trim', explode(',', $delegation->vehicle_registration)));
                                            @endphp
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($vehicles as $vehicle)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        {{ $vehicle }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic text-xs">Brak</span>
                                        @endif
                                    </td>
                                    <td class="w-24" style="white-space: normal;">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1">
                                                @if($delegation->delegation_status === 'draft')
                                                    <div class="text-xs font-medium px-2 py-1 rounded bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 break-words">
                                                        Szkic
                                                    </div>
                                                @elseif($delegation->delegation_status === 'employee_approved')
                                                    <div class="text-xs font-medium px-2 py-1 rounded bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 break-words">
                                                        Zaakceptowana przez pracownika
                                                    </div>
                                                @elseif($delegation->delegation_status === 'approved')
                                                    <div class="text-xs font-medium px-2 py-1 rounded bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 break-words">
                                                        Zatwierdzona
                                                    </div>
                                                @elseif($delegation->delegation_status === 'completed')
                                                    <div class="text-xs font-medium px-2 py-1 rounded bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 break-words">
                                                        Zakończona
                                                    </div>
                                                @elseif($delegation->delegation_status === 'cancelled')
                                                    <div class="text-xs font-medium px-2 py-1 rounded bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 break-words">
                                                        Anulowana
                                                    </div>
                                                @endif
                                            </div>

                                            @if((auth()->user()->isAdmin() || auth()->user()->isKierownik()) && $delegation->delegation_status === 'employee_approved')
                                                <form action="{{ route('delegations.supervisor-approval', $delegation) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit"
                                                            class="inline-flex items-center justify-center p-1.5 rounded-lg text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/20 transition-colors"
                                                            title="Zatwierdź delegację"
                                                            onclick="return confirm('Czy na pewno chcesz zatwierdzić tę delegację?')">
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Brak delegacji do wyświetlenia
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($delegations->hasPages())
                        <div class="mt-6">
                            {{ $delegations->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>