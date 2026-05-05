<?php
/**
 * Register view
 * Variables: $error, $success
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - EmoEat</title>
    <link rel="stylesheet" href="/style.css?v=25">
</head>
<body>
<?php require dirname(dirname(__DIR__)) . '/Views/partials/navbar.php'; ?>

<div class="auth-split">
    <div class="auth-image-panel">
        <img src="/images/imagessmoothie-bowls.jpg" alt="Smoothie bowls colorés" loading="lazy">
        <div class="auth-image-overlay">
            <span class="aio-badge">🚀 Commencez gratuitement</span>
            <h2>Votre parcours nutritionnel commence ici</h2>
            <p>Créez votre compte et découvrez des recommandations alimentaires personnalisées selon vos émotions.</p>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="form-card">
            <div class="form-logo">
                <div class="logo-circle">🥗</div>
                <h2>Créer un compte</h2>
                <p>Rejoignez EmoEat et mangez selon vos émotions</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">⚠️ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if(!empty($success)): ?>
                <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if(empty($success)): ?>
            <form method="POST" action="/register" novalidate>
                <div class="form-group">
                    <label for="name">Nom complet <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="name" name="name" class="form-control"
                           placeholder="Votre nom complet" required
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="email">Adresse email <span style="color:var(--danger)">*</span></label>
                    <input type="email" id="email" name="email" class="form-control"
                           placeholder="votre@email.com" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe <span style="color:var(--danger)">*</span></label>
                    <div class="pass-wrap">
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="Minimum 6 caractères" required minlength="6">
                        <button type="button" class="pass-toggle" onclick="togglePass('password',this)" title="Afficher/masquer">👁</button>
                    </div>
                    <span class="form-hint">Au moins 6 caractères</span>
                </div>

                <div class="form-group">
                    <label for="confirm">Confirmer le mot de passe <span style="color:var(--danger)">*</span></label>
                    <div class="pass-wrap">
                        <input type="password" id="confirm" name="confirm" class="form-control"
                               placeholder="Retapez votre mot de passe" required>
                        <button type="button" class="pass-toggle" onclick="togglePass('confirm',this)" title="Afficher/masquer">👁</button>
                    </div>
                </div>

                <button type="submit" name="register" class="btn btn-green btn-full" style="margin-top:4px;">
                    🚀 Créer mon compte
                </button>
            </form>
            <?php else: ?>
            <div style="text-align:center;margin-top:12px;">
                <a href="/login" class="btn btn-green">🔑 Se connecter maintenant</a>
            </div>
            <?php endif; ?>

            <div class="form-divider"></div>
            <div class="form-footer">
                Déjà un compte ? <a href="/login">Se connecter</a>
            </div>
        </div>
    </div>
</div>

<?php require dirname(dirname(__DIR__)) . '/Views/partials/footer.php'; ?>
<script>
function togglePass(id, btn) {
    var input = document.getElementById(id);
    if (input.type === 'password') { input.type = 'text'; btn.textContent = '🙈'; }
    else { input.type = 'password'; btn.textContent = '👁'; }
}
</script>
</body>
</html>
