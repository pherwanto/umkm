<?php
require_once __DIR__ . '/../core/Database.php';

class Satuan
{
    public static function all(): array
    {
        $pdo = Database::getInstance();
        return $pdo->query("SELECT * FROM satuan ORDER BY nama_satuan ASC")->fetchAll();
    }
}
