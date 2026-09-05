<?php
namespace App\Models;

use App\Core\Model;

class Profile extends Model
{
    protected string $table = 'profile';

    public function get(): ?array
    {
        return $this->db->queryOne("SELECT * FROM profile WHERE id = 1");
    }

    public function update(array $data): int
    {
        $fields = [];
        $values = [];
        foreach ($data as $k => $v) {
            $fields[] = "`{$k}` = ?";
            $values[] = $v;
        }
        $values[] = 1;
        return $this->db->execute("UPDATE profile SET " . implode(', ', $fields) . " WHERE id = ?", $values);
    }

    public function ensureExists(): void
    {
        $exists = $this->db->queryOne("SELECT id FROM profile WHERE id = 1");
        if (!$exists) {
            $this->db->execute(
                "INSERT INTO profile (id, name, role, bio, avatar_url) VALUES (1, 'NAZO ナゾ', 'MLBB CONTENT CREATOR', '', '/NazoLinktree/Foto/fotoProfileNazo.png')"
            );
        }
    }
}
