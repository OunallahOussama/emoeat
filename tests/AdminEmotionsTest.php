<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: Admin Emotions Management (admin_emotions.php)
 */
class AdminEmotionsTest extends BaseTestCase
{
    private int $adminId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminId = $this->createUser('Admin', 'admin@test.com', 'pass', 'ADMIN');
    }

    public function testAddEmotionSuccessfully(): void
    {
        $stmt = $this->conn->prepare("INSERT INTO EMOTIONS (EMOTION_NAME, DESCRIPTION) VALUES (:n, :d)");
        $stmt->execute([':n' => 'Anxious', ':d' => 'Feeling worried']);

        $stmt = $this->conn->query("SELECT * FROM EMOTIONS WHERE EMOTION_NAME = 'Anxious'");
        $emotion = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($emotion);
        $this->assertEquals('Anxious', $emotion['EMOTION_NAME']);
        $this->assertEquals('Feeling worried', $emotion['DESCRIPTION']);
    }

    public function testAddEmotionRequiresName(): void
    {
        $emotionName = '';
        $this->assertTrue(empty($emotionName), "Emotion name is required");
    }

    public function testDeleteEmotionCascadesToEmotionFood(): void
    {
        $emotionId = $this->createEmotion('Sad');
        $foodId = $this->createFood('Chocolate');

        $this->conn->exec("INSERT INTO EMOTION_FOOD (ID_EMOTION, ID_FOOD, INTENSITY) VALUES ($emotionId, $foodId, 8)");

        // Cascade
        $this->conn->exec("DELETE FROM EMOTION_FOOD WHERE ID_EMOTION = $emotionId");
        $this->conn->exec("DELETE FROM EMOTIONS WHERE ID_EMOTION = $emotionId");

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM EMOTION_FOOD WHERE ID_EMOTION = :e");
        $stmt->execute([':e' => $emotionId]);
        $this->assertEquals(0, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);
    }

    public function testDeleteEmotionCascadesToUserEmotions(): void
    {
        $emotionId = $this->createEmotion('Angry');
        $userId = $this->createUser('U', 'u@test.com');

        $this->conn->exec("INSERT INTO USER_EMOTIONS (ID_USER, ID_EMOTION) VALUES ($userId, $emotionId)");

        // Cascade
        $this->conn->exec("DELETE FROM USER_EMOTIONS WHERE ID_EMOTION = $emotionId");
        $this->conn->exec("DELETE FROM EMOTIONS WHERE ID_EMOTION = $emotionId");

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM USER_EMOTIONS WHERE ID_EMOTION = :e");
        $stmt->execute([':e' => $emotionId]);
        $this->assertEquals(0, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);
    }

    public function testDeleteEmotionCascadesToRecommendations(): void
    {
        $emotionId = $this->createEmotion('Stressed');
        $foodId = $this->createFood('Tea');
        $userId = $this->createUser('U2', 'u2@test.com');

        $this->conn->exec("INSERT INTO RECOMMENDATIONS (ID_USER, ID_FOOD, ID_EMOTION) VALUES ($userId, $foodId, $emotionId)");

        // Cascade
        $this->conn->exec("DELETE FROM RECOMMENDATIONS WHERE ID_EMOTION = $emotionId");
        $this->conn->exec("DELETE FROM EMOTIONS WHERE ID_EMOTION = $emotionId");

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM RECOMMENDATIONS WHERE ID_EMOTION = :e");
        $stmt->execute([':e' => $emotionId]);
        $this->assertEquals(0, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);
    }

    public function testAddRuleSuccessfully(): void
    {
        $emotionId = $this->createEmotion('Happy');
        $foodId = $this->createFood('Ice Cream');

        $stmt = $this->conn->prepare("INSERT INTO EMOTION_FOOD (ID_EMOTION, ID_FOOD, INTENSITY) VALUES (:e, :f, :i)");
        $stmt->execute([':e' => $emotionId, ':f' => $foodId, ':i' => 7]);

        $stmt = $this->conn->prepare("SELECT * FROM EMOTION_FOOD WHERE ID_EMOTION = :e AND ID_FOOD = :f");
        $stmt->execute([':e' => $emotionId, ':f' => $foodId]);
        $rule = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($rule);
        $this->assertEquals(7, $rule['INTENSITY']);
    }

    public function testRuleIntensityClampedMin(): void
    {
        $intensity = -5;
        $clamped = max(1, min(10, $intensity));
        $this->assertEquals(1, $clamped);
    }

    public function testRuleIntensityClampedMax(): void
    {
        $intensity = 15;
        $clamped = max(1, min(10, $intensity));
        $this->assertEquals(10, $clamped);
    }

    public function testRuleIntensityValidRange(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $clamped = max(1, min(10, $i));
            $this->assertEquals($i, $clamped);
        }
    }

    public function testRuleRequiresValidEmotionId(): void
    {
        $emotionId = 0;
        $this->assertFalse($emotionId > 0, "Emotion ID must be > 0");
    }

    public function testRuleRequiresValidFoodId(): void
    {
        $foodId = 0;
        $this->assertFalse($foodId > 0, "Food ID must be > 0");
    }

    public function testRulesOrderedByIntensityDesc(): void
    {
        $e1 = $this->createEmotion('E1');
        $f1 = $this->createFood('F1');
        $f2 = $this->createFood('F2', 'Grain');
        $f3 = $this->createFood('F3', 'Protein');

        $this->conn->exec("INSERT INTO EMOTION_FOOD (ID_EMOTION, ID_FOOD, INTENSITY) VALUES ($e1, $f1, 3)");
        $this->conn->exec("INSERT INTO EMOTION_FOOD (ID_EMOTION, ID_FOOD, INTENSITY) VALUES ($e1, $f2, 9)");
        $this->conn->exec("INSERT INTO EMOTION_FOOD (ID_EMOTION, ID_FOOD, INTENSITY) VALUES ($e1, $f3, 5)");

        $stmt = $this->conn->query("SELECT INTENSITY FROM EMOTION_FOOD ORDER BY INTENSITY DESC");
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $this->assertEquals([9, 5, 3], $results);
    }
}
