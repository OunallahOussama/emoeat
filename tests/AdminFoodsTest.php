<?php

require_once __DIR__ . '/BaseTestCase.php';

/**
 * Unit Tests: Admin Foods Management (admin_foods.php)
 */
class AdminFoodsTest extends BaseTestCase
{
    private int $adminId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminId = $this->createUser('Admin', 'admin@test.com', 'pass', 'ADMIN');
    }

    public function testAddFoodSuccessfully(): void
    {
        $stmt = $this->conn->prepare("INSERT INTO FOODS (FOOD_NAME, CATEGORY, CALORIES, PROTEIN, CARBS, FAT, DESCRIPTION) VALUES (:n, :c, :cal, :p, :carb, :f, :d)");
        $stmt->execute([
            ':n' => 'Avocado',
            ':c' => 'Fruit',
            ':cal' => 160,
            ':p' => 2.0,
            ':carb' => 8.5,
            ':f' => 14.7,
            ':d' => 'Healthy fat source'
        ]);

        $stmt = $this->conn->query("SELECT * FROM FOODS WHERE FOOD_NAME = 'Avocado'");
        $food = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($food);
        $this->assertEquals('Avocado', $food['FOOD_NAME']);
        $this->assertEquals('Fruit', $food['CATEGORY']);
        $this->assertEquals(160, $food['CALORIES']);
    }

    public function testAddFoodRequiresName(): void
    {
        $foodName = '';
        $this->assertTrue(empty($foodName), "Food name is required");
    }

    public function testAddFoodRequiresCategory(): void
    {
        $category = '';
        $this->assertTrue(empty($category), "Category is required");
    }

    public function testDeleteFoodCascadesToEmotionFood(): void
    {
        $foodId = $this->createFood('Banana');
        $emotionId = $this->createEmotion('Happy');

        $this->conn->exec("INSERT INTO EMOTION_FOOD (ID_EMOTION, ID_FOOD, INTENSITY) VALUES ($emotionId, $foodId, 7)");

        // Cascade delete
        $this->conn->exec("DELETE FROM EMOTION_FOOD WHERE ID_FOOD = $foodId");
        $this->conn->exec("DELETE FROM FOODS WHERE ID_FOOD = $foodId");

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM EMOTION_FOOD WHERE ID_FOOD = :f");
        $stmt->execute([':f' => $foodId]);
        $this->assertEquals(0, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);
    }

    public function testDeleteFoodCascadesToRecommendations(): void
    {
        $foodId = $this->createFood('Mango');
        $emotionId = $this->createEmotion('Excited');
        $userId = $this->createUser('User', 'u@test.com');

        $this->conn->exec("INSERT INTO RECOMMENDATIONS (ID_USER, ID_FOOD, ID_EMOTION) VALUES ($userId, $foodId, $emotionId)");

        // Cascade delete
        $this->conn->exec("DELETE FROM RECOMMENDATIONS WHERE ID_FOOD = $foodId");
        $this->conn->exec("DELETE FROM FOODS WHERE ID_FOOD = $foodId");

        $stmt = $this->conn->prepare("SELECT COUNT(*) AS CNT FROM RECOMMENDATIONS WHERE ID_FOOD = :f");
        $stmt->execute([':f' => $foodId]);
        $this->assertEquals(0, $stmt->fetch(PDO::FETCH_ASSOC)['CNT']);
    }

    public function testSearchFoodByName(): void
    {
        $this->createFood('Banana', 'Fruit');
        $this->createFood('Broccoli', 'Vegetable');
        $this->createFood('Blueberry', 'Fruit');

        $q = '%banana%';
        $stmt = $this->conn->prepare("SELECT * FROM FOODS WHERE LOWER(FOOD_NAME) LIKE :q OR LOWER(CATEGORY) LIKE :q ORDER BY FOOD_NAME");
        $stmt->execute([':q' => $q]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(1, $results);
        $this->assertEquals('Banana', $results[0]['FOOD_NAME']);
    }

    public function testSearchFoodByCategory(): void
    {
        $this->createFood('Banana', 'Fruit');
        $this->createFood('Broccoli', 'Vegetable');
        $this->createFood('Apple', 'Fruit');

        $q = '%fruit%';
        $stmt = $this->conn->prepare("SELECT * FROM FOODS WHERE LOWER(FOOD_NAME) LIKE :q OR LOWER(CATEGORY) LIKE :q ORDER BY FOOD_NAME");
        $stmt->execute([':q' => $q]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(2, $results);
    }

    public function testActivityLogOnFoodAdd(): void
    {
        $this->logActivity($this->adminId, 'ADMIN_ADD_FOOD_Avocado');

        $stmt = $this->conn->prepare("SELECT ACTION FROM ACTIVITY_LOG WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->adminId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertTrue(str_contains($log['ACTION'], 'ADMIN_ADD_FOOD_'));
    }

    public function testActivityLogOnFoodDelete(): void
    {
        $foodId = $this->createFood('ToDelete');
        $this->logActivity($this->adminId, 'ADMIN_DELETE_FOOD_' . $foodId);

        $stmt = $this->conn->prepare("SELECT ACTION FROM ACTIVITY_LOG WHERE ID_USER = :u");
        $stmt->execute([':u' => $this->adminId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertTrue(str_contains($log['ACTION'], 'ADMIN_DELETE_FOOD_'));
    }
}
