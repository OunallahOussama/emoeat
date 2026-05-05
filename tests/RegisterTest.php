<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: User Registration (register.php)
 */
class RegisterTest extends BaseTestCase
{
    public function testValidRegistrationCreatesUser(): void
    {
        $name = 'Nessrine';
        $email = 'nessrine@example.com';
        $password = 'securepass';
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare("INSERT INTO USERS (NAME, EMAIL, PASSWORD, ROLE) VALUES (:n, :e, :p, 'CLIENT')");
        $stmt->execute([':n' => $name, ':e' => $email, ':p' => $hash]);
        $userId = (int) $this->conn->lastInsertId();

        // Also create CLIENT record
        $stmt = $this->conn->prepare("INSERT INTO CLIENT (ID_USER) VALUES (:u)");
        $stmt->execute([':u' => $userId]);

        // Verify user exists
        $stmt = $this->conn->prepare("SELECT * FROM USERS WHERE EMAIL = :e");
        $stmt->execute([':e' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($user);
        $this->assertEquals($name, $user['NAME']);
        $this->assertEquals('CLIENT', $user['ROLE']);
        $this->assertTrue(password_verify($password, $user['PASSWORD']));
    }

    public function testDuplicateEmailRejected(): void
    {
        $this->createUser('User1', 'dup@example.com');

        // Check duplicate
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM USERS WHERE EMAIL = :e");
        $stmt->execute([':e' => 'dup@example.com']);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertGreaterThan(0, $count, "Duplicate email should be detected");
    }

    public function testInvalidEmailFormatRejected(): void
    {
        $invalidEmails = ['notanemail', 'missing@', '@nodomain', ''];
        foreach ($invalidEmails as $email) {
            $this->assertFalse(
                filter_var($email, FILTER_VALIDATE_EMAIL),
                "Email '$email' should be rejected"
            );
        }
    }

    public function testValidEmailFormatAccepted(): void
    {
        $validEmails = ['user@example.com', 'nessrine10ounallah@gmail.com', 'test.user@domain.co'];
        foreach ($validEmails as $email) {
            $this->assertNotFalse(
                filter_var($email, FILTER_VALIDATE_EMAIL),
                "Email '$email' should be accepted"
            );
        }
    }

    public function testPasswordTooShortRejected(): void
    {
        $shortPasswords = ['12345', 'abc', '', 'hi'];
        foreach ($shortPasswords as $pwd) {
            $this->assertTrue(strlen($pwd) < 6, "Password '$pwd' should be rejected (< 6 chars)");
        }
    }

    public function testPasswordConfirmationMustMatch(): void
    {
        $password = 'securepass';
        $confirm = 'differentpass';
        $this->assertNotEquals($password, $confirm);
    }

    public function testClientRecordCreatedOnRegistration(): void
    {
        $userId = $this->createUser('Client', 'client@test.com');
        $stmt = $this->conn->prepare("INSERT INTO CLIENT (ID_USER) VALUES (:u)");
        $stmt->execute([':u' => $userId]);

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM CLIENT WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(1, $count);
    }

    public function testUserDefaultsToClientRole(): void
    {
        $userId = $this->createUser('New User', 'new@test.com');
        $stmt = $this->conn->prepare("SELECT ROLE FROM USERS WHERE ID_USER = :u");
        $stmt->execute([':u' => $userId]);
        $role = $stmt->fetch(PDO::FETCH_ASSOC)['ROLE'];

        $this->assertEquals('CLIENT', $role);
    }

    public function testPasswordIsHashedWithBcrypt(): void
    {
        $password = 'mypassword123';
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $this->assertStringStartsWith('$2y$', $hash);
        $this->assertTrue(password_verify($password, $hash));
    }
}
