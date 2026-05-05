<?php

use PHPUnit\Framework\TestCase;

/**
 * Base test class with shared database helpers
 */
abstract class BaseTestCase extends TestCase
{
    protected ?PDO $conn = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createTestDatabase();
        $this->createSchema();
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
    }

    protected function tearDown(): void
    {
        $this->conn = null;
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        parent::tearDown();
    }

    private function createTestDatabase(): PDO
    {
        $conn = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        return $conn;
    }

    private function createSchema(): void
    {
        $this->conn->exec("
            CREATE TABLE USERS (
                ID_USER INTEGER PRIMARY KEY AUTOINCREMENT,
                NAME TEXT NOT NULL,
                EMAIL TEXT NOT NULL UNIQUE,
                PASSWORD TEXT NOT NULL,
                ROLE TEXT NOT NULL DEFAULT 'CLIENT',
                CREATED_AT DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $this->conn->exec("
            CREATE TABLE CLIENT (
                ID_CLIENT INTEGER PRIMARY KEY AUTOINCREMENT,
                ID_USER INTEGER NOT NULL,
                FOREIGN KEY (ID_USER) REFERENCES USERS(ID_USER)
            )
        ");

        $this->conn->exec("
            CREATE TABLE ADMIN (
                ID_ADMIN INTEGER PRIMARY KEY AUTOINCREMENT,
                ID_USER INTEGER NOT NULL,
                FOREIGN KEY (ID_USER) REFERENCES USERS(ID_USER)
            )
        ");

        $this->conn->exec("
            CREATE TABLE USER_PROFILE (
                ID_PROFILE INTEGER PRIMARY KEY AUTOINCREMENT,
                ID_USER INTEGER NOT NULL,
                WEIGHT REAL,
                HEIGHT REAL,
                ALLERGIES TEXT,
                GOAL TEXT,
                FOREIGN KEY (ID_USER) REFERENCES USERS(ID_USER)
            )
        ");

        $this->conn->exec("
            CREATE TABLE EMOTIONS (
                ID_EMOTION INTEGER PRIMARY KEY AUTOINCREMENT,
                EMOTION_NAME TEXT NOT NULL,
                DESCRIPTION TEXT
            )
        ");

        $this->conn->exec("
            CREATE TABLE FOODS (
                ID_FOOD INTEGER PRIMARY KEY AUTOINCREMENT,
                FOOD_NAME TEXT NOT NULL,
                CATEGORY TEXT,
                CALORIES REAL,
                PROTEIN REAL,
                CARBS REAL,
                FAT REAL,
                DESCRIPTION TEXT
            )
        ");

        $this->conn->exec("
            CREATE TABLE EMOTION_FOOD (
                ID_RULE INTEGER PRIMARY KEY AUTOINCREMENT,
                ID_EMOTION INTEGER NOT NULL,
                ID_FOOD INTEGER NOT NULL,
                INTENSITY INTEGER DEFAULT 5,
                FOREIGN KEY (ID_EMOTION) REFERENCES EMOTIONS(ID_EMOTION),
                FOREIGN KEY (ID_FOOD) REFERENCES FOODS(ID_FOOD)
            )
        ");

        $this->conn->exec("
            CREATE TABLE USER_EMOTIONS (
                ID_UE INTEGER PRIMARY KEY AUTOINCREMENT,
                ID_USER INTEGER NOT NULL,
                ID_EMOTION INTEGER NOT NULL,
                EMOTION_DATE DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (ID_USER) REFERENCES USERS(ID_USER),
                FOREIGN KEY (ID_EMOTION) REFERENCES EMOTIONS(ID_EMOTION)
            )
        ");

        $this->conn->exec("
            CREATE TABLE RECOMMENDATIONS (
                ID_REC INTEGER PRIMARY KEY AUTOINCREMENT,
                ID_USER INTEGER NOT NULL,
                ID_FOOD INTEGER NOT NULL,
                ID_EMOTION INTEGER NOT NULL,
                BENEFIT TEXT,
                RECOMMENDATION_DATE DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (ID_USER) REFERENCES USERS(ID_USER),
                FOREIGN KEY (ID_FOOD) REFERENCES FOODS(ID_FOOD),
                FOREIGN KEY (ID_EMOTION) REFERENCES EMOTIONS(ID_EMOTION)
            )
        ");

        $this->conn->exec("
            CREATE TABLE ACTIVITY_LOG (
                ID_LOG INTEGER PRIMARY KEY AUTOINCREMENT,
                ID_USER INTEGER NOT NULL,
                ACTION TEXT NOT NULL,
                LOG_DATE DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (ID_USER) REFERENCES USERS(ID_USER)
            )
        ");
    }

    /**
     * Helper: create a test user and return their ID
     */
    protected function createUser(string $name = 'Test User', string $email = 'test@example.com', string $password = 'password123', string $role = 'CLIENT'): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("INSERT INTO USERS (NAME, EMAIL, PASSWORD, ROLE) VALUES (:n, :e, :p, :r)");
        $stmt->execute([':n' => $name, ':e' => $email, ':p' => $hash, ':r' => $role]);
        return (int) $this->conn->lastInsertId();
    }

    /**
     * Helper: create a test food and return its ID
     */
    protected function createFood(string $name = 'Banana', string $category = 'Fruit', float $calories = 89): int
    {
        $stmt = $this->conn->prepare("INSERT INTO FOODS (FOOD_NAME, CATEGORY, CALORIES) VALUES (:n, :c, :cal)");
        $stmt->execute([':n' => $name, ':c' => $category, ':cal' => $calories]);
        return (int) $this->conn->lastInsertId();
    }

    /**
     * Helper: create a test emotion and return its ID
     */
    protected function createEmotion(string $name = 'Happy', string $desc = 'Feeling good'): int
    {
        $stmt = $this->conn->prepare("INSERT INTO EMOTIONS (EMOTION_NAME, DESCRIPTION) VALUES (:n, :d)");
        $stmt->execute([':n' => $name, ':d' => $desc]);
        return (int) $this->conn->lastInsertId();
    }

    /**
     * Helper: simulate logActivity function
     */
    protected function logActivity(int $userId, string $action): void
    {
        $stmt = $this->conn->prepare("INSERT INTO ACTIVITY_LOG (ID_USER, ACTION, LOG_DATE) VALUES (:u, :a, datetime('now'))");
        $stmt->execute([':u' => $userId, ':a' => $action]);
    }
}
