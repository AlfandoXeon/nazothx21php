<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Models\Article;
use App\Models\Profile;
use App\Models\Card;
use App\Models\SocialHeader;
use App\Models\Comment;
use App\Models\Setting;
use App\Models\VisitorLog;
use App\Models\CardClick;

class HomeController extends Controller
{
    public function index(array $params = []): void
    {
        $settings = (new Setting())->allAsMap();

        // 1. Maintenance Mode Check
        if (($settings['maintenance_mode'] ?? '0') === '1') {
            $profile = (new Profile())->get();
            $socials = (new SocialHeader())->allOrdered();
            $this->view('maintenance.index', [
                'profile'  => $profile,
                'socials'  => $socials,
                'settings' => $settings,
            ], 'none');
            return;
        }

        // 2. Record Visitor Log
        $visitorLog = new VisitorLog();
        $visitorLog->record($_SERVER['REMOTE_ADDR'] ?? '', '/');

        $profile        = (new Profile())->get();
        $cards          = (new Card())->allActive();
        $socials        = (new SocialHeader())->allOrdered();
        $articleModel   = new Article();
        $latestArticles = $articleModel->allPublished(null, null, 3, 0);
        $totalArticles  = $articleModel->countPublishedTotal();
        $commentModel   = new Comment();
        $comments       = $commentModel->latestApproved(4);
        $totalComments  = $commentModel->countApproved();
        $visitorCount   = $visitorLog->totalCount();

        // Ad popup
        $adEnabled = ($settings['ad_enabled'] ?? '0') === '1';

        // SEO Meta
        $seoTitle   = !empty($settings['seo_title']) ? $settings['seo_title'] : 'NAZO ナゾ — Links';
        $seoDesc    = !empty($settings['seo_description']) ? $settings['seo_description'] : 'NAZO ナゾ — MLBB Content Creator. Temukan semua media sosial dan konten Nazo di sini.';
        $seoOgImage = $settings['seo_og_image'] ?? '';

        $this->view('home.index', [
            'profile'        => $profile,
            'cards'          => $cards,
            'socials'        => $socials,
            'latestArticles' => $latestArticles,
            'totalArticles'  => $totalArticles,
            'comments'       => $comments,
            'totalComments'  => $totalComments,
            'settings'       => $settings,
            'adEnabled'      => $adEnabled,
            'visitorCount'   => $visitorCount,
            'seoTitle'       => $seoTitle,
            'seoDesc'        => $seoDesc,
            'seoOgImage'     => $seoOgImage,
            'flash'          => $this->getFlash('comment'),
            'csrfToken'      => Security::csrfToken(),
        ]);
    }

    /**
     * Halaman Tab Komentar Lengkap
     */
    public function comments(array $params = []): void
    {
        $settings = (new Setting())->allAsMap();

        if (($settings['maintenance_mode'] ?? '0') === '1') {
            $profile = (new Profile())->get();
            $socials = (new SocialHeader())->allOrdered();
            $this->view('maintenance.index', [
                'profile'  => $profile,
                'socials'  => $socials,
                'settings' => $settings,
            ], 'none');
            return;
        }

        // Record Visitor Log
        $visitorLog = new VisitorLog();
        $visitorLog->record($_SERVER['REMOTE_ADDR'] ?? '', '/komentar');

        $profile       = (new Profile())->get();
        $comments      = (new Comment())->allApproved();
        $adEnabled     = ($settings['ad_enabled'] ?? '0') === '1';

        $siteTitle  = !empty($settings['seo_title']) ? $settings['seo_title'] : 'NAZO ナゾ';
        $seoTitle   = 'Komentar — ' . $siteTitle;
        $seoDesc    = 'Semua komentar dan pesan untuk ' . ($profile['name'] ?? 'NAZO');
        $seoOgImage = $settings['seo_og_image'] ?? '';

        $this->view('comments.index', [
            'profile'      => $profile,
            'comments'     => $comments,
            'settings'     => $settings,
            'adEnabled'    => $adEnabled,
            'seoTitle'     => $seoTitle,
            'seoDesc'      => $seoDesc,
            'seoOgImage'   => $seoOgImage,
            'flash'        => $this->getFlash('comment'),
            'csrfToken'    => Security::csrfToken(),
        ]);
    }

    /**
     * Track card click and redirect to destination URL.
     */
    public function redirectCard(array $params = []): void
    {
        $id   = (int)($params['id'] ?? 0);
        $card = (new Card())->find($id);
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');

        if (!$card || empty($card['url'])) {
            $this->redirect($base . '/');
            return;
        }

        // Record click
        (new CardClick())->record($id, $_SERVER['REMOTE_ADDR'] ?? '');

        // Redirect to external target
        header("Location: " . $card['url']);
        exit;
    }

    public function storeComment(array $params = []): void
    {
        Security::requireCsrf();

        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        $redirectUrl = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : ($base . '/#komentar');
        $ip = Security::getClientIp();

        // 1. Cek jeda waktu (cooldown) berbasis IP (2 menit = 120 detik)
        $commentModel = new Comment();
        $cooldownSeconds = 120; // 2 menit
        $lastComment = $commentModel->lastCommentByIp($ip);

        if ($lastComment && !empty($lastComment['created_at'])) {
            $lastTime = strtotime($lastComment['created_at']);
            $diff = time() - $lastTime;
            if ($diff < $cooldownSeconds) {
                $remaining = $cooldownSeconds - $diff;
                $remainingMinutes = ceil($remaining / 60);
                $this->flash('comment', "error:Mohon tunggu sekitar {$remainingMinutes} menit ({$remaining} detik) sebelum dapat mengirim komentar lagi.");
                $this->redirect($redirectUrl);
                return;
            }
        }

        // 2. Rate Limiting cadangan berbasis session
        if (!Security::rateLimit('comment_' . $ip, 2, 120)) {
            $this->flash('comment', 'error:Terlalu banyak permintaan. Mohon tunggu 2 menit sebelum mencoba lagi.');
            $this->redirect($redirectUrl);
            return;
        }

        $name    = trim($_POST['name'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($message)) {
            $this->flash('comment', 'error:Nama dan pesan tidak boleh kosong.');
            $this->redirect($redirectUrl);
            return;
        }

        if (mb_strlen($name) > 100) {
            $this->flash('comment', 'error:Nama terlalu panjang (maksimal 100 karakter).');
            $this->redirect($redirectUrl);
            return;
        }

        // Validasi komentar tidak boleh lebih dari 1000 karakter
        if (mb_strlen($message) > 1000) {
            $this->flash('comment', 'error:Komentar terlalu panjang. Maksimal 1000 karakter dalam 1 kali kirim.');
            $this->redirect($redirectUrl);
            return;
        }

        $commentModel->create([
            'name'    => $name,
            'message' => $message,
            'ip'      => $ip,
        ]);

        $this->flash('comment', 'success:Komentar berhasil dikirim. Terima kasih!');
        $this->redirect($redirectUrl);
    }
}
