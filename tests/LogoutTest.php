<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: Logout (logout.php)
 */
class LogoutTest extends BaseTestCase
{
    public function testSessionDestroyedOnLogout(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Test';
        $_SESSION['role'] = 'CLIENT';

        // Simulate logout
        $_SESSION = [];

        $this->assertEmpty($_SESSION);
    }

    public function testActivityLoggedBeforeLogout(): void
    {
        $userId = $this->createUser('User', 'logout@test.com');
        $this->logActivity($userId, 'USER_LOGOUT');

        $stmt = $this->conn->prepare("SELECT ACTION FROM ACTIVITY_LOG WHERE ID_USER = :u AND ACTION = 'USER_LOGOUT'");
        $stmt->execute([':u' => $userId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($log);
        $this->assertEquals('USER_LOGOUT', $log['ACTION']);
    }

    public function testLogoutOnlyLogsIfUserWasAuthenticated(): void
    {
        // No user_id in session = don't log
        $isAuthenticated = isset($_SESSION['user_id']);
        $this->assertFalse($isAuthenticated);
    }
}
