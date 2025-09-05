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
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Data rozpoczęcia</dt>
                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $task->start_datetime->format('d.m.Y H:i') }}
                                    <span class="text-gray-500 dark:text-gray-400">({{ $task->start_datetime->diffForHumans() }})</span>
                                </dd>
                            </div>

                            @if($task->end_datetime)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Data zakończenia</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $task->end_datetime->format('d.m.Y H:i') }}
                                        <span class="text-gray-500 dark:text-gray-400">({{ $task->end_datetime->diffForHumans() }})</span>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Czas trwania</dt>
                                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $task->duration_hours }}h ({{ $task->duration }} minut)
                                    </dd>
                                </div>
                            @endif
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

            <!-- Images Gallery -->
            @if($task->images && count($task->images) > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                            Załączone zdjęcia
                            <span class="text-sm text-gray-500 dark:text-gray-400 font-normal">({{ count($task->images) }})</span>
                        </h4>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            @foreach($task->images as $index => $image)
                                <div class="relative group">
                                    <div class="aspect-square overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700">
                                        <img src="{{ asset('storage/' . $image['path']) }}" 
                                             alt="{{ $image['original_name'] }}" 
                                             class="w-full h-full object-cover cursor-pointer hover:opacity-90 transition-opacity duration-200 gallery-image"
                                             data-image-src="{{ asset('storage/' . $image['path']) }}"
                                             data-filename="{{ $image['original_name'] }}"
                                             data-index="{{ $index + 1 }}"
                                             data-total="{{ count($task->images) }}">
                                    </div>
                                    
                                    <!-- Image overlay with filename -->
                                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent p-2 rounded-b-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <p class="text-white text-xs truncate">{{ $image['original_name'] }}</p>
                                        @if(isset($image['uploaded_at']))
                                            <p class="text-gray-300 text-xs">{{ \Carbon\Carbon::parse($image['uploaded_at'])->format('d.m.Y H:i') }}</p>
                                        @endif
                                    </div>
                                    
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
                <!-- Close button -->
                <button type="button" onclick="closeGalleryModal()" class="absolute top-4 right-4 z-10 text-white hover:text-gray-300 transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <!-- Navigation buttons -->
                <button type="button" id="prev-image" onclick="previousImage()" class="absolute left-4 top-1/2 transform -translate-y-1/2 z-10 text-white hover:text-gray-300 transition-colors bg-black bg-opacity-50 rounded-full p-3 hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                
                <button type="button" id="next-image" onclick="nextImage()" class="absolute right-4 top-1/2 transform -translate-y-1/2 z-10 text-white hover:text-gray-300 transition-colors bg-black bg-opacity-50 rounded-full p-3 hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                console.log('Gallery initialized with', totalImages, 'images');
                console.log('Images data:', images);
            @else
                console.log('No images found for this task');
            @endif
            
            // Test if modal exists
            const modal = document.getElementById('image-gallery-modal');
            console.log('Modal element found:', modal ? 'YES' : 'NO');
            
            // Add test function to window for debugging
            window.testModal = function() {
                console.log('Testing modal...');
                const modal = document.getElementById('image-gallery-modal');
                if (modal) {
                    console.log('Modal found, current style:', modal.style.cssText);
                    console.log('Modal classes:', modal.classList.toString());
                    
                    // Try both approaches
                    modal.classList.remove('hidden');
                    modal.style.display = 'block';
                    
                    console.log('Modal should be visible now');
                    console.log('New classes:', modal.classList.toString());
                    console.log('New style:', modal.style.cssText);
                } else {
                    console.log('Modal not found');
                }
            };
            
            // Add event listeners to gallery images
            const galleryImages = document.querySelectorAll('.gallery-image');
            console.log('Found', galleryImages.length, 'gallery images');
            
            galleryImages.forEach(function(img) {
                img.addEventListener('click', function() {
                    const imageSrc = this.getAttribute('data-image-src');
                    const filename = this.getAttribute('data-filename');
                    const imageIndex = parseInt(this.getAttribute('data-index'));
                    const total = parseInt(this.getAttribute('data-total'));
                    
                    console.log('Image clicked:', imageSrc, filename, imageIndex, total);
                    openGalleryModal(imageSrc, filename, imageIndex, total);
                });
            });
        });

        // Test function first
        window.openGalleryModal = function(imageSrc, filename, imageIndex, total) {
            console.log('Opening gallery modal:', imageSrc, filename, imageIndex, total);
            
            // Check if modal exists
            const modal = document.getElementById('image-gallery-modal');
            if (!modal) {
                console.error('Modal not found!');
                return;
            }
            
            console.log('Modal element:', modal);
            console.log('Modal classes before:', modal.classList.toString());
            
            currentImageIndex = imageIndex - 1; // Convert to 0-based index
            totalImages = total;
            
            // Set image source
            const modalImage = document.getElementById('modal-image');
            const modalFilename = document.getElementById('modal-filename');
            const modalCounter = document.getElementById('modal-counter');
            
            console.log('Setting image source to:', imageSrc);
            modalImage.src = imageSrc;
            modalFilename.textContent = filename;
            modalCounter.textContent = `${imageIndex} z ${total}`;
            
            // Show/hide navigation buttons
            const prevBtn = document.getElementById('prev-image');
            const nextBtn = document.getElementById('next-image');
            
            if (total > 1) {
                prevBtn.classList.toggle('hidden', currentImageIndex === 0);
                nextBtn.classList.toggle('hidden', currentImageIndex === total - 1);
            } else {
                prevBtn.classList.add('hidden');
                nextBtn.classList.add('hidden');
            }
            
            // Show modal
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            console.log('Modal display after:', modal.style.display);
            console.log('Modal should be visible now!');
        };

        window.closeGalleryModal = function() {
            console.log('Closing gallery modal');
            document.getElementById('image-gallery-modal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function previousImage() {
            if (currentImageIndex > 0) {
                currentImageIndex--;
                updateModalImage();
            }
        }

        function nextImage() {
            if (currentImageIndex < totalImages - 1) {
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
            
            // Update navigation buttons
            const prevBtn = document.getElementById('prev-image');
            const nextBtn = document.getElementById('next-image');
            
            prevBtn.classList.toggle('hidden', currentImageIndex === 0);
            nextBtn.classList.toggle('hidden', currentImageIndex === totalImages - 1);
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