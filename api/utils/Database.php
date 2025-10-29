<?php
/**
 * Database Connection Class
 * Singleton pattern for PDO connection management
 */

namespace App\Utils;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';

        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new \Exception("Database connection failed. Please check your configuration.");
        }
    }

    /**
     * Get singleton instance
     *
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO connection
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Execute a SELECT query
     *
     * @param string $query
     * @param array $params
     * @return array
     */
    public function query(string $query, array $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            throw new \Exception("Database query failed.");
        }
    }

    /**
     * Execute an INSERT/UPDATE/DELETE query
     *
     * @param string $query
     * @param array $params
     * @return int Last insert ID or affected rows
     */
    public function execute(string $query, array $params = []): int
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $this->connection->lastInsertId() ?: $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Execute Error: " . $e->getMessage());
            throw new \Exception("Database execution failed.");
        }
    }

    /**
     * Begin transaction
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     *
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
