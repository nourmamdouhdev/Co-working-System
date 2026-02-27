<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use Throwable;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $host = (string) config('db.host');
        $port = (int) config('db.port');
        $name = (string) config('db.name');
        $user = (string) config('db.user');
        $pass = (string) config('db.pass');
        $charset = (string) config('db.charset', 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::syncSessionTimezone();
        } catch (PDOException $exception) {
            http_response_code(500);
            echo 'Database connection failed.';
            if ((bool) config('app.debug', false)) {
                echo '<pre>' . e($exception->getMessage()) . '</pre>';
            }
            exit;
        }

        return self::$pdo;
    }

    public static function syncSessionTimezone(): void
    {
        if (self::$pdo === null) {
            return;
        }

        $offset = date('P');
        if (!preg_match('/^[+-]\d{2}:\d{2}$/', $offset)) {
            return;
        }

        $stmt = self::$pdo->prepare('SET time_zone = :offset');
        $stmt->execute(['offset' => $offset]);
    }

    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connection();
        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();
            return $result;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }
}
