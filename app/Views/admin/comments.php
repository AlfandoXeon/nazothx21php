<?php use App\Core\Security; ?>

<div class="mb-6">
    <h1 class="font-condensed font-bold text-3xl text-white tracking-wide">Komentar Publik</h1>
    <p class="text-gray-600 text-sm mt-1">Moderasi komentar yang masuk dari pengunjung.</p>
</div>

<?php if (empty($comments)): ?>
<div class="text-center py-20 text-gray-700">
    <i data-lucide="message-square" class="w-12 h-12 mx-auto mb-3 opacity-30"></i>
    <p class="text-sm">Belum ada komentar.</p>
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
                <tr class="border-b border-white/5 hover:bg-white/2 transition-colors" id="row-<?= $c['id'] ?>">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-black text-xs font-bold flex-shrink-0"
                                 style="background:#f59e0b">
                                <?= strtoupper(mb_substr($c['name'], 0, 1)) ?>
                            </div>
                            <span class="font-medium text-white"><?= Security::e($c['name']) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 max-w-xs">
                        <p class="text-gray-400 text-sm line-clamp-2"><?= Security::e($c['message']) ?></p>
                    </td>
                    <td class="px-6 py-4 hidden md:table-cell text-gray-600 text-xs">
                        <?= date('d M Y, H:i', strtotime($c['created_at'])) ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="<?= $c['is_approved'] ? 'text-green-400 bg-green-500/10 border-green-500/20' : 'text-gray-500 bg-gray-500/10 border-gray-500/20' ?> px-2.5 py-1 rounded-lg text-xs border">
                            <?= $c['is_approved'] ? 'Tampil' : 'Hidden' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="toggleComment(<?= $c['id'] ?>, this)"
                                    class="p-2 rounded-lg text-gray-500 hover:text-amber-400 hover:bg-amber-500/10 transition-all"
                                    title="Toggle visibilitas">
                                <i data-lucide="<?= $c['is_approved'] ? 'eye-off' : 'eye' ?>" class="w-4 h-4"></i>
                            </button>
                            <button onclick="deleteComment(<?= $c['id'] ?>)"
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

<script>
    const csrf = '<?= htmlspecialchars($csrfToken) ?>';
    const adminBase = '<?= $adminBase ?>';

    function deleteComment(id) {
        if (!confirm('Hapus komentar ini?')) return;
        fetch(adminBase + '/comments/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_csrf=' + encodeURIComponent(csrf) + '&id=' + id
        }).then(r => r.json()).then(() => {
            document.getElementById('row-' + id)?.remove();
        });
    }

    function toggleComment(id, btn) {
        fetch(adminBase + '/comments/toggle', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_csrf=' + encodeURIComponent(csrf) + '&id=' + id
        }).then(r => r.json()).then(() => location.reload());
    }
</script>
