@php
$aussteller = $getRecord();
$medien = $aussteller ? $aussteller->medien()
    ->orderBy('category')
    ->orderBy('title')
    ->get() : collect();

// Gruppiere Medien nach Kategorie
$medienGrouped = $medien->groupBy('category');
@endphp

<style>
    .medien-thumbnail {
        width: 100%;
        height: 300px;
    }

    .upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 0.5rem;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .upload-area:hover {
        border-color: #60a5fa;
        background-color: #eff6ff;
    }

    .medien-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
    }
</style>

<div x-data="medienManager({{ $aussteller?->id ?? 'null' }})" class="space-y-6">
    <!-- Upload Bereich -->
    <div
        class="upload-area"
        @click="$refs.fileInput.click()"
        @dragover.prevent="dragOver = true"
        @dragleave.prevent="dragOver = false"
        @drop.prevent="handleDrop($event)"
        :class="{ 'border-blue-400 bg-blue-50': dragOver }"
        x-data="{ dragOver: false }">
        <input
            type="file"
            multiple
            accept="image/*,.pdf,.doc,.docx"
            x-ref="fileInput"
            @change="handleFileUpload($event)"
            class="hidden">

        <div class="space-y-3">
            <div class="flex justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
            </div>
            <div>
                <p class="text-lg font-medium text-gray-700 mb-1">
                    Dateien hier ablegen oder klicken zum Auswählen
                </p>
                <p class="text-sm text-gray-500">
                    Bilder, PDFs und Dokumente • max. 10MB pro Datei
                </p>
            </div>
        </div>
    </div>

    <!-- Upload Progress -->
    <div x-show="uploading" class="space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">Upload läuft...</span>
            <span class="text-sm text-gray-500" x-text="`${uploadProgress}%`"></span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="`width: ${uploadProgress}%`"></div>
        </div>
    </div>

    <!-- Bestehende Medien -->
    @if($medien->count() > 0)
    @foreach($medienGrouped as $kategorie => $medienInKategorie)
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3 flex items-center">
                @switch($kategorie)
                    @case('angebot') 🛍️ Warenangebot @break
                    @case('stand') 🏪 Stand/Verkaufsstand @break  
                    @case('werkstatt') 🔨 Werkstatt @break
                    @case('vita') 📄 Vita/Lebenslauf @break
                    @default {{ $kategorie }}
                @endswitch
                <span class="ml-2 text-sm text-gray-500">({{ $medienInKategorie->count() }})</span>
            </h3>
            
            <div class="medien-grid">
                @foreach($medienInKategorie as $medium)
                <div class="bg-white border border-gray-200 rounded-lg p-2 hover:shadow-md transition-shadow flex flex-col medien-thumbnail">
                    <!-- Medien Vorschau -->
                    <div class="flex-1 bg-gray-100 rounded mb-2 flex items-center justify-center overflow-hidden">
                        @if($medium->isImage())
                        <a href="{{ $medium->url }}"
                            class="glightbox"
                            data-title="{{ $medium->title }}"
                            data-gallery="medien-{{ $kategorie }}">
                            <img src="{{ $medium->url }}"
                                alt="{{ $medium->title }}"
                                class="w-full h-full object-cover cursor-pointer rounded">
                        </a>
                        @else
                        <div class="text-center">
                            <svg class="w-32 h-32 text-gray-400 mx-auto mb-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-md text-gray-500 uppercase">{{ $medium->file_extension }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Medien Info -->
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs text-gray-600">
                                {{ $medium->getFormattedSizeAttribute() ?? 'Unbekannt' }}
                            </span>
                            <button
                                type="button"
                                @click="deleteMedium({{ $medium->id }})"
                                class="text-red-500 hover:text-red-700 transition-colors"
                                title="Löschen">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <h4 class="font-medium text-gray-900 text-xs truncate" title="{{ $medium->title }}">
                            {{ $medium->title }}
                        </h4>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endforeach
    @else
    <div class="text-center py-8 text-gray-500">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <p class="text-lg font-medium">Noch keine Medien hochgeladen</p>
        <p class="text-sm">Laden Sie Bilder und Dokumente hoch, um loszulegen.</p>
    </div>
    @endif

    <!-- Modal für Medien-Details -->
    <div x-show="showModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black bg-opacity-60 z-50 flex items-center justify-center p-4"
        @click.self="showModal = false"
        style="display: none;">

        <div class="bg-white rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-xl"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95">

            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Medien-Details eingeben</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="px-6 py-4">
                <div class="space-y-6" x-show="pendingFiles.length > 0">
                    <template x-for="(file, index) in pendingFiles" :key="index">
                        <div class="border border-gray-200 rounded-lg p-4">
                            <!-- Datei Vorschau -->
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <template x-if="file.preview">
                                        <img :src="file.preview" :alt="file.name" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!file.preview">
                                        <div class="text-center">
                                            <svg class="w-6 h-6 text-gray-400 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate" x-text="file.name"></p>
                                    <p class="text-sm text-gray-500" x-text="formatFileSize(file.size)"></p>
                                </div>
                            </div>

                            <!-- Formular -->
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategorie *</label>
                                    <select x-model="file.category" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Kategorie wählen</option>
                                        <option value="angebot">🛍️ Warenangebot</option>
                                        <option value="stand">🏪 Stand/Verkaufsstand</option>
                                        <option value="werkstatt">🔨 Werkstatt</option>
                                        <option value="vita">📄 Vita/Lebenslauf</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Titel</label>
                                    <input
                                        type="text"
                                        x-model="file.title"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Automatisch aus Dateiname">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Beschreibung (optional)</label>
                                    <textarea
                                        x-model="file.description"
                                        rows="2"
                                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Kurze Beschreibung der Datei..."></textarea>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 rounded-b-lg">
                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="showModal = false; pendingFiles = []"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Abbrechen
                    </button>
                    <button
                        type="button"
                        @click="uploadFiles()"
                        :disabled="!canUpload"
                        :class="canUpload ? 'bg-primary-600 hover:bg-blue-700 focus:ring-blue-500 text-white' : 'bg-gray-300 cursor-not-allowed text-gray-700'"
                        class="px-6 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 transition-colors">
                        <span x-show="!uploading">Hochladen</span>
                        <span x-show="uploading">Uploading...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function medienManager(ausstellerId) {
        return {
            showModal: false,
            uploading: false,
            uploadProgress: 0,
            pendingFiles: [],
            ausstellerId: ausstellerId,

            get canUpload() {
                return this.pendingFiles.length > 0 && this.pendingFiles.every(file => file.category);
            },

            handleFileUpload(event) {
                const files = Array.from(event.target.files);
                this.processFiles(files);
            },

            handleDrop(event) {
                const files = Array.from(event.dataTransfer.files);
                this.processFiles(files);
            },

            processFiles(files) {
                this.pendingFiles = [];

                files.forEach(file => {
                    const fileData = {
                        file: file,
                        name: file.name,
                        size: file.size,
                        title: this.getFilenameWithoutExtension(file.name),
                        category: '',
                        description: '',
                        preview: null
                    };

                    // Vorschau für Bilder generieren
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => fileData.preview = e.target.result;
                        reader.readAsDataURL(file);
                    }

                    this.pendingFiles.push(fileData);
                });

                if (files.length > 0) {
                    this.showModal = true;
                }
            },

            getFilenameWithoutExtension(filename) {
                return filename.substring(0, filename.lastIndexOf('.')) || filename;
            },

            formatFileSize(bytes) {
                if (bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            },

            async uploadFiles() {
                if (!this.ausstellerId) {
                    alert('Aussteller muss zuerst gespeichert werden.');
                    return;
                }

                this.uploading = true;
                this.uploadProgress = 0;

                try {
                    for (let i = 0; i < this.pendingFiles.length; i++) {
                        const fileData = this.pendingFiles[i];
                        const formData = new FormData();

                        formData.append('file', fileData.file);
                        formData.append('category', fileData.category);
                        formData.append('title', fileData.title);
                        formData.append('description', fileData.description);
                        formData.append('aussteller_id', this.ausstellerId);

                        const response = await fetch('/admin/medien/upload', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Upload fehlgeschlagen');
                        }

                        this.uploadProgress = Math.round(((i + 1) / this.pendingFiles.length) * 100);
                    }

                    // Seite neu laden um neue Medien anzuzeigen
                    window.location.reload();

                } catch (error) {
                    alert('Fehler beim Upload: ' + error.message);
                } finally {
                    this.uploading = false;
                    this.showModal = false;
                    this.pendingFiles = [];
                }
            },

            async deleteMedium(mediumId) {
                if (!confirm('Medium wirklich löschen?')) return;

                try {
                    const response = await fetch(`/admin/medien/${mediumId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (response.ok) {
                        window.location.reload();
                    } else {
                        throw new Error('Löschen fehlgeschlagen');
                    }
                } catch (error) {
                    alert('Fehler beim Löschen: ' + error.message);
                }
            },

        }
    }
</script>

@pushonce('scripts')
<script type="module">
    const lightbox = GLightbox(@json(config('filament-lightbox')));
</script>
@endpushonce