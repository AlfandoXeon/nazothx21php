<?php
use App\Core\Security;
$base = rtrim($_ENV['APP_URL'] ?? '', '/');
$totalComments = count($comments);
?>

<!-- Header -->
<section class="flex flex-col items-center text-center pt-12 pb-8 px-4">
    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-xs text-amber-400 font-semibold mb-3"
         data-aos="fade-down" data-aos-duration="500">
        <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
        <span>Komentar</span>
    </div>

    <h1 class="font-condensed font-extrabold text-4xl tracking-widest text-white mb-1"
        data-aos="fade-down" data-aos-duration="600">
        TINGGALKAN PESAN KALIAN
    </h1>
    <div class="w-12 h-0.5 mx-auto mt-1 mb-3" style="background:#f59e0b"
         data-aos="fade-up" data-aos-delay="80"></div>
    <p class="text-gray-400 text-xs tracking-wider max-w-sm"
       data-aos="fade-up" data-aos-delay="120">
        Tinggalkan pesan, saran, atau sapaan untuk <?= Security::e($profile['name'] ?? 'NAZO') ?>!
    </p>
</section>

<!-- Content Section -->
<section class="max-w-xl mx-auto px-4 pb-16">
    <!-- Alert via JS Flash -->
    <?php if (!empty($flash)): ?>
    <?php
        [$flashType, $flashMsg] = explode(':', $flash, 2);
    ?>
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            alert(<?= json_encode($flashMsg) ?>);
        });
    </script>
    <?php endif; ?>

    <!-- Form Kirim Komentar -->
    <div class="p-5 sm:p-6 rounded-2xl bg-dark-800 border border-white/5 mb-8 shadow-xl" data-aos="fade-up" data-aos-delay="50">
        <div class="flex items-center gap-2 mb-4">
            <i data-lucide="pen-tool" class="w-4 h-4 text-amber-500"></i>
            <h2 class="font-condensed font-bold text-lg tracking-wide text-white">TULIS PESAN BARU</h2>
        </div>

        <form id="comment-form-full" method="POST" action="<?= $base ?>/komentar" class="space-y-3">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($base . '/komentar') ?>">
            
            <div>
                <input type="text" id="full_name" name="name" placeholder="Nama kamu / Username" required maxlength="100"
                       class="w-full bg-dark-700 border border-white/5 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-amber-500/50 transition-colors">
            </div>

            <div>
                <textarea id="full_message" name="message" placeholder="Tulis komentar atau pesan kamu di sini (maksimal 1000 karakter)..." required maxlength="1000" rows="3"
                          class="w-full bg-dark-700 border border-white/5 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-amber-500/50 transition-colors resize-none"></textarea>
                <div class="flex justify-end mt-1">
                    <span id="full_counter" class="text-[11px] text-gray-500">0 / 1000</span>
                </div>
            </div>

            <button type="submit" id="full_submit_btn"
                    class="w-full py-2.5 rounded-xl text-sm font-semibold text-black transition-all duration-200 hover:opacity-90 active:scale-[.98] flex items-center justify-center gap-2"
                    style="background:#f59e0b">
                <i data-lucide="send" class="w-4 h-4"></i>
                <span>Kirim Sekarang</span>
            </button>
        </form>
    </div>

    <!-- Daftar Semua Komentar -->
    <div>
        <div class="flex items-center justify-between mb-4 px-1" data-aos="fade-up">
            <div class="flex items-center gap-2 text-sm text-gray-400">
                <i data-lucide="messages-square" class="w-4 h-4 text-amber-500"></i>
                <span class="font-medium">Total <?= (int)$totalComments ?> Komentar</span>
            </div>
            <a href="<?= $base ?>/" class="text-xs text-gray-400 hover:text-amber-400 inline-flex items-center gap-1 transition-colors">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Beranda</span>
            </a>
        </div>

        <div class="space-y-3">
            <?php if (empty($comments)): ?>
            <div class="text-center py-16 bg-dark-800/40 border border-white/5 rounded-2xl" data-aos="fade-up">
                <i data-lucide="message-square-off" class="w-10 h-10 mx-auto mb-3 text-gray-600"></i>
                <p class="text-sm text-gray-500">Belum ada komentar sama sekali.</p>
                <p class="text-xs text-gray-600 mt-1">Jadilah orang pertama yang meninggalkan jejak di sini!</p>
            </div>
            <?php else: ?>
                <?php foreach ($comments as $i => $comment): ?>
                <div class="p-4 rounded-xl bg-dark-700 border border-white/5 hover:border-amber-500/30 transition-all duration-200"
                     data-aos="fade-up" data-aos-delay="<?= min($i * 35, 200) ?>">
                    <div class="flex items-center justify-between gap-3 mb-2.5">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-black text-xs font-bold flex-shrink-0 shadow-md"
                                 style="background:#f59e0b">
                                <?= strtoupper(mb_substr($comment['name'], 0, 1)) ?>
                            </div>
                            <div class="truncate">
                                <div class="text-sm font-semibold text-white truncate"><?= Security::e($comment['name']) ?></div>
                                <div class="text-[11px] text-gray-500"><?= htmlspecialchars(date('d M Y, H:i', strtotime($comment['created_at']))) ?> WIB</div>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-300 leading-relaxed pl-11 break-words">
                        <?= Security::e($comment['message']) ?>
                    </p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
(function() {
    const form = document.getElementById('comment-form-full');
    const msgInput = document.getElementById('full_message');
    const nameInput = document.getElementById('full_name');
    const counter = document.getElementById('full_counter');

    if (msgInput && counter) {
        msgInput.addEventListener('input', function() {
            const len = this.value.length;
            counter.textContent = `${len} / 1000`;
            if (len > 1000) {
                counter.classList.add('text-red-500');
                counter.classList.remove('text-gray-500');
            } else {
                counter.classList.remove('text-red-500');
                counter.classList.add('text-gray-500');
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            const name = (nameInput ? nameInput.value : '').trim();
            const msg = (msgInput ? msgInput.value : '').trim();

            if (!name) {
                e.preventDefault();
                alert('Nama tidak boleh kosong.');
                nameInput && nameInput.focus();
                return;
            }

            if (!msg) {
                e.preventDefault();
                alert('Komentar tidak boleh kosong.');
                msgInput && msgInput.focus();
                return;
            }

            if (msg.length > 1000) {
                e.preventDefault();
                alert('Komentar tidak boleh lebih dari 1000 karakter dalam 1 kali kirim. Saat ini: ' + msg.length + ' karakter.');
                msgInput && msgInput.focus();
                return;
            }
        });
    }
})();
</script>
