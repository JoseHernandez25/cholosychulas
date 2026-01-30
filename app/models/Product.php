<?php
// app/models/Product.php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

class Product
{
    private $db;

    public function __construct()
    {
        $this->db = getDB(); // PDO
    }

    // ==========================================================
    // ✅ ADMIN: listar todos (activos e inactivos)
    // ==========================================================
    public function allForAdmin(): array
    {
        $sql = "SELECT p.id,
                       p.category_id,
                       p.name,
                       p.description,
                       p.price,
                       p.stock,
                       p.image_url,
                       p.active,
                       p.sort_order,
                       p.barcode,
                       p.barcode_path,
                       c.name AS category
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                ORDER BY p.id DESC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // ==========================================================
    // ✅ ADMIN: buscar 1 producto por ID
    // ==========================================================
    public function find(int $id): ?array
    {
        $sql = "SELECT *
                FROM products
                WHERE id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ==========================================================
    // ✅ ADMIN: activar/desactivar
    // ==========================================================
    public function toggleActive(int $id): bool
    {
        $sql = "UPDATE products
                SET active = CASE WHEN active = 1 THEN 0 ELSE 1 END
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    // ==========================================================
    // Productos activos para catálogo público
    // ==========================================================
    public function allActiveForCatalog(): array
    {
        $sql = "SELECT p.id,
                       p.category_id,
                       p.name,
                       p.description,
                       p.price,
                       p.image_url,
                       p.active,
                       p.barcode,
                       p.barcode_path,
                       c.name AS category
                FROM products p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE p.active = 1
                ORDER BY (p.sort_order IS NULL), p.sort_order, p.name";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // Variantes (talla, color, stock) por producto
    public function getVariants(int $productId): array
    {
        $sql = "SELECT id, size, color, stock
                FROM product_variants
                WHERE product_id = :id AND stock > 0
                ORDER BY size, color";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $productId]);
        return $stmt->fetchAll();
    }

    // ✅ Buscar por barcode para POS
    public static function findByBarcode(string $barcode): ?array
    {
        $barcode = trim($barcode);
        if ($barcode === '') return null;

        $db = getDB();

        $sql = "SELECT id, name, price, stock
                FROM products
                WHERE active = 1 AND barcode = :b
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(['b' => $barcode]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ==========================================================
    // ✅ SAVE (INSERT/UPDATE) + BARCODE AUTO CC-000001
    // ==========================================================
    public function save(array $data): bool
    {
        $isNew = empty($data['id']);

        $this->db->beginTransaction();
        try {
            // Genera barcode solo al crear
            $barcode = $isNew ? $this->nextBarcodeCC() : null;

            if ($isNew) {
                $stmt = $this->db->prepare("
                    INSERT INTO products
                    (name, price, category_id, stock, description, sort_order, active, image_url, barcode)
                    VALUES
                    (:name, :price, :category_id, :stock, :description, :sort_order, :active, :image_url, :barcode)
                ");
                $stmt->execute([
                    ':name'        => $data['name'],
                    ':price'       => $data['price'],
                    ':category_id' => $data['category_id'],
                    ':stock'       => $data['stock'],
                    ':description' => $data['description'] ?? '',
                    ':sort_order'  => $data['sort_order'] ?? 0,
                    ':active'      => $data['active'] ?? 0,
                    ':image_url'   => $data['image_url'] ?? '',
                    ':barcode'     => $barcode,
                ]);

                $newId = (int)$this->db->lastInsertId();

                // Crear PNG y guardar ruta
                $relative = "assets/imgs/barcodes/{$barcode}.png";
                $absolute = __DIR__ . "/../../" . $relative; // ✅ tu estructura

                $this->createBarcodePng($barcode, $absolute);

                $up = $this->db->prepare("UPDATE products SET barcode_path = :p WHERE id = :id");
                $up->execute([':p' => $relative, ':id' => $newId]);

            } else {
                $stmt = $this->db->prepare("
                    UPDATE products SET
                      name=:name,
                      price=:price,
                      category_id=:category_id,
                      stock=:stock,
                      description=:description,
                      sort_order=:sort_order,
                      active=:active,
                      image_url=:image_url
                    WHERE id=:id
                ");
                $stmt->execute([
                    ':id'          => $data['id'],
                    ':name'        => $data['name'],
                    ':price'       => $data['price'],
                    ':category_id' => $data['category_id'],
                    ':stock'       => $data['stock'],
                    ':description' => $data['description'] ?? '',
                    ':sort_order'  => $data['sort_order'] ?? 0,
                    ':active'      => $data['active'] ?? 0,
                    ':image_url'   => $data['image_url'] ?? '',
                ]);
            }

            $this->db->commit();
            return true;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // ==========================================================
    // ✅ Genera siguiente CC-000001 seguro (FOR UPDATE)
    // ==========================================================
    private function nextBarcodeCC(): string
    {
        $sql = "SELECT barcode
                FROM products
                WHERE barcode LIKE 'CC-%'
                ORDER BY id DESC
                LIMIT 1
                FOR UPDATE";
        $last = $this->db->query($sql)->fetchColumn();

        if (!$last) return "CC-000001";

        $num = (int)substr($last, 3); // CC- = 3
        $num++;

        return "CC-" . str_pad((string)$num, 6, "0", STR_PAD_LEFT);
    }

    // ==========================================================
    // ✅ Crea imagen PNG Code128
    // ==========================================================
    private function createBarcodePng(string $barcode, string $absolutePath): void
    {
        $dir = dirname($absolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $gen = new \Picqer\Barcode\BarcodeGeneratorPNG();

        // 🔧 barras más gruesas y altas
        $bars = $gen->getBarcode($barcode, $gen::TYPE_CODE_128, 3, 90);

        // 🧱 lienzo con fondo blanco + quiet zone
        $margin = 20;
        $width  = imagesx(imagecreatefromstring($bars)) + ($margin * 2);
        $height = imagesy(imagecreatefromstring($bars)) + ($margin * 2) + 20; // espacio para texto

        $img = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $white);

        // pega las barras centradas
        $barsImg = imagecreatefromstring($bars);
        imagecopy($img, $barsImg, $margin, $margin, 0, 0, imagesx($barsImg), imagesy($barsImg));

        // 🧾 texto legible debajo
        imagestring($img, 5, ($width/2) - (strlen($barcode)*4), $height - 18, $barcode, $black);

        imagepng($img, $absolutePath);
        imagedestroy($img);
        imagedestroy($barsImg);
    }
}