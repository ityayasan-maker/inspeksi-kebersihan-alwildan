<?php
/**
 * Base Model
 */
abstract class Model
{
    protected PDO    $db;
    protected string $table  = '';
    protected string $pk     = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /* ── CRUD dasar ─────────────────────────────────────── */

    public function findAll(string $orderBy = '', int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) $sql .= " ORDER BY {$orderBy}";
        if ($limit)   $sql .= " LIMIT {$limit} OFFSET {$offset}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function find(int|string $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->pk} = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private static array $tableColumnsCache = [];

    protected function filterColumns(array $data): array
    {
        if (empty($data)) return [];

        if (!isset(self::$tableColumnsCache[$this->table])) {
            try {
                $isPg = ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql');
                if ($isPg) {
                    $stmt = $this->db->prepare(
                        "SELECT column_name FROM information_schema.columns WHERE table_name = ?"
                    );
                    $stmt->execute([$this->table]);
                    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
                } else {
                    $stmt = $this->db->query("DESCRIBE `{$this->table}`");
                    $cols = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
                }
                self::$tableColumnsCache[$this->table] = $cols ?: [];
            } catch (Throwable) {
                self::$tableColumnsCache[$this->table] = [];
            }
        }

        $validCols = self::$tableColumnsCache[$this->table];
        if (!empty($validCols)) {
            $filtered = [];
            foreach ($data as $k => $v) {
                if (in_array($k, $validCols, true)) {
                    $filtered[$k] = $v;
                }
            }
            return $filtered ?: $data;
        }

        return $data;
    }

    public function insert(array $data): int
    {
        $data = $this->filterColumns($data);
        if (empty($data)) return 0;

        $cols = implode(', ', array_keys($data));
        $phs  = implode(', ', array_fill(0, count($data), '?'));
        $isPg = ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql');

        if ($isPg) {
            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table} ({$cols}) VALUES ({$phs}) RETURNING {$this->pk}"
            );
            $stmt->execute(array_values($data));
            return (int) $stmt->fetchColumn();
        }

        $stmt = $this->db->prepare(
            "INSERT INTO `{$this->table}` ({$cols}) VALUES ({$phs})"
        );
        $stmt->execute(array_values($data));
        
        try {
            return (int) $this->db->lastInsertId();
        } catch (Throwable) {
            return 0;
        }
    }

    public function update(int|string $id, array $data): bool
    {
        $data = $this->filterColumns($data);
        if (empty($data)) return false;

        $isPg  = ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql');
        $quote = $isPg ? '' : '`';

        $set  = implode(', ', array_map(fn($k) => "{$quote}{$k}{$quote} = ?", array_keys($data)));
        $stmt = $this->db->prepare(
            "UPDATE {$quote}{$this->table}{$quote} SET {$set} WHERE {$quote}{$this->pk}{$quote} = ?"
        );
        $vals = array_values($data);
        $vals[] = $id;
        return $stmt->execute($vals);
    }

    public function delete(int|string $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->table} WHERE {$this->pk} = ?"
        );
        return $stmt->execute([$id]);
    }

    public function count(string $where = '', array $params = []): int
    {
        $sql  = "SELECT COUNT(*) FROM {$this->table}";
        if ($where) $sql .= " WHERE {$where}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /* ── Query helpers ───────────────────────────────────── */

    /** Jalankan query bebas dengan parameter */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function queryScalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /** Pagination helper */
    public function paginate(
        string $sql,
        array  $params,
        int    $page,
        int    $perPage
    ): array {
        $total  = (int) $this->queryScalar(
            "SELECT COUNT(*) FROM ({$sql}) AS _sub", $params
        );
        $offset = ($page - 1) * $perPage;

        $rows = $this->query("{$sql} LIMIT {$perPage} OFFSET {$offset}", $params);

        return [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }
}
