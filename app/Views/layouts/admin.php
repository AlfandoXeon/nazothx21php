<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel — NAZO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        amber: { 400:'#fbbf24', 500:'#f59e0b', 600:'#d97706' },
                        dark:  { 900:'#0a0a0a', 800:'#111111', 700:'#1a1a1a', 600:'#222222', 500:'#2a2a2a' }
                    },
                    fontFamily: {
                        sans: ['Inter','sans-serif'],
                        condensed: ['Barlow Condensed','sans-serif'],
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        ::-webkit-scrollbar { width:4px; }
        ::-webkit-scrollbar-track { background:#111; }
        ::-webkit-scrollbar-thumb { background:#333; border-radius:2px; }
        ::-webkit-scrollbar-thumb:hover { background:#f59e0b; }
        .sidebar-link { transition: all .15s ease; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(245,158,11,.1); color: #f59e0b; border-left-color: #f59e0b; }
        .sidebar-link { border-left: 2px solid transparent; }
    </style>
</head>
<body class="bg-dark-900 text-gray-200 font-sans min-h-screen flex">

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-dark-800 border-r border-white/5 flex flex-col z-40 transition-transform duration-300 md:translate-x-0 -translate-x-full">
        <!-- Logo -->
        <div class="p-6 border-b border-white/5">
            <div class="font-condensed font-bold text-2xl text-white tracking-widest">NAZO<span class="text-amber-500">.</span></div>
            <div class="text-xs text-gray-500 mt-0.5">Admin Panel</div>
        </div>

        <!-- Nav Links -->
        <nav class="flex-1 py-4 overflow-y-auto">
            <?php
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $adminBase   = $adminBase ?? '';
            $adminSegment = '/' . ($_ENV['ADMIN_ROUTE'] ?? 'adminadalahraja');

            $navItems = [
                ['icon'=>'layout-dashboard', 'label'=>'Dashboard',  'path'=>'/dashboard'],
                ['icon'=>'link-2',            'label'=>'Cards',      'path'=>'/cards'],
                ['icon'=>'image',             'label'=>'Gallery',    'path'=>'/gallery'],
                ['icon'=>'message-square',    'label'=>'Komentar',   'path'=>'/comments'],
                ['icon'=>'share-2',           'label'=>'Social Icons','path'=>'/social'],
                ['icon'=>'user',              'label'=>'Profil',     'path'=>'/profile'],
                ['icon'=>'settings',          'label'=>'Settings',   'path'=>'/settings'],
            ];
            foreach ($navItems as $item):
                $href = $adminBase . $item['path'];
                $isActive = str_contains($currentPath, $adminSegment . $item['path']) ? 'active' : '';
            ?>
            <a href="<?= $href ?>" class="sidebar-link <?= $isActive ?> flex items-center gap-3 px-5 py-3 text-sm text-gray-400 font-medium">
                <i data-lucide="<?= $item['icon'] ?>" class="w-4 h-4 flex-shrink-0"></i>
                <?= $item['label'] ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <!-- Logout -->
        <div class="p-4 border-t border-white/5">
            <a href="<?= $adminBase ?>/logout"
               class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-500 hover:text-red-400 rounded-lg hover:bg-red-400/10 transition-all">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                Logout
            </a>
        </div>
    </aside>

    <!-- Mobile Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 z-30 hidden md:hidden" onclick="toggleSidebar()"></div>

    <!-- Main Content -->
    <div class="flex-1 md:ml-64 flex flex-col min-h-screen">
        <!-- Topbar -->
        <header class="sticky top-0 z-20 bg-dark-800/80 backdrop-blur border-b border-white/5 h-14 flex items-center px-4 md:px-6 gap-4">
            <button onclick="toggleSidebar()" class="md:hidden text-gray-400 hover:text-white">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
            <div class="flex-1"></div>
            <span class="text-xs text-gray-600">Admin</span>
            <div class="w-7 h-7 rounded-full bg-amber-500 flex items-center justify-center">
                <span class="text-black text-xs font-bold">N</span>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-4 md:p-6 lg:p-8">
            <?php echo $content; ?>
        </main>
    </div>

    <script>
        lucide.createIcons();
        function toggleSidebar() {
            const s = document.getElementById('sidebar');
            const o = document.getElementById('sidebar-overlay');
            s.classList.toggle('-translate-x-full');
            o.classList.toggle('hidden');
        }
    </script>
</body>
</html>
