<?php
use App\Core\Security;
$base = rtrim($_ENV['APP_URL'] ?? '', '/');
$resolveMedia = function(?string $path) use ($base): string {
    if (empty($path)) return '';
    if (strpos($path, 'http') === 0) return $path;
    return $base . '/' . ltrim(preg_replace('#^/NazoLinktree/#', '', $path), '/');
};
?>

<!-- Header -->
<section class="flex flex-col items-center text-center pt-12 pb-8 px-4">
    <h1 class="font-condensed font-extrabold text-4xl tracking-widest text-white mb-1"
        data-aos="fade-down" data-aos-duration="600">
        <?= Security::e($settings['gallery_title'] ?? 'Gallery') ?>
    </h1>
    <div class="w-12 h-0.5 mx-auto mt-1 mb-3" style="background:#f59e0b"
         data-aos="fade-up" data-aos-delay="80"></div>
    <p class="text-gray-500 text-xs tracking-widest uppercase"
       data-aos="fade-up" data-aos-delay="120">
        <?= Security::e($settings['gallery_subtitle'] ?? '') ?>
    </p>
</section>

<!-- Tab Bar -->
<div class="max-w-3xl mx-auto px-4 mb-8" data-aos="fade-up" data-aos-delay="150">
    <div class="flex gap-0 border-b border-white/5">
        <button id="tab-photos" onclick="switchTab('photos')"
                class="tab-btn active px-5 py-3 text-sm font-medium border-b-2 border-amber-500 text-amber-500 -mb-px transition-colors">
            Foto <span class="ml-1 text-xs opacity-60"><?= count($photos) ?></span>
        </button>
        <button id="tab-videos" onclick="switchTab('videos')"
                class="tab-btn px-5 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 -mb-px transition-colors hover:text-gray-300">
            Video <span class="ml-1 text-xs opacity-60"><?= count($videos) ?></span>
        </button>
    </div>
</div>

<!-- Photos Grid -->
<div id="panel-photos" class="max-w-3xl mx-auto px-4 pb-16">
    <?php if (empty($photos)): ?>
    <div class="text-center py-20 text-gray-700" data-aos="fade-up">
        <i data-lucide="image" class="w-10 h-10 mx-auto mb-3 opacity-40"></i>
        <p class="text-sm">Belum ada foto.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <?php foreach ($photos as $i => $item):
            $mediaUrl = $resolveMedia($item['file_path']);
        ?>
        <div class="aspect-square rounded-xl overflow-hidden bg-dark-700 cursor-pointer group relative"
             onclick="openLightbox('<?= htmlspecialchars($mediaUrl) ?>', '<?= Security::e($item['caption'] ?? '') ?>')"
             data-aos="zoom-in" data-aos-delay="<?= min($i * 50, 250) ?>" data-aos-duration="400">
            <img src="<?= htmlspecialchars($mediaUrl) ?>"
                 alt="<?= Security::e($item['caption'] ?? '') ?>"
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                 loading="lazy">
            <?php if (!empty($item['caption'])): ?>
            <div class="absolute inset-x-0 bottom-0 p-2 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                <p class="text-xs text-white truncate"><?= Security::e($item['caption']) ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Videos Panel -->
<div id="panel-videos" class="hidden max-w-3xl mx-auto px-4 pb-16">
    <?php if (empty($videos)): ?>
    <div class="text-center py-20 text-gray-700" data-aos="fade-up">
        <i data-lucide="video" class="w-10 h-10 mx-auto mb-3 opacity-40"></i>
        <p class="text-sm">Belum ada video.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <?php foreach ($videos as $i => $item):
            $mediaUrl = $resolveMedia($item['file_path']);
        ?>
        <div class="rounded-xl overflow-hidden bg-dark-700 border border-white/5"
             data-aos="fade-up" data-aos-delay="<?= min($i * 80, 300) ?>">
            <video controls class="w-full aspect-video bg-black" preload="metadata">
                <source src="<?= htmlspecialchars($mediaUrl) ?>" type="video/mp4">
            </video>
            <?php if (!empty($item['caption'])): ?>
            <div class="px-4 py-3">
                <p class="text-xs text-gray-500"><?= Security::e($item['caption']) ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Lightbox -->
<div id="lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/90 backdrop-blur-sm p-4"
     onclick="closeLightbox(event)">
    <button class="absolute top-4 right-4 text-gray-400 hover:text-white z-10" onclick="closeLightbox()">
        <i data-lucide="x" class="w-6 h-6"></i>
    </button>
    <img id="lightbox-img" src="" alt=""
         class="max-w-full max-h-[85vh] rounded-xl object-contain shadow-2xl"
         data-aos="zoom-in" data-aos-duration="300">
    <p id="lightbox-caption" class="absolute bottom-6 left-0 right-0 text-center text-sm text-gray-400"></p>
</div>

<script>
function switchTab(tab) {
    const photos = document.getElementById('panel-photos');
    const videos = document.getElementById('panel-videos');
    const btnP   = document.getElementById('tab-photos');
    const btnV   = document.getElementById('tab-videos');

    if (tab === 'photos') {
        photos.classList.remove('hidden');
        videos.classList.add('hidden');
        btnP.classList.add('active','border-amber-500','text-amber-500');
        btnP.classList.remove('border-transparent','text-gray-500');
        btnV.classList.remove('active','border-amber-500','text-amber-500');
        btnV.classList.add('border-transparent','text-gray-500');
    } else {
        videos.classList.remove('hidden');
        photos.classList.add('hidden');
        btnV.classList.add('active','border-amber-500','text-amber-500');
        btnV.classList.remove('border-transparent','text-gray-500');
        btnP.classList.remove('active','border-amber-500','text-amber-500');
        btnP.classList.add('border-transparent','text-gray-500');
    }
}

function openLightbox(src, caption) {
    const lb  = document.getElementById('lightbox');
    const img = document.getElementById('lightbox-img');
    const cap = document.getElementById('lightbox-caption');
    img.src         = src;
    cap.textContent = caption;
    lb.classList.remove('hidden');
    lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeLightbox(e) {
    if (e && e.target !== document.getElementById('lightbox')) return;
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden');
    lb.classList.remove('flex');
    document.body.style.overflow = '';
}
</script>
