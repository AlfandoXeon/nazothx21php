<?php
namespace App\Core;

/**
 * Safe lightweight Markdown renderer & reading utilities.
 * Escapes all raw HTML first via Security::e() to prevent Stored XSS.
 */
class Markdown
{
    /**
     * Render lightweight Markdown to safe HTML styled for dark-amber theme.
     */
    public static function toHtml(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));
        if ($text === '') {
            return '';
        }

        $lines = explode("\n", $text);
        $html  = [];
        $count = count($lines);
        $i     = 0;

        while ($i < $count) {
            $line = $lines[$i];
            $trimmed = trim($line);

            // Skip blank lines
            if ($trimmed === '') {
                $i++;
                continue;
            }

            // Fenced code block ```
            if (strpos($trimmed, '```') === 0) {
                $codeLines = [];
                $i++;
                while ($i < $count && strpos(trim($lines[$i]), '```') !== 0) {
                    $codeLines[] = Security::e($lines[$i]);
                    $i++;
                }
                if ($i < $count) {
                    $i++; // consume closing ```
                }
                $html[] = '<pre class="my-5 p-4 rounded-xl bg-dark-900 border border-white/10 overflow-x-auto text-xs sm:text-sm text-amber-300 font-mono leading-relaxed"><code>'
                    . implode("\n", $codeLines)
                    . '</code></pre>';
                continue;
            }

            // Horizontal rule --- or ***
            if (preg_match('/^([-*_])\1{2,}$/', $trimmed)) {
                $html[] = '<hr class="my-6 border-white/10">';
                $i++;
                continue;
            }

            // Headings (## and ###, plus # mapped to h2 for hierarchy)
            if (preg_match('/^(#{1,3})\s+(.+)$/', $trimmed, $m)) {
                $level = strlen($m[1]);
                $inner = self::formatInline($m[2]);
                if ($level <= 2) {
                    $html[] = '<h2 class="font-condensed font-bold text-2xl sm:text-3xl text-white tracking-wide mt-8 mb-3">' . $inner . '</h2>';
                } else {
                    $html[] = '<h3 class="font-condensed font-bold text-xl text-amber-400 tracking-wide mt-6 mb-2">' . $inner . '</h3>';
                }
                $i++;
                continue;
            }

            // Blockquote >
            if (preg_match('/^>\s?(.*)$/', $trimmed)) {
                $quoteLines = [];
                while ($i < $count && preg_match('/^>\s?(.*)$/', trim($lines[$i]), $qm)) {
                    $quoteLines[] = self::formatInline($qm[1]);
                    $i++;
                }
                $html[] = '<blockquote class="my-5 pl-4 border-l-2 border-amber-500 bg-amber-500/5 py-3 pr-4 rounded-r-xl text-gray-300 italic text-sm sm:text-base leading-relaxed">'
                    . implode('<br>', $quoteLines)
                    . '</blockquote>';
                continue;
            }

            // Unordered list (- or *)
            if (preg_match('/^[-*]\s+(.+)$/', $trimmed)) {
                $items = [];
                while ($i < $count && preg_match('/^[-*]\s+(.+)$/', trim($lines[$i]), $lm)) {
                    $items[] = '<li class="leading-relaxed">' . self::formatInline($lm[1]) . '</li>';
                    $i++;
                }
                $html[] = '<ul class="my-4 pl-5 list-disc space-y-1.5 text-gray-300 text-sm sm:text-base marker:text-amber-500">'
                    . implode('', $items)
                    . '</ul>';
                continue;
            }

            // Ordered list (1. 2. ...)
            if (preg_match('/^\d+\.\s+(.+)$/', $trimmed)) {
                $items = [];
                while ($i < $count && preg_match('/^\d+\.\s+(.+)$/', trim($lines[$i]), $om)) {
                    $items[] = '<li class="leading-relaxed">' . self::formatInline($om[1]) . '</li>';
                    $i++;
                }
                $html[] = '<ol class="my-4 pl-5 list-decimal space-y-1.5 text-gray-300 text-sm sm:text-base marker:text-amber-500">'
                    . implode('', $items)
                    . '</ol>';
                continue;
            }

            // Paragraph (collect consecutive regular lines)
            $paraLines = [];
            while ($i < $count) {
                $cur = trim($lines[$i]);
                if (
                    $cur === ''
                    || strpos($cur, '```') === 0
                    || preg_match('/^([-*_])\1{2,}$/', $cur)
                    || preg_match('/^(#{1,3})\s+/', $cur)
                    || preg_match('/^>\s?/', $cur)
                    || preg_match('/^[-*]\s+/', $cur)
                    || preg_match('/^\d+\.\s+/', $cur)
                ) {
                    break;
                }
                $paraLines[] = self::formatInline($lines[$i]);
                $i++;
            }

            if (!empty($paraLines)) {
                $html[] = '<p class="my-4 text-gray-300 text-sm sm:text-base leading-relaxed">'
                    . implode('<br>', $paraLines)
                    . '</p>';
            }
        }

        return implode("\n", $html);
    }

    /**
     * Format inline syntax after HTML-escaping raw text.
     */
    private static function formatInline(string $line): string
    {
        $escaped = Security::e($line);

        // Protect inline code spans first so markers inside code are untouched
        $codePlaceholders = [];
        $escaped = preg_replace_callback('/`([^`]+)`/', function ($m) use (&$codePlaceholders) {
            $token = '%%CODE_' . count($codePlaceholders) . '%%';
            $codePlaceholders[$token] = '<code class="px-1.5 py-0.5 rounded bg-white/10 text-amber-300 text-xs sm:text-sm font-mono">' . $m[1] . '</code>';
            return $token;
        }, $escaped);

        // Safe links: [label](https://...) — only allow http:// or https://
        $escaped = preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/i', function ($m) {
            $label = $m[1];
            $url   = $m[2];
            return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer" class="text-amber-400 hover:text-amber-300 underline decoration-amber-500/40 underline-offset-2 transition-colors">' . $label . '</a>';
        }, $escaped);

        // Bold: **text**
        $escaped = preg_replace('/\*\*(.+?)\*\*/', '<strong class="font-semibold text-white">$1</strong>', $escaped);

        // Italic: *text* (avoid matching inside words with underscores)
        $escaped = preg_replace('/(?<!\*)\*(?!\s)(.+?)(?<!\s)\*(?!\*)/', '<em class="italic text-gray-200">$1</em>', $escaped);

        // Restore inline code spans
        if (!empty($codePlaceholders)) {
            $escaped = strtr($escaped, $codePlaceholders);
        }

        return $escaped;
    }

    /**
     * Strip markdown tokens to clean plain text (useful for excerpts & SEO).
     */
    public static function toPlainText(string $text): string
    {
        $plain = preg_replace('/```[\s\S]*?```/', ' ', $text);
        $plain = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '$1', $plain);
        $plain = preg_replace('/^[#>*\-\d.\s]+/m', '', $plain);
        $plain = str_replace(['**', '*', '`'], '', $plain);
        $plain = preg_replace('/\s+/', ' ', trim($plain));
        return $plain;
    }

    /**
     * Generate a short excerpt from markdown content.
     */
    public static function excerpt(string $text, int $maxLength = 160): string
    {
        $plain = self::toPlainText($text);
        if (mb_strlen($plain) <= $maxLength) {
            return $plain;
        }
        return rtrim(mb_substr($plain, 0, $maxLength - 3)) . '...';
    }

    /**
     * Estimate reading time in minutes (~200 words/minute).
     */
    public static function readingTime(string $text): int
    {
        $plain = self::toPlainText($text);
        $words = preg_split('/\s+/u', trim($plain), -1, PREG_SPLIT_NO_EMPTY);
        $count = is_array($words) ? count($words) : 0;
        return max(1, (int)ceil($count / 200));
    }

    /**
     * Resolve local or external media path cleanly across localhost & hosting.
     */
    public static function resolveMediaUrl(?string $path, string $base): string
    {
        if (empty($path)) {
            return '';
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        $clean = preg_replace('#^/NazoLinktree/#', '', $path);
        return rtrim($base, '/') . '/' . ltrim($clean, '/');
    }

    /**
     * Validate URL scheme for thumbnail inputs (allows empty, http, https, or local /Media/ or /Foto/).
     */
    public static function isSafeMediaUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '') {
            return true;
        }
        if (preg_match('#^https?://#i', $url)) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        }
        if (strpos($url, '/Media/') === 0 || strpos($url, '/Foto/') === 0) {
            return strpos($url, '..') === false;
        }
        return false;
    }
}
