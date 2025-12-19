<?php
// gestion_brochures.php

require 'connexion.php'; 

$message = '';
$created_by_admin_id = 1; 

// --- 1. TRAITEMENT DE L'AJOUT D'UNE NOUVELLE BROCHURE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_brochure'])) {
    $title = trim($_POST['title']);
    $file = $_FILES['brochure_file']; 

    if (empty($title) || $file['error'] != UPLOAD_ERR_OK) {
        $message = '<div class="alert alert-danger">Veuillez fournir un titre et sélectionner un fichier valide.</div>';
    } else {
        
        $upload_dir_relative = 'uploads/brochures/'; 
        $upload_dir_absolute = __DIR__ . '/' . $upload_dir_relative;
        
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('brochure_', true) . '.' . $file_extension;
        $target_file_absolute = $upload_dir_absolute . $new_filename;
        $file_path_db = $upload_dir_relative . $new_filename;

        // Vérification du type MIME (sécurité)
        $allowed_mime_types = ['application/pdf', 'image/jpeg', 'image/png']; 
        if (!in_array(mime_content_type($file['tmp_name']), $allowed_mime_types)) {
            $message = '<div class="alert alert-danger">Type de fichier non autorisé. Seuls PDF, JPG et PNG sont permis.</div>';
        } else {
            if (move_uploaded_file($file['tmp_name'], $target_file_absolute)) {
                
                try {
                    // --- SYNTAXE PDO POUR L'INSERTION ---
                    $sql = "INSERT INTO brochures (title, file_path, created_by) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    
                    if ($stmt->execute([$title, $file_path_db, $created_by_admin_id])) {
                        header("Location: gestion_brochures.php?success=added");
                        exit;
                    } else {
                        $message = '<div class="alert alert-danger">Erreur lors de l\'ajout en base de données.</div>';
                    }
                } catch (PDOException $e) {
                    $message = '<div class="alert alert-danger">Erreur SQL : ' . $e->getMessage() . '</div>';
                }

            } else {
                $message = '<div class="alert alert-danger">Erreur lors du déplacement du fichier. Vérifiez le dossier "uploads/brochures/".</div>';
            }
        }
    }
}

// --- 2. RÉCUPÉRATION DES BROCHURES EXISTANTES ---
$brochures = [];
try {
    $sql_select = "
        SELECT b.brochure_id, b.title, b.file_path, b.created_at, a.username AS admin_username
        FROM brochures b
        JOIN admins a ON b.created_by = a.admin_id
        ORDER BY b.created_at DESC
    ";
    
    // --- SYNTAXE PDO POUR LA LECTURE ---
    $stmt_select = $conn->query($sql_select);
    $brochures = $stmt_select->fetchAll(PDO::FETCH_ASSOC); 
    // fetchAll remplace num_rows et la boucle while manuelle
} catch (PDOException $e) {
    $message .= '<div class="alert alert-danger">Erreur de lecture : ' . $e->getMessage() . '</div>';
}

// --- 3. GESTION DES MESSAGES DE STATUT ---
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'added') {
        $message = '<div class="alert alert-success">Brochure publiée avec succès!</div>';
    } elseif ($_GET['success'] == 'deleted') {
        $message = '<div class="alert alert-success">Brochure supprimée avec succès!</div>';
    }
} elseif (isset($_GET['error']) && $_GET['error'] == 'delete_failed') {
    $message = '<div class="alert alert-danger">Erreur lors de la suppression de la brochure.</div>';
}

// Avec PDO, pas besoin de $conn->close(), la connexion se ferme à la fin du script.
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Brochures - Nafas Admin</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
</head>

<body>
    <div class="container">
    
        <div class="navigation">
            <ul>
                <li><a href="index.php"><span class="icon"><ion-icon name="happy-outline"></ion-icon></span> <span class="title">Nafas</span></a></li>
                <li><a href="index.php"><span class="icon"><ion-icon name="home-outline"></ion-icon></span> <span class="title">Dashboard</span></a></li>
                <li><a href="gestion_membres.php"><span class="icon"><ion-icon name="people-outline"></ion-icon></span> <span class="title">Membres</span></a></li>
                <li><a href="gestion_opportunites.php"><span class="icon"><ion-icon name="briefcase-outline"></ion-icon></span> <span class="title">Opportunités</span></a></li>
                <li><a href="gerer_quiz_complet.php"><span class="icon"><ion-icon name="help-circle-outline"></ion-icon></span> <span class="title">Quiz</span></a></li>
                <li><a href="gestion_storytelling.php"><span class="icon"><ion-icon name="book-outline"></ion-icon></span> <span class="title">Storytelling</span></a></li>
                
                <li class="hovered"><a href="gestion_brochures.php"><span class="icon"><ion-icon name="document-text-outline"></ion-icon></span> <span class="title">Brochures</span></a></li>
                
                <li><a href="logout.php"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span> <span class="title">Déconnexion</span></a></li>
            </ul>
        </div>

        <div class="main">

            <div class="details p-3 p-md-5">

                <h1 class="mb-4" style="color: var(--blue);">Gestion des Brochures</h1>
                
                <?php echo $message; ?>

                <div class="recentOrders" style="min-height: auto; margin-bottom: 30px;">
                    <div class="cardHeader">
                        <h2>Ajouter une Nouvelle Brochure</h2>
                    </div>
                    
                    <form action="gestion_brochures.php" method="POST" enctype="multipart/form-data" class="p-3">
                        <input type="hidden" name="add_brochure" value="1">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Titre de la Brochure</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="brochure_file" class="form-label">Sélectionner le Fichier (PDF, JPG, PNG)</label>
                            <input type="file" class="form-control" id="brochure_file" name="brochure_file" required>
                            <small class="form-text text-muted">Le fichier sera stocké dans <?php echo $upload_dir_relative; ?>.</small>
                        </div>
                        
                        <button type="submit" class="btn status delivered mt-2">Publier la Brochure</button>
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
                                <td>Lien de Téléchargement</td>
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
                                            <a href="<?php echo htmlspecialchars($b['file_path']); ?>" target="_blank" class="status delivered" style="text-decoration: none;">Télécharger</a>
                                        </td>
                                        <td>
                                            <a href="supprimer.php?table=brochures&id=<?php echo $b['brochure_id']; ?>&redirect=gestion_brochures.php" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette brochure ?');"
                                               class="status return btn btn-sm" style="text-decoration: none; padding: 2px 8px;">
                                                Supprimer
                                            </a>
                                        </td>
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