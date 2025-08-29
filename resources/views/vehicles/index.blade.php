<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-1">
                    {{ __('Pojazdy') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Zarządzanie flotą pojazdów (Admin tylko)
                </p>
            </div>
            
            @can('create', App\Models\Vehicle::class)
                <div class="mt-3 sm:mt-0">
                    <a href="{{ route('vehicles.create') }}" class="btn-kt-primary">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                        </svg>
                        Dodaj pojazd
                    </a>
                </div>
            @endcan
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

            <div class="kt-card">
                @if($vehicles->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table-kt">
                            <thead>
                                <tr>
                                    <th>Pojazd</th>
                                    <th>Rejestracja</th>
                                    <th>Status</th>
                                    <th>Aktywne zadania</th>
                                    <th>Utworzono</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicles as $vehicle)
                                    <tr>
                                        <td>
                                            <a href="{{ route('vehicles.show', $vehicle) }}" class="flex items-center hover:bg-gray-50 dark:hover:bg-gray-700/50 -mx-4 px-4 py-2 rounded transition-colors">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-purple-100 dark:bg-purple-900/20 flex items-center justify-center">
                                                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">{{ $vehicle->name }}</div>
                                                    @if($vehicle->description)
                                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($vehicle->description, 40) }}</div>
                                                    @endif
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-xs">{{ $vehicle->registration }}</span>
                                        </td>
                                        <td>
                                            @if($vehicle->is_active)
                                                <span class="badge-kt-success">Aktywny</span>
                                            @else
                                                <span class="badge-kt-danger">Nieaktywny</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($vehicle->tasks_count > 0)
                                                <span class="badge-kt-primary">
                                                    {{ $vehicle->tasks_count }} {{ Str::plural('zadanie', $vehicle->tasks_count) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">Brak zadań</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $vehicle->created_at->format('d.m.Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="kt-card-footer">
                        {{ $vehicles->links() }}
                    </div>
                @else
                    <div class="kt-card-body text-center">
                        <div class="text-gray-500 dark:text-gray-400 mb-4">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Brak pojazdów</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">W systemie nie ma jeszcze żadnych pojazdów.</p>
                        @can('create', App\Models\Vehicle::class)
                            <a href="{{ route('vehicles.create') }}" class="btn-kt-primary">
                                Dodaj pierwszy pojazd
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>