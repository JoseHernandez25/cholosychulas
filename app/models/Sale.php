<?php
// app/models/Sale.php
require_once __DIR__ . '/../../db.php';

class Sale
{
    public static function createFromItems(array $items): array
    {
        $db = getDB(); // PDO

        // Normaliza ids
        $ids = [];
        foreach ($items as $it) {
            $ids[] = (int)($it['id'] ?? 0);
        }
        $ids = array_values(array_filter($ids, fn($x)=>$x>0));
        if (!$ids) throw new Exception("Items inválidos");

        // Trae productos válidos + STOCK
        $in = implode(',', array_fill(0, count($ids), '?'));
        $st = $db->prepare("SELECT id, name, price, stock FROM products WHERE active=1 AND id IN ($in)");
        $st->execute($ids);
        $prods = $st->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        foreach ($prods as $p) $map[(int)$p['id']] = $p;

        $rows  = [];
        $total = 0.0;
        $pzas  = 0;

        // Armar renglones y VALIDAR STOCK
        foreach ($items as $it) {
            $id  = (int)($it['id'] ?? 0);
            $qty = (int)($it['qty'] ?? 0);
            if ($id<=0 || $qty<=0) continue;

            if (!isset($map[$id])) {
                throw new Exception("Producto inválido/inactivo (ID: $id)");
            }

            $stockActual = (int)($map[$id]['stock'] ?? 0);
            if ($stockActual < $qty) {
                throw new Exception("Stock insuficiente para {$map[$id]['name']} (stock: $stockActual, pedido: $qty)");
            }

            $precio = (float)$map[$id]['price'];
            $sub = $precio * $qty;

            $total += $sub;
            $pzas  += $qty;

            $rows[] = [
                'id_producto' => $id,
                'nombre'      => $map[$id]['name'],
                'qty'         => $qty,
                'precio'      => $precio,
                'subtotal'    => $sub
            ];
        }

        if (!$rows) throw new Exception("Carrito inválido");

        // ===== TRANSACCIÓN =====
        $db->beginTransaction();

        try {
            // 1) Insert venta (pagado)
            $stV = $db->prepare("
                INSERT INTO sales (created_at, total, pieces, status)
                VALUES (NOW(), ?, ?, 'pagado')
            ");
            $stV->execute([
                number_format($total,2,'.',''),
                $pzas
            ]);

            $idSale = (int)$db->lastInsertId();

            // 2) Insert detalle
            $stD = $db->prepare("
                INSERT INTO sale_items (sale_id, product_id, qty, price, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");

            // 3) Descontar stock (SIN NEGATIVOS)
            $stStock = $db->prepare("
                UPDATE products
                SET stock = stock - ?
                WHERE id = ? AND stock >= ?
            ");

            foreach ($rows as $r) {
                $stD->execute([
                    $idSale,
                    $r['id_producto'],
                    $r['qty'],
                    number_format($r['precio'],2,'.',''),
                    number_format($r['subtotal'],2,'.','')
                ]);

                $stStock->execute([
                    $r['qty'],
                    $r['id_producto'],
                    $r['qty']
                ]);

                // Si no actualizó, es porque ya no había stock (venta simultánea o dato inconsistente)
                if ($stStock->rowCount() === 0) {
                    throw new Exception("No se pudo descontar stock para {$r['nombre']} (posible venta simultánea).");
                }
            }

            $db->commit();

            return [
                'id_venta'    => $idSale,
                'ticket_html' => self::buildTicketHtml($idSale, $rows, $total)
            ];

        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    private static function buildTicketHtml(int $folio, array $rows, float $total): string
    {
        $itemsHtml = '';
        foreach ($rows as $r) {
            $name = htmlspecialchars($r['nombre']);
            $qty  = (int)$r['qty'];
            $sub  = number_format((float)$r['subtotal'], 2);
            $itemsHtml .= "<tr>
              <td style='padding:4px 0;width:70%'><b>{$name}</b><br><span style='opacity:.75'>x{$qty}</span></td>
              <td style='padding:4px 0;text-align:right;width:30%'>\${$sub}</td>
            </tr>";
        }

        $tot = number_format($total, 2);

        return "<!doctype html><html><head><meta charset='utf-8'>
        <style>
          body{font-family:Arial,sans-serif;margin:0;padding:10px;width:280px}
          .c{text-align:center}
          hr{border:none;border-top:1px dashed #000;margin:10px 0}
          table{width:100%;border-collapse:collapse;font-size:12px}
          .tot{font-size:14px}
        </style></head><body>
          <div class='c'>
            <div style='font-weight:900;font-size:16px'>CHOLOS Y CHULAS</div>
            <div style='font-size:12px'>Ticket de venta</div>
            <div style='font-size:12px'>Folio: <b>#{$folio}</b></div>
            <div style='font-size:11px'>".date('Y-m-d H:i')."</div>
          </div>
          <hr>
          <table>{$itemsHtml}</table>
          <hr>
          <table class='tot'>
            <tr><td><b>TOTAL</b></td><td style='text-align:right'><b>\${$tot}</b></td></tr>
          </table>
          <hr>
          <div class='c' style='font-size:12px'>Gracias por su compra</div>
        </body></html>";
    }
}