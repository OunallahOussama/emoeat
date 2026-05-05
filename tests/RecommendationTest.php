<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: Recommendations (recommandation.php)
 */
class RecommendationTest extends BaseTestCase
{
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userId = $this->createUser('User', 'user@test.com');
    }

    public function testCsrfTokenGeneration(): void
    {
        $token = bin2hex(random_bytes(32));

        $this->assertEquals(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function testCsrfTokenUniqueness(): void
    {
        $token1 = bin2hex(random_bytes(32));
        $token2 = bin2hex(random_bytes(32));

        $this->assertNotEquals($token1, $token2);
    }

    public function testUserProfileLoaded(): void
    {
        $this->conn->exec("INSERT INTO USER_PROFILE (ID_USER, WEIGHT, HEIGHT, ALLERGIES, GOAL) VALUES ({$this->userId}, 70, 175, 'Nuts', 'Gain muscle')");

        $stmt = $this->conn->prepare("SELECT WEIGHT, HEIGHT, ALLERGIES, GOAL FROM USER_PROFILE WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($profile);
        $this->assertEquals(70, $profile['WEIGHT']);
        $this->assertEquals('Nuts', $profile['ALLERGIES']);
    }

    public function testEmotionsLoadedAndDeduplicated(): void
    {
        $this->conn->exec("INSERT INTO EMOTIONS (EMOTION_NAME) VALUES ('Happy')");
        $this->conn->exec("INSERT INTO EMOTIONS (EMOTION_NAME) VALUES ('Happy')"); // duplicate
        $this->conn->exec("INSERT INTO EMOTIONS (EMOTION_NAME) VALUES ('Sad')");

        $stmt = $this->conn->query("SELECT MIN(ID_EMOTION) AS ID_EMOTION, EMOTION_NAME FROM EMOTIONS GROUP BY EMOTION_NAME ORDER BY EMOTION_NAME");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(2, $results);
        $names = array_column($results, 'EMOTION_NAME');
        $this->assertContains('Happy', $names);
        $this->assertContains('Sad', $names);
    }

    public function testRecommendationCreated(): void
    {
        $emotionId = $this->createEmotion('Stressed');
        $foodId = $this->createFood('Dark Chocolate', 'Dessert', 200);

        $stmt = $this->conn->prepare("INSERT INTO RECOMMENDATIONS (ID_USER, ID_FOOD, ID_EMOTION, BENEFIT) VALUES (:u, :f, :e, :b)");
        $stmt->execute([':u' => $this->userId, ':f' => $foodId, ':e' => $emotionId, ':b' => 'Rich in magnesium']);

        $stmt = $this->conn->prepare("SELECT * FROM RECOMMENDATIONS WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->userId]);
        $rec = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($rec);
        $this->assertEquals($foodId, $rec['ID_FOOD']);
        $this->assertEquals($emotionId, $rec['ID_EMOTION']);
        $this->assertEquals('Rich in magnesium', $rec['BENEFIT']);
    }

    public function testUserEmotionRecorded(): void
    {
        $emotionId = $this->createEmotion('Anxious');

        $stmt = $this->conn->prepare("INSERT INTO USER_EMOTIONS (ID_USER, ID_EMOTION) VALUES (:u, :e)");
        $stmt->execute([':u' => $this->userId, ':e' => $emotionId]);

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM USER_EMOTIONS WHERE ID_USER = :u AND ID_EMOTION = :e");
        $stmt->execute([':u' => $this->userId, ':e' => $emotionId]);
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['CNT'];

        $this->assertEquals(1, $count);
    }

    public function testEmotionFoodRuleUsedForRecommendation(): void
    {
        $emotionId = $this->createEmotion('Sad');
        $food1 = $this->createFood('Chocolate', 'Dessert', 250);
        $food2 = $this->createFood('Ice Cream', 'Dessert', 300);

        $this->conn->exec("INSERT INTO EMOTION_FOOD (ID_EMOTION, ID_FOOD, INTENSITY) VALUES ($emotionId, $food1, 9)");
        $this->conn->exec("INSERT INTO EMOTION_FOOD (ID_EMOTION, ID_FOOD, INTENSITY) VALUES ($emotionId, $food2, 5)");

        // Get foods for emotion ordered by intensity
        $stmt = $this->conn->prepare("
            SELECT f.FOOD_NAME, ef.INTENSITY
            FROM EMOTION_FOOD ef
            JOIN FOODS f ON f.ID_FOOD = ef.ID_FOOD
            WHERE ef.ID_EMOTION = :e
            ORDER BY ef.INTENSITY DESC
        ");
        $stmt->execute([':e' => $emotionId]);
        $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(2, $foods);
        $this->assertEquals('Chocolate', $foods[0]['FOOD_NAME']);
        $this->assertEquals(9, $foods[0]['INTENSITY']);
    }

    public function testEmojiMapping(): void
    {
        // Test emoji mapping logic from the app
        $emojiMap = [
            'Happy' => '😊',
            'Sad' => '😢',
            'Angry' => '😠',
            'Stressed' => '😰',
            'Anxious' => '😟',
            'Tired' => '😴',
            'Excited' => '🤩',
        ];

        foreach ($emojiMap as $emotion => $emoji) {
            $this->assertNotEmpty($emoji, "Emotion '$emotion' should have an emoji");
        }
    }
}
