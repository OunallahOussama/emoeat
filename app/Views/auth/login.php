<?php
/**
 * Login view
 * Variables: $error
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - EmoEat</title>
    <link rel="stylesheet" href="/style.css?v=25">
</head>
<body>
<?php require dirname(dirname(__DIR__)) . '/Views/partials/navbar.php'; ?>

<div class="auth-split">
    <div class="auth-image-panel">
        <img src="/images/food-colorful.jpg" alt="Alimentation colorée saine" loading="lazy">
        <div class="auth-image-overlay">
            <span class="aio-badge">🥗 Nutrition Émotionnelle</span>
            <h2>Mangez selon ce que vous ressentez</h2>
            <p>Connectez-vous et laissez EmoEat vous guider vers une alimentation adaptée à vos émotions.</p>
        </div>
    </div>

    <div class="auth-form-panel">
        <div class="form-card">
            <div class="form-logo">
                <div class="logo-circle">🥗</div>
                <h2>Bon retour !</h2>
                <p>Connectez-vous à votre compte EmoEat</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">⚠️ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="/login" novalidate>
                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email" class="form-control"
                           placeholder="votre@email.com" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="pass-wrap">
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="••••••••" required>
                        <button type="button" class="pass-toggle" onclick="togglePass('password',this)" title="Afficher/masquer">👁</button>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-green btn-full" style="margin-top:4px;">
                    🔑 Se connecter
                </button>
            </form>

            <div class="form-footer" style="margin-top:16px;">
                <a href="/forgot-password" style="color:var(--text-l);font-size:13px;">🔒 Mot de passe oublié ?</a>
            </div>
            <div class="form-divider"></div>
            <div class="form-footer" style="margin-top:0;">
                Pas encore de compte ? <a href="/register">Créer un compte</a>
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
