<?php
include 'connexion.php'; 

$message = '';
$created_by_admin_id = 1; 

// --- 1. AJOUT D'UN QUIZ ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_quiz'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if (empty($title) || empty($content)) {
        $message = '<div class="alert alert-danger">Veuillez remplir le Titre et le Contenu du Quiz.</div>';
    } else {
        try {
            $sql = "INSERT INTO quiz (title, content, created_by) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            // En PDO, on execute avec un tableau de paramètres (plus simple que bind_param)
            if ($stmt->execute([$title, $content, $created_by_admin_id])) {
                $last_quiz_id = $conn->lastInsertId(); // Équivalent de $conn->insert_id
                $message = '<div class="alert alert-success">Quiz "<b>' . htmlspecialchars($title) . '</b>" créé avec succès! <a href="ajouter_questions.php?quiz_id=' . $last_quiz_id . '" class="btn btn-sm btn-info ms-3">Ajouter des questions maintenant</a></div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Erreur lors de l\'ajout du quiz: ' . $e->getMessage() . '</div>';
        }
    }
}

// --- 2. RÉCUPÉRATION DES QUIZ ---
$quiz_list = [];
$sql_select = "
    SELECT q.quiz_id, q.title, q.created_at, a.username AS admin_username, 
            (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.quiz_id) AS total_questions
    FROM quiz q
    JOIN admins a ON q.created_by = a.admin_id
    ORDER BY q.created_at DESC
";

try {
    $result = $conn->query($sql_select);

    if ($result) {
        $quiz_list = $result->fetchAll(PDO::FETCH_ASSOC);
        
    }
} catch (PDOException $e) {
    $message .= '<div class="alert alert-danger">Erreur de lecture des quiz: ' . $e->getMessage() . '</div>';
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Quiz - Nafas Admin</title>
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
                    <a href="admin_sensibilisation.php">
                        <span class="icon">
                            <ion-icon name="document-text-outline"></ion-icon>
                        </span>
                        <span class="title">contenus</span>
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
            <ion-icon name="log-out-outline"></ion-icon>
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

                <h1 class="mb-4" style="color: var(--blue);">Gestion des Quiz</h1>
                
                <?php echo $message; ?>

                <div class="recentOrders" style="min-height: auto; margin-bottom: 30px;">
                    <div class="cardHeader">
                        <h2>Créer un Nouveau Quiz</h2>
                    </div>
                    
                    <form action="gestion_quiz.php" method="POST" class="p-3">
                        <input type="hidden" name="add_quiz" value="1">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Titre du Quiz</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Contenu/Description du Quiz</label>
                            <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success mt-2">Créer le Quiz</button>
                    </form>
                </div>

                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Liste des Quiz Existants (<?php echo count($quiz_list); ?>)</h2>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <td>ID</td>
                                <td>Titre</td>
                                <td>Questions</td>
                                <td>Créé par</td>
                                <td>Date de création</td>
                                <td>Action</td>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($quiz_list) > 0): ?>
                                <?php foreach ($quiz_list as $quiz): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($quiz['quiz_id']); ?></td>
                                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                        <td><?php echo htmlspecialchars($quiz['total_questions']); ?></td>
                                        <td><?php echo htmlspecialchars($quiz['admin_username']); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($quiz['created_at'])); ?></td>
                                        <td>
                                            <a href="ajouter_questions.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="status inProgress btn btn-sm me-2" style="text-decoration: none;">
                                                Ajouter Questions
                                            </a>
                                            <button class="status return btn btn-sm">Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Aucun quiz trouvé. Créez-en un ci-dessus!</td>
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