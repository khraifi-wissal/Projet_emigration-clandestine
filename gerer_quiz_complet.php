<?php
// Inclusion du fichier de connexion (MySQLi)
include 'connexion.php'; 

$message = '';
$created_by_admin_id = 1; 

// Récupération du quiz_id depuis l'URL (si l'on veut ajouter des questions à un quiz existant)
$quiz_id_to_manage = isset($_GET['quiz_id']) && is_numeric($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : null;
$quiz_info = null;

// --- 1. FONCTIONS DE TRAITEMENT ---

function check_quiz_existence($conn, $id) {
    $stmt = $conn->prepare("SELECT quiz_id, title, content FROM quiz WHERE quiz_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $info = $result->fetch_assoc();
    $stmt->close();
    return $info;
}

// Traitement de l'ajout d'une nouvelle question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $current_quiz_id = (int)$_POST['current_quiz_id'];
    $question_text = trim($_POST['question_text']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d']);
    $correct_option = $_POST['correct_option'];

    // Validation des options (au moins A et B)
    if (empty($question_text) || empty($option_a) || empty($option_b) || empty($correct_option)) {
        $message = '<div class="alert alert-danger">Veuillez remplir le texte de la question, au moins les options A et B, et la bonne option.</div>';
    } else {
        // Insertion dans la table quiz_questions
        $sql = "INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
             $message = '<div class="alert alert-danger">Erreur de préparation: ' . $conn->error . '</div>';
        } else {
            $stmt->bind_param("issssss", $current_quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option);
            
            if ($stmt->execute()) {
                $message = '<div class="alert alert-success">Question ajoutée avec succès!</div>';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de l\'ajout de la question: ' . $conn->error . '</div>';
            }
            $stmt->close();
        }
    }
}


// Traitement de l'ajout d'un nouveau quiz (et redirection pour ajouter les questions)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_quiz'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
        $message = '<div class="alert alert-danger">Veuillez remplir le Titre et la Description du Quiz.</div>';
    } else {
        // Insertion dans la table quiz
        $sql = "INSERT INTO quiz (title, content, created_by) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
             $message = '<div class="alert alert-danger">Erreur de préparation: ' . $conn->error . '</div>';
        } else {
            $stmt->bind_param("ssi", $title, $content, $created_by_admin_id);
            
            if ($stmt->execute()) {
                $last_quiz_id = $conn->insert_id;
                // Redirection vers cette même page pour ajouter les questions
                header("Location: gerer_quiz_complet.php?quiz_id=" . $last_quiz_id . "&success=1");
                exit;
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de la création du quiz: ' . $conn->error . '</div>';
            }
            $stmt->close();
        }
    }
}


// --- 2. LOGIQUE D'AFFICHAGE ---

// Si un quiz_id est fourni, charger ses infos et ses questions
if ($quiz_id_to_manage) {
    $quiz_info = check_quiz_existence($conn, $quiz_id_to_manage);
    
    // Charger les questions existantes
    $questions = [];
    $sql_select = "
        SELECT question_id, question_text, option_a, option_b, option_c, option_d, correct_option 
        FROM quiz_questions 
        WHERE quiz_id = ?
        ORDER BY question_id ASC
    ";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $quiz_id_to_manage);
    $stmt_select->execute();
    $result_questions = $stmt_select->get_result();

    if ($result_questions) {
        while($row = $result_questions->fetch_assoc()) {
            $questions[] = $row;
        }
        $result_questions->free();
    }
    $stmt_select->close();
    
    // Message de succès après la création
    if (isset($_GET['success']) && $_GET['success'] == 1) {
         $message = '<div class="alert alert-success">Quiz créé avec succès. Vous pouvez maintenant ajouter les questions!</div>';
    }

} else {
    // Si aucun quiz_id n'est fourni, charger la liste des quiz pour que l'admin choisisse
    $quiz_list = [];
    $sql_select = "
        SELECT q.quiz_id, q.title, q.created_at, a.username AS admin_username, 
               (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.quiz_id) AS total_questions
        FROM quiz q
        JOIN admins a ON q.created_by = a.admin_id
        ORDER BY q.created_at DESC
    ";
    $result = $conn->query($sql_select);

    if ($result) {
        while($row = $result->fetch_assoc()) {
            $quiz_list[] = $row;
        }
        $result->free();
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
    <title>Gestion des Quiz - Nafas Admin</title>
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
                <li class="hovered"><a href="gerer_quiz_complet.php"><span class="icon"><ion-icon name="help-circle-outline"></ion-icon></span> <span class="title">Quiz</span></a></li>
                <li><a href=""><span class="icon"><ion-icon name="book-outline"></ion-icon></span> <span class="title">Storytelling</span></a></li>
                <li><a href=""><span class="icon"><ion-icon name="document-text-outline"></ion-icon></span> <span class="title">Brochures</span></a></li>
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

                <?php if ($quiz_info): ?>
                    <h1 class="mb-2" style="color: var(--blue);">Ajouter Questions au Quiz: "<?php echo htmlspecialchars($quiz_info['title']); ?>"</h1>
                    <p><a href="gerer_quiz_complet.php">← Retour à la liste des Quiz</a></p>
                    
                    <div class="alert alert-info">
                        **Description du Quiz :** <?php echo nl2br(htmlspecialchars($quiz_info['content'])); ?>
                    </div>
                    
                    <?php echo $message; ?>

                    <div class="recentOrders" style="min-height: auto; margin-bottom: 30px;">
                        <div class="cardHeader">
                            <h2>Ajouter une Question (4 propositions max.)</h2>
                        </div>
                        
                        <form action="gerer_quiz_complet.php?quiz_id=<?php echo $quiz_id_to_manage; ?>" method="POST" class="p-3">
                            <input type="hidden" name="add_question" value="1">
                            <input type="hidden" name="current_quiz_id" value="<?php echo $quiz_id_to_manage; ?>">
                            
                            <div class="mb-3">
                                <label for="question_text" class="form-label">Texte de la Question</label>
                                <textarea class="form-control" id="question_text" name="question_text" rows="2" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="option_a" class="form-label">Proposition A</label>
                                    <input type="text" class="form-control" id="option_a" name="option_a" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="option_b" class="form-label">Proposition B</label>
                                    <input type="text" class="form-control" id="option_b" name="option_b" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="option_c" class="form-label">Proposition C (Optionnel)</label>
                                    <input type="text" class="form-control" id="option_c" name="option_c">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="option_d" class="form-label">Proposition D (Optionnel)</label>
                                    <input type="text" class="form-control" id="option_d" name="option_d">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="correct_option" class="form-label">Bonne Réponse</label>
                                <select class="form-select" id="correct_option" name="correct_option" required>
                                    <option value="">-- Choisir --</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-success mt-2">Enregistrer la Question</button>
                        </form>
                    </div>

                    <div class="recentOrders">
                        <div class="cardHeader">
                            <h2>Questions Actuelles (Total: <?php echo count($questions); ?>)</h2>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <td>#</td>
                                    <td>Question</td>
                                    <td>Réponse Correcte</td>
                                    <td>Action</td>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (count($questions) > 0): ?>
                                    <?php foreach ($questions as $q): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($q['question_id']); ?></td>
                                            <td><?php echo nl2br(htmlspecialchars($q['question_text'])); ?></td>
                                            <td><span class="status delivered"><?php echo htmlspecialchars($q['correct_option']); ?></span></td>
                                            <td><button class="status return btn btn-sm">Supprimer</button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Ce quiz n'a pas encore de questions.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>


                <?php else: ?>
                    <h1 class="mb-4" style="color: var(--blue);">Création & Gestion des Quiz</h1>
                    
                    <?php echo $message; ?>

                    <div class="recentOrders" style="min-height: auto; margin-bottom: 30px;">
                        <div class="cardHeader">
                            <h2>Créer un Nouveau Quiz (Étape 1)</h2>
                        </div>
                        
                        <form action="gerer_quiz_complet.php" method="POST" class="p-3">
                            <input type="hidden" name="create_quiz" value="1">
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">Titre du Quiz</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Description du Quiz</label>
                                <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary mt-2">Créer le Quiz & Ajouter des Questions</button>
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
                                    <td>Date</td>
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
                                                <a href="gerer_quiz_complet.php?quiz_id=<?php echo $quiz['quiz_id']; ?>" class="status inProgress btn btn-sm me-2" style="text-decoration: none;">
                                                    Ajouter/Voir Questions
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

                <?php endif; ?>
                
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script> 
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>