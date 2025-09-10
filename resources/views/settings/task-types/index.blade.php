<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Typy zadań
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Zarządzanie rodzajami zadań w systemie
                </p>
            </div>
            <div class="mt-3 sm:mt-0">
                <a href="{{ route('settings.index') }}" class="btn-kt-secondary mr-3">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót do ustawień
                </a>
                <button type="button" onclick="openAddModal()" class="btn-kt-primary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    Dodaj typ zadania
                </button>
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

            @if(session('error'))
                <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="kt-card">
                <div class="kt-card-body">
                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>Nazwa</th>
                                    <th>Opis</th>
                                    <th>Status</th>
                                    <th>Liczba zadań</th>
                                    <th class="text-right">Akcje</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($taskTypes as $taskType)
                                <tr>
                                    <td>
                                        <strong>{{ $taskType->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $taskType->description ?: '-' }}</span>
                                    </td>
                                    <td>
                                        @if($taskType->active)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Aktywny
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                                Nieaktywny
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $taskType->tasks_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button type="button" onclick="editTaskType({{ $taskType->id }}, '{{ addslashes($taskType->name) }}', '{{ addslashes($taskType->description ?? '') }}', {{ $taskType->active ? 'true' : 'false' }})" 
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:text-blue-200 dark:hover:bg-blue-800 rounded-md transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                                </svg>
                                                Edytuj
                                            </button>
                                            
                                            <form method="POST" action="{{ route('settings.task-types.toggle-active', $taskType) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium {{ $taskType->active ? 'text-yellow-700 bg-yellow-100 hover:bg-yellow-200 dark:bg-yellow-900 dark:text-yellow-200 dark:hover:bg-yellow-800' : 'text-green-700 bg-green-100 hover:bg-green-200 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800' }} rounded-md transition-colors"
                                                        title="{{ $taskType->active ? 'Dezaktywuj' : 'Aktywuj' }}">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        @if($taskType->active)
                                                            <path d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z"></path>
                                                            <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"></path>
                                                        @else
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                                        @endif
                                                    </svg>
                                                    {{ $taskType->active ? 'Dezaktywuj' : 'Aktywuj' }}
                                                </button>
                                            </form>
                                            
                                            @if($taskType->tasks()->count() == 0)
                                            <form method="POST" action="{{ route('settings.task-types.destroy', $taskType) }}" 
                                                  class="inline" onsubmit="return confirm('Czy na pewno chcesz usunąć ten typ zadania?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800 rounded-md transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2h8a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 112 0v4a1 1 0 11-2 0V9zm4 0a1 1 0 112 0v4a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Usuń
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Brak typów zadań do wyświetlenia
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
</div>

<!-- Modal dodawania typu zadania -->
<div id="addTaskTypeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" onclick="if(event.target === this) closeAddModal()">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Dodaj nowy typ zadania</h3>
                <button type="button" onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('settings.task-types.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="form-kt-label">Nazwa typu zadania <span class="text-red-500">*</span></label>
                    <input type="text" class="form-kt-control" id="name" name="name" required maxlength="255">
                </div>
                <div>
                    <label for="description" class="form-kt-label">Opis</label>
                    <textarea class="form-kt-control" id="description" name="description" rows="3" 
                              placeholder="Opcjonalny opis typu zadania"></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeAddModal()" class="btn-kt-secondary">Anuluj</button>
                    <button type="submit" class="btn-kt-primary">Dodaj typ zadania</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal edycji typu zadania -->
<div id="editTaskTypeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" onclick="if(event.target === this) closeEditModal()">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white dark:bg-gray-800">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edytuj typ zadania</h3>
                <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form method="POST" id="editTaskTypeForm" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_name" class="form-kt-label">Nazwa typu zadania <span class="text-red-500">*</span></label>
                    <input type="text" class="form-kt-control" id="edit_name" name="name" required maxlength="255">
                </div>
                <div>
                    <label for="edit_description" class="form-kt-label">Opis</label>
                    <textarea class="form-kt-control" id="edit_description" name="description" rows="3" 
                              placeholder="Opcjonalny opis typu zadania"></textarea>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" id="edit_active" name="active" value="1" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded">
                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Aktywny</span>
                    </label>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeEditModal()" class="btn-kt-secondary">Anuluj</button>
                    <button type="submit" class="btn-kt-primary">Zapisz zmiany</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addTaskTypeModal').classList.remove('hidden');
}

function closeAddModal() {
    document.getElementById('addTaskTypeModal').classList.add('hidden');
    // Reset form
    document.getElementById('name').value = '';
    document.getElementById('description').value = '';
}

function editTaskType(id, name, description, active) {
    const form = document.getElementById('editTaskTypeForm');
    form.action = `/settings/task-types/${id}`;
    
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_active').checked = active;
    
    document.getElementById('editTaskTypeModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editTaskTypeModal').classList.add('hidden');
    // Reset form
    document.getElementById('edit_name').value = '';
    document.getElementById('edit_description').value = '';
    document.getElementById('edit_active').checked = false;
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddModal();
        closeEditModal();
    }
});
</script>
</x-app-layout>