<?php
// admin_login.php - Fichier unique de connexion et traitement

// 1. Démarrer la session en premier (TOUJOURS)
session_start();

// Si l'administrateur est déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// 2. Inclure la connexion à la BDD
// Assurez-vous que ce fichier existe !
require 'connexion.php'; 

$error_message = ''; 

// 3. Traitement du formulaire POST (Le formulaire soumet à cette même page)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($_POST['email']) && isset($_POST['password'])) {
        
        $email = trim($_POST['email']);
        $password = $_POST['password']; // Mot de passe en clair posté

        // Requête sécurisée pour trouver l'admin par email
        $sql = "SELECT admin_id, username, password FROM admins WHERE email = ?";
        $req = $conn->prepare($sql);

        if ($req === false) {
            $error_message = "Erreur interne de base de données. (Code 500)";
        } else {
            $req->bind_param("s", $email);
            $req->execute();
            $result = $req->get_result();

            // Vérifier si l'admin a été trouvé
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                // --- DEBUG TEMPORAIRE (Décommentez pour voir les valeurs) ---
                /*
                echo "DEBUG INFO:<br>";
                echo "Email POSTÉ : " . htmlspecialchars($email) . "<br>";
                echo "Email trouvé en BDD: " . htmlspecialchars($admin['email']) . "<br>";
                echo "Hash en BDD: " . htmlspecialchars($admin['password']) . "<br>";
                echo "Résultat verification (1=VRAI, VIDE=FAUX): "; 
                var_dump(password_verify($password, $admin['password']));
                echo "<br>FIN DEBUG<hr>";
                exit; // Arrêter le script ici pour voir le DEBUG
                */
                // --- FIN DEBUG TEMPORAIRE ---
                
                // 🔑 Vérification du mot de passe haché
                if (password_verify($password, $admin['password'])) {
                    
                    // Connexion réussie : Création de la session
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    
                    // Mise à jour de last_login (bonne pratique)
                    $update_sql = "UPDATE admins SET last_login = NOW() WHERE admin_id = ?";
                    $update_req = $conn->prepare($update_sql);
                    $update_req->bind_param("i", $admin['admin_id']);
                    $update_req->execute();

                    // Redirection vers le dashboard
                    header("Location: index.php");
                    exit;
                }
            }
            
            // ÉCHEC : Si l'email est inconnu ou le mot de passe est faux
            $error_message = "Email ou mot de passe incorrect.";
        }
    }
}

// Fermeture de la connexion à la fin du traitement PHP
if (isset($conn)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion Admin</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <script src="js/bootstrap.bundle.min.js"></script>
    
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="col-md-4 mx-auto">
        <div class="card p-4 shadow">
            <h3 class="text-center mb-3">Connexion Admin</h3>

            <?php 
            // Affichage du message d'erreur
            if (!empty($error_message)) {
                echo '<div class="alert alert-danger text-center">' . htmlspecialchars($error_message) . '</div>';
            }
            ?>
            
            <form action="admin_login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" class="form-control" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>

            <p class="text-center mt-3">
                Pas encore inscrit ? 
                <a href="admin_register.html">Créer un compte</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>