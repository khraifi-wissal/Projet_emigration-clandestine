<?php
session_start();
require_once "connexion.php"; 

$login_error = "";
$signup_error = "";

//LOGIN 
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM members WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Successful login
        $_SESSION['member_id'] = $user['member_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];

        // Update last login
        $update = $conn->prepare("UPDATE members SET last_login = NOW() WHERE member_id = ?");
        $update->bind_param("i", $user['member_id']);
        $update->execute();

        header("Location: index.php"); // Redirect to main page
        exit;
    } else {
        $login_error = "Email ou mot de passe incorrect.";
    }
}

//SIGNUP 
if (isset($_POST['signup'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $signup_error = "Les mots de passe ne correspondent pas.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM members WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $signup_error = "Cet email est déjà utilisé.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO members (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                $signup_success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            } else {
                $signup_error = "Erreur lors de l'inscription. Veuillez réessayer.";
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
    <title>Authentification</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">

        <!-- LOGIN FORM -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Login</h3>
                    <?php if ($login_error): ?>
                        <div class="alert alert-danger"><?= $login_error ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Mot de passe</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" required id="loginPassword">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('loginPassword')">👁️</button>
                            </div>
                        </div>
                        <button type="submit" name="login" class="btn btn-success w-100">Se connecter</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- SIGNUP FORM -->
        <div class="col-md-5 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Inscription</h3>
                    <?php if ($signup_error): ?>
                        <div class="alert alert-danger"><?= $signup_error ?></div>
                    <?php endif; ?>
                    <?php if (!empty($signup_success)): ?>
                        <div class="alert alert-success"><?= $signup_success ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Nom d'utilisateur</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Mot de passe</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" required id="signupPassword">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('signupPassword')">👁️</button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Confirmer le mot de passe</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="signup" class="btn btn-primary w-100">S'inscrire</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

</body>
</html>
