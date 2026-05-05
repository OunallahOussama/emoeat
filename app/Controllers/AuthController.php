<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\PasswordResetToken;

class AuthController extends Controller
{
    private User $userModel;
    private ActivityLog $activityLog;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User($this->db);
        $this->activityLog = new ActivityLog($this->db);
    }

    public function loginForm(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect($_SESSION['role'] === 'ADMIN' ? '/admin/dashboard' : '/dashboard');
        }
        $this->view('auth/login', ['error' => '']);
    }

    public function login(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect($_SESSION['role'] === 'ADMIN' ? '/admin/dashboard' : '/dashboard');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->view('auth/login', ['error' => 'Veuillez remplir tous les champs.']);
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            $this->view('auth/login', ['error' => 'Aucun compte trouve avec cet email.']);
            return;
        }

        if (!password_verify($password, $user['PASSWORD'])) {
            $this->view('auth/login', ['error' => 'Mot de passe incorrect.']);
            return;
        }

        $_SESSION['user_id'] = $user['ID_USER'];
        $_SESSION['user_name'] = $user['NAME'];
        $_SESSION['role'] = strtoupper(trim($user['ROLE']));

        $this->activityLog->log((int)$user['ID_USER'], 'USER_LOGIN');

        if ($_SESSION['role'] === 'ADMIN') {
            $this->redirect('/admin/dashboard');
        } else {
            $this->redirect('/dashboard');
        }
    }

    public function registerForm(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/register', ['error' => '', 'success' => '']);
    }

    public function register(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            $this->view('auth/register', ['error' => 'Veuillez remplir tous les champs obligatoires.', 'success' => '']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view('auth/register', ['error' => 'Adresse email invalide.', 'success' => '']);
            return;
        }

        if (strlen($password) < 6) {
            $this->view('auth/register', ['error' => 'Le mot de passe doit contenir au moins 6 caracteres.', 'success' => '']);
            return;
        }

        if ($password !== $confirm) {
            $this->view('auth/register', ['error' => 'Les mots de passe ne correspondent pas.', 'success' => '']);
            return;
        }

        if ($this->userModel->emailExists($email)) {
            $this->view('auth/register', ['error' => 'Cet email est deja utilise.', 'success' => '']);
            return;
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $newId = $this->userModel->create($name, $email, $hashed);

        $this->activityLog->log($newId, 'USER_REGISTER');

        $subject = "Bienvenue sur EmoEat, " . $name . " !";
        $body = "Bonjour " . $name . ",\r\n\r\n";
        $body .= "Votre compte EmoEat a été créé avec succès !\r\n\r\n";
        $body .= "Voici vos informations :\r\n";
        $body .= "- Nom : " . $name . "\r\n";
        $body .= "- Email : " . $email . "\r\n";
        $body .= "- Rôle : Client\r\n\r\n";
        $body .= "Vous pouvez vous connecter dès maintenant sur EmoEat.\r\n\r\n";
        $body .= "-- L'équipe EmoEat";
        $headers = "From: no-reply@emoeat.health\r\nReply-To: no-reply@emoeat.health\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        @mail($email, $subject, $body, $headers);

        $this->view('auth/register', [
            'error' => '',
            'success' => 'Compte cree avec succes ! Un email de confirmation a été envoyé. Vous pouvez maintenant vous connecter.'
        ]);
    }

    public function logout(): void
    {
        if ($this->isLoggedIn()) {
            $this->activityLog->log($this->getUserId(), 'USER_LOGOUT');
        }
        session_destroy();
        $this->redirect('/login');
    }

    public function forgotPasswordForm(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $this->view('auth/forgot_password', ['error' => '', 'success' => '']);
    }

    public function forgotPassword(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->view('auth/forgot_password', ['error' => 'Veuillez entrer une adresse email valide.', 'success' => '']);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            $this->view('auth/forgot_password', ['error' => 'Aucun compte trouvé avec cette adresse email.', 'success' => '']);
            return;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $tokenModel = new PasswordResetToken($this->db);
        $tokenModel->create((int)$user['ID_USER'], $token, $expiresAt);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $resetLink = $protocol . '://' . $host . '/reset-password?token=' . $token;

        $subject = "EmoEat - Réinitialisation de votre mot de passe";
        $message = "Bonjour " . htmlspecialchars($user['NAME']) . ",\r\n\r\n";
        $message .= "Cliquez sur le lien suivant pour définir un nouveau mot de passe :\r\n";
        $message .= $resetLink . "\r\n\r\n";
        $message .= "Ce lien expire dans 1 heure.\r\n\r\n";
        $message .= "-- L'équipe EmoEat";
        $headers = "From: no-reply@emoeat.health\r\nReply-To: no-reply@emoeat.health\r\nContent-Type: text/plain; charset=UTF-8\r\n";

        if (@mail($email, $subject, $message, $headers)) {
            $this->activityLog->log((int)$user['ID_USER'], 'PASSWORD_RESET_REQUESTED');
            $this->view('auth/forgot_password', ['error' => '', 'success' => 'Un email de réinitialisation a été envoyé à votre adresse.']);
        } else {
            $this->view('auth/forgot_password', ['error' => 'Erreur lors de l\'envoi de l\'email.', 'success' => '']);
        }
    }

    public function resetPasswordForm(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $token = trim($_GET['token'] ?? '');
        $tokenModel = new PasswordResetToken($this->db);
        $tokenRow = null;
        $error = '';

        if (empty($token)) {
            $error = 'Aucun token de réinitialisation fourni.';
        } else {
            $tokenRow = $tokenModel->validate($token);
            if (!$tokenRow) {
                $error = 'Ce lien est invalide ou a expiré. Veuillez refaire une demande.';
            }
        }

        $this->view('auth/reset_password', [
            'error' => $error,
            'success' => '',
            'validToken' => $tokenRow !== null,
            'tokenRow' => $tokenRow,
            'token' => $token,
        ]);
    }

    public function resetPassword(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $token = trim($_POST['token'] ?? '');
        $tokenModel = new PasswordResetToken($this->db);
        $tokenRow = $tokenModel->validate($token);

        if (!$tokenRow) {
            $this->view('auth/reset_password', [
                'error' => 'Ce lien est invalide ou a expiré.',
                'success' => '',
                'validToken' => false,
                'tokenRow' => null,
                'token' => $token,
            ]);
            return;
        }

        $password = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($password)) {
            $this->view('auth/reset_password', ['error' => 'Veuillez entrer un nouveau mot de passe.', 'success' => '', 'validToken' => true, 'tokenRow' => $tokenRow, 'token' => $token]);
            return;
        }

        if (strlen($password) < 6) {
            $this->view('auth/reset_password', ['error' => 'Le mot de passe doit contenir au moins 6 caractères.', 'success' => '', 'validToken' => true, 'tokenRow' => $tokenRow, 'token' => $token]);
            return;
        }

        if ($password !== $confirm) {
            $this->view('auth/reset_password', ['error' => 'Les mots de passe ne correspondent pas.', 'success' => '', 'validToken' => true, 'tokenRow' => $tokenRow, 'token' => $token]);
            return;
        }

        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $this->userModel->updatePassword((int)$tokenRow['ID_USER'], $hashed);
        $tokenModel->markUsed((int)$tokenRow['ID_TOKEN']);
        $this->activityLog->log((int)$tokenRow['ID_USER'], 'PASSWORD_RESET');

        $this->view('auth/reset_password', [
            'error' => '',
            'success' => 'Mot de passe réinitialisé avec succès ! Vous pouvez maintenant vous connecter.',
            'validToken' => false,
            'tokenRow' => null,
            'token' => '',
        ]);
    }
}
