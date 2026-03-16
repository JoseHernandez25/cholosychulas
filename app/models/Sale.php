<?php

require_once __DIR__ . '/../../db.php';

class Sale
{
    public function allForAdmin()
    {
        $db = getDB();

        $sql = "SELECT
                    id,
                    customer_name,
                    customer_phone,
                    total,
                    pieces,
                    status,
                    created_at,
                    cash_register_id,
                    user_id
                FROM sales
                ORDER BY id DESC";

        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findForAdmin(int $id)
    {
        $db = getDB();

        $sql = "SELECT *
                FROM sales
                WHERE id = ?
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function itemsBySale(int $saleId)
    {
        $db = getDB();

        $sql = "SELECT
                    si.product_id,
                    si.variant_id,
                    p.name,
                    pv.size,
                    pv.color,
                    si.qty,
                    si.price,
                    si.subtotal
                FROM sale_items si
                LEFT JOIN products p 
                    ON p.id = si.product_id
                LEFT JOIN product_variants pv 
                    ON pv.id = si.variant_id
                WHERE si.sale_id = ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$saleId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createFromItems(array $items, array $extra = [])
    {
        $db = getDB();

        if (empty($items)) {
            throw new Exception('Carrito vacío');
        }

        $cashRegisterId = $extra['cash_register_id'] ?? null;
        $userId         = $extra['user_id'] ?? null;

        try {
            $db->beginTransaction();

            $total      = 0;
            $pieces     = 0;
            $cleanItems = [];

            foreach ($items as $item) {
                $productId = (int)($item['id'] ?? 0);
                $variantId = isset($item['variant_id']) && $item['variant_id'] !== null && $item['variant_id'] !== ''
                    ? (int)$item['variant_id']
                    : null;
                $qty = (int)($item['qty'] ?? 0);

                if ($productId <= 0 || $qty <= 0) {
                    throw new Exception('Item inválido en carrito');
                }

                // ======================================================
                // CASO 1: PRODUCTO CON VARIANTE
                // ======================================================
                if ($variantId) {
                    $stmt = $db->prepare("
                        SELECT
                            p.id AS product_id,
                            p.name,
                            p.active AS product_active,
                            pv.id AS variant_id,
                            pv.size,
                            pv.color,
                            pv.price,
                            pv.stock,
                            pv.active AS variant_active
                        FROM product_variants pv
                        INNER JOIN products p 
                            ON p.id = pv.product_id
                        WHERE p.id = ?
                          AND pv.id = ?
                        LIMIT 1
                    ");
                    $stmt->execute([$productId, $variantId]);
                    $variant = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$variant) {
                        throw new Exception("Variante no encontrada para el producto ID {$productId}");
                    }

                    if ((int)$variant['product_active'] !== 1) {
                        throw new Exception("El producto '{$variant['name']}' está inactivo");
                    }

                    if ((int)$variant['variant_active'] !== 1) {
                        throw new Exception("La variante de '{$variant['name']}' está inactiva");
                    }

                    if ((int)$variant['stock'] < $qty) {
                        $detalle = [];
                        if (!empty($variant['size'])) {
                            $detalle[] = $variant['size'];
                        }
                        if (!empty($variant['color'])) {
                            $detalle[] = $variant['color'];
                        }

                        $nombreCompleto = $variant['name'];
                        if (!empty($detalle)) {
                            $nombreCompleto .= ' - ' . implode(' / ', $detalle);
                        }

                        throw new Exception("Stock insuficiente para '{$nombreCompleto}'");
                    }

                    $price = (float)$variant['price'];
                    $subtotal = $price * $qty;

                    $total  += $subtotal;
                    $pieces += $qty;

                    $cleanItems[] = [
                        'product_id' => (int)$variant['product_id'],
                        'variant_id' => (int)$variant['variant_id'],
                        'name'       => $variant['name'],
                        'size'       => $variant['size'],
                        'color'      => $variant['color'],
                        'qty'        => $qty,
                        'price'      => $price,
                        'subtotal'   => $subtotal,
                        'is_variant' => true
                    ];

                    continue;
                }

                // ======================================================
                // CASO 2: PRODUCTO SIMPLE
                // ======================================================
                $stmt = $db->prepare("
                    SELECT id, name, price, stock, active, has_variants
                    FROM products
                    WHERE id = ?
                    LIMIT 1
                ");
                $stmt->execute([$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new Exception("Producto no encontrado: ID {$productId}");
                }

                if ((int)$product['active'] !== 1) {
                    throw new Exception("El producto '{$product['name']}' está inactivo");
                }

                if ((int)$product['has_variants'] === 1) {
                    throw new Exception("El producto '{$product['name']}' debe venderse por variante");
                }

                if ((int)$product['stock'] < $qty) {
                    throw new Exception("Stock insuficiente para '{$product['name']}'");
                }

                $price    = (float)$product['price'];
                $subtotal = $price * $qty;

                $total  += $subtotal;
                $pieces += $qty;

                $cleanItems[] = [
                    'product_id' => (int)$product['id'],
                    'variant_id' => null,
                    'name'       => $product['name'],
                    'size'       => null,
                    'color'      => null,
                    'qty'        => $qty,
                    'price'      => $price,
                    'subtotal'   => $subtotal,
                    'is_variant' => false
                ];
            }

            // ==========================================================
            // INSERTAR VENTA PRINCIPAL
            // ==========================================================
            $sqlSale = "INSERT INTO sales
                        (customer_name, customer_phone, total, status, created_at, pieces, cash_register_id, user_id)
                        VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)";

            $stmtSale = $db->prepare($sqlSale);
            $stmtSale->execute([
                'Publico',
                null,
                $total,
                'pagado',
                $pieces,
                $cashRegisterId,
                $userId
            ]);

            $saleId = (int)$db->lastInsertId();

            // ==========================================================
            // INSERTAR DETALLE
            // ==========================================================
            $sqlItem = "INSERT INTO sale_items
                        (sale_id, product_id, variant_id, qty, price, subtotal)
                        VALUES (?, ?, ?, ?, ?, ?)";

            $stmtItem = $db->prepare($sqlItem);

            $sqlStockProduct = "UPDATE products
                                SET stock = stock - ?
                                WHERE id = ?
                                  AND stock >= ?";

            $stmtStockProduct = $db->prepare($sqlStockProduct);

            $sqlStockVariant = "UPDATE product_variants
                                SET stock = stock - ?
                                WHERE id = ?
                                  AND stock >= ?";

            $stmtStockVariant = $db->prepare($sqlStockVariant);

            foreach ($cleanItems as $row) {
                $stmtItem->execute([
                    $saleId,
                    $row['product_id'],
                    $row['variant_id'],
                    $row['qty'],
                    $row['price'],
                    $row['subtotal']
                ]);

                if ($row['is_variant']) {
                    $stmtStockVariant->execute([
                        $row['qty'],
                        $row['variant_id'],
                        $row['qty']
                    ]);

                    if ($stmtStockVariant->rowCount() <= 0) {
                        $detalle = [];
                        if (!empty($row['size'])) {
                            $detalle[] = $row['size'];
                        }
                        if (!empty($row['color'])) {
                            $detalle[] = $row['color'];
                        }

                        $nombreCompleto = $row['name'];
                        if (!empty($detalle)) {
                            $nombreCompleto .= ' - ' . implode(' / ', $detalle);
                        }

                        throw new Exception("No se pudo descontar stock de '{$nombreCompleto}'");
                    }
                } else {
                    $stmtStockProduct->execute([
                        $row['qty'],
                        $row['product_id'],
                        $row['qty']
                    ]);

                    if ($stmtStockProduct->rowCount() <= 0) {
                        throw new Exception("No se pudo descontar stock de '{$row['name']}'");
                    }
                }
            }

            $db->commit();

            return [
                'id_venta' => $saleId,
                'ticket_html' => ''
            ];

        } catch (Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}