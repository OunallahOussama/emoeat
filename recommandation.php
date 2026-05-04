<?php
/* ================================================
   recommandation.php — Recommandations alimentaires
   L'utilisateur choisit son émotion et reçoit une liste
   d'aliments adaptés à son humeur du moment.
   ================================================ */
session_start();
include("connexion.php");

/* Protection : accès réservé aux utilisateurs connectés */
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* -- Jeton CSRF pour protéger le formulaire contre les attaques externes -- */
if(empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

/* -- Chargement du profil nutritionnel (poids, taille, allergies) -- */
$stmtP = $conn->prepare("SELECT weight, height, allergies, goal FROM USER_PROFILE WHERE id_user = :u");
$stmtP->bindParam(":u", $user_id);
$stmtP->execute();
$profile = $stmtP->fetch(PDO::FETCH_ASSOC);

/* -- Chargement des émotions disponibles depuis la base (une seule par nom) -- */
$emStmt = $conn->prepare("SELECT MIN(id_emotion) AS id_emotion, emotion_name FROM EMOTIONS GROUP BY emotion_name ORDER BY emotion_name");
$emStmt->execute();
$rawEmotions = $emStmt->fetchAll(PDO::FETCH_ASSOC);

/* -- Déduplication : on regroupe les émotions synonymes (happy/joy/joyeux ? une seule entrée) -- */
$emotions = [];
$seenLabels = [];
foreach($rawEmotions as $em) {
    $label = emoLabel($em['EMOTION_NAME']);
    if(!isset($seenLabels[$label])) {
        $seenLabels[$label] = true;
        $emotions[] = $em;
    }
}

/* -- Retourne l'émoji correspondant à l'émotion -- */
function emoEmoji(string $name): string {
    $map = [
        'happy'     => '😊', 'sad'      => '😢', 'angry'   => '😠',
        'stress'    => '😰', 'stressed' => '😰', 'excited' => '🤩',
        'anxious'   => '😟', 'calm'     => '😌', 'tired'   => '😴',
        'fear'      => '😱', 'joy'      => '😄', 'love'       => '❤️',
        'frustrated'=> '😤', 'bored'    => '😑', 'nervous'    => '😬',
        'exhausted' => '😵', 'sick'     => '🤒', 'sleepy'     => '😪'
    ];
    return $map[strtolower(trim($name))] ?? '😶';
}

/* -- Retourne le nom français de l'émotion -- */
function emoLabel(string $name): string {
    $map = [
        'happy'      => 'Joyeux',       'sad'        => 'Triste',
        'angry'      => 'En colère',    'stress'     => 'Stressé',
        'stressed'   => 'Stressé',      'excited'    => 'Excité',
        'anxious'    => 'Anxieux',      'calm'       => 'Calme',
        'tired'      => 'Fatigué',      'fear'       => 'Apeuré',
        'joy'        => 'Joyeux',       'love'       => 'Amoureux',
        'frustrated' => 'Frustré',      'bored'      => 'Ennuyé',
        'nervous'    => 'Nerveux',      'exhausted'  => 'Épuisé',
        'sick'       => 'Malade',       'sleepy'     => 'Somnolent',
    ];
    return $map[strtolower(trim($name))] ?? ucfirst(strtolower(trim($name)));
}

function foodEmoji(string $name, string $cat = ''): string {
    $nm = strtolower($name);
    $keywords = [
        'banane'=>'🍌','pomme'=>'🍎','orange'=>'🍊','chocolat'=>'🍫',
        'salade'=>'🥗','riz'=>'🍚','poulet'=>'🍗','saumon'=>'🐟',
        'soupe'=>'🥣','pain'=>'🍞','noix'=>'🥜','lentilles'=>'🫘',
        'oeuf'=>'🥚','pâtes'=>'🍝','avocat'=>'🥑','légumes'=>'🥦',
        'thé'=>'🍵','café'=>'☕','eau'=>'💧','jus'=>'🥤',
        'smoothie'=>'🥤'
    ];
    foreach($keywords as $kw => $em) {
        if(strpos($nm, $kw) !== false) return $em;
    }
    $cats = ['fruit'=>'🍎','vegetable'=>'🥦','dairy'=>'🥛','grain'=>'🌾','protein'=>'🥩','dessert'=>'🍰','beisson'=>'🥤'];
    return $cats[strtolower($cat)] ?? '🍽';
}

/* -- Photo Unsplash par catégorie / nom d'aliment -- */
function getFoodImage(string $name, string $cat): string {
    $nm = strtolower($name);
    // Photos locales prioritaires (images/)
    $localMap = [
        'flocon'   => 'images/Berry Bliss Smoothie Bowl.jpg',
        'avoine'   => 'images/Berry Bliss Smoothie Bowl.jpg',
        'lentille' => 'images/Irresistible Best Lentil Soup for a Cozy, Hearty Dinner.jpg',
        'escalope' => 'images/Escalopes de dinde panées - Recette Traditionelle.jpg',
        'dinde'    => 'images/Escalopes de dinde panées - Recette Traditionelle.jpg',
        'ground beef' => 'images/Ground Beef Hot Honey Bowl.jpg',
        'hot honey'   => 'images/Ground Beef Hot Honey Bowl.jpg',
        'patate'      => 'images/Ground Beef Hot Honey Bowl.jpg',
        'chocolat'    => 'images/Chocolate Sauce.jpg',
        'cacao'       => 'images/Chocolate Sauce.jpg',
        'miel'        => 'images/Homemade Honey Syrup_ Sweet, Simple, and So Useful!.jpg',
        'honey'       => 'images/Homemade Honey Syrup_ Sweet, Simple, and So Useful!.jpg',
        'pizza'       => 'images/download (10).jpg',
        'jus'         => 'images/download (11).jpg',
        'orange'      => 'images/download (11).jpg',
        'pate'        => 'images/pasta.jpg',
        'pasta'       => 'images/pasta.jpg',
        'fruit sec'   => 'images/fruit sec.jpg',
        'noix'        => 'images/fruit sec.jpg',
        'amande'      => 'images/fruit sec.jpg',
        'menthe'      => 'images/Tisane menthe.jpg',
        'the vert'    => 'images/Tisane menthe.jpg',
        'thé vert'    => 'images/Tisane menthe.jpg',
        'camomille'   => 'images/Tisane camomille.jpg',
        'gingembre'   => 'images/Tisane camomille.jpg',
        'tisane'      => 'images/Tisane camomille.jpg',
    ];
    foreach($localMap as $kw => $path) {
        if(strpos($nm, $kw) !== false)
            return $path;
    }
    // Mots-clés aliment spécifiques
    $keywords = [
        'banane'      => 'photo-1528825871115-3581a5387919',
        'pomme'       => 'photo-1560806887-1e4cd0b6cbd6',
        'orange'      => 'photo-1547514701-42782101795e',
        'fraise'      => 'photo-1464965911861-746a04b4bca6',
        'myrtille'    => 'photo-1498557850523-fd3d118b962e',
        'chocolat'    => 'photo-1511381939415-e44f3c9a3d74',
        'salade'      => 'photo-1512621776951-a57141f2eefd',
        'riz'         => 'photo-1586201375761-83865001e31c',
        'poulet'      => 'photo-1604908176997-125f25cc6f3d',
        'saumon'      => 'photo-1467003909585-2f8a72700288',
        'escalope'    => 'photo-1604908176997-125f25cc6f3d',
        'dinde'       => 'photo-1604908176997-125f25cc6f3d',
        'soupe'       => 'photo-1547592180-85f173990554',
        'pain'        => 'photo-1509440159596-0249088772ff',
        'noix'        => 'photo-1508061253366-f7da158b6d46', /* remplacé par image locale */
        'avocat'      => 'photo-1601039641847-7857b994d704',
        'smoothie'    => 'photo-1490818387583-1baba5e638af',
        'jus'         => 'photo-1534353436294-0dbd4bdac845',
        'oeuf'        => 'photo-1582169505937-b9992bd01695',
        'lentilles'   => 'photo-1515543904379-3d757afe72e4',
        'brocoli'     => 'photo-1459411621453-7b03977f4bfc',
        'épinard'     => 'photo-1576045057995-568f588f82fb',
        'carotte'     => 'photo-1598170845058-32b9d6a5da37',
        'tomate'      => 'photo-1546094096-0df4bcaaa337',
        'concombre'   => 'photo-1604977042946-1eecc30f269e',
        'amande'      => 'photo-1508061253366-f7da158b6d46',
        'yaourt'      => 'photo-1571212515416-fef01fc43637',
        'lait'        => 'photo-1563636619-e9143da7973b',
    ];
    foreach($keywords as $kw => $pid) {
        if(strpos($nm, $kw) !== false)
            return "https://images.unsplash.com/$pid?w=400&q=75";
    }
    // Par catégorie
    $catMap = [
        'fruit'     => 'photo-1490474418585-ba9bad8fd0ea',
        'vegetable' => 'photo-1540420773420-3366772f4999',
        'grain'     => 'photo-1586201375761-83865001e31c',
        'protein'   => 'photo-1467003909585-2f8a72700288',
        'dairy'     => 'photo-1563636619-e9143da7973b',
        'dessert'   => 'photo-1563805042-7684c019e1cb',
        'beverage'  => 'photo-1490818387583-1baba5e638af',
        'legume'    => 'photo-1515543904379-3d757afe72e4',
        'nut'       => 'photo-1508061253366-f7da158b6d46',
    ];
    $pid = $catMap[strtolower($cat)] ?? 'photo-1504674900247-0877df9cc836';
    return "https://images.unsplash.com/$pid?w=400&q=75";
}

/* -- Process form -- */
$results             = [];
$selected_emotion_id = null;
$selected_emotion_nm = '';
$filter_info         = '';

$db_error    = '';
$save_success = false;

/* -- Step 2 : save selected foods -- */
if(isset($_POST['save_selection'])) {
    if(empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Requête invalide.");
    }
    $s_emo_id   = (int)($_POST['emotion_id'] ?? 0);
    $s_food_ids = array_values(array_filter(array_map('intval', $_POST['selected_foods'] ?? [])));

    if($s_emo_id > 0 && !empty($s_food_ids)) {
        try {
            $saveKey = 'reco_saved_' . $user_id . '_' . $s_emo_id;
            if(empty($_SESSION[$saveKey])) {
                $_SESSION[$saveKey] = true;
                $stUE = $conn->prepare("INSERT INTO USER_EMOTIONS (id_user, id_emotion, emotion_date) VALUES (:u, :e, SYSDATE)");
                $stUE->execute([':u' => $user_id, ':e' => $s_emo_id]);

                $stFd = $conn->prepare("SELECT description FROM FOODS WHERE id_food = :id");
                $stR  = $conn->prepare("INSERT INTO RECOMMENDATIONS (id_emotion, id_food, benefit, id_user, recommendation_date) VALUES (:e, :f, :b, :u, SYSDATE)");
                foreach($s_food_ids as $fid) {
                    $stFd->execute([':id' => $fid]);
                    $fdRow   = $stFd->fetch(PDO::FETCH_ASSOC);
                    $benefit = !empty($fdRow['DESCRIPTION'] ?? $fdRow['description'] ?? '') ? ($fdRow['DESCRIPTION'] ?? $fdRow['description']) : 'Recommandé pour votre état émotionnel.';
                    $stR->execute([':e' => $s_emo_id, ':f' => $fid, ':b' => $benefit, ':u' => $user_id]);
                }
                logActivity($conn, $user_id, 'RECOMMENDATION_SAVED');
            }
            $save_success = true;
            $selected_emotion_id = $s_emo_id;
            /* Re-load results for display */
            $stE2 = $conn->prepare("SELECT emotion_name FROM EMOTIONS WHERE id_emotion = :id");
            $stE2->execute([':id' => $s_emo_id]);
            $eRow2 = $stE2->fetch(PDO::FETCH_ASSOC);
            $selected_emotion_nm = $eRow2['EMOTION_NAME'] ?? $eRow2['emotion_name'] ?? '';
            $st2 = $conn->prepare("
                SELECT f.id_food, f.food_name, f.calories, f.category,
                       f.protein, f.carbs, f.fat, f.description AS benefit,
                       ef.intensity AS score
                FROM EMOTION_FOOD ef JOIN FOODS f ON f.id_food = ef.id_food
                WHERE ef.id_emotion = :emo ORDER BY ef.intensity DESC");
            $st2->execute([':emo' => $s_emo_id]);
            $results = $st2->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $db_error = "Erreur SQL : " . htmlspecialchars($e->getMessage());
        }
    }
}

if(isset($_POST['get_reco']) && !empty($_POST['emotion'])) {

    /* CSRF validation */
    if(empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Requête invalide.");
    }

    $emotion_id = (int)$_POST['emotion'];

    /* Resolve emotion name directly from the loaded list */
    foreach($emotions as $em) {
        $emId = (int)($em['ID_EMOTION'] ?? $em['id_emotion'] ?? 0);
        if($emId === $emotion_id) {
            $selected_emotion_id = $emotion_id;
            $selected_emotion_nm = $em['EMOTION_NAME'] ?? $em['emotion_name'] ?? '';
            break;
        }
    }

    /* Fallback: if deduplication removed it, trust the posted id */
    if($selected_emotion_id === null && $emotion_id > 0) {
        $selected_emotion_id = $emotion_id;
        try {
            $stE = $conn->prepare("SELECT emotion_name FROM EMOTIONS WHERE id_emotion = :id");
            $stE->execute([':id' => $emotion_id]);
            $eRow = $stE->fetch(PDO::FETCH_ASSOC);
            $selected_emotion_nm = $eRow['EMOTION_NAME'] ?? $eRow['emotion_name'] ?? '';
        } catch(PDOException $e) {
            $selected_emotion_nm = '';
        }
    }

    if($selected_emotion_id !== null) {
        try {
            $query = "
                SELECT f.id_food, f.food_name, f.calories, f.category,
                       f.protein, f.carbs, f.fat, f.description AS benefit,
                       ef.intensity AS score
                FROM EMOTION_FOOD ef
                JOIN FOODS f ON f.id_food = ef.id_food
                WHERE ef.id_emotion = :emo
                ORDER BY ef.intensity DESC
            ";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(":emo", $selected_emotion_id, PDO::PARAM_INT);
            $stmt->execute();

            $profileRow   = is_array($profile) ? $profile : [];
            $goal         = strtolower(trim($profileRow['GOAL']      ?? $profileRow['goal']      ?? ''));
            $allergiesRaw = strtolower(trim($profileRow['ALLERGIES'] ?? $profileRow['allergies'] ?? ''));
            $allergyList  = array_filter(array_map('trim', explode(',', $allergiesRaw)));
            $filters      = [];

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $foodName = strtolower($row['FOOD_NAME'] ?? $row['food_name'] ?? '');
                $calories = (int)($row['CALORIES'] ?? $row['calories'] ?? 0);

                /* Filtre Allergies */
                $blocked = false;
                foreach($allergyList as $allergen) {
                    if($allergen !== '' && strpos($foodName, $allergen) !== false) {
                        $filters[] = "allergie ($allergen)";
                        $blocked = true;
                        break;
                    }
                }
                if($blocked) continue;

                /* Filtre Objectif */
                if($goal === 'perte de poids' && $calories > 300) {
                    $filters[] = "objectif perte de poids (>300 cal)";
                    continue;
                }
                $results[] = $row;
            }

            if(!empty($filters)) {
                $filter_info = "Filtres appliqués : " . implode(', ', array_unique($filters)) . ".";
            }

        } catch(PDOException $e) {
            $db_error = "Erreur SQL : " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recommandations — EmoEat</title>
    <link rel="stylesheet" href="style.css?v=25">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="page-wrap page-wrap-md" style="max-width:980px; margin: auto; padding: 20px;">

    <div class="breadcrumb" style="margin-bottom: 20px; color: #666;">
        <a href="dashboard.php" style="color: #2D5A27; text-decoration: none;">🏠 Tableau de bord</a> › Recommandations
    </div>

    <div class="page-header" style="text-align: center; margin-bottom: 30px;">
        <h1 style="color: #5C4033;">🍽 Recommandation Intelligente</h1>
        <p>Sélectionnez votre émotion actuelle et recevez des suggestions alimentaires personnalisées.</p>
    </div>

    <?php if(!$profile): ?>
    <div class="alert alert-warning" style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        ⚠️ Votre profil nutritionnel est incomplet — les filtres (allergies, objectif) ne seront pas appliqués.
        <a href="profile.php" style="font-weight:700; text-decoration:underline; color: #856404;">Compléter mon profil ?</a>
    </div>
    <?php endif; ?>

    <div class="card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom:32px;">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="emotion-section">
                <h3 style="color: #2D5A27; margin-bottom: 15px;">Comment vous sentez-vous en ce moment ?</h3>

                <?php if(empty($emotions)): ?>
                    <div class="alert alert-info">Aucune émotion disponible dans la base de données.</div>
                <?php else: ?>
                <div class="emotion-grid" style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
                    <?php foreach($emotions as $em): ?>
                    <label class="emotion-item" style="cursor: pointer; padding: 10px 15px; border: 1px solid #ccc; border-radius: 20px;">
                        <input type="radio" name="emotion" value="<?php echo (int)$em['ID_EMOTION']; ?>" <?php echo ($selected_emotion_id === (int)$em['ID_EMOTION']) ? 'checked' : ''; ?>>
                        <span class="emotion-label">
                            <span class="emotion-emoji"><?php echo emoEmoji($em['EMOTION_NAME']); ?></span>
                            <span class="emotion-name"><?php echo htmlspecialchars(emoLabel($em['EMOTION_NAME'])); ?></span>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <button type="submit" name="get_reco" class="btn btn-green btn-full" style="background: #2D5A27; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; width: 100%;">
                🍽 Obtenir mes recommandations
            </button>
        </form>
    </div>

    <?php if(!empty($db_error)): ?>
    <div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px; margin-bottom:20px; border:1px solid #f5c6cb;">
        ⚠️ <?php echo $db_error; ?>
    </div>
    <?php endif; ?>

    <?php if(isset($_POST['get_reco'])): ?>

        <?php if(!empty($filter_info)): ?>
        <div class="alert alert-info" style="background: #e2e3e5; padding: 10px; border-radius: 5px; margin-bottom: 20px;">ℹ️ <?php echo htmlspecialchars($filter_info); ?></div>
        <?php endif; ?>

        <?php if(!empty($results)): ?>
        <div style="margin-bottom:20px;">
            <h2 style="color: #2D5A27;">
                <?php echo emoEmoji($selected_emotion_nm); ?> Recommandations pour « <?php echo htmlspecialchars(emoLabel($selected_emotion_nm)); ?> »
            </h2>
            <?php if(!$save_success): ?>
            <p style="color:#666; margin:4px 0 0;">Cochez les aliments souhaités puis confirmez votre sélection.</p>
            <?php endif; ?>
        </div>

        <?php if($save_success): ?>
        <div style="background:#d4edda;color:#155724;padding:14px 18px;border-radius:8px;margin-bottom:20px;border:1px solid #c3e6cb;font-weight:600;">
            ? Votre sélection a été enregistrée dans votre historique !
        </div>
        <?php endif; ?>

        <form method="POST" id="selectionForm">
            <input type="hidden" name="csrf_token"  value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" name="emotion_id"  value="<?php echo (int)$selected_emotion_id; ?>">

            <div class="food-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px;margin-bottom:24px;">
            <?php foreach($results as $row):
                $fid  = (int)($row['ID_FOOD']   ?? $row['id_food']   ?? 0);
                $fnm  = $row['FOOD_NAME']  ?? $row['food_name']  ?? '';
                $fcat = $row['CATEGORY']   ?? $row['category']   ?? '';
                $fcal = (int)($row['CALORIES']  ?? $row['calories']  ?? 0);
                $fprt = (int)($row['PROTEIN']   ?? $row['protein']   ?? 0);
                $fcrb = (int)($row['CARBS']     ?? $row['carbs']     ?? 0);
                $ffat = (int)($row['FAT']       ?? $row['fat']       ?? 0);
                $fben = $row['BENEFIT']    ?? $row['benefit']    ?? '';
                $femo = foodEmoji($fnm, $fcat);
                $fimg = getFoodImage($fnm, $fcat);
            ?>
            <label for="food_<?php echo $fid; ?>" class="food-card-with-photo" id="lbl_<?php echo $fid; ?>">
                <!-- Photo -->
                <img src="<?php echo $fimg; ?>"
                     alt="<?php echo htmlspecialchars($fnm); ?>"
                     class="food-card-photo"
                     loading="lazy"
                     onerror="this.src='https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400&q=75'">
                <div class="food-card-body-inner">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                        <div>
                            <span style="font-size:22px;"><?php echo $femo; ?></span>
                            <h4 style="margin:3px 0 0;color:#5C4033;font-size:15px;"><?php echo htmlspecialchars($fnm); ?></h4>
                            <small style="color:#888;"><?php echo htmlspecialchars(ucfirst(strtolower($fcat))); ?></small>
                        </div>
                        <input type="checkbox" id="food_<?php echo $fid; ?>" name="selected_foods[]" value="<?php echo $fid; ?>"
                               style="width:22px;height:22px;cursor:pointer;accent-color:#2D5A27;flex-shrink:0;"
                               data-name="<?php echo htmlspecialchars($fnm); ?>"
                               data-emoji="<?php echo $femo; ?>"
                               data-cal="<?php echo $fcal; ?>" data-prot="<?php echo $fprt; ?>"
                               data-carb="<?php echo $fcrb; ?>" data-fat="<?php echo $ffat; ?>"
                               <?php echo $save_success ? 'disabled checked' : ''; ?>>
                    </div>
                    <div style="border-top:1px solid #eee;padding-top:8px;">
                        <p style="margin:3px 0;font-size:13px;">🔥 <strong><?php echo $fcal ?: '—'; ?></strong> cal
                        <?php if($fprt): ?> &nbsp;|&nbsp; 💪 <strong><?php echo $fprt; ?>g</strong><?php endif; ?></p>
                        <?php if(!empty($fben)): ?>
                        <p style="margin-top:5px;color:#666;font-size:12px;line-height:1.5;"><?php echo htmlspecialchars($fben); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </label>
            <?php endforeach; ?>
            </div>

            <!-- -- LIVE SUMMARY BOARD -- -->
            <div id="summaryBoard" style="margin-bottom:24px;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,0.08);overflow:hidden;display:none;">
                <div style="background:#2D5A27;color:#fff;padding:14px 20px;display:flex;align-items:center;gap:10px;">
                    <span style="font-size:18px;">🛒</span>
                    <h3 style="margin:0;font-size:16px;">Ma sélection</h3>
                    <span id="selCount" style="margin-left:auto;background:rgba(255,255,255,.2);border-radius:20px;padding:2px 12px;font-size:13px;">0 aliment</span>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:14px;">
                        <thead>
                            <tr style="background:#f5f9f5;border-bottom:2px solid #e0ede0;">
                                <th style="padding:10px 14px;text-align:left;color:#2D5A27;">Aliment</th>
                                <th style="padding:10px 14px;text-align:center;color:#2D5A27;">🔥 Cal</th>
                                <th style="padding:10px 14px;text-align:center;color:#2D5A27;">💪 Prot</th>
                                <th style="padding:10px 14px;text-align:center;color:#2D5A27;">🌾 Gluc</th>
                                <th style="padding:10px 14px;text-align:center;color:#2D5A27;">🧈 Lip</th>
                            </tr>
                        </thead>
                        <tbody id="summaryBody"></tbody>
                        <tfoot>
                            <tr style="background:#f5f9f5;font-weight:700;border-top:2px solid #2D5A27;">
                                <td style="padding:10px 14px;color:#2D5A27;">Total</td>
                                <td id="tCal"  style="padding:10px 14px;text-align:center;color:#2D5A27;">—</td>
                                <td id="tProt" style="padding:10px 14px;text-align:center;color:#2D5A27;">—</td>
                                <td id="tCarb" style="padding:10px 14px;text-align:center;color:#2D5A27;">—</td>
                                <td id="tFat"  style="padding:10px 14px;text-align:center;color:#2D5A27;">—</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <!-- -- END SUMMARY BOARD -- -->

            <?php if(!$save_success): ?>
            <button type="submit" name="save_selection"
                    id="confirmBtn" disabled
                    style="background:#aaa;color:#fff;padding:13px 24px;border:none;border-radius:8px;font-size:15px;cursor:not-allowed;width:100%;transition:background .2s;">
                ? Confirmer ma sélection
            </button>
            <?php endif; ?>
        </form>

        <script>
        (function(){
            const cbs    = document.querySelectorAll('input[name="selected_foods[]"]');
            const board  = document.getElementById('summaryBoard');
            const sbody  = document.getElementById('summaryBody');
            const count  = document.getElementById('selCount');
            const tCal   = document.getElementById('tCal');
            const tProt  = document.getElementById('tProt');
            const tCarb  = document.getElementById('tCarb');
            const tFat   = document.getElementById('tFat');
            const btn    = document.getElementById('confirmBtn');

            cbs.forEach(cb => {
                const card = cb.closest('label');
                cb.addEventListener('change', function() {
                    if(this.checked) {
                        card.classList.add('checked');
                    } else {
                        card.classList.remove('checked');
                    }
                    update();
                });
            });

            function update() {
                const checked = [...cbs].filter(c => c.checked);
                sbody.innerHTML = '';
                let tC=0,tP=0,tG=0,tL=0;

                checked.forEach((c, i) => {
                    const cal=+c.dataset.cal||0, prot=+c.dataset.prot||0,
                          carb=+c.dataset.carb||0, fat=+c.dataset.fat||0;
                    tC+=cal; tP+=prot; tG+=carb; tL+=fat;
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid #eee';
                    if(i%2===1) tr.style.background='#fafff9';
                    tr.innerHTML = `
                        <td style="padding:9px 14px;font-weight:600;">${c.dataset.emoji} ${c.dataset.name}</td>
                        <td style="padding:9px 14px;text-align:center;">${cal||'—'}</td>
                        <td style="padding:9px 14px;text-align:center;">${prot?prot+'g':'—'}</td>
                        <td style="padding:9px 14px;text-align:center;">${carb?carb+'g':'—'}</td>
                        <td style="padding:9px 14px;text-align:center;">${fat?fat+'g':'—'}</td>`;
                    sbody.appendChild(tr);
                });

                board.style.display = checked.length ? 'block' : 'none';
                count.textContent = checked.length + ' aliment' + (checked.length>1?'s':'');
                tCal.textContent  = tC || '—';
                tProt.textContent = tP ? tP+'g' : '—';
                tCarb.textContent = tG ? tG+'g' : '—';
                tFat.textContent  = tL ? tL+'g' : '—';

                if(btn) {
                    btn.disabled = checked.length === 0;
                    btn.style.background = checked.length ? '#2D5A27' : '#aaa';
                    btn.style.cursor     = checked.length ? 'pointer'  : 'not-allowed';
                }
            }
        })();
        </script>

        <?php else: ?>
        <div class="empty-state" style="text-align: center; padding: 40px; background: #fff; border-radius: 8px;">
            <h3>Aucun résultat</h3>
            <p>Aucun aliment ne correspond à votre émotion avec votre profil actuel (filtres restrictifs).</p>
        </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<?php include('footer.php'); ?>
</body>
</html>

