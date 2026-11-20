<?php
namespace App\Models;

use App\Core\Model;

class Article extends Model
{
    protected string $table = 'articles';

    /**
     * Get paginated published articles with optional search & category filter.
     * Includes approved comment count per article.
     */
    public function allPublished(?string $search = null, ?string $category = null, int $limit = 9, int $offset = 0): array
    {
        $where    = ["a.status = 'published'"];
        $bindings = [];

        if ($search !== null && $search !== '') {
            $like = '%' . $search . '%';
            $where[]    = "(a.title LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ?)";
            $bindings[] = $like;
            $bindings[] = $like;
            $bindings[] = $like;
        }

        if ($category !== null && $category !== '') {
            $where[]    = "a.category = ?";
            $bindings[] = $category;
        }

        $whereSql = implode(' AND ', $where);
        $limit    = max(1, $limit);
        $offset   = max(0, $offset);

        try {
            return $this->db->query(
                "SELECT a.*,
                        (SELECT COUNT(*) FROM article_comments ac WHERE ac.article_id = a.id AND ac.is_approved = 1) AS comments_count
                 FROM articles a
                 WHERE {$whereSql}
                 ORDER BY COALESCE(a.published_at, a.created_at) DESC, a.id DESC
                 LIMIT {$limit} OFFSET {$offset}",
                $bindings
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Count published articles matching optional search & category filter.
     */
    public function countPublished(?string $search = null, ?string $category = null): int
    {
        $where    = ["status = 'published'"];
        $bindings = [];

        if ($search !== null && $search !== '') {
            $like = '%' . $search . '%';
            $where[]    = "(title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
            $bindings[] = $like;
            $bindings[] = $like;
            $bindings[] = $like;
        }

        if ($category !== null && $category !== '') {
            $where[]    = "category = ?";
            $bindings[] = $category;
        }

        $whereSql = implode(' AND ', $where);

        try {
            $row = $this->db->queryOne(
                "SELECT COUNT(*) AS total FROM articles WHERE {$whereSql}",
                $bindings
            );
            return (int)($row['total'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get distinct non-empty categories of published articles.
     */
    public function publishedCategories(): array
    {
        try {
            $rows = $this->db->query(
                "SELECT DISTINCT category FROM articles
                 WHERE status = 'published' AND category IS NOT NULL AND category != ''
                 ORDER BY category ASC"
            );
            return array_column($rows, 'category');
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Find a published article by its slug.
     */
    public function findPublishedBySlug(string $slug): ?array
    {
        try {
            return $this->db->queryOne(
                "SELECT * FROM articles WHERE slug = ? AND status = 'published' LIMIT 1",
                [$slug]
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get related/other recent published articles excluding current ID.
     */
    public function recentOtherPublished(int $excludeId, int $limit = 3): array
    {
        $limit = max(1, $limit);
        try {
            return $this->db->query(
                "SELECT id, title, slug, excerpt, thumbnail_url, category, views_count, published_at, created_at
                 FROM articles
                 WHERE status = 'published' AND id != ?
                 ORDER BY COALESCE(published_at, created_at) DESC, id DESC
                 LIMIT {$limit}",
                [$excludeId]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * List all articles for admin panel with comment counts.
     */
    public function allAdminWithStats(): array
    {
        try {
            return $this->db->query(
                "SELECT a.*,
                        (SELECT COUNT(*) FROM article_comments ac WHERE ac.article_id = a.id) AS comments_count
                 FROM articles a
                 ORDER BY a.created_at DESC, a.id DESC"
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Create a new article and return its inserted ID.
     */
    public function create(array $data): int
    {
        $status      = ($data['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
        $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;

        $this->db->execute(
            "INSERT INTO articles (title, slug, excerpt, content, thumbnail_url, category, status, published_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['title'],
                $data['slug'],
                $data['excerpt'] ?? '',
                $data['content'],
                $data['thumbnail_url'] ?? '',
                $data['category'] ?? 'Umum',
                $status,
                $publishedAt,
            ]
        );

        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing article.
     */
    public function update(int $id, array $data): int
    {
        $existing = $this->find($id);
        $status   = ($data['status'] ?? 'published') === 'draft' ? 'draft' : 'published';

        $publishedAt = $existing['published_at'] ?? null;
        if ($status === 'published' && empty($publishedAt)) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        return $this->db->execute(
            "UPDATE articles
             SET title = ?, slug = ?, excerpt = ?, content = ?, thumbnail_url = ?, category = ?, status = ?, published_at = ?
             WHERE id = ?",
            [
                $data['title'],
                $data['slug'],
                $data['excerpt'] ?? '',
                $data['content'],
                $data['thumbnail_url'] ?? '',
                $data['category'] ?? 'Umum',
                $status,
                $publishedAt,
                $id,
            ]
        );
    }

    /**
     * Toggle status between 'published' and 'draft'.
     */
    public function toggleStatus(int $id): ?string
    {
        $article = $this->find($id);
        if (!$article) {
            return null;
        }

        $newStatus   = $article['status'] === 'published' ? 'draft' : 'published';
        $publishedAt = $article['published_at'];
        if ($newStatus === 'published' && empty($publishedAt)) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $this->db->execute(
            "UPDATE articles SET status = ?, published_at = ? WHERE id = ?",
            [$newStatus, $publishedAt, $id]
        );

        return $newStatus;
    }

    /**
     * Atomically increment article views counter.
     */
    public function incrementViews(int $id): void
    {
        try {
            $this->db->execute(
                "UPDATE articles SET views_count = views_count + 1 WHERE id = ?",
                [$id]
            );
        } catch (\Exception $e) {
            // Silently fail so reading experience is never broken
        }
    }

    /**
     * Generate a clean, unique URL slug from title or custom input.
     */
    public function generateUniqueSlug(string $source, ?int $excludeId = null): string
    {
        $slug = strtolower(trim($source));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'artikel-' . date('Ymd');
        }

        // Reserve room for uniqueness suffix (-999)
        $baseSlug  = substr($slug, 0, 190);
        $candidate = $baseSlug;
        $counter   = 2;

        while ($this->slugExists($candidate, $excludeId)) {
            $candidate = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }

    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId !== null) {
            $row = $this->db->queryOne(
                "SELECT id FROM articles WHERE slug = ? AND id != ? LIMIT 1",
                [$slug, $excludeId]
            );
        } else {
            $row = $this->db->queryOne(
                "SELECT id FROM articles WHERE slug = ? LIMIT 1",
                [$slug]
            );
        }
        return $row !== null;
    }

    /**
     * Safe total count of all articles (returns 0 if table doesn't exist yet).
     */
    public function countTotal(): int
    {
        try {
            $row = $this->db->queryOne("SELECT COUNT(*) AS total FROM articles");
            return (int)($row['total'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Count published articles only.
     */
    public function countPublishedTotal(): int
    {
        try {
            $row = $this->db->queryOne("SELECT COUNT(*) AS total FROM articles WHERE status = 'published'");
            return (int)($row['total'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Sum of views across all articles.
     */
    public function totalViews(): int
    {
        try {
            $row = $this->db->queryOne("SELECT COALESCE(SUM(views_count), 0) AS total FROM articles");
            return (int)($row['total'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Top read articles for admin dashboard.
     */
    public function topArticles(int $limit = 5): array
    {
        $limit = max(1, $limit);
        try {
            return $this->db->query(
                "SELECT id, title, slug, category, status, views_count, published_at, created_at
                 FROM articles
                 ORDER BY views_count DESC, id DESC
                 LIMIT {$limit}"
            );
        } catch (\Exception $e) {
            return [];
        }
    }
}
