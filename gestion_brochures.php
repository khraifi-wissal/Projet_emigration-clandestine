<?php
include 'connexion.php'; 

$message = '';
$created_by_admin_id = 1; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_brochure'])) {
    $title = trim($_POST['title']);
    $file_path = trim($_POST['file_path']); 

    if (empty($title) || empty($file_path)) {
        $message = '<div class="alert alert-danger">Veuillez remplir le Titre et le Chemin du fichier.</div>';
    } else {
        $sql = "INSERT INTO brochures (title, file_path, created_by) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
             $message = '<div class="alert alert-danger">Erreur de préparation: ' . $conn->error . '</div>';
        } else {
            $stmt->bind_param("ssi", $title, $file_path, $created_by_admin_id);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Brochure "<b>' . htmlspecialchars($title) . '</b>" ajoutée avec succès!</div>';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de l\'ajout de la brochure: ' . $conn->error . '</div>';
            }
            $stmt->close();
        }
    }
}

$brochures = [];
$sql_select = "
    SELECT b.brochure_id, b.title, b.file_path, b.created_at, a.username AS admin_username
    FROM brochures b
    JOIN admins a ON b.created_by = a.admin_id
    ORDER BY b.created_at DESC
";
$result = $conn->query($sql_select);

if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $brochures[] = $row;
        }
    }
    $result->free();
} else {
    $message .= '<div class="alert alert-danger">Erreur de lecture des brochures: ' . $conn->error . '</div>';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Brochures - Nafas Admin</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
    
        <div class="navigation">
            <ul>
                <li>
                    <a href="#">
        
                        <span class="title">Nafas</span>
                    </a>
                </li>

                <li class="hovered">
                    <a href="#">
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
                    <a href="gestion_quiz.php">
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
                    <a href="admin_login.php">
                        <span class="icon">
                            <ion-icon name="sign-out"></ion-icon>
                        </span>
                        <span class="title">Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="main">
            <div class="topbar">
                <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
                <div class="search"><label><input type="text" placeholder="Rechercher..."><ion-icon name="search-outline"></ion-icon></label></div>
                <div class="user"><img src="image.png" alt=""></div>
            </div>

            <div class="details p-3 p-md-5">

                <h1 class="mb-4" style="color: var(--blue);">Gestion des Brochures</h1>
                
                <?php echo $message; ?>

                <div class="recentOrders" style="min-height: auto; margin-bottom: 30px;">
                    <div class="cardHeader">
                        <h2>Ajouter une Nouvelle Brochure</h2>
                    </div>
                    
                    <form action="gestion_brochures.php" method="POST" class="p-3">
                        <input type="hidden" name="add_brochure" value="1">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Titre de la Brochure</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="file_path" class="form-label">Chemin du Fichier (Ex: /uploads/doc_1.pdf)</label>
                            <input type="text" class="form-control" id="file_path" name="file_path" placeholder="Ceci sera le chemin d'accès pour le téléchargement" required>
                        </div>
                        
                        <button type="submit" class="btn btn-success mt-2">Publier la Brochure</button>
                    </form>
                </div>

                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Liste des Brochures Publiées (<?php echo count($brochures); ?>)</h2>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <td>ID</td>
                                <td>Titre</td>
                                <td>Publié par</td>
                                <td>Date</td>
                                <td>Chemin du Fichier</td>
                                <td>Action</td>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($brochures) > 0): ?>
                                <?php foreach ($brochures as $b): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($b['brochure_id']); ?></td>
                                        <td><?php echo htmlspecialchars($b['title']); ?></td>
                                        <td><?php echo htmlspecialchars($b['admin_username']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($b['created_at'])); ?></td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($b['file_path']); ?>" target="_blank" class="status delivered" style="text-decoration: none;">Voir Fichier</a>
                                        </td>
                                        <td><button class="status return btn btn-sm">Supprimer</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucune brochure trouvée.</td>
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