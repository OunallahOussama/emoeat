<?php
namespace Tests\Models;

use App\Models\Recommendation;
use Tests\BaseTestCase;

class RecommendationTest extends BaseTestCase
{
    private Recommendation $recommendation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recommendation = new Recommendation($this->pdo);
    }

    public function testCountByUser(): void
    {
        $stmt = $this->mockStatement([['C' => 7]]);
        $this->expectPrepare($stmt);

        $this->assertEquals(7, $this->recommendation->countByUser(1));
    }

    public function testGetRecentByUserReturnsLimited(): void
    {
        $rows = [
            ['FOOD_NAME' => 'Banana', 'EMOTION_NAME' => 'Happy', 'RECOMMENDATION_DATE' => '2024-01-05'],
            ['FOOD_NAME' => 'Apple', 'EMOTION_NAME' => 'Sad', 'RECOMMENDATION_DATE' => '2024-01-04'],
        ];
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->method('fetch')->willReturnOnConsecutiveCalls($rows[0], $rows[1], false);
        $this->expectPrepare($stmt);

        $result = $this->recommendation->getRecentByUser(1, 5);
        $this->assertCount(2, $result);
        $this->assertEquals('Banana', $result[0]['FOOD_NAME']);
    }

    public function testGetHistoryByUser(): void
    {
        $rows = [
            ['FOOD_NAME' => 'Banana', 'CALORIES' => 89, 'CATEGORY' => 'Fruit',
             'BENEFIT' => 'Energy', 'RECOMMENDATION_DATE' => '2024-01-05', 'EMOTION_NAME' => 'Happy'],
        ];
        $stmt = $this->mockStatement($rows);
        $this->expectPrepare($stmt);

        $result = $this->recommendation->getHistoryByUser(1);
        $this->assertCount(1, $result);
    }

    public function testSaveExecutes(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->expects($this->once())->method('execute')->willReturn(true);
        $this->expectPrepare($stmt);

        $this->recommendation->save(1, 2, 'Good for energy', 1);
    }
}
