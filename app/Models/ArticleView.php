<?php
namespace App\Models;

use App\Core\Model;

class ArticleView extends Model
{
    protected string $table = 'article_views';

    /**
     * Record a unique reader hit for an article with 1-hour deduplication per IP.
     * Returns true if a new unique view was recorded, false otherwise.
     */
    public function record(int $articleId, string $ip): bool
    {
        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }

        try {
            $recent = $this->db->queryOne(
                "SELECT id FROM article_views
                 WHERE article_id = ? AND ip_address = ? AND viewed_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                 LIMIT 1",
                [$articleId, $ip]
            );

            if (!$recent) {
                $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
                $this->db->execute(
                    "INSERT INTO article_views (article_id, ip_address, user_agent) VALUES (?, ?, ?)",
                    [$articleId, $ip, $ua]
                );
                return true;
            }
        } catch (\Exception $e) {
            // Silently fail so article reading is never blocked
        }

        return false;
    }
}
