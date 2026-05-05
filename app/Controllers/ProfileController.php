<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserProfile;
use App\Models\User;
use App\Models\ActivityLog;

class ProfileController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $userId = $this->getUserId();
        $profileModel = new UserProfile($this->db);
        $userModel = new User($this->db);

        $profile = $profileModel->findByUser($userId);
        $userInfo = $userModel->findById($userId);

        $message = '';
        $msg_type = 'success';
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            $msg_type = $_SESSION['msg_type'] ?? 'success';
            unset($_SESSION['message'], $_SESSION['msg_type']);
        }

        // BMI calculation
        $bmi = null;
        $bmi_label = '';
        $bmi_class = '';
        $w = (float)($profile['WEIGHT'] ?? 0);
        $h = (float)($profile['HEIGHT'] ?? 0);
        if ($w > 0 && $h > 0) {
            $hm = $h / 100;
            $bmi = round($w / ($hm * $hm), 1);
            if ($bmi < 18.5) { $bmi_label = 'Insuffisance pondérale'; $bmi_class = 'bmi-under'; }
            elseif ($bmi < 25) { $bmi_label = 'Poids normal ✓'; $bmi_class = 'bmi-normal'; }
            elseif ($bmi < 30) { $bmi_label = 'Surpoids'; $bmi_class = 'bmi-over'; }
            else { $bmi_label = 'Obésité'; $bmi_class = 'bmi-obese'; }
        }

        $this->view('profile/index', [
            'profile' => $profile,
            'userInfo' => $userInfo,
            'message' => $message,
            'msg_type' => $msg_type,
            'bmi' => $bmi,
            'bmi_label' => $bmi_label,
            'bmi_class' => $bmi_class,
            'name' => $this->getUserName(),
            'role' => $this->getUserRole(),
        ]);
    }

    public function save(): void
    {
        $this->requireAuth();

        $userId = $this->getUserId();
        $weight = (float)($_POST['weight'] ?? 0);
        $height = (float)($_POST['height'] ?? 0);
        $allergies = trim($_POST['allergies'] ?? '');
        $goal = trim($_POST['goal'] ?? '');

        if ($weight <= 0 || $height <= 0) {
            $_SESSION['message'] = "Veuillez entrer un poids et une taille valides.";
            $_SESSION['msg_type'] = "danger";
        } else {
            $profileModel = new UserProfile($this->db);
            $profileModel->save($userId, $weight, $height, $allergies, $goal);

            $activityLog = new ActivityLog($this->db);
            $activityLog->log($userId, 'PROFILE_UPDATED');

            $_SESSION['message'] = "Profil nutritionnel sauvegardé avec succès !";
            $_SESSION['msg_type'] = "success";
        }

        $this->redirect('/profile');
    }
}
