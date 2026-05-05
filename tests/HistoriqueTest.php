<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: History (historique.php)
 */
class HistoriqueTest extends BaseTestCase
{
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userId = $this->createUser('User', 'hist@test.com');
    }

    public function testEmotionHistoryLoadedSortedByDate(): void
    {
        $e1 = $this->createEmotion('Happy');
        $e2 = $this->createEmotion('Sad');

        $this->conn->exec("INSERT INTO USER_EMOTIONS (ID_USER, ID_EMOTION, EMOTION_DATE) VALUES ({$this->userId}, $e1, '2026-01-01 10:00:00')");
        $this->conn->exec("INSERT INTO USER_EMOTIONS (ID_USER, ID_EMOTION, EMOTION_DATE) VALUES ({$this->userId}, $e2, '2026-01-02 10:00:00')");

        $stmt = $this->conn->prepare("
            SELECT ue.EMOTION_DATE, e.EMOTION_NAME
            FROM USER_EMOTIONS ue
            JOIN EMOTIONS e ON e.ID_EMOTION = ue.ID_EMOTION
            WHERE ue.ID_USER = :u
            ORDER BY ue.EMOTION_DATE DESC
        ");
        $stmt->execute([':u' => $this->userId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(2, $results);
        $this->assertEquals('Sad', $results[0]['EMOTION_NAME']);   // Most recent first
        $this->assertEquals('Happy', $results[1]['EMOTION_NAME']);
    }

    public function testRecommendationHistoryWithJoins(): void
    {
        $emotionId = $this->createEmotion('Tired');
        $foodId = $this->createFood('Coffee', 'Beverage', 5);

        $this->conn->exec("INSERT INTO RECOMMENDATIONS (ID_USER, ID_FOOD, ID_EMOTION, BENEFIT, RECOMMENDATION_DATE) VALUES ({$this->userId}, $foodId, $emotionId, 'Energy boost', '2026-01-05 08:00:00')");

        $stmt = $this->conn->prepare("
            SELECT f.FOOD_NAME, f.CALORIES, f.CATEGORY, r.BENEFIT, r.RECOMMENDATION_DATE, e.EMOTION_NAME
            FROM RECOMMENDATIONS r
            JOIN FOODS f ON f.ID_FOOD = r.ID_FOOD
            JOIN EMOTIONS e ON e.ID_EMOTION = r.ID_EMOTION
            WHERE r.ID_USER = :u
            ORDER BY r.RECOMMENDATION_DATE DESC
        ");
        $stmt->execute([':u' => $this->userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('Coffee', $result['FOOD_NAME']);
        $this->assertEquals(5, $result['CALORIES']);
        $this->assertEquals('Beverage', $result['CATEGORY']);
        $this->assertEquals('Energy boost', $result['BENEFIT']);
        $this->assertEquals('Tired', $result['EMOTION_NAME']);
    }

    public function testEmptyHistoryReturnsNoRows(): void
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM RECOMMENDATIONS WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(0, $count);
    }

    public function testHistoryCountsMatch(): void
    {
        $e1 = $this->createEmotion('Happy');
        $f1 = $this->createFood('Banana');

        // 3 emotions, 2 recommendations
        for ($i = 0; $i < 3; $i++) {
            $this->conn->exec("INSERT INTO USER_EMOTIONS (ID_USER, ID_EMOTION) VALUES ({$this->userId}, $e1)");
        }
        for ($i = 0; $i < 2; $i++) {
            $this->conn->exec("INSERT INTO RECOMMENDATIONS (ID_USER, ID_FOOD, ID_EMOTION) VALUES ({$this->userId}, $f1, $e1)");
        }

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM USER_EMOTIONS WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $this->assertEquals(3, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM RECOMMENDATIONS WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $this->assertEquals(2, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);
    }

    public function testHistoryOnlyShowsCurrentUser(): void
    {
        $otherUser = $this->createUser('Other', 'other@test.com');
        $emotionId = $this->createEmotion('Happy');

        $this->conn->exec("INSERT INTO USER_EMOTIONS (ID_USER, ID_EMOTION) VALUES ({$this->userId}, $emotionId)");
        $this->conn->exec("INSERT INTO USER_EMOTIONS (ID_USER, ID_EMOTION) VALUES ($otherUser, $emotionId)");

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM USER_EMOTIONS WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $this->assertEquals(1, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);
    }
}
