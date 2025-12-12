<?php

include 'connexion.php'; 

$message = '';
// Utilisation de l'ID de l'admin comme ID du membre pour la publication admin.
// ASSUREZ-VOUS que l'admin_id (ex: 1) existe aussi dans la table 'members'.


// --- A. TRAITEMENT DU CHANGEMENT DE STATUT (ACCEPTER/REJETER) ---
if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id']) && isset($_GET['new_status'])) {
    $story_id = (int)$_GET['id'];
    $new_status = $_GET['new_status'];
    
    if (in_array($new_status, ['approved', 'rejected'])) {
        
        $sql = "UPDATE storytelling SET status = ? WHERE story_id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("si", $new_status, $story_id);
            $stmt->execute();
            $stmt->close();
            
            $action_verb = ($new_status == 'approved') ? 'acceptée' : 'rejetée';
            header("Location: gestion_storytelling.php?success=" . $action_verb);
            exit;
        } else {
            $message = '<div class="alert alert-danger">Erreur de préparation SQL.</div>';
        }
    }
}

// --- B. TRAITEMENT DE L'AJOUT D'UNE NOUVELLE HISTOIRE PAR L'ADMIN (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_story_admin'])) {
    $content = trim($_POST['content']);

    if (empty($content)) {
        $message = '<div class="alert alert-danger">Veuillez entrer le contenu de l\'histoire.</div>';
    } else {
        // L'histoire postée par l'admin est immédiatement approuvée ('approved')
        $status = 'approved'; 
        // Insertion dans la table storytelling (en tant qu'histoire principale, parent_id = NULL)
        $sql = "INSERT INTO storytelling (member_id, content, parent_id, status) VALUES (?, ?, NULL, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
             $message = '<div class="alert alert-danger">Erreur de préparation: ' . $conn->error . '</div>';
        } else {
            $stmt->bind_param("iss", $admin_member_id, $content, $status);
            
            if ($stmt->execute()) {
                header("Location: gestion_storytelling.php?success=postée");
                exit;
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de l\'ajout de l\'histoire: ' . $conn->error . '</div>';
            }
            $stmt->close();
        }
    }
}


// --- C. GESTION DES MESSAGES DE STATUT ---
if (isset($_GET['success'])) {
    $action_message = '';
    if ($_GET['success'] == 'acceptée') {
        $action_message = 'L\'histoire a été acceptée et est maintenant active.';
    } elseif ($_GET['success'] == 'rejetée') {
        $action_message = 'L\'histoire a été rejetée.';
    } elseif ($_GET['success'] == 'deleted') {
        $action_message = 'L\'histoire ou le fil de discussion a été supprimé.';
    } elseif ($_GET['success'] == 'updated') {
         $action_message = 'Le contenu a été modifié avec succès.';
    } elseif ($_GET['success'] == 'postée') {
         $action_message = 'Votre histoire a été publiée avec succès.';
    }
    $message = '<div class="alert alert-success">' . $action_message . '</div>';
}

// --- D. RÉCUPÉRATION DES HISTOIRES POUR L'AFFICHAGE ---
$stories_pending = [];
$stories_approved = [];

// 1. Histoires en attente de modération
$sql_pending = "
    SELECT 
        s.story_id, s.content, s.created_at, m.username AS member_username
    FROM storytelling s
    JOIN members m ON s.member_id = m.member_id
    WHERE s.parent_id IS NULL AND s.status = 'pending' 
    ORDER BY s.created_at ASC
";
$result_pending = $conn->query($sql_pending);
if ($result_pending) {
    while($row = $result_pending->fetch_assoc()) {
        $stories_pending[] = $row;
    }
}

// 2. Histoires approuvées (pour la vue d'ensemble)
$sql_approved = "
    SELECT 
        s.story_id, SUBSTRING(s.content, 1, 80) AS preview_content, s.created_at, 
        m.username AS member_username, s.status,
        (SELECT COUNT(*) FROM storytelling sub WHERE sub.parent_id = s.story_id) AS total_responses
    FROM storytelling s
    JOIN members m ON s.member_id = m.member_id
    WHERE s.parent_id IS NULL AND s.status = 'approved' 
    ORDER BY s.created_at DESC LIMIT 10
";
$result_approved = $conn->query($sql_approved);
if ($result_approved) {
    while($row = $result_approved->fetch_assoc()) {
        $stories_approved[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Storytelling - Modération Admin</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .story-pending { background-color: #fff3cd; border-left: 5px solid #ffc107; padding: 15px; margin-bottom: 10px; border-radius: 5px; }
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

                <h1 class="mb-4" style="color: var(--blue);">Modération Storytelling & Histoires Principales</h1>
                
                <?php echo $message; ?>
                
                <div class="recentOrders" style="min-height: auto; margin-bottom: 30px;">
                    <div class="cardHeader">
                        <h2>Publier une Nouvelle Histoire ou Annonce (Admin)</h2>
                    </div>
                    
                    <form action="gestion_storytelling.php" method="POST" class="p-3">
                        <input type="hidden" name="add_story_admin" value="1">
                        
                        <div class="mb-3">
                            <label for="content_post" class="form-label">Contenu de l'Histoire / Annonce</label>
                            <textarea class="form-control" id="content_post" name="content" rows="4" required></textarea>
                            <small class="form-text text-muted">Cette histoire sera publiée immédiatement (Statut : Approuvé).</small>
                        </div>
                        
                        <button type="submit" class="btn status delivered mt-2">Publier l'Histoire</button>
                    </form>
                </div>


                <div class="recentOrders" style="min-height: auto; margin-bottom: 30px;">
                    <div class="cardHeader">
                        <h2>Stories en Attente de Modération (<?php echo count($stories_pending); ?>)</h2>
                    </div>
                    
                    <?php if (count($stories_pending) > 0): ?>
                        <?php foreach ($stories_pending as $story): ?>
                            <div class="story-pending shadow-sm mb-3">
                                <h5>Post ID: <?php echo $story['story_id']; ?> | Par: **<?php echo htmlspecialchars($story['member_username']); ?>**</h5>
                                <p class="small text-muted">Publié le: <?php echo date('Y-m-d H:i', strtotime($story['created_at'])); ?></p>
                                <hr>
                                <p><?php echo nl2br(htmlspecialchars($story['content'])); ?></p>
                                
                                <div class="mt-3">
                                    <a href="?action=update_status&id=<?php echo $story['story_id']; ?>&new_status=approved" 
                                       class="btn status delivered me-2" style="text-decoration: none;">
                                        Accepter
                                    </a>
                                    <a href="?action=update_status&id=<?php echo $story['story_id']; ?>&new_status=rejected" 
                                       class="btn status pending me-2" style="text-decoration: none;">
                                        Rejeter
                                    </a>
                                    <a href="supprimer.php?table=storytelling&id=<?php echo $story['story_id']; ?>&redirect=gestion_storytelling.php" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir SUPPRIMER cette histoire ?');"
                                       class="btn status return" style="text-decoration: none;">
                                        Supprimer
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info mt-3">Aucune nouvelle histoire en attente de modération.</div>
                    <?php endif; ?>
                </div>

                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Histoires Approuvées et Actives (<?php echo count($stories_approved); ?>)</h2>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <td>ID</td>
                                <td>Aperçu du Contenu</td>
                                <td>Publié par</td>
                                <td>Réponses</td>
                                <td>Action</td>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($stories_approved) > 0): ?>
                                <?php foreach ($stories_approved as $story): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($story['story_id']); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($story['preview_content'])); ?>...</td>
                                        <td><?php echo htmlspecialchars($story['member_username']); ?></td>
                                        <td><span class="status delivered"><?php echo $story['total_responses']; ?></span></td>
                                        <td>
                                            <a href="gestion_reponses_story.php?story_id=<?php echo $story['story_id']; ?>" 
                                               class="status inProgress btn btn-sm me-2" style="text-decoration: none;">
                                                Voir/Répondre
                                            </a>
                                            <a href="modifier_story_post.php?id=<?php echo $story['story_id']; ?>" 
                                               class="status pending btn btn-sm me-2" style="text-decoration: none;">
                                                Éditer
                                            </a>
                                            <a href="supprimer.php?table=storytelling&id=<?php echo $story['story_id']; ?>&redirect=gestion_storytelling.php" 
                                               onclick="return confirm('Supprimer ce fil supprimera TOUTES les réponses ! Continuer ?');"
                                               class="status return btn btn-sm" style="text-decoration: none;">
                                                Supprimer
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Aucune histoire approuvée trouvée.</td>
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