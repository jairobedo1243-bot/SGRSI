<?php

declare(strict_types=1);
defined('ENTRYPOINT') || (http_response_code(404) && exit);
require_once __DIR__ . '/../config/config.php';

class Conexion
{
    private static ?mysqli $connection = null;

    public static function getConnection(): mysqli
    {
        if (self::$connection instanceof mysqli) {
            return self::$connection;
        }

        // Mismo comportamiento en PHP 8.0 y 8.1+: los errores de MySQL lanzan excepción.
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
        } catch (mysqli_sql_exception $e) {
            error_log('Error de conexión: ' . $e->getMessage());
            throw new RuntimeException('No se pudo conectar a la base de datos', 0, $e);
        }

        $connection->set_charset('utf8mb4');
        self::$connection = $connection;

        return self::$connection;
    }

    public static function select(string $sql, array $params = []): array
    {
        $stmt = self::prepare($sql, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $rows;
    }

    public static function selectOne(string $sql, array $params = []): ?array
    {
        $stmt = self::prepare($sql, $params);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        return $row ?: null;
    }

    public static function scalar(string $sql, array $params = []): mixed
    {
        $row = self::selectOne($sql, $params);
        if ($row === null) {
            return null;
        }

        return reset($row);
    }

    public static function execute(string $sql, array $params = []): bool
    {
        return self::filasAfectadas($sql, $params) >= 0;
    }

    /**
     * Ejecuta un INSERT/UPDATE/DELETE y devuelve cuántas filas cambió.
     * Devuelve -1 si la base rechaza la operación (dato repetido, clave foránea, etc.).
     */
    public static function filasAfectadas(string $sql, array $params = []): int
    {
        $stmt = self::prepare($sql, $params);

        try {
            $stmt->execute();
            $filas = $stmt->affected_rows;
        } catch (mysqli_sql_exception $e) {
            error_log('Error SQL: ' . $e->getMessage());
            $filas = -1;
        }
        $stmt->close();

        return (int)$filas;
    }

    private static function prepare(string $sql, array $params = []): mysqli_stmt
    {
        $statement = self::getConnection()->prepare($sql);

        if ($params !== []) {
            $types = str_repeat('s', count($params));
            $statement->bind_param($types, ...$params);
        }

        return $statement;
    }
}
