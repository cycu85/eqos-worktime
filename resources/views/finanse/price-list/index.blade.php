<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Cennik
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

            <div class="kt-card">
                <div class="kt-card-body">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Lista cen
                        </h3>
                        <button onclick="openAddModal()" class="btn-kt-primary">
                            Dodaj cenę
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>Rodzaj zadania</th>
                                    <th>Cena (zł)</th>
                                    <th>Obowiązuje od</th>
                                    <th>Dodano</th>
                                    <th class="text-center">Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($prices as $price)
                                    <tr>
                                        <td class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ $price->taskType->name }}
                                        </td>
                                        <td>
                                            {{ number_format($price->price, 2, ',', ' ') }} zł
                                        </td>
                                        <td>
                                            {{ $price->valid_from->format('Y-m-d') }}
                                        </td>
                                        <td>
                                            {{ $price->created_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="text-center">
                                            <button
                                                onclick="editPrice({{ $price->id }}, {{ $price->task_type_id }}, {{ $price->price }}, '{{ $price->valid_from->format('Y-m-d') }}')"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3"
                                            >
                                                Edytuj
                                            </button>
                                            <form action="{{ route('finanse.price-list.destroy', $price) }}" method="POST" class="inline-block" onsubmit="return confirm('Czy na pewno chcesz usunąć tę cenę?')">
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
                                        <td colspan="5" class="text-center text-gray-500 dark:text-gray-400 py-4">
                                            Brak wpisów w cenniku
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

    <!-- Modal dodawania ceny -->
    <div id="addModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                    Dodaj cenę
                </h3>
                <form method="POST" action="{{ route('finanse.price-list.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="add_task_type_id" class="form-kt-label">
                            Rodzaj zadania
                        </label>
                        <select name="task_type_id" id="add_task_type_id" class="form-kt-select" required>
                            <option value="">Wybierz rodzaj zadania</option>
                            @foreach($taskTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="add_price" class="form-kt-label">
                            Cena (zł)
                        </label>
                        <input
                            type="number"
                            name="price"
                            id="add_price"
                            step="0.01"
                            min="0"
                            class="form-kt-control"
                            required
                        >
                    </div>
                    <div class="mb-4">
                        <label for="add_valid_from" class="form-kt-label">
                            Obowiązuje od
                        </label>
                        <input
                            type="date"
                            name="valid_from"
                            id="add_valid_from"
                            class="form-kt-control"
                            required
                        >
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeAddModal()" class="btn-kt-secondary">
                            Anuluj
                        </button>
                        <button type="submit" class="btn-kt-primary">
                            Dodaj
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal edycji ceny -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                    Edytuj cenę
                </h3>
                <form method="POST" id="editForm">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label for="edit_task_type_id" class="form-kt-label">
                            Rodzaj zadania
                        </label>
                        <select name="task_type_id" id="edit_task_type_id" class="form-kt-select" required>
                            <option value="">Wybierz rodzaj zadania</option>
                            @foreach($taskTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="edit_price" class="form-kt-label">
                            Cena (zł)
                        </label>
                        <input
                            type="number"
                            name="price"
                            id="edit_price"
                            step="0.01"
                            min="0"
                            class="form-kt-control"
                            required
                        >
                    </div>
                    <div class="mb-4">
                        <label for="edit_valid_from" class="form-kt-label">
                            Obowiązuje od
                        </label>
                        <input
                            type="date"
                            name="valid_from"
                            id="edit_valid_from"
                            class="form-kt-control"
                            required
                        >
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeEditModal()" class="btn-kt-secondary">
                            Anuluj
                        </button>
                        <button type="submit" class="btn-kt-primary">
                            Zapisz
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.getElementById('add_task_type_id').value = '';
            document.getElementById('add_price').value = '';
            document.getElementById('add_valid_from').value = '';
        }

        function editPrice(id, taskTypeId, price, validFrom) {
            const form = document.getElementById('editForm');
            form.action = `/finanse/cennik/${id}`;
            document.getElementById('edit_task_type_id').value = taskTypeId;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_valid_from').value = validFrom;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('edit_task_type_id').value = '';
            document.getElementById('edit_price').value = '';
            document.getElementById('edit_valid_from').value = '';
        }
    </script>
</x-app-layout>
