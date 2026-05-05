<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Recommendation;
use App\Models\UserEmotion;

class HistoryController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $userId = $this->getUserId();

        $emoModel = new UserEmotion($this->db);
        $recModel = new Recommendation($this->db);

        $emoHistory = $emoModel->getHistoryByUser($userId);
        $history = $recModel->getHistoryByUser($userId);

        $this->view('history/index', [
            'emoHistory' => $emoHistory,
            'history' => $history,
        ]);
    }

    public static function emoEmoji(string $name): string
    {
        $map = [
            'happy' => '😊', 'sad' => '😢', 'angry' => '😠', 'stress' => '😰',
            'stressed' => '😰', 'excited' => '🤩', 'anxious' => '😟', 'calm' => '😌',
            'tired' => '😴', 'fear' => '😱', 'joy' => '😄',
        ];
        return $map[strtolower(trim($name))] ?? '😶';
    }
}
