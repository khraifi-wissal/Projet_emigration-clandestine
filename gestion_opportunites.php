<?php
include 'connexion.php'; 

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_opp'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $region = trim($_POST['region']);
    $link = trim($_POST['link']); 
    
  
    $created_by_admin_id = 1; 

    if (empty($title) || empty($description) || empty($category) || empty($link)) {
        $message = '<div class="alert alert-danger">Veuillez remplir tous les champs obligatoires (Titre, Description, Catégorie, Lien).</div>';
    } else {
        $allowed_categories = ['formation', 'emploi', 'stage', 'projet'];
        if (!in_array($category, $allowed_categories)) {
            $message = '<div class="alert alert-danger">Catégorie non valide.</div>';
        } else {

            $sql = "INSERT INTO opportunities (title, description, category, region, created_by, link) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            
            if ($stmt === false) {
                 $message = '<div class="alert alert-danger">Erreur de préparation: ' . $conn->error . '</div>';
            } else {
                $stmt->bind_param("ssssis", $title, $description, $category, $region, $created_by_admin_id, $link);
                
                if ($stmt->execute()) {
                    $message = '<div class="alert alert-success">Opportunité "<b>' . htmlspecialchars($title) . '</b>" publiée avec succès!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Erreur lors de l\'ajout de l\'opportunité: ' . $conn->error . '</div>';
                }
                $stmt->close();
            }
        }
    }
}

$opportunities = [];
$sql_select = "
    SELECT o.opp_id, o.title, o.description, o.category, o.region, o.created_at, o.link, a.username AS admin_username
    FROM opportunities o
    JOIN admins a ON o.created_by = a.admin_id
    ORDER BY o.created_at DESC
";
$result = $conn->query($sql_select);

if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $opportunities[] = $row;
        }
    }
    $result->free();
} else {
    $message .= '<div class="alert alert-danger">Erreur de lecture des opportunités: ' . $conn->error . '</div>';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Opportunités - Nafas Admin</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <a href="">
                        <span class="icon">
                            <ion-icon name="book-outline"></ion-icon>
                        </span>
                        <span class="title">Storytelling</span>
                    </a>
                </li>
                
                <li>
                    <a href="">
                        <span class="icon">
                            <ion-icon name="document-text-outline"></ion-icon>
                        </span>
                        <span class="title">Brochures</span>
                    </a>
                </li>

                <li>
                    <a href="#">
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

                <h1 class="mb-4" style="color: var(--blue);">Gestion des Opportunités</h1>
                
                <?php echo $message; ?>

                                <div class="recentOrders" style="min-height: auto; margin-bottom: 30px;">
                    <div class="cardHeader">
                        <h2>Ajouter une Nouvelle Opportunité</h2>
                    </div>
                    
                    <form action="gestion_opportunites.php" method="POST" class="p-3">
                        <input type="hidden" name="add_opp" value="1">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Titre</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="link" class="form-label">Lien (URL)</label>
                            <input type="url" class="form-control" id="link" name="link" placeholder="Ex: https://example.com/offre-demploi" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Catégorie</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">-- Choisir --</option>
                                    <option value="formation">Formation</option>
                                    <option value="emploi">Emploi</option>
                                    <option value="stage">Stage</option>
                                    <option value="projet">Projet</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="region" class="form-label">Région</label>
                                <input type="text" class="form-control" id="region" name="region">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success mt-2">Publier l'Opportunité</button>
                    </form>
                </div>

                                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Liste des Opportunités Publiées (<?php echo count($opportunities); ?>)</h2>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <td>Titre</td>
                                <td>Catégorie</td>
                                <td>Publié le</td>
                                <td>Créé par</td>
                                <td>Lien</td>
                                <td>Action</td>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($opportunities) > 0): ?>
                                <?php foreach ($opportunities as $opp): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($opp['title']); ?></td>
                                        <td>
                                            <?php 
                                                $cat_class = ['formation' => 'inProgress', 'emploi' => 'delivered', 'stage' => 'pending', 'projet' => 'return'][$opp['category']] ?? 'inProgress';
                                                echo '<span class="status ' . $cat_class . '">' . ucfirst($opp['category']) . '</span>';
                                            ?>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($opp['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($opp['admin_username']); ?></td>
                                        <td>
                                            <a href="<?php echo htmlspecialchars($opp['link']); ?>" target="_blank" class="status delivered" style="text-decoration: none;">Voir l'offre</a>
                                        </td>
                                        <td><button class="status return btn btn-sm">Supprimer</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucune opportunité trouvée.</td>
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