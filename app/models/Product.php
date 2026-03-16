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
        $sql = "SELECT 
                    p.id,
                    p.category_id,
                    p.name,
                    p.description,
                    p.price,
                    p.has_variants,
                    CASE
                        WHEN p.has_variants = 1 THEN COALESCE(SUM(CASE WHEN pv.active = 1 THEN pv.stock ELSE 0 END), 0)
                        ELSE p.stock
                    END AS stock,
                    p.image_url,
                    p.active,
                    p.sort_order,
                    CASE
                        WHEN p.has_variants = 1 THEN NULL
                        ELSE p.barcode
                    END AS barcode,
                    p.barcode_path,
                    c.name AS category
                FROM products p
                LEFT JOIN categories c 
                    ON c.id = p.category_id
                LEFT JOIN product_variants pv 
                    ON pv.product_id = p.id
                GROUP BY 
                    p.id,
                    p.category_id,
                    p.name,
                    p.description,
                    p.price,
                    p.has_variants,
                    p.stock,
                    p.image_url,
                    p.active,
                    p.sort_order,
                    p.barcode,
                    p.barcode_path,
                    c.name
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
    // ✅ Catálogo público
    // ==========================================================
    public function allActiveForCatalog(): array
    {
        $sql = "SELECT 
                    p.id,
                    p.category_id,
                    p.name,
                    p.description,
                    p.price,
                    p.has_variants,
                    p.image_url,
                    p.active,
                    p.barcode,
                    p.barcode_path,
                    c.name AS category
                FROM products p
                LEFT JOIN categories c 
                    ON c.id = p.category_id
                WHERE p.active = 1
                ORDER BY (p.sort_order IS NULL), p.sort_order, p.name";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // ==========================================================
    // ✅ Variantes por producto
    // onlyActive = true => solo activas
    // onlyWithStock = true => solo con stock > 0
    // ==========================================================
    public function getVariants(int $productId, bool $onlyActive = false, bool $onlyWithStock = false): array
    {
        $sql = "SELECT 
                    id,
                    product_id,
                    size,
                    color,
                    price,
                    stock,
                    barcode,
                    barcode_path,
                    active,
                    created_at
                FROM product_variants
                WHERE product_id = :id";

        if ($onlyActive) {
            $sql .= " AND active = 1";
        }

        if ($onlyWithStock) {
            $sql .= " AND stock > 0";
        }

        $sql .= " ORDER BY size, color, id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $productId]);
        return $stmt->fetchAll();
    }

    // ==========================================================
    // ✅ Buscar por barcode para POS
    // Primero variantes, luego producto simple
    // ==========================================================
    public static function findByBarcode(string $barcode): ?array
    {
        $barcode = trim($barcode);
        if ($barcode === '') {
            return null;
        }

        $db = getDB();

        // 1) Buscar primero en variantes
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.image_url,
                    p.has_variants,
                    pv.id AS variant_id,
                    pv.size,
                    pv.color,
                    pv.price,
                    pv.stock,
                    pv.barcode
                FROM product_variants pv
                INNER JOIN products p 
                    ON p.id = pv.product_id
                WHERE p.active = 1
                  AND pv.active = 1
                  AND pv.barcode = :b
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute(['b' => $barcode]);
        $row = $stmt->fetch();

        if ($row) {
            return [
                'id'           => (int)$row['id'],
                'name'         => $row['name'],
                'price'        => (float)$row['price'],
                'stock'        => (int)$row['stock'],
                'image_url'    => $row['image_url'] ?? '',
                'has_variants' => 1,
                'variant_id'   => (int)$row['variant_id'],
                'size'         => $row['size'],
                'color'        => $row['color'],
                'barcode'      => $row['barcode'],
            ];
        }

        // 2) Si no existe en variantes, buscar en products
        $sql = "SELECT 
                    id,
                    name,
                    price,
                    stock,
                    image_url,
                    has_variants,
                    barcode
                FROM products
                WHERE active = 1
                  AND barcode = :b
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute(['b' => $barcode]);
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return [
            'id'           => (int)$row['id'],
            'name'         => $row['name'],
            'price'        => (float)$row['price'],
            'stock'        => (int)$row['stock'],
            'image_url'    => $row['image_url'] ?? '',
            'has_variants' => (int)$row['has_variants'],
            'variant_id'   => null,
            'size'         => null,
            'color'        => null,
            'barcode'      => $row['barcode'],
        ];
    }

    // ==========================================================
    // ✅ Borrar variantes de un producto
    // ==========================================================
    public function deleteVariants(int $productId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM product_variants WHERE product_id = :product_id");
        return $stmt->execute(['product_id' => $productId]);
    }

    // ==========================================================
    // ✅ Insertar una variante
    // ==========================================================
    public function insertVariant(array $data): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO product_variants
            (product_id, size, color, price, stock, barcode, barcode_path, active)
            VALUES
            (:product_id, :size, :color, :price, :stock, :barcode, :barcode_path, :active)
        ");

        return $stmt->execute([
            ':product_id'   => $data['product_id'],
            ':size'         => $data['size'] !== '' ? $data['size'] : null,
            ':color'        => $data['color'] !== '' ? $data['color'] : null,
            ':price'        => $data['price'] !== '' ? $data['price'] : null,
            ':stock'        => (int)($data['stock'] ?? 0),
            ':barcode'      => $data['barcode'] !== '' ? $data['barcode'] : null,
            ':barcode_path' => $data['barcode_path'] !== '' ? $data['barcode_path'] : null,
            ':active'       => !empty($data['active']) ? 1 : 0,
        ]);
    }

    // ==========================================================
    // ✅ SAVE (INSERT/UPDATE)
    // Producto simple => barcode CC-000001
    // Variante => barcode CCV-000001
    // ==========================================================
public function save(array $data): bool
{
    $isNew = empty($data['id']);
    $hasVariants = !empty($data['has_variants']) ? 1 : 0;
    $variants = $data['variants'] ?? [];

    $this->db->beginTransaction();

    try {
        $barcode = null;

        // ==========================================================
        // PRODUCTO PRINCIPAL
        // ==========================================================
        if ($isNew && !$hasVariants) {
            $barcode = $this->nextBarcodeCC();
        }

        if ($isNew) {
            $stmt = $this->db->prepare("
                INSERT INTO products
                (name, price, has_variants, category_id, stock, description, sort_order, active, image_url, barcode)
                VALUES
                (:name, :price, :has_variants, :category_id, :stock, :description, :sort_order, :active, :image_url, :barcode)
            ");

            $stmt->execute([
                ':name'         => $data['name'],
                ':price'        => $data['price'] !== '' ? $data['price'] : null,
                ':has_variants' => $hasVariants,
                ':category_id'  => $data['category_id'],
                ':stock'        => $hasVariants ? 0 : (int)($data['stock'] ?? 0),
                ':description'  => $data['description'] ?? '',
                ':sort_order'   => $data['sort_order'] ?? 0,
                ':active'       => $data['active'] ?? 0,
                ':image_url'    => $data['image_url'] ?? '',
                ':barcode'      => $barcode,
            ]);

            $productId = (int)$this->db->lastInsertId();

            if (!$hasVariants && $barcode) {
                $relative = "assets/imgs/barcodes/{$barcode}.png";
                $absolute = __DIR__ . "/../../" . $relative;

                $this->createBarcodePng($barcode, $absolute);

                $up = $this->db->prepare("
                    UPDATE products
                    SET barcode_path = :p
                    WHERE id = :id
                ");
                $up->execute([
                    ':p'  => $relative,
                    ':id' => $productId
                ]);
            }

        } else {
            $productId = (int)$data['id'];

            $stmt = $this->db->prepare("
                UPDATE products SET
                    name = :name,
                    price = :price,
                    has_variants = :has_variants,
                    category_id = :category_id,
                    stock = :stock,
                    description = :description,
                    sort_order = :sort_order,
                    active = :active,
                    image_url = :image_url,
                    barcode = CASE WHEN :has_variants = 1 THEN NULL ELSE barcode END,
                    barcode_path = CASE WHEN :has_variants = 1 THEN NULL ELSE barcode_path END
                WHERE id = :id
            ");

            $stmt->execute([
                ':id'           => $productId,
                ':name'         => $data['name'],
                ':price'        => $data['price'] !== '' ? $data['price'] : null,
                ':has_variants' => $hasVariants,
                ':category_id'  => $data['category_id'],
                ':stock'        => $hasVariants ? 0 : (int)($data['stock'] ?? 0),
                ':description'  => $data['description'] ?? '',
                ':sort_order'   => $data['sort_order'] ?? 0,
                ':active'       => $data['active'] ?? 0,
                ':image_url'    => $data['image_url'] ?? '',
            ]);

            if (!$hasVariants) {
                $product = $this->find($productId);

                if (empty($product['barcode'])) {
                    $barcode = $this->nextBarcodeCC();

                    $relative = "assets/imgs/barcodes/{$barcode}.png";
                    $absolute = __DIR__ . "/../../" . $relative;

                    $this->createBarcodePng($barcode, $absolute);

                    $up = $this->db->prepare("
                        UPDATE products
                        SET barcode = :barcode,
                            barcode_path = :barcode_path
                        WHERE id = :id
                    ");
                    $up->execute([
                        ':barcode'      => $barcode,
                        ':barcode_path' => $relative,
                        ':id'           => $productId
                    ]);
                }
            }
        }

        // ==========================================================
        // VARIANTES
        // Mantener barcode e id si la variante ya existía
        // ==========================================================
        if (!$hasVariants) {
            // Si ya no tiene variantes, eliminarlas todas
            $this->deleteVariants($productId);
        } else {
            $keepIds = [];

            foreach ($variants as $variant) {
                $variantId = isset($variant['id']) && $variant['id'] !== '' ? (int)$variant['id'] : 0;
                $size      = trim($variant['size'] ?? '');
                $color     = trim($variant['color'] ?? '');

                // fila vacía
                if ($size === '' && $color === '') {
                    continue;
                }

                // Si ya existe: actualizar y conservar barcode
                if ($variantId > 0) {
                    $existing = $this->findVariantById($variantId, $productId);

                    if ($existing) {
                        $this->updateVariant([
                            'id'         => $variantId,
                            'product_id' => $productId,
                            'size'       => $size,
                            'color'      => $color,
                            'price'      => $variant['price'] ?? null,
                            'stock'      => $variant['stock'] ?? 0,
                            'active'     => isset($variant['active']) ? 1 : 0,
                        ]);

                        $keepIds[] = $variantId;
                        continue;
                    }
                }

                // Si es nueva: generar barcode nuevo
                $variantBarcode = $this->nextVariantBarcodeCC();
                $relative = "assets/imgs/barcodes/{$variantBarcode}.png";
                $absolute = __DIR__ . "/../../" . $relative;

                $this->createBarcodePng($variantBarcode, $absolute);

                $this->insertVariant([
                    'product_id'   => $productId,
                    'size'         => $size,
                    'color'        => $color,
                    'price'        => $variant['price'] ?? null,
                    'stock'        => $variant['stock'] ?? 0,
                    'barcode'      => $variantBarcode,
                    'barcode_path' => $relative,
                    'active'       => isset($variant['active']) ? 1 : 0,
                ]);

                $keepIds[] = (int)$this->db->lastInsertId();
            }

            // Borrar solo las variantes que ya no vinieron en el form
            $this->deleteVariantsExcept($productId, $keepIds);
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

        if (!$last) {
            return "CC-000001";
        }

        $num = (int)substr($last, 3); // CC- = 3
        $num++;

        return "CC-" . str_pad((string)$num, 6, "0", STR_PAD_LEFT);
    }

    // ==========================================================
    // ✅ Genera siguiente CCV-000001 seguro (FOR UPDATE)
    // ==========================================================
    private function nextVariantBarcodeCC(): string
    {
        $sql = "SELECT barcode
                FROM product_variants
                WHERE barcode LIKE 'CCV-%'
                ORDER BY id DESC
                LIMIT 1
                FOR UPDATE";

        $last = $this->db->query($sql)->fetchColumn();

        if (!$last) {
            return "CCV-000001";
        }

        $num = (int)substr($last, 4); // CCV- = 4
        $num++;

        return "CCV-" . str_pad((string)$num, 6, "0", STR_PAD_LEFT);
    }
    public function findVariantById(int $variantId, int $productId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM product_variants
            WHERE id = :id
            AND product_id = :product_id
            LIMIT 1
        ");
        $stmt->execute([
            ':id' => $variantId,
            ':product_id' => $productId
        ]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateVariant(array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE product_variants
            SET
                size = :size,
                color = :color,
                price = :price,
                stock = :stock,
                active = :active
            WHERE id = :id
            AND product_id = :product_id
        ");

        return $stmt->execute([
            ':id'         => $data['id'],
            ':product_id' => $data['product_id'],
            ':size'       => $data['size'] !== '' ? $data['size'] : null,
            ':color'      => $data['color'] !== '' ? $data['color'] : null,
            ':price'      => $data['price'] !== '' ? $data['price'] : null,
            ':stock'      => (int)($data['stock'] ?? 0),
            ':active'     => !empty($data['active']) ? 1 : 0,
        ]);
    }

    public function deleteVariantsExcept(int $productId, array $keepIds = []): bool
    {
        if (empty($keepIds)) {
            $stmt = $this->db->prepare("DELETE FROM product_variants WHERE product_id = :product_id");
            return $stmt->execute([':product_id' => $productId]);
        }

        $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
        $params = array_merge([$productId], $keepIds);

        $stmt = $this->db->prepare("
            DELETE FROM product_variants
            WHERE product_id = ?
            AND id NOT IN ($placeholders)
        ");

        return $stmt->execute($params);
    }
    public function allLabelsForAdmin(): array
    {
        $sql = "
            SELECT
                p.id AS product_id,
                NULL AS variant_id,
                p.name,
                c.name AS category,
                p.price,
                p.stock,
                p.barcode,
                p.barcode_path,
                NULL AS size,
                NULL AS color,
                0 AS has_variants
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.active = 1
            AND p.has_variants = 0

            UNION ALL

            SELECT
                p.id AS product_id,
                pv.id AS variant_id,
                p.name,
                c.name AS category,
                pv.price,
                pv.stock,
                pv.barcode,
                pv.barcode_path,
                pv.size,
                pv.color,
                1 AS has_variants
            FROM product_variants pv
            INNER JOIN products p ON p.id = pv.product_id
            LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.active = 1
            AND pv.active = 1

            ORDER BY name ASC, variant_id ASC
        ";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
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

        $gen = new BarcodeGeneratorPNG();
        $bars = $gen->getBarcode($barcode, $gen::TYPE_CODE_128, 3, 90);

        $barsImg = imagecreatefromstring($bars);
        if ($barsImg === false) {
            throw new \Exception("No se pudo generar la imagen del barcode.");
        }

        $margin = 20;
        $barsWidth  = imagesx($barsImg);
        $barsHeight = imagesy($barsImg);

        $width  = $barsWidth + ($margin * 2);
        $height = $barsHeight + ($margin * 2) + 20;

        $img = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);

        imagefill($img, 0, 0, $white);

        imagecopy($img, $barsImg, $margin, $margin, 0, 0, $barsWidth, $barsHeight);

        imagestring(
            $img,
            5,
            max(10, (int)(($width / 2) - (strlen($barcode) * 4))),
            $height - 18,
            $barcode,
            $black
        );

        imagepng($img, $absolutePath);

        imagedestroy($img);
        imagedestroy($barsImg);
    }
    
}