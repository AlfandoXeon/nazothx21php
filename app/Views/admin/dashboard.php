<?php use App\Core\Security; ?>

<!-- Page Header -->
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="font-condensed font-bold text-3xl text-white tracking-wide">Dashboard</h1>
        <p class="text-gray-600 text-sm mt-1">Selamat datang di panel admin NAZO.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="<?= rtrim($_ENV['APP_URL'] ?? '', '/') ?>/" target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-dark-800 border border-white/10 text-gray-300 hover:text-white hover:border-amber-500/50 transition-all">
            <i data-lucide="external-link" class="w-3.5 h-3.5 text-amber-500"></i>
            <span>Buka Website</span>
        </a>
    </div>
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
    <?php
    $stats = [
        ['label'=>'Total Kunjungan', 'value'=>$totalVisitors ?? 0, 'icon'=>'users',          'color'=>'#f59e0b'],
        ['label'=>'Hari Ini',        'value'=>$todayVisitors ?? 0, 'icon'=>'user-check',     'color'=>'#10b981'],
        ['label'=>'Klik Link',       'value'=>$totalClicks ?? 0,   'icon'=>'mouse-pointer',  'color'=>'#3b82f6'],
        ['label'=>'Active Cards',    'value'=>$totalCards,         'icon'=>'link-2',         'color'=>'#8b5cf6'],
        ['label'=>'Media Gallery',   'value'=>$totalPhotos + $totalVideos, 'icon'=>'image',  'color'=>'#ec4899'],
        ['label'=>'Komentar',        'value'=>$totalComments,      'icon'=>'message-square', 'color'=>'#06b6d4'],
    ];
    foreach ($stats as $stat):
    ?>
    <div class="bg-dark-800 border border-white/5 rounded-2xl p-4 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-medium text-gray-500 uppercase tracking-wider"><?= $stat['label'] ?></span>
            <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                 style="background:<?= $stat['color'] ?>15; color:<?= $stat['color'] ?>">
                <i data-lucide="<?= $stat['icon'] ?>" class="w-3.5 h-3.5"></i>
            </div>
        </div>
        <div class="text-2xl font-condensed font-bold text-white"><?= number_format($stat['value']) ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Analytics & Top Links Grid -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">

    <!-- 7-Day Visitor Analytics (CSS Bar Chart) -->
    <div class="lg:col-span-7 bg-dark-800 border border-white/5 rounded-2xl p-6 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="font-condensed font-bold text-lg text-white tracking-wide">Tren Kunjungan (7 Hari)</h2>
                <p class="text-xs text-gray-500 mt-0.5">Jumlah pengunjung unik per hari</p>
            </div>
            <div class="text-xs font-semibold text-amber-500 bg-amber-500/10 px-2.5 py-1 rounded-md border border-amber-500/20">
                <?= number_format(array_sum(array_column($visitor7Days ?? [], 'visits'))) ?> total
            </div>
        </div>

        <?php
        $chartData = $visitor7Days ?? [];
        $maxVisits = 1;
        foreach ($chartData as $d) {
            if ($d['visits'] > $maxVisits) $maxVisits = $d['visits'];
        }
        ?>

        <!-- Chart Bars -->
        <div class="h-44 w-full flex items-end justify-between gap-2 pt-4 px-2 border-b border-white/10 pb-2">
            <?php foreach ($chartData as $bar):
                $heightPct = round(($bar['visits'] / $maxVisits) * 100);
                $isToday   = $bar['date'] === date('Y-m-d');
            ?>
            <div class="flex-1 flex flex-col items-center justify-end h-full group relative">
                <!-- Tooltip on hover -->
                <div class="absolute -top-8 bg-black/90 border border-white/10 text-white text-[10px] px-2 py-0.5 rounded shadow pointer-events-none opacity-0 group-hover:opacity-100 transition-opacity z-10 whitespace-nowrap">
                    <?= $bar['visits'] ?> kunjungan
                </div>

                <!-- Bar Column -->
                <div class="w-full max-w-[38px] rounded-t-md transition-all duration-300 relative flex items-end justify-center"
                     style="height: <?= max(6, $heightPct) ?>%; background: <?= $isToday ? 'linear-gradient(180deg, #fbbf24 0%, #f59e0b 100%)' : 'linear-gradient(180deg, rgba(245,158,11,0.5) 0%, rgba(245,158,11,0.2) 100%)' ?>;">
                    <?php if ($bar['visits'] > 0): ?>
                    <span class="text-[10px] font-semibold text-black mb-1 hidden sm:inline"><?= $bar['visits'] ?></span>
                    <?php endif; ?>
                </div>

                <!-- Date Label -->
                <span class="text-[10px] <?= $isToday ? 'text-amber-400 font-bold' : 'text-gray-500' ?> mt-2 block truncate">
                    <?= $isToday ? 'Hari ini' : $bar['label'] ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Top Clicked Cards -->
    <div class="lg:col-span-5 bg-dark-800 border border-white/5 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-condensed font-bold text-lg text-white tracking-wide">Top Links Paling Populer</h2>
                <p class="text-xs text-gray-500 mt-0.5">Card dengan klik terbanyak</p>
            </div>
            <a href="<?= $adminBase ?>/cards" class="text-xs text-amber-500 hover:underline">Semua Card</a>
        </div>

        <?php if (empty($topCards)): ?>
        <p class="text-gray-600 text-sm text-center py-8">Belum ada data klik card.</p>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach ($topCards as $idx => $tc): ?>
            <div class="flex items-center gap-3 p-2.5 rounded-xl bg-dark-700/60 border border-white/5">
                <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold font-condensed <?= $idx === 0 ? 'bg-amber-500 text-black' : 'bg-white/10 text-gray-400' ?>">
                    <?= $idx + 1 ?>
                </span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-white truncate"><?= Security::e($tc['title']) ?></div>
                    <div class="text-[11px] text-gray-500 truncate"><?= Security::e($tc['url']) ?></div>
                </div>
                <div class="flex items-center gap-1 px-2 py-1 rounded-md bg-white/5 text-amber-400 text-xs font-semibold flex-shrink-0">
                    <i data-lucide="mouse-pointer" class="w-3 h-3"></i>
                    <span><?= number_format($tc['clicks'] ?? 0) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- Quick Actions -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <?php
    $actions = [
        ['href'=>$adminBase.'/cards',    'icon'=>'plus-circle',  'label'=>'Kelola Cards & Link', 'desc'=>'Tambah link atau cek jumlah klik'],
        ['href'=>$adminBase.'/gallery',  'icon'=>'upload-cloud', 'label'=>'Upload ke Gallery',   'desc'=>'Foto atau video baru'],
        ['href'=>$adminBase.'/settings', 'icon'=>'sliders',      'label'=>'Konfigurasi Website', 'desc'=>'SEO, Maintenance, & Popup Promo'],
    ];
    foreach ($actions as $a):
    ?>
    <a href="<?= $a['href'] ?>"
       class="bg-dark-800 border border-white/5 hover:border-amber-500/30 rounded-2xl p-5 flex items-center gap-4 transition-all group">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 bg-amber-500/10 text-amber-500 group-hover:bg-amber-500 group-hover:text-black transition-all">
            <i data-lucide="<?= $a['icon'] ?>" class="w-5 h-5"></i>
        </div>
        <div>
            <div class="text-sm font-semibold text-white"><?= $a['label'] ?></div>
            <div class="text-xs text-gray-600 mt-0.5"><?= $a['desc'] ?></div>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<!-- Activity Log -->
<div class="bg-dark-800 border border-white/5 rounded-2xl p-6">
    <h2 class="font-condensed font-bold text-lg text-white tracking-wide mb-4">Aktivitas Terbaru</h2>
    <?php if (empty($logs)): ?>
    <p class="text-gray-700 text-sm text-center py-4">Belum ada aktivitas.</p>
    <?php else: ?>
    <div class="space-y-2">
        <?php foreach ($logs as $log): ?>
        <div class="flex items-center gap-4 py-2.5 border-b border-white/5 last:border-0">
            <div class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:#f59e0b"></div>
            <span class="text-sm text-gray-400 flex-1"><?= Security::e($log['action']) ?>
                <?php if ($log['detail']): ?><span class="text-gray-600 text-xs ml-1"><?= Security::e($log['detail']) ?></span><?php endif; ?>
            </span>
            <span class="text-xs text-gray-700 flex-shrink-0"><?= date('d/m H:i', strtotime($log['created_at'])) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
