<?php use App\Core\Security; ?>

<div class="mb-6">
    <h1 class="font-condensed font-bold text-2xl sm:text-3xl text-white tracking-wide">Settings</h1>
    <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Konfigurasi SEO, mode maintenance, database, dan fitur sistem.</p>
</div>

<!-- Flash -->
<?php if (!empty($flash)): ?>
<?php [$t,$m] = explode(':', $flash, 2); ?>
<div class="mb-4 px-4 py-3 rounded-xl text-sm border <?= $t==='success'?'bg-green-500/10 border-green-500/20 text-green-400':'bg-red-500/10 border-red-500/20 text-red-400' ?>">
    <?= Security::e($m) ?>
</div>
<?php endif; ?>

<?php
$s = $settings;
$inputCls = "w-full bg-dark-700 border border-white/5 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-700 focus:outline-none focus:border-amber-500/50 transition-colors";
?>

<form method="POST" action="<?= $adminBase ?>/settings/update" class="space-y-6">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">

    <!-- Section: SEO & Meta Tags -->
    <div class="bg-dark-800 border border-white/5 rounded-2xl p-6 space-y-5">
        <div class="flex items-center justify-between border-b border-white/5 pb-3">
            <div>
                <h2 class="font-condensed font-bold text-lg text-white tracking-wide">SEO & Meta Tags</h2>
                <p class="text-xs text-gray-600 mt-0.5">Optimalkan tampilan halaman di Google Search & share media sosial</p>
            </div>
            <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
                <i data-lucide="search" class="w-4 h-4"></i>
            </div>
        </div>

        <div>
            <label class="text-xs text-gray-500 mb-1 block">Meta Title (Judul Tab & Hasil Google)</label>
            <input type="text" name="seo_title" value="<?= Security::e($s['seo_title'] ?? 'NAZO ナゾ — Links') ?>"
                   class="<?= $inputCls ?>" placeholder="NAZO ナゾ — Links">
            <span class="text-[11px] text-gray-600 mt-1 block">Contoh: "NAZO ナゾ — Official MLBB Content Creator Links"</span>
        </div>

        <div>
            <label class="text-xs text-gray-500 mb-1 block">Meta Description (Deskripsi Cuplikan)</label>
            <textarea name="seo_description" rows="3" class="<?= $inputCls ?> resize-none"
                      placeholder="Deskripsi singkat yang muncul di hasil pencarian Google dan share link"><?= Security::e($s['seo_description'] ?? '') ?></textarea>
            <span class="text-[11px] text-gray-600 mt-1 block">Rekomendasi 120-160 karakter agar tidak terpotong di Google.</span>
        </div>

        <div>
            <label class="text-xs text-gray-500 mb-1 block">Open Graph Image URL (Gambar Share Preview)</label>
            <input type="text" name="seo_og_image" value="<?= Security::e($s['seo_og_image'] ?? '') ?>"
                   class="<?= $inputCls ?>" placeholder="https://example.com/banner.jpg (kosongkan jika pakai foto profil)">
            <span class="text-[11px] text-gray-600 mt-1 block">Gambar thumbnail saat link dibagikan ke WhatsApp, Facebook, Twitter, Telegram, dll.</span>
        </div>
    </div>

    <!-- Section: General & Maintenance -->
    <div class="bg-dark-800 border border-white/5 rounded-2xl p-6 space-y-5">
        <h2 class="font-condensed font-bold text-lg text-white tracking-wide border-b border-white/5 pb-3">Umum & Pemeliharaan</h2>

        <div>
            <label class="text-xs text-gray-500 mb-1 block">Teks Footer</label>
            <textarea name="footer_text" rows="3" class="<?= $inputCls ?> resize-none"
                      placeholder="Teks yang tampil di footer halaman utama"><?= Security::e($s['footer_text'] ?? '') ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Judul Gallery</label>
                <input type="text" name="gallery_title" value="<?= Security::e($s['gallery_title'] ?? '') ?>"
                       class="<?= $inputCls ?>" placeholder="Makasih Udah Mampir">
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Subtitle Gallery</label>
                <input type="text" name="gallery_subtitle" value="<?= Security::e($s['gallery_subtitle'] ?? '') ?>"
                       class="<?= $inputCls ?>" placeholder="Powered by...">
            </div>
        </div>

        <!-- Maintenance Mode Box -->
        <div class="p-5 bg-dark-700/80 rounded-xl border border-white/5 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm text-white font-medium flex items-center gap-2">
                        <span>Mode Maintenance</span>
                        <?php if (($s['maintenance_mode'] ?? '0') === '1'): ?>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">AKTIF</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs text-gray-500 mt-0.5">Tampilkan halaman Coming Soon elegan dengan countdown timer ke pengunjung</div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                    <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer"
                           <?= ($s['maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                    <div class="w-11 h-6 bg-dark-500 rounded-full peer peer-checked:bg-amber-500 peer-focus:ring-2 peer-focus:ring-amber-500/30 transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                </label>
            </div>

            <!-- Countdown Datetime Picker -->
            <div class="pt-3 border-t border-white/5">
                <label class="text-xs text-gray-400 mb-1.5 block flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span>Target Waktu Selesai (Countdown Timer)</span>
                </label>
                <input type="datetime-local" name="maintenance_countdown"
                       value="<?= !empty($s['maintenance_countdown']) ? htmlspecialchars(date('Y-m-d\TH:i', strtotime($s['maintenance_countdown']))) : '' ?>"
                       class="<?= $inputCls ?>">
                <span class="text-[11px] text-gray-600 mt-1 block">Countdown timer di halaman maintenance akan menghitung mundur hingga waktu ini. Kosongkan jika tanpa target waktu pasti.</span>
            </div>
        </div>
    </div>

    <!-- Section: Popup Promosi -->
    <div class="bg-dark-800 border border-white/5 rounded-2xl p-6 space-y-5">
        <h2 class="font-condensed font-bold text-lg text-white tracking-wide border-b border-white/5 pb-3">Popup Promosi</h2>

        <div class="flex items-center justify-between p-4 bg-dark-700 rounded-xl border border-white/5">
            <div>
                <div class="text-sm text-white font-medium">Aktifkan Popup Promosi</div>
                <div class="text-xs text-gray-600 mt-0.5">Tampilkan popup promosi otomatis setelah pengunjung membuka halaman (delay 3 detik)</div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                <input type="checkbox" name="ad_enabled" value="1" class="sr-only peer" id="ad-toggle"
                       <?= ($s['ad_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
                       onchange="document.getElementById('ad-config').classList.toggle('hidden', !this.checked)">
                <div class="w-11 h-6 bg-dark-500 rounded-full peer peer-checked:bg-amber-500 transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
            </label>
        </div>

        <div id="ad-config" class="space-y-4 <?= ($s['ad_enabled'] ?? '0') !== '1' ? 'hidden' : '' ?>">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Judul Popup</label>
                    <input type="text" name="ad_title" value="<?= Security::e($s['ad_title'] ?? '') ?>" class="<?= $inputCls ?>" placeholder="Judul promo">
                </div>
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Teks Tombol</label>
                    <input type="text" name="ad_button_text" value="<?= Security::e($s['ad_button_text'] ?? 'Lihat Sekarang') ?>" class="<?= $inputCls ?>" placeholder="Lihat Sekarang">
                </div>
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Deskripsi</label>
                <textarea name="ad_description" rows="2" class="<?= $inputCls ?> resize-none"><?= Security::e($s['ad_description'] ?? '') ?></textarea>
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">URL Gambar Banner</label>
                <input type="text" name="ad_image_url" value="<?= Security::e($s['ad_image_url'] ?? '') ?>" class="<?= $inputCls ?>" placeholder="https://">
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">URL Tujuan</label>
                <input type="text" name="ad_target_url" value="<?= Security::e($s['ad_target_url'] ?? '') ?>" class="<?= $inputCls ?>" placeholder="https://">
            </div>
        </div>
    </div>

    <button type="submit" class="w-full sm:w-auto px-8 py-3 rounded-xl text-sm font-semibold text-black hover:opacity-90 transition-all shadow-md" style="background:#f59e0b">
        Simpan Semua Pengaturan
    </button>
</form>

<!-- Section: Kelola Database (Database Tools) -->
<div class="mt-10 bg-dark-800 border border-white/5 rounded-2xl p-6 space-y-6">
    <div class="flex items-center justify-between border-b border-white/5 pb-3">
        <div>
            <h2 class="font-condensed font-bold text-lg text-white tracking-wide">Kelola Database</h2>
            <p class="text-xs text-gray-500 mt-0.5">Backup data, optimasi performa tabel, dan pembersihan log sistem</p>
        </div>
        <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
            <i data-lucide="database" class="w-4 h-4"></i>
        </div>
    </div>

    <!-- Quick Stats Database -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div class="p-4 rounded-xl bg-dark-700/60 border border-white/5">
            <span class="text-[11px] font-medium text-gray-400 block uppercase">Ukuran Database</span>
            <span class="text-xl font-condensed font-bold text-white mt-1 block"><?= number_format($totalSizeKb ?? 0, 1) ?> KB</span>
        </div>
        <div class="p-4 rounded-xl bg-dark-700/60 border border-white/5">
            <span class="text-[11px] font-medium text-gray-400 block uppercase">Total Baris Data</span>
            <span class="text-xl font-condensed font-bold text-white mt-1 block"><?= number_format($totalRows ?? 0) ?> baris</span>
        </div>
        <div class="p-4 rounded-xl bg-dark-700/60 border border-white/5 col-span-2 sm:col-span-1">
            <span class="text-[11px] font-medium text-gray-400 block uppercase">Jumlah Tabel</span>
            <span class="text-xl font-condensed font-bold text-amber-400 mt-1 block"><?= count($tableStats ?? []) ?> tabel</span>
        </div>
    </div>

    <!-- Backup & Optimize Actions -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Backup Action -->
        <div class="p-5 bg-dark-700/40 rounded-xl border border-white/5 flex flex-col justify-between space-y-3">
            <div>
                <div class="flex items-center gap-2 text-white font-medium text-sm">
                    <i data-lucide="download" class="w-4 h-4 text-amber-500"></i>
                    <span>Cadangkan Database (SQL Backup)</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Unduh seluruh struktur tabel dan data terkini ke file .sql untuk keamanan cadangan.</p>
            </div>
            <a href="<?= $adminBase ?>/database/backup"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-black bg-amber-500 hover:opacity-90 transition-all">
                <i data-lucide="download-cloud" class="w-3.5 h-3.5"></i>
                <span>Download File Backup (.sql)</span>
            </a>
        </div>

        <!-- Optimize Action -->
        <div class="p-5 bg-dark-700/40 rounded-xl border border-white/5 flex flex-col justify-between space-y-3">
            <div>
                <div class="flex items-center gap-2 text-white font-medium text-sm">
                    <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                    <span>Optimasi & Perbaiki Tabel</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Menjalankan kueri OPTIMIZE TABLE untuk merapikan indeks, defragmentasi, dan mempercepat kueri.</p>
            </div>
            <form method="POST" action="<?= $adminBase ?>/database/optimize">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 transition-all">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                    <span>Jalankan Optimasi Tabel</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Cleanup Data Section -->
    <div class="p-5 bg-dark-700/30 rounded-xl border border-white/5 space-y-3">
        <div>
            <div class="flex items-center gap-2 text-white font-medium text-sm">
                <i data-lucide="trash-2" class="w-4 h-4 text-amber-500"></i>
                <span>Pembersihan Log & Riwayat (Maintenance Storage)</span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Pilih data riwayat yang ingin dibersihkan agar database tetap ringan dan cepat.</p>
        </div>

        <form method="POST" action="<?= $adminBase ?>/database/cleanup" class="flex flex-col sm:flex-row gap-3 pt-2"
              onsubmit="return confirm('Apakah Anda yakin ingin melakukan pembersihan data ini?')">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <select name="cleanup_type" required
                    class="flex-1 bg-dark-700 border border-white/5 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-amber-500/50">
                <option value="visitor_logs_30d">Log Pengunjung: Hapus yang lebih dari 30 hari lalu</option>
                <option value="visitor_logs_all">Log Pengunjung: Kosongkan seluruh data kunjungan</option>
                <option value="admin_logs_all">Log Admin: Kosongkan seluruh riwayat log aktivitas admin</option>
                <option value="clicks_all">Log Klik: Reset semua data analitik klik card</option>
            </select>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-xs font-semibold text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 transition-all whitespace-nowrap">
                Bersihkan Sekarang
            </button>
        </form>
    </div>

    <!-- Rincian Tabel -->
    <?php if (!empty($tableStats)): ?>
    <div class="overflow-x-auto border border-white/5 rounded-xl">
        <table class="w-full text-xs text-left">
            <thead>
                <tr class="border-b border-white/5 text-gray-500 uppercase tracking-wider bg-dark-700/50">
                    <th class="px-4 py-3">Nama Tabel</th>
                    <th class="px-4 py-3 text-right">Perkiraan Baris</th>
                    <th class="px-4 py-3 text-right">Ukuran</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tableStats as $ts): ?>
                <tr class="border-b border-white/5 last:border-0 hover:bg-white/2">
                    <td class="px-4 py-2.5 font-medium text-white font-mono"><?= htmlspecialchars($ts['table']) ?></td>
                    <td class="px-4 py-2.5 text-right text-gray-400"><?= number_format($ts['rows']) ?></td>
                    <td class="px-4 py-2.5 text-right text-amber-400 font-semibold"><?= number_format((float)$ts['size_kb'], 1) ?> KB</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
