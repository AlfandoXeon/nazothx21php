<?php use App\Core\Security; ?>

<div class="mb-6">
    <h1 class="font-condensed font-bold text-3xl text-white tracking-wide">Profil</h1>
    <p class="text-gray-600 text-sm mt-1">Edit nama, role, bio, dan foto profil yang tampil di halaman utama.</p>
</div>

<!-- Flash -->
<?php if (!empty($flash)): ?>
<?php [$t,$m] = explode(':', $flash, 2); ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm border <?= $t==='success'?'bg-green-500/10 border-green-500/20 text-green-400':'bg-red-500/10 border-red-500/20 text-red-400' ?>">
    <?= Security::e($m) ?>
</div>
<?php endif; ?>

<div class="max-w-lg">
    <form method="POST" action="<?= $adminBase ?>/profile/update" enctype="multipart/form-data"
          class="bg-dark-800 border border-white/5 rounded-2xl p-6 space-y-5">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">

        <!-- Avatar Preview & Upload -->
        <div class="flex items-center gap-5">
            <div class="w-20 h-20 rounded-full overflow-hidden bg-dark-600 flex-shrink-0 ring-2 ring-amber-500/20">
                <img id="avatar-preview"
                     src="<?= htmlspecialchars($profile['avatar_url'] ?? '') ?>"
                     alt="Avatar" class="w-full h-full object-cover">
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Foto Profil</label>
                <label for="avatar-upload"
                       class="cursor-pointer flex items-center gap-2 px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-sm text-gray-300 border border-white/5 transition-all">
                    <i data-lucide="upload" class="w-4 h-4"></i> Ganti Foto
                </label>
                <input type="file" name="avatar" id="avatar-upload" accept="image/*" class="hidden"
                       onchange="previewAvatar(this)">
                <p class="text-xs text-gray-700 mt-1">JPG, PNG, WebP — maks. 5MB</p>
            </div>
        </div>

        <div>
            <label class="text-xs text-gray-500 mb-1 block">Nama</label>
            <input type="text" name="name" value="<?= Security::e($profile['name'] ?? '') ?>" required
                   class="w-full bg-dark-700 border border-white/5 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-amber-500/50 transition-colors">
        </div>

        <div>
            <label class="text-xs text-gray-500 mb-1 block">Role / Jabatan</label>
            <input type="text" name="role" value="<?= Security::e($profile['role'] ?? '') ?>"
                   class="w-full bg-dark-700 border border-white/5 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-amber-500/50 transition-colors"
                   placeholder="MLBB CONTENT CREATOR">
        </div>

        <div>
            <label class="text-xs text-gray-500 mb-1 block">Bio Singkat</label>
            <textarea name="bio" rows="3"
                      class="w-full bg-dark-700 border border-white/5 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-amber-500/50 transition-colors resize-none"
                      placeholder="Perkenalan singkat..."><?= Security::e($profile['bio'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold text-black hover:opacity-90 transition-all" style="background:#f59e0b">
            Simpan Perubahan
        </button>
    </form>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
