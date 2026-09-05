<?php use App\Core\Security; ?>

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="font-condensed font-bold text-3xl text-white tracking-wide">Social Icons</h1>
        <p class="text-gray-600 text-sm mt-1">Kelola ikon sosial media di header profil.</p>
    </div>
    <button onclick="openModal('modal-add')"
            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-black hover:opacity-90"
            style="background:#f59e0b">
        <i data-lucide="plus" class="w-4 h-4"></i> Tambah
    </button>
</div>

<!-- Flash -->
<?php if (!empty($flash)): ?>
<?php [$t,$m] = explode(':', $flash, 2); ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm border <?= $t==='success'?'bg-green-500/10 border-green-500/20 text-green-400':'bg-red-500/10 border-red-500/20 text-red-400' ?>">
    <?= Security::e($m) ?>
</div>
<?php endif; ?>

<!-- Info box -->
<div class="mb-4 px-4 py-3 rounded-xl text-xs border border-amber-500/20 bg-amber-500/5 text-amber-400/70">
    Icon slug menggunakan nama dari <strong>Simple Icons</strong> (huruf kecil). Contoh: <code>tiktok</code>, <code>instagram</code>, <code>youtube</code>, <code>whatsapp</code>
</div>

<!-- Table -->
<div class="bg-dark-800 border border-white/5 rounded-2xl overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-white/5 text-left text-xs text-gray-600 uppercase tracking-wider">
                <th class="px-6 py-4">Platform</th>
                <th class="px-6 py-4 hidden sm:table-cell">URL</th>
                <th class="px-6 py-4">Icon Slug</th>
                <th class="px-6 py-4">Urutan</th>
                <th class="px-6 py-4 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($socials as $s): ?>
            <tr class="border-b border-white/5 hover:bg-white/2 transition-colors" id="sr-<?= $s['id'] ?>">
                <td class="px-6 py-4 font-medium text-white"><?= Security::e($s['name']) ?></td>
                <td class="px-6 py-4 hidden sm:table-cell text-gray-600 text-xs max-w-[150px] truncate"><?= Security::e($s['url']) ?></td>
                <td class="px-6 py-4"><code class="text-amber-400/80 text-xs"><?= Security::e($s['icon_slug'] ?? '') ?></code></td>
                <td class="px-6 py-4 text-gray-500"><?= $s['sort_order'] ?></td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <button onclick='openEdit(<?= json_encode($s) ?>)'
                                class="p-2 rounded-lg text-gray-500 hover:text-amber-400 hover:bg-amber-500/10 transition-all">
                            <i data-lucide="pencil" class="w-4 h-4"></i>
                        </button>
                        <button onclick="deleteSocial(<?= $s['id'] ?>)"
                                class="p-2 rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition-all">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($socials)): ?>
            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-700 text-sm">Belum ada social link.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ADD MODAL -->
<div id="modal-add" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-dark-800 border border-white/10 rounded-2xl w-full max-w-md">
        <div class="p-6 border-b border-white/5 flex items-center justify-between">
            <h3 class="font-condensed font-bold text-xl text-white">Tambah Social Link</h3>
            <button onclick="closeModal('modal-add')" class="text-gray-600 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="<?= $adminBase ?>/social/store" class="p-6 space-y-4">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <?= socialFields() ?>
            <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold text-black hover:opacity-90" style="background:#f59e0b">Simpan</button>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-dark-800 border border-white/10 rounded-2xl w-full max-w-md">
        <div class="p-6 border-b border-white/5 flex items-center justify-between">
            <h3 class="font-condensed font-bold text-xl text-white">Edit Social Link</h3>
            <button onclick="closeModal('modal-edit')" class="text-gray-600 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form method="POST" action="<?= $adminBase ?>/social/update" class="p-6 space-y-4">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="id" id="edit-id">
            <?= socialFields('edit-') ?>
            <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold text-black hover:opacity-90" style="background:#f59e0b">Perbarui</button>
        </form>
    </div>
</div>

<?php function socialFields(string $p = ''): string {
    $cls = "w-full bg-dark-700 border border-white/5 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-700 focus:outline-none focus:border-amber-500/50 transition-colors";
    return "
    <div><label class='text-xs text-gray-500 mb-1 block'>Nama Platform</label>
    <input type='text' name='name' id='{$p}name' required class='$cls' placeholder='TikTok, Instagram...'></div>
    <div><label class='text-xs text-gray-500 mb-1 block'>URL</label>
    <input type='url' name='url' id='{$p}url' required class='$cls' placeholder='https://'></div>
    <div><label class='text-xs text-gray-500 mb-1 block'>Icon Slug (Simple Icons)</label>
    <input type='text' name='icon_slug' id='{$p}icon_slug' class='$cls' placeholder='tiktok'></div>
    <div><label class='text-xs text-gray-500 mb-1 block'>Urutan</label>
    <input type='number' name='sort_order' id='{$p}sort_order' value='0' class='$cls'></div>
    ";
}
?>

<script>
    const csrf = '<?= htmlspecialchars($csrfToken) ?>';
    const adminBase = '<?= $adminBase ?>';
    function openModal(id)  { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); document.getElementById(id).classList.remove('flex'); }
    function openEdit(s) {
        document.getElementById('edit-id').value = s.id;
        document.getElementById('edit-name').value = s.name;
        document.getElementById('edit-url').value = s.url;
        document.getElementById('edit-icon_slug').value = s.icon_slug || '';
        document.getElementById('edit-sort_order').value = s.sort_order || 0;
        openModal('modal-edit');
    }
    function deleteSocial(id) {
        if (!confirm('Hapus social link ini?')) return;
        fetch(adminBase + '/social/delete', {
            method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:'_csrf='+encodeURIComponent(csrf)+'&id='+id
        }).then(r=>r.json()).then(()=>location.reload());
    }
</script>
