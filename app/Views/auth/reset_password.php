<?php
/**
 * Reset password view
 * Variables: $error, $success, $validToken, $tokenRow, $token
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe - EmoEat</title>
    <link rel="stylesheet" href="/style.css?v=25">
</head>
<body>
<?php require dirname(dirname(__DIR__)) . '/Views/partials/navbar.php'; ?>

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
        <form method="POST" action="/reset-password" novalidate>
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
                <a href="/login">&#10132; Se connecter</a>
            <?php else: ?>
                <a href="/forgot-password">&#8592; Refaire une demande</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require dirname(dirname(__DIR__)) . '/Views/partials/footer.php'; ?>
</body>
</html>
