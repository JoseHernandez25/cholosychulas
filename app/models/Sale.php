<?php

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
                    p.name,
                    si.qty,
                    si.price,
                    si.subtotal
                FROM sale_items si
                LEFT JOIN products p ON p.id = si.product_id
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

            $total  = 0;
            $pieces = 0;
            $cleanItems = [];

            foreach ($items as $item) {
                $productId = (int)($item['id'] ?? 0);
                $qty       = (int)($item['qty'] ?? 0);

                if ($productId <= 0 || $qty <= 0) {
                    throw new Exception('Item inválido en carrito');
                }

                // Traer producto real
                $stmt = $db->prepare("SELECT id, name, price, stock, active FROM products WHERE id = ? LIMIT 1");
                $stmt->execute([$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    throw new Exception("Producto no encontrado: ID {$productId}");
                }

                if ((int)$product['active'] !== 1) {
                    throw new Exception("El producto '{$product['name']}' está inactivo");
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
                    'name'       => $product['name'],
                    'qty'        => $qty,
                    'price'      => $price,
                    'subtotal'   => $subtotal
                ];
            }

            // Insertar venta principal
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

            // Insertar detalle + descontar stock
            $sqlItem = "INSERT INTO sale_items
                        (sale_id, product_id, variant_id, qty, price, subtotal)
                        VALUES (?, ?, ?, ?, ?, ?)";

            $stmtItem = $db->prepare($sqlItem);

            $sqlStock = "UPDATE products
                         SET stock = stock - ?
                         WHERE id = ?";

            $stmtStock = $db->prepare($sqlStock);

            foreach ($cleanItems as $row) {
                $stmtItem->execute([
                    $saleId,
                    $row['product_id'],
                    null, // variant_id
                    $row['qty'],
                    $row['price'],
                    $row['subtotal']
                ]);

                $stmtStock->execute([
                    $row['qty'],
                    $row['product_id']
                ]);
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