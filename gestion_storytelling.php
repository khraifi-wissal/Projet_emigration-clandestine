<?php
include 'connexion.php'; 

$message = '';
$created_by_member_id = 1; 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_story'])) {
    $content = trim($_POST['content']);

    if (empty($content)) {
        $message = '<div class="alert alert-danger">Veuillez entrer le contenu de l\'histoire.</div>';
    } else {
        $sql = "INSERT INTO storytelling (member_id, content, parent_id) VALUES (?, ?, NULL)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
             $message = '<div class="alert alert-danger">Erreur de préparation: ' . $conn->error . '</div>';
        } else {
            $stmt->bind_param("is", $created_by_member_id, $content);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Histoire publiée avec succès!</div>';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de l\'ajout de l\'histoire: ' . $conn->error . '</div>';
            }
            $stmt->close();
        }
    }
}

$stories = [];
$sql_select = "
    SELECT 
        s.story_id, 
        SUBSTRING(s.content, 1, 100) AS preview_content, 
        s.created_at, 
        m.username AS member_username,
        (SELECT COUNT(*) FROM storytelling sub WHERE sub.parent_id = s.story_id) AS total_responses
    FROM storytelling s
    JOIN members m ON s.member_id = m.member_id
    WHERE s.parent_id IS NULL 
    ORDER BY s.created_at DESC
";
$result = $conn->query($sql_select);

if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $stories[] = $row;
        }
    }
    $result->free();
} else {
    $message .= '<div class="alert alert-danger">Erreur de lecture des histoires: ' . $conn->error . '</div>';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Storytelling - Nafas Admin</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
    
        <div class="navigation">
            <ul>
                <li><a href="index.php"><span class="icon"><ion-icon name="happy-outline"></ion-icon></span> <span class="title">Nafas</span></a></li>
                <li><a href="index.php"><span class="icon"><ion-icon name="home-outline"></ion-icon></span> <span class="title">Dashboard</span></a></li>
                <li><a href="gestion_membres.php"><span class="icon"><ion-icon name="people-outline"></ion-icon></span> <span class="title">Membres</span></a></li>
                <li><a href="gestion_opportunites.php"><span class="icon"><ion-icon name="briefcase-outline"></ion-icon></span> <span class="title">Opportunités</span></a></li>
                <li><a href="gestion_quiz.php"><span class="icon"><ion-icon name="help-circle-outline"></ion-icon></span> <span class="title">Quiz</span></a></li>
                
                <li class="hovered"><a href="gestion_storytelling.php"><span class="icon"><ion-icon name="book-outline"></ion-icon></span> <span class="title">Storytelling</span></a></li>
                
                <li><a href="#"><span class="icon"><ion-icon name="document-text-outline"></ion-icon></span> <span class="title">Brochures</span></a></li>
                <li><a href="#"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span> <span class="title">Déconnexion</span></a></li>
            </ul>
        </div>

        <div class="main">
            <div class="topbar">
                <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
                <div class="search"><label><input type="text" placeholder="Rechercher..."><ion-icon name="search-outline"></ion-icon></label></div>
                <div class="user"><img src="image.png" alt=""></div>
            </div>

            <div class="details p-3 p-md-5">

                <h1 class="mb-4" style="color: var(--blue);">Gestion du Storytelling</h1>
                
                <?php echo $message; ?>

                <div class="recentOrders" style="min-height: auto; margin-bottom: 30px;">
                    <div class="cardHeader">
                        <h2>Publier une Nouvelle Histoire</h2>
                    </div>
                    
                    <form action="gestion_storytelling.php" method="POST" class="p-3">
                        <input type="hidden" name="add_story" value="1">
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Contenu de l'Histoire / Topic</label>
                            <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success mt-2">Publier l'Histoire</button>
                    </form>
                </div>

                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Histoires Principales Publiées (<?php echo count($stories); ?>)</h2>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <td>ID</td>
                                <td>Aperçu du Contenu</td>
                                <td>Publié par</td>
                                <td>Date</td>
                                <td>Réponses</td>
                                <td>Action</td>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($stories) > 0): ?>
                                <?php foreach ($stories as $story): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($story['story_id']); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($story['preview_content'])); ?>...</td>
                                        <td><?php echo htmlspecialchars($story['member_username']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($story['created_at'])); ?></td>
                                        <td><span class="status delivered"><?php echo $story['total_responses']; ?></span></td>
                                        <td>
                                            <a href="#" class="status inProgress btn btn-sm me-2" style="text-decoration: none;">Voir Réponses</a>
                                            <button class="status return btn btn-sm">Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucune histoire publiée pour le moment.</td>
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