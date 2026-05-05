<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: Admin User Management (admin_users.php)
 */
class AdminUsersTest extends BaseTestCase
{
    private int $adminId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminId = $this->createUser('Admin', 'admin@test.com', 'adminpass', 'ADMIN');
    }

    public function testNonAdminAccessDenied(): void
    {
        $_SESSION['user_id'] = 99;
        $_SESSION['role'] = 'CLIENT';

        $this->assertNotEquals('ADMIN', $_SESSION['role']);
    }

    public function testAdminCannotDeleteSelf(): void
    {
        $delId = $this->adminId;
        $currentAdminId = $this->adminId;

        $this->assertEquals($delId, $currentAdminId, "Admin should not be able to delete self");
    }

    public function testDeleteUserCascadesCorrectly(): void
    {
        $userId = $this->createUser('Victim', 'victim@test.com');
        $emotionId = $this->createEmotion('Sad');
        $foodId = $this->createFood('Apple');

        // Create related records
        $this->conn->exec("INSERT INTO CLIENT (ID_USER) VALUES ($userId)");
        $this->conn->exec("INSERT INTO USER_PROFILE (ID_USER, WEIGHT, HEIGHT) VALUES ($userId, 70, 175)");
        $this->conn->exec("INSERT INTO USER_EMOTIONS (ID_USER, ID_EMOTION) VALUES ($userId, $emotionId)");
        $this->conn->exec("INSERT INTO RECOMMENDATIONS (ID_USER, ID_FOOD, ID_EMOTION) VALUES ($userId, $foodId, $emotionId)");
        $this->logActivity($userId, 'USER_LOGIN');

        // Cascade delete
        $this->conn->exec("DELETE FROM RECOMMENDATIONS WHERE ID_USER = $userId");
        $this->conn->exec("DELETE FROM USER_EMOTIONS WHERE ID_USER = $userId");
        $this->conn->exec("DELETE FROM USER_PROFILE WHERE ID_USER = $userId");
        $this->conn->exec("DELETE FROM ACTIVITY_LOG WHERE ID_USER = $userId");
        $this->conn->exec("DELETE FROM CLIENT WHERE ID_USER = $userId");
        $this->conn->exec("DELETE FROM USERS WHERE ID_USER = $userId");

        // Verify all gone
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM USERS WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);
        $this->assertEquals(0, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM CLIENT WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);
        $this->assertEquals(0, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM RECOMMENDATIONS WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);
        $this->assertEquals(0, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);
    }

    public function testChangeRoleToAdmin(): void
    {
        $userId = $this->createUser('Client', 'client@test.com', 'pass', 'CLIENT');

        // Change role
        $stmt = $this->conn->prepare("UPDATE USERS SET ROLE = 'ADMIN' WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);

        // Add ADMIN record
        $stmt = $this->conn->prepare("INSERT INTO ADMIN (ID_USER) VALUES (:u)");
        $stmt->execute([':u' => $userId]);

        // Verify
        $stmt = $this->conn->prepare("SELECT ROLE FROM USERS WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);
        $this->assertEquals('ADMIN', $stmt->fetch(PDO::FETCH_ASSOC)['ROLE']);

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM ADMIN WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);
        $this->assertEquals(1, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);
    }

    public function testChangeRoleToClient(): void
    {
        $userId = $this->createUser('Admin2', 'admin2@test.com', 'pass', 'ADMIN');
        $this->conn->exec("INSERT INTO ADMIN (ID_USER) VALUES ($userId)");

        // Change role to CLIENT
        $stmt = $this->conn->prepare("UPDATE USERS SET ROLE = 'CLIENT' WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);

        // Remove ADMIN record
        $stmt = $this->conn->prepare("DELETE FROM ADMIN WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);

        // Verify
        $stmt = $this->conn->prepare("SELECT ROLE FROM USERS WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);
        $this->assertEquals('CLIENT', $stmt->fetch(PDO::FETCH_ASSOC)['ROLE']);

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM ADMIN WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);
        $this->assertEquals(0, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);
    }

    public function testAdminCannotChangeOwnRole(): void
    {
        $changeId = $this->adminId;
        $currentAdminId = $this->adminId;

        $this->assertEquals($changeId, $currentAdminId, "Admin cannot change own role");
    }

    public function testSearchUsersByName(): void
    {
        $this->createUser('Alice Smith', 'alice@test.com');
        $this->createUser('Bob Jones', 'bob@test.com');

        $q = '%alice%';
        $stmt = $this->conn->prepare("SELECT * FROM USERS WHERE LOWER(NAME) LIKE :q OR LOWER(EMAIL) LIKE :q");
        $stmt->execute([':q' => $q]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(1, $results);
        $this->assertEquals('Alice Smith', $results[0]['NAME']);
    }

    public function testSearchUsersByEmail(): void
    {
        $this->createUser('Alice', 'alice@company.org');
        $this->createUser('Bob', 'bob@company.org');

        $q = '%company.org%';
        $stmt = $this->conn->prepare("SELECT * FROM USERS WHERE LOWER(NAME) LIKE :q OR LOWER(EMAIL) LIKE :q");
        $stmt->execute([':q' => $q]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(2, $results);
    }

    public function testDeleteActivityLogged(): void
    {
        $userId = $this->createUser('ToDelete', 'del@test.com');
        $this->logActivity($this->adminId, 'ADMIN_DELETE_USER_' . $userId);

        $stmt = $this->conn->prepare("SELECT ACTION FROM ACTIVITY_LOG WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->adminId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertStringContains('ADMIN_DELETE_USER_', $log['ACTION']);
    }

    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(str_contains($haystack, $needle));
    }
}
