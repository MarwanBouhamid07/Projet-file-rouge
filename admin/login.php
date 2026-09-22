<?php
/**
 * Admin Login Page
 * Sprint 1 - Recruitment Platform
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Veuillez saisir votre adresse email et votre mot de passe.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === 'admin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['success_message'] = "Connexion réussie ! Bienvenue dans l'espace administration.";

                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Accès refusé : Ce compte ne dispose pas des privilèges administrateur.";
            }
        } else {
            $error = "Identifiants invalides. Veuillez vérifier votre email et mot de passe.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administrateur | SmartMatch</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-brand">
                    <span class="logo-badge">SM</span>
                    <span class="brand-title">SmartMatch</span>
                </div>
                <h2>Espace Administration</h2>
                <p>Veuillez vous connecter pour gérer la plateforme</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <div class="alert-icon">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    </div>
                    <div class="alert-content"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Adresse Email</label>
                    <div class="input-with-icon">
                        <svg class="input-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" placeholder="admin@recrutement.com" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="input-with-icon">
                        <svg class="input-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-hint">
                    <span class="hint-text">Compte de test : <code>admin@recrutement.com</code> / <code>admin123</code></span>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
            </form>

            <div class="login-footer">
                <a href="../public/index.php" class="back-to-site">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                    Retourner au site public
                </a>
            </div>
        </div>
    </div>
</body>
</html>
