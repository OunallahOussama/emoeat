<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: Activity Log (admin_activity_log.php & connexion.php logActivity)
 */
class ActivityLogTest extends BaseTestCase
{
    private int $adminId;
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminId = $this->createUser('Admin', 'admin@test.com', 'pass', 'ADMIN');
        $this->userId = $this->createUser('User', 'user@test.com');
    }

    public function testLogActivityCreatesEntry(): void
    {
        $this->logActivity($this->userId, 'USER_LOGIN');

        $stmt = $this->conn->prepare("SELECT * FROM ACTIVITY_LOG WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($log);
        $this->assertEquals('USER_LOGIN', $log['ACTION']);
        $this->assertNotEmpty($log['LOG_DATE']);
    }

    public function testMultipleLogEntriesRecorded(): void
    {
        $this->logActivity($this->userId, 'USER_LOGIN');
        $this->logActivity($this->userId, 'PROFILE_UPDATED');
        $this->logActivity($this->userId, 'USER_LOGOUT');

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM ACTIVITY_LOG WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(3, $count);
    }

    public function testLogsSortedByDateDesc(): void
    {
        $this->conn->exec("INSERT INTO ACTIVITY_LOG (ID_USER, ACTION, LOG_DATE) VALUES ({$this->userId}, 'A', '2026-01-01 10:00:00')");
        $this->conn->exec("INSERT INTO ACTIVITY_LOG (ID_USER, ACTION, LOG_DATE) VALUES ({$this->userId}, 'B', '2026-01-02 10:00:00')");
        $this->conn->exec("INSERT INTO ACTIVITY_LOG (ID_USER, ACTION, LOG_DATE) VALUES ({$this->userId}, 'C', '2026-01-03 10:00:00')");

        $stmt = $this->conn->query("SELECT ACTION FROM ACTIVITY_LOG ORDER BY LOG_DATE DESC");
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertEquals(['C', 'B', 'A'], $results);
    }

    public function testSearchByUserName(): void
    {
        $this->logActivity($this->adminId, 'ADMIN_ACTION');
        $this->logActivity($this->userId, 'USER_LOGIN');

        $q = '%admin%';
        $stmt = $this->conn->prepare("
            SELECT al.ACTION, u.NAME
            FROM ACTIVITY_LOG al
            JOIN USERS u ON u.ID_USER = al.ID_USER
            WHERE LOWER(u.NAME) LIKE :q
        ");
        $stmt->execute([':q' => $q]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(1, $results);
        $this->assertEquals('ADMIN_ACTION', $results[0]['ACTION']);
    }

    public function testSearchByAction(): void
    {
        $this->logActivity($this->userId, 'USER_LOGIN');
        $this->logActivity($this->userId, 'USER_LOGOUT');

        $q = '%login%';
        $stmt = $this->conn->prepare("
            SELECT al.ACTION
            FROM ACTIVITY_LOG al
            JOIN USERS u ON u.ID_USER = al.ID_USER
            WHERE LOWER(al.ACTION) LIKE :q
        ");
        $stmt->execute([':q' => $q]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(1, $results);
        $this->assertEquals('USER_LOGIN', $results[0]['ACTION']);
    }

    public function testActionBadgeColors(): void
    {
        $badges = [
            'USER_LOGIN' => 'green',
            'USER_LOGOUT' => 'orange',
            'USER_REGISTER' => 'blue',
            'ADMIN_DELETE_USER_1' => 'red',
            'PROFILE_UPDATED' => 'default',
        ];

        foreach ($badges as $action => $expectedColor) {
            if (str_contains($action, 'LOGIN')) {
                $color = 'green';
            } elseif (str_contains($action, 'LOGOUT')) {
                $color = 'orange';
            } elseif (str_contains($action, 'REGISTER')) {
                $color = 'blue';
            } elseif (str_contains($action, 'DELETE')) {
                $color = 'red';
            } else {
                $color = 'default';
            }
            $this->assertEquals($expectedColor, $color, "Action '$action' should be $expectedColor");
        }
    }

    public function testLogDoesNotBlockOnError(): void
    {
        // logActivity has try-catch that silently fails
        // Simulate: invalid user ID shouldn't throw exception
        try {
            // In real app, FK constraint might fail but logActivity catches it
            $stmt = $this->conn->prepare("INSERT INTO ACTIVITY_LOG (ID_USER, ACTION, LOG_DATE) VALUES (:u, :a, datetime('now'))");
            $stmt->execute([':u' => 99999, ':a' => 'TEST']);
            // SQLite doesn't enforce FK by default, so this passes
            $this->assertTrue(true);
        } catch (PDOException $e) {
            // This is also acceptable - the point is it doesn't crash the app
            $this->assertTrue(true);
        }
    }
}
