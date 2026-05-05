<?php
namespace Tests\Controllers;

use App\Controllers\RecommendationController;
use PHPUnit\Framework\TestCase;

class RecommendationControllerTest extends TestCase
{
    public function testEmoEmojiReturnsExpectedEmoji(): void
    {
        $this->assertEquals('😊', RecommendationController::emoEmoji('Happy'));
        $this->assertEquals('😢', RecommendationController::emoEmoji('Sad'));
        $this->assertEquals('😠', RecommendationController::emoEmoji('Angry'));
        $this->assertEquals('😟', RecommendationController::emoEmoji('Anxious'));
        $this->assertEquals('😴', RecommendationController::emoEmoji('Tired'));
        $this->assertEquals('😰', RecommendationController::emoEmoji('Stress'));
    }

    public function testEmoEmojiReturnsDefaultForUnknown(): void
    {
        $this->assertEquals('😶', RecommendationController::emoEmoji('UnknownEmotion'));
    }

    public function testEmoLabelMapsCorrectly(): void
    {
        $this->assertEquals('Joyeux', RecommendationController::emoLabel('Happy'));
        $this->assertEquals('Triste', RecommendationController::emoLabel('Sad'));
        $this->assertEquals('En colère', RecommendationController::emoLabel('Angry'));
        $this->assertEquals('Anxieux', RecommendationController::emoLabel('Anxious'));
        $this->assertEquals('Fatigué', RecommendationController::emoLabel('Tired'));
    }

    public function testEmoLabelReturnsUcfirstForUnknown(): void
    {
        $this->assertEquals('Customlabel', RecommendationController::emoLabel('CustomLabel'));
    }

    public function testFoodEmojiReturnsExpectedEmoji(): void
    {
        $this->assertEquals('🍌', RecommendationController::foodEmoji('Banane'));
        $this->assertEquals('🍫', RecommendationController::foodEmoji('Chocolat noir'));
        $this->assertEquals('🥑', RecommendationController::foodEmoji('Avocat'));
    }

    public function testFoodEmojiReturnsCategoryEmoji(): void
    {
        $this->assertEquals('🍎', RecommendationController::foodEmoji('Unknown', 'Fruit'));
        $this->assertEquals('🥦', RecommendationController::foodEmoji('Unknown', 'Vegetable'));
    }

    public function testFoodEmojiReturnsDefaultForUnknown(): void
    {
        $this->assertEquals('🍽', RecommendationController::foodEmoji('Something', ''));
    }

    public function testGetFoodImageReturnsImagePath(): void
    {
        $result = RecommendationController::getFoodImage('Chocolat', 'Dessert');
        $this->assertStringStartsWith('/', $result);
    }
}
