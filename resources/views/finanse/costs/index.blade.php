<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Koszty
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
                   class="px-4 py-2 text-sm font-medium border-b-2 border-blue-500 text-blue-600 dark:text-blue-400">
                    Ogólne
                </a>
                <a href="{{ route('finanse.leasing.index') }}"
                   class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    Leasing
                </a>
            </div>

            <!-- Filtry -->
            <div class="kt-card mb-6">
                <div class="kt-card-body">
                    <form method="GET" action="{{ route('finanse.costs.index') }}" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label for="date_from" class="form-kt-label">Data od</label>
                            <input type="date" name="date_from" id="date_from" class="form-kt-control"
                                   value="{{ request('date_from') }}">
                        </div>
                        <div>
                            <label for="date_to" class="form-kt-label">Data do</label>
                            <input type="date" name="date_to" id="date_to" class="form-kt-control"
                                   value="{{ request('date_to') }}">
                        </div>
                        <div>
                            <label for="cost_category_id" class="form-kt-label">Kategoria</label>
                            <select name="cost_category_id" id="cost_category_id" class="form-kt-select">
                                <option value="">Wszystkie kategorie</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('cost_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="btn-kt-primary">Filtruj</button>
                            <a href="{{ route('finanse.costs.index') }}" class="btn-kt-secondary">Wyczyść</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="kt-card">
                <div class="kt-card-body">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Lista kosztów
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Łączna kwota: <strong>{{ number_format($totalAmount, 2, ',', ' ') }} €</strong>
                            </p>
                        </div>
                        <button onclick="openAddModal()" class="btn-kt-primary">
                            Dodaj koszt
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>Nazwa</th>
                                    <th>Kategoria</th>
                                    <th>Kwota</th>
                                    <th>Data</th>
                                    <th>Opis</th>
                                    <th class="text-center">Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($costs as $cost)
                                    <tr>
                                        <td class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $cost->name }}
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                {{ $cost->category->name }}
                                            </span>
                                        </td>
                                        <td class="font-medium">
                                            {{ number_format($cost->amount, 2, ',', ' ') }} €
                                        </td>
                                        <td>
                                            {{ $cost->cost_date->format('Y-m-d') }}
                                        </td>
                                        <td class="text-gray-500 dark:text-gray-400">
                                            {{ $cost->description ?: '-' }}
                                        </td>
                                        <td class="text-center">
                                            <button
                                                onclick="editCost({{ $cost->id }}, '{{ addslashes($cost->name) }}', {{ $cost->amount }}, '{{ $cost->cost_date->format('Y-m-d') }}', {{ $cost->cost_category_id }}, '{{ addslashes($cost->description ?? '') }}')"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3"
                                            >
                                                Edytuj
                                            </button>
                                            <form action="{{ route('finanse.costs.destroy', $cost) }}" method="POST" class="inline-block" onsubmit="return confirm('Czy na pewno chcesz usunąć ten koszt?')">
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
                                        <td colspan="6" class="text-center text-gray-500 dark:text-gray-400 py-4">
                                            Brak kosztów do wyświetlenia
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

    <!-- Modal dodawania kosztu -->
    <div id="addModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" onclick="if(event.target === this) closeAddModal()">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Dodaj koszt</h3>
                    <button type="button" onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('finanse.costs.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="add_name" class="form-kt-label">Nazwa <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="add_name" class="form-kt-control" required maxlength="255">
                    </div>
                    <div>
                        <label for="add_cost_category_id" class="form-kt-label">Kategoria <span class="text-red-500">*</span></label>
                        <select name="cost_category_id" id="add_cost_category_id" class="form-kt-select" required>
                            <option value="">Wybierz kategorię</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="add_amount" class="form-kt-label">Kwota (€) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="add_amount" step="0.01" min="0" class="form-kt-control" required>
                    </div>
                    <div>
                        <label for="add_cost_date" class="form-kt-label">Data <span class="text-red-500">*</span></label>
                        <input type="date" name="cost_date" id="add_cost_date" class="form-kt-control" required>
                    </div>
                    <div>
                        <label for="add_description" class="form-kt-label">Opis</label>
                        <textarea name="description" id="add_description" rows="3" class="form-kt-control" placeholder="Opcjonalny opis kosztu"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" onclick="closeAddModal()" class="btn-kt-secondary">Anuluj</button>
                        <button type="submit" class="btn-kt-primary">Dodaj</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal edycji kosztu -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" onclick="if(event.target === this) closeEditModal()">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edytuj koszt</h3>
                    <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form method="POST" id="editForm" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="edit_name" class="form-kt-label">Nazwa <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-kt-control" required maxlength="255">
                    </div>
                    <div>
                        <label for="edit_cost_category_id" class="form-kt-label">Kategoria <span class="text-red-500">*</span></label>
                        <select name="cost_category_id" id="edit_cost_category_id" class="form-kt-select" required>
                            <option value="">Wybierz kategorię</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="edit_amount" class="form-kt-label">Kwota (€) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="edit_amount" step="0.01" min="0" class="form-kt-control" required>
                    </div>
                    <div>
                        <label for="edit_cost_date" class="form-kt-label">Data <span class="text-red-500">*</span></label>
                        <input type="date" name="cost_date" id="edit_cost_date" class="form-kt-control" required>
                    </div>
                    <div>
                        <label for="edit_description" class="form-kt-label">Opis</label>
                        <textarea name="description" id="edit_description" rows="3" class="form-kt-control" placeholder="Opcjonalny opis kosztu"></textarea>
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
            document.getElementById('add_cost_date').value = '{{ date('Y-m-d') }}';
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.getElementById('add_name').value = '';
            document.getElementById('add_cost_category_id').value = '';
            document.getElementById('add_amount').value = '';
            document.getElementById('add_cost_date').value = '';
            document.getElementById('add_description').value = '';
        }

        function editCost(id, name, amount, costDate, categoryId, description) {
            const form = document.getElementById('editForm');
            form.action = `/finanse/koszty/${id}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_amount').value = amount;
            document.getElementById('edit_cost_date').value = costDate;
            document.getElementById('edit_cost_category_id').value = categoryId;
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
