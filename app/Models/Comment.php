<?php
namespace App\Models;

use App\Core\Model;

class Comment extends Model
{
    protected string $table = 'comments';

    public function allApproved(): array
    {
        return $this->db->query("SELECT * FROM comments WHERE is_approved = 1 ORDER BY created_at DESC");
    }

    public function latestApproved(int $limit = 5): array
    {
        $limit = max(1, $limit);
        return $this->db->query("SELECT * FROM comments WHERE is_approved = 1 ORDER BY created_at DESC LIMIT {$limit}");
    }

    public function countApproved(): int
    {
        $r = $this->db->queryOne("SELECT COUNT(*) as c FROM comments WHERE is_approved = 1");
        return (int)($r['c'] ?? 0);
    }

    public function allAdmin(): array
    {
        return $this->db->query("SELECT * FROM comments ORDER BY created_at DESC");
    }

    public function create(array $data): int
    {
        return $this->db->execute(
            "INSERT INTO comments (name, message, is_approved, ip_address) VALUES (?, ?, 1, ?)",
            [
                $data['name'],
                $data['message'],
                $data['ip'] ?? '',
            ]
        );
    }

    /**
     * Dapatkan komentar terakhir berdasarkan IP
     */
    public function lastCommentByIp(string $ip): ?array
    {
        if (empty($ip)) return null;
        return $this->db->queryOne(
            "SELECT * FROM comments WHERE ip_address = ? ORDER BY created_at DESC LIMIT 1",
            [$ip]
        );
    }

    public function toggle(int $id): void
    {
        $this->db->execute("UPDATE comments SET is_approved = NOT is_approved WHERE id = ?", [$id]);
    }

    public function countTotal(): int
    {
        $r = $this->db->queryOne("SELECT COUNT(*) as c FROM comments");
        return (int)($r['c'] ?? 0);
    }
}
