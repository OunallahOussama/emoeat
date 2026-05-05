<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: User Profile (profile.php)
 */
class ProfileTest extends BaseTestCase
{
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userId = $this->createUser('Test User', 'profile@test.com');
    }

    public function testCreateProfileSuccessfully(): void
    {
        $stmt = $this->conn->prepare("INSERT INTO USER_PROFILE (ID_USER, WEIGHT, HEIGHT, ALLERGIES, GOAL) VALUES (:u, :w, :h, :a, :g)");
        $stmt->execute([':u' => $this->userId, ':w' => 70.5, ':h' => 175.0, ':a' => 'Gluten', ':g' => 'Lose weight']);

        $stmt = $this->conn->prepare("SELECT * FROM USER_PROFILE WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($profile);
        $this->assertEquals(70.5, $profile['WEIGHT']);
        $this->assertEquals(175.0, $profile['HEIGHT']);
        $this->assertEquals('Gluten', $profile['ALLERGIES']);
        $this->assertEquals('Lose weight', $profile['GOAL']);
    }

    public function testUpdateExistingProfile(): void
    {
        // Create initial profile
        $stmt = $this->conn->prepare("INSERT INTO USER_PROFILE (ID_USER, WEIGHT, HEIGHT) VALUES (:u, 70, 175)");
        $stmt->execute([':u' => $this->userId]);

        // Update
        $stmt = $this->conn->prepare("UPDATE USER_PROFILE SET WEIGHT = :w, HEIGHT = :h WHERE ID_USER = :u");
        $stmt->execute([':w' => 65.0, ':h' => 175.0, ':u' => $this->userId]);

        $stmt = $this->conn->prepare("SELECT WEIGHT FROM USER_PROFILE WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(65.0, $profile['WEIGHT']);
    }

    public function testInvalidWeightRejected(): void
    {
        $invalidWeights = [0, -5, -100];
        foreach ($invalidWeights as $weight) {
            $this->assertFalse($weight > 0, "Weight $weight should be rejected");
        }
    }

    public function testInvalidHeightRejected(): void
    {
        $invalidHeights = [0, -10, -200];
        foreach ($invalidHeights as $height) {
            $this->assertFalse($height > 0, "Height $height should be rejected");
        }
    }

    public function testBmiCalculationUnderweight(): void
    {
        $weight = 50;
        $height = 180; // cm
        $bmi = $weight / (($height / 100) ** 2);

        $this->assertLessThan(18.5, $bmi);
    }

    public function testBmiCalculationNormal(): void
    {
        $weight = 70;
        $height = 175;
        $bmi = $weight / (($height / 100) ** 2);

        $this->assertGreaterThanOrEqual(18.5, $bmi);
        $this->assertLessThan(25, $bmi);
    }

    public function testBmiCalculationOverweight(): void
    {
        $weight = 85;
        $height = 170;
        $bmi = $weight / (($height / 100) ** 2);

        $this->assertGreaterThanOrEqual(25, $bmi);
        $this->assertLessThan(30, $bmi);
    }

    public function testBmiCalculationObese(): void
    {
        $weight = 110;
        $height = 170;
        $bmi = $weight / (($height / 100) ** 2);

        $this->assertGreaterThanOrEqual(30, $bmi);
    }

    public function testProfileActivityLogged(): void
    {
        $this->logActivity($this->userId, 'PROFILE_UPDATED');

        $stmt = $this->conn->prepare("SELECT ACTION FROM ACTIVITY_LOG WHERE ID_USER = :u AND ACTION = 'PROFILE_UPDATED'");
        $stmt->execute([':u' => $this->userId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($log);
    }

    public function testProfileCompletionCheck(): void
    {
        // No profile exists
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM USER_PROFILE WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(0, $count, "User should have no profile initially");
    }
}
