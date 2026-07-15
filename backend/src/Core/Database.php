<?php
namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/config.php';
            $db = $config['db'];

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $db['host'],
                $db['port'],
                $db['name'],
                $db['charset']
            );

            try {
                self::$instance = new PDO($dsn, $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);

                $tz = $config['timezone'] ?? 'UTC';
                date_default_timezone_set($tz);
                try {
                    $offset = (new \DateTime('now', new \DateTimeZone($tz)))->format('P');
                    self::$instance->exec("SET time_zone = '{$offset}'");
                } catch (\Throwable) {
                    // Non-fatal if the server rejects session time zones.
                }
            } catch (PDOException $e) {
                Response::json(['message' => 'Database connection failed', 'error' => $e->getMessage()], 500);
                exit;
            }
        }

        return self::$instance;
    }
}
