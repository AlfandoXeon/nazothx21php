<?php
namespace App\Models;

use App\Core\Model;

class ArticleComment extends Model
{
    protected string $table = 'article_comments';

    /**
     * Get all approved comments for a specific article (newest first).
     */
    public function approvedByArticle(int $articleId): array
    {
        try {
            return $this->db->query(
                "SELECT * FROM article_comments
                 WHERE article_id = ? AND is_approved = 1
                 ORDER BY created_at DESC, id DESC",
                [$articleId]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Count approved comments for a specific article.
     */
    public function countApprovedByArticle(int $articleId): int
    {
        try {
            $row = $this->db->queryOne(
                "SELECT COUNT(*) AS total FROM article_comments WHERE article_id = ? AND is_approved = 1",
                [$articleId]
            );
            return (int)($row['total'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * List all article comments for admin moderation (with article title & slug).
     */
    public function allAdmin(?int $articleId = null): array
    {
        try {
            if ($articleId !== null && $articleId > 0) {
                return $this->db->query(
                    "SELECT ac.*, a.title AS article_title, a.slug AS article_slug
                     FROM article_comments ac
                     INNER JOIN articles a ON a.id = ac.article_id
                     WHERE ac.article_id = ?
                     ORDER BY ac.created_at DESC, ac.id DESC",
                    [$articleId]
                );
            }

            return $this->db->query(
                "SELECT ac.*, a.title AS article_title, a.slug AS article_slug
                 FROM article_comments ac
                 INNER JOIN articles a ON a.id = ac.article_id
                 ORDER BY ac.created_at DESC, ac.id DESC"
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Store a new article comment.
     */
    public function create(array $data): int
    {
        return $this->db->execute(
            "INSERT INTO article_comments (article_id, name, message, is_approved, ip_address)
             VALUES (?, ?, ?, 1, ?)",
            [
                (int)$data['article_id'],
                $data['name'],
                $data['message'],
                $data['ip'] ?? '',
            ]
        );
    }

    /**
     * Get the latest comment submitted from a specific IP (for anti-spam cooldown).
     */
    public function lastCommentByIp(string $ip): ?array
    {
        if (empty($ip)) {
            return null;
        }
        try {
            return $this->db->queryOne(
                "SELECT * FROM article_comments WHERE ip_address = ? ORDER BY created_at DESC LIMIT 1",
                [$ip]
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Toggle comment approval status.
     */
    public function toggle(int $id): void
    {
        $this->db->execute(
            "UPDATE article_comments SET is_approved = NOT is_approved WHERE id = ?",
            [$id]
        );
    }

    /**
     * Total count of article comments.
     */
    public function countTotal(): int
    {
        try {
            $row = $this->db->queryOne("SELECT COUNT(*) AS total FROM article_comments");
            return (int)($row['total'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
