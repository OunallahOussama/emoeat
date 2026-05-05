<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Emotion;
use App\Models\Recommendation;
use App\Models\UserEmotion;
use App\Models\UserProfile;
use App\Models\ActivityLog;

class RecommendationController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $userId = $this->getUserId();

        // CSRF token
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $csrf_token = $_SESSION['csrf_token'];

        $profileModel = new UserProfile($this->db);
        $emotionModel = new Emotion($this->db);

        $profile = $profileModel->findByUser($userId);
        $rawEmotions = $emotionModel->getGrouped();

        // Deduplicate emotions
        $emotions = [];
        $seenLabels = [];
        foreach ($rawEmotions as $em) {
            $label = self::emoLabel($em['EMOTION_NAME']);
            if (!isset($seenLabels[$label])) {
                $seenLabels[$label] = true;
                $emotions[] = $em;
            }
        }

        $this->view('recommendation/index', [
            'csrf_token' => $csrf_token,
            'profile' => $profile,
            'emotions' => $emotions,
            'results' => [],
            'selected_emotion_id' => null,
            'selected_emotion_nm' => '',
            'filter_info' => '',
            'db_error' => '',
            'save_success' => false,
        ]);
    }

    public function getRecommendation(): void
    {
        $this->requireAuth();

        $userId = $this->getUserId();

        // CSRF validation
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
            die("Requête invalide.");
        }

        $profileModel = new UserProfile($this->db);
        $emotionModel = new Emotion($this->db);

        $profile = $profileModel->findByUser($userId);
        $rawEmotions = $emotionModel->getGrouped();

        $emotions = [];
        $seenLabels = [];
        foreach ($rawEmotions as $em) {
            $label = self::emoLabel($em['EMOTION_NAME']);
            if (!isset($seenLabels[$label])) {
                $seenLabels[$label] = true;
                $emotions[] = $em;
            }
        }

        $csrf_token = $_SESSION['csrf_token'];
        $results = [];
        $selected_emotion_id = null;
        $selected_emotion_nm = '';
        $filter_info = '';
        $db_error = '';
        $save_success = false;

        if (isset($_POST['save_selection'])) {
            $sEmoId = (int)($_POST['emotion_id'] ?? 0);
            $sFoodIds = array_values(array_filter(array_map('intval', $_POST['selected_foods'] ?? [])));

            if ($sEmoId > 0 && !empty($sFoodIds)) {
                $saveKey = 'reco_saved_' . $userId . '_' . $sEmoId;
                if (empty($_SESSION[$saveKey])) {
                    $_SESSION[$saveKey] = true;

                    $userEmoModel = new UserEmotion($this->db);
                    $userEmoModel->save($userId, $sEmoId);

                    $recModel = new Recommendation($this->db);
                    $stFd = $this->db->prepare("SELECT description FROM FOODS WHERE id_food = :id");
                    foreach ($sFoodIds as $fid) {
                        $stFd->execute([':id' => $fid]);
                        $fdRow = $stFd->fetch(\PDO::FETCH_ASSOC);
                        $benefit = !empty($fdRow['DESCRIPTION'] ?? $fdRow['description'] ?? '') ? ($fdRow['DESCRIPTION'] ?? $fdRow['description']) : 'Recommandé pour votre état émotionnel.';
                        $recModel->save($sEmoId, $fid, $benefit, $userId);
                    }

                    $activityLog = new ActivityLog($this->db);
                    $activityLog->log($userId, 'RECOMMENDATION_SAVED');
                }
                $save_success = true;
                $selected_emotion_id = $sEmoId;

                $eRow = $emotionModel->findById($sEmoId);
                $selected_emotion_nm = $eRow['EMOTION_NAME'] ?? '';
                $results = $emotionModel->getFoodsForEmotion($sEmoId);
            }
        } elseif (isset($_POST['get_reco']) && !empty($_POST['emotion'])) {
            $emotionId = (int)$_POST['emotion'];

            foreach ($emotions as $em) {
                $emId = (int)($em['ID_EMOTION'] ?? $em['id_emotion'] ?? 0);
                if ($emId === $emotionId) {
                    $selected_emotion_id = $emotionId;
                    $selected_emotion_nm = $em['EMOTION_NAME'] ?? $em['emotion_name'] ?? '';
                    break;
                }
            }

            if ($selected_emotion_id === null && $emotionId > 0) {
                $selected_emotion_id = $emotionId;
                $eRow = $emotionModel->findById($emotionId);
                $selected_emotion_nm = $eRow['EMOTION_NAME'] ?? '';
            }

            if ($selected_emotion_id !== null) {
                $allFoods = $emotionModel->getFoodsForEmotion($selected_emotion_id);

                $profileRow = is_array($profile) ? $profile : [];
                $goal = strtolower(trim($profileRow['GOAL'] ?? ''));
                $allergiesRaw = strtolower(trim($profileRow['ALLERGIES'] ?? ''));
                $allergyList = array_filter(array_map('trim', explode(',', $allergiesRaw)));
                $filters = [];

                foreach ($allFoods as $row) {
                    $foodName = strtolower($row['FOOD_NAME'] ?? $row['food_name'] ?? '');
                    $calories = (int)($row['CALORIES'] ?? $row['calories'] ?? 0);

                    $blocked = false;
                    foreach ($allergyList as $allergen) {
                        if ($allergen !== '' && strpos($foodName, $allergen) !== false) {
                            $filters[] = "allergie ($allergen)";
                            $blocked = true;
                            break;
                        }
                    }
                    if ($blocked) continue;

                    if ($goal === 'perte de poids' && $calories > 300) {
                        $filters[] = "objectif perte de poids (>300 cal)";
                        continue;
                    }
                    $results[] = $row;
                }

                if (!empty($filters)) {
                    $filter_info = "Filtres appliqués : " . implode(', ', array_unique($filters)) . ".";
                }
            }
        }

        $this->view('recommendation/index', [
            'csrf_token' => $csrf_token,
            'profile' => $profile,
            'emotions' => $emotions,
            'results' => $results,
            'selected_emotion_id' => $selected_emotion_id,
            'selected_emotion_nm' => $selected_emotion_nm,
            'filter_info' => $filter_info,
            'db_error' => $db_error,
            'save_success' => $save_success,
        ]);
    }

    public static function emoEmoji(string $name): string
    {
        $map = [
            'happy' => '😊', 'sad' => '😢', 'angry' => '😠',
            'stress' => '😰', 'stressed' => '😰', 'excited' => '🤩',
            'anxious' => '😟', 'calm' => '😌', 'tired' => '😴',
            'fear' => '😱', 'joy' => '😄', 'love' => '❤️',
            'frustrated' => '😤', 'bored' => '😑', 'nervous' => '😬',
            'exhausted' => '😵', 'sick' => '🤒', 'sleepy' => '😪'
        ];
        return $map[strtolower(trim($name))] ?? '😶';
    }

    public static function emoLabel(string $name): string
    {
        $map = [
            'happy' => 'Joyeux', 'sad' => 'Triste',
            'angry' => 'En colère', 'stress' => 'Stressé',
            'stressed' => 'Stressé', 'excited' => 'Excité',
            'anxious' => 'Anxieux', 'calm' => 'Calme',
            'tired' => 'Fatigué', 'fear' => 'Apeuré',
            'joy' => 'Joyeux', 'love' => 'Amoureux',
            'frustrated' => 'Frustré', 'bored' => 'Ennuyé',
            'nervous' => 'Nerveux', 'exhausted' => 'Épuisé',
            'sick' => 'Malade', 'sleepy' => 'Somnolent',
        ];
        return $map[strtolower(trim($name))] ?? ucfirst(strtolower(trim($name)));
    }

    public static function foodEmoji(string $name, string $cat = ''): string
    {
        $nm = strtolower($name);
        $keywords = [
            'banane' => '🍌', 'pomme' => '🍎', 'orange' => '🍊', 'chocolat' => '🍫',
            'salade' => '🥗', 'riz' => '🍚', 'poulet' => '🍗', 'saumon' => '🐟',
            'soupe' => '🥣', 'pain' => '🍞', 'noix' => '🥜', 'lentilles' => '🫘',
            'oeuf' => '🥚', 'pâtes' => '🍝', 'avocat' => '🥑', 'légumes' => '🥦',
            'thé' => '🍵', 'café' => '☕', 'eau' => '💧', 'jus' => '🥤',
            'smoothie' => '🥤'
        ];
        foreach ($keywords as $kw => $em) {
            if (strpos($nm, $kw) !== false) return $em;
        }
        $cats = ['fruit' => '🍎', 'vegetable' => '🥦', 'dairy' => '🥛', 'grain' => '🌾', 'protein' => '🥩', 'dessert' => '🍰', 'beverage' => '🥤'];
        return $cats[strtolower($cat)] ?? '🍽';
    }

    public static function getFoodImage(string $name, string $cat): string
    {
        $nm = strtolower($name);
        $localMap = [
            'flocon' => '/images/Berry Bliss Smoothie Bowl.jpg',
            'avoine' => '/images/Berry Bliss Smoothie Bowl.jpg',
            'lentille' => '/images/Irresistible Best Lentil Soup for a Cozy, Hearty Dinner.jpg',
            'escalope' => '/images/Escalopes de dinde panées - Recette Traditionelle.jpg',
            'dinde' => '/images/Escalopes de dinde panées - Recette Traditionelle.jpg',
            'ground beef' => '/images/Ground Beef Hot Honey Bowl.jpg',
            'hot honey' => '/images/Ground Beef Hot Honey Bowl.jpg',
            'patate' => '/images/Ground Beef Hot Honey Bowl.jpg',
            'chocolat' => '/images/Chocolate Sauce.jpg',
            'cacao' => '/images/Chocolate Sauce.jpg',
            'miel' => '/images/Homemade Honey Syrup_ Sweet, Simple, and So Useful!.jpg',
            'honey' => '/images/Homemade Honey Syrup_ Sweet, Simple, and So Useful!.jpg',
            'pizza' => '/images/download (10).jpg',
            'jus' => '/images/download (11).jpg',
            'orange' => '/images/download (11).jpg',
            'pate' => '/images/pasta.jpg',
            'pasta' => '/images/pasta.jpg',
            'fruit sec' => '/images/fruit sec.jpg',
            'noix' => '/images/fruit sec.jpg',
            'amande' => '/images/fruit sec.jpg',
            'menthe' => '/images/Tisane menthe.jpg',
            'the vert' => '/images/Tisane menthe.jpg',
            'thé vert' => '/images/Tisane menthe.jpg',
            'camomille' => '/images/Tisane camomille.jpg',
            'gingembre' => '/images/Tisane camomille.jpg',
            'tisane' => '/images/Tisane camomille.jpg',
        ];
        foreach ($localMap as $kw => $path) {
            if (strpos($nm, $kw) !== false) return $path;
        }

        $keywords = [
            'banane' => 'photo-1528825871115-3581a5387919',
            'pomme' => 'photo-1560806887-1e4cd0b6cbd6',
            'orange' => 'photo-1547514701-42782101795e',
            'fraise' => 'photo-1464965911861-746a04b4bca6',
            'myrtille' => 'photo-1498557850523-fd3d118b962e',
            'chocolat' => 'photo-1511381939415-e44f3c9a3d74',
            'salade' => 'photo-1512621776951-a57141f2eefd',
            'riz' => 'photo-1586201375761-83865001e31c',
            'poulet' => 'photo-1604908176997-125f25cc6f3d',
            'saumon' => 'photo-1467003909585-2f8a72700288',
            'soupe' => 'photo-1547592180-85f173990554',
            'pain' => 'photo-1509440159596-0249088772ff',
            'noix' => 'photo-1508061253366-f7da158b6d46',
            'avocat' => 'photo-1601039641847-7857b994d704',
            'smoothie' => 'photo-1490818387583-1baba5e638af',
            'jus' => 'photo-1534353436294-0dbd4bdac845',
            'oeuf' => 'photo-1582169505937-b9992bd01695',
            'lentilles' => 'photo-1515543904379-3d757afe72e4',
            'brocoli' => 'photo-1459411621453-7b03977f4bfc',
            'épinard' => 'photo-1576045057995-568f588f82fb',
            'carotte' => 'photo-1598170845058-32b9d6a5da37',
            'tomate' => 'photo-1546094096-0df4bcaaa337',
            'concombre' => 'photo-1604977042946-1eecc30f269e',
            'amande' => 'photo-1508061253366-f7da158b6d46',
            'yaourt' => 'photo-1571212515416-fef01fc43637',
            'lait' => 'photo-1563636619-e9143da7973b',
        ];
        foreach ($keywords as $kw => $pid) {
            if (strpos($nm, $kw) !== false) return "https://images.unsplash.com/$pid?w=400&q=75";
        }

        $catMap = [
            'fruit' => 'photo-1490474418585-ba9bad8fd0ea',
            'vegetable' => 'photo-1540420773420-3366772f4999',
            'grain' => 'photo-1586201375761-83865001e31c',
            'protein' => 'photo-1467003909585-2f8a72700288',
            'dairy' => 'photo-1563636619-e9143da7973b',
            'dessert' => 'photo-1563805042-7684c019e1cb',
            'beverage' => 'photo-1490818387583-1baba5e638af',
            'legume' => 'photo-1515543904379-3d757afe72e4',
            'nut' => 'photo-1508061253366-f7da158b6d46',
        ];
        $pid = $catMap[strtolower($cat)] ?? 'photo-1504674900247-0877df9cc836';
        return "https://images.unsplash.com/$pid?w=400&q=75";
    }
}
