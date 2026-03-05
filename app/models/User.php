<?php
require_once __DIR__ . '/../../db.php';

class User
{
    public static function findByUsername(string $username): ?array
    {
        $db = getDB();
        $st = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $st->execute([$username]);
        $u = $st->fetch(PDO::FETCH_ASSOC);
        return $u ?: null;
    }
}