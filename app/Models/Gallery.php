<?php
namespace App\Models;

use App\Core\Model;

class Gallery extends Model
{
    protected string $table = 'gallery';

    public function allOrdered(): array
    {
        return $this->db->query("SELECT * FROM gallery ORDER BY sort_order ASC, id DESC");
    }

    public function byType(string $type): array
    {
        return $this->db->query(
            "SELECT * FROM gallery WHERE file_type = ? ORDER BY sort_order ASC, id DESC",
            [$type]
        );
    }

    public function create(array $data): int
    {
        return $this->db->execute(
            "INSERT INTO gallery (file_path, file_type, caption, sort_order) VALUES (?, ?, ?, ?)",
            [$data['file_path'], $data['file_type'] ?? 'image', $data['caption'] ?? '', $data['sort_order'] ?? 0]
        );
    }

    public function countImages(): int
    {
        $r = $this->db->queryOne("SELECT COUNT(*) as c FROM gallery WHERE file_type = 'image'");
        return (int)($r['c'] ?? 0);
    }

    public function countVideos(): int
    {
        $r = $this->db->queryOne("SELECT COUNT(*) as c FROM gallery WHERE file_type = 'video'");
        return (int)($r['c'] ?? 0);
    }
}
