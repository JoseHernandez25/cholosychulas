<?php
// app/models/Category.php
require_once __DIR__ . '/../../db.php';

class Category
{
    private $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    public function all(): array
    {
        $sql = "SELECT id, name, active
                FROM categories
                ORDER BY name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function allActive(): array
    {
        $sql = "SELECT id, name, active
                FROM categories
                WHERE active = 1
                ORDER BY name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, active
            FROM categories
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $name = trim($name);

        if ($excludeId) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM categories
                WHERE name = :name
                  AND id <> :id
            ");
            $stmt->execute([
                ':name' => $name,
                ':id'   => $excludeId
            ]);
        } else {
            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM categories
                WHERE name = :name
            ");
            $stmt->execute([':name' => $name]);
        }

        return (int)$stmt->fetchColumn() > 0;
    }

    public function save(array $data): bool
    {
        $id     = !empty($data['id']) ? (int)$data['id'] : null;
        $name   = trim($data['name'] ?? '');
        $active = !empty($data['active']) ? 1 : 0;

        if ($name === '') {
            throw new Exception('El nombre de la categoría es obligatorio');
        }

        if ($this->existsByName($name, $id)) {
            throw new Exception('Ya existe una categoría con ese nombre');
        }

        if ($id) {
            $stmt = $this->db->prepare("
                UPDATE categories
                SET name = :name,
                    active = :active
                WHERE id = :id
            ");

            return $stmt->execute([
                ':id'     => $id,
                ':name'   => $name,
                ':active' => $active
            ]);
        }

        $stmt = $this->db->prepare("
            INSERT INTO categories (name, active)
            VALUES (:name, :active)
        ");

        return $stmt->execute([
            ':name'   => $name,
            ':active' => $active
        ]);
    }

    public function toggleActive(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE categories
            SET active = CASE WHEN active = 1 THEN 0 ELSE 1 END
            WHERE id = :id
        ");

        return $stmt->execute([':id' => $id]);
    }
}