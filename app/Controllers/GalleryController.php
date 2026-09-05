<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Gallery;
use App\Models\Setting;
use App\Models\Profile;
use App\Models\SocialHeader;
use App\Models\VisitorLog;

class GalleryController extends Controller
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
        (new VisitorLog())->record($_SERVER['REMOTE_ADDR'] ?? '', '/gallery');

        $gallery = new Gallery();
        $profile = (new Profile())->get();
        $socials = (new SocialHeader())->allOrdered();

        $photos = $gallery->byType('image');
        $videos = $gallery->byType('video');

        // adEnabled needed by main layout for popup
        $adEnabled = ($settings['ad_enabled'] ?? '0') === '1';

        // SEO Meta
        $galleryTitle = !empty($settings['gallery_title']) ? $settings['gallery_title'] : 'Gallery';
        $siteTitle    = !empty($settings['seo_title']) ? $settings['seo_title'] : 'NAZO ナゾ';
        $seoTitle     = $galleryTitle . ' — ' . $siteTitle;
        $seoDesc      = !empty($settings['gallery_subtitle']) ? $settings['gallery_subtitle'] : ($settings['seo_description'] ?? '');
        $seoOgImage   = $settings['seo_og_image'] ?? '';

        $this->view('gallery.index', [
            'photos'     => $photos,
            'videos'     => $videos,
            'settings'   => $settings,
            'profile'    => $profile,
            'socials'    => $socials,
            'adEnabled'  => $adEnabled,
            'seoTitle'   => $seoTitle,
            'seoDesc'    => $seoDesc,
            'seoOgImage' => $seoOgImage,
        ]);
    }
}
