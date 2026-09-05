<?php
use App\Core\Security;
$base = rtrim($_ENV['APP_URL'] ?? '', '/');

/**
 * Inline SVG brand icons — digunakan langsung tanpa fungsi dengan global
 * agar tidak bermasalah saat di-require dari dalam layout.
 */
$_BRAND_ICONS = [
    'tiktok'    => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>',
    'instagram' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12c0 3.259.014 3.668.072 4.948.058 1.268.261 2.14.558 2.906.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.906.558C8.333 23.988 8.74 24 12 24c3.259 0 3.668-.014 4.948-.072 1.268-.058 2.139-.261 2.907-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.906.06-1.28.072-1.687.072-4.947 0-3.259-.014-3.667-.072-4.947-.06-1.277-.261-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.906-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405a1.441 1.441 0 01-2.88 0 1.44 1.44 0 012.88 0z"/></svg>',
    'youtube'   => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current"><path d="M23.495 6.205a3.007 3.007 0 00-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 00.527 6.205a31.247 31.247 0 00-.522 5.805 31.247 31.247 0 00.522 5.783 3.007 3.007 0 002.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 002.088-2.088 31.247 31.247 0 00.5-5.783 31.247 31.247 0 00-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/></svg>',
    'whatsapp'  => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',
    'twitter'   => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
    'facebook'  => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
    'discord'   => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057c.002.022.015.04.033.05a19.911 19.911 0 0 0 5.993 3.03.077.077 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03z"/></svg>',
    'telegram'  => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 fill-current"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
];
?>

<?php
$avatarUrl = $profile['avatar_url'] ?? '';
if (empty($avatarUrl)) {
    $avatarUrl = $base . '/Foto/fotoProfileNazo.png';
} elseif (strpos($avatarUrl, 'http') !== 0) {
    $avatarUrl = $base . '/' . ltrim(preg_replace('#^/NazoLinktree/#', '', $avatarUrl), '/');
}
?>
<!-- ── HERO ───────────────────────────────────────────────────── -->
<section class="flex flex-col items-center text-center pt-12 pb-10 px-4">

    <!-- Avatar with animated aura -->
    <div class="relative mb-8" data-aos="fade-down" data-aos-duration="700">
        <!-- Aura wrapper — spins + pulses via CSS ::before and ::after -->
        <div class="avatar-wrapper w-28 h-28">
            <!-- Photo sits on top of aura layers -->
            <div class="avatar-img-wrapper w-28 h-28">
                <img src="<?= htmlspecialchars($avatarUrl) ?>"
                     alt="<?= Security::e($profile['name'] ?? 'NAZO') ?>"
                     class="w-full h-full object-cover">
            </div>
        </div>
        <!-- Zap badge -->
        <div class="absolute -bottom-1 -right-1 w-7 h-7 bg-amber-500 rounded-full flex items-center justify-center shadow-lg z-10">
            <i data-lucide="zap" class="w-3.5 h-3.5 text-black"></i>
        </div>
    </div>

    <!-- Name & Role -->
    <h1 class="font-condensed font-extrabold text-4xl tracking-widest text-white mb-1"
        data-aos="fade-up" data-aos-delay="50">
        <?= Security::e($profile['name'] ?? 'NAZO ナゾ') ?>
    </h1>
    <p class="text-xs font-medium tracking-[0.3em] uppercase mb-3"
       style="color:#f59e0b" data-aos="fade-up" data-aos-delay="100">
        <?= Security::e($profile['role'] ?? 'MLBB CONTENT CREATOR') ?>
    </p>
    <?php if (!empty($profile['bio'])): ?>
    <p class="text-gray-500 text-sm max-w-xs leading-relaxed mb-3"
       data-aos="fade-up" data-aos-delay="130">
        <?= Security::e($profile['bio']) ?>
    </p>
    <?php endif; ?>

    <!-- Visitor Counter Pill -->
    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-xs text-gray-400 mb-3"
         data-aos="fade-up" data-aos-delay="140">
        <i data-lucide="eye" class="w-3.5 h-3.5 text-amber-500"></i>
        <span id="visitor-counter" class="font-semibold text-white">0</span>
        <span class="text-gray-500 text-[11px]">pengunjung</span>
    </div>

    <!-- Social Header Icons -->
    <?php if (!empty($socials)): ?>
    <div class="flex items-center gap-3 mt-1" data-aos="fade-up" data-aos-delay="160">
        <?php foreach ($socials as $social):
            $slug    = strtolower(trim($social['icon_slug'] ?? ''));
            $svgIcon = $_BRAND_ICONS[$slug] ?? null;
        ?>
        <a href="<?= htmlspecialchars($social['url']) ?>" target="_blank" rel="noopener"
           title="<?= Security::e($social['name']) ?>"
           class="w-10 h-10 rounded-xl flex items-center justify-center text-gray-400 hover:text-amber-400 bg-white/5 hover:bg-amber-500/10 border border-white/5 hover:border-amber-500/30 transition-all duration-200">
            <?php if ($svgIcon): ?>
                <?= $svgIcon ?>
            <?php else: ?>
                <span class="text-[10px] font-bold tracking-tight leading-none text-center px-0.5">
                    <?= Security::e(strtoupper(substr($social['name'], 0, 2))) ?>
                </span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Share Actions (WA & Copy Link) -->
    <div class="flex items-center justify-center gap-2 mt-4" data-aos="fade-up" data-aos-delay="190">
        <button type="button" id="btn-copy-link" onclick="copyPageLink()"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-medium text-gray-300 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-amber-500/40 hover:text-white transition-all duration-200 active:scale-95">
            <i data-lucide="link" class="w-3.5 h-3.5 text-amber-500" id="icon-copy-link"></i>
            <span id="text-copy-link">Salin Link</span>
        </button>

        <a href="https://api.whatsapp.com/send?text=Yuk+kunjungi+profil+resmi+NAZO:+<?= urlencode(rtrim($_ENV['APP_URL'] ?? '', '/')) ?>"
           target="_blank" rel="noopener"
           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-medium text-gray-300 bg-white/5 hover:bg-emerald-500/10 border border-white/10 hover:border-emerald-500/40 hover:text-emerald-400 transition-all duration-200 active:scale-95">
            <span class="w-3.5 h-3.5 text-emerald-400 flex items-center justify-center">
                <?= $_BRAND_ICONS['whatsapp'] ?>
            </span>
            <span>Bagikan WA</span>
        </a>
    </div>
</section>

<!-- ── CARDS ──────────────────────────────────────────────────── -->
<section class="max-w-lg mx-auto px-4 pb-10 space-y-3">
    <?php foreach ($cards as $i => $card): ?>
    <a href="<?= $base ?>/go/<?= (int)$card['id'] ?>" target="_blank" rel="noopener"
       class="link-card flex items-center gap-4 p-4 rounded-xl bg-dark-700 border border-white/5 group hover:border-amber-500/40 transition-all duration-250"
       data-aos="fade-up" data-aos-delay="<?= min($i * 60, 300) ?>">

        <!-- Thumbnail -->
        <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0 bg-dark-600">
            <?php if (!empty($card['image_url'])): ?>
            <img src="<?= htmlspecialchars($card['image_url']) ?>"
                 alt="<?= Security::e($card['title']) ?>"
                 class="w-full h-full object-cover"
                 onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-gray-700\'><svg xmlns=\'http://www.w3.org/2000/svg\' width=\'20\' height=\'20\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg></div>'">
            <?php else: ?>
            <div class="w-full h-full flex items-center justify-center text-gray-700">
                <i data-lucide="link-2" class="w-5 h-5"></i>
            </div>
            <?php endif; ?>
        </div>

        <!-- Text -->
        <div class="flex-1 min-w-0">
            <div class="font-condensed font-bold text-base tracking-wide text-white group-hover:text-amber-400 transition-colors truncate">
                <?= Security::e($card['title']) ?>
            </div>
            <?php if (!empty($card['subtitle'])): ?>
            <div class="text-xs text-gray-500 truncate mt-0.5"><?= Security::e($card['subtitle']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Arrow -->
        <div class="flex-shrink-0 text-gray-700 group-hover:text-amber-500 transition-colors">
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
        </div>
    </a>
    <?php endforeach; ?>

    <?php if (empty($cards)): ?>
    <div class="text-center py-12 text-gray-700" data-aos="fade-up">
        <i data-lucide="link" class="w-8 h-8 mx-auto mb-3 opacity-50"></i>
        <p class="text-sm">Belum ada link yang ditambahkan.</p>
    </div>
    <?php endif; ?>
</section>

<!-- ── KOMENTAR ───────────────────────────────────────────────── -->
<section id="komentar" class="max-w-lg mx-auto px-4 pb-16">
    <div class="mb-6" data-aos="fade-up">
        <h2 class="font-condensed font-bold text-2xl tracking-widest text-white">KOMENTAR</h2>
        <div class="w-12 h-0.5 mt-1" style="background:#f59e0b"></div>
    </div>

    <!-- Flash Message -->
    <?php if (!empty($flash)): ?>
    <?php
        [$type, $msg] = explode(':', $flash, 2);
        $flashBg = $type === 'success' ? 'bg-green-500/10 border-green-500/30 text-green-400' : 'bg-red-500/10 border-red-500/30 text-red-400';
    ?>
    <div class="mb-4 px-4 py-3 rounded-lg border text-sm <?= $flashBg ?>">
        <?= Security::e($msg) ?>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="<?= $base ?>/komentar" class="mb-8 space-y-3" data-aos="fade-up" data-aos-delay="60">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
        <input type="text" name="name" placeholder="Nama kamu" required maxlength="100"
               class="w-full bg-dark-700 border border-white/5 rounded-lg px-4 py-3 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-amber-500/50 transition-colors">
        <textarea name="message" placeholder="Tulis komentar..." required maxlength="500" rows="3"
                  class="w-full bg-dark-700 border border-white/5 rounded-lg px-4 py-3 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-amber-500/50 transition-colors resize-none"></textarea>
        <button type="submit"
                class="w-full py-2.5 rounded-lg text-sm font-semibold text-black transition-all duration-200 hover:opacity-90 active:scale-[.98]"
                style="background:#f59e0b">
            Kirim Komentar
        </button>
    </form>

    <!-- Comment List -->
    <div class="space-y-3">
        <?php if (empty($comments)): ?>
        <p class="text-center text-gray-700 text-sm py-6">Belum ada komentar. Jadilah yang pertama!</p>
        <?php endif; ?>

        <?php foreach ($comments as $i => $comment): ?>
        <div class="p-4 rounded-xl bg-dark-700 border border-white/5"
             data-aos="fade-up" data-aos-delay="<?= min($i * 50, 200) ?>">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-black text-xs font-bold flex-shrink-0"
                     style="background:#f59e0b">
                    <?= strtoupper(mb_substr($comment['name'], 0, 1)) ?>
                </div>
                <div>
                    <div class="text-sm font-semibold text-white"><?= Security::e($comment['name']) ?></div>
                    <div class="text-xs text-gray-600"><?= htmlspecialchars(date('d M Y', strtotime($comment['created_at']))) ?></div>
                </div>
            </div>
            <p class="text-sm text-gray-400 leading-relaxed pl-10">
                <?= Security::e($comment['message']) ?>
            </p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<script>
function copyPageLink() {
    const url = window.location.origin + window.location.pathname;
    const btnText = document.getElementById('text-copy-link');
    const btnIcon = document.getElementById('icon-copy-link');

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
        navigator.clipboard.writeText(url).then(onSuccess).catch(() => fallbackCopy(url, onSuccess));
    } else {
        fallbackCopy(url, onSuccess);
    }
}

function fallbackCopy(text, cb) {
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

// Animated Visitor Count Up
(function() {
    const counter = document.getElementById('visitor-counter');
    if (!counter) return;
    const target = <?= (int)($visitorCount ?? 0) ?>;
    if (target <= 0) {
        counter.textContent = '0';
        return;
    }
    const duration = 1200;
    const startTime = performance.now();

    function easeOutExpo(x) {
        return x === 1 ? 1 : 1 - Math.pow(2, -10 * x);
    }

    function updateCount(now) {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current = Math.floor(easeOutExpo(progress) * target);
        counter.textContent = current.toLocaleString('id-ID');
        if (progress < 1) {
            requestAnimationFrame(updateCount);
        } else {
            counter.textContent = target.toLocaleString('id-ID');
        }
    }
    requestAnimationFrame(updateCount);
})();
</script>
