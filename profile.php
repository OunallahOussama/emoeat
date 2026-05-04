<?php
/* ================================================
   profile.php �?" Profil nutritionnel
   L'utilisateur consulte et modifie ses informations
   personnelles : poids, taille, allergies, objectifs.
   ================================================ */
session_start();
include("connexion.php");

/* Protection : accès réservé aux utilisateurs connectés */
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id  = $_SESSION['user_id'];
$message  = "";
$msg_type = "success";

/* Lecture d'un message flash (succès ou erreur) laissé après une action */
if(isset($_SESSION['message'])) {
    $message  = $_SESSION['message'];
    $msg_type = $_SESSION['msg_type'] ?? 'success';
    unset($_SESSION['message'], $_SESSION['msg_type']);
}

$profile  = null;
$userInfo = null;

try {
    /* Chargement du profil nutritionnel de l'utilisateur */
    $stmtR = $conn->prepare("SELECT * FROM USER_PROFILE WHERE ID_USER = :u");
    $stmtR->bindParam(":u", $user_id);
    $stmtR->execute();
    $profile = $stmtR->fetch(PDO::FETCH_ASSOC);

    /* Chargement des infos du compte (nom, email, rôle) */
    $stmtU = $conn->prepare("SELECT NAME, EMAIL, ROLE, CREATED_AT FROM USERS WHERE ID_USER = :u");
    $stmtU->bindParam(":u", $user_id);
    $stmtU->execute();
    $userInfo = $stmtU->fetch(PDO::FETCH_ASSOC);

    /* �"?�"? Sauvegarde du profil quand l'utilisateur clique sur Enregistrer �"?�"? */
    if(isset($_POST['save'])) {
        $weight    = (float)$_POST['weight'];
        $height    = (float)$_POST['height'];
        $allergies = trim($_POST['allergies']);
        $goal      = trim($_POST['goal']);

        /* Vérification que les valeurs sont positives */
        if($weight <= 0 || $height <= 0) {
            $_SESSION['message']  = "Veuillez entrer un poids et une taille valides.";
            $_SESSION['msg_type'] = "danger";
        } else {
            /* Si le profil existe déjà on le met à jour, sinon on le crée */
            if($profile) {
                $sql2 = "UPDATE USER_PROFILE SET WEIGHT = :weight, HEIGHT = :height,
                         ALLERGIES = :allergies, GOAL = :goal WHERE ID_USER = :u";
            } else {
                $sql2 = "INSERT INTO USER_PROFILE (ID_USER, WEIGHT, HEIGHT, ALLERGIES, GOAL)
                         VALUES (:u, :weight, :height, :allergies, :goal)";
            }
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(":u",         $user_id);
            $stmt2->bindParam(":weight",    $weight);
            $stmt2->bindParam(":height",    $height);
            $stmt2->bindParam(":allergies", $allergies);
            $stmt2->bindParam(":goal",      $goal);
            $stmt2->execute();

            /* ACTIVITY_LOG (MPD) */
            logActivity($conn, $user_id, 'PROFILE_UPDATED');

            $_SESSION['message']  = "Profil nutritionnel sauvegardé avec succès !";
            $_SESSION['msg_type'] = "success";
        }
        header("Location: profile.php");
        exit();
    }
} catch(PDOException $e) {
    $_SESSION['message']  = "Erreur de base de données : " . htmlspecialchars($e->getMessage());
    $_SESSION['msg_type'] = "danger";
}

/* �"?�"? BMI calculation �"?�"? */
$bmi = null; $bmi_label = ''; $bmi_class = '';
$w = (float)($profile['WEIGHT'] ?? 0);
$h = (float)($profile['HEIGHT'] ?? 0);
if($w > 0 && $h > 0) {
    $hm  = $h / 100;
    $bmi = round($w / ($hm * $hm), 1);
    if($bmi < 18.5)     { $bmi_label = 'Insuffisance pondérale'; $bmi_class = 'bmi-under'; }
    elseif($bmi < 25)   { $bmi_label = 'Poids normal ✓';         $bmi_class = 'bmi-normal'; }
    elseif($bmi < 30)   { $bmi_label = 'Surpoids';               $bmi_class = 'bmi-over'; }
    else                { $bmi_label = 'Obésité';                $bmi_class = 'bmi-obese'; }
}

$name = $_SESSION['user_name'] ?? 'Utilisateur';
$role = $_SESSION['role']      ?? 'CLIENT';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil &#127869; EmoEat</title>
    <link rel="stylesheet" href="style.css?v=25">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="page-wrap page-wrap-md" style="max-width:900px;">

    <div class="breadcrumb">
        <a href="dashboard.php">&#127968; Tableau de bord</a> &rsaquo; Mon Profil
    </div>

    <div class="profile-banner">
        <div class="profile-avatar">&#128100;</div>
        <div>
            <h2><?php echo htmlspecialchars($name); ?></h2>
            <p><?php echo htmlspecialchars($userInfo['EMAIL'] ?? ''); ?></p>
            <span class="role-badge"><?php echo htmlspecialchars($role); ?></span>
        </div>
        <div style="margin-left:auto;text-align:right;">
            <p style="font-size:12px;color:rgba(255,255,255,.6);">Membre depuis</p>
            <p style="font-size:14px;font-weight:600;"><?php echo htmlspecialchars($userInfo['CREATED_AT'] ?? '&#8212;'); ?></p>
        </div>
    </div>

    <?php if(!empty($message)): ?>
    <div class="alert alert-<?php echo $msg_type; ?>">
        <?php echo $msg_type === 'success' ? '&#9989;' : '&#9888;&#65039;'; ?>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <div class="grid-2">

        <div class="card">
            <h3 style="font-size:17px;font-weight:800;color:var(--primary-d);margin-bottom:22px;">&#128203; Données nutritionnelles</h3>
            <form method="POST">

                <div class="form-group">
                    <label for="weight">Poids (kg)</label>
                    <input type="number" id="weight" name="weight" class="form-control"
                        placeholder="ex: 70" min="1" max="300" step="0.1" required
                        value="<?php echo htmlspecialchars($profile['WEIGHT'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="height">Taille (cm)</label>
                    <input type="number" id="height" name="height" class="form-control"
                        placeholder="ex: 175" min="50" max="250" step="1" required
                        value="<?php echo htmlspecialchars($profile['HEIGHT'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="allergies">Allergies alimentaires</label>
                    <input type="text" id="allergies" name="allergies" class="form-control"
                        placeholder="ex: gluten, lactose, arachide..."
                        value="<?php echo htmlspecialchars($profile['ALLERGIES'] ?? ''); ?>">
                    <p class="form-hint">Séparez par des virgules si plusieurs allergies.</p>
                </div>

                <div class="form-group">
                    <label for="goal">Objectif nutritionnel</label>
                    <select id="goal" name="goal" class="form-control">
                        <?php
                        $goals = ['lose weight' => 'Perdre du poids', 'gain' => 'Prendre du poids', 'maintain' => 'Maintenir le poids', '' => 'Non défini'];
                        $currentGoal = strtolower(trim($profile['GOAL'] ?? ''));
                        foreach($goals as $val => $lbl):
                        ?>
                        <option value="<?php echo htmlspecialchars($val); ?>"
                            <?php echo ($currentGoal === $val) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lbl); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="save" class="btn btn-green btn-full" style="margin-top:8px;">
                    &#9989; Sauvegarder le profil
                </button>

            </form>
        </div>

        <div>
            <?php if($bmi !== null): ?>
            <div class="bmi-box" style="margin-bottom:24px;">
                <p style="font-size:12px;font-weight:700;color:var(--text-l);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">Indice de Masse Corporelle</p>
                <div class="bmi-val"><?php echo $bmi; ?></div>
                <div class="bmi-label">kg/m²</div>
                <div class="bmi-status <?php echo $bmi_class; ?>"><?php echo htmlspecialchars($bmi_label); ?></div>
                <div class="progress" style="margin-top:16px;">
                    <?php
                    $bmi_pct = min(100, max(0, ($bmi - 10) / (45 - 10) * 100));
                    ?>
                    <div class="progress-bar" style="width:<?php echo $bmi_pct; ?>%;"></div>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-l);margin-top:4px;">
                    <span>10</span><span>18.5</span><span>25</span><span>30</span><span>45+</span>
                </div>
            </div>
            <?php else: ?>
            <div class="bmi-box" style="margin-bottom:24px;text-align:center;padding:36px 20px;">
                <div style="font-size:48px;margin-bottom:12px;">&#9878;</div>
                <p style="font-size:14px;color:var(--text-l);">Renseignez votre poids et taille pour calculer votre IMC.</p>
            </div>
            <?php endif; ?>

            <div class="card">
                <h4 style="font-size:15px;font-weight:700;color:var(--primary-d);margin-bottom:16px;">&#128100; Conseils nutritionnels</h4>
                <?php
                $tips = [
                    'lose weight' => ['Limitez les aliments à plus de 300 cal', 'Privilégiez les légumes et protéines maigres', 'Buvez au moins 2L d\'eau par jour', 'Évitez les sucres ajoutés'],
                    'gain'        => ['Consommez des aliments riches en calories saines', 'Augmentez les portions de protéines', 'Intégrez des noix et avocats', 'Mangez 5-6 fois par jour'],
                    'maintain'    => ['Maintenez un équilibre entre protéines, glucides et lipides', 'Variez les sources alimentaires', 'Écoutez les signaux de faim de votre corps'],
                ];
                $currentGoal = strtolower(trim($profile['GOAL'] ?? ''));
                $activeTips  = $tips[$currentGoal] ?? ['Complétez votre profil pour recevoir des conseils personnalisés.'];
                foreach($activeTips as $tip):
                ?>
                <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:10px;font-size:13px;color:var(--text-m);">
                    <span style="color:var(--primary);font-size:16px;flex-shrink:0;">✓</span>
                    <?php echo htmlspecialchars($tip); ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>

<?php include('footer.php'); ?>
</body>
</html>

