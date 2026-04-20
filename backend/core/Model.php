<?php
namespace App\Core;

use App\Config\Database;
use PDO;

/**
 * Base Model providing generic CRUD against a configured table.
 */
abstract class Model
{
    protected PDO $db;
    protected string $table;
    /** Columns allowed in create/update. Override in subclasses. */
    protected array $fillable = [];

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function where(string $column, $value): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = :v ORDER BY id DESC");
        $stmt->execute([':v' => $value]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $data = $this->onlyFillable($data);
        $cols = array_keys($data);
        $placeholders = array_map(fn($c) => ":$c", $cols);
        $sql  = "INSERT INTO {$this->table} (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);
        foreach ($data as $k => $v) $stmt->bindValue(":$k", $v);
        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->onlyFillable($data);
        if (!$data) return false;
        $sets = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
        $sql  = "UPDATE {$this->table} SET {$sets} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        foreach ($data as $k => $v) $stmt->bindValue(":$k", $v);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    protected function onlyFillable(array $data): array
    {
        if (!$this->fillable) return $data;
        return array_intersect_key($data, array_flip($this->fillable));
    }
}
