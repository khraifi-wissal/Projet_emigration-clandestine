<?php
// On inclut la connexion qui utilise PDO
require_once 'connexion.php'; 

$message = '';

// --- AJOUT D'UN MEMBRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_member'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; 

    if (empty($username) || empty($email) || empty($password)) {
        $message = '<div class="alert alert-danger">Veuillez remplir tous les champs.</div>';
    } else {
        try {
            // Hachage du mot de passe (Sécurité)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Requête avec PDO
            $sql = "INSERT INTO members (username, email, password) VALUES (:username, :email, :password)";
            $stmt = $conn->prepare($sql);
            
            $stmt->execute([
                ':username' => $username,
                ':email'    => $email,
                ':password' => $hashed_password
            ]);

            $message = '<div class="alert alert-success">Membre <b>' . htmlspecialchars($username) . '</b> ajouté avec succès!</div>';
        } catch (PDOException $e) {
            // Gestion de l'erreur d'email en double (Duplicate entry)
            if ($e->getCode() == 23000) { 
                $message = '<div class="alert alert-danger">Erreur : L\'email existe déjà.</div>';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de l\'ajout: ' . $e->getMessage() . '</div>';
            }
        }
    }
}

// --- LECTURE DES MEMBRES ---
$members = [];
try {
    $sql_select = "SELECT member_id, username, email, created_at, last_login FROM members ORDER BY created_at DESC";
    
    // En PDO, on utilise query() pour les SELECT simples
    $stmt_select = $conn->query($sql_select);
    
    // fetchAll remplace la boucle while + fetch_assoc + num_rows
    $members = $stmt_select->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message .= '<div class="alert alert-danger">Erreur de lecture : ' . $e->getMessage() . '</div>';
}

// Note : Avec PDO, la connexion se ferme automatiquement, pas besoin de $conn->close()
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Membres - Nafas Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    
</head>

<body>
        <div class="navigation">
            <ul>
                <li>
                    <a href="#">
                        <span class="title">Nafas</span>
                    </a>
                </li>

                <li class="hovered">
                    <a href="index.php">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="gestion_membres.php">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">Membres</span>
                    </a>
                </li>

                <li>
                    <a href="gestion_opportunites.php">
                        <span class="icon">
                            <ion-icon name="briefcase-outline"></ion-icon>
                        </span>
                        <span class="title">Opportunités</span>
                    </a>
                </li>

                <li>
                    <a href="gerer_quiz_complet.php">
                        <span class="icon">
                            <ion-icon name="help-circle-outline"></ion-icon>
                        </span>
                        <span class="title">Quiz</span>
                    </a>
                </li>
                
                <li>
                    <a href="gestion_storytelling.php">
                        <span class="icon">
                            <ion-icon name="book-outline"></ion-icon>
                        </span>
                        <span class="title">Storytelling</span>
                    </a>
                </li>
                
                <li>
                    <a href="gestion_brochures.php">
                        <span class="icon">
                            <ion-icon name="document-text-outline"></ion-icon>
                        </span>
                        <span class="title">Brochures</span>
                    </a>
                </li>

                <li>
                    <a href="logout.php">
                        <span class="icon">
                            <ion-icon name="sign-out"></ion-icon>
                        </span>
                        <span class="title">Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="main">
            <div class="details p-3 p-md-5">

                <h1 class="mb-4" style="color: var(--blue);">Gestion des Membres</h1>
                
                <?php echo $message; ?>

                <div class="recentOrders" style="min-height: auto; margin-bottom: 30px;">
                    <div class="cardHeader">
                        <h2>Ajouter un Nouveau Membre</h2>
                    </div>
                    
                    <form action="gestion_membres.php" method="POST" class="p-3">
                        <input type="hidden" name="add_member" value="1">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe initial</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-success mt-2">Ajouter le Membre</button>
                    </form>
                </div>

                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Liste des Membres (<?php echo count($members); ?>)</h2>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <td>ID</td>
                                <td>Nom d'utilisateur</td>
                                <td>Email</td>
                                <td>Inscrit le</td>
                                <td>Dernière Connexion</td>
                                <td>Action</td>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($members) > 0): ?>
                                <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($member['member_id']); ?></td>
                                        <td><?php echo htmlspecialchars($member['username']); ?></td>
                                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($member['created_at'])); ?></td>
                                        <td>
                                            <?php 
                                                echo $member['last_login'] 
                                                    ? date('Y-m-d H:i', strtotime($member['last_login'])) 
                                                    : '<span class="status return">Jamais</span>'; 
                                            ?>
                                        </td>
                                        <td><button class="status return btn btn-sm">Supprimer</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucun membre trouvé.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
            </div>
    </div>

    <script src="assets/js/main.js"></script> 
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>