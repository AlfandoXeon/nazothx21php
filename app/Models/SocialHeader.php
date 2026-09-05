<?php
namespace App\Models;

use App\Core\Model;

class SocialHeader extends Model
{
    protected string $table = 'social_header';

    public function allOrdered(): array
    {
        return $this->db->query("SELECT * FROM social_header ORDER BY sort_order ASC, id ASC");
    }

    public function create(array $data): int
    {
        return $this->db->execute(
            "INSERT INTO social_header (name, url, icon_slug, sort_order) VALUES (?, ?, ?, ?)",
            [$data['name'], $data['url'], $data['icon_slug'] ?? '', $data['sort_order'] ?? 0]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE social_header SET name=?, url=?, icon_slug=?, sort_order=? WHERE id=?",
            [$data['name'], $data['url'], $data['icon_slug'] ?? '', $data['sort_order'] ?? 0, $id]
        );
    }
}
