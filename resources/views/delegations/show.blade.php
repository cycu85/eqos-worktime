<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Delegacja: {{ $delegation->full_name }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $delegation->destination_city }}, {{ $delegation->country }}
                </p>
            </div>
            <div class="mt-3 sm:mt-0 flex flex-wrap gap-3">
                <a href="{{ route('delegations.index') }}" class="btn-kt-secondary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót do listy
                </a>

                {{-- Employee approval button --}}
                @php
                    $user = auth()->user();
                    $nameParts = explode(' ', trim($user->name), 2);
                    $firstName = $nameParts[0] ?? '';
                    $lastName = $nameParts[1] ?? '';
                    $isOwner = ($delegation->first_name === $firstName && $delegation->last_name === $lastName);
                    $allDatesFilled = $delegation->departure_date && $delegation->arrival_date && 
                                     $delegation->departure_time && $delegation->arrival_time;
                    $isApproved = $delegation->supervisor_approval_status === 'approved';
                @endphp

                @if($isOwner && $allDatesFilled && $delegation->employee_approval_status !== 'approved')
                    <form method="POST" action="{{ route('delegations.employee-approval', $delegation) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-kt-success" 
                                onclick="return confirm('Czy na pewno chcesz zaakceptować tę delegację?')">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Akceptuję
                        </button>
                    </form>
                @endif

                {{-- Supervisor approval button --}}
                @if(($user->isKierownik() || $user->isAdmin()) && $delegation->employee_approval_status === 'approved' && $delegation->supervisor_approval_status !== 'approved')
                    <form method="POST" action="{{ route('delegations.supervisor-approval', $delegation) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-kt-primary" 
                                onclick="return confirm('Czy na pewno chcesz zaakceptować tę delegację?')">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Zaakceptuj
                        </button>
                    </form>
                @endif

                {{-- Revoke approval button (admin only) --}}
                @if($user->isAdmin() && $isApproved)
                    <form method="POST" action="{{ route('delegations.revoke-approval', $delegation) }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-kt-warning" 
                                onclick="return confirm('Czy na pewno chcesz cofnąć akceptację tej delegacji?')">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            Cofnij akceptację
                        </button>
                    </form>
                @endif

                {{-- Edit button - only if not fully approved --}}
                @if(!$isApproved)
                    <a href="{{ route('delegations.edit', $delegation) }}" class="btn-kt-warning">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                        </svg>
                        Edytuj
                    </a>
                @endif

                {{-- Delete button - only if not fully approved --}}
                @if(!$isApproved)
                    <form method="POST" action="{{ route('delegations.destroy', $delegation) }}" 
                          class="inline" onsubmit="return confirm('Czy na pewno chcesz usunąć tę delegację?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-kt-danger">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 112 0v4a1 1 0 11-2 0V9zm4 0a1 1 0 112 0v4a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                            </svg>
                            Usuń
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Główne informacje -->
                <div class="lg:col-span-2">
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Szczegóły delegacji</h3>
                        </div>
                        <div class="kt-card-body">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Dane osobowe -->
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Dane pracownika</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Imię i nazwisko:</span>
                                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $delegation->full_name }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Daty -->
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Daty i czas</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Data polecenia:</span>
                                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $delegation->order_date->format('d.m.Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Wyjazd:</span>
                                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">
                                                @if($delegation->departure_date)
                                                    {{ $delegation->departure_date->format('d.m.Y') }}
                                                    @if($delegation->departure_time)
                                                        o {{ $delegation->departure_time }}
                                                    @endif
                                                @else
                                                    <span class="text-gray-400 italic">Nie określono</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Przyjazd:</span>
                                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">
                                                @if($delegation->arrival_date)
                                                    {{ $delegation->arrival_date->format('d.m.Y') }}
                                                    @if($delegation->arrival_time)
                                                        o {{ $delegation->arrival_time }}
                                                    @endif
                                                @else
                                                    <span class="text-gray-400 italic">Nie określono</span>
                                                @endif
                                            </span>
                                        </div>
                                        @if($delegation->delegation_duration)
                                        <div>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Czas trwania:</span>
                                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $delegation->delegation_duration }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Cel podróży -->
                                <div class="md:col-span-2">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Cel podróży</h4>
                                    <p class="text-gray-700 dark:text-gray-300">{{ $delegation->travel_purpose }}</p>
                                    @if($delegation->project)
                                    <div class="mt-2">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Projekt:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $delegation->project }}</span>
                                    </div>
                                    @endif
                                </div>

                                <!-- Miejsce docelowe -->
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Miejsce docelowe</h4>
                                    <div class="space-y-2">
                                        <div>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Miejscowość:</span>
                                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $delegation->destination_city }}</span>
                                        </div>
                                        <div>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Kraj:</span>
                                            <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $delegation->country }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Transport -->
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Transport</h4>
                                    @if($delegation->vehicle_registration)
                                    <div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Pojazd:</span>
                                        <span class="ml-2 font-medium text-gray-900 dark:text-gray-100">{{ $delegation->vehicle_registration }}</span>
                                    </div>
                                    @else
                                    <span class="text-gray-500 italic">Nie określono pojazdu</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel boczny -->
                <div>
                    <!-- Status -->
                    <div class="kt-card mb-6">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Status</h3>
                        </div>
                        <div class="kt-card-body">
                            @if($delegation->delegation_status === 'draft')
                                <span class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 2h12v8H4V6z"></path>
                                    </svg>
                                    Szkic
                                </span>
                            @elseif($delegation->delegation_status === 'employee_approved')
                                <span class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Zaakceptowana przez pracownika
                                </span>
                            @elseif($delegation->delegation_status === 'approved')
                                <span class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Zaakceptowana
                                </span>
                            @elseif($delegation->delegation_status === 'completed')
                                <span class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Zakończona
                                </span>
                            @elseif($delegation->delegation_status === 'cancelled')
                                <span class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    Anulowana
                                </span>
                            @endif

                            {{-- Approval dates --}}
                            @if($delegation->employee_approval_date)
                                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        <strong>Data akceptacji pracownika:</strong><br>
                                        {{ $delegation->employee_approval_date->format('d.m.Y H:i') }}
                                    </div>
                                </div>
                            @endif

                            @if($delegation->supervisor_approval_date)
                                <div class="mt-2">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        <strong>Data akceptacji kierownika:</strong><br>
                                        {{ $delegation->supervisor_approval_date->format('d.m.Y H:i') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Koszty i rozliczenie -->
                    <div class="kt-card">
                        <div class="kt-card-header">
                            <h3 class="kt-card-title">Rozliczenie</h3>
                        </div>
                        <div class="kt-card-body space-y-4">
                            <!-- Kwota diety -->
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Kwota diety PLN:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($delegation->diet_amount_pln, 2, ',', ' ') }} PLN</span>
                            </div>

                            @if($delegation->diet_amount_currency)
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Kwota diety waluta:</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($delegation->diet_amount_currency, 2, ',', ' ') }} EUR</span>
                            </div>
                            @endif

                            <!-- NBP -->
                            @if($delegation->exchange_rate)
                            <div class="border-t pt-4">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Kurs NBP</h4>
                                <div class="space-y-2 text-sm">
                                    @if($delegation->nbp_table_date)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Data:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $delegation->nbp_table_date->format('d.m.Y') }}</span>
                                    </div>
                                    @endif
                                    @if($delegation->nbp_table_number)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Numer tabeli:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $delegation->nbp_table_number }}</span>
                                    </div>
                                    @endif
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Kurs:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ number_format($delegation->exchange_rate, 4, ',', ' ') }}</span>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Noclegi i posiłki -->
                            <div class="border-t pt-4">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Noclegi i posiłki</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Ilość noclegów:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $delegation->nights_count }}</span>
                                    </div>
                                    @if($delegation->accommodation_limit)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Limit za nocleg:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ number_format($delegation->accommodation_limit, 2, ',', ' ') }} PLN</span>
                                    </div>
                                    @endif
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Śniadania:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $delegation->breakfasts }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Obiady:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $delegation->lunches }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Kolacje:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $delegation->dinners }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Podsumowanie -->
                            <div class="border-t pt-4">
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Suma diet należnych:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($delegation->total_diet_pln, 2, ',', ' ') }} PLN</span>
                                    </div>
                                    @if($delegation->total_diet_currency)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Suma diet waluta:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($delegation->total_diet_currency, 2, ',', ' ') }}</span>
                                    </div>
                                    @endif
                                    <div class="flex justify-between">
                                        <span class="text-gray-600 dark:text-gray-400">Suma poniesionych:</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($delegation->total_expenses, 2, ',', ' ') }} PLN</span>
                                    </div>
                                    <div class="flex justify-between text-lg font-semibold text-green-600 dark:text-green-400 border-t pt-2">
                                        <span>Do wypłaty:</span>
                                        <span class="text-gray-900 dark:text-gray-100">{{ number_format($delegation->amount_to_pay, 2, ',', ' ') }} PLN</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>