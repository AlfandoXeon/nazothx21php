<?php
$base      = rtrim($_ENV['APP_URL'] ?? '', '/');
$pageTitle = htmlspecialchars($seoTitle ?? 'NAZO ナゾ — Links');
$pageDesc  = htmlspecialchars($seoDesc ?? 'NAZO ナゾ — MLBB Content Creator. Temukan semua media sosial dan konten Nazo di sini.');
$pageOgImg = !empty($seoOgImage) ? htmlspecialchars($seoOgImage) : $base . '/Foto/fotoProfileNazo.png';
$pageUrl   = htmlspecialchars($base . ($_SERVER['REQUEST_URI'] ?? '/'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <meta name="description" content="<?= $pageDesc ?>">

    <!-- Open Graph / SEO -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= $pageTitle ?>">
    <meta property="og:description" content="<?= $pageDesc ?>">
    <meta property="og:image" content="<?= $pageOgImg ?>">
    <meta property="og:url" content="<?= $pageUrl ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $pageTitle ?>">
    <meta name="twitter:description" content="<?= $pageDesc ?>">
    <meta name="twitter:image" content="<?= $pageOgImg ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $base ?>/Foto/nazo_icon.png">
    <link rel="apple-touch-icon" href="<?= $base ?>/Foto/nazo_icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        amber: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        },
                        dark: {
                            900: '#0a0a0a',
                            800: '#111111',
                            700: '#1a1a1a',
                            600: '#222222',
                            500: '#2a2a2a',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        condensed: ['Barlow Condensed', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- AOS.js -->
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <!-- Simple Icons (for brand icons) -->
    <script src="https://cdn.simpleicons.org/simpleicons.min.js" onerror=""></script>

    <style>
        :root {
            --amber: #f59e0b;
            --amber-dim: rgba(245,158,11,0.15);
            --amber-glow: rgba(245,158,11,0.3);
        }

        * { box-sizing: border-box; }

        body {
            background-color: #0a0a0a;
            color: #e5e5e5;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        /* Particle background */
        #particles {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: var(--amber);
            border-radius: 50%;
            opacity: 0;
            animation: float-particle linear infinite;
        }

        @keyframes float-particle {
            0%   { transform: translateY(100vh) translateX(0); opacity: 0; }
            10%  { opacity: 0.6; }
            90%  { opacity: 0.3; }
            100% { transform: translateY(-10px) translateX(var(--drift)); opacity: 0; }
        }

        /* Glow avatar */
        /* ── Avatar Aura ────────────────────────────────────────── */
        .avatar-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Spinning gradient ring */
        .avatar-wrapper::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            background: conic-gradient(
                from 0deg,
                transparent 0%,
                #f59e0b 25%,
                #fbbf24 40%,
                transparent 55%,
                transparent 75%,
                #d97706 85%,
                transparent 100%
            );
            animation: aura-spin 3s linear infinite;
            z-index: 0;
        }

        /* Pulsing outer glow */
        .avatar-wrapper::after {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            background: transparent;
            box-shadow:
                0 0 20px 6px rgba(245,158,11,0.35),
                0 0 50px 10px rgba(245,158,11,0.15);
            animation: aura-pulse 2.5s ease-in-out infinite alternate;
            z-index: 0;
        }

        @keyframes aura-spin {
            to { transform: rotate(360deg); }
        }

        @keyframes aura-pulse {
            from {
                box-shadow:
                    0 0 15px 4px rgba(245,158,11,0.25),
                    0 0 40px 8px rgba(245,158,11,0.10);
            }
            to {
                box-shadow:
                    0 0 30px 10px rgba(245,158,11,0.45),
                    0 0 70px 18px rgba(245,158,11,0.20);
            }
        }

        /* Avatar image stays above aura layers */
        .avatar-img-wrapper {
            position: relative;
            z-index: 1;
            border-radius: 50%;
            overflow: hidden;
        }

        /* Card hover */
        .link-card {
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        .link-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 32px var(--amber-dim), 0 0 0 1px var(--amber);
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #111; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 2px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--amber); }

        /* Tab active */
        .tab-btn.active {
            color: var(--amber);
            border-bottom-color: var(--amber);
        }

        /* Smooth scroll */
        html { scroll-behavior: smooth; }

        /* Text gradient */
        .text-gradient {
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="relative">

    <!-- Particle Background -->
    <div id="particles"></div>

    <!-- Navbar -->
    <?php require APP_PATH . '/Views/partials/navbar.php'; ?>

    <!-- Main Content -->
    <main class="relative z-10 pt-16">
        <?php echo $content; ?>
    </main>

    <!-- Footer -->
    <?php require APP_PATH . '/Views/partials/footer.php'; ?>

    <!-- Ad Popup — hidden, revealed after 3s delay via JS -->
    <?php if (!empty($adEnabled) && $adEnabled): ?>
    <?php
        $adImg    = $settings['ad_image_url'] ?? '';
        $adTitle  = $settings['ad_title'] ?? '';
        $adDesc   = $settings['ad_description'] ?? '';
        $adUrl    = $settings['ad_target_url'] ?? '';
        $adBtn    = $settings['ad_button_text'] ?? 'Lihat Sekarang';
    ?>
    <div id="ad-popup"
         style="display:none; opacity:0; transition: opacity 0.4s ease;"
         class="fixed inset-0 z-50 items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="relative bg-dark-800 border border-white/10 rounded-2xl max-w-sm w-full p-6 shadow-2xl"
             style="transform: translateY(20px); transition: transform 0.4s ease;">
            <button onclick="closeAdPopup()"
                class="absolute top-3 right-3 text-gray-500 hover:text-white transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
            <?php if ($adImg): ?>
            <img src="<?= htmlspecialchars($adImg) ?>" alt="" class="w-full h-40 object-cover rounded-xl mb-4">
            <?php endif; ?>
            <?php if ($adTitle): ?>
            <h3 class="font-condensed font-bold text-xl text-white mb-1"><?= htmlspecialchars($adTitle) ?></h3>
            <?php endif; ?>
            <?php if ($adDesc): ?>
            <p class="text-gray-400 text-sm mb-4"><?= htmlspecialchars($adDesc) ?></p>
            <?php endif; ?>
            <?php if ($adUrl): ?>
            <a href="<?= htmlspecialchars($adUrl) ?>" target="_blank" rel="noopener"
               class="block w-full text-center bg-amber-500 hover:bg-amber-600 text-black font-semibold py-2.5 rounded-lg transition-colors text-sm">
                <?= htmlspecialchars($adBtn) ?>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Show popup after 3 seconds — smooth fade + slide up
        window.addEventListener('load', function () {
            setTimeout(function () {
                const popup = document.getElementById('ad-popup');
                const card  = popup ? popup.querySelector('div') : null;
                if (!popup) return;

                // Make it flex then fade in
                popup.style.display = 'flex';
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        popup.style.opacity = '1';
                        if (card) card.style.transform = 'translateY(0)';
                        lucide.createIcons(); // re-init X icon inside popup
                    });
                });
            }, 3000);
        });

        function closeAdPopup() {
            const popup = document.getElementById('ad-popup');
            const card  = popup ? popup.querySelector('div') : null;
            if (!popup) return;
            popup.style.opacity = '0';
            if (card) card.style.transform = 'translateY(20px)';
            setTimeout(() => popup.remove(), 400);
        }
    </script>
    <?php endif; ?>

    <!-- AOS Init -->
    <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

    <!-- Lucide Init -->
    <script>
        lucide.createIcons();
        AOS.init({
            duration: 500,
            easing: 'ease-out',
            once: true,
            offset: 60,
            delay: 0,
        });
    </script>

    <!-- Particles Script -->
    <script>
        (function() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 30; i++) {
                const p = document.createElement('div');
                p.className = 'particle';
                p.style.cssText = `
                    left: ${Math.random() * 100}%;
                    animation-duration: ${8 + Math.random() * 12}s;
                    animation-delay: ${Math.random() * 10}s;
                    --drift: ${(Math.random() - 0.5) * 100}px;
                    opacity: 0;
                `;
                container.appendChild(p);
            }
        })();
    </script>

</body>
</html>
