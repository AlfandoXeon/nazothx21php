<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
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

        $profile      = (new Profile())->get();
        $cards        = (new Card())->allActive();
        $socials      = (new SocialHeader())->allOrdered();
        $comments     = (new Comment())->allApproved();
        $visitorCount = $visitorLog->totalCount();

        // Ad popup
        $adEnabled = ($settings['ad_enabled'] ?? '0') === '1';

        // SEO Meta
        $seoTitle   = !empty($settings['seo_title']) ? $settings['seo_title'] : 'NAZO ナゾ — Links';
        $seoDesc    = !empty($settings['seo_description']) ? $settings['seo_description'] : 'NAZO ナゾ — MLBB Content Creator. Temukan semua media sosial dan konten Nazo di sini.';
        $seoOgImage = $settings['seo_og_image'] ?? '';

        $this->view('home.index', [
            'profile'      => $profile,
            'cards'        => $cards,
            'socials'      => $socials,
            'comments'     => $comments,
            'settings'     => $settings,
            'adEnabled'    => $adEnabled,
            'visitorCount' => $visitorCount,
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

        if (!Security::rateLimit('comment_' . ($_SERVER['REMOTE_ADDR'] ?? ''), 3, 120)) {
            $this->flash('comment', 'error:Terlalu banyak komentar. Coba lagi sebentar lagi.');
            $this->redirect($base . '/');
            return;
        }

        $name    = trim($_POST['name'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($message)) {
            $this->flash('comment', 'error:Nama dan pesan tidak boleh kosong.');
            $this->redirect($base . '/');
            return;
        }

        if (mb_strlen($name) > 100 || mb_strlen($message) > 500) {
            $this->flash('comment', 'error:Input terlalu panjang.');
            $this->redirect($base . '/');
            return;
        }

        (new Comment())->create([
            'name'    => $name,
            'message' => $message,
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
        ]);

        $this->flash('comment', 'success:Komentar berhasil dikirim. Terima kasih!');
        $this->redirect($base . '/#komentar');
    }
}
