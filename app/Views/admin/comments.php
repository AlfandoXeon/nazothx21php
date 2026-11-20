<?php
use App\Core\Security;

$base            = rtrim($_ENV['APP_URL'] ?? '', '/');
$activeTab       = ($activeTab ?? 'main') === 'artikel' ? 'artikel' : 'main';
$articleComments = $articleComments ?? [];
$filterArticleId = $filterArticleId ?? null;
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="font-condensed font-bold text-2xl sm:text-3xl text-white tracking-wide">Komentar Publik</h1>
        <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Moderasi komentar dari halaman utama dan setiap artikel.</p>
    </div>
    <?php if (!empty($filterArticleId)): ?>
    <a href="<?= $adminBase ?>/comments?tab=artikel"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-medium text-amber-400 bg-amber-500/10 border border-amber-500/30 hover:bg-amber-500/20 transition-all self-start sm:self-auto">
        <i data-lucide="x" class="w-3.5 h-3.5"></i>
        <span>Tampilkan Semua Komentar Artikel</span>
    </a>
    <?php endif; ?>
</div>

<!-- Tab Bar -->
<div class="flex gap-1 border-b border-white/5 mb-6">
    <button type="button" id="btn-tab-main" onclick="switchCommentTab('main')"
            class="px-5 py-3 text-sm font-medium border-b-2 -mb-px transition-colors <?= $activeTab === 'main' ? 'border-amber-500 text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-300' ?>">
        Komentar Utama
        <span class="ml-1.5 text-xs px-2 py-0.5 rounded-full bg-white/5"><?= count($comments) ?></span>
    </button>
    <button type="button" id="btn-tab-artikel" onclick="switchCommentTab('artikel')"
            class="px-5 py-3 text-sm font-medium border-b-2 -mb-px transition-colors <?= $activeTab === 'artikel' ? 'border-amber-500 text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-300' ?>">
        Komentar Artikel
        <span class="ml-1.5 text-xs px-2 py-0.5 rounded-full bg-white/5"><?= count($articleComments) ?></span>
    </button>
</div>

<!-- Panel: Komentar Utama -->
<div id="panel-comment-main" class="<?= $activeTab === 'main' ? '' : 'hidden' ?>">
    <?php if (empty($comments)): ?>
    <div class="text-center py-20 text-gray-700 bg-dark-800 border border-white/5 rounded-2xl">
        <i data-lucide="message-square" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="text-sm">Belum ada komentar di halaman utama.</p>
    </div>
    <?php else: ?>
    <div class="bg-dark-800 border border-white/5 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-left text-xs text-gray-600 uppercase tracking-wider">
                        <th class="px-6 py-4">Pengirim</th>
                        <th class="px-6 py-4">Pesan</th>
                        <th class="px-6 py-4 hidden md:table-cell">Tanggal</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $c): ?>
                    <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors" id="row-<?= (int)$c['id'] ?>">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-black text-xs font-bold flex-shrink-0"
                                     style="background:#f59e0b">
                                    <?= Security::e(strtoupper(mb_substr($c['name'], 0, 1))) ?>
                                </div>
                                <span class="font-medium text-white"><?= Security::e($c['name']) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 max-w-xs">
                            <p class="text-gray-400 text-sm line-clamp-2"><?= Security::e($c['message']) ?></p>
                        </td>
                        <td class="px-6 py-4 hidden md:table-cell text-gray-600 text-xs">
                            <?= htmlspecialchars(date('d M Y, H:i', strtotime($c['created_at']))) ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="<?= $c['is_approved'] ? 'text-amber-400 bg-amber-500/10 border-amber-500/30' : 'text-gray-500 bg-dark-600 border-white/5' ?> px-2.5 py-1 rounded-lg text-xs border">
                                <?= $c['is_approved'] ? 'Tampil' : 'Hidden' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="toggleComment(<?= (int)$c['id'] ?>)"
                                        class="p-2 rounded-lg text-gray-500 hover:text-amber-400 hover:bg-amber-500/10 transition-all"
                                        title="Toggle visibilitas">
                                    <i data-lucide="<?= $c['is_approved'] ? 'eye-off' : 'eye' ?>" class="w-4 h-4"></i>
                                </button>
                                <button onclick="deleteComment(<?= (int)$c['id'] ?>)"
                                        class="p-2 rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-all">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Panel: Komentar Artikel -->
<div id="panel-comment-artikel" class="<?= $activeTab === 'artikel' ? '' : 'hidden' ?>">
    <?php if (empty($articleComments)): ?>
    <div class="text-center py-20 text-gray-700 bg-dark-800 border border-white/5 rounded-2xl">
        <i data-lucide="file-text" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
        <p class="text-sm">Belum ada komentar pada artikel.</p>
    </div>
    <?php else: ?>
    <div class="bg-dark-800 border border-white/5 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-left text-xs text-gray-600 uppercase tracking-wider">
                        <th class="px-6 py-4">Pengirim</th>
                        <th class="px-4 py-4">Artikel</th>
                        <th class="px-4 py-4">Pesan</th>
                        <th class="px-4 py-4 hidden md:table-cell">Tanggal</th>
                        <th class="px-4 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articleComments as $ac): ?>
                    <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors" id="ac-row-<?= (int)$ac['id'] ?>">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-black text-xs font-bold flex-shrink-0"
                                     style="background:#f59e0b">
                                    <?= Security::e(strtoupper(mb_substr($ac['name'], 0, 1))) ?>
                                </div>
                                <span class="font-medium text-white"><?= Security::e($ac['name']) ?></span>
                            </div>
                        </td>
                        <td class="px-4 py-4 max-w-[180px]">
                            <a href="<?= $base ?>/artikel/<?= rawurlencode($ac['article_slug'] ?? '') ?>" target="_blank" rel="noopener"
                               class="text-xs text-amber-400 hover:underline truncate block">
                                <?= Security::e($ac['article_title'] ?? 'Artikel') ?>
                            </a>
                        </td>
                        <td class="px-4 py-4 max-w-xs">
                            <p class="text-gray-400 text-sm line-clamp-2"><?= Security::e($ac['message']) ?></p>
                        </td>
                        <td class="px-4 py-4 hidden md:table-cell text-gray-600 text-xs">
                            <?= htmlspecialchars(date('d M Y, H:i', strtotime($ac['created_at']))) ?>
                        </td>
                        <td class="px-4 py-4">
                            <span class="<?= $ac['is_approved'] ? 'text-amber-400 bg-amber-500/10 border-amber-500/30' : 'text-gray-500 bg-dark-600 border-white/5' ?> px-2.5 py-1 rounded-lg text-xs border">
                                <?= $ac['is_approved'] ? 'Tampil' : 'Hidden' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="toggleArticleComment(<?= (int)$ac['id'] ?>)"
                                        class="p-2 rounded-lg text-gray-500 hover:text-amber-400 hover:bg-amber-500/10 transition-all"
                                        title="Toggle visibilitas">
                                    <i data-lucide="<?= $ac['is_approved'] ? 'eye-off' : 'eye' ?>" class="w-4 h-4"></i>
                                </button>
                                <button onclick="deleteArticleComment(<?= (int)$ac['id'] ?>)"
                                        class="p-2 rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-all">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
    const csrf      = '<?= htmlspecialchars($csrfToken) ?>';
    const adminBase = '<?= $adminBase ?>';

    function switchCommentTab(tab) {
        const panelMain    = document.getElementById('panel-comment-main');
        const panelArtikel = document.getElementById('panel-comment-artikel');
        const btnMain      = document.getElementById('btn-tab-main');
        const btnArtikel   = document.getElementById('btn-tab-artikel');

        if (tab === 'artikel') {
            panelArtikel.classList.remove('hidden');
            panelMain.classList.add('hidden');
            btnArtikel.classList.add('border-amber-500', 'text-amber-400');
            btnArtikel.classList.remove('border-transparent', 'text-gray-500');
            btnMain.classList.remove('border-amber-500', 'text-amber-400');
            btnMain.classList.add('border-transparent', 'text-gray-500');
        } else {
            panelMain.classList.remove('hidden');
            panelArtikel.classList.add('hidden');
            btnMain.classList.add('border-amber-500', 'text-amber-400');
            btnMain.classList.remove('border-transparent', 'text-gray-500');
            btnArtikel.classList.remove('border-amber-500', 'text-amber-400');
            btnArtikel.classList.add('border-transparent', 'text-gray-500');
        }
    }

    function deleteComment(id) {
        if (!confirm('Hapus komentar ini?')) return;
        fetch(adminBase + '/comments/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_csrf=' + encodeURIComponent(csrf) + '&id=' + id
        }).then(r => r.json()).then(() => {
            document.getElementById('row-' + id)?.remove();
        });
    }

    function toggleComment(id) {
        fetch(adminBase + '/comments/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_csrf=' + encodeURIComponent(csrf) + '&id=' + id
        }).then(r => r.json()).then(() => location.reload());
    }

    function deleteArticleComment(id) {
        if (!confirm('Hapus komentar artikel ini?')) return;
        fetch(adminBase + '/articles/comments/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_csrf=' + encodeURIComponent(csrf) + '&id=' + id
        }).then(r => r.json()).then(() => {
            document.getElementById('ac-row-' + id)?.remove();
        });
    }

    function toggleArticleComment(id) {
        fetch(adminBase + '/articles/comments/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_csrf=' + encodeURIComponent(csrf) + '&id=' + id
        }).then(r => r.json()).then(() => {
            window.location.href = adminBase + '/comments?tab=artikel';
        });
    }
</script>
