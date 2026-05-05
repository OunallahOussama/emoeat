<?php
namespace Tests\Models;

use App\Models\ActivityLog;
use Tests\BaseTestCase;

class ActivityLogTest extends BaseTestCase
{
    private ActivityLog $log;

    protected function setUp(): void
    {
        parent::setUp();
        $this->log = new ActivityLog($this->pdo);
    }

    public function testLogExecutesSilently(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->expects($this->once())->method('execute')->willReturn(true);
        $this->expectPrepare($stmt);

        $this->log->log(1, 'User logged in');
    }

    public function testLogCatchesException(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->method('execute')->willThrowException(new \PDOException('DB Error'));
        $this->expectPrepare($stmt);

        // Should not throw
        $this->log->log(1, 'Some action');
        $this->assertTrue(true); // Reached here = no exception propagated
    }

    public function testSearchWithQuery(): void
    {
        $rows = [
            ['ID_LOG' => 1, 'ACTION' => 'Login', 'LOG_DATE' => '2024-01-01', 'NAME' => 'John', 'EMAIL' => 'john@example.com'],
        ];
        $stmt = $this->mockStatement($rows);
        $this->expectPrepare($stmt);

        $result = $this->log->search('john');
        $this->assertCount(1, $result);
        $this->assertEquals('Login', $result[0]['ACTION']);
    }

    public function testSearchWithoutQuery(): void
    {
        $rows = [
            ['ID_LOG' => 1, 'ACTION' => 'Login', 'LOG_DATE' => '2024-01-01', 'NAME' => 'John', 'EMAIL' => 'john@example.com'],
            ['ID_LOG' => 2, 'ACTION' => 'Logout', 'LOG_DATE' => '2024-01-02', 'NAME' => 'Jane', 'EMAIL' => 'jane@example.com'],
        ];
        $stmt = $this->mockStatement($rows);
        $this->expectPrepare($stmt);

        $result = $this->log->search('');
        $this->assertCount(2, $result);
    }
}
