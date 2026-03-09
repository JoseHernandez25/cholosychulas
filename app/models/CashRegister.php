<?php
require_once __DIR__ . '/../../db.php';

class CashRegister
{
    public function getOpenByUser(int $userId)
    {
        $db = getDB();

        $sql = "SELECT *
                FROM cash_registers
                WHERE user_id = ?
                  AND status = 'open'
                ORDER BY id DESC
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function open(int $userId, float $amount)
    {
        $db = getDB();

        $sql = "INSERT INTO cash_registers
                (user_id, opening_amount, status, opened_at)
                VALUES (?, ?, 'open', NOW())";

        $stmt = $db->prepare($sql);
        return $stmt->execute([$userId, $amount]);
    }

    public function close(int $id, float $closingAmount, float $totalSales, int $totalItems, int $transactions)
    {
        $db = getDB();

        $sql = "UPDATE cash_registers
                SET closing_amount = ?,
                    total_sales = ?,
                    total_items = ?,
                    total_transactions = ?,
                    status = 'closed',
                    closed_at = NOW()
                WHERE id = ?";

        $stmt = $db->prepare($sql);

        return $stmt->execute([
            $closingAmount,
            $totalSales,
            $totalItems,
            $transactions,
            $id
        ]);
    }

    public function getSalesSummary(int $cashRegisterId)
    {
        $db = getDB();

        $sql = "SELECT
                    COUNT(*) AS transactions,
                    COALESCE(SUM(total), 0) AS total_sales,
                    COALESCE(SUM(pieces), 0) AS total_items
                FROM sales
                WHERE cash_register_id = ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$cashRegisterId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getById(int $id)
    {
        $db = getDB();

        $sql = "SELECT *
                FROM cash_registers
                WHERE id = ?
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}