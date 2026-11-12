<?php
$base        = rtrim($_ENV['APP_URL'] ?? '', '/');
$adminRoute  = $_ENV['ADMIN_ROUTE'] ?? 'adminadalahraja';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$navLinks = [
    ['href' => $base . '/',         'label' => 'Links',    'path' => ''],
    ['href' => $base . '/gallery',  'label' => 'Gallery',  'path' => 'gallery'],
    ['href' => $base . '/komentar', 'label' => 'Komentar', 'path' => 'komentar'],
];
$currentUrlParam = trim($_GET['url'] ?? '', '/');
?>
<nav class="fixed top-0 left-0 right-0 z-30 bg-dark-900/80 backdrop-blur-md border-b border-white/5">
    <div class="max-w-2xl mx-auto px-4 h-16 flex items-center justify-between">
        <!-- Logo -->
        <a href="<?= $base ?>/" class="font-condensed font-bold text-xl tracking-widest text-white">
            NAZO<span style="color:#f59e0b">.</span>
        </a>

        <!-- Desktop Nav -->
        <div class="hidden sm:flex items-center gap-1">
            <?php foreach ($navLinks as $link): 
                $isActive = ($link['path'] === $currentUrlParam);
            ?>
            <a href="<?= htmlspecialchars($link['href']) ?>"
               class="px-3 py-1.5 text-sm rounded-md transition-all <?= $isActive ? 'text-amber-400 font-semibold bg-amber-500/10 border border-amber-500/20' : 'text-gray-400 hover:text-white hover:bg-white/5' ?>">
                <?= htmlspecialchars($link['label']) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Mobile Hamburger -->
        <button id="nav-toggle" class="sm:hidden text-gray-400 hover:text-white">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden sm:hidden border-t border-white/5 bg-dark-900">
        <?php foreach ($navLinks as $link): 
            $isActive = ($link['path'] === $currentUrlParam);
        ?>
        <a href="<?= htmlspecialchars($link['href']) ?>"
           class="block px-6 py-3 text-sm transition-all <?= $isActive ? 'text-amber-400 font-semibold bg-amber-500/10' : 'text-gray-400 hover:text-white hover:bg-white/5' ?>">
            <?= htmlspecialchars($link['label']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</nav>

<script>
    document.getElementById('nav-toggle')?.addEventListener('click', function() {
        document.getElementById('mobile-menu').classList.toggle('hidden');
    });
</script>
