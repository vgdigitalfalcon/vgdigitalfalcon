<?php
/**
 * VG Digital Falcon - Database Access Layer
 * Robust PDO Singleton Wrapper with Automatic Table Provisioning
 */

namespace VGDigitalFalcon\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdo = null;
    private bool $connected = false;

    private function __construct()
    {
        $configFile = __DIR__ . '/../config/config.php';
        if (!file_exists($configFile)) {
            $configFile = __DIR__ . '/../config/config.sample.php';
        }

        if (file_exists($configFile)) {
            $config = include $configFile;
            if (isset($config['database'])) {
                $db = $config['database'];
                $host = $db['host'] ?? 'localhost';
                $name = $db['database'] ?? '';
                $user = $db['username'] ?? '';
                $pass = $db['password'] ?? '';
                $charset = $db['charset'] ?? 'utf8mb4';

                // Skip connection if sample placeholder remains
                if ($pass !== 'YOUR_SECURE_PASSWORD_HERE' && !empty($name)) {
                    try {
                        $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
                        $options = [
                            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES   => false,
                        ];
                        $this->pdo = new PDO($dsn, $user, $pass, $options);
                        $this->connected = true;
                        $this->ensureTablesExist();
                    } catch (PDOException $e) {
                        error_log("Database connection failed: " . $e->getMessage());
                        $this->pdo = null;
                        $this->connected = false;
                    }
                }
            }
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): ?PDO
    {
        return $this->pdo;
    }

    public function isConnected(): bool
    {
        return $this->connected && $this->pdo !== null;
    }

    private function ensureTablesExist(): void
    {
        if (!$this->isConnected()) return;

        $sql = "CREATE TABLE IF NOT EXISTS vg_leads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            service VARCHAR(100) NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) DEFAULT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            channel VARCHAR(255) DEFAULT NULL,
            budget VARCHAR(100) DEFAULT NULL,
            subject VARCHAR(255) DEFAULT NULL,
            message TEXT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Table provisioning warning: " . $e->getMessage());
        }
    }

    public function saveLead(array $data): bool
    {
        if (!$this->isConnected()) {
            return false;
        }

        $sql = "INSERT INTO vg_leads (service, name, email, phone, website, channel, budget, subject, message, ip_address, user_agent, created_at)
                VALUES (:service, :name, :email, :phone, :website, :channel, :budget, :subject, :message, :ip, :ua, NOW())";

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':service' => $data['service'] ?? 'general',
                ':name'    => $data['name'] ?? '',
                ':email'   => $data['email'] ?? null,
                ':phone'   => $data['phone'] ?? null,
                ':website' => $data['website'] ?? null,
                ':channel' => $data['channel'] ?? null,
                ':budget'  => $data['budget'] ?? null,
                ':subject' => $data['subject'] ?? null,
                ':message' => $data['message'] ?? null,
                ':ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
                ':ua'      => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
            ]);
        } catch (PDOException $e) {
            error_log("Failed to insert lead: " . $e->getMessage());
            return false;
        }
    }
}
