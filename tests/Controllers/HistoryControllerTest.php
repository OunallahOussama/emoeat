<?php
namespace Tests\Controllers;

use App\Controllers\HistoryController;
use PHPUnit\Framework\TestCase;

class HistoryControllerTest extends TestCase
{
    public function testEmoEmojiHappy(): void
    {
        $this->assertEquals('😊', HistoryController::emoEmoji('Happy'));
    }

    public function testEmoEmojiSad(): void
    {
        $this->assertEquals('😢', HistoryController::emoEmoji('Sad'));
    }

    public function testEmoEmojiAngry(): void
    {
        $this->assertEquals('😠', HistoryController::emoEmoji('Angry'));
    }

    public function testEmoEmojiStress(): void
    {
        $this->assertEquals('😰', HistoryController::emoEmoji('Stress'));
    }

    public function testEmoEmojiStressed(): void
    {
        $this->assertEquals('😰', HistoryController::emoEmoji('Stressed'));
    }

    public function testEmoEmojiTired(): void
    {
        $this->assertEquals('😴', HistoryController::emoEmoji('Tired'));
    }

    public function testEmoEmojiUnknown(): void
    {
        $this->assertEquals('😶', HistoryController::emoEmoji('Unknown'));
    }

    public function testEmoEmojiCaseInsensitive(): void
    {
        $this->assertEquals('😊', HistoryController::emoEmoji('HAPPY'));
        $this->assertEquals('😢', HistoryController::emoEmoji('sAd'));
    }
}
