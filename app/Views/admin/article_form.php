<?php
use App\Core\Markdown;
use App\Core\Security;

$base         = rtrim($_ENV['APP_URL'] ?? '', '/');
$isEdit       = !empty($isEdit) && !empty($article);
$actionUrl    = $isEdit ? ($adminBase . '/articles/update') : ($adminBase . '/articles/store');
$pageTitle    = $isEdit ? 'Edit Artikel' : 'Tulis Artikel Baru';
$currentThumb = $isEdit ? ($article['thumbnail_url'] ?? '') : '';
$previewThumb = Markdown::resolveMediaUrl($currentThumb, $base);

$inputCls = 'w-full bg-dark-700 border border-white/5 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-amber-500/50 transition-colors';

$suggestedCats = array_values(array_unique(array_merge(
    ['Umum', 'MLBB', 'Tips & Trik', 'Update', 'Tutorial', 'Opini'],
    $categories ?? []
)));
?>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <a href="<?= $adminBase ?>/articles" class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-amber-400 transition-colors mb-2">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Kembali ke Daftar Artikel</span>
        </a>
        <h1 class="font-condensed font-bold text-2xl sm:text-3xl text-white tracking-wide"><?= $pageTitle ?></h1>
    </div>
    <?php if ($isEdit && ($article['status'] ?? '') === 'published'): ?>
    <a href="<?= $base ?>/artikel/<?= rawurlencode($article['slug']) ?>" target="_blank" rel="noopener"
       class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-dark-800 border border-white/10 text-gray-300 hover:text-white hover:border-amber-500/40 transition-all">
        <i data-lucide="external-link" class="w-3.5 h-3.5 text-amber-500"></i>
        <span>Lihat Artikel</span>
    </a>
    <?php endif; ?>
</div>

<!-- Flash -->
<?php if (!empty($flash)): ?>
<?php [$t, $m] = explode(':', $flash, 2); ?>
<div class="mb-5 px-4 py-3 rounded-xl text-sm border <?= $t === 'success' ? 'bg-green-500/10 border-green-500/20 text-green-400' : 'bg-red-500/10 border-red-500/20 text-red-400' ?>">
    <?= Security::e($m) ?>
</div>
<?php endif; ?>

<form method="POST" action="<?= $actionUrl ?>" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
    <?php if ($isEdit): ?>
    <input type="hidden" name="id" value="<?= (int)$article['id'] ?>">
    <?php endif; ?>

    <!-- Main Editor Column -->
    <div class="lg:col-span-8 space-y-5">
        <div class="bg-dark-800 border border-white/5 rounded-2xl p-5 sm:p-6 space-y-5">
            <div>
                <label class="text-xs text-gray-400 mb-1.5 block">Judul Artikel <span class="text-red-400">*</span></label>
                <input type="text" name="title" id="article-title" required maxlength="200"
                       value="<?= Security::e($article['title'] ?? '') ?>"
                       class="<?= $inputCls ?> text-base font-medium"
                       placeholder="Contoh: Tips Push Rank Solo MLBB Season Terbaru">
            </div>

            <div>
                <label class="text-xs text-gray-400 mb-1.5 block">
                    Slug URL <span class="text-gray-600">(opsional — otomatis dibuat dari judul jika kosong)</span>
                </label>
                <div class="flex items-center bg-dark-700 border border-white/5 rounded-xl overflow-hidden focus-within:border-amber-500/50 transition-colors">
                    <span class="px-3 text-xs text-gray-500 bg-dark-900/50 py-3 border-r border-white/5 font-mono">/artikel/</span>
                    <input type="text" name="slug" id="article-slug" maxlength="200"
                           value="<?= Security::e($article['slug'] ?? '') ?>"
                           class="flex-1 bg-transparent px-3 py-3 text-sm text-white placeholder-gray-600 focus:outline-none font-mono"
                           placeholder="tips-push-rank-solo-mlbb">
                </div>
            </div>

            <div>
                <label class="text-xs text-gray-400 mb-1.5 block">
                    Ringkasan Singkat / Excerpt <span class="text-gray-600">(opsional — otomatis diambil dari paragraf awal jika kosong)</span>
                </label>
                <textarea name="excerpt" rows="2" maxlength="350"
                          class="<?= $inputCls ?> resize-none"
                          placeholder="Ringkasan singkat yang tampil di daftar artikel dan hasil pencarian Google..."><?= Security::e($article['excerpt'] ?? '') ?></textarea>
            </div>

            <!-- Content Editor with Toolbar & Live Preview -->
            <div>
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                    <label class="text-xs text-gray-400">Isi Artikel <span class="text-red-400">*</span></label>
                    <div class="inline-flex rounded-lg bg-dark-900 border border-white/10 p-0.5 text-xs">
                        <button type="button" id="tab-write" onclick="switchEditorMode('write')"
                                class="px-3 py-1 rounded-md font-medium bg-amber-500 text-black transition-all">
                            Tulis
                        </button>
                        <button type="button" id="tab-preview" onclick="switchEditorMode('preview')"
                                class="px-3 py-1 rounded-md font-medium text-gray-400 hover:text-white transition-all">
                            Preview
                        </button>
                    </div>
                </div>

                <!-- Formatting Toolbar -->
                <div id="editor-toolbar" class="flex flex-wrap items-center gap-1 p-2 rounded-t-xl bg-dark-900 border border-b-0 border-white/10">
                    <button type="button" onclick="insertLinePrefix('## ')" title="Heading"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-gray-400 hover:text-amber-400 hover:bg-white/5 transition-colors">H2</button>
                    <button type="button" onclick="insertLinePrefix('### ')" title="Subheading"
                            class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-gray-400 hover:text-amber-400 hover:bg-white/5 transition-colors">H3</button>
                    <span class="w-px h-4 bg-white/10 mx-1"></span>
                    <button type="button" onclick="wrapSelection('**', '**')" title="Tebal (Bold)"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-amber-400 hover:bg-white/5 transition-colors">
                        <i data-lucide="bold" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" onclick="wrapSelection('*', '*')" title="Miring (Italic)"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-amber-400 hover:bg-white/5 transition-colors">
                        <i data-lucide="italic" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" onclick="wrapSelection('`', '`')" title="Kode Inline"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-amber-400 hover:bg-white/5 transition-colors">
                        <i data-lucide="code" class="w-3.5 h-3.5"></i>
                    </button>
                    <span class="w-px h-4 bg-white/10 mx-1"></span>
                    <button type="button" onclick="insertLinePrefix('- ')" title="Daftar Poin"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-amber-400 hover:bg-white/5 transition-colors">
                        <i data-lucide="list" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" onclick="insertLinePrefix('1. ')" title="Daftar Bernomor"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-amber-400 hover:bg-white/5 transition-colors">
                        <i data-lucide="list-ordered" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" onclick="insertLinePrefix('> ')" title="Kutipan (Quote)"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-amber-400 hover:bg-white/5 transition-colors">
                        <i data-lucide="quote" class="w-3.5 h-3.5"></i>
                    </button>
                    <button type="button" onclick="insertLink()" title="Sisipkan Tautan"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-amber-400 hover:bg-white/5 transition-colors">
                        <i data-lucide="link-2" class="w-3.5 h-3.5"></i>
                    </button>
                </div>

                <!-- Textarea -->
                <textarea name="content" id="article-content" required rows="14"
                          class="w-full bg-dark-700 border border-white/10 rounded-b-xl px-4 py-3.5 text-sm text-white placeholder-gray-600 focus:outline-none focus:border-amber-500/50 transition-colors leading-relaxed font-sans"
                          placeholder="Tulis isi artikel di sini...&#10;&#10;Mendukung format sederhana:&#10;## Subjudul&#10;**teks tebal**, *teks miring*, - poin daftar, > kutipan, dan [tautan](https://...)"><?= Security::e($article['content'] ?? '') ?></textarea>

                <!-- Preview Panel -->
                <div id="article-preview"
                     class="hidden min-h-[320px] bg-dark-700/70 border border-white/10 rounded-xl p-5 text-sm text-gray-300 leading-relaxed break-words">
                </div>

                <div class="flex items-center justify-between mt-1.5 text-[11px] text-gray-600">
                    <span>Format aman: Heading (##), **Bold**, *Italic*, Daftar (- / 1.), > Kutipan, [Link](https://...)</span>
                    <span id="word-count">0 kata</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Settings Column -->
    <div class="lg:col-span-4 space-y-5">
        <!-- Publish Box -->
        <div class="bg-dark-800 border border-white/5 rounded-2xl p-5 space-y-4">
            <h2 class="font-condensed font-bold text-lg text-white tracking-wide border-b border-white/5 pb-2.5">Publikasi</h2>

            <div>
                <label class="text-xs text-gray-400 mb-1.5 block">Status Artikel</label>
                <select name="status" class="<?= $inputCls ?>">
                    <option value="published" <?= ($article['status'] ?? 'published') === 'published' ? 'selected' : '' ?>>Published (Tampil Publik)</option>
                    <option value="draft" <?= ($article['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft (Disembunyikan)</option>
                </select>
            </div>

            <div>
                <label class="text-xs text-gray-400 mb-1.5 block">Kategori</label>
                <input type="text" name="category" list="category-suggestions" maxlength="80"
                       value="<?= Security::e($article['category'] ?? 'Umum') ?>"
                       class="<?= $inputCls ?>" placeholder="Contoh: MLBB, Tips, Update">
                <datalist id="category-suggestions">
                    <?php foreach ($suggestedCats as $cat): ?>
                    <option value="<?= Security::e($cat) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <?php if ($isEdit): ?>
            <div class="p-3 rounded-xl bg-dark-700/60 border border-white/5 text-xs text-gray-400 space-y-1.5">
                <div class="flex items-center justify-between">
                    <span>Total Pembaca</span>
                    <span class="font-semibold text-amber-400"><?= number_format((int)($article['views_count'] ?? 0)) ?></span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Dibuat</span>
                    <span><?= htmlspecialchars(date('d M Y, H:i', strtotime($article['created_at']))) ?></span>
                </div>
            </div>
            <?php endif; ?>

            <button type="submit"
                    class="w-full py-3 rounded-xl text-sm font-semibold text-black hover:opacity-90 transition-all shadow-md flex items-center justify-center gap-2"
                    style="background:#f59e0b">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span><?= $isEdit ? 'Simpan Perubahan' : 'Publikasikan Artikel' ?></span>
            </button>
        </div>

        <!-- Thumbnail Box -->
        <div class="bg-dark-800 border border-white/5 rounded-2xl p-5 space-y-4">
            <h2 class="font-condensed font-bold text-lg text-white tracking-wide border-b border-white/5 pb-2.5">Cover Thumbnail</h2>

            <div class="aspect-video rounded-xl overflow-hidden bg-dark-700 border border-white/5 flex items-center justify-center">
                <img id="thumb-preview-img"
                     src="<?= htmlspecialchars($previewThumb) ?>"
                     alt="Preview Thumbnail"
                     class="w-full h-full object-cover <?= $previewThumb !== '' ? '' : 'hidden' ?>">
                <div id="thumb-placeholder" class="text-center p-4 <?= $previewThumb !== '' ? 'hidden' : '' ?>">
                    <i data-lucide="image" class="w-8 h-8 mx-auto text-gray-600 mb-1.5"></i>
                    <p class="text-xs text-gray-600">Belum ada gambar cover</p>
                </div>
            </div>

            <div>
                <label class="text-xs text-gray-400 mb-1.5 block">Upload Gambar Cover (opsional)</label>
                <label for="thumbnail_file"
                       class="cursor-pointer w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-xs font-medium text-gray-300 border border-white/10 transition-all">
                    <i data-lucide="upload" class="w-4 h-4 text-amber-500"></i>
                    <span id="thumb-file-label">Pilih File Gambar</span>
                </label>
                <input type="file" name="thumbnail_file" id="thumbnail_file" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden"
                       onchange="previewThumbFile(this)">
                <p class="text-[11px] text-gray-600 mt-1">JPG, PNG, WebP, GIF. Mengunggah file akan menggantikan URL di bawah.</p>
            </div>

            <div>
                <label class="text-xs text-gray-400 mb-1.5 block">Atau URL Gambar Eksternal</label>
                <input type="text" name="thumbnail_url" id="thumbnail_url" maxlength="500"
                       value="<?= Security::e($currentThumb) ?>"
                       oninput="previewThumbUrl(this.value)"
                       class="<?= $inputCls ?>" placeholder="https://example.com/cover.jpg">
            </div>

            <?php if ($isEdit && $currentThumb !== ''): ?>
            <label class="flex items-center gap-2 text-xs text-red-400 cursor-pointer pt-1">
                <input type="checkbox" name="remove_thumbnail" value="1" class="rounded border-white/20 bg-dark-700 accent-red-500">
                <span>Hapus gambar cover saat disimpan</span>
            </label>
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
    const contentEl  = document.getElementById('article-content');
    const previewEl  = document.getElementById('article-preview');
    const toolbarEl  = document.getElementById('editor-toolbar');
    const wordCount  = document.getElementById('word-count');
    const titleEl    = document.getElementById('article-title');
    const slugEl     = document.getElementById('article-slug');

    function updateWordCount() {
        const text  = (contentEl.value || '').trim();
        const words = text ? text.split(/\s+/).length : 0;
        if (wordCount) wordCount.textContent = words + ' kata';
    }
    contentEl.addEventListener('input', updateWordCount);
    updateWordCount();

    // Update slug placeholder from title while creating
    <?php if (!$isEdit): ?>
    titleEl.addEventListener('input', function() {
        const generated = this.value.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-')
            .replace(/^-+|-+$/g, '');
        slugEl.placeholder = generated || 'slug-artikel-otomatis';
    });
    <?php endif; ?>

    function wrapSelection(before, after) {
        const start = contentEl.selectionStart;
        const end   = contentEl.selectionEnd;
        const val   = contentEl.value;
        const sel   = val.substring(start, end) || 'teks';
        contentEl.value = val.substring(0, start) + before + sel + after + val.substring(end);
        contentEl.focus();
        contentEl.setSelectionRange(start + before.length, start + before.length + sel.length);
        updateWordCount();
    }

    function insertLinePrefix(prefix) {
        const start = contentEl.selectionStart;
        const val   = contentEl.value;
        const lineStart = val.lastIndexOf('\n', start - 1) + 1;
        contentEl.value = val.substring(0, lineStart) + prefix + val.substring(lineStart);
        contentEl.focus();
        contentEl.setSelectionRange(start + prefix.length, start + prefix.length);
        updateWordCount();
    }

    function insertLink() {
        const url = prompt('Masukkan URL tujuan (harus diawali https:// atau http://):', 'https://');
        if (!url || !/^https?:\/\//i.test(url.trim())) return;
        wrapSelection('[', '](' + url.trim() + ')');
    }

    function escapeHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatInlinePreview(line) {
        let s = escapeHtml(line);
        s = s.replace(/`([^`]+)`/g, '<code class="px-1.5 py-0.5 rounded bg-white/10 text-amber-300 text-xs font-mono">$1</code>');
        s = s.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/gi, '<a href="$2" target="_blank" rel="noopener noreferrer" class="text-amber-400 underline">$1</a>');
        s = s.replace(/\*\*(.+?)\*\*/g, '<strong class="font-semibold text-white">$1</strong>');
        s = s.replace(/(^|[^*])\*([^\s*][^*]*?)\*(?!\*)/g, '$1<em class="italic text-gray-200">$2</em>');
        return s;
    }

    function renderMarkdownPreview(raw) {
        const lines = raw.replace(/\r\n?/g, '\n').split('\n');
        const out   = [];
        let i = 0;

        while (i < lines.length) {
            const trimmed = lines[i].trim();
            if (!trimmed) { i++; continue; }

            if (trimmed.startsWith('```')) {
                const code = [];
                i++;
                while (i < lines.length && !lines[i].trim().startsWith('```')) {
                    code.push(escapeHtml(lines[i]));
                    i++;
                }
                i++;
                out.push('<pre class="my-4 p-3 rounded-xl bg-dark-900 border border-white/10 overflow-x-auto text-xs text-amber-300 font-mono"><code>' + code.join('\n') + '</code></pre>');
                continue;
            }

            if (/^([-*_])\1{2,}$/.test(trimmed)) {
                out.push('<hr class="my-5 border-white/10">');
                i++;
                continue;
            }

            const hm = trimmed.match(/^(#{1,3})\s+(.+)$/);
            if (hm) {
                const lvl = hm[1].length;
                out.push(lvl <= 2
                    ? '<h2 class="font-condensed font-bold text-2xl text-white mt-6 mb-2">' + formatInlinePreview(hm[2]) + '</h2>'
                    : '<h3 class="font-condensed font-bold text-xl text-amber-400 mt-5 mb-2">' + formatInlinePreview(hm[2]) + '</h3>');
                i++;
                continue;
            }

            if (/^>\s?/.test(trimmed)) {
                const q = [];
                while (i < lines.length && /^>\s?/.test(lines[i].trim())) {
                    q.push(formatInlinePreview(lines[i].trim().replace(/^>\s?/, '')));
                    i++;
                }
                out.push('<blockquote class="my-4 pl-4 border-l-2 border-amber-500 bg-amber-500/5 py-2 pr-3 rounded-r-lg italic text-gray-300">' + q.join('<br>') + '</blockquote>');
                continue;
            }

            if (/^[-*]\s+/.test(trimmed)) {
                const li = [];
                while (i < lines.length && /^[-*]\s+/.test(lines[i].trim())) {
                    li.push('<li>' + formatInlinePreview(lines[i].trim().replace(/^[-*]\s+/, '')) + '</li>');
                    i++;
                }
                out.push('<ul class="my-3 pl-5 list-disc space-y-1 text-gray-300">' + li.join('') + '</ul>');
                continue;
            }

            if (/^\d+\.\s+/.test(trimmed)) {
                const li = [];
                while (i < lines.length && /^\d+\.\s+/.test(lines[i].trim())) {
                    li.push('<li>' + formatInlinePreview(lines[i].trim().replace(/^\d+\.\s+/, '')) + '</li>');
                    i++;
                }
                out.push('<ol class="my-3 pl-5 list-decimal space-y-1 text-gray-300">' + li.join('') + '</ol>');
                continue;
            }

            const p = [];
            while (i < lines.length) {
                const c = lines[i].trim();
                if (!c || c.startsWith('```') || /^([-*_])\1{2,}$/.test(c) || /^(#{1,3})\s+/.test(c) || /^>\s?/.test(c) || /^[-*]\s+/.test(c) || /^\d+\.\s+/.test(c)) {
                    break;
                }
                p.push(formatInlinePreview(lines[i]));
                i++;
            }
            if (p.length) {
                out.push('<p class="my-3 text-gray-300 leading-relaxed">' + p.join('<br>') + '</p>');
            }
        }
        return out.length ? out.join('') : '<p class="text-gray-600 italic">Belum ada isi artikel untuk dipratinjau.</p>';
    }

    function switchEditorMode(mode) {
        const tabWrite   = document.getElementById('tab-write');
        const tabPreview = document.getElementById('tab-preview');

        if (mode === 'preview') {
            previewEl.innerHTML = renderMarkdownPreview(contentEl.value);
            contentEl.classList.add('hidden');
            toolbarEl.classList.add('hidden');
            previewEl.classList.remove('hidden');
            tabPreview.classList.add('bg-amber-500', 'text-black');
            tabPreview.classList.remove('text-gray-400');
            tabWrite.classList.remove('bg-amber-500', 'text-black');
            tabWrite.classList.add('text-gray-400');
        } else {
            previewEl.classList.add('hidden');
            contentEl.classList.remove('hidden');
            toolbarEl.classList.remove('hidden');
            tabWrite.classList.add('bg-amber-500', 'text-black');
            tabWrite.classList.remove('text-gray-400');
            tabPreview.classList.remove('bg-amber-500', 'text-black');
            tabPreview.classList.add('text-gray-400');
        }
    }

    function previewThumbFile(input) {
        if (input.files && input.files[0]) {
            const file  = input.files[0];
            const label = document.getElementById('thumb-file-label');
            if (label) label.textContent = file.name;
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('thumb-preview-img');
                const ph  = document.getElementById('thumb-placeholder');
                img.src = e.target.result;
                img.classList.remove('hidden');
                ph.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    }

    function previewThumbUrl(url) {
        const img = document.getElementById('thumb-preview-img');
        const ph  = document.getElementById('thumb-placeholder');
        const v   = (url || '').trim();
        if (/^https?:\/\//i.test(v) || v.startsWith('/Media/') || v.startsWith('/Foto/')) {
            img.src = v.startsWith('/') ? ('<?= $base ?>' + v) : v;
            img.classList.remove('hidden');
            ph.classList.add('hidden');
        } else if (!v) {
            img.classList.add('hidden');
            ph.classList.remove('hidden');
        }
    }
</script>
