<?php
$base        = rtrim($_ENV['APP_URL'] ?? '', '/');
$footerText  = $settings['footer_text'] ?? 'By @NazoTHX';
$year        = date('Y');
?>
<footer class="relative z-10 border-t border-white/5 mt-16">
    <div class="max-w-2xl mx-auto px-4 py-10 text-center" data-aos="fade-up" data-aos-offset="30">
        <div class="font-condensed font-bold text-2xl tracking-widest text-white mb-4">
            NAZO<span style="color:#f59e0b">.</span>
        </div>
        <div class="text-gray-500 text-sm leading-relaxed mb-6 max-w-md mx-auto">
            <?= $footerText ?>
        </div>
        <div class="text-xs text-gray-700">
            &copy; <?= $year ?> NAZO ナゾ. All rights reserved.
        </div>
    </div>
</footer>
