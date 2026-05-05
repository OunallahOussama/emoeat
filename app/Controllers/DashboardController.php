<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Recommendation;
use App\Models\UserEmotion;
use App\Models\UserProfile;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $userId = $this->getUserId();
        $name = $this->getUserName();
        $role = $this->getUserRole();

        $recModel = new Recommendation($this->db);
        $emoModel = new UserEmotion($this->db);
        $profileModel = new UserProfile($this->db);

        $cntRec = $recModel->countByUser($userId);
        $cntEmo = $emoModel->countByUser($userId);
        $hasProfile = $profileModel->hasProfile($userId);
        $recent = $recModel->getRecentByUser($userId, 5);

        $this->view('dashboard/index', [
            'name' => $name,
            'role' => $role,
            'cntRec' => $cntRec,
            'cntEmo' => $cntEmo,
            'hasProfile' => $hasProfile,
            'recent' => $recent,
        ]);
    }
}
