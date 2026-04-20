<?php
namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo === null) {
            $cfg = require __DIR__ . '/config.php';
            $dbc = $cfg['db'];
            $dsn = "mysql:host={$dbc['host']};port={$dbc['port']};dbname={$dbc['name']};charset={$dbc['charset']}";

            try {
                self::$pdo = new PDO($dsn, $dbc['user'], $dbc['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Database connection failed: ' . $e->getMessage(),
                ]);
                exit;
            }
        }
        return self::$pdo;
    }
}
