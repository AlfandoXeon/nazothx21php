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
