<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Security;
use App\Models\Card;
use App\Models\Gallery;
use App\Models\Comment;
use App\Models\SocialHeader;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\VisitorLog;
use App\Models\CardClick;
use App\Core\Database;

class AdminController extends Controller
{
    private string $base;
    private string $adminRoute;

    public function __construct()
    {
        $this->base       = rtrim($_ENV['APP_URL'] ?? '', '/');
        $this->adminRoute = $_ENV['ADMIN_ROUTE'] ?? 'adminadalahraja';
    }

    private function adminBase(): string
    {
        return $this->base . '/' . $this->adminRoute;
    }

    private function log(string $action, string $detail = ''): void
    {
        try {
            Database::getInstance()->execute(
                "INSERT INTO admin_logs (action, detail) VALUES (?, ?)",
                [$action, $detail]
            );
        } catch (\Exception $e) {}
    }

    // ─── Auth ───────────────────────────────────────────────────────────────

    public function login(array $params = []): void
    {
        if (Auth::check()) {
            $this->redirect($this->adminBase() . '/dashboard');
            return;
        }
        $this->view('admin.login', [
            'csrfToken' => Security::csrfToken(),
            'error'     => $this->getFlash('login_error'),
        ], 'admin_auth');
    }

    public function processLogin(array $params = []): void
    {
        Security::requireCsrf();

        if (!Security::rateLimit('admin_login', 5, 300)) {
            $this->flash('login_error', 'Terlalu banyak percobaan. Tunggu 5 menit.');
            $this->redirect($this->adminBase());
            return;
        }

        $password = $_POST['password'] ?? '';
        if (Auth::attempt($password)) {
            $this->log('LOGIN', 'Admin login berhasil');
            $this->redirect($this->adminBase() . '/dashboard');
        } else {
            $this->flash('login_error', 'Password salah.');
            $this->redirect($this->adminBase());
        }
    }

    public function logout(array $params = []): void
    {
        Auth::logout();
        $this->redirect($this->adminBase());
    }

    // ─── Dashboard ──────────────────────────────────────────────────────────

    public function dashboard(array $params = []): void
    {
        Auth::requireAuth();
        $card       = new Card();
        $gallery    = new Gallery();
        $comment    = new Comment();
        $visitorLog = new VisitorLog();
        $cardClick  = new CardClick();

        $db   = Database::getInstance();
        $logs = $db->query("SELECT * FROM admin_logs ORDER BY created_at DESC LIMIT 10");

        $this->view('admin.dashboard', [
            'totalCards'    => $card->count(),
            'totalPhotos'   => $gallery->countImages(),
            'totalVideos'   => $gallery->countVideos(),
            'totalComments' => $comment->countTotal(),
            'totalVisitors' => $visitorLog->totalCount(),
            'todayVisitors' => $visitorLog->todayCount(),
            'visitor7Days'  => $visitorLog->last7Days(),
            'totalClicks'   => $cardClick->totalClicks(),
            'topCards'      => $cardClick->topCards(5),
            'logs'          => $logs,
            'csrfToken'     => Security::csrfToken(),
            'adminBase'     => $this->adminBase(),
        ], 'admin');
    }

    // ─── Cards ──────────────────────────────────────────────────────────────

    public function cards(array $params = []): void
    {
        Auth::requireAuth();
        $this->view('admin.cards', [
            'cards'       => (new Card())->allOrdered(),
            'clickCounts' => (new CardClick())->countsMap(),
            'csrfToken'   => Security::csrfToken(),
            'flash'       => $this->getFlash('cards'),
            'adminBase'   => $this->adminBase(),
        ], 'admin');
    }

    public function storeCard(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();

        (new Card())->create([
            'title'         => $_POST['title'] ?? '',
            'subtitle'      => $_POST['subtitle'] ?? '',
            'url'           => $_POST['url'] ?? '',
            'image_url'     => $_POST['image_url'] ?? '',
            'is_special'    => isset($_POST['is_special']) ? 1 : 0,
            'special_title' => $_POST['special_title'] ?? '',
            'sort_order'    => (int)($_POST['sort_order'] ?? 0),
            'is_active'     => 1,
        ]);

        $this->log('CARD_CREATE', $_POST['title'] ?? '');
        $this->flash('cards', 'success:Card berhasil ditambahkan.');
        $this->redirect($this->adminBase() . '/cards');
    }

    public function updateCard(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        (new Card())->update($id, [
            'title'         => $_POST['title'] ?? '',
            'subtitle'      => $_POST['subtitle'] ?? '',
            'url'           => $_POST['url'] ?? '',
            'image_url'     => $_POST['image_url'] ?? '',
            'is_special'    => isset($_POST['is_special']) ? 1 : 0,
            'special_title' => $_POST['special_title'] ?? '',
            'sort_order'    => (int)($_POST['sort_order'] ?? 0),
            'is_active'     => isset($_POST['is_active']) ? 1 : 0,
        ]);

        $this->log('CARD_UPDATE', "ID:{$id}");
        $this->flash('cards', 'success:Card berhasil diperbarui.');
        $this->redirect($this->adminBase() . '/cards');
    }

    public function deleteCard(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();
        $id = (int)($_POST['id'] ?? 0);
        (new Card())->delete($id);
        $this->log('CARD_DELETE', "ID:{$id}");
        $this->json(['success' => true]);
    }

    public function toggleCard(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();
        $id = (int)($_POST['id'] ?? 0);
        (new Card())->toggle($id);
        $this->json(['success' => true]);
    }

    public function reorderCards(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        if (is_array($ids)) {
            (new Card())->reorder($ids);
        }
        $this->json(['success' => true]);
    }

    // ─── Gallery ────────────────────────────────────────────────────────────

    public function gallery(array $params = []): void
    {
        Auth::requireAuth();
        $this->view('admin.gallery', [
            'items'     => (new Gallery())->allOrdered(),
            'csrfToken' => Security::csrfToken(),
            'flash'     => $this->getFlash('gallery'),
            'adminBase' => $this->adminBase(),
        ], 'admin');
    }

    public function uploadGallery(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();

        $file    = $_FILES['file'] ?? null;
        $caption = trim($_POST['caption'] ?? '');

        if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $this->flash('gallery', 'error:Pilih file terlebih dahulu.');
            $this->redirect($this->adminBase() . '/gallery');
            return;
        }

        // Detect image or video
        $imageMimes = ['image/jpeg','image/png','image/gif','image/webp'];
        $videoMimes = ['video/mp4','video/webm','video/ogg','video/quicktime'];

        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mime     = $finfo->file($file['tmp_name']);
        $fileType = in_array($mime, $imageMimes) ? 'image' : (in_array($mime, $videoMimes) ? 'video' : null);

        if (!$fileType) {
            $this->flash('gallery', 'error:Tipe file tidak didukung.');
            $this->redirect($this->adminBase() . '/gallery');
            return;
        }

        $destDir = ROOT_PATH . '/Media/' . ($fileType === 'image' ? 'gallery' : 'videos');

        try {
            $filename = Security::handleUpload($file, $destDir, array_merge($imageMimes, $videoMimes));
            $filePath = $this->base . '/Media/' . ($fileType === 'image' ? 'gallery' : 'videos') . '/' . $filename;

            (new Gallery())->create([
                'file_path' => $filePath,
                'file_type' => $fileType,
                'caption'   => $caption,
                'sort_order'=> 0,
            ]);

            $this->log('GALLERY_UPLOAD', $filename);
            $this->flash('gallery', 'success:File berhasil diupload.');
        } catch (\Exception $e) {
            $this->flash('gallery', 'error:' . $e->getMessage());
        }

        $this->redirect($this->adminBase() . '/gallery');
    }

    public function deleteGallery(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();

        $id   = (int)($_POST['id'] ?? 0);
        $item = (new Gallery())->find($id);

        if ($item) {
            $rel = parse_url($item['file_path'], PHP_URL_PATH) ?? '';
            $rel = preg_replace('#^/NazoLinktree#', '', $rel);
            $path = ROOT_PATH . '/' . ltrim($rel, '/');
            if (file_exists($path)) @unlink($path);
            (new Gallery())->delete($id);
            $this->log('GALLERY_DELETE', "ID:{$id}");
        }

        $this->json(['success' => true]);
    }

    // ─── Comments ───────────────────────────────────────────────────────────

    public function comments(array $params = []): void
    {
        Auth::requireAuth();
        $this->view('admin.comments', [
            'comments'  => (new Comment())->allAdmin(),
            'csrfToken' => Security::csrfToken(),
            'adminBase' => $this->adminBase(),
        ], 'admin');
    }

    public function deleteComment(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();
        $id = (int)($_POST['id'] ?? 0);
        (new Comment())->delete($id);
        $this->log('COMMENT_DELETE', "ID:{$id}");
        $this->json(['success' => true]);
    }

    public function toggleComment(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();
        $id = (int)($_POST['id'] ?? 0);
        (new Comment())->toggle($id);
        $this->json(['success' => true]);
    }

    // ─── Social Header ──────────────────────────────────────────────────────

    public function social(array $params = []): void
    {
        Auth::requireAuth();
        $this->view('admin.social', [
            'socials'   => (new SocialHeader())->allOrdered(),
            'csrfToken' => Security::csrfToken(),
            'flash'     => $this->getFlash('social'),
            'adminBase' => $this->adminBase(),
        ], 'admin');
    }

    public function storeSocial(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();
        (new SocialHeader())->create([
            'name'      => $_POST['name'] ?? '',
            'url'       => $_POST['url'] ?? '',
            'icon_slug' => $_POST['icon_slug'] ?? '',
            'sort_order'=> (int)($_POST['sort_order'] ?? 0),
        ]);
        $this->flash('social', 'success:Social link berhasil ditambahkan.');
        $this->redirect($this->adminBase() . '/social');
    }

    public function updateSocial(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();
        $id = (int)($_POST['id'] ?? 0);
        (new SocialHeader())->update($id, [
            'name'      => $_POST['name'] ?? '',
            'url'       => $_POST['url'] ?? '',
            'icon_slug' => $_POST['icon_slug'] ?? '',
            'sort_order'=> (int)($_POST['sort_order'] ?? 0),
        ]);
        $this->flash('social', 'success:Social link berhasil diperbarui.');
        $this->redirect($this->adminBase() . '/social');
    }

    public function deleteSocial(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();
        $id = (int)($_POST['id'] ?? 0);
        (new SocialHeader())->delete($id);
        $this->json(['success' => true]);
    }

    // ─── Profile ────────────────────────────────────────────────────────────

    public function profile(array $params = []): void
    {
        Auth::requireAuth();
        $profile = new Profile();
        $profile->ensureExists();
        $this->view('admin.profile', [
            'profile'   => $profile->get(),
            'csrfToken' => Security::csrfToken(),
            'flash'     => $this->getFlash('profile'),
            'adminBase' => $this->adminBase(),
        ], 'admin');
    }

    public function updateProfile(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();

        $data = [
            'name' => $_POST['name'] ?? '',
            'role' => $_POST['role'] ?? '',
            'bio'  => $_POST['bio'] ?? '',
        ];

        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $destDir = ROOT_PATH . '/Media/avatars';
            try {
                $filename           = Security::handleUpload($_FILES['avatar'], $destDir, ['image/jpeg','image/png','image/webp','image/gif']);
                $data['avatar_url'] = $this->base . '/Media/avatars/' . $filename;
            } catch (\Exception $e) {
                $this->flash('profile', 'error:' . $e->getMessage());
                $this->redirect($this->adminBase() . '/profile');
                return;
            }
        }

        (new Profile())->update($data);
        $this->log('PROFILE_UPDATE');
        $this->flash('profile', 'success:Profil berhasil diperbarui.');
        $this->redirect($this->adminBase() . '/profile');
    }

    // ─── Settings ───────────────────────────────────────────────────────────

    public function settings(array $params = []): void
    {
        Auth::requireAuth();
        $settings = (new Setting())->allAsMap();

        // Ambil ringkasan status tabel database
        $db = Database::getInstance();
        $tableStats = [];
        $totalSizeKb = 0;
        $totalRows = 0;

        try {
            $dbName = $_ENV['DB_NAME'] ?? 'nazo_linktree';
            $rows = $db->query("
                SELECT table_name AS `table`,
                       table_rows AS `rows`,
                       ROUND((data_length + index_length) / 1024, 2) AS `size_kb`
                FROM information_schema.tables
                WHERE table_schema = ?
                ORDER BY (data_length + index_length) DESC
            ", [$dbName]);

            foreach ($rows as $r) {
                $tableStats[] = $r;
                $totalSizeKb += (float)$r['size_kb'];
                $totalRows   += (int)$r['rows'];
            }
        } catch (\Exception $e) {
            $tableStats = [];
        }

        $this->view('admin.settings', [
            'settings'    => $settings,
            'csrfToken'   => Security::csrfToken(),
            'flash'       => $this->getFlash('settings'),
            'adminBase'   => $this->adminBase(),
            'tableStats'  => $tableStats,
            'totalSizeKb' => $totalSizeKb,
            'totalRows'   => $totalRows,
        ], 'admin');
    }

    /**
     * Download Backup Database SQL
     */
    public function backupDatabase(array $params = []): void
    {
        Auth::requireAuth();

        $db = Database::getInstance();
        $pdo = $db->getPdo();
        $dbName = $_ENV['DB_NAME'] ?? 'nazo_linktree';

        $tables = [];
        $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE'");
        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $sqlDump = "-- NAZO Linktree Database Backup\n";
        $sqlDump .= "-- Generated on " . date('Y-m-d H:i:s') . "\n";
        $sqlDump .= "-- Database: `{$dbName}`\n\n";
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sqlDump .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n";

        foreach ($tables as $table) {
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $createStmt->fetch(\PDO::FETCH_NUM);
            $sqlDump .= "-- Table structure for `{$table}`\n";
            $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sqlDump .= $createRow[1] . ";\n\n";

            $rowsStmt = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $rowsStmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $sqlDump .= "-- Dumping data for `{$table}`\n";
                foreach ($rows as $r) {
                    $cols = array_map(function($c) { return "`" . str_replace("`", "``", $c) . "`"; }, array_keys($r));
                    $vals = array_map(function($v) use ($pdo) {
                        return ($v === null) ? "NULL" : $pdo->quote($v);
                    }, array_values($r));

                    $sqlDump .= "INSERT INTO `{$table}` (" . implode(", ", $cols) . ") VALUES (" . implode(", ", $vals) . ");\n";
                }
                $sqlDump .= "\n";
            }
        }

        $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $this->log('DB_BACKUP', 'Download backup sql database');

        $filename = 'nazo_backup_' . date('Ymd_His') . '.sql';
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($sqlDump));
        echo $sqlDump;
        exit;
    }

    /**
     * Optimasi & Defragmentasi Tabel Database
     */
    public function optimizeDatabase(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();

        $db = Database::getInstance();
        $pdo = $db->getPdo();

        try {
            $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE'");
            $tables = [];
            while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                $tables[] = "`" . $row[0] . "`";
            }

            if (!empty($tables)) {
                $tableList = implode(", ", $tables);
                $pdo->query("OPTIMIZE TABLE {$tableList}");
            }

            $this->log('DB_OPTIMIZE', 'Optimasi tabel database berhasil');
            $this->flash('settings', 'success:Optimasi dan defragmentasi tabel database berhasil dilakukan.');
        } catch (\Exception $e) {
            $this->flash('settings', 'error:Gagal melakukan optimasi: ' . $e->getMessage());
        }

        $this->redirect($this->adminBase() . '/settings');
    }

    /**
     * Bersihkan Data Log / Klik
     */
    public function cleanupDatabase(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();

        $db = Database::getInstance();
        $type = $_POST['cleanup_type'] ?? '';

        try {
            if ($type === 'visitor_logs_30d') {
                $count = $db->execute("DELETE FROM visitor_logs WHERE visited_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
                $this->log('DB_CLEANUP', "Pembersihan log pengunjung >30 hari ({$count} baris)");
                $this->flash('settings', "success:Berhasil membersihkan {$count} data log pengunjung lebih dari 30 hari.");
            } elseif ($type === 'visitor_logs_all') {
                $db->execute("TRUNCATE TABLE visitor_logs");
                $this->log('DB_CLEANUP', "Truncate semua log pengunjung");
                $this->flash('settings', 'success:Semua data log pengunjung telah dikosongkan.');
            } elseif ($type === 'admin_logs_all') {
                $db->execute("TRUNCATE TABLE admin_logs");
                $this->log('DB_CLEANUP', "Truncate semua log aktivitas admin");
                $this->flash('settings', 'success:Semua riwayat aktivitas admin berhasil dibersihkan.');
            } elseif ($type === 'clicks_all') {
                $db->execute("TRUNCATE TABLE card_clicks");
                $this->log('DB_CLEANUP', "Truncate log klik card");
                $this->flash('settings', 'success:Semua data riwayat klik kartu berhasil direset.');
            } else {
                $this->flash('settings', 'error:Opsi pembersihan tidak valid.');
            }
        } catch (\Exception $e) {
            $this->flash('settings', 'error:Gagal membersihkan data: ' . $e->getMessage());
        }

        $this->redirect($this->adminBase() . '/settings');
    }

    public function updateSettings(array $params = []): void
    {
        Auth::requireAuth();
        Security::requireCsrf();

        // Regular text fields
        $textFields = [
            'footer_text','gallery_title','gallery_subtitle',
            'ad_image_url','ad_title','ad_description',
            'ad_target_url','ad_button_text',
            'maintenance_countdown', 'seo_title', 'seo_description', 'seo_og_image',
        ];

        // Checkbox fields — must explicitly save '0' when unchecked
        // because unchecked checkboxes are absent from $_POST entirely
        $checkboxFields = ['ad_enabled', 'maintenance_mode'];

        $setting = new Setting();

        foreach ($textFields as $key) {
            if (isset($_POST[$key])) {
                $setting->set($key, $_POST[$key]);
            }
        }

        foreach ($checkboxFields as $key) {
            // Save '1' if checked, '0' if not in POST (unchecked)
            $setting->set($key, isset($_POST[$key]) ? '1' : '0');
        }

        $this->log('SETTINGS_UPDATE');
        $this->flash('settings', 'success:Pengaturan berhasil disimpan.');
        $this->redirect($this->adminBase() . '/settings');
    }

    /**
     * Card redirect with click tracking
     */
    public function redirectCard(array $params = []): void
    {
        $id   = (int)($params['id'] ?? 0);
        $card = (new Card())->find($id);

        if (!$card || empty($card['url'])) {
            $this->redirect($this->base . '/');
            return;
        }

        (new CardClick())->record($id, $_SERVER['REMOTE_ADDR'] ?? '');
        header("Location: " . $card['url']);
        exit;
    }
}
