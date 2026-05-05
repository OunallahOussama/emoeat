<?php
/**
 * Forgot password view
 * Variables: $error, $success
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - EmoEat</title>
    <link rel="stylesheet" href="/style.css?v=25">
</head>
<body>
<?php require dirname(dirname(__DIR__)) . '/Views/partials/navbar.php'; ?>

<div class="auth-wrap">
    <div class="form-card" style="max-width:460px;">
        <div class="form-logo">
            <div class="logo-circle">&#128273;</div>
            <h2>Mot de passe oublié</h2>
            <p>Entrez votre adresse email. Vous recevrez un lien pour réinitialiser votre mot de passe.</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger">&#9888; <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="alert alert-success">&#10004; <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if(empty($success)): ?>
        <form method="POST" action="/forgot-password" novalidate>
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" class="form-control"
                       placeholder="votre@email.com" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <button type="submit" name="send_reset" class="btn btn-green btn-full" style="margin-top:8px;">
                &#128233; Envoyer le lien de réinitialisation
            </button>
        </form>
        <?php endif; ?>

        <div class="form-footer">
            <a href="/login">&larr; Retour à la connexion</a>
        </div>
    </div>
</div>

<?php require dirname(dirname(__DIR__)) . '/Views/partials/footer.php'; ?>
</body>
</html>
