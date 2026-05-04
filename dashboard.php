<?php
/* ================================================
   dashboard.php �?" Tableau de bord utilisateur
   Page principale après la connexion.
   Affiche les statistiques et les dernières recommandations.
   ================================================ */
session_start();
include("connexion.php");

/* Si l'utilisateur n'est pas connecté, on le renvoie à la connexion */
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name    = $_SESSION['user_name'] ?? 'Utilisateur';
$role    = isset($_SESSION['role']) ? strtoupper(trim($_SESSION['role'])) : 'CLIENT';

/* �"?�"? Compteurs pour les statistiques du tableau de bord �"?�"? */
$cntRec = 0;     /* Nombre de recommandations reçues */
$cntEmo = 0;     /* Nombre d'émotions enregistrées */
$hasProfile = false; /* L'utilisateur a-t-il complété son profil ? */
$recent = [];    /* Les 5 dernières recommandations */

try {
        /* Compte le nombre de recommandations de cet utilisateur */
        $stmt1 = $conn->prepare("SELECT COUNT(*) AS C FROM RECOMMENDATIONS WHERE ID_USER = :u");
        $stmt1->bindParam(":u", $user_id);
        $stmt1->execute();
        if($r1 = $stmt1->fetch(PDO::FETCH_ASSOC)) $cntRec = (int)$r1['C'];

        /* Compte le nombre d'émotions enregistrées */
        $stmt2 = $conn->prepare("SELECT COUNT(*) AS C FROM USER_EMOTIONS WHERE ID_USER = :u");
        $stmt2->bindParam(":u", $user_id);
        $stmt2->execute();
        if($r2 = $stmt2->fetch(PDO::FETCH_ASSOC)) $cntEmo = (int)$r2['C'];

        /* Vérifie si le profil nutritionnel est complété */
        $stmt3 = $conn->prepare("SELECT COUNT(*) AS C FROM USER_PROFILE WHERE ID_USER = :u");
        $stmt3->bindParam(":u", $user_id);
        $stmt3->execute();
        if($r3 = $stmt3->fetch(PDO::FETCH_ASSOC)) $hasProfile = ((int)$r3['C'] > 0);

        /* Récupère les 5 dernières recommandations avec l'aliment et l'émotion associés */
        $qR = "SELECT f.FOOD_NAME, e.EMOTION_NAME, r.RECOMMENDATION_DATE
               FROM RECOMMENDATIONS r
               JOIN FOODS f  ON f.ID_FOOD    = r.ID_FOOD
               JOIN EMOTIONS e ON e.ID_EMOTION = r.ID_EMOTION
               WHERE r.ID_USER = :u
               ORDER BY r.RECOMMENDATION_DATE DESC";

        $stmtR = $conn->prepare($qR);
        $stmtR->bindParam(":u", $user_id);
        $stmtR->execute();

        $recCount = 0;
        while($row = $stmtR->fetch(PDO::FETCH_ASSOC)) {
            if($recCount >= 5) break;
            $recent[] = $row;
            $recCount++;
        }
} catch (PDOException $e) {
    $error_msg = "Erreur de chargement des données.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord &#127869; EmoEat</title>
    <link rel="stylesheet" href="style.css?v=25">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="page-wrap">

    <div style="background:linear-gradient(135deg,var(--primary-d),var(--primary-l));border-radius:var(--radius);padding:32px 36px;color:#fff;margin-bottom:32px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px;box-shadow:var(--shadow-lg);">
        <div>
            <p style="font-size:13px;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;">Bienvenue</p>
            <h1 style="font-size:clamp(22px,3.5vw,34px);font-weight:900;margin-bottom:8px;"><?php echo htmlspecialchars($name); ?> &#128075;</h1>
            <p style="font-size:15px;color:rgba(255,255,255,.8);">
                Rôle : <strong style="color:var(--accent);"><?php echo htmlspecialchars($role); ?></strong>
                &nbsp;&mdash;&nbsp; <?php echo date('l d F Y'); ?>
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="recommandation.php" class="btn btn-primary" style="background-color: var(--accent); border: none;">&#127869; Nouvelle recommandation</a>
            <a href="logout.php" class="btn btn-outline" style="border-radius:var(--radius-sm); background-color: #d9534f; color: white; border: none; padding: 10px 15px; text-decoration: none;">&#128682; Déconnexion</a>
        </div>
    </div>

    <?php if(!$hasProfile): ?>
    <div class="alert alert-warning" style="background-color:#fff3cd; color:#856404; padding:15px; border-radius:8px; margin-bottom:20px;">
        &#9888;&#65039; Votre profil nutritionnel est incomplet.
        <a href="profile.php" style="font-weight:700;color:inherit;text-decoration:underline;">Complétez-le maintenant &#8594;</a>
    </div>
    <?php endif; ?>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon si-green">&#127869;</div>
            <div>
                <div class="stat-val"><?php echo $cntRec; ?></div>
                <div class="stat-lbl">Recommandations reçues</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-orange">&#128522;</div>
            <div>
                <div class="stat-val"><?php echo $cntEmo; ?></div>
                <div class="stat-lbl">&Eacute;motions enregistrées</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-blue">&#128100;</div>
            <div>
                <div class="stat-val"><?php echo $hasProfile ? '&#10003;' : '&#10007;'; ?></div>
                <div class="stat-lbl">Profil nutritionnel</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-purple">&#127807;</div>
            <div>
                <div class="stat-val">ODD 3</div>
                <div class="stat-lbl">Bonne Santé &amp; Bien-être</div>
            </div>
        </div>
    </div>

    <h2 style="font-size:18px;font-weight:800;color:var(--primary-d);margin-bottom:4px;margin-top:30px;">Accès rapide</h2>
    <p style="font-size:14px;color:var(--text-l);margin-bottom:15px;">Naviguez rapidement vers les fonctionnalités.</p>
    <div class="quick-grid">
        <a href="recommandation.php" class="quick-card">
            <div class="qc-icon">&#127869;</div>
            <span>Recommandations</span>
        </a>
        <a href="historique.php" class="quick-card">
            <div class="qc-icon">&#128202;</div>
            <span>Mon historique</span>
        </a>
        <a href="profile.php" class="quick-card">
            <div class="qc-icon">&#128100;</div>
            <span>Mon profil</span>
        </a>
        <?php if($role === 'ADMIN'): ?>
        <a href="dashboard_admin.php" class="quick-card">
            <div class="qc-icon">&#9881;&#65039;</div>
            <span>Panneau Admin</span>
        </a>
        <?php endif; ?>
    </div>

    <?php if(!empty($recent)): ?>
    <div style="margin-top:40px;">
        <div class="table-wrap">
            <div class="table-head">
                <h3>&#128197; Activité récente</h3>
                <a href="historique.php" class="btn btn-sm btn-green" style="text-decoration:none; padding:5px 10px; background:#28a745; color:white; border-radius:5px;">Voir tout &#8594;</a>
            </div>
            <table class="data-table" style="width:100%; border-collapse:collapse; margin-top:15px;">
                <thead style="background:#f8f9fa; text-align:left;">
                    <tr>
                        <th style="padding:10px; border-bottom:2px solid #dee2e6;">Aliment</th>
                        <th style="padding:10px; border-bottom:2px solid #dee2e6;">&Eacute;motion</th>
                        <th style="padding:10px; border-bottom:2px solid #dee2e6;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent as $r): ?>
                    <tr>
                        <td style="padding:10px; border-bottom:1px solid #eee;"><strong><?php echo htmlspecialchars($r['FOOD_NAME'] ?? 'Inconnu'); ?></strong></td>
                        <td style="padding:10px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($r['EMOTION_NAME'] ?? 'Inconnue'); ?></td>
                        <td style="padding:10px; border-bottom:1px solid #eee;"><?php echo htmlspecialchars($r['RECOMMENDATION_DATE'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div style="margin-top:40px; padding:30px; text-align:center; background:#f8f9fa; border-radius:8px; color:#6c757d;">
        Aucune activité récente. <br><a href="recommandation.php" style="color:var(--primary-d); font-weight:bold;">Obtenez votre première recommandation !</a>
    </div>
    <?php endif; ?>

</div>

<?php include('footer.php'); ?>
</body>
</html>

