<?php
/**
 * Admin Users view
 * Variables: $users, $search, $msg, $msg_type, $admin_id
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les utilisateurs &#9881;&#65039; EmoEat Admin</title>
    <link rel="stylesheet" href="/style.css?v=25">
</head>
<body>
<?php require dirname(dirname(__DIR__)) . '/Views/partials/navbar.php'; ?>

<div class="page-wrap">
    <div class="breadcrumb">
        <a href="/admin/dashboard">&#9881;&#65039; Admin</a> &rsaquo; Utilisateurs
    </div>

    <div class="page-header-row page-header">
        <div>
            <h1>&#128101; Gestion des Utilisateurs</h1>
            <p>Consulter, modifier le rôle ou supprimer des comptes.</p>
        </div>
        <a href="/admin/dashboard" class="btn btn-outline">&rsaquo; Retour Admin</a>
    </div>

    <?php if($msg): ?>
    <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <form method="GET" action="/admin/users" style="margin-bottom:20px;display:flex;gap:10px;">
        <input type="text" name="q" class="form-control" placeholder="Rechercher par nom ou email &#128269;"
               value="<?php echo htmlspecialchars($search); ?>" style="max-width:380px;">
        <button type="submit" class="btn btn-green">&#128269; Chercher</button>
        <?php if($search): ?><a href="/admin/users" class="btn btn-outline">&#215; Effacer</a><?php endif; ?>
    </form>

    <div class="table-wrap">
        <div class="table-head">
            <h3>Liste des utilisateurs</h3>
            <span class="tag tag-g"><?php echo count($users); ?> résultat(s)</span>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>#</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Inscription</th><th>Actions</th></tr>
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
                    <form method="POST" action="/admin/users" style="display:inline;">
                        <input type="hidden" name="ch_id" value="<?php echo (int)$u['ID_USER']; ?>">
                        <input type="hidden" name="ch_role" value="<?php echo ($u['ROLE'] === 'ADMIN') ? 'CLIENT' : 'ADMIN'; ?>">
                        <button type="submit" name="change_role" class="btn btn-outline" style="padding:4px 10px;font-size:12px;"
                                onclick="return confirm('Changer le rôle de cet utilisateur ?');">
                            <?php echo ($u['ROLE'] === 'ADMIN') ? '&rarr; CLIENT' : '&rarr; ADMIN'; ?>
                        </button>
                    </form>
                    <?php if((int)$u['ID_USER'] !== $admin_id): ?>
                    <form method="POST" action="/admin/users" style="display:inline;">
                        <input type="hidden" name="del_id" value="<?php echo (int)$u['ID_USER']; ?>">
                        <button type="submit" name="delete_user" class="btn" style="padding:4px 10px;font-size:12px;background:#d9534f;color:#fff;border:none;border-radius:6px;"
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

<?php require dirname(dirname(__DIR__)) . '/Views/partials/footer.php'; ?>
</body>
</html>
