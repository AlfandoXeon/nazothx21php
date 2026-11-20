<?php
use App\Core\Markdown;
use App\Core\Security;

$base = rtrim($_ENV['APP_URL'] ?? '', '/');
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="font-condensed font-bold text-2xl sm:text-3xl text-white tracking-wide">Artikel By Nazo</h1>
        <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Kelola artikel, pantau total pembaca, dan moderasi publikasi.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?= $base ?>/artikel" target="_blank" rel="noopener"
           class="flex-1 sm:flex-none justify-center inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-dark-800 border border-white/10 text-gray-300 hover:text-white hover:border-amber-500/40 transition-all">
            <i data-lucide="external-link" class="w-3.5 h-3.5 text-amber-500"></i>
            <span>Lihat Halaman</span>
        </a>
        <a href="<?= $adminBase ?>/articles/create"
           class="flex-1 sm:flex-none justify-center inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-black transition-all hover:opacity-90 shadow-md"
           style="background:#f59e0b">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tulis Artikel</span>
        </a>
    </div>
</div>

<!-- Flash -->
<?php if (!empty($flash)): ?>
<?php [$t, $m] = explode(':', $flash, 2); ?>
<div class="mb-5 px-4 py-3 rounded-xl text-sm border <?= $t === 'success' ? 'bg-green-500/10 border-green-500/20 text-green-400' : 'bg-red-500/10 border-red-500/20 text-red-400' ?>">
    <?= Security::e($m) ?>
</div>
<?php endif; ?>

<!-- Summary Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <?php
    $summaryCards = [
        ['label' => 'Total Artikel',    'value' => $totalArticles ?? 0,        'icon' => 'file-text'],
        ['label' => 'Terpublikasi',     'value' => $publishedCount ?? 0,       'icon' => 'check-circle'],
        ['label' => 'Total Pembaca',    'value' => $totalViews ?? 0,           'icon' => 'eye'],
        ['label' => 'Komentar Artikel', 'value' => $totalArticleComments ?? 0, 'icon' => 'message-square'],
    ];
    foreach ($summaryCards as $sc):
    ?>
    <div class="bg-dark-800 border border-white/5 hover:border-amber-500/20 rounded-2xl p-4 flex flex-col justify-between transition-all">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider truncate mr-1"><?= $sc['label'] ?></span>
            <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 bg-amber-500/10 text-amber-500">
                <i data-lucide="<?= $sc['icon'] ?>" class="w-3.5 h-3.5"></i>
            </div>
        </div>
        <div class="text-2xl font-condensed font-bold text-white"><?= number_format((int)$sc['value']) ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Articles Table -->
<div class="bg-dark-800 border border-white/5 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/5 text-left text-xs text-gray-600 uppercase tracking-wider">
                    <th class="px-6 py-4">Artikel</th>
                    <th class="px-4 py-4 hidden md:table-cell">Kategori</th>
                    <th class="px-4 py-4 text-center">Pembaca</th>
                    <th class="px-4 py-4 text-center hidden sm:table-cell">Komentar</th>
                    <th class="px-4 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $a):
                    $thumbUrl    = Markdown::resolveMediaUrl($a['thumbnail_url'] ?? '', $base);
                    $isPublished = ($a['status'] ?? 'draft') === 'published';
                    $dateStr     = !empty($a['published_at']) ? $a['published_at'] : $a['created_at'];
                ?>
                <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors" id="article-row-<?= (int)$a['id'] ?>">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-10 rounded-lg overflow-hidden bg-dark-600 flex-shrink-0">
                                <?php if ($thumbUrl !== ''): ?>
                                <img src="<?= htmlspecialchars($thumbUrl) ?>" class="w-full h-full object-cover" alt="">
                                <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-gray-600">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="min-w-0">
                                <div class="font-medium text-white truncate max-w-[200px] sm:max-w-[280px]">
                                    <?= Security::e($a['title']) ?>
                                </div>
                                <div class="text-[11px] text-gray-500 flex items-center gap-2 mt-0.5">
                                    <span class="font-mono truncate max-w-[160px]">/artikel/<?= Security::e($a['slug']) ?></span>
                                    <span class="hidden sm:inline">&bull;</span>
                                    <span class="hidden sm:inline"><?= htmlspecialchars(date('d M Y', strtotime($dateStr))) ?></span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 hidden md:table-cell">
                        <span class="px-2.5 py-1 rounded-lg text-xs bg-white/5 text-gray-300 border border-white/5">
                            <?= Security::e($a['category'] ?? 'Umum') ?>
                        </span>
                    </td>
                    <td class="px-4 py-4 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold">
                            <i data-lucide="eye" class="w-3 h-3"></i>
                            <?= number_format((int)($a['views_count'] ?? 0)) ?>
                        </span>
                    </td>
                    <td class="px-4 py-4 text-center hidden sm:table-cell">
                        <a href="<?= $adminBase ?>/comments?tab=artikel&article_id=<?= (int)$a['id'] ?>"
                           title="Kelola komentar artikel ini"
                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-white/5 hover:bg-amber-500/10 border border-white/5 hover:border-amber-500/30 text-gray-300 hover:text-amber-400 text-xs transition-all">
                            <i data-lucide="message-square" class="w-3 h-3"></i>
                            <?= number_format((int)($a['comments_count'] ?? 0)) ?>
                        </a>
                    </td>
                    <td class="px-4 py-4">
                        <button onclick="toggleArticle(<?= (int)$a['id'] ?>)"
                                title="Klik untuk ubah status Published / Draft"
                                class="<?= $isPublished ? 'bg-amber-500/10 text-amber-400 border-amber-500/30' : 'bg-dark-600 text-gray-500 border-white/5' ?> px-2.5 py-1 rounded-lg text-xs border transition-all hover:border-amber-500/50">
                            <?= $isPublished ? 'Published' : 'Draft' ?>
                        </button>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <?php if ($isPublished): ?>
                            <a href="<?= $base ?>/artikel/<?= rawurlencode($a['slug']) ?>" target="_blank" rel="noopener"
                               title="Buka artikel di tab baru"
                               class="p-2 rounded-lg text-gray-500 hover:text-white hover:bg-white/5 transition-all">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                            </a>
                            <?php endif; ?>
                            <a href="<?= $adminBase ?>/articles/edit/<?= (int)$a['id'] ?>"
                               title="Edit artikel"
                               class="p-2 rounded-lg text-gray-500 hover:text-amber-400 hover:bg-amber-500/10 transition-all">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </a>
                            <button onclick="deleteArticle(<?= (int)$a['id'] ?>)"
                                    title="Hapus artikel"
                                    class="p-2 rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-all">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($articles)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center text-gray-600">
                        <i data-lucide="file-text" class="w-10 h-10 mx-auto mb-3 opacity-30"></i>
                        <p class="text-sm text-gray-500">Belum ada artikel.</p>
                        <a href="<?= $adminBase ?>/articles/create"
                           class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-xl text-xs font-semibold text-black hover:opacity-90 transition-all"
                           style="background:#f59e0b">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Buat Artikel Pertama</span>
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const csrf      = '<?= htmlspecialchars($csrfToken) ?>';
    const adminBase = '<?= $adminBase ?>';

    function toggleArticle(id) {
        fetch(adminBase + '/articles/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_csrf=' + encodeURIComponent(csrf) + '&id=' + id
        }).then(r => r.json()).then(() => location.reload());
    }

    function deleteArticle(id) {
        if (!confirm('Hapus artikel ini beserta seluruh komentar dan statistik pembacanya?')) return;
        fetch(adminBase + '/articles/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_csrf=' + encodeURIComponent(csrf) + '&id=' + id
        }).then(r => r.json()).then(() => location.reload());
    }
</script>
