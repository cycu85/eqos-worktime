<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Szczegóły zadania
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Utworzone {{ $task->created_at->format('d.m.Y H:i') }}
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('tasks.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Powrót do listy
                </a>

                @can('update', $task)
                    @if($task->isLockedForUser(auth()->user()))
                        <span class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed" title="Zadanie jest zablokowane - tylko Administrator i Kierownik mogą edytować zadania ze statusem 'Zaakceptowane'">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                            </svg>
                            Zablokowane
                        </span>
                    @else
                        <a href="{{ route('tasks.edit', $task) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                            </svg>
                            Edytuj
                        </a>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Task Header Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $task->title }}</h3>
                            @php
                                $statusColors = [
                                    'planned' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                    'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                    'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                    'accepted' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                                ];
                                $statusLabels = [
                                    'planned' => 'Planowane',
                                    'in_progress' => 'W trakcie',
                                    'completed' => 'Ukończone',
                                    'cancelled' => 'Anulowane',
                                    'accepted' => 'Zaakceptowane'
                                ];
                            @endphp
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full {{ $statusColors[$task->status] ?? 'bg-gray-100 text-gray-800' }} mt-2">
                                {{ $statusLabels[$task->status] ?? $task->status }}
                            </span>
                        </div>
                        @can('delete', $task)
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Czy na pewno chcesz usunąć to zadanie?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Usuń
                                </button>
                            </form>
                        @endcan
                    </div>

                    @if($task->description)
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300">{{ $task->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Task Details -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Szczegóły zadania</h4>
                        
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Lider zadania</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $task->leader->name }}</dd>
                            </div>

                            @if($task->taskType)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Rodzaj zadania</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $task->taskType->name }}
                                        </span>
                                        @if($task->taskType->description)
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $task->taskType->description }}
                                            </div>
                                        @endif
                                    </dd>
                                </div>
                            @endif

                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pojazdy</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    @if($task->vehicles->count() > 0)
                                        @foreach($task->vehicles as $vehicle)
                                            {{ $vehicle->name }} ({{ $vehicle->registration }})@if(!$loop->last), @endif
                                        @endforeach
                                    @else
                                        <span class="text-gray-500 dark:text-gray-400">Brak przypisanych pojazdów</span>
                                    @endif
                                </dd>
                            </div>

                            @if($task->team)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Zespół</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $task->team }}</dd>
                                </div>
                            @endif

                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    Okres realizacji
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $task->start_date->format('d.m.Y') }} - {{ $task->end_date->format('d.m.Y') }}
                                    <span class="text-gray-500 dark:text-gray-400">({{ $task->workLogs->count() }} dni pracy)</span>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Łączny czas pracy</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $task->duration_hours }}h 
                                    <span class="text-gray-500 dark:text-gray-400">({{ $task->getTotalRoboczogodziny() }}h roboczogodziny)</span>
                                    @can('update', $task)
                                        <div class="mt-2">
                                            <a href="{{ route('tasks.work-logs', $task) }}" class="btn-kt-secondary text-xs">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                                                </svg>
                                                Edytuj harmonogram
                                            </a>
                                        </div>
                                    @endcan
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

            </div>

            <!-- Notes -->
            @if($task->notes)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Notatki</h4>
                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $task->notes }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Attachments Gallery -->
            @if($task->attachments && count($task->attachments) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                            Załączniki
                            <span class="text-sm text-gray-500 dark:text-gray-400 font-normal">({{ count($task->attachments) }})</span>
                        </h4>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            @foreach($task->attachments as $index => $attachment)
                                <div class="relative group">
                                    @if($attachment->isImage())
                                        <div class="aspect-square overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700">
                                            <img src="{{ $attachment->url }}" 
                                                 alt="{{ $attachment->original_name }}" 
                                                 class="w-full h-full object-cover cursor-pointer hover:opacity-75 hover:scale-105 transition-all duration-200 gallery-image"
                                                 style="cursor: pointer !important;"
                                                 data-image-src="{{ $attachment->url }}"
                                                 data-filename="{{ $attachment->original_name }}"
                                                 data-index="{{ $index + 1 }}"
                                                 data-total="{{ count($task->attachments->where('mime_type', 'like', 'image/%')) }}">
                                        </div>
                                    @else
                                        <div class="aspect-square flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-600">
                                            <div class="text-center">
                                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="mt-1 text-xs text-gray-500 truncate px-2">{{ Str::limit($attachment->original_name, 15) }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Attachment overlay with info -->
                                    @if($attachment->isImage())
                                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-2 rounded-b-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                            <p class="text-white text-xs truncate">{{ $attachment->original_name }}</p>
                                            <p class="text-gray-300 text-xs">{{ $attachment->formatted_size }} • {{ $attachment->created_at->format('d.m.Y H:i') }}</p>
                                        </div>
                                    @else
                                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-2 rounded-b-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                            <p class="text-white text-xs">{{ $attachment->formatted_size }}</p>
                                            <p class="text-gray-300 text-xs">{{ $attachment->created_at->format('d.m.Y H:i') }}</p>
                                        </div>
                                    @endif
                                    
                                    <!-- Click to view hint -->
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-lg">
                                        <div class="bg-white dark:bg-gray-800 rounded-full p-2">
                                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Gallery info -->
                        <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                            <p>Kliknij na zdjęcie, aby wyświetlić powiększenie</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Image Gallery Modal -->
    <div id="image-gallery-modal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" aria-labelledby="gallery-modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-black bg-opacity-90 transition-opacity" onclick="closeGalleryModal()"></div>

            <!-- Modal content -->
            <div class="relative bg-transparent max-w-6xl max-h-full w-full">
                <!-- Fixed positioned buttons relative to modal -->
                <!-- Close button - fixed top right -->
                <button type="button" onclick="closeGalleryModal()" class="absolute z-50 text-white hover:text-gray-300 transition-colors bg-black bg-opacity-80 hover:bg-opacity-100 rounded-full p-3 border-2 border-white" style="left: 80%; top: auto;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Navigation buttons - fixed left and right -->
                <button type="button" id="prev-image" onclick="previousImage()" class="absolute z-40 text-white hover:text-gray-300 transition-all duration-200 bg-black bg-opacity-80 hover:bg-opacity-100 rounded-full p-4 border-2 border-white hidden" style="top: 50%; left: 2%; transform: translateY(-50%);">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <button type="button" id="next-image" onclick="nextImage()" class="absolute z-40 text-white hover:text-gray-300 transition-all duration-200 bg-black bg-opacity-80 hover:bg-opacity-100 rounded-full p-4 border-2 border-white hidden" style="top: 50%; left: 95%; transform: translateY(-50%);">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <!-- Image container -->
                <div class="text-center">
                    <img id="modal-image" src="" alt="" class="max-w-full max-h-[80vh] mx-auto rounded-lg shadow-2xl">
                    
                    <!-- Image info -->
                    <div class="mt-4 text-white">
                        <p id="modal-filename" class="text-lg font-medium"></p>
                        <p id="modal-counter" class="text-sm text-gray-300 mt-1"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentImageIndex = 0;
        let totalImages = 0;
        let images = [];

        // Initialize images array from task data
        document.addEventListener('DOMContentLoaded', function() {
            @if($task->images && count($task->images) > 0)
                images = @json($task->images);
                totalImages = images.length;
            @endif
            
            // Add event listeners to gallery images
            const galleryImages = document.querySelectorAll('.gallery-image');
            
            // Setup gallery image click handlers
            galleryImages.forEach(function(img, index) {
                // Ensure images are clickable
                img.style.cursor = 'pointer';
                img.style.zIndex = '1';
                img.style.position = 'relative';
                
                img.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const imageSrc = this.getAttribute('data-image-src');
                    const filename = this.getAttribute('data-filename');
                    const imageIndex = parseInt(this.getAttribute('data-index'));
                    const total = parseInt(this.getAttribute('data-total'));
                    
                    openGalleryModal(imageSrc, filename, imageIndex, total);
                });
            });
            
        });

        window.openGalleryModal = function(imageSrc, filename, imageIndex, total) {
            const modal = document.getElementById('image-gallery-modal');
            if (!modal) {
                console.error('Modal not found!');
                return;
            }
            
            currentImageIndex = imageIndex - 1;
            totalImages = total;
            
            // Set image source
            const modalImage = document.getElementById('modal-image');
            const modalFilename = document.getElementById('modal-filename');
            const modalCounter = document.getElementById('modal-counter');
            
            modalImage.src = imageSrc;
            modalFilename.textContent = filename;
            modalCounter.textContent = `${imageIndex} z ${total}`;
            
            // Show/hide navigation buttons
            const prevBtn = document.getElementById('prev-image');
            const nextBtn = document.getElementById('next-image');
            
            if (total > 1) {
                prevBtn.classList.remove('hidden');
                nextBtn.classList.remove('hidden');
                prevBtn.classList.toggle('opacity-50', currentImageIndex === 0);
                nextBtn.classList.toggle('opacity-50', currentImageIndex === total - 1);
            } else {
                prevBtn.classList.add('hidden');
                nextBtn.classList.add('hidden');
            }
            
            // Show modal
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        };

        window.closeGalleryModal = function() {
            const modal = document.getElementById('image-gallery-modal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        window.previousImage = function() {
            const prevBtn = document.getElementById('prev-image');
            if (currentImageIndex > 0 && !prevBtn.disabled) {
                currentImageIndex--;
                updateModalImage();
            }
        }

        window.nextImage = function() {
            const nextBtn = document.getElementById('next-image');
            if (currentImageIndex < totalImages - 1 && !nextBtn.disabled) {
                currentImageIndex++;
                updateModalImage();
            }
        }

        function updateModalImage() {
            const image = images[currentImageIndex];
            const imageSrc = "{{ asset('storage/') }}/" + image.path;
            
            document.getElementById('modal-image').src = imageSrc;
            document.getElementById('modal-filename').textContent = image.original_name;
            document.getElementById('modal-counter').textContent = `${currentImageIndex + 1} z ${totalImages}`;
            
            // Update navigation buttons visibility and state
            const prevBtn = document.getElementById('prev-image');
            const nextBtn = document.getElementById('next-image');
            
            if (totalImages > 1) {
                // Show buttons but make them semi-transparent when disabled
                prevBtn.classList.remove('hidden');
                nextBtn.classList.remove('hidden');
                
                if (currentImageIndex === 0) {
                    prevBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    prevBtn.disabled = true;
                } else {
                    prevBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    prevBtn.disabled = false;
                }
                
                if (currentImageIndex === totalImages - 1) {
                    nextBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    nextBtn.disabled = true;
                } else {
                    nextBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    nextBtn.disabled = false;
                }
            } else {
                prevBtn.classList.add('hidden');
                nextBtn.classList.add('hidden');
            }
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('image-gallery-modal');
            if (modal.style.display === 'none' || modal.style.display === '') {
                return;
            }
            
            if (e.key === 'Escape') {
                closeGalleryModal();
            } else if (e.key === 'ArrowLeft') {
                previousImage();
            } else if (e.key === 'ArrowRight') {
                nextImage();
            }
        });
    </script>
</x-app-layout>