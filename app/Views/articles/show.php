<?php
use App\Core\Markdown;
use App\Core\Security;

$base          = rtrim($_ENV['APP_URL'] ?? '', '/');
$thumbUrl      = Markdown::resolveMediaUrl($article['thumbnail_url'] ?? '', $base);
$avatarUrl     = Markdown::resolveMediaUrl($profile['avatar_url'] ?? '/Foto/fotoProfileNazo.png', $base);
$authorName    = $profile['name'] ?? 'NAZO ナゾ';
$dateStr       = !empty($article['published_at']) ? $article['published_at'] : $article['created_at'];
$articleUrl    = $base . '/artikel/' . rawurlencode($article['slug']);
$totalComments = count($comments);
?>

<article class="max-w-2xl mx-auto px-4 pt-8 pb-12">
    <!-- Top Breadcrumb -->
    <div class="flex items-center justify-between gap-3 mb-6" data-aos="fade-down">
        <a href="<?= $base ?>/artikel"
           class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-amber-400 transition-colors">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke Daftar Artikel</span>
        </a>
        <?php if (!empty($article['category'])): ?>
        <a href="<?= $base ?>/artikel?kategori=<?= urlencode($article['category']) ?>"
           class="px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20 text-[11px] font-semibold uppercase tracking-wider text-amber-400 hover:bg-amber-500/20 transition-colors">
            <?= Security::e($article['category']) ?>
        </a>
        <?php endif; ?>
    </div>

    <!-- Article Header -->
    <header class="mb-6" data-aos="fade-up">
        <h1 class="font-condensed font-extrabold text-3xl sm:text-4xl text-white tracking-wide leading-tight mb-4">
            <?= Security::e($article['title']) ?>
        </h1>

        <div class="flex flex-wrap items-center justify-between gap-4 pb-5 border-b border-white/5">
            <!-- Author -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full overflow-hidden bg-dark-700 ring-1 ring-amber-500/30 flex-shrink-0">
                    <img src="<?= htmlspecialchars($avatarUrl) ?>"
                         alt="<?= Security::e($authorName) ?>"
                         class="w-full h-full object-cover">
                </div>
                <div>
                    <div class="text-sm font-semibold text-white leading-tight"><?= Security::e($authorName) ?></div>
                    <div class="text-[11px] text-gray-500 mt-0.5">
                        <?= htmlspecialchars(date('d M Y, H:i', strtotime($dateStr))) ?> WIB
                    </div>
                </div>
            </div>

            <!-- Stats pills -->
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 border border-white/5">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span><?= (int)$readingTime ?> mnt</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/5 border border-white/5">
                    <i data-lucide="eye" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span><?= number_format((int)($article['views_count'] ?? 0)) ?> dibaca</span>
                </span>
            </div>
        </div>
    </header>

    <!-- Cover Thumbnail -->
    <?php if ($thumbUrl !== ''): ?>
    <div class="mb-8 rounded-2xl overflow-hidden bg-dark-800 border border-white/5 shadow-xl" data-aos="fade-up" data-aos-delay="50">
        <img src="<?= htmlspecialchars($thumbUrl) ?>"
             alt="<?= Security::e($article['title']) ?>"
             class="w-full max-h-[420px] object-cover">
    </div>
    <?php endif; ?>

    <!-- Article Body -->
    <div class="bg-dark-800/60 border border-white/5 rounded-2xl p-5 sm:p-8 mb-6 break-words" data-aos="fade-up" data-aos-delay="80">
        <?= $renderedBody ?>
    </div>

    <!-- Share Bar -->
    <div class="flex flex-wrap items-center justify-between gap-3 p-4 rounded-2xl bg-dark-800 border border-white/5 mb-10" data-aos="fade-up">
        <div class="text-xs text-gray-400 flex items-center gap-2">
            <i data-lucide="share-2" class="w-4 h-4 text-amber-500"></i>
            <span>Bagikan artikel ini:</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="btn-copy-article" onclick="copyArticleLink()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-medium text-gray-300 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-amber-500/40 hover:text-white transition-all active:scale-95">
                <i data-lucide="link" class="w-3.5 h-3.5 text-amber-500" id="icon-copy-article"></i>
                <span id="text-copy-article">Salin Link</span>
            </button>
            <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['title'] . ' — ' . $articleUrl) ?>"
               target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-medium text-gray-300 bg-white/5 hover:bg-emerald-500/10 border border-white/10 hover:border-emerald-500/40 hover:text-emerald-400 transition-all active:scale-95">
                <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-400"></i>
                <span>Bagikan WA</span>
            </a>
        </div>
    </div>

    <!-- Other Articles -->
    <?php if (!empty($otherArticles)): ?>
    <section class="mb-12" data-aos="fade-up">
        <div class="mb-4">
            <h2 class="font-condensed font-bold text-xl tracking-widest text-white uppercase">Artikel Lainnya</h2>
            <div class="w-10 h-0.5 mt-1" style="background:#f59e0b"></div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <?php foreach ($otherArticles as $other):
                $oDate = !empty($other['published_at']) ? $other['published_at'] : $other['created_at'];
            ?>
            <a href="<?= $base ?>/artikel/<?= rawurlencode($other['slug']) ?>"
               class="p-4 rounded-xl bg-dark-800 border border-white/5 hover:border-amber-500/40 transition-all group flex flex-col justify-between">
                <div>
                    <span class="text-[10px] uppercase tracking-wider text-amber-400/80 font-semibold">
                        <?= Security::e($other['category'] ?? 'Umum') ?>
                    </span>
                    <div class="font-condensed font-bold text-base text-white group-hover:text-amber-400 transition-colors mt-1 line-clamp-2">
                        <?= Security::e($other['title']) ?>
                    </div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-gray-500 mt-3 pt-2 border-t border-white/5">
                    <span><?= htmlspecialchars(date('d M Y', strtotime($oDate))) ?></span>
                    <span class="inline-flex items-center gap-1">
                        <i data-lucide="eye" class="w-3 h-3 text-amber-500/70"></i>
                        <?= number_format((int)($other['views_count'] ?? 0)) ?>
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- ── KOMENTAR ARTIKEL ──────────────────────────────────────── -->
    <section id="komentar" class="pt-4">
        <div class="flex items-center justify-between mb-6" data-aos="fade-up">
            <div>
                <h2 class="font-condensed font-bold text-2xl tracking-widest text-white uppercase">Komentar Artikel</h2>
                <div class="w-12 h-0.5 mt-1" style="background:#f59e0b"></div>
            </div>
            <span class="inline-flex items-center gap-1.5 text-xs text-amber-400 font-semibold px-3 py-1 rounded-lg bg-amber-500/10 border border-amber-500/20">
                <i data-lucide="messages-square" class="w-3.5 h-3.5"></i>
                <span><?= (int)$totalComments ?> Komentar</span>
            </span>
        </div>

        <!-- Flash Alert (konsisten dengan halaman komentar utama) -->
        <?php if (!empty($flash)): ?>
        <?php [$flashType, $flashMsg] = explode(':', $flash, 2); ?>
        <div class="mb-5 px-4 py-3 rounded-xl text-sm border <?= $flashType === 'success' ? 'bg-green-500/10 border-green-500/20 text-green-400' : 'bg-red-500/10 border-red-500/20 text-red-400' ?>">
            <?= Security::e($flashMsg) ?>
        </div>
        <?php endif; ?>

        <!-- Form Kirim Komentar -->
        <div class="p-5 sm:p-6 rounded-2xl bg-dark-800 border border-white/5 mb-8 shadow-xl" data-aos="fade-up" data-aos-delay="50">
            <div class="flex items-center gap-2 mb-4">
                <i data-lucide="pen-tool" class="w-4 h-4 text-amber-500"></i>
                <h3 class="font-condensed font-bold text-lg tracking-wide text-white">TULIS KOMENTAR</h3>
            </div>

            <form id="article-comment-form" method="POST" action="<?= $base ?>/artikel/<?= rawurlencode($article['slug']) ?>/komentar" class="space-y-3">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">

                <div>
                    <input type="text" id="ac_name" name="name" placeholder="Nama kamu / Username" required maxlength="100"
                           class="w-full bg-dark-700 border border-white/5 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-amber-500/50 transition-colors">
                </div>

                <div>
                    <textarea id="ac_message" name="message" placeholder="Tulis tanggapan atau komentar tentang artikel ini (maksimal 1000 karakter)..." required maxlength="1000" rows="3"
                              class="w-full bg-dark-700 border border-white/5 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-amber-500/50 transition-colors resize-none"></textarea>
                    <div class="flex justify-end mt-1">
                        <span id="ac_counter" class="text-[11px] text-gray-500">0 / 1000</span>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-2.5 rounded-xl text-sm font-semibold text-black transition-all duration-200 hover:opacity-90 active:scale-[.98] flex items-center justify-center gap-2"
                        style="background:#f59e0b">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Kirim Komentar</span>
                </button>
            </form>
        </div>

        <!-- Daftar Komentar -->
        <div class="space-y-3">
            <?php if (empty($comments)): ?>
            <div class="text-center py-12 bg-dark-800/40 border border-white/5 rounded-2xl" data-aos="fade-up">
                <i data-lucide="message-square-off" class="w-9 h-9 mx-auto mb-3 text-gray-600"></i>
                <p class="text-sm text-gray-500">Belum ada komentar untuk artikel ini.</p>
                <p class="text-xs text-gray-600 mt-1">Jadilah yang pertama memberikan tanggapan!</p>
            </div>
            <?php else: ?>
                <?php foreach ($comments as $i => $comment): ?>
                <div class="p-4 rounded-xl bg-dark-700 border border-white/5 hover:border-amber-500/30 transition-all duration-200"
                     data-aos="fade-up" data-aos-delay="<?= min($i * 35, 200) ?>">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-black text-xs font-bold flex-shrink-0 shadow-md"
                             style="background:#f59e0b">
                            <?= Security::e(strtoupper(mb_substr($comment['name'], 0, 1))) ?>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-white truncate"><?= Security::e($comment['name']) ?></div>
                            <div class="text-[11px] text-gray-500"><?= htmlspecialchars(date('d M Y, H:i', strtotime($comment['created_at']))) ?> WIB</div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-300 leading-relaxed pl-11 break-words">
                        <?= nl2br(Security::e($comment['message'])) ?>
                    </p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</article>

<script>
function copyArticleLink() {
    const url     = window.location.origin + window.location.pathname;
    const btnText = document.getElementById('text-copy-article');
    const btnIcon = document.getElementById('icon-copy-article');

    function onSuccess() {
        if (btnText) btnText.textContent = 'Tersalin!';
        if (btnIcon) {
            btnIcon.setAttribute('data-lucide', 'check');
            lucide.createIcons();
        }
        setTimeout(() => {
            if (btnText) btnText.textContent = 'Salin Link';
            if (btnIcon) {
                btnIcon.setAttribute('data-lucide', 'link');
                lucide.createIcons();
            }
        }, 2000);
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(onSuccess).catch(() => fallbackCopyArticle(url, onSuccess));
    } else {
        fallbackCopyArticle(url, onSuccess);
    }
}

function fallbackCopyArticle(text, cb) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try {
        document.execCommand('copy');
        if (cb) cb();
    } catch (e) {}
    document.body.removeChild(ta);
}

// Validasi Front-End Komentar Artikel
(function() {
    const form      = document.getElementById('article-comment-form');
    const nameInput = document.getElementById('ac_name');
    const msgInput  = document.getElementById('ac_message');
    const counter   = document.getElementById('ac_counter');

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
            const msg  = (msgInput ? msgInput.value : '').trim();

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
                alert('Komentar tidak boleh lebih dari 1000 karakter. Saat ini: ' + msg.length + ' karakter.');
                msgInput && msgInput.focus();
                return;
            }
        });
    }
})();
</script>
