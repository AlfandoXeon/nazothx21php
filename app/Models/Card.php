<?php
namespace App\Models;

use App\Core\Model;

class Card extends Model
{
    protected string $table = 'cards';

    public function allActive(): array
    {
        return $this->db->query("SELECT * FROM cards WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    }

    public function allOrdered(): array
    {
        return $this->db->query("SELECT * FROM cards ORDER BY sort_order ASC, id ASC");
    }

    public function create(array $data): int
    {
        return $this->db->execute(
            "INSERT INTO cards (title, subtitle, url, image_url, is_special, special_title, sort_order, is_active)
             VALUES (:title, :subtitle, :url, :image_url, :is_special, :special_title, :sort_order, :is_active)",
            [
                ':title'         => $data['title'],
                ':subtitle'      => $data['subtitle'] ?? '',
                ':url'           => $data['url'],
                ':image_url'     => $data['image_url'] ?? '',
                ':is_special'    => $data['is_special'] ?? 0,
                ':special_title' => $data['special_title'] ?? '',
                ':sort_order'    => $data['sort_order'] ?? 0,
                ':is_active'     => $data['is_active'] ?? 1,
            ]
        );
    }

    public function update(int $id, array $data): int
    {
        return $this->db->execute(
            "UPDATE cards SET title=:title, subtitle=:subtitle, url=:url, image_url=:image_url,
             is_special=:is_special, special_title=:special_title, sort_order=:sort_order, is_active=:is_active
             WHERE id=:id",
            [
                ':title'         => $data['title'],
                ':subtitle'      => $data['subtitle'] ?? '',
                ':url'           => $data['url'],
                ':image_url'     => $data['image_url'] ?? '',
                ':is_special'    => $data['is_special'] ?? 0,
                ':special_title' => $data['special_title'] ?? '',
                ':sort_order'    => $data['sort_order'] ?? 0,
                ':is_active'     => $data['is_active'] ?? 1,
                ':id'            => $id,
            ]
        );
    }

    public function toggle(int $id): void
    {
        $this->db->execute("UPDATE cards SET is_active = NOT is_active WHERE id = ?", [$id]);
    }

    public function reorder(array $ids): void
    {
        foreach ($ids as $order => $id) {
            $this->db->execute("UPDATE cards SET sort_order = ? WHERE id = ?", [$order, (int)$id]);
        }
    }
}
