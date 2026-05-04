<?php
ob_start();
/* ================================================
   register.php — Page d'inscription
   L'utilisateur crée son compte en renseignant
   son nom, email et mot de passe.
   ================================================ */
session_start();
include("connexion.php");

/* Si l'utilisateur est déjà connecté, il n'a pas besoin de s'inscrire */
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error   = "";
$success = "";

/* ── Traitement du formulaire d'inscription ── */
if(isset($_POST['register'])) {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    /* Vérifications de base avant d'insérer en base */
    if(empty($name) || empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } elseif(strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caracteres.";
    } elseif($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        /* On chiffre le mot de passe avant de le stocker (sécurité) */
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        /* Vérification que l'email n'est pas déjà utilisé */
        $chkS = $conn->prepare("SELECT COUNT(*) AS CNT FROM USERS WHERE EMAIL = :email");
        $chkS->bindParam(":email", $email);
        $chkS->execute();
        $chkR = $chkS->fetch(PDO::FETCH_ASSOC);

        if((int)$chkR['CNT'] > 0) {
            $error = "Cet email est deja utilise.";
        } else {
            try {
                /* Insertion du nouvel utilisateur dans la table USERS */
                $stmtU = $conn->prepare(
                    "INSERT INTO USERS (name, email, password, role, created_at)
                     VALUES (:name, :email, :password, 'CLIENT', NOW())"
                );
                $stmtU->bindParam(":name",     $name);
                $stmtU->bindParam(":email",    $email);
                $stmtU->bindParam(":password", $hashed);
                $stmtU->execute();

                /* On récupère l'ID du nouvel utilisateur via lastInsertId */
                $newId = (int) $conn->lastInsertId();

                /* On crée aussi l'entrée dans la table CLIENT */
                $stmtC = $conn->prepare(
                    "INSERT INTO CLIENT (id_user) VALUES (:id_user)"
                );
                $stmtC->bindParam(":id_user", $newId);
                $stmtC->execute();

                /* ACTIVITY_LOG (MPD) */
                logActivity($conn, (int)$newId, 'USER_REGISTER');

                $success = "Compte cree avec succes ! Vous pouvez maintenant vous connecter.";
            } catch(PDOException $e) {
                $error = "Erreur lors de la creation du compte : " . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - EmoEat</title>
    <link rel="stylesheet" href="style.css?v=22">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="auth-split">

    <!-- ── Panneau image gauche ── -->
    <div class="auth-image-panel">
        <img src="images/imagessmoothie-bowls.jpg"
             alt="Smoothie bowls colorés" loading="lazy">
        <div class="auth-image-overlay">
            <span class="aio-badge">🚀 Commencez gratuitement</span>
            <h2>Votre parcours nutritionnel commence ici</h2>
            <p>Créez votre compte et découvrez des recommandations alimentaires personnalisées selon vos émotions.</p>
        </div>
    </div>

    <!-- ── Panneau formulaire droite ── -->
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
            <form method="POST" novalidate>

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
                <a href="login.php" class="btn btn-green">🔑 Se connecter maintenant</a>
            </div>
            <?php endif; ?>

            <div class="form-divider"></div>
            <div class="form-footer">
                Déjà un compte ? <a href="login.php">Se connecter</a>
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
</script>
</body>
</html>
