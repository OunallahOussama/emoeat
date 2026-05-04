<?php
/* ================================================
   admin_activity_log.php -?" Journal d'activité
   Affiche toutes les actions effectuées par les
   utilisateurs (connexions, déconnexions, modifications).
   On peut aussi faire une recherche par nom ou action.
   ================================================ */
session_start();
include("connexion.php");

/* Protection : réservé aux administrateurs */
if(!isset($_SESSION['user_id']) || strtoupper(trim($_SESSION['role'])) !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

/* -"?-"? Chargement de toutes les entrées du journal, avec possibilité de filtrer -"?-"? */
$logs = [];
$search = trim($_GET['q'] ?? '');
try {
    if($search !== '') {
        $stmt = $conn->prepare(
            "SELECT al.ID_LOG, al.ACTION, al.LOG_DATE, u.NAME, u.EMAIL
             FROM ACTIVITY_LOG al JOIN USERS u ON u.ID_USER = al.ID_USER
             WHERE LOWER(u.NAME) LIKE :q1 OR LOWER(u.EMAIL) LIKE :q2 OR LOWER(al.ACTION) LIKE :q3
             ORDER BY al.LOG_DATE DESC"
        );
        $like = '%' . strtolower($search) . '%';
        $stmt->bindParam(':q1', $like);
        $stmt->bindParam(':q2', $like);
        $stmt->bindParam(':q3', $like);
    } else {
        $stmt = $conn->prepare(
            "SELECT al.ID_LOG, al.ACTION, al.LOG_DATE, u.NAME, u.EMAIL
             FROM ACTIVITY_LOG al JOIN USERS u ON u.ID_USER = al.ID_USER
             ORDER BY al.LOG_DATE DESC"
        );
    }
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Erreur de chargement : " . htmlspecialchars($e->getMessage());
}

function actionBadge(string $action): string {
    if(strpos($action, 'LOGIN')    !== false) return '<span class="tag tag-g">' . htmlspecialchars($action) . '</span>';
    if(strpos($action, 'LOGOUT')   !== false) return '<span class="tag tag-o">' . htmlspecialchars($action) . '</span>';
    if(strpos($action, 'REGISTER') !== false) return '<span class="tag tag-b">' . htmlspecialchars($action) . '</span>';
    if(strpos($action, 'DELETE')   !== false) return '<span class="tag tag-r">' . htmlspecialchars($action) . '</span>';
    return '<span class="tag">' . htmlspecialchars($action) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal d'activité &#9881;&#65039; EmoEat Admin</title>
    <link rel="stylesheet" href="style.css?v=25">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="page-wrap">

    <div class="breadcrumb">
        <a href="dashboard_admin.php">&#9881;&#65039; Admin</a>  &rsaquo; Journal d'activité
    </div>

    <div class="page-header-row page-header">
        <div>
            <h1>&#128202; Journal d'activité</h1>
            <p>Historique complet des actions enregistrées dans la table <strong>ACTIVITY_LOG</strong>.</p>
        </div>
        <a href="dashboard_admin.php" class="btn btn-outline"> &rsaquo; Retour Admin</a>
    </div>

    <?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="GET" style="margin-bottom:16px;display:flex;gap:10px;">
        <input type="text" name="q" class="form-control" placeholder="Rechercher par nom, email ou action?"
               value="<?php echo htmlspecialchars($search); ?>" style="max-width:400px;">
        <button type="submit" class="btn btn-green">Y"</button>
        <?php if($search): ?><a href="admin_activity_log.php" class="btn btn-outline">o.</a><?php endif; ?>
    </form>

    <div class="table-wrap">
        <div class="table-head">
            <h3>Entrées ACTIVITY_LOG</h3>
            <span class="tag tag-g"><?php echo count($logs); ?> entrée(s)</span>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Utilisateur</th><th>Email</th><th>Action</th><th>Date</th></tr>
            </thead>
            <tbody>
            <?php if(empty($logs)): ?>
                <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-l);">Aucune entrée dans le journal.</td></tr>
            <?php else: ?>
            <?php foreach($logs as $l): ?>
            <tr>
                <td><?php echo (int)$l['ID_LOG']; ?></td>
                <td><strong><?php echo htmlspecialchars($l['NAME'] ?? ''); ?></strong></td>
                <td><?php echo htmlspecialchars($l['EMAIL'] ?? ''); ?></td>
                <td><?php echo actionBadge($l['ACTION'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($l['LOG_DATE'] ?? ''); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include('footer.php'); ?>
</body>
</html>


