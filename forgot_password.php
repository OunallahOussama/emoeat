<?php
/* ================================================
   forgot_password.php - Réinitialisation du mot de passe
   L'utilisateur entre son email et reçoit un lien
   de réinitialisation par email.
   ================================================ */
session_start();
include("connexion.php");

/* Si l'utilisateur est déjà connecté, pas besoin de reset */
if(isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error   = '';
$success = '';

/* Traitement du formulaire : envoi du lien de réinitialisation */
if(isset($_POST['send_reset'])) {
    $email = trim($_POST['email']);
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez entrer une adresse email valide.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT ID_USER, NAME FROM USERS WHERE EMAIL = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$user) {
                $error = "Aucun compte trouvé avec cette adresse email.";
            } else {
                /* Générer un token unique */
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                /* Invalider les anciens tokens pour cet utilisateur */
                $stDel = $conn->prepare("UPDATE PASSWORD_RESET_TOKENS SET USED = 1 WHERE ID_USER = :u AND USED = 0");
                $stDel->execute([':u' => $user['ID_USER']]);

                /* Sauvegarder le nouveau token */
                $stIns = $conn->prepare("INSERT INTO PASSWORD_RESET_TOKENS (ID_USER, TOKEN, EXPIRES_AT) VALUES (:u, :t, :e)");
                $stIns->execute([':u' => $user['ID_USER'], ':t' => $token, ':e' => $expiresAt]);

                /* Construire le lien de réinitialisation */
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                $resetLink = $protocol . '://' . $host . '/reset_password.php?token=' . $token;

                /* Envoyer l'email */
                $to = $email;
                $subject = "EmoEat - Réinitialisation de votre mot de passe";
                $message = "Bonjour " . htmlspecialchars($user['NAME']) . ",\r\n\r\n";
                $message .= "Vous avez demandé la réinitialisation de votre mot de passe.\r\n\r\n";
                $message .= "Cliquez sur le lien suivant pour définir un nouveau mot de passe :\r\n";
                $message .= $resetLink . "\r\n\r\n";
                $message .= "Ce lien expire dans 1 heure.\r\n\r\n";
                $message .= "Si vous n'avez pas fait cette demande, ignorez cet email.\r\n\r\n";
                $message .= "-- L'équipe EmoEat";

                $headers = "From: noreply@emoeat.health\r\n";
                $headers .= "Reply-To: noreply@emoeat.health\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                if(mail($to, $subject, $message, $headers)) {
                    logActivity($conn, (int)$user['ID_USER'], 'PASSWORD_RESET_REQUESTED');
                    $success = "Un email de réinitialisation a été envoyé à votre adresse. Vérifiez votre boîte de réception.";
                } else {
                    $error = "Erreur lors de l'envoi de l'email. Veuillez réessayer.";
                }
            }
        } catch(PDOException $e) {
            $error = "Erreur de base de données : " . htmlspecialchars($e->getMessage());
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
            <p>Entrez votre adresse email. Vous recevrez un lien pour réinitialiser votre mot de passe.</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger">&#9888; <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div class="alert alert-success">&#10004; <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if(empty($success)): ?>
        <form method="POST" novalidate>
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
            <a href="login.php">&larr; Retour à la connexion</a>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
</body>
</html>


