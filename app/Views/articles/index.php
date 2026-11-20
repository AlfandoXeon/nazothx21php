<?php
use App\Core\Markdown;
use App\Core\Security;

$base         = rtrim($_ENV['APP_URL'] ?? '', '/');
$pageHeading  = !empty($settings['article_title']) ? $settings['article_title'] : 'Artikel By Nazo';
$pageSubtitle = !empty($settings['article_subtitle']) ? $settings['article_subtitle'] : 'Tulisan, panduan, dan catatan terbaru dari Nazo.';

$buildQueryUrl = function (array $overrides = []) use ($base, $search, $activeCat, $currentPage): string {
    $params = [
        'q'        => $search,
        'kategori' => $activeCat,
        'page'     => $currentPage,
    ];
    foreach ($overrides as $k => $v) {
        $params[$k] = $v;
    }
    $clean = [];
    if (!empty($params['q'])) {
        $clean['q'] = $params['q'];
    }
    if (!empty($params['kategori'])) {
        $clean['kategori'] = $params['kategori'];
    }
    if (!empty($params['page']) && (int)$params['page'] > 1) {
        $clean['page'] = (int)$params['page'];
    }
    $qs = http_build_query($clean);
    return $base . '/artikel' . ($qs !== '' ? '?' . $qs : '');
};
?>

<!-- Header -->
<section class="flex flex-col items-center text-center pt-12 pb-8 px-4">
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-xs text-amber-400 font-semibold mb-3"
         data-aos="fade-down" data-aos-duration="500">
        <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
        <span>Artikel</span>
    </div>

    <h1 class="font-condensed font-extrabold text-4xl tracking-widest text-white uppercase mb-1"
        data-aos="fade-down" data-aos-duration="600">
        <?= Security::e($pageHeading) ?>
    </h1>
    <div class="w-12 h-0.5 mx-auto mt-1 mb-3" style="background:#f59e0b"
         data-aos="fade-up" data-aos-delay="80"></div>
    <p class="text-gray-400 text-xs sm:text-sm tracking-wide max-w-md"
       data-aos="fade-up" data-aos-delay="120">
        <?= Security::e($pageSubtitle) ?>
    </p>
</section>

<!-- Search & Categories -->
<section class="max-w-3xl mx-auto px-4 mb-8 space-y-4" data-aos="fade-up" data-aos-delay="140">
    <form method="GET" action="<?= $base ?>/artikel" class="flex gap-2">
        <?php if (!empty($activeCat)): ?>
        <input type="hidden" name="kategori" value="<?= Security::e($activeCat) ?>">
        <?php endif; ?>
        <div class="relative flex-1">
            <i data-lucide="search" class="w-4 h-4 text-gray-500 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
            <input type="text" name="q" value="<?= Security::e($search) ?>"
                   placeholder="Cari judul atau topik artikel..."
                   maxlength="100"
                   class="w-full bg-dark-800 border border-white/10 rounded-xl pl-10 pr-4 py-2.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-amber-500/50 transition-colors">
        </div>
        <button type="submit"
                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-black hover:opacity-90 transition-all flex-shrink-0"
                style="background:#f59e0b">
            Cari
        </button>
        <?php if ($search !== '' || $activeCat !== ''): ?>
        <a href="<?= $base ?>/artikel"
           title="Reset filter"
           class="px-3 py-2.5 rounded-xl text-xs text-gray-400 hover:text-white bg-dark-800 border border-white/10 flex items-center justify-center transition-colors">
            <i data-lucide="x" class="w-4 h-4"></i>
        </a>
        <?php endif; ?>
    </form>

    <?php if (!empty($categories)): ?>
    <div class="flex items-center gap-2 overflow-x-auto pb-1">
        <a href="<?= htmlspecialchars($buildQueryUrl(['kategori' => '', 'page' => 1])) ?>"
           class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap border transition-all <?= $activeCat === '' ? 'bg-amber-500/15 text-amber-400 border-amber-500/30' : 'bg-dark-800 text-gray-400 border-white/5 hover:text-white hover:border-white/15' ?>">
            Semua
        </a>
        <?php foreach ($categories as $cat): ?>
        <a href="<?= htmlspecialchars($buildQueryUrl(['kategori' => $cat, 'page' => 1])) ?>"
           class="px-3 py-1.5 rounded-lg text-xs font-medium whitespace-nowrap border transition-all <?= $activeCat === $cat ? 'bg-amber-500/15 text-amber-400 border-amber-500/30' : 'bg-dark-800 text-gray-400 border-white/5 hover:text-white hover:border-white/15' ?>">
            <?= Security::e($cat) ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- Articles Grid -->
<section class="max-w-3xl mx-auto px-4 pb-16">
    <?php if (empty($articles)): ?>
    <div class="text-center py-20 bg-dark-800/40 border border-white/5 rounded-2xl px-6" data-aos="fade-up">
        <i data-lucide="file-text" class="w-10 h-10 mx-auto mb-3 text-gray-600"></i>
        <?php if ($search !== '' || $activeCat !== ''): ?>
        <p class="text-sm text-gray-400">Tidak ada artikel yang cocok dengan pencarian Anda.</p>
        <a href="<?= $base ?>/artikel"
           class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-xl text-xs font-semibold text-amber-400 bg-amber-500/10 border border-amber-500/30 hover:bg-amber-500/20 transition-all">
            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
            <span>Tampilkan Semua Artikel</span>
        </a>
        <?php else: ?>
        <p class="text-sm text-gray-500">Belum ada artikel yang dipublikasikan.</p>
        <p class="text-xs text-gray-600 mt-1">Nantikan tulisan terbaru dari <?= Security::e($profile['name'] ?? 'NAZO') ?> segera!</p>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <?php foreach ($articles as $i => $item):
            $thumbUrl    = Markdown::resolveMediaUrl($item['thumbnail_url'] ?? '', $base);
            $excerpt     = !empty($item['excerpt']) ? $item['excerpt'] : Markdown::excerpt($item['content'] ?? '', 130);
            $readMinutes = Markdown::readingTime($item['content'] ?? '');
            $dateStr     = !empty($item['published_at']) ? $item['published_at'] : $item['created_at'];
        ?>
        <a href="<?= $base ?>/artikel/<?= rawurlencode($item['slug']) ?>"
           class="link-card group flex flex-col rounded-2xl overflow-hidden bg-dark-800 border border-white/5 hover:border-amber-500/40 transition-all duration-200"
           data-aos="fade-up" data-aos-delay="<?= min($i * 60, 240) ?>">

            <!-- Cover Thumbnail -->
            <div class="relative aspect-video bg-dark-700 overflow-hidden">
                <?php if ($thumbUrl !== ''): ?>
                <img src="<?= htmlspecialchars($thumbUrl) ?>"
                     alt="<?= Security::e($item['title']) ?>"
                     loading="lazy"
                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                     onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-dark-700 to-dark-900 text-amber-500/40\'><span class=\'font-condensed font-extrabold text-3xl tracking-widest\'>NAZO.</span></div>'">
                <?php else: ?>
                <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-dark-700 via-dark-800 to-dark-900 border-b border-white/5">
                    <span class="font-condensed font-extrabold text-3xl tracking-widest text-white/15">NAZO<span class="text-amber-500/40">.</span></span>
                    <span class="text-[10px] uppercase tracking-[0.25em] text-amber-500/50 mt-1">Artikel</span>
                </div>
                <?php endif; ?>

                <?php if (!empty($item['category'])): ?>
                <span class="absolute top-3 left-3 px-2.5 py-1 rounded-lg bg-black/75 backdrop-blur-sm border border-amber-500/30 text-[10px] font-semibold uppercase tracking-wider text-amber-400">
                    <?= Security::e($item['category']) ?>
                </span>
                <?php endif; ?>
            </div>

            <!-- Body -->
            <div class="p-5 flex-1 flex flex-col">
                <div class="flex items-center gap-3 text-[11px] text-gray-500 mb-2">
                    <span class="inline-flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3 h-3 text-amber-500/70"></i>
                        <?= htmlspecialchars(date('d M Y', strtotime($dateStr))) ?>
                    </span>
                    <span>&bull;</span>
                    <span class="inline-flex items-center gap-1">
                        <i data-lucide="clock" class="w-3 h-3 text-amber-500/70"></i>
                        <?= $readMinutes ?> mnt baca
                    </span>
                </div>

                <h2 class="font-condensed font-bold text-xl text-white group-hover:text-amber-400 transition-colors leading-snug mb-2 line-clamp-2">
                    <?= Security::e($item['title']) ?>
                </h2>

                <p class="text-xs sm:text-sm text-gray-400 leading-relaxed line-clamp-3 mb-4 flex-1">
                    <?= Security::e($excerpt) ?>
                </p>

                <!-- Footer Stats -->
                <div class="pt-3 border-t border-white/5 flex items-center justify-between text-xs text-gray-500">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1">
                            <i data-lucide="eye" class="w-3.5 h-3.5 text-amber-500"></i>
                            <span><?= number_format((int)($item['views_count'] ?? 0)) ?></span>
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <i data-lucide="message-square" class="w-3.5 h-3.5 text-gray-500"></i>
                            <span><?= number_format((int)($item['comments_count'] ?? 0)) ?></span>
                        </span>
                    </div>
                    <span class="inline-flex items-center gap-1 text-amber-400 font-medium group-hover:translate-x-0.5 transition-transform">
                        <span>Baca</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="mt-8 flex items-center justify-center gap-2" data-aos="fade-up">
        <?php if ($currentPage > 1): ?>
        <a href="<?= htmlspecialchars($buildQueryUrl(['page' => $currentPage - 1])) ?>"
           class="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl text-xs font-medium text-gray-300 bg-dark-800 border border-white/10 hover:border-amber-500/40 hover:text-amber-400 transition-all">
            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
            <span>Sebelumnya</span>
        </a>
        <?php endif; ?>

        <span class="px-3 py-2 text-xs text-gray-500">
            Halaman <strong class="text-white"><?= (int)$currentPage ?></strong> dari <?= (int)$totalPages ?>
        </span>

        <?php if ($currentPage < $totalPages): ?>
        <a href="<?= htmlspecialchars($buildQueryUrl(['page' => $currentPage + 1])) ?>"
           class="inline-flex items-center gap-1 px-3.5 py-2 rounded-xl text-xs font-medium text-gray-300 bg-dark-800 border border-white/10 hover:border-amber-500/40 hover:text-amber-400 transition-all">
            <span>Berikutnya</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</section>
