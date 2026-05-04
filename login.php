<?php
/* ================================================
   login.php — Page de connexion
   L'utilisateur entre son email et son mot de passe.
   Si tout est correct, il est redirigé vers son tableau de bord.
   ================================================ */
session_start();
include("connexion.php");

/* Si l'utilisateur est déjà connecté, on le redirige directement */
if(isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'ADMIN' ? 'dashboard_admin.php' : 'dashboard.php'));
    exit();
}

$error = "";

/* -- Traitement du formulaire quand l'utilisateur clique sur "Se connecter" -- */
if(isset($_POST['login'])) {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    /* Vérification que les champs ne sont pas vides */
    if(empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            /* On cherche l'utilisateur dans la base par son email */
            $stmt = $conn->prepare("SELECT id_user, name, password, role FROM USERS WHERE EMAIL = :email");
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if($user) {
                /* On vérifie que le mot de passe saisi correspond au hash stocké */
                if(password_verify($password, $user['PASSWORD'])) {
                    /* Connexion réussie : on enregistre les infos en session */
                    $_SESSION['user_id']   = $user['ID_USER'];
                    $_SESSION['user_name'] = $user['NAME'];
                    $_SESSION['role']      = strtoupper(trim($user['ROLE']));

                    /* On enregistre l'action dans le journal d'activité */
                    logActivity($conn, (int)$user['ID_USER'], 'USER_LOGIN');

                    /* Redirection selon le rôle : admin ou client */
                    if($_SESSION['role'] === 'ADMIN') {
                        header("Location: dashboard_admin.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit();
                } else {
                    $error = "Mot de passe incorrect.";
                }
            } else {
                $error = "Aucun compte trouve avec cet email.";
            }
        } catch(PDOException $e) {
            $error = "Erreur de base de donnees : " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - EmoEat</title>
    <link rel="stylesheet" href="style.css?v=25">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="auth-split">

    <!-- -- Panneau image gauche -- -->
    <div class="auth-image-panel">
        <img src="images/food-colorful.jpg"
             alt="Alimentation colorée saine" loading="lazy">
        <div class="auth-image-overlay">
            <span class="aio-badge">🥗 Nutrition Émotionnelle</span>
            <h2>Mangez selon ce que vous ressentez</h2>
            <p>Connectez-vous et laissez EmoEat vous guider vers une alimentation adaptée à vos émotions.</p>
        </div>
    </div>

    <!-- -- Panneau formulaire droite -- -->
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

            <form method="POST" novalidate>

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
                <a href="forgot_password.php" style="color:var(--text-l);font-size:13px;">🔒 Mot de passe oublié ?</a>
            </div>
            <div class="form-divider"></div>
            <div class="form-footer" style="margin-top:0;">
                Pas encore de compte ? <a href="register.php">Créer un compte</a>
            </div>

        </div>
    </div><!-- /auth-form-panel -->

</div><!-- /auth-split -->

<?php include('footer.php'); ?>
<script>
function togglePass(id, btn) {
    var input = document.getElementById(id);
    if (input.type === 'password') { input.type = 'text';  btn.textContent = '🙈'; }
    else                           { input.type = 'password'; btn.textContent = '👁'; }
}
(function() {
    var form = document.querySelector('form[method="POST"]');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        var email = document.getElementById('email').value.trim();
        var pass  = document.getElementById('password').value;
        var existing = form.querySelector('.js-error');
        if (existing) existing.remove();
        if (!email || !pass) {
            e.preventDefault();
            var div = document.createElement('div');
            div.className = 'alert alert-danger js-error';
            div.textContent = 'Veuillez remplir tous les champs.';
            form.insertAdjacentElement('beforebegin', div);
        }
    });
})();
</script>
</body>
</html>


