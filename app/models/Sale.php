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
                    created_at
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
}