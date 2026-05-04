<?php
/* ================================================
   historique.php — Historique de l'utilisateur
   Affiche toutes les émotions enregistrées et
   toutes les recommandations reçues dans le passé.
   ================================================ */
session_start();
include("connexion.php");

/* Protection : seuls les utilisateurs connectés peuvent voir leur historique */
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$emoHistory = []; /* Historique des émotions */
$history = [];    /* Historique des recommandations */

try {
        /* -- Récupère les émotions enregistrées par l'utilisateur, du plus récent au plus ancien -- */
        $queryEmo = "
            SELECT ue.EMOTION_DATE, e.EMOTION_NAME
            FROM USER_EMOTIONS ue
            JOIN EMOTIONS e ON e.ID_EMOTION = ue.ID_EMOTION
            WHERE ue.ID_USER = :u
            ORDER BY ue.EMOTION_DATE DESC
        ";
        $stmtEmo = $conn->prepare($queryEmo);
        $stmtEmo->bindParam(":u", $user_id);
        $stmtEmo->execute();
        while($r = $stmtEmo->fetch(PDO::FETCH_ASSOC)) {
            $emoHistory[] = $r;
        }

        /* -- Récupère toutes les recommandations alimentaires avec l'aliment et l'émotion -- */
        $queryRec = "
            SELECT f.FOOD_NAME, f.CALORIES, f.CATEGORY,
                   r.BENEFIT, r.RECOMMENDATION_DATE, e.EMOTION_NAME
            FROM RECOMMENDATIONS r
            JOIN FOODS f    ON f.ID_FOOD    = r.ID_FOOD
            JOIN EMOTIONS e ON e.ID_EMOTION = r.ID_EMOTION
            WHERE r.ID_USER = :u
            ORDER BY r.RECOMMENDATION_DATE DESC
        ";
        $stmtRec = $conn->prepare($queryRec);
        $stmtRec->bindParam(":u", $user_id);
        $stmtRec->execute();
        while($row = $stmtRec->fetch(PDO::FETCH_ASSOC)) {
            $history[] = $row;
        }

} catch (PDOException $e) {
    $error_msg = "Erreur de chargement de l'historique.";
}

/* -- Retourne l'émoji correspondant à l'émotion (utilisé dans le tableau) -- */
function hEmoEmoji(string $name): string {
    $map = [
        'happy'=>'😊','sad'=>'😢','angry'=>'😠','stress'=>'😰',
        'stressed'=>'😰','excited'=>'🤩','anxious'=>'😟','calm'=>'😌',
        'tired'=>'😴','fear'=>'😱','joy'=>'😄',
    ];
    return $map[strtolower(trim($name))] ?? '😶';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique — EmoEat</title>
    <link rel="stylesheet" href="style.css?v=25">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="page-wrap">

    <div class="breadcrumb">
        <a href="dashboard.php">🏠 Tableau de bord</a> › Historique
    </div>

    <div class="page-header-row page-header">
        <div>
            <h1>📊 Mon Historique</h1>
            <p>Retrouvez toutes vos recommandations alimentaires passées.</p>
        </div>
        <a href="recommandation.php" class="btn btn-green">+ Nouvelle recommandation</a>
    </div>

    <div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));margin-bottom:36px;">
        <div class="stat-card">
            <div class="stat-icon si-green">😊</div>
            <div><div class="stat-val"><?php echo count($history); ?></div><div class="stat-lbl">Aliments recommandés</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-orange">🍽</div>
            <div><div class="stat-val"><?php echo count($emoHistory); ?></div><div class="stat-lbl">Émotions enregistrées</div></div>
        </div>
    </div>

    <?php if(!empty($emoHistory)): ?>
    <div style="margin-bottom:40px;">
        <div class="table-wrap">
            <div class="table-head">
                <h3>😊 Historique des émotions</h3>
                <span class="tag tag-o"><?php echo count($emoHistory); ?> entrée(s)</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>Émotion</th><th>Date et heure</th></tr>
                </thead>
                <tbody>
                    <?php foreach($emoHistory as $e): ?>
                    <tr>
                        <td>
                            <?php echo hEmoEmoji($e['EMOTION_NAME']); ?>
                            <strong><?php echo htmlspecialchars($e['EMOTION_NAME']); ?></strong>
                        </td>
                        <td><?php echo htmlspecialchars($e['EMOTION_DATE']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="table-wrap">
        <div class="table-head">
            <h3>?? Recommandations alimentaires</h3>
            <?php if(!empty($history)): ?>
            <span class="tag tag-g"><?php echo count($history); ?> résultat(s)</span>
            <?php endif; ?>
        </div>

        <?php if(empty($history)): ?>
        <div class="empty-state">
            <div class="es-icon">🍽</div>
            <h3>Aucune recommandation</h3>
            <p>Vous n'avez pas encore reçu de recommandations alimentaires. Commencez par sélectionner une émotion !</p>
            <a href="recommandation.php" class="btn btn-green">🍽 Obtenir une recommandation</a>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Aliment</th>
                    <th>Émotion</th>
                    <th>Calories</th>
                    <th>Bénéfice</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($history as $row): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($row['FOOD_NAME'] ?? 'Inconnu'); ?></strong>
                        <?php if(!empty($row['CATEGORY'])): ?>
                        <br><span class="tag tag-b" style="margin-top:4px;display:inline-block;"><?php echo htmlspecialchars($row['CATEGORY']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo hEmoEmoji($row['EMOTION_NAME'] ?? ''); ?>
                        <?php echo htmlspecialchars($row['EMOTION_NAME'] ?? 'Inconnue'); ?>
                    </td>
                    <td>🔥 <?php echo !empty($row['CALORIES']) ? (int)$row['CALORIES'].' kcal' : '—'; ?></td>
                    <td style="max-width:220px;font-size:13px;color:var(--text-m);">
                        <?php echo !empty($row['BENEFIT']) ? htmlspecialchars(mb_strimwidth($row['BENEFIT'], 0, 80, '…')) : '—'; ?>
                    </td>
                    <td style="white-space:nowrap;font-size:13px;">
                        <?php echo htmlspecialchars($row['RECOMMENDATION_DATE'] ?? '-'); ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<?php include('footer.php'); ?>
</body>
</html>


