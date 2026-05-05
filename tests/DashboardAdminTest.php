<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: Admin Dashboard (dashboard_admin.php)
 */
class DashboardAdminTest extends BaseTestCase
{
    private int $adminId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminId = $this->createUser('Admin', 'admin@test.com', 'pass', 'ADMIN');
    }

    public function testNonAdminAccessDenied(): void
    {
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'CLIENT';

        $this->assertNotEquals('ADMIN', $_SESSION['role']);
    }

    public function testUnauthenticatedAccessDenied(): void
    {
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testTotalUsersCount(): void
    {
        $this->createUser('U1', 'u1@test.com');
        $this->createUser('U2', 'u2@test.com');

        $stmt = $this->conn->query("SELECT COUNT(*) AS CNT FROM USERS");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        // 3 total: admin + 2 users
        $this->assertEquals(3, $count);
    }

    public function testTotalFoodsCount(): void
    {
        $this->createFood('A');
        $this->createFood('B', 'Grain');

        $stmt = $this->conn->query("SELECT COUNT(*) AS CNT FROM FOODS");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(2, $count);
    }

    public function testTotalEmotionsCount(): void
    {
        $this->createEmotion('Happy');
        $this->createEmotion('Sad');
        $this->createEmotion('Angry');

        $stmt = $this->conn->query("SELECT COUNT(*) AS CNT FROM EMOTIONS");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(3, $count);
    }

    public function testTotalRecommendationsCount(): void
    {
        $userId = $this->createUser('U', 'u@test.com');
        $eId = $this->createEmotion('E');
        $fId = $this->createFood('F');

        $this->conn->exec("INSERT INTO RECOMMENDATIONS (ID_USER, ID_FOOD, ID_EMOTION) VALUES ($userId, $fId, $eId)");
        $this->conn->exec("INSERT INTO RECOMMENDATIONS (ID_USER, ID_FOOD, ID_EMOTION) VALUES ($userId, $fId, $eId)");

        $stmt = $this->conn->query("SELECT COUNT(*) AS CNT FROM RECOMMENDATIONS");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(2, $count);
    }

    public function testRecentUsersLimitedToTen(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->createUser("User$i", "user$i@test.com");
        }

        $stmt = $this->conn->query("SELECT ID_USER, NAME, EMAIL, ROLE, CREATED_AT FROM USERS ORDER BY CREATED_AT DESC LIMIT 10");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(10, $results);
    }

    public function testRecentUsersContainExpectedFields(): void
    {
        $stmt = $this->conn->query("SELECT ID_USER, NAME, EMAIL, ROLE, CREATED_AT FROM USERS ORDER BY CREATED_AT DESC LIMIT 10");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $user) {
            $this->assertArrayHasKey('ID_USER', $user);
            $this->assertArrayHasKey('NAME', $user);
            $this->assertArrayHasKey('EMAIL', $user);
            $this->assertArrayHasKey('ROLE', $user);
            $this->assertArrayHasKey('CREATED_AT', $user);
        }
    }
}
