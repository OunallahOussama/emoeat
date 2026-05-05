<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: Dashboard (dashboard.php)
 */
class DashboardTest extends BaseTestCase
{
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userId = $this->createUser('Dashboard User', 'dash@test.com');
    }

    public function testUnauthenticatedUserRedirected(): void
    {
        // No session set = not authenticated
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testRecommendationCountAccurate(): void
    {
        $emotionId = $this->createEmotion('Happy');
        $foodId = $this->createFood('Banana');

        // Insert 3 recommendations
        for ($i = 0; $i < 3; $i++) {
            $stmt = $this->conn->prepare("INSERT INTO RECOMMENDATIONS (ID_USER, ID_FOOD, ID_EMOTION) VALUES (:u, :f, :e)");
            $stmt->execute([':u' => $this->userId, ':f' => $foodId, ':e' => $emotionId]);
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM RECOMMENDATIONS WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(3, $count);
    }

    public function testEmotionCountAccurate(): void
    {
        $emotionId = $this->createEmotion('Sad');

        for ($i = 0; $i < 5; $i++) {
            $stmt = $this->conn->prepare("INSERT INTO USER_EMOTIONS (ID_USER, ID_EMOTION) VALUES (:u, :e)");
            $stmt->execute([':u' => $this->userId, ':e' => $emotionId]);
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM USER_EMOTIONS WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(5, $count);
    }

    public function testProfileExistsCheckTrue(): void
    {
        $stmt = $this->conn->prepare("INSERT INTO USER_PROFILE (ID_USER, WEIGHT, HEIGHT) VALUES (:u, 70, 175)");
        $stmt->execute([':u' => $this->userId]);

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM USER_PROFILE WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(1, $count);
    }

    public function testProfileExistsCheckFalse(): void
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM USER_PROFILE WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(0, $count);
    }

    public function testRecentRecommendationsLimitedToFive(): void
    {
        $emotionId = $this->createEmotion('Stressed');
        $foodId = $this->createFood('Dark Chocolate');

        // Insert 7 recommendations
        for ($i = 0; $i < 7; $i++) {
            $stmt = $this->conn->prepare("INSERT INTO RECOMMENDATIONS (ID_USER, ID_FOOD, ID_EMOTION) VALUES (:u, :f, :e)");
            $stmt->execute([':u' => $this->userId, ':f' => $foodId, ':e' => $emotionId]);
        }

        $stmt = $this->conn->prepare("
            SELECT f.FOOD_NAME, e.EMOTION_NAME, r.RECOMMENDATION_DATE
            FROM RECOMMENDATIONS r
            JOIN FOODS f ON f.ID_FOOD = r.ID_FOOD
            JOIN EMOTIONS e ON e.ID_EMOTION = r.ID_EMOTION
            WHERE r.ID_USER = :u
            ORDER BY r.RECOMMENDATION_DATE DESC LIMIT 5
        ");
        $stmt->execute([':u' => $this->userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(5, $results);
    }

    public function testRecentRecommendationsContainCorrectFields(): void
    {
        $emotionId = $this->createEmotion('Anxious');
        $foodId = $this->createFood('Chamomile Tea', 'Beverage', 2);

        $stmt = $this->conn->prepare("INSERT INTO RECOMMENDATIONS (ID_USER, ID_FOOD, ID_EMOTION) VALUES (:u, :f, :e)");
        $stmt->execute([':u' => $this->userId, ':f' => $foodId, ':e' => $emotionId]);

        $stmt = $this->conn->prepare("
            SELECT f.FOOD_NAME, e.EMOTION_NAME, r.RECOMMENDATION_DATE
            FROM RECOMMENDATIONS r
            JOIN FOODS f ON f.ID_FOOD = r.ID_FOOD
            JOIN EMOTIONS e ON e.ID_EMOTION = r.ID_EMOTION
            WHERE r.ID_USER = :u
        ");
        $stmt->execute([':u' => $this->userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Chamomile Tea', $result['FOOD_NAME']);
        $this->assertEquals('Anxious', $result['EMOTION_NAME']);
        $this->assertArrayHasKey('RECOMMENDATION_DATE', $result);
    }
}
