<?php
/**
 * Admin Activity Log view
 * Variables: $logs, $search
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal d'activité &#9881;&#65039; EmoEat Admin</title>
    <link rel="stylesheet" href="/style.css?v=25">
</head>
<body>
<?php require dirname(dirname(__DIR__)) . '/Views/partials/navbar.php'; ?>

<div class="page-wrap">
    <div class="breadcrumb">
        <a href="/admin/dashboard">&#9881;&#65039; Admin</a> &rsaquo; Journal d'activité
    </div>

    <div class="page-header-row page-header">
        <div>
            <h1>&#128202; Journal d'Activité</h1>
            <p>Consultez le ACTIVITY_LOG des utilisateurs.</p>
        </div>
        <a href="/admin/dashboard" class="btn btn-outline">&rsaquo; Retour Admin</a>
    </div>

    <form method="GET" action="/admin/activity-log" style="margin-bottom:20px;display:flex;gap:10px;">
        <input type="text" name="q" class="form-control" placeholder="Rechercher par action, utilisateur &#128269;"
               value="<?php echo htmlspecialchars($search); ?>" style="max-width:380px;">
        <button type="submit" class="btn btn-green">&#128269; Chercher</button>
        <?php if($search): ?><a href="/admin/activity-log" class="btn btn-outline">&#215; Effacer</a><?php endif; ?>
    </form>

    <div class="table-wrap">
        <div class="table-head">
            <h3>Logs récents</h3>
            <span class="tag tag-g"><?php echo count($logs); ?> résultat(s)</span>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Utilisateur</th><th>Action</th><th>Détails</th><th>Date</th></tr>
            </thead>
            <tbody>
            <?php if(empty($logs)): ?>
                <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-l);">Aucun log trouvé.</td></tr>
            <?php else: ?>
            <?php foreach($logs as $l): ?>
            <?php
                $action = $l['ACTION'] ?? '';
                $badge_color = '#6c757d';
                if(stripos($action, 'login') !== false) $badge_color = '#28a745';
                elseif(stripos($action, 'logout') !== false) $badge_color = '#17a2b8';
                elseif(stripos($action, 'register') !== false) $badge_color = '#6f42c1';
                elseif(stripos($action, 'delete') !== false) $badge_color = '#d9534f';
                elseif(stripos($action, 'update') !== false || stripos($action, 'change') !== false) $badge_color = '#fd7e14';
            ?>
            <tr>
                <td><?php echo (int)($l['ID_LOG'] ?? 0); ?></td>
                <td><strong><?php echo htmlspecialchars($l['USER_NAME'] ?? $l['NAME'] ?? 'Système'); ?></strong></td>
                <td><span style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;color:#fff;background:<?php echo $badge_color; ?>;"><?php echo htmlspecialchars($action); ?></span></td>
                <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($l['DETAILS'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($l['LOG_DATE'] ?? ''); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(dirname(__DIR__)) . '/Views/partials/footer.php'; ?>
</body>
</html>
