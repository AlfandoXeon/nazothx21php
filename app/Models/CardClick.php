<?php
namespace App\Models;

use App\Core\Model;

class CardClick extends Model
{
    protected string $table = 'card_clicks';

    /**
     * Record a click on a card.
     */
    public function record(int $cardId, string $ip = ''): void
    {
        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }

        try {
            $this->db->execute(
                "INSERT INTO card_clicks (card_id, ip_address) VALUES (?, ?)",
                [$cardId, $ip]
            );
        } catch (\Exception $e) {
            // Silently fail so redirect always succeeds
        }
    }

    /**
     * Count clicks for a specific card.
     */
    public function countByCard(int $cardId): int
    {
        try {
            $row = $this->db->queryOne(
                "SELECT COUNT(*) as total FROM card_clicks WHERE card_id = ?",
                [$cardId]
            );
            return (int)($row['total'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get a map of [card_id => total_clicks] for all cards.
     */
    public function countsMap(): array
    {
        try {
            $rows = $this->db->query(
                "SELECT card_id, COUNT(*) as total FROM card_clicks GROUP BY card_id"
            );
            $map = [];
            foreach ($rows as $row) {
                $map[(int)$row['card_id']] = (int)$row['total'];
            }
            return $map;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get top clicked cards.
     */
    public function topCards(int $limit = 5): array
    {
        try {
            return $this->db->query(
                "SELECT c.id, c.title, c.subtitle, c.url, COUNT(cc.id) as clicks
                 FROM cards c
                 LEFT JOIN card_clicks cc ON c.id = cc.card_id
                 WHERE c.is_active = 1
                 GROUP BY c.id, c.title, c.subtitle, c.url
                 ORDER BY clicks DESC, c.sort_order ASC
                 LIMIT " . (int)$limit
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Total clicks across all cards.
     */
    public function totalClicks(): int
    {
        try {
            $row = $this->db->queryOne("SELECT COUNT(*) as total FROM card_clicks");
            return (int)($row['total'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
