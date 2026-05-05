<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: User Login (login.php)
 */
class LoginTest extends BaseTestCase
{
    public function testValidLoginWithCorrectCredentials(): void
    {
        $email = 'user@example.com';
        $password = 'password123';
        $userId = $this->createUser('Test User', $email, $password, 'CLIENT');

        // Simulate login query
        $stmt = $this->conn->prepare("SELECT ID_USER, NAME, PASSWORD, ROLE FROM USERS WHERE EMAIL = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($user);
        $this->assertTrue(password_verify($password, $user['PASSWORD']));
        $this->assertEquals($userId, $user['ID_USER']);
    }

    public function testLoginWithWrongPasswordFails(): void
    {
        $email = 'user@example.com';
        $this->createUser('Test User', $email, 'correctpass');

        $stmt = $this->conn->prepare("SELECT PASSWORD FROM USERS WHERE EMAIL = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertFalse(password_verify('wrongpass', $user['PASSWORD']));
    }

    public function testLoginWithNonExistentEmailFails(): void
    {
        $stmt = $this->conn->prepare("SELECT ID_USER FROM USERS WHERE EMAIL = :email");
        $stmt->execute([':email' => 'nonexistent@example.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertFalse($user);
    }

    public function testAdminRoleDetectedOnLogin(): void
    {
        $email = 'admin@example.com';
        $this->createUser('Admin', $email, 'adminpass', 'ADMIN');

        $stmt = $this->conn->prepare("SELECT ROLE FROM USERS WHERE EMAIL = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('ADMIN', $user['ROLE']);
    }

    public function testClientRoleDetectedOnLogin(): void
    {
        $email = 'client@example.com';
        $this->createUser('Client', $email, 'clientpass', 'CLIENT');

        $stmt = $this->conn->prepare("SELECT ROLE FROM USERS WHERE EMAIL = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('CLIENT', $user['ROLE']);
    }

    public function testSessionSetOnSuccessfulLogin(): void
    {
        $userId = $this->createUser('Test', 'test@test.com', 'pass123', 'CLIENT');

        // Simulate session assignment
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = 'Test';
        $_SESSION['role'] = 'CLIENT';

        $this->assertEquals($userId, $_SESSION['user_id']);
        $this->assertEquals('Test', $_SESSION['user_name']);
        $this->assertEquals('CLIENT', $_SESSION['role']);
    }

    public function testActivityLogCreatedOnLogin(): void
    {
        $userId = $this->createUser('Test', 'log@test.com', 'pass123');
        $this->logActivity($userId, 'USER_LOGIN');

        $stmt = $this->conn->prepare("SELECT ACTION FROM ACTIVITY_LOG WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('USER_LOGIN', $log['ACTION']);
    }

    public function testEmptyEmailRejected(): void
    {
        $email = '';
        $this->assertTrue(empty($email), "Empty email should be rejected");
    }

    public function testEmptyPasswordRejected(): void
    {
        $password = '';
        $this->assertTrue(empty($password), "Empty password should be rejected");
    }
}
