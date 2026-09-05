<?php use App\Core\Security; ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="font-condensed font-bold text-3xl text-white tracking-wide">Gallery</h1>
        <p class="text-gray-600 text-sm mt-1">Upload dan kelola foto serta video.</p>
    </div>
    <button onclick="openModal('modal-upload')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-black hover:opacity-90"
            style="background:#f59e0b">
        <i data-lucide="upload-cloud" class="w-4 h-4"></i> Upload
    </button>
</div>

<!-- Flash -->
<?php if (!empty($flash)): ?>
<?php [$t,$m] = explode(':', $flash, 2); ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm border <?= $t==='success'?'bg-green-500/10 border-green-500/20 text-green-400':'bg-red-500/10 border-red-500/20 text-red-400' ?>">
    <?= Security::e($m) ?>
</div>
<?php endif; ?>

<!-- Gallery Grid -->
<?php if (empty($items)): ?>
<div class="text-center py-20 text-gray-700">
    <i data-lucide="image" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
    <p class="text-sm">Belum ada file. Upload foto atau video.</p>
</div>
<?php else: ?>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
    <?php foreach ($items as $item): ?>
    <div class="bg-dark-800 border border-white/5 rounded-2xl overflow-hidden group relative">
        <?php if ($item['file_type'] === 'image'): ?>
        <div class="aspect-square">
            <img src="<?= htmlspecialchars($item['file_path']) ?>" alt="" class="w-full h-full object-cover" loading="lazy">
        </div>
        <?php else: ?>
        <div class="aspect-square bg-dark-700 flex flex-col items-center justify-center gap-2">
            <i data-lucide="video" class="w-10 h-10 text-gray-600"></i>
            <span class="text-xs text-gray-700">Video</span>
        </div>
        <?php endif; ?>

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-all flex flex-col items-center justify-center gap-2 p-3">
            <?php if (!empty($item['caption'])): ?>
            <p class="text-xs text-gray-300 text-center truncate w-full"><?= Security::e($item['caption']) ?></p>
            <?php endif; ?>
            <span class="text-xs px-2 py-0.5 rounded-full border border-white/20 text-gray-400">
                <?= $item['file_type'] === 'image' ? 'Foto' : 'Video' ?>
            </span>
            <button onclick="deleteMedia(<?= $item['id'] ?>)"
                    class="px-3 py-1.5 rounded-lg bg-red-500/20 text-red-400 text-xs hover:bg-red-500 hover:text-white transition-all flex items-center gap-1">
                <i data-lucide="trash-2" class="w-3 h-3"></i> Hapus
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Upload Modal -->
<div id="modal-upload" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-dark-800 border border-white/10 rounded-2xl w-full max-w-md">
        <div class="p-6 border-b border-white/5 flex items-center justify-between">
            <h3 class="font-condensed font-bold text-xl text-white">Upload File</h3>
            <button onclick="closeUploadModal()" class="text-gray-600 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" action="<?= $adminBase ?>/gallery/upload" enctype="multipart/form-data"
              class="p-6 space-y-4" id="upload-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">

            <!-- Drop Zone -->
            <div id="drop-zone"
                 class="relative border-2 border-dashed border-white/10 rounded-xl transition-all duration-200 cursor-pointer overflow-hidden"
                 style="min-height: 180px;">

                <!-- Default state -->
                <div id="dz-default" class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center pointer-events-none">
                    <i data-lucide="upload-cloud" class="w-10 h-10 mb-3 text-gray-600"></i>
                    <p class="text-sm text-gray-500">Klik atau seret file ke sini</p>
                    <p class="text-xs text-gray-700 mt-1">JPG, PNG, WebP, GIF, MP4, WebM — maks. 10MB</p>
                </div>

                <!-- Image Preview state (hidden by default) -->
                <div id="dz-preview" class="hidden absolute inset-0">
                    <img id="preview-img" src="" alt="" class="w-full h-full object-cover">
                    <!-- Remove overlay -->
                    <div class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center">
                        <button type="button" onclick="clearFile(event)"
                                class="flex items-center gap-1.5 px-3 py-2 rounded-lg bg-red-500/80 text-white text-xs font-medium hover:bg-red-500 transition-colors">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Ganti File
                        </button>
                    </div>
                </div>

                <!-- Video Preview state (hidden by default) -->
                <div id="dz-video" class="hidden absolute inset-0 flex flex-col items-center justify-center gap-2 bg-dark-700">
                    <i data-lucide="film" class="w-12 h-12 text-amber-500"></i>
                    <p id="video-name" class="text-xs text-gray-400 px-4 text-center truncate max-w-full"></p>
                    <button type="button" onclick="clearFile(event)"
                            class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-500/20 text-red-400 text-xs hover:bg-red-500 hover:text-white transition-all">
                        <i data-lucide="x" class="w-3 h-3"></i> Ganti File
                    </button>
                </div>

                <!-- Drag Hover Overlay -->
                <div id="dz-dragover"
                     class="hidden absolute inset-0 flex items-center justify-center rounded-xl pointer-events-none"
                     style="background: rgba(245,158,11,0.08); border-color: #f59e0b;">
                    <div class="text-center">
                        <i data-lucide="download" class="w-10 h-10 mx-auto mb-2 text-amber-500"></i>
                        <p class="text-sm text-amber-400 font-medium">Lepaskan file di sini</p>
                    </div>
                </div>

                <!-- Invisible click target -->
                <div class="absolute inset-0" id="dz-click-target"></div>
            </div>

            <!-- Hidden file input -->
            <input type="file" name="file" id="file-input" class="hidden" accept="image/*,video/*">

            <div>
                <label class="text-xs text-gray-500 mb-1 block">Caption (opsional)</label>
                <input type="text" name="caption" maxlength="255"
                       class="w-full bg-dark-700 border border-white/5 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-700 focus:outline-none focus:border-amber-500/50 transition-colors"
                       placeholder="Keterangan foto/video">
            </div>

            <button type="submit" id="submit-btn"
                    class="w-full py-3 rounded-xl text-sm font-semibold text-black hover:opacity-90 transition-all"
                    style="background:#f59e0b">
                Upload
            </button>
        </form>
    </div>
</div>

<script>
    const csrf      = '<?= htmlspecialchars($csrfToken) ?>';
    const adminBase = '<?= $adminBase ?>';

    // ── Modal ────────────────────────────────────────────────────
    function openModal(id)  {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }
    function closeUploadModal() {
        clearFile();
        closeModal('modal-upload');
    }

    // ── Drop Zone Setup ──────────────────────────────────────────
    const dropZone   = document.getElementById('drop-zone');
    const fileInput  = document.getElementById('file-input');
    const dzDefault  = document.getElementById('dz-default');
    const dzPreview  = document.getElementById('dz-preview');
    const dzVideo    = document.getElementById('dz-video');
    const dzDragover = document.getElementById('dz-dragover');
    const previewImg = document.getElementById('preview-img');
    const videoName  = document.getElementById('video-name');

    // Click to open file picker
    document.getElementById('dz-click-target').addEventListener('click', () => {
        fileInput.click();
    });

    // Drag over — highlight zone
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.style.borderColor = '#f59e0b';
        dzDragover.classList.remove('hidden');
    });

    // Drag leave — reset
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.style.borderColor = '';
        dzDragover.classList.add('hidden');
    });

    // Drop — handle file
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.style.borderColor = '';
        dzDragover.classList.add('hidden');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            setFile(files[0]);
        }
    });

    // File input change
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            setFile(fileInput.files[0]);
        }
    });

    function setFile(file) {
        // Sync to actual file input if dropped (create DataTransfer)
        if (file && fileInput.files.length === 0) {
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
            } catch(e) {}
        }

        const isImage = file.type.startsWith('image/');
        const isVideo = file.type.startsWith('video/');

        // Hide default
        dzDefault.classList.add('hidden');

        if (isImage) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                dzPreview.classList.remove('hidden');
                dzVideo.classList.add('hidden');
                // Re-init lucide for buttons inside preview
                if (window.lucide) lucide.createIcons();
            };
            reader.readAsDataURL(file);
        } else if (isVideo) {
            videoName.textContent = file.name;
            dzVideo.classList.remove('hidden');
            dzPreview.classList.add('hidden');
            if (window.lucide) lucide.createIcons();
        }

        // Update zone border
        dropZone.style.borderColor = '#f59e0b';
        dropZone.style.borderStyle = 'solid';
    }

    function clearFile(e) {
        if (e) e.stopPropagation();
        fileInput.value = '';
        previewImg.src  = '';
        videoName.textContent = '';
        dropZone.style.borderColor = '';
        dropZone.style.borderStyle = '';
        dzDefault.classList.remove('hidden');
        dzPreview.classList.add('hidden');
        dzVideo.classList.add('hidden');
    }

    // ── Delete Media ─────────────────────────────────────────────
    function deleteMedia(id) {
        if (!confirm('Hapus file ini dari server?')) return;
        fetch(adminBase + '/gallery/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_csrf=' + encodeURIComponent(csrf) + '&id=' + id
        }).then(r => r.json()).then(() => location.reload());
    }
</script>
