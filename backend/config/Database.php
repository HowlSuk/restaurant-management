<?php
namespace App\Config;

use PDO;
use PDOException;

/*Its job is to manage the connection 
so i don't accidentally open a hundred connections at once, which would crash my local server.*/
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

/*The DSN: 
--It builds a Data Source Name, which is just a formatted string like 
  "mysql:host=127.0.0.1;dbname=restaurant_db"

The Singleton Pattern: 
--the if (self::$pdo === null). This is a smart trick. It checks: "Am I already connected?" * If No: It creates a new connection using PDO (PHP Data Objects).
  If Yes: It just hands you the existing connection. This saves memory and speed. */


/*The config/ folder acts as a centralized settings hub that allows you to change global variables (like database passwords or security keys) */