<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

/**
 * Base test case providing a mock PDO connection for model tests.
 */
abstract class BaseTestCase extends TestCase
{
    protected \PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = $this->createMock(\PDO::class);
    }

    /**
     * Create a mock PDOStatement that returns given data.
     */
    protected function mockStatement(array $rows = [], int $rowCount = 0): \PDOStatement
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->method('rowCount')->willReturn($rowCount ?: count($rows));

        if (count($rows) === 1) {
            $stmt->method('fetch')->willReturn($rows[0]);
            $stmt->method('fetchAll')->willReturn($rows);
        } elseif (count($rows) > 1) {
            $stmt->method('fetch')->willReturnOnConsecutiveCalls(...array_merge($rows, [false]));
            $stmt->method('fetchAll')->willReturn($rows);
        } else {
            $stmt->method('fetch')->willReturn(false);
            $stmt->method('fetchAll')->willReturn([]);
        }

        return $stmt;
    }

    /**
     * Helper: make PDO return a prepared mock statement.
     */
    protected function expectPrepare(\PDOStatement $stmt): void
    {
        $this->pdo->method('prepare')->willReturn($stmt);
    }
}
