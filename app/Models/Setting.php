<?php
namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    protected string $table = 'settings';

    public function get(string $key, string $default = ''): string
    {
        $row = $this->db->queryOne("SELECT `value` FROM settings WHERE `key` = ?", [$key]);
        return $row ? ($row['value'] ?? $default) : $default;
    }

    public function set(string $key, string $value): void
    {
        $this->db->execute(
            "INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?",
            [$key, $value, $value]
        );
    }

    public function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function allAsMap(): array
    {
        $rows = $this->db->query("SELECT `key`, `value` FROM settings");
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['key']] = $row['value'];
        }
        return $map;
    }
}
