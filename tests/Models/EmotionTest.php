<?php
namespace Tests\Models;

use App\Models\Emotion;
use Tests\BaseTestCase;

class EmotionTest extends BaseTestCase
{
    private Emotion $emotion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emotion = new Emotion($this->pdo);
    }

    public function testFindByIdReturnsEmotion(): void
    {
        $expected = ['ID_EMOTION' => 1, 'EMOTION_NAME' => 'Happy', 'DESCRIPTION' => 'Feeling good'];
        $stmt = $this->mockStatement([$expected]);
        $this->expectPrepare($stmt);

        $result = $this->emotion->findById(1);
        $this->assertEquals($expected, $result);
    }

    public function testFindByIdReturnsNull(): void
    {
        $stmt = $this->mockStatement([]);
        $this->expectPrepare($stmt);

        $result = $this->emotion->findById(999);
        $this->assertNull($result);
    }

    public function testGetAllReturnsList(): void
    {
        $data = [
            ['ID_EMOTION' => 1, 'EMOTION_NAME' => 'Happy', 'DESCRIPTION' => 'Joy'],
            ['ID_EMOTION' => 2, 'EMOTION_NAME' => 'Sad', 'DESCRIPTION' => 'Sorrow'],
        ];
        $stmt = $this->mockStatement($data);
        $this->expectPrepare($stmt);

        $result = $this->emotion->getAll();
        $this->assertCount(2, $result);
    }

    public function testGetGroupedReturnsMinIds(): void
    {
        $data = [
            ['ID_EMOTION' => 1, 'EMOTION_NAME' => 'Happy'],
            ['ID_EMOTION' => 3, 'EMOTION_NAME' => 'Sad'],
        ];
        $stmt = $this->mockStatement($data);
        $this->expectPrepare($stmt);

        $result = $this->emotion->getGrouped();
        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['ID_EMOTION']);
    }

    public function testCreateExecutes(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->expects($this->once())->method('execute')->willReturn(true);
        $this->expectPrepare($stmt);

        $this->emotion->create('Excited', 'High energy');
    }

    public function testDeleteCascadesFourTables(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->expects($this->exactly(4))->method('execute')->willReturn(true);

        $this->pdo->expects($this->exactly(4))->method('prepare')->willReturn($stmt);

        $this->emotion->delete(1);
    }

    public function testAddRuleExecutes(): void
    {
        $stmt = $this->createMock(\PDOStatement::class);
        $stmt->method('bindParam')->willReturn(true);
        $stmt->expects($this->once())->method('execute')->willReturn(true);
        $this->expectPrepare($stmt);

        $this->emotion->addRule(1, 2, 5);
    }

    public function testGetRulesReturnsList(): void
    {
        $rules = [
            ['ID_RULE' => 1, 'INTENSITY' => 5, 'EMOTION_NAME' => 'Happy', 'FOOD_NAME' => 'Banana'],
        ];
        $stmt = $this->mockStatement($rules);
        $this->expectPrepare($stmt);

        $result = $this->emotion->getRules();
        $this->assertCount(1, $result);
        $this->assertEquals('Happy', $result[0]['EMOTION_NAME']);
    }

    public function testGetFoodsForEmotionReturnsList(): void
    {
        $foods = [
            ['id_food' => 1, 'food_name' => 'Banana', 'calories' => 89, 'category' => 'Fruit',
             'protein' => 1, 'carbs' => 23, 'fat' => 0, 'benefit' => 'Energy boost', 'score' => 8],
        ];
        $stmt = $this->mockStatement($foods);
        $this->expectPrepare($stmt);

        $result = $this->emotion->getFoodsForEmotion(1);
        $this->assertCount(1, $result);
        $this->assertEquals('Banana', $result[0]['food_name']);
    }

    public function testCountAll(): void
    {
        $stmt = $this->mockStatement([['C' => 10]]);
        $this->expectPrepare($stmt);

        $this->assertEquals(10, $this->emotion->countAll());
    }
}
