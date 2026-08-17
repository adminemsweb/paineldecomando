<?php
declare(strict_types=1);

namespace App\Database;

use App\Config\Env;
use PDO;

final class Connection
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo) return self::$pdo;
        if (Env::get('DB_DRIVER') === 'sqlite') {
            $database = Env::get('DB_DATABASE', BASE_PATH . '/storage/database.sqlite');
            $pdo = new PDO('sqlite:' . $database, options: [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdo->exec('PRAGMA foreign_keys = ON');
            return self::$pdo = $pdo;
        }
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', Env::get('DB_HOST', '127.0.0.1'), Env::get('DB_PORT', '3306'), Env::get('DB_DATABASE', 'industrial_site'));
        return self::$pdo = new PDO($dsn, Env::get('DB_USERNAME', 'root'), Env::get('DB_PASSWORD', ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
