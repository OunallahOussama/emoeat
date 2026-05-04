<?php
/* ================================================
   forgot_password.php - Réinitialisation du mot de passe
   Étape 1 : l'utilisateur entre son email.
   Étape 2 : il choisit un nouveau mot de passe.
   ================================================ */
session_start();
include("connexion.php");

/* Si l'utilisateur est déjà connecté, pas besoin de reset */
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$step    = 1;   /* Étape actuelle : 1 = saisie email, 2 = nouveau mot de passe */
$error   = '';
$success = '';
$found_email = '';

/* Étape 1 : on vérifie que l'email existe dans la base */
if(isset($_POST['check_email'])) {
    $email = trim($_POST['email']);
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez entrer une adresse email valide.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) AS CNT FROM USERS WHERE EMAIL = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if((int)$row['CNT'] === 0) {
                $error = "Aucun compte trouvé avec cette adresse email.";
            } else {
                /* Email trouvé : on passe à l'étape 2 */
                $step = 2;
                $found_email = $email;
            }
        } catch(PDOException $e) {
            $error = "Erreur de base de données : " . htmlspecialchars($e->getMessage());
        }
    }
}

/* Étape 2 : on enregistre le nouveau mot de passe chiffré */
if(isset($_POST['reset_password'])) {
    $email    = trim($_POST['email']);
    $password = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    if(empty($password)) {
        $error = "Veuillez entrer un nouveau mot de passe.";
        $step  = 2;
        $found_email = $email;
    } elseif(strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
        $step  = 2;
        $found_email = $email;
    } elseif($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
        $step  = 2;
        $found_email = $email;
    } else {
        try {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE USERS SET PASSWORD = :pwd WHERE EMAIL = :email");
            $stmt->bindParam(':pwd',   $hashed);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            /* Log de l'activité */
            $stId = $conn->prepare("SELECT ID_USER FROM USERS WHERE EMAIL = :email");
            $stId->bindParam(':email', $email);
            $stId->execute();
            $uRow = $stId->fetch(PDO::FETCH_ASSOC);
            if($uRow) {
                logActivity($conn, (int)$uRow['ID_USER'], 'PASSWORD_RESET');
            }

            $success = "Mot de passe réinitialisé avec succès ! Vous pouvez maintenant vous connecter.";
            $step = 1;
        } catch(PDOException $e) {
            $error = "Erreur : " . htmlspecialchars($e->getMessage());
            $step  = 2;
            $found_email = $email;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - EmoEat</title>
    <link rel="stylesheet" href="style.css?v=25">
</head>
<body>
<?php include('navbar.php'); ?>

<div class="auth-wrap">
    <div class="form-card" style="max-width:460px;">
        <div class="form-logo">
            <div class="logo-circle">&#128273;</div>
            <h2>Mot de passe oublié</h2>
            <p>
                <?php if($step === 1): ?>
                    Entrez votre adresse email pour réinitialiser votre mot de passe.
                <?php else: ?>
                    Choisissez un nouveau mot de passe pour <strong><?php echo htmlspecialchars($found_email); ?></strong>.
                <?php endif; ?>
            </p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger">&#9888; <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="alert alert-success">&#10004; <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if($step === 1 && empty($success)): ?>
        <!-- Étape 1 : Vérification email -->
        <form method="POST" novalidate>
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" class="form-control"
                       placeholder="votre@email.com" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <button type="submit" name="check_email" class="btn btn-green btn-full" style="margin-top:8px;">
                &#128269; Vérifier mon email
            </button>
        </form>

        <?php elseif($step === 2): ?>
        <!-- Étape 2 : Nouveau mot de passe -->
        <form method="POST" novalidate>
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($found_email); ?>">
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
            <a href="login.php">&larr; Retour à la connexion</a>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
</body>
</html>


