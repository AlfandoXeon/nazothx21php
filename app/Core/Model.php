<?php
namespace App\Core;

use App\Core\Database;

abstract class Model
{
    protected Database $db;
    protected string $table = '';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(string $orderBy = 'id', string $dir = 'ASC'): array
    {
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        return $this->db->query("SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$dir}");
    }

    public function find(int $id): ?array
    {
        return $this->db->queryOne("SELECT * FROM `{$this->table}` WHERE id = ?", [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM `{$this->table}` WHERE id = ?", [$id]);
    }

    public function count(): int
    {
        $row = $this->db->queryOne("SELECT COUNT(*) as total FROM `{$this->table}`");
        return (int)($row['total'] ?? 0);
    }
}
