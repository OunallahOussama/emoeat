<?php
/* ================================================
   dashboard_admin.php -?" Tableau de bord administrateur
   Réservé aux admins : affiche les statistiques globales
   (utilisateurs, aliments, émotions, recommandations)
   et les derniers comptes créés.
   ================================================ */
session_start();
include("connexion.php");

/* Protection double : doit être connecté ET avoir le rôle ADMIN */
if(!isset($_SESSION['user_id']) || strtoupper(trim($_SESSION['role'])) !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['user_name'];

/* -"?-"? Compteurs globaux du système -"?-"? */
$totalUsers = 0; $totalFoods = 0; $totalEmo = 0; $totalRec = 0;

/* On exécute 4 requêtes de comptage en une seule boucle */
$sq = [
    "SELECT COUNT(*) AS C FROM USERS"           => &$totalUsers,
    "SELECT COUNT(*) AS C FROM FOODS"           => &$totalFoods,
    "SELECT COUNT(*) AS C FROM EMOTIONS"        => &$totalEmo,
    "SELECT COUNT(*) AS C FROM RECOMMENDATIONS" => &$totalRec,
];

foreach($sq as $sql => &$var) {
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if($r) $var = (int)$r['C'];
    } catch (PDOException $e) {}
}
unset($var);

/* -"?-"? Charge les 10 derniers utilisateurs inscrits -"?-"? */
$users = [];
try {
    $uQ = "SELECT ID_USER, NAME, EMAIL, ROLE, CREATED_AT FROM USERS ORDER BY CREATED_AT DESC";
    $stmt = $conn->prepare($uQ);
    $stmt->execute();
    $uCount = 0;
    while($u = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if($uCount >= 10) break;
        $users[] = $u;
        $uCount++;
    }
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration &#127869; EmoEat</title>
    <link rel="stylesheet" href="style.css?v=25">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="page-wrap">

    <div class="admin-header">
        <div>
            <p style="font-size:12px;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Panneau d'administration</p>
            <h2>&#128075; Bonjour, <?php echo htmlspecialchars($admin_name); ?></h2>
            <p>Gérez les utilisateurs, les aliments, les émotions et les recommandations.</p>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
            <a href="dashboard.php" class="btn btn-outline" style="border-radius:var(--radius-sm); color: white; border-color: white; padding: 8px 12px; text-decoration: none;">&#127968; Accueil Client</a>
            <a href="logout.php" class="btn btn-outline" style="border-radius:var(--radius-sm); background-color: #d9534f; color: white; border: none; padding: 8px 12px; text-decoration: none;">&#128682; Déconnexion</a>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon si-blue">&#128101;</div>
            <div><div class="stat-val"><?php echo $totalUsers; ?></div><div class="stat-lbl">Utilisateurs</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-green">&#129367;</div>
            <div><div class="stat-val"><?php echo $totalFoods; ?></div><div class="stat-lbl">Aliments</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-orange">&#128522;</div>
            <div><div class="stat-val"><?php echo $totalEmo; ?></div><div class="stat-lbl">&Eacute;MOTIONS</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon si-purple">⭐</div>
            <div><div class="stat-val"><?php echo $totalRec; ?></div><div class="stat-lbl">Recommandations</div></div>
        </div>
    </div>

    <h2 style="font-size:18px;font-weight:800;color:var(--primary-d);margin-bottom:16px;">Actions de gestion</h2>
    <div class="admin-actions">
        <a href="admin_users.php" style="text-decoration:none;color:inherit;">
            <div class="action-card" style="cursor:pointer;">
                <div class="action-icon ai-blue">&#128101;</div>
                <div><h4>Gérer les utilisateurs</h4><p>Voir, modifier, supprimer les comptes</p></div>
            </div>
        </a>
        <a href="admin_foods.php" style="text-decoration:none;color:inherit;">
            <div class="action-card" style="cursor:pointer;">
                <div class="action-icon ai-green">&#129367;</div>
                <div><h4>Gérer les aliments</h4><p>Ajouter, éditer les aliments et leurs données</p></div>
            </div>
        </a>
        <a href="admin_emotions.php" style="text-decoration:none; color:inherit;">
            <div class="action-card" style="cursor:pointer;">
                <div class="action-icon ai-orange">&#128522;</div>
                <div><h4>Gérer les émotions</h4><p>Configurer les émotions et leurs règles</p></div>
            </div>
        </a>
        <a href="admin_emotions.php" style="text-decoration:none;color:inherit;">
            <div class="action-card" style="cursor:pointer;">
                <div class="action-icon ai-purple">⭐</div>
                <div><h4>Gérer les recommandations</h4><p>Associer aliments et émotions (EMOTION_FOOD)</p></div>
            </div>
        </a>
        <a href="admin_activity_log.php" style="text-decoration:none;color:inherit;">
            <div class="action-card" style="cursor:pointer;">
                <div class="action-icon ai-red">&#128202;</div>
                <div><h4>Journal d'activité</h4><p>Consulter l'ACTIVITY_LOG des utilisateurs</p></div>
            </div>
        </a>
    </div>

    <div style="margin-top:40px;">
        <div class="table-wrap">
            <div class="table-head">
                <h3>Y' Utilisateurs récents (10 derniers)</h3>
                <span style="font-size:13px;color:var(--text-l);">Total : <?php echo $totalUsers; ?> comptes</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Inscription</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($users)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--text-l);">Aucun utilisateur trouvé.</td></tr>
                    <?php else: ?>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?php echo (int)($u['ID_USER'] ?? 0); ?></td>
                        <td><strong><?php echo htmlspecialchars($u['NAME'] ?? 'Inconnu'); ?></strong></td>
                        <td><?php echo htmlspecialchars($u['EMAIL'] ?? 'Inconnu'); ?></td>
                        <td>
                            <span class="tag <?php echo (isset($u['ROLE']) && $u['ROLE'] === 'ADMIN') ? 'tag-r' : 'tag-g'; ?>">
                                <?php echo htmlspecialchars($u['ROLE'] ?? 'CLIENT'); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($u['CREATED_AT'] ?? 'Date inconnue'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include('footer.php'); ?>
</body>
</html>

