<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
require_once 'connexion.php'; 

$message = '';
$created_by_admin_id = 1; 

// --- 1. AJOUTER UN QUIZ (Logique PDO Corrigée) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_quiz'])) {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        
        if (empty($title) || empty($content)) {
            $message = '<div class="alert alert-danger">Veuillez remplir le Titre et le Contenu du Quiz.</div>';
        } else {
            try {
                $sql = "INSERT INTO quiz (title, content, created_by) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                
                // Exécution PDO
                if ($stmt->execute([$title, $content, $created_by_admin_id])) {
                    $last_quiz_id = $conn->lastInsertId(); // Méthode PDO pour récupérer l'ID
                    $message = '<div class="alert alert-success">Quiz "<b>' . htmlspecialchars($title) . '</b>" créé avec succès! <a href="gerer_quiz_complet.php?quiz_id=' . $last_quiz_id . '" class="btn btn-sm btn-info ms-3">Ajouter des questions</a></div>';
                } else {
                    $message = '<div class="alert alert-danger">Erreur lors de l\'ajout du quiz.</div>';
                }
            } catch (PDOException $e) {
                $message = '<div class="alert alert-danger">Erreur SQL : ' . $e->getMessage() . '</div>';
            }
        }
    } elseif (isset($_POST['delete_quiz'])) {
        $id = $_POST['id'];
        try {
            $sql = "DELETE FROM quiz WHERE quiz_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            $message = '<div class="alert alert-success">Quiz supprimé avec succès.</div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
        }
    }
}

// --- 2. LISTE DES QUIZ (Logique PDO Corrigée) ---
$quiz_list = [];
try {
    $sql_select = "
        SELECT q.quiz_id, q.title, q.created_at, a.username AS admin_username, 
               (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.quiz_id) AS total_questions
        FROM quiz q
        JOIN admins a ON q.created_by = a.admin_id
        ORDER BY q.created_at DESC
    ";
    
    // Utilisation de PDO query et fetchAll
    $stmt = $conn->query($sql_select);
    $quiz_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message .= '<div class="alert alert-danger">Erreur de lecture : ' . $e->getMessage() . '</div>';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Quiz - Nafas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root { --brand-teal: #0F766E; --brand-dark: #0f172a; --brand-light: #f1f5f9; --white: #ffffff; --card-radius: 20px; }
        body { background: var(--brand-light); font-family: 'Ubuntu', sans-serif; overflow-x: hidden; }
        .container-fluid-custom { position: relative; width: 100%; }
        .navigation { position: fixed; width: 300px; height: 100%; background: var(--brand-dark); border-left: 10px solid var(--brand-dark); transition: 0.5s; overflow: hidden; z-index: 100; }
        .navigation.active { width: 80px; }
        .navigation ul { position: absolute; top: 0; left: 0; width: 100%; padding-left: 0; }
        .navigation ul li { position: relative; width: 100%; list-style: none; border-top-left-radius: 30px; border-bottom-left-radius: 30px; }
        .navigation ul li:hover, .navigation ul li.hovered { background-color: var(--brand-light); }
        .navigation ul li:nth-child(1) { margin-bottom: 40px; pointer-events: none; }
        .navigation ul li a { position: relative; display: block; width: 100%; display: flex; text-decoration: none; color: var(--white); }
        .navigation ul li:hover a, .navigation ul li.hovered a { color: var(--brand-teal); }
        .navigation ul li a .icon { position: relative; display: block; min-width: 60px; height: 60px; line-height: 75px; text-align: center; }
        .navigation ul li a .icon ion-icon { font-size: 1.75rem; }
        .navigation ul li a .title { position: relative; display: block; padding: 0 10px; height: 60px; line-height: 60px; text-align: start; white-space: nowrap; }
        .navigation ul li:hover a::before, .navigation ul li.hovered a::before { content: ''; position: absolute; right: 0; top: -50px; width: 50px; height: 50px; background-color: transparent; border-radius: 50%; box-shadow: 35px 35px 0 10px var(--brand-light); pointer-events: none; }
        .navigation ul li:hover a::after, .navigation ul li.hovered a::after { content: ''; position: absolute; right: 0; bottom: -50px; width: 50px; height: 50px; background-color: transparent; border-radius: 50%; box-shadow: 35px -35px 0 10px var(--brand-light); pointer-events: none; }
        .main { position: absolute; width: calc(100% - 300px); left: 300px; min-height: 100vh; background: var(--brand-light); transition: 0.5s; }
        .navigation.active ~ .main { width: calc(100% - 80px); left: 80px; }
        .topbar { width: 100%; height: 60px; display: flex; justify-content: space-between; align-items: center; padding: 0 10px; margin-bottom: 20px;}
        .toggle { width: 60px; height: 60px; display: flex; justify-content: center; align-items: center; font-size: 2.5rem; cursor: pointer; color: var(--brand-dark); }
        .user { width: 40px; height: 40px; border-radius: 50%; overflow: hidden; margin-right: 20px; border: 2px solid var(--brand-teal); }
        .user img { width: 100%; height: 100%; object-fit: cover; }
        .details { position: relative; width: 100%; padding: 20px; display: grid; grid-template-columns: 1fr; grid-gap: 30px; }
        .recentOrders { position: relative; display: grid; background: var(--white); padding: 20px; box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08); border-radius: var(--card-radius); }
        .cardHeader { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .cardHeader h2 { font-weight: 600; color: var(--brand-teal); }
        .status.inProgress { padding: 2px 4px; background: #1795ce; color: var(--white); border-radius: 4px; font-size: 14px; font-weight: 500; }
        .status.return { padding: 2px 4px; background: #f00; color: var(--white); border-radius: 4px; font-size: 14px; font-weight: 500; border:none; }
        @media (max-width: 991px) { .navigation { left: -300px; } .navigation.active { width: 300px; left: 0; } .main { width: 100%; left: 0; } .main.active { left: 300px; } }
    </style>
</head>

<body>
    <div class="container-fluid-custom">
        <div class="navigation">
            <ul>
                <li><a href="#"><span class="title" style="font-weight: 700; font-size: 1.2rem;">Nafas Admin</span></a></li>
                <li><a href="index.php"><span class="icon"><ion-icon name="home-outline"></ion-icon></span><span class="title">Dashboard</span></a></li>
                <li><a href="gestion_membres.php"><span class="icon"><ion-icon name="people-outline"></ion-icon></span><span class="title">Membres</span></a></li>
                <li><a href="gestion_opportunites.php"><span class="icon"><ion-icon name="briefcase-outline"></ion-icon></span><span class="title">Opportunités</span></a></li>
                <li class="hovered"><a href="gestion_quiz.php"><span class="icon"><ion-icon name="help-circle-outline"></ion-icon></span><span class="title">Quiz</span></a></li>
                <li><a href="gestion_storytelling.php"><span class="icon"><ion-icon name="book-outline"></ion-icon></span><span class="title">Storytelling</span></a></li>
                <li><a href="admin_sensibilisation.php"><span class="icon"><ion-icon name="megaphone-outline"></ion-icon></span><span class="title">Contenus</span></a></li>
                <li><a href="gestion_brochures.php"><span class="icon"><ion-icon name="document-text-outline"></ion-icon></span><span class="title">Brochures</span></a></li>
                <li><a href="logout.php"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span><span class="title">Déconnexion</span></a></li>
            </ul>
        </div>

        <div class="main">
            <div class="topbar">
                <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
                <div class="user"><img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Admin"></div>
            </div>

            <div class="details">
                <h1 class="mb-4" style="color: var(--brand-teal);">Gestion des Quiz</h1>
                
                <?php echo $message; ?>

                <div class="recentOrders mb-4">
                    <div class="cardHeader">
                        <h2>Créer un Nouveau Quiz</h2>
                    </div>
                    
                    <form action="gestion_quiz.php" method="POST">
                        <input type="hidden" name="add_quiz" value="1">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Titre du Quiz</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label fw-bold">Description</label>
                            <textarea class="form-control" name="content" rows="3" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-success" style="background-color: var(--brand-teal); border:none;">Créer le Quiz</button>
                    </form>
                </div>

                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Liste des Quiz Existants (<?php echo count($quiz_list); ?>)</h2>
                    </div>

                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <td>Titre</td>
                                <td>Questions</td>
                                <td>Créé par</td>
                                <td>Date</td>
                                <td>Action</td>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($quiz_list) > 0): ?>
                                <?php foreach ($quiz_list as $quiz): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($quiz['total_questions']); ?></span></td>
                                        <td><?php echo htmlspecialchars($quiz['admin_username']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($quiz['created_at'])); ?></td>
                                        <td>
                                            <a href="gerer_quiz_complet.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="status inProgress btn btn-sm text-decoration-none text-white me-2">
                                                Gérer / Questions
                                            </a>
                                            <form action="gestion_quiz.php" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce quiz ?');">
                                                <input type="hidden" name="delete_quiz" value="1">
                                                <input type="hidden" name="id" value="<?php echo $quiz['quiz_id']; ?>">
                                                <button type="submit" class="status return btn btn-sm" style="cursor:pointer;">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Aucun quiz trouvé. Créez-en un ci-dessus!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
        let toggle = document.querySelector('.toggle');
        let navigation = document.querySelector('.navigation');
        let main = document.querySelector('.main');
        toggle.onclick = function () { navigation.classList.toggle('active'); main.classList.toggle('active'); }
    </script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>