<?php
namespace App\Core;

/**
 * Abstract Base Model
 * Menyediakan operasi CRUD standar. Setiap domain model (Guru, Siswa, dll)
 * extends class ini dan mendefinisikan $table serta $primaryKey.
 */
abstract class BaseModel
{
    protected \mysqli $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = App::getInstance()->db();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }

    /**
     * @param array<string,mixed> $conditions ['kolom' => 'nilai']
     */
    public function findWhere(array $conditions, string $orderBy = '', int $limit = 0): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $types = '';
        $values = [];

        if (!empty($conditions)) {
            $parts = [];
            foreach ($conditions as $col => $val) {
                $parts[] = "$col = ?";
                $types .= is_int($val) ? 'i' : 's';
                $values[] = $val;
            }
            $sql .= ' WHERE ' . implode(' AND ', $parts);
        }

        if ($orderBy) $sql .= " ORDER BY $orderBy";
        if ($limit > 0) $sql .= " LIMIT $limit";

        $stmt = $this->db->prepare($sql);
        if ($values) {
            $stmt->bind_param($types, ...$values);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }

    public function findOne(array $conditions): ?array
    {
        $rows = $this->findWhere($conditions, '', 1);
        return $rows[0] ?? null;
    }

    public function count(string $where = '', string $types = '', mixed ...$params): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($where) $sql .= " WHERE $where";

        if ($types && $params) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return (int)($row['total'] ?? 0);
        }

        $result = $this->db->query($sql);
        return (int)($result->fetch_assoc()['total'] ?? 0);
    }

    /**
     * INSERT data, return insert_id atau false
     */
    public function insert(array $data): int|false
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $types = $this->detectTypes($data);
        $values = array_values($data);
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            $id = $this->db->insert_id;
            $stmt->close();
            return $id ?: 1; // return 1 jika tabel tanpa auto_increment
        }

        $stmt->close();
        return false;
    }

    /**
     * UPDATE data berdasarkan kondisi WHERE
     */
    public function update(array $data, array $where): bool
    {
        $setParts = [];
        foreach (array_keys($data) as $col) {
            $setParts[] = "$col = ?";
        }

        $whereParts = [];
        foreach (array_keys($where) as $col) {
            $whereParts[] = "$col = ?";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setParts)
             . " WHERE " . implode(' AND ', $whereParts);

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $types = $this->detectTypes($data) . $this->detectTypes($where);
        $values = array_merge(array_values($data), array_values($where));
        $stmt->bind_param($types, ...$values);

        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->bind_param('i', $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Eksekusi raw query dengan prepared statement
     */
    protected function query(string $sql, string $types = '', mixed ...$params): \mysqli_result|bool
    {
        if (empty($types)) {
            return $this->db->query($sql);
        }

        $stmt = $this->db->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    /**
     * Auto-detect tipe data untuk bind_param
     */
    protected function detectTypes(array $data): string
    {
        $types = '';
        foreach ($data as $value) {
            if (is_int($value))        $types .= 'i';
            elseif (is_float($value))  $types .= 'd';
            else                       $types .= 's';
        }
        return $types;
    }

    /**
     * Begin transaction wrapper
     */
    public function beginTransaction(): void
    {
        $this->db->begin_transaction();
    }

    public function commit(): void
    {
        $this->db->commit();
    }

    public function rollback(): void
    {
        $this->db->rollback();
    }
}
