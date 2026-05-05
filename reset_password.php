<?php
/* ================================================
   reset_password.php - Définir un nouveau mot de passe
   L'utilisateur arrive ici via le lien envoyé par email.
   Le token est validé puis il peut choisir un nouveau mdp.
   ================================================ */
session_start();
include("connexion.php");

if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error   = '';
$success = '';
$validToken = false;
$token = '';

/* Vérifier le token dans l'URL */
if(isset($_GET['token'])) {
    $token = trim($_GET['token']);
} elseif(isset($_POST['token'])) {
    $token = trim($_POST['token']);
}

if(!empty($token)) {
    /* Valider : token existe, non utilisé, non expiré */
    try {
        $stmt = $conn->prepare("
            SELECT t.ID_TOKEN, t.ID_USER, u.EMAIL, u.NAME
            FROM PASSWORD_RESET_TOKENS t
            JOIN USERS u ON u.ID_USER = t.ID_USER
            WHERE t.TOKEN = :token AND t.USED = 0 AND t.EXPIRES_AT > NOW()
        ");
        $stmt->execute([':token' => $token]);
        $tokenRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if($tokenRow) {
            $validToken = true;
        } else {
            $error = "Ce lien est invalide ou a expiré. Veuillez refaire une demande.";
        }
    } catch(PDOException $e) {
        $error = "Erreur de base de données.";
    }
} else {
    $error = "Aucun token de réinitialisation fourni.";
}

/* Traitement du formulaire de nouveau mot de passe */
if(isset($_POST['reset_password']) && $validToken && !empty($tokenRow)) {
    $password = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    if(empty($password)) {
        $error = "Veuillez entrer un nouveau mot de passe.";
    } elseif(strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            /* Mettre à jour le mot de passe */
            $stUp = $conn->prepare("UPDATE USERS SET PASSWORD = :pwd WHERE ID_USER = :u");
            $stUp->execute([':pwd' => $hashed, ':u' => $tokenRow['ID_USER']]);

            /* Marquer le token comme utilisé */
            $stTk = $conn->prepare("UPDATE PASSWORD_RESET_TOKENS SET USED = 1 WHERE ID_TOKEN = :id");
            $stTk->execute([':id' => $tokenRow['ID_TOKEN']]);

            /* Logger l'activité */
            logActivity($conn, (int)$tokenRow['ID_USER'], 'PASSWORD_RESET');

            $success = "Mot de passe réinitialisé avec succès ! Vous pouvez maintenant vous connecter.";
            $validToken = false; /* Cacher le formulaire */
        } catch(PDOException $e) {
            $error = "Erreur lors de la réinitialisation.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe - EmoEat</title>
    <link rel="stylesheet" href="style.css?v=25">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="auth-wrap">
    <div class="form-card" style="max-width:460px;">
        <div class="form-logo">
            <div class="logo-circle">&#128274;</div>
            <h2>Nouveau mot de passe</h2>
            <?php if($validToken && !empty($tokenRow)): ?>
                <p>Choisissez un nouveau mot de passe pour <strong><?php echo htmlspecialchars($tokenRow['EMAIL']); ?></strong>.</p>
            <?php endif; ?>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger">&#9888; <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="alert alert-success">&#10004; <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if($validToken): ?>
        <form method="POST" novalidate>
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div class="form-group">
                <label for="new_password">Nouveau mot de passe</label>
                <input type="password" id="new_password" name="new_password" class="form-control"
                       placeholder="••••••••" required minlength="6">
                <small style="color:var(--text-l);font-size:12px;">Minimum 6 caractères</small>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" name="reset_password" class="btn btn-green btn-full" style="margin-top:8px;">
                &#128274; Enregistrer le nouveau mot de passe
            </button>
        </form>
        <?php endif; ?>

        <div class="form-footer">
            <?php if(!empty($success)): ?>
                <a href="login.php">&#10132; Se connecter</a>
            <?php else: ?>
                <a href="forgot_password.php">&#8592; Refaire une demande</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
</body>
</html>
