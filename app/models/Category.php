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

    public function allActive()
    {
        $sql = "SELECT id, name
                FROM categories
                WHERE active = 1
                ORDER BY id asc";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}