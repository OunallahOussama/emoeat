<?php
/**
 * History view
 * Variables: $emoHistory, $history
 * Uses static helper: HistoryController::emoEmoji()
 */
use App\Controllers\HistoryController;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique &mdash; EmoEat</title>
    <link rel="stylesheet" href="/style.css?v=25">
</head>
<body>

<?php require dirname(dirname(__DIR__)) . '/Views/partials/navbar.php'; ?>

<div class="page-wrap">

    <div class="breadcrumb">
        <a href="/dashboard">&#127968; Tableau de bord</a> &rsaquo; Historique
    </div>

    <div class="page-header-row page-header">
        <div>
            <h1>&#128202; Mon Historique</h1>
            <p>Retrouvez toutes vos recommandations alimentaires passées.</p>
        </div>
        <a href="/recommendation" class="btn btn-green">+ Nouvelle recommandation</a>
    </div>

    <div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));margin-bottom:36px;">
        <div class="stat-card">
            <div class="stat-icon si-green">&#128522;</div>
            <div><div class="stat-val"><?php echo count($history); ?></div><div class="stat-lbl">Aliments recommandés</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-orange">&#127869;</div>
            <div><div class="stat-val"><?php echo count($emoHistory); ?></div><div class="stat-lbl">&Eacute;motions enregistrées</div></div>
        </div>
    </div>

    <?php if(!empty($emoHistory)): ?>
    <div style="margin-bottom:40px;">
        <div class="table-wrap">
            <div class="table-head">
                <h3>&#128522; Historique des émotions</h3>
                <span class="tag tag-o"><?php echo count($emoHistory); ?> entrée(s)</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr><th>&Eacute;motion</th><th>Date et heure</th></tr>
                </thead>
                <tbody>
                    <?php foreach($emoHistory as $e): ?>
                    <tr>
                        <td>
                            <?php echo HistoryController::emoEmoji($e['EMOTION_NAME']); ?>
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
            <h3>&#127869; Recommandations alimentaires</h3>
            <?php if(!empty($history)): ?>
            <span class="tag tag-g"><?php echo count($history); ?> résultat(s)</span>
            <?php endif; ?>
        </div>

        <?php if(empty($history)): ?>
        <div class="empty-state">
            <div class="es-icon">&#127869;</div>
            <h3>Aucune recommandation</h3>
            <p>Vous n'avez pas encore reçu de recommandations alimentaires. Commencez par sélectionner une émotion !</p>
            <a href="/recommendation" class="btn btn-green">&#127869; Obtenir une recommandation</a>
        </div>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Aliment</th>
                    <th>&Eacute;motion</th>
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
                        <?php echo HistoryController::emoEmoji($row['EMOTION_NAME'] ?? ''); ?>
                        <?php echo htmlspecialchars($row['EMOTION_NAME'] ?? 'Inconnue'); ?>
                    </td>
                    <td>&#128293; <?php echo !empty($row['CALORIES']) ? (int)$row['CALORIES'].' kcal' : '&mdash;'; ?></td>
                    <td style="max-width:220px;font-size:13px;color:var(--text-m);">
                        <?php echo !empty($row['BENEFIT']) ? htmlspecialchars(mb_strimwidth($row['BENEFIT'], 0, 80, '...')) : '&mdash;'; ?>
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

<?php require dirname(dirname(__DIR__)) . '/Views/partials/footer.php'; ?>
</body>
</html>
