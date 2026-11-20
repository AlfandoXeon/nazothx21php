<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Markdown;
use App\Core\Security;
use App\Models\Article;
use App\Models\ArticleComment;
use App\Models\ArticleView;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\SocialHeader;
use App\Models\VisitorLog;

class ArticleController extends Controller
{
    private function checkMaintenance(array $settings): bool
    {
        if (($settings['maintenance_mode'] ?? '0') === '1') {
            $profile = (new Profile())->get();
            $socials = (new SocialHeader())->allOrdered();
            $this->view('maintenance.index', [
                'profile'  => $profile,
                'socials'  => $socials,
                'settings' => $settings,
            ], 'none');
            return true;
        }
        return false;
    }

    /**
     * Halaman daftar artikel publik (/artikel)
     */
    public function index(array $params = []): void
    {
        $settings = (new Setting())->allAsMap();
        if ($this->checkMaintenance($settings)) {
            return;
        }

        // Record visitor log
        (new VisitorLog())->record(Security::getClientIp(), '/artikel');

        $search   = trim((string)($_GET['q'] ?? ''));
        if (mb_strlen($search) > 100) {
            $search = mb_substr($search, 0, 100);
        }
        $category = trim((string)($_GET['kategori'] ?? ''));
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = 6;
        $offset   = ($page - 1) * $perPage;

        $articleModel = new Article();
        $totalItems   = $articleModel->countPublished($search, $category);
        $totalPages   = max(1, (int)ceil($totalItems / $perPage));
        if ($page > $totalPages) {
            $page   = $totalPages;
            $offset = ($page - 1) * $perPage;
        }

        $articles   = $articleModel->allPublished($search, $category, $perPage, $offset);
        $categories = $articleModel->publishedCategories();
        $profile    = (new Profile())->get();
        $adEnabled  = ($settings['ad_enabled'] ?? '0') === '1';

        $articleTitle = !empty($settings['article_title']) ? $settings['article_title'] : 'Artikel By Nazo';
        $siteTitle    = !empty($settings['seo_title']) ? $settings['seo_title'] : 'NAZO ナゾ';
        $seoTitle     = $articleTitle . ' — ' . $siteTitle;
        $seoDesc      = !empty($settings['article_subtitle'])
            ? $settings['article_subtitle']
            : 'Kumpulan artikel, tips, dan catatan terbaru dari ' . ($profile['name'] ?? 'NAZO') . '.';
        $seoOgImage   = $settings['seo_og_image'] ?? '';

        $this->view('articles.index', [
            'articles'     => $articles,
            'categories'   => $categories,
            'search'       => $search,
            'activeCat'    => $category,
            'currentPage'  => $page,
            'totalPages'   => $totalPages,
            'totalItems'   => $totalItems,
            'profile'      => $profile,
            'settings'     => $settings,
            'adEnabled'    => $adEnabled,
            'seoTitle'     => $seoTitle,
            'seoDesc'      => $seoDesc,
            'seoOgImage'   => $seoOgImage,
        ]);
    }

    /**
     * Halaman detail baca artikel (/artikel/:slug)
     */
    public function show(array $params = []): void
    {
        $settings = (new Setting())->allAsMap();
        if ($this->checkMaintenance($settings)) {
            return;
        }

        $slug         = trim((string)($params['slug'] ?? ''));
        $articleModel = new Article();
        $article      = $slug !== '' ? $articleModel->findPublishedBySlug($slug) : null;

        if (!$article) {
            $this->renderNotFound();
            return;
        }

        $ip = Security::getClientIp();

        // Record site visitor log & deduplicated article reader view
        (new VisitorLog())->record($ip, '/artikel/' . $article['slug']);
        $isNewReader = (new ArticleView())->record((int)$article['id'], $ip);
        if ($isNewReader) {
            $articleModel->incrementViews((int)$article['id']);
            $article['views_count'] = ((int)$article['views_count']) + 1;
        }

        $profile       = (new Profile())->get();
        $comments      = (new ArticleComment())->approvedByArticle((int)$article['id']);
        $otherArticles = $articleModel->recentOtherPublished((int)$article['id'], 3);
        $adEnabled     = ($settings['ad_enabled'] ?? '0') === '1';

        $base       = rtrim($_ENV['APP_URL'] ?? '', '/');
        $siteTitle  = !empty($settings['seo_title']) ? $settings['seo_title'] : 'NAZO ナゾ';
        $seoTitle   = $article['title'] . ' — ' . $siteTitle;
        $seoDesc    = !empty($article['excerpt'])
            ? $article['excerpt']
            : Markdown::excerpt($article['content'], 155);
        $seoOgImage = !empty($article['thumbnail_url'])
            ? Markdown::resolveMediaUrl($article['thumbnail_url'], $base)
            : ($settings['seo_og_image'] ?? '');

        $this->view('articles.show', [
            'article'       => $article,
            'renderedBody'  => Markdown::toHtml($article['content']),
            'readingTime'   => Markdown::readingTime($article['content']),
            'comments'      => $comments,
            'otherArticles' => $otherArticles,
            'profile'       => $profile,
            'settings'      => $settings,
            'adEnabled'     => $adEnabled,
            'seoTitle'      => $seoTitle,
            'seoDesc'       => $seoDesc,
            'seoOgImage'    => $seoOgImage,
            'flash'         => $this->getFlash('article_comment'),
            'csrfToken'     => Security::csrfToken(),
        ]);
    }

    /**
     * Simpan komentar artikel (/artikel/:slug/komentar)
     */
    public function storeComment(array $params = []): void
    {
        Security::requireCsrf();

        $base         = rtrim($_ENV['APP_URL'] ?? '', '/');
        $slug         = trim((string)($params['slug'] ?? ''));
        $articleModel = new Article();
        $article      = $slug !== '' ? $articleModel->findPublishedBySlug($slug) : null;

        if (!$article) {
            $this->redirect($base . '/artikel');
            return;
        }

        $redirectUrl = $base . '/artikel/' . rawurlencode($article['slug']) . '#komentar';
        $ip          = Security::getClientIp();

        // 1. Cooldown berbasis IP (2 menit = 120 detik), sama seperti komentar beranda
        $commentModel    = new ArticleComment();
        $cooldownSeconds = 120;
        $lastComment     = $commentModel->lastCommentByIp($ip);

        if ($lastComment && !empty($lastComment['created_at'])) {
            $lastTime = strtotime($lastComment['created_at']);
            $diff     = time() - $lastTime;
            if ($diff < $cooldownSeconds) {
                $remaining        = $cooldownSeconds - $diff;
                $remainingMinutes = ceil($remaining / 60);
                $this->flash('article_comment', "error:Mohon tunggu sekitar {$remainingMinutes} menit ({$remaining} detik) sebelum dapat mengirim komentar lagi.");
                $this->redirect($redirectUrl);
                return;
            }
        }

        // 2. Rate limiting cadangan berbasis session
        if (!Security::rateLimit('article_comment_' . $ip, 2, 120)) {
            $this->flash('article_comment', 'error:Terlalu banyak permintaan. Mohon tunggu 2 menit sebelum mencoba lagi.');
            $this->redirect($redirectUrl);
            return;
        }

        $name    = trim($_POST['name'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($name === '' || $message === '') {
            $this->flash('article_comment', 'error:Nama dan pesan tidak boleh kosong.');
            $this->redirect($redirectUrl);
            return;
        }

        if (mb_strlen($name) > 100) {
            $this->flash('article_comment', 'error:Nama terlalu panjang (maksimal 100 karakter).');
            $this->redirect($redirectUrl);
            return;
        }

        if (mb_strlen($message) > 1000) {
            $this->flash('article_comment', 'error:Komentar terlalu panjang. Maksimal 1000 karakter dalam 1 kali kirim.');
            $this->redirect($redirectUrl);
            return;
        }

        $commentModel->create([
            'article_id' => (int)$article['id'],
            'name'       => $name,
            'message'    => $message,
            'ip'         => $ip,
        ]);

        $this->flash('article_comment', 'success:Komentar berhasil dikirim. Terima kasih!');
        $this->redirect($redirectUrl);
    }

    private function renderNotFound(): void
    {
        http_response_code(404);
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        echo "<!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>404 — Artikel Tidak Ditemukan</title>
            <link rel='preconnect' href='https://fonts.googleapis.com'>
            <link href='https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap' rel='stylesheet'>
            <style>
                *{margin:0;padding:0;box-sizing:border-box}
                body{background:#0a0a0a;color:#fff;font-family:'Inter',sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;text-align:center;padding:1rem}
                h1{font-size:5rem;font-weight:600;color:#f59e0b;line-height:1}
                p{color:#666;margin-top:1rem;font-size:.95rem}
                a{color:#f59e0b;text-decoration:none;margin-top:2rem;display:inline-block;border:1px solid #f59e0b;padding:.55rem 1.5rem;border-radius:8px;font-size:.875rem;transition:all .2s}
                a:hover{background:#f59e0b;color:#000}
            </style>
        </head>
        <body>
            <div>
                <h1>404</h1>
                <p>Artikel tidak ditemukan atau belum dipublikasikan.</p>
                <a href='" . htmlspecialchars($base . '/artikel') . "'>Kembali ke Daftar Artikel</a>
            </div>
        </body>
        </html>";
    }
}
