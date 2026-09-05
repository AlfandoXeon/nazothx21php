<?php
namespace App\Models;

use App\Core\Model;

class VisitorLog extends Model
{
    protected string $table = 'visitor_logs';

    /**
     * Record a visitor hit with 1-hour deduplication per IP.
     */
    public function record(string $ip, string $page = '/'): void
    {
        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        }

        try {
            // Deduplicate: ignore if same IP visited in the last 1 hour
            $recent = $this->db->queryOne(
                "SELECT id FROM visitor_logs WHERE ip_address = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) LIMIT 1",
                [$ip]
            );

            if (!$recent) {
                $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
                $this->db->execute(
                    "INSERT INTO visitor_logs (ip_address, user_agent, page) VALUES (?, ?, ?)",
                    [$ip, $ua, $page]
                );
            }
        } catch (\Exception $e) {
            // Silently fail so visitor experience is never broken
        }
    }

    /**
     * Total visitor count of all time.
     */
    public function totalCount(): int
    {
        try {
            $row = $this->db->queryOne("SELECT COUNT(*) as total FROM visitor_logs");
            return (int)($row['total'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Visitors today.
     */
    public function todayCount(): int
    {
        try {
            $row = $this->db->queryOne("SELECT COUNT(*) as total FROM visitor_logs WHERE DATE(visited_at) = CURDATE()");
            return (int)($row['total'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Daily visits for the past 7 days (including days with 0 visits).
     * Returns array of: [['date' => 'Y-m-d', 'label' => 'd M', 'visits' => int]]
     */
    public function last7Days(): array
    {
        try {
            $rows = $this->db->query(
                "SELECT DATE(visited_at) as log_date, COUNT(*) as visits 
                 FROM visitor_logs 
                 WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                 GROUP BY DATE(visited_at)"
            );

            $visitsByDate = [];
            foreach ($rows as $row) {
                $visitsByDate[$row['log_date']] = (int)$row['visits'];
            }

            $result = [];
            for ($i = 6; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-{$i} days"));
                $label = date('d M', strtotime($d));
                $result[] = [
                    'date'   => $d,
                    'label'  => $label,
                    'visits' => $visitsByDate[$d] ?? 0,
                ];
            }
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }
}
