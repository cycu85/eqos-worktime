<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    Harmonogram pracy
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $task->title }}
                </p>
            </div>
            <div class="mt-3 sm:mt-0 flex space-x-3">
                <a href="{{ route('tasks.show', $task) }}" class="btn-kt-secondary">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót do zadania
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Task Info -->
            <div class="kt-card mb-6">
                <div class="kt-card-body">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Okres realizacji</h4>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $task->start_date->format('d.m.Y') }} - {{ $task->end_date->format('d.m.Y') }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $task->workLogs->count() }} dni pracy
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Łączne godziny</h4>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $task->getTotalWorkHours() }}h
                            </p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Roboczogodziny</h4>
                            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $task->getTotalRoboczogodziny() }}h
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                ({{ $task->getTeamSize() }} pracowników)
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Logs Form -->
            <div class="kt-card">
                <div class="kt-card-header">
                    <h3 class="kt-card-title">Harmonogram pracy</h3>
                </div>
                <div class="kt-card-body">
                    <form method="POST" action="{{ route('tasks.work-logs.bulk-update', $task) }}">
                        @csrf
                        
                        <div class="space-y-4">
                            @foreach($task->workLogs as $log)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
                                        <!-- Date -->
                                        <div class="md:w-32">
                                            <div class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $log->work_date->format('d.m.Y') }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $log->work_date->locale('pl')->isoFormat('dddd') }}
                                            </div>
                                        </div>
                                        
                                        <!-- Delete button -->
                                        <div class="md:w-10">
                                            <button type="button" 
                                                    onclick="deleteWorkLog({{ $log->id }})"
                                                    class="text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 p-2 rounded-full transition-colors"
                                                    title="Usuń dzień pracy">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <!-- Time inputs -->
                                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-4 gap-4">
                                            <div>
                                                <label class="form-kt-label text-xs">Początek</label>
                                                <input type="time" 
                                                       name="logs[{{ $log->id }}][start_time]" 
                                                       value="{{ substr($log->start_time, 0, 5) }}"
                                                       class="form-kt-control text-sm">
                                            </div>
                                            <div>
                                                <label class="form-kt-label text-xs">Koniec</label>
                                                <input type="time" 
                                                       name="logs[{{ $log->id }}][end_time]" 
                                                       value="{{ substr($log->end_time, 0, 5) }}"
                                                       class="form-kt-control text-sm">
                                            </div>
                                            <div>
                                                <label class="form-kt-label text-xs">Status</label>
                                                <select name="logs[{{ $log->id }}][status]" class="form-kt-select text-sm">
                                                    <option value="planned" {{ $log->status === 'planned' ? 'selected' : '' }}>Planowane</option>
                                                    <option value="in_progress" {{ $log->status === 'in_progress' ? 'selected' : '' }}>W trakcie</option>
                                                    <option value="completed" {{ $log->status === 'completed' ? 'selected' : '' }}>Ukończone</option>
                                                    <option value="cancelled" {{ $log->status === 'cancelled' ? 'selected' : '' }}>Anulowane</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-kt-label text-xs">Godziny</label>
                                                <div class="form-kt-control bg-gray-50 dark:bg-gray-800 text-sm flex items-center">
                                                    <span class="duration-display" data-log-id="{{ $log->id }}">
                                                        {{ $log->getDurationHours() ?? 0 }}h
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Notes -->
                                    <div class="mt-4">
                                        <label class="form-kt-label text-xs">Notatki</label>
                                        <textarea name="logs[{{ $log->id }}][notes]" 
                                                  rows="2"
                                                  class="form-kt-control text-sm"
                                                  placeholder="Opcjonalne notatki do tego dnia...">{{ $log->notes }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                            
                            <!-- Add new day button -->
                            <div class="border border-gray-200 dark:border-gray-700 border-dashed rounded-lg p-4">
                                <div class="flex items-center justify-center">
                                    <button type="button" 
                                            onclick="showDatePicker()"
                                            class="flex items-center space-x-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 px-4 py-2 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        <span class="font-medium">Dodaj dzień pracy</span>
                                    </button>
                                </div>
                                <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-2">
                                    Wybierz datę aby dodać nowy dzień pracy do zadania
                                </p>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex justify-between items-center">
                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <span id="total-hours">{{ $task->getTotalWorkHours() }}</span>h łącznie | 
                                <span id="total-roboczogodziny">{{ $task->getTotalRoboczogodziny() }}</span>h roboczogodziny
                            </div>
                            <button type="submit" class="btn-kt-primary">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Zapisz harmonogram
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden form for deleting work logs -->
    <form id="delete-work-log-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Hidden form for adding new work day -->
    <form id="add-work-day-form" method="POST" action="{{ route('tasks.work-logs.add', $task) }}" style="display: none;">
        @csrf
        <input type="hidden" name="work_date" id="selected-work-date">
    </form>

    <!-- Date Picker Modal -->
    <div id="date-picker-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Wybierz datę nowego dnia pracy
                    </h3>
                    <button type="button" onclick="closeDatePicker()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-4">
                    <label class="form-kt-label">Data</label>
                    <input type="date" 
                           id="work-date-input" 
                           class="form-kt-control"
                           min="{{ now()->format('Y-m-d') }}">
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeDatePicker()" 
                            class="btn-kt-secondary">
                        Anuluj
                    </button>
                    <button type="button" 
                            onclick="confirmAddWorkDay()" 
                            class="btn-kt-primary">
                        Dodaj dzień pracy
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for real-time duration calculation and deletion -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timeInputs = document.querySelectorAll('input[type="time"]');
            
            timeInputs.forEach(input => {
                input.addEventListener('change', updateDurations);
            });
            
            function updateDurations() {
                let totalHours = 0;
                const teamSize = {{ $task->getTeamSize() }};
                
                // Iteruj przez wszystkie work_logs bez używania Blade foreach w JS
                const logIds = [{{ $task->workLogs->pluck('id')->join(',') }}];
                
                logIds.forEach(logId => {
                    const startTimeEl = document.querySelector(`input[name="logs[${logId}][start_time]"]`);
                    const endTimeEl = document.querySelector(`input[name="logs[${logId}][end_time]"]`);
                    
                    if (startTimeEl && endTimeEl) {
                        const startTime = startTimeEl.value;
                        const endTime = endTimeEl.value;
                        
                        if (startTime && endTime) {
                            const start = new Date('2000-01-01 ' + startTime);
                            const end = new Date('2000-01-01 ' + endTime);
                            const diff = (end - start) / (1000 * 60 * 60); // hours
                            
                            if (diff > 0) {
                                totalHours += diff;
                                const durationEl = document.querySelector(`[data-log-id="${logId}"]`);
                                if (durationEl) {
                                    durationEl.textContent = diff.toFixed(2) + 'h';
                                }
                            } else {
                                const durationEl = document.querySelector(`[data-log-id="${logId}"]`);
                                if (durationEl) {
                                    durationEl.textContent = '0h';
                                }
                            }
                        }
                    }
                });
                
                const totalHoursElement = document.getElementById('total-hours');
                const totalRoboczogodzinieElement = document.getElementById('total-roboczogodziny');
                
                if (totalHoursElement) totalHoursElement.textContent = totalHours.toFixed(2);
                if (totalRoboczogodzinieElement) totalRoboczogodzinieElement.textContent = (totalHours * teamSize).toFixed(2);
            }
        });

        // Global function for delete button onclick
        function deleteWorkLog(workLogId) {
            if (confirm('Czy na pewno chcesz usunąć ten dzień pracy? Ta operacja jest nieodwracalna.')) {
                const form = document.getElementById('delete-work-log-form');
                form.action = `/tasks/{{ $task->id }}/work-logs/${workLogId}`;
                form.submit();
            }
        }

        // Make deleteWorkLog globally available
        window.deleteWorkLog = deleteWorkLog;

        // Global function for showing date picker
        function showDatePicker() {
            const modal = document.getElementById('date-picker-modal');
            const dateInput = document.getElementById('work-date-input');
            
            // Calculate next day after last work log
            const logIds = [{{ $task->workLogs->pluck('id')->join(',') }}];
            let lastDate = null;
            
            logIds.forEach(logId => {
                const startTimeEl = document.querySelector(`input[name="logs[${logId}][start_time]"]`);
                if (startTimeEl) {
                    // Get work date from the display element
                    const logContainer = startTimeEl.closest('.border');
                    const dateText = logContainer.querySelector('.text-base').textContent;
                    const dateParts = dateText.split('.');
                    if (dateParts.length === 3) {
                        const formattedDate = `${dateParts[2]}-${dateParts[1].padStart(2, '0')}-${dateParts[0].padStart(2, '0')}`;
                        if (!lastDate || formattedDate > lastDate) {
                            lastDate = formattedDate;
                        }
                    }
                }
            });
            
            // Set default date to next day after last work log
            if (lastDate) {
                const nextDay = new Date(lastDate);
                nextDay.setDate(nextDay.getDate() + 1);
                const formattedNextDay = nextDay.toISOString().split('T')[0];
                dateInput.value = formattedNextDay;
            } else {
                // Fallback to tomorrow
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                dateInput.value = tomorrow.toISOString().split('T')[0];
            }
            
            modal.style.display = 'block';
        }

        function closeDatePicker() {
            const modal = document.getElementById('date-picker-modal');
            modal.style.display = 'none';
        }

        function confirmAddWorkDay() {
            const dateInput = document.getElementById('work-date-input');
            const selectedDate = dateInput.value;
            
            if (!selectedDate) {
                alert('Proszę wybrać datę.');
                return;
            }
            
            // Check if date already exists
            const existingDates = [];
            const logIds = [{{ $task->workLogs->pluck('id')->join(',') }}];
            
            logIds.forEach(logId => {
                const startTimeEl = document.querySelector(`input[name="logs[${logId}][start_time]"]`);
                if (startTimeEl) {
                    const logContainer = startTimeEl.closest('.border');
                    const dateText = logContainer.querySelector('.text-base').textContent;
                    const dateParts = dateText.split('.');
                    if (dateParts.length === 3) {
                        const formattedDate = `${dateParts[2]}-${dateParts[1].padStart(2, '0')}-${dateParts[0].padStart(2, '0')}`;
                        existingDates.push(formattedDate);
                    }
                }
            });
            
            if (existingDates.includes(selectedDate)) {
                alert('Dzień pracy na wybraną datę już istnieje.');
                return;
            }
            
            // Set the date and submit form
            document.getElementById('selected-work-date').value = selectedDate;
            document.getElementById('add-work-day-form').submit();
        }

        // Make functions globally available
        window.showDatePicker = showDatePicker;
        window.closeDatePicker = closeDatePicker;
        window.confirmAddWorkDay = confirmAddWorkDay;

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('date-picker-modal');
            if (event.target === modal) {
                closeDatePicker();
            }
        });
    </script>
</x-app-layout>