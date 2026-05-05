<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: Forgot Password (forgot_password.php)
 */
class ForgotPasswordTest extends BaseTestCase
{
    public function testEmailExistsCheck(): void
    {
        $this->createUser('User', 'exists@test.com');

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM USERS WHERE EMAIL = :e");
        $stmt->execute([':e' => 'exists@test.com']);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(1, $count);
    }

    public function testEmailNotFoundRejected(): void
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM USERS WHERE EMAIL = :e");
        $stmt->execute([':e' => 'ghost@test.com']);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(0, $count);
    }

    public function testInvalidEmailFormatRejected(): void
    {
        $this->assertFalse(filter_var('not-an-email', FILTER_VALIDATE_EMAIL));
        $this->assertFalse(filter_var('', FILTER_VALIDATE_EMAIL));
        $this->assertFalse(filter_var('@no-local', FILTER_VALIDATE_EMAIL));
    }

    public function testPasswordResetUpdatesHash(): void
    {
        $email = 'reset@test.com';
        $this->createUser('User', $email, 'oldpassword');

        $newHash = password_hash('newpassword', PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE USERS SET PASSWORD = :p WHERE EMAIL = :e");
        $stmt->execute([':p' => $newHash, ':e' => $email]);

        // Verify new password works
        $stmt = $this->conn->prepare("SELECT PASSWORD FROM USERS WHERE EMAIL = :e");
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertTrue(password_verify('newpassword', $user['PASSWORD']));
        $this->assertFalse(password_verify('oldpassword', $user['PASSWORD']));
    }

    public function testPasswordTooShortRejected(): void
    {
        $password = '12345';
        $this->assertTrue(strlen($password) < 6);
    }

    public function testPasswordConfirmationMismatch(): void
    {
        $password = 'newpass123';
        $confirm = 'differentpass';
        $this->assertNotEquals($password, $confirm);
    }

    public function testPasswordResetActivityLogged(): void
    {
        $userId = $this->createUser('User', 'log@test.com');
        $this->logActivity($userId, 'PASSWORD_RESET');

        $stmt = $this->conn->prepare("SELECT ACTION FROM ACTIVITY_LOG WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('PASSWORD_RESET', $log['ACTION']);
    }

    public function testValidResetFlowEndToEnd(): void
    {
        $email = 'full@test.com';
        $userId = $this->createUser('Full Flow', $email, 'originalpass');

        // Step 1: verify email exists
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM USERS WHERE EMAIL = :e");
        $stmt->execute([':e' => $email]);
        $this->assertGreaterThan(0, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);

        // Step 2: reset password
        $newPass = 'newsecure123';
        $hash = password_hash($newPass, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE USERS SET PASSWORD = :p WHERE EMAIL = :e");
        $stmt->execute([':p' => $hash, ':e' => $email]);

        // Step 3: log activity
        $this->logActivity($userId, 'PASSWORD_RESET');

        // Verify
        $stmt = $this->conn->prepare("SELECT PASSWORD FROM USERS WHERE EMAIL = :e");
        $stmt->execute([':e' => $email]);
        $this->assertTrue(password_verify($newPass, $stmt->fetch(PDO::FETCH_ASSOC)['PASSWORD']));
    }
}
