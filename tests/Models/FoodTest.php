<?php
namespace Tests\Models;

use App\Models\Food;
use Tests\BaseTestCase;

class FoodTest extends BaseTestCase
{
    private Food $food;

    protected function setUp(): void
    {
        parent::setUp();
        $this->food = new Food($this->pdo);
    }

    public function testFindByIdReturnsFood(): void
    {
        $expected = ['ID_FOOD' => 1, 'FOOD_NAME' => 'Banana', 'CATEGORY' => 'Fruit'];
        $stmt = $this->mockStatement([$expected]);
        $this->expectPrepare($stmt);

        $result = $this->food->findById(1);
        $this->assertEquals($expected, $result);
    }

    public function testFindByIdReturnsNull(): void
    {
        $stmt = $this->mockStatement([]);
        $this->expectPrepare($stmt);

        $result = $this->food->findById(999);
        $this->assertNull($result);
    }

    public function testCreateExecutes(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->expects($this->once())->method('execute')->willReturn(true);

        $this->expectPrepare($stmt);

        $this->food->create('Apple', 'Fruit', 95, 0.5, 25.0, 0.3, 'Rich in fiber');
    }

    public function testDeleteCascades(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->expects($this->exactly(3))->method('execute')->willReturn(true);

        $this->pdo->expects($this->exactly(3))->method('prepare')->willReturn($stmt);

        $this->food->delete(1);
    }

    public function testSearchWithQuery(): void
    {
        $foods = [
            ['ID_FOOD' => 1, 'FOOD_NAME' => 'Banana', 'CATEGORY' => 'Fruit', 'CALORIES' => 89, 'PROTEIN' => 1, 'CARBS' => 23, 'FAT' => 0, 'DESCRIPTION' => ''],
        ];
        $stmt = $this->mockStatement($foods);
        $this->expectPrepare($stmt);

        $result = $this->food->search('banana');
        $this->assertCount(1, $result);
        $this->assertEquals('Banana', $result[0]['FOOD_NAME']);
    }

    public function testSearchWithoutQuery(): void
    {
        $foods = [
            ['ID_FOOD' => 1, 'FOOD_NAME' => 'Apple'],
            ['ID_FOOD' => 2, 'FOOD_NAME' => 'Banana'],
        ];
        $stmt = $this->mockStatement($foods);
        $this->expectPrepare($stmt);

        $result = $this->food->search('');
        $this->assertCount(2, $result);
    }

    public function testGetAllReturnsList(): void
    {
        $foods = [
            ['ID_FOOD' => 1, 'FOOD_NAME' => 'Apple', 'CATEGORY' => 'Fruit'],
            ['ID_FOOD' => 2, 'FOOD_NAME' => 'Rice', 'CATEGORY' => 'Grain'],
        ];
        $stmt = $this->mockStatement($foods);
        $this->expectPrepare($stmt);

        $result = $this->food->getAll();
        $this->assertCount(2, $result);
    }

    public function testCountAll(): void
    {
        $stmt = $this->mockStatement([['C' => 15]]);
        $this->expectPrepare($stmt);

        $this->assertEquals(15, $this->food->countAll());
    }
}
