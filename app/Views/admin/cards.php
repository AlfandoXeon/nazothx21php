<?php use App\Core\Security; ?>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="font-condensed font-bold text-3xl text-white tracking-wide">Cards</h1>
        <p class="text-gray-600 text-sm mt-1">Kelola semua link card yang tampil di halaman utama.</p>
    </div>
    <button onclick="openModal('modal-add')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-black transition-all hover:opacity-90"
            style="background:#f59e0b">
        <i data-lucide="plus" class="w-4 h-4"></i> Tambah Card
    </button>
</div>

<!-- Flash -->
<?php if (!empty($flash)): ?>
<?php [$t,$m] = explode(':', $flash, 2); ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm border <?= $t==='success'?'bg-green-500/10 border-green-500/20 text-green-400':'bg-red-500/10 border-red-500/20 text-red-400' ?>">
    <?= Security::e($m) ?>
</div>
<?php endif; ?>

<!-- Cards Table -->
<div class="bg-dark-800 border border-white/5 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/5 text-left text-xs text-gray-600 uppercase tracking-wider">
                    <th class="px-6 py-4 w-8">No</th>
                    <th class="px-6 py-4">Card</th>
                    <th class="px-6 py-4 hidden sm:table-cell">URL</th>
                    <th class="px-6 py-4 text-center">Klik</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="cards-list">
                <?php foreach ($cards as $i => $card): ?>
                <tr class="border-b border-white/5 hover:bg-white/2 transition-colors" data-id="<?= $card['id'] ?>">
                    <td class="px-6 py-4 text-gray-600"><?= $i + 1 ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg overflow-hidden bg-dark-600 flex-shrink-0">
                                <?php if ($card['image_url']): ?>
                                <img src="<?= htmlspecialchars($card['image_url']) ?>" class="w-full h-full object-cover" alt="">
                                <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-gray-700">
                                    <i data-lucide="link-2" class="w-4 h-4"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="font-medium text-white"><?= Security::e($card['title']) ?></div>
                                <div class="text-xs text-gray-600 truncate max-w-[200px]"><?= Security::e($card['subtitle'] ?? '') ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 hidden sm:table-cell">
                        <span class="text-gray-600 text-xs truncate block max-w-[150px]"><?= Security::e($card['url']) ?></span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold">
                            <i data-lucide="mouse-pointer" class="w-3 h-3"></i>
                            <?= number_format($clickCounts[$card['id']] ?? 0) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="toggleCard(<?= $card['id'] ?>, this)"
                                class="<?= $card['is_active'] ? 'bg-green-500/10 text-green-400 border-green-500/20' : 'bg-gray-500/10 text-gray-500 border-gray-500/20' ?> px-2.5 py-1 rounded-lg text-xs border transition-all">
                            <?= $card['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </button>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick='openEdit(<?= json_encode($card) ?>)'
                                    class="p-2 rounded-lg text-gray-500 hover:text-amber-400 hover:bg-amber-500/10 transition-all">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </button>
                            <button onclick="deleteCard(<?= $card['id'] ?>)"
                                    class="p-2 rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-all">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($cards)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-700 text-sm">Belum ada card.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD MODAL -->
<div id="modal-add" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-dark-800 border border-white/10 rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-white/5 flex items-center justify-between">
            <h3 class="font-condensed font-bold text-xl text-white">Tambah Card Baru</h3>
            <button onclick="closeModal('modal-add')" class="text-gray-600 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="<?= $adminBase ?>/cards/store" class="p-6 space-y-4">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <?= cardFormFields() ?>
            <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold text-black" style="background:#f59e0b">Simpan Card</button>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-dark-800 border border-white/10 rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-white/5 flex items-center justify-between">
            <h3 class="font-condensed font-bold text-xl text-white">Edit Card</h3>
            <button onclick="closeModal('modal-edit')" class="text-gray-600 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="<?= $adminBase ?>/cards/update" class="p-6 space-y-4" id="edit-form">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="id" id="edit-id">
            <?= cardFormFields('edit-') ?>
            <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold text-black" style="background:#f59e0b">Perbarui Card</button>
        </form>
    </div>
</div>

<?php function cardFormFields(string $prefix = ''): string {
    $cls = 'w-full bg-dark-700 border border-white/5 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-700 focus:outline-none focus:border-amber-500/50 transition-colors';
    return "
    <div><label class='text-xs text-gray-500 mb-1 block'>Judul <span class='text-red-400'>*</span></label>
    <input type='text' name='title' id='{$prefix}title' required class='{$cls}' placeholder='Nama platform / card'></div>

    <div><label class='text-xs text-gray-500 mb-1 block'>Subtitle / Deskripsi</label>
    <input type='text' name='subtitle' id='{$prefix}subtitle' class='{$cls}' placeholder='Teks kecil di bawah judul'></div>

    <div><label class='text-xs text-gray-500 mb-1 block'>URL Tujuan <span class='text-red-400'>*</span></label>
    <input type='url' name='url' id='{$prefix}url' required class='{$cls}' placeholder='https://'></div>

    <div><label class='text-xs text-gray-500 mb-1 block'>URL Gambar Thumbnail</label>
    <input type='text' name='image_url' id='{$prefix}image_url' class='{$cls}' placeholder='https:// atau path lokal'></div>

    <div><label class='text-xs text-gray-500 mb-1 block'>Urutan (sort order)</label>
    <input type='number' name='sort_order' id='{$prefix}sort_order' value='0' class='{$cls}'></div>
    ";
}
?>

<script>
    const csrf = '<?= htmlspecialchars($csrfToken) ?>';
    const adminBase = '<?= $adminBase ?>';

    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        document.getElementById(id).classList.add('flex');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
        document.getElementById(id).classList.remove('flex');
    }

    function openEdit(card) {
        document.getElementById('edit-id').value = card.id;
        document.getElementById('edit-title').value = card.title;
        document.getElementById('edit-subtitle').value = card.subtitle || '';
        document.getElementById('edit-url').value = card.url;
        document.getElementById('edit-image_url').value = card.image_url || '';
        document.getElementById('edit-sort_order').value = card.sort_order || 0;
        openModal('modal-edit');
    }

    function toggleCard(id, btn) {
        fetch(adminBase + '/cards/toggle', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_csrf=' + encodeURIComponent(csrf) + '&id=' + id
        }).then(r => r.json()).then(() => location.reload());
    }

    function deleteCard(id) {
        if (!confirm('Hapus card ini?')) return;
        fetch(adminBase + '/cards/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_csrf=' + encodeURIComponent(csrf) + '&id=' + id
        }).then(r => r.json()).then(() => location.reload());
    }
</script>
