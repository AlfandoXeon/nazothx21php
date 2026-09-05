<?php use App\Core\Security; ?>
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-sm">

        <!-- Branding -->
        <div class="text-center mb-8">
            <div class="font-condensed font-extrabold text-5xl tracking-widest text-white mb-1">
                NAZO<span style="color:#f59e0b">.</span>
            </div>
            <p class="text-xs text-gray-600 tracking-[0.2em] uppercase">Admin Panel</p>
        </div>

        <!-- Error Alert -->
        <?php if (!empty($error)): ?>
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
            <?= Security::e($error) ?>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" class="space-y-4">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="relative">
                <input type="password" name="password" id="password" placeholder="Password" required
                       autocomplete="current-password"
                       class="w-full bg-[#111] border border-white/10 rounded-xl px-4 py-3.5 text-sm text-white placeholder-gray-700 focus:outline-none focus:border-amber-500/50 transition-colors pr-12">
                <button type="button" onclick="togglePass()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-400 transition-colors">
                    <i data-lucide="eye" id="eye-icon" class="w-4 h-4"></i>
                </button>
            </div>

            <button type="submit"
                    class="w-full py-3.5 rounded-xl text-sm font-semibold text-black transition-all duration-200 hover:opacity-90 active:scale-[.98]"
                    style="background:#f59e0b">
                Masuk
            </button>
        </form>

        <p class="text-center text-xs text-gray-700 mt-6">
            &copy; <?= date('Y') ?> NAZO ナゾ
        </p>
    </div>
</div>

<script>
function togglePass() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eye-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        input.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
    }
    lucide.createIcons();
}
</script>
