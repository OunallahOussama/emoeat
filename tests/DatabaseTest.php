<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: Database Connection (config/Database.php)
 */
class DatabaseTest extends BaseTestCase
{
    public function testConnectionReturnsValidPdo(): void
    {
        $this->assertInstanceOf(PDO::class, $this->conn);
    }

    public function testConnectionCanExecuteQuery(): void
    {
        $stmt = $this->conn->query("SELECT 1");
        $this->assertNotFalse($stmt);
    }

    public function testTablesCreatedCorrectly(): void
    {
        $stmt = $this->conn->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $expected = ['ACTIVITY_LOG', 'ADMIN', 'CLIENT', 'EMOTION_FOOD', 'EMOTIONS', 'FOODS', 'RECOMMENDATIONS', 'USER_EMOTIONS', 'USER_PROFILE', 'USERS'];
        foreach ($expected as $table) {
            $this->assertContains($table, $tables, "Table $table should exist");
        }
    }

    public function testPdoErrorModeIsException(): void
    {
        $mode = $this->conn->getAttribute(PDO::ATTR_ERRMODE);
        $this->assertEquals(PDO::ERRMODE_EXCEPTION, $mode);
    }
}
