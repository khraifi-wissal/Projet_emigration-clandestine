<?php
// gestion_reponses_story.php

// 1. Décommenter pour utiliser les sessions. Assurez-vous que l'ID de l'admin
// est correctement défini dans $_SESSION['member_id'] lors de la connexion.
// session_start(); 
include 'connexion.php'; 

$message = '';
$story_id = null;
$parent_story_info = null;

// Utiliser l'ID de session si disponible, sinon valeur par défaut 1.
$replying_member_id = $_SESSION['member_id'] ?? 18; // ID du membre pour les réponses Admin (doit être configuré)

// Vérification de l'ID de l'histoire et récupération des informations
if (isset($_GET['story_id']) && is_numeric($_GET['story_id'])) {
    $story_id = (int)$_GET['story_id'];

    // Récupération de l'histoire principale
    $stmt_parent = $conn->prepare("
        SELECT s.story_id, s.content, s.created_at, m.username AS member_username
        FROM storytelling s
        JOIN members m ON s.member_id = m.member_id
        WHERE s.story_id = ? AND s.parent_id IS NULL
    ");
    $stmt_parent->bind_param("i", $story_id);
    $stmt_parent->execute();
    $result_parent = $stmt_parent->get_result();

    if ($result_parent->num_rows === 0) {
        $message = '<div class="alert alert-warning">Histoire principale non trouvée ou ID invalide.</div>';
        $story_id = null; 
    } else {
        $parent_story_info = $result_parent->fetch_assoc();
    }
    $stmt_parent->close();

} else {
    header("Location: gestion_storytelling.php");
    exit();
}

// --- TRAITEMENT DE L'AJOUT DE RÉPONSE PAR L'ADMIN (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_response']) && $story_id !== null) {
    $content = trim($_POST['content']);

    if (empty($content)) {
        $message = '<div class="alert alert-danger">Veuillez entrer le contenu de votre réponse.</div>';
    } else {
        $sql = "INSERT INTO storytelling (member_id, content, parent_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
             $message = '<div class="alert alert-danger">Erreur de préparation: ' . $conn->error . '</div>';
        } else {
            // Utilisation de $replying_member_id (provenant de la session ou valeur par défaut)
            $stmt->bind_param("isi", $replying_member_id, $content, $story_id); 
            
            if ($stmt->execute()) {
                header("Location: gestion_reponses_story.php?story_id=" . $story_id . "&success=reply");
                exit;
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de l\'ajout de la réponse: ' . $conn->error . '</div>';
            }
            $stmt->close();
        }
    }
}

// --- RÉCUPÉRATION DES RÉPONSES EXISTANTES ---
$responses = [];
if ($story_id !== null) {
    $sql_responses = "
        SELECT 
            s.story_id, 
            s.content, 
            s.created_at, 
            m.username AS member_username,
            s.parent_id
        FROM storytelling s
        JOIN members m ON s.member_id = m.member_id
        WHERE s.parent_id = ? 
        ORDER BY s.created_at ASC
    ";
    $stmt_responses = $conn->prepare($sql_responses);
    $stmt_responses->bind_param("i", $story_id);
    $stmt_responses->execute();
    $result_responses = $stmt_responses->get_result();

    if ($result_responses) {
        while($row = $result_responses->fetch_assoc()) {
            $responses[] = $row;
        }
    }
    $stmt_responses->close();
}

// Affichage des messages de statut (succès/erreur)
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'reply') {
         $message = '<div class="alert alert-success">Votre réponse a été publiée avec succès!</div>';
    } elseif ($_GET['success'] == 'deleted') {
         $message = '<div class="alert alert-success">Le post a été supprimé.</div>';
    } elseif ($_GET['success'] == 'updated') {
         $message = '<div class="alert alert-success">Le post a été modifié avec succès.</div>';
    }
}

if (isset($_GET['error']) && $_GET['error'] == 'delete_failed') {
    $message = '<div class="alert alert-danger">Erreur lors de la suppression du post.</div>';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réponses Storytelling - Nafas Admin</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .story-parent-card {
            background-color: var(--gray);
            border-left: 5px solid var(--blue);
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 5px;
        }
    </style>
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
                <li class="hovered"><a href="gestion_storytelling.php"><span class="icon"><ion-icon name="book-outline"></ion-icon></span> <span class="title">Storytelling</span></a></li>
                <li><a href="gestion_brochures.php"><span class="icon"><ion-icon name="document-text-outline"></ion-icon></span> <span class="title">Brochures</span></a></li>
                <li><a href="logout.php"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span> <span class="title">Déconnexion</span></a></li>
            </ul>
        </div>

        <div class="main">
            <div class="topbar">
                <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
                <div class="search"><label><input type="text" placeholder="Rechercher..."><ion-icon name="search-outline"></ion-icon></label></div>
                <div class="user"><img src="image.png" alt=""></div>
            </div>

            <div class="details p-3 p-md-5">

                <h1 class="mb-2" style="color: var(--blue);">Fil de Discussion Storytelling</h1>
                <p><a href="gestion_storytelling.php">← Retour aux Histoires Principales</a></p>
                
                <?php echo $message; ?>

                <?php if ($parent_story_info): ?>
                    
                    <div class="story-parent-card shadow-sm">
                        <h2 class="h5 mb-2">Histoire Initiée (ID: <?php echo $story_id; ?>)</h2>
                        <p class="text-muted small">
                            Par: <b><?php echo htmlspecialchars($parent_story_info['member_username']); ?></b> 
                            | Le: <?php echo date('Y-m-d H:i', strtotime($parent_story_info['created_at'])); ?>
                        </p>
                        <hr>
                        <p><?php echo nl2br(htmlspecialchars($parent_story_info['content'])); ?></p>
                        <div class="mt-3">
                            <a href="modifier_story_post.php?id=<?php echo $story_id; ?>" 
                                class="status inProgress btn btn-sm me-2" style="text-decoration: none; padding: 2px 8px;">
                                 Modifier Histoire
                            </a>
                            <a href="supprimer.php?table=storytelling&id=<?php echo $story_id; ?>&redirect=gestion_storytelling.php" 
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette histoire principale et toutes ses réponses ?');"
                                class="status return btn btn-sm" style="text-decoration: none; padding: 2px 8px;">
                                 Supprimer le Fil
                            </a>
                        </div>
                    </div>

                    <div class="recentOrders" style="min-height: auto; margin-bottom: 30px;">
                        <div class="cardHeader">
                            <h2>Répondre au Fil (En tant que Membre ID: <?php echo $replying_member_id; ?>)</h2>
                        </div>
                        
                        <form action="gestion_reponses_story.php?story_id=<?php echo $story_id; ?>" method="POST" class="p-3">
                            <input type="hidden" name="add_response" value="1">
                            
                            <div class="mb-3">
                                <textarea class="form-control" id="content" name="content" rows="3" required placeholder="Écrivez votre réponse ici..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn status delivered mt-2">Publier la Réponse</button>
                        </form>
                    </div>

                    <div class="recentOrders">
                        <div class="cardHeader">
                            <h2>Réponses reçues (Total: <?php echo count($responses); ?>)</h2>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <td>Membre</td>
                                    <td>Contenu</td>
                                    <td>Date</td>
                                    <td>Action</td>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (count($responses) > 0): ?>
                                    <?php foreach ($responses as $response): ?>
                                        <tr>
                                            <td><b><?php echo htmlspecialchars($response['member_username']); ?></b></td>
                                            <td><?php echo nl2br(htmlspecialchars(substr($response['content'], 0, 150))); ?>...</td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($response['created_at'])); ?></td>
                                            <td>
                                                <a href="modifier_story_post.php?id=<?php echo $response['story_id']; ?>" 
                                                    class="status inProgress btn btn-sm me-2" style="text-decoration: none; padding: 2px 8px;">
                                                     Modifier
                                                </a>
                                                <a href="supprimer.php?table=storytelling&id=<?php echo $response['story_id']; ?>&redirect=gestion_reponses_story.php?story_id=<?php echo $story_id; ?>" 
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réponse ?');"
                                                    class="status return btn btn-sm" style="text-decoration: none; padding: 2px 8px;">
                                                     Supprimer
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Cette histoire n'a pas encore reçu de réponses.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script> 
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>