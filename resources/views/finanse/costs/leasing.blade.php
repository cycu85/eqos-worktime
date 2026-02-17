<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Koszty — Leasing
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-800 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-800 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Zakładki -->
            <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6">
                <a href="{{ route('finanse.costs.index') }}"
                   class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    Ogólne
                </a>
                <a href="{{ route('finanse.leasing.index') }}"
                   class="px-4 py-2 text-sm font-medium border-b-2 border-blue-500 text-blue-600 dark:text-blue-400">
                    Leasing
                </a>
            </div>

            <!-- Filtry -->
            <div class="kt-card mb-6">
                <div class="kt-card-body">
                    <form method="GET" action="{{ route('finanse.leasing.index') }}" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label for="date_from" class="form-kt-label">Data płatności od</label>
                            <input type="date" name="date_from" id="date_from" class="form-kt-control"
                                   value="{{ request('date_from') }}">
                        </div>
                        <div>
                            <label for="date_to" class="form-kt-label">Data płatności do</label>
                            <input type="date" name="date_to" id="date_to" class="form-kt-control"
                                   value="{{ request('date_to') }}">
                        </div>
                        <div>
                            <label for="leasing_cost_type_id" class="form-kt-label">Typ kosztu</label>
                            <select name="leasing_cost_type_id" id="leasing_cost_type_id" class="form-kt-select">
                                <option value="">Wszystkie typy</option>
                                @foreach($costTypes as $type)
                                    <option value="{{ $type->id }}" {{ request('leasing_cost_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="vehicle_id" class="form-kt-label">Pojazd</label>
                            <select name="vehicle_id" id="vehicle_id" class="form-kt-select">
                                <option value="">Wszystkie pojazdy</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                        {{ $vehicle->name }} ({{ $vehicle->registration }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn-kt-primary">Filtruj</button>
                            <a href="{{ route('finanse.leasing.index') }}" class="btn-kt-secondary">Wyczyść</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-body">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Lista leasingów
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Łączna kwota rat: <strong>{{ number_format($totalAmount, 2, ',', ' ') }} €</strong>
                            </p>
                        </div>
                        <button onclick="openAddModal()" class="btn-kt-primary">
                            Dodaj leasing
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>Pojazd</th>
                                    <th>Leasingodawca</th>
                                    <th>Nr umowy</th>
                                    <th>Typ kosztu</th>
                                    <th>Kwota raty</th>
                                    <th>Data płatności</th>
                                    <th>Okres leasingu</th>
                                    <th class="text-center">Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leasings as $leasing)
                                    <tr>
                                        <td class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $leasing->vehicle->name }}<br>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $leasing->vehicle->registration }}</span>
                                        </td>
                                        <td>{{ $leasing->lessor }}</td>
                                        <td class="font-mono text-sm">{{ $leasing->contract_number }}</td>
                                        <td>
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ $leasing->leasingCostType->name }}
                                            </span>
                                        </td>
                                        <td class="font-medium">
                                            {{ number_format($leasing->amount, 2, ',', ' ') }} €
                                        </td>
                                        <td>{{ $leasing->payment_date->format('Y-m-d') }}</td>
                                        <td class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $leasing->date_from->format('Y-m-d') }} — {{ $leasing->date_to->format('Y-m-d') }}
                                        </td>
                                        <td class="text-center">
                                            <button
                                                onclick="editLeasing({{ $leasing->id }}, {{ $leasing->vehicle_id }}, {{ $leasing->leasing_cost_type_id }}, '{{ addslashes($leasing->lessor) }}', '{{ addslashes($leasing->contract_number) }}', '{{ $leasing->date_from->format('Y-m-d') }}', '{{ $leasing->date_to->format('Y-m-d') }}', {{ $leasing->amount }}, '{{ $leasing->payment_date->format('Y-m-d') }}', '{{ addslashes($leasing->description ?? '') }}')"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3"
                                            >
                                                Edytuj
                                            </button>
                                            <form action="{{ route('finanse.leasing.destroy', $leasing) }}" method="POST" class="inline-block" onsubmit="return confirm('Czy na pewno chcesz usunąć ten rekord?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    Usuń
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-gray-500 dark:text-gray-400 py-4">
                                            Brak rekordów leasingu do wyświetlenia
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal dodawania leasingu -->
    <div id="addModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" onclick="if(event.target === this) closeAddModal()">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Dodaj leasing</h3>
                    <button type="button" onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('finanse.leasing.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="add_vehicle_id" class="form-kt-label">Pojazd <span class="text-red-500">*</span></label>
                            <select name="vehicle_id" id="add_vehicle_id" class="form-kt-select" required>
                                <option value="">Wybierz pojazd</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->name }} ({{ $vehicle->registration }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="add_leasing_cost_type_id" class="form-kt-label">Typ kosztu <span class="text-red-500">*</span></label>
                            <select name="leasing_cost_type_id" id="add_leasing_cost_type_id" class="form-kt-select" required>
                                <option value="">Wybierz typ</option>
                                @foreach($costTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="add_lessor" class="form-kt-label">Leasingodawca <span class="text-red-500">*</span></label>
                        <input type="text" name="lessor" id="add_lessor" class="form-kt-control" required maxlength="255">
                    </div>
                    <div>
                        <label for="add_contract_number" class="form-kt-label">Numer umowy <span class="text-red-500">*</span></label>
                        <input type="text" name="contract_number" id="add_contract_number" class="form-kt-control" required maxlength="255">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="add_date_from" class="form-kt-label">Okres od <span class="text-red-500">*</span></label>
                            <input type="date" name="date_from" id="add_date_from" class="form-kt-control" required>
                        </div>
                        <div>
                            <label for="add_date_to" class="form-kt-label">Okres do <span class="text-red-500">*</span></label>
                            <input type="date" name="date_to" id="add_date_to" class="form-kt-control" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="add_amount" class="form-kt-label">Kwota raty (€) <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" id="add_amount" step="0.01" min="0" class="form-kt-control" required>
                        </div>
                        <div>
                            <label for="add_payment_date" class="form-kt-label">Data płatności <span class="text-red-500">*</span></label>
                            <input type="date" name="payment_date" id="add_payment_date" class="form-kt-control" required>
                        </div>
                    </div>
                    <div>
                        <label for="add_description" class="form-kt-label">Uwagi</label>
                        <textarea name="description" id="add_description" rows="3" class="form-kt-control" placeholder="Opcjonalne uwagi"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="closeAddModal()" class="btn-kt-secondary">Anuluj</button>
                        <button type="submit" class="btn-kt-primary">Dodaj</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal edycji leasingu -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" onclick="if(event.target === this) closeEditModal()">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edytuj leasing</h3>
                    <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form method="POST" id="editForm" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_vehicle_id" class="form-kt-label">Pojazd <span class="text-red-500">*</span></label>
                            <select name="vehicle_id" id="edit_vehicle_id" class="form-kt-select" required>
                                <option value="">Wybierz pojazd</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->name }} ({{ $vehicle->registration }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="edit_leasing_cost_type_id" class="form-kt-label">Typ kosztu <span class="text-red-500">*</span></label>
                            <select name="leasing_cost_type_id" id="edit_leasing_cost_type_id" class="form-kt-select" required>
                                <option value="">Wybierz typ</option>
                                @foreach($costTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="edit_lessor" class="form-kt-label">Leasingodawca <span class="text-red-500">*</span></label>
                        <input type="text" name="lessor" id="edit_lessor" class="form-kt-control" required maxlength="255">
                    </div>
                    <div>
                        <label for="edit_contract_number" class="form-kt-label">Numer umowy <span class="text-red-500">*</span></label>
                        <input type="text" name="contract_number" id="edit_contract_number" class="form-kt-control" required maxlength="255">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_date_from" class="form-kt-label">Okres od <span class="text-red-500">*</span></label>
                            <input type="date" name="date_from" id="edit_date_from" class="form-kt-control" required>
                        </div>
                        <div>
                            <label for="edit_date_to" class="form-kt-label">Okres do <span class="text-red-500">*</span></label>
                            <input type="date" name="date_to" id="edit_date_to" class="form-kt-control" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_amount" class="form-kt-label">Kwota raty (€) <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" id="edit_amount" step="0.01" min="0" class="form-kt-control" required>
                        </div>
                        <div>
                            <label for="edit_payment_date" class="form-kt-label">Data płatności <span class="text-red-500">*</span></label>
                            <input type="date" name="payment_date" id="edit_payment_date" class="form-kt-control" required>
                        </div>
                    </div>
                    <div>
                        <label for="edit_description" class="form-kt-label">Uwagi</label>
                        <textarea name="description" id="edit_description" rows="3" class="form-kt-control" placeholder="Opcjonalne uwagi"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="closeEditModal()" class="btn-kt-secondary">Anuluj</button>
                        <button type="submit" class="btn-kt-primary">Zapisz</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('add_payment_date').value = '{{ date('Y-m-d') }}';
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        function editLeasing(id, vehicleId, costTypeId, lessor, contractNumber, dateFrom, dateTo, amount, paymentDate, description) {
            const form = document.getElementById('editForm');
            form.action = `/finanse/koszty/leasing/${id}`;
            document.getElementById('edit_vehicle_id').value = vehicleId;
            document.getElementById('edit_leasing_cost_type_id').value = costTypeId;
            document.getElementById('edit_lessor').value = lessor;
            document.getElementById('edit_contract_number').value = contractNumber;
            document.getElementById('edit_date_from').value = dateFrom;
            document.getElementById('edit_date_to').value = dateTo;
            document.getElementById('edit_amount').value = amount;
            document.getElementById('edit_payment_date').value = paymentDate;
            document.getElementById('edit_description').value = description;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddModal();
                closeEditModal();
            }
        });
    </script>
</x-app-layout>
