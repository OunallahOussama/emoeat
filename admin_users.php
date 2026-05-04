<?php
/* ================================================
   admin_users.php -?" Gestion des utilisateurs
   Permet à l'admin de voir, modifier et supprimer
   les comptes utilisateurs du système.
   ================================================ */
session_start();
include("connexion.php");

/* Protection : accès réservé aux administrateurs uniquement */
if(!isset($_SESSION['user_id']) || strtoupper(trim($_SESSION['role'])) !== 'ADMIN') {
    header("Location: login.php");
    exit();
}

$admin_id = (int)$_SESSION['user_id'];
$msg = ''; $msg_type = 'success';

/* -"?-"? Suppression d'un utilisateur -"?-"? */
if(isset($_POST['delete_user'])) {
    $del_id = (int)$_POST['del_id'];
    /* Sécurité : un admin ne peut pas supprimer son propre compte */
    if($del_id === $admin_id) {
        $msg = "Vous ne pouvez pas supprimer votre propre compte.";
        $msg_type = 'danger';
    } else {
        try {
            /* On supprime d'abord toutes les données liées avant de supprimer le compte */
            foreach([
                "DELETE FROM RECOMMENDATIONS  WHERE ID_USER = :id",
                "DELETE FROM USER_EMOTIONS    WHERE ID_USER = :id",
                "DELETE FROM USER_PROFILE     WHERE ID_USER = :id",
                "DELETE FROM ACTIVITY_LOG     WHERE ID_USER = :id",
                "DELETE FROM CLIENT           WHERE ID_USER = :id",
                "DELETE FROM ADMIN            WHERE ID_USER = :id",
                "DELETE FROM USERS            WHERE ID_USER = :id",
            ] as $sql) {
                $st = $conn->prepare($sql);
                $st->bindParam(':id', $del_id, PDO::PARAM_INT);
                $st->execute();
            }
            logActivity($conn, $admin_id, 'ADMIN_DELETE_USER_' . $del_id);
            $msg = "Utilisateur #$del_id supprimé avec succès.";
        } catch(PDOException $e) {
            $msg = "Erreur : " . htmlspecialchars($e->getMessage());
            $msg_type = 'danger';
        }
    }
}

/* -"?-"? Changement de rôle -"?-"? */
if(isset($_POST['change_role'])) {
    $ch_id   = (int)$_POST['ch_id'];
    $ch_role = ($_POST['ch_role'] === 'ADMIN') ? 'ADMIN' : 'CLIENT';
    if($ch_id === $admin_id) {
        $msg = "Vous ne pouvez pas modifier votre propre rôle.";
        $msg_type = 'danger';
    } else {
        try {
            $stR = $conn->prepare("UPDATE USERS SET ROLE = :r WHERE ID_USER = :id");
            $stR->bindParam(':r',  $ch_role, PDO::PARAM_STR);
            $stR->bindParam(':id', $ch_id,   PDO::PARAM_INT);
            $stR->execute();
            // Synchroniser la table ADMIN
            if($ch_role === 'ADMIN') {
                $chk = $conn->prepare("SELECT COUNT(*) AS C FROM ADMIN WHERE ID_USER = :id");
                $chk->bindParam(':id', $ch_id, PDO::PARAM_INT);
                $chk->execute();
                $row = $chk->fetch(PDO::FETCH_ASSOC);
                if((int)$row['C'] === 0) {
                    $ins = $conn->prepare("INSERT INTO ADMIN (ID_USER) VALUES (:id)");
                    $ins->bindParam(':id', $ch_id, PDO::PARAM_INT);
                    $ins->execute();
                }
            } else {
                $delA = $conn->prepare("DELETE FROM ADMIN WHERE ID_USER = :id");
                $delA->bindParam(':id', $ch_id, PDO::PARAM_INT);
                $delA->execute();
            }
            logActivity($conn, $admin_id, 'ADMIN_CHANGE_ROLE_' . $ch_id . '_TO_' . $ch_role);
            $msg = "Rôle de l'utilisateur #$ch_id mis à jour : $ch_role.";
        } catch(PDOException $e) {
            $msg = "Erreur : " . htmlspecialchars($e->getMessage());
            $msg_type = 'danger';
        }
    }
}

/* -"?-"? Chargement de la liste -"?-"? */
$search = trim($_GET['q'] ?? '');
$users  = [];
try {
    if($search !== '') {
        $stmt = $conn->prepare(
            "SELECT ID_USER, NAME, EMAIL, ROLE, CREATED_AT FROM USERS
             WHERE LOWER(NAME) LIKE :q OR LOWER(EMAIL) LIKE :q
             ORDER BY CREATED_AT DESC"
        );
        $like = '%' . strtolower($search) . '%';
        $stmt->bindParam(':q', $like);
    } else {
        $stmt = $conn->prepare(
            "SELECT ID_USER, NAME, EMAIL, ROLE, CREATED_AT FROM USERS ORDER BY CREATED_AT DESC"
        );
    }
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $msg = "Erreur de chargement.";
    $msg_type = 'danger';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les utilisateurs &#9881;&#65039; EmoEat Admin</title>
    <link rel="stylesheet" href="style.css?v=25">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="page-wrap">

    <div class="breadcrumb">
        <a href="dashboard_admin.php">&#9881;&#65039; Admin</a>  &rsaquo; Utilisateurs
    </div>

    <div class="page-header-row page-header">
        <div>
            <h1>&#128101; Gestion des Utilisateurs</h1>
            <p>Consulter, modifier le rôle ou supprimer des comptes.</p>
        </div>
        <a href="dashboard_admin.php" class="btn btn-outline"> &rsaquo; Retour Admin</a>
    </div>

    <?php if($msg): ?>
    <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <!-- Recherche -->
    <form method="GET" style="margin-bottom:20px;display:flex;gap:10px;">
        <input type="text" name="q" class="form-control" placeholder="Rechercher par nom ou email &#128269;"
               value="<?php echo htmlspecialchars($search); ?>" style="max-width:380px;">
        <button type="submit" class="btn btn-green">&#128269; Chercher</button>
        <?php if($search): ?><a href="admin_users.php" class="btn btn-outline">&#215; Effacer</a><?php endif; ?>
    </form>

    <div class="table-wrap">
        <div class="table-head">
            <h3>Liste des utilisateurs</h3>
            <span class="tag tag-g"><?php echo count($users); ?> résultat(s)</span>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Inscription</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($users)): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-l);">Aucun utilisateur trouvé.</td></tr>
            <?php else: ?>
            <?php foreach($users as $u): ?>
            <tr>
                <td><?php echo (int)$u['ID_USER']; ?></td>
                <td><strong><?php echo htmlspecialchars($u['NAME'] ?? ''); ?></strong></td>
                <td><?php echo htmlspecialchars($u['EMAIL'] ?? ''); ?></td>
                <td>
                    <span class="tag <?php echo ($u['ROLE'] === 'ADMIN') ? 'tag-r' : 'tag-g'; ?>">
                        <?php echo htmlspecialchars($u['ROLE'] ?? 'CLIENT'); ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($u['CREATED_AT'] ?? '&#8212;'); ?></td>
                <td style="display:flex;gap:6px;flex-wrap:wrap;">
                    <!-- Changer rôle -->
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="ch_id"   value="<?php echo (int)$u['ID_USER']; ?>">
                        <input type="hidden" name="ch_role" value="<?php echo ($u['ROLE'] === 'ADMIN') ? 'CLIENT' : 'ADMIN'; ?>">
                        <button type="submit" name="change_role"
                                class="btn btn-outline" style="padding:4px 10px;font-size:12px;"
                                onclick="return confirm('Changer le rôle de cet utilisateur ?');">
                            <?php echo ($u['ROLE'] === 'ADMIN') ? '&rarr; CLIENT' : '&rarr; ADMIN'; ?>
                        </button>
                    </form>
                    <!-- Supprimer -->
                    <?php if((int)$u['ID_USER'] !== $admin_id): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="del_id" value="<?php echo (int)$u['ID_USER']; ?>">
                        <button type="submit" name="delete_user"
                                class="btn" style="padding:4px 10px;font-size:12px;background:#d9534f;color:#fff;border:none;border-radius:6px;"
                                onclick="return confirm('Supprimer définitivement cet utilisateur et toutes ses données ?');">
                            &#128465; Supprimer
                        </button>
                    </form>
                    <?php endif; ?>
                </td>
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


