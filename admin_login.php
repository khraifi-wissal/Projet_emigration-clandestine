<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Login Admin</title>
</head>
<body>

<h2>Connexion Admin</h2>

<?php if (isset($_GET['error'])) echo "<p style='color:red'>Login incorrect</p>"; ?>

<form method="POST" action="login_process.php">
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Mot de passe" required><br><br>
    <button type="submit">Se connecter</button>
</form>

</body>
</html>
