<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Food;
use App\Models\Emotion;
use App\Models\Recommendation;
use App\Models\ActivityLog;

class AdminController extends Controller
{
    private ActivityLog $activityLog;

    public function __construct()
    {
        parent::__construct();
        $this->activityLog = new ActivityLog($this->db);
    }

    public function dashboard(): void
    {
        $this->requireAdmin();

        $userModel = new User($this->db);
        $foodModel = new Food($this->db);
        $emotionModel = new Emotion($this->db);
        $recModel = new Recommendation($this->db);

        $totalUsers = $userModel->countAll();
        $totalFoods = $foodModel->countAll();
        $totalEmo = $emotionModel->countAll();
        $totalRec = $recModel->countAll();
        $users = $userModel->getRecentUsers(10);

        $this->view('dashboard/admin', [
            'admin_name' => $this->getUserName(),
            'totalUsers' => $totalUsers,
            'totalFoods' => $totalFoods,
            'totalEmo' => $totalEmo,
            'totalRec' => $totalRec,
            'users' => $users,
        ]);
    }

    public function users(): void
    {
        $this->requireAdmin();

        $userModel = new User($this->db);
        $adminId = $this->getUserId();
        $msg = '';
        $msg_type = 'success';

        if (isset($_POST['delete_user'])) {
            $delId = (int)$_POST['del_id'];
            if ($delId === $adminId) {
                $msg = "Vous ne pouvez pas supprimer votre propre compte.";
                $msg_type = 'danger';
            } else {
                $userModel->delete($delId);
                $this->activityLog->log($adminId, 'ADMIN_DELETE_USER_' . $delId);
                $msg = "Utilisateur #$delId supprimé avec succès.";
            }
        }

        if (isset($_POST['change_role'])) {
            $chId = (int)$_POST['ch_id'];
            $chRole = ($_POST['ch_role'] === 'ADMIN') ? 'ADMIN' : 'CLIENT';
            if ($chId === $adminId) {
                $msg = "Vous ne pouvez pas modifier votre propre rôle.";
                $msg_type = 'danger';
            } else {
                $userModel->updateRole($chId, $chRole);
                $this->activityLog->log($adminId, 'ADMIN_CHANGE_ROLE_' . $chId . '_TO_' . $chRole);
                $msg = "Rôle de l'utilisateur #$chId mis à jour : $chRole.";
            }
        }

        $search = trim($_GET['q'] ?? '');
        $users = $userModel->search($search);

        $this->view('admin/users', [
            'users' => $users,
            'search' => $search,
            'msg' => $msg,
            'msg_type' => $msg_type,
            'admin_id' => $adminId,
        ]);
    }

    public function usersPost(): void
    {
        $this->users();
    }

    public function foods(): void
    {
        $this->requireAdmin();

        $foodModel = new Food($this->db);
        $adminId = $this->getUserId();
        $msg = '';
        $msg_type = 'success';

        if (isset($_POST['add_food'])) {
            $fname = trim($_POST['food_name'] ?? '');
            $cat = trim($_POST['food_category'] ?? '');
            $calories = (int)($_POST['food_cal'] ?? 0);
            $protein = (int)($_POST['food_prot'] ?? 0);
            $carbs = (int)($_POST['food_carb'] ?? 0);
            $fat = (int)($_POST['food_fat'] ?? 0);
            $desc = trim($_POST['food_desc'] ?? '');

            if (empty($fname) || empty($cat)) {
                $msg = "Le nom et la catégorie sont obligatoires.";
                $msg_type = 'danger';
            } else {
                $foodModel->create($fname, $cat, $calories, $protein, $carbs, $fat, $desc);
                $this->activityLog->log($adminId, 'ADMIN_ADD_FOOD_' . $fname);
                $msg = "Aliment \"$fname\" ajouté avec succès.";
            }
        }

        if (isset($_POST['delete_food'])) {
            $delId = (int)$_POST['del_id'];
            $foodModel->delete($delId);
            $this->activityLog->log($adminId, 'ADMIN_DELETE_FOOD_' . $delId);
            $msg = "Aliment #$delId supprimé.";
        }

        $search = trim($_GET['q'] ?? '');
        $foods = $foodModel->search($search);
        $categories = ['Fruit', 'Vegetable', 'Grain', 'Protein', 'Dairy', 'Dessert', 'Beverage', 'Legume', 'Nut', 'Other'];

        $this->view('admin/foods', [
            'foods' => $foods,
            'search' => $search,
            'msg' => $msg,
            'msg_type' => $msg_type,
            'categories' => $categories,
        ]);
    }

    public function foodsPost(): void
    {
        $this->foods();
    }

    public function emotions(): void
    {
        $this->requireAdmin();

        $emotionModel = new Emotion($this->db);
        $foodModel = new Food($this->db);
        $adminId = $this->getUserId();
        $msg = '';
        $msg_type = 'success';

        if (isset($_POST['add_emotion'])) {
            $ename = trim($_POST['emo_name'] ?? '');
            if (empty($ename)) {
                $msg = "Le nom de l'émotion est obligatoire.";
                $msg_type = 'danger';
            } else {
                $emotionModel->create($ename, '');
                $this->activityLog->log($adminId, 'ADMIN_ADD_EMOTION_' . $ename);
                $msg = "Émotion \"$ename\" ajoutée avec succès.";
            }
        }

        if (isset($_POST['delete_emotion'])) {
            $delId = (int)$_POST['del_emo'];
            $emotionModel->delete($delId);
            $this->activityLog->log($adminId, 'ADMIN_DELETE_EMOTION_' . $delId);
            $msg = "Émotion #$delId supprimée.";
        }

        if (isset($_POST['add_rule'])) {
            $rEmo = (int)($_POST['rule_emo'] ?? 0);
            $rFood = (int)($_POST['rule_food'] ?? 0);
            $rInt = 5;
            if ($rEmo > 0 && $rFood > 0) {
                $emotionModel->addRule($rEmo, $rFood, $rInt);
                $this->activityLog->log($adminId, 'ADMIN_ADD_RULE_E' . $rEmo . '_F' . $rFood);
                $msg = "Règle ajoutée (émotion #$rEmo → aliment #$rFood).";
            } else {
                $msg = "Veuillez sélectionner une émotion et un aliment.";
                $msg_type = 'danger';
            }
        }

        $emotions = $emotionModel->getAll();
        $foods = $foodModel->getAll();
        $rules = $emotionModel->getRules();

        $this->view('admin/emotions', [
            'emotions' => $emotions,
            'foods' => $foods,
            'rules' => $rules,
            'msg' => $msg,
            'msg_type' => $msg_type,
        ]);
    }

    public function emotionsPost(): void
    {
        $this->emotions();
    }

    public function activityLog(): void
    {
        $this->requireAdmin();

        $logModel = new ActivityLog($this->db);
        $search = trim($_GET['q'] ?? '');
        $logs = $logModel->search($search);

        $this->view('admin/activity_log', [
            'logs' => $logs,
            'search' => $search,
        ]);
    }
}
