<?php
session_start();
include 'connexion.php'; // Vérifiez que ce fichier contient bien votre variable $pdo

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        try {
            // Requête pour trouver l'admin avec l'email ET le mot de passe exact
            $stmt = $conn->prepare("SELECT admin_id, username FROM admins WHERE email = :email AND password = :password");
            $stmt->execute([
                ':email' => $email,
                ':password' => $password
            ]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin) {
                // Si une ligne correspond, la connexion est réussie
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_user'] = $admin['username'];

                header('Location: index.php'); 
                exit();
            } else {
                // Si aucune ligne ne correspond
                $error = "Email ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $error = "Erreur de connexion à la base de données.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Admin</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body { background: #f4f7f6; display: flex; align-items: center; height: 100vh; }
        .card { width: 400px; margin: auto; border: none; border-radius: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow-lg">
        <div class="card-body p-4">
            <h3 class="text-center mb-4">Administration</h3>

            <?php if ($error): ?>
                <div class="alert alert-danger text-center py-2"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Accéder au Dashboard</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>