<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
require_once 'connexion.php'; 

$message = '';
// Utilisation de l'ID admin de la session
$admin_id_author = $_SESSION['admin_id']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        // --- ACTION : RÉPONDRE EN TANT QU'ADMIN ---
        if ($_POST['action'] === 'repondre') {
            $parent_id = (int)$_POST['parent_id'];
            $content = trim($_POST['content']);
            
            if (!empty($content)) {
                // On insère la réponse avec le préfixe [ADMIN] pour la clarté
                $stmt = $conn->prepare("INSERT INTO storytelling (member_id, content, parent_id, status) VALUES (?, ?, ?, 'approved')");
                // On lie cette réponse à un ID membre admin (ex: 22) ou on gère via la logique de votre DB
                $stmt->execute([22, "[ADMIN] " . $content, $parent_id]); 
                header("Location: gestion_storytelling.php?msg=rep_ok");
                exit;
            }
        }

        // --- ACTION : SUPPRIMER CHAQUE COMMENTAIRE ---
        if ($_POST['action'] === 'supprimer') {
            $story_id = (int)$_POST['story_id'];
            // Supprime l'élément précis. Si c'est un parent, supprime aussi les enfants.
            $stmt = $conn->prepare("DELETE FROM storytelling WHERE story_id = ? OR parent_id = ?");
            $stmt->execute([$story_id, $story_id]);
            header("Location: gestion_storytelling.php?msg=del_ok");
            exit;
        }
    } catch (PDOException $e) { 
        $message = '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>'; 
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'rep_ok') $message = '<div class="alert alert-success">✅ Réponse admin publiée.</div>';
    if ($_GET['msg'] == 'del_ok') $message = '<div class="alert alert-success">🗑️ Élément supprimé.</div>';
}

// Récupération des discussions principales
$sql = "SELECT s.*, m.username FROM storytelling s JOIN members m ON s.member_id = m.member_id WHERE s.parent_id IS NULL ORDER BY s.created_at DESC";
$stories = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storytelling - Nafas Admin</title>
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
        .navigation ul li a { position: relative; display: flex; text-decoration: none; color: var(--white); width: 100%; }
        .navigation ul li:hover a, .navigation ul li.hovered a { color: var(--brand-teal); }
        .navigation ul li a .icon { position: relative; display: block; min-width: 60px; height: 60px; line-height: 75px; text-align: center; }
        .navigation ul li a .icon ion-icon { font-size: 1.75rem; }
        .navigation ul li a .title { position: relative; display: block; padding: 0 10px; height: 60px; line-height: 60px; text-align: start; white-space: nowrap; }
        .main { position: absolute; width: calc(100% - 300px); left: 300px; min-height: 100vh; background: var(--brand-light); transition: 0.5s; }
        .navigation.active ~ .main { width: calc(100% - 80px); left: 80px; }
        .topbar { width: 100%; height: 60px; display: flex; justify-content: space-between; align-items: center; padding: 0 10px; margin-bottom: 20px;}
        .toggle { width: 60px; height: 60px; display: flex; justify-content: center; align-items: center; font-size: 2.5rem; cursor: pointer; color: var(--brand-dark); }
        .user { width: 40px; height: 40px; border-radius: 50%; overflow: hidden; margin-right: 20px; border: 2px solid var(--brand-teal); }
        .user img { width: 100%; height: 100%; object-fit: cover; }
        .details { position: relative; width: 100%; padding: 20px; }
        
        .member-story-card { background: white; border-radius: 15px; box-shadow: 0 7px 25px rgba(0,0,0,0.08); margin-bottom: 30px; border: none; }
        .admin-reply-item { background: #f8fafc; border-left: 4px solid var(--brand-teal); padding: 12px; margin-top: 10px; border-radius: 0 8px 8px 0; display: flex; justify-content: space-between; align-items: center; }
        .btn-primary-nafas { background-color: var(--brand-teal); color: white; border: none; }
        .btn-primary-nafas:hover { background-color: #0d635d; color:white; }
        
        @media (max-width: 991px) { .navigation { left: -300px; } .navigation.active { width: 300px; left: 0; } .main { width: 100%; left: 0; } .main.active { left: 300px; } }
    </style>
</head>
<body>
    <div class="container-fluid-custom">
        <div class="navigation">
            <ul>
                <li><a href="#"><span class="title" style="font-weight: 700; font-size: 1.2rem;">Nafas</span></a></li>
                <li><a href="index.php"><span class="icon"><ion-icon name="home-outline"></ion-icon></span><span class="title">Dashboard</span></a></li>
                <li><a href="gestion_membres.php"><span class="icon"><ion-icon name="people-outline"></ion-icon></span><span class="title">Membres</span></a></li>
                <li><a href="gestion_opportunites.php"><span class="icon"><ion-icon name="briefcase-outline"></ion-icon></span><span class="title">Opportunités</span></a></li>
                <li><a href="gestion_quiz.php"><span class="icon"><ion-icon name="help-circle-outline"></ion-icon></span><span class="title">Quiz</span></a></li>
                <li><a href="gestion_storytelling.php"><span class="icon"><ion-icon name="book-outline"></ion-icon></span><span class="title">Storytelling</span></a></li>
                <li class="hovered"><a href="admin_sensibilisation.php"><span class="icon"><ion-icon name="megaphone-outline"></ion-icon></span><span class="title">Contenus</span></a></li>
                <li><a href="gestion_brochures.php"><span class="icon"><ion-icon name="document-text-outline"></ion-icon></span><span class="title">Brochures</span></a></li>
                <li><a href="admin_login.php"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span><span class="title">Déconnexion</span></a></li>
            </ul>
        </div>

        <div class="main">
            <div class="topbar">
                <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
                <div class="user"><img src="admin.jpg" alt="Admin"></div>
            </div>

            <div class="details">
                <h1 class="mb-4" style="color: var(--brand-teal);">Modération des Témoignages</h1>
                <?php echo $message; ?>

                <?php foreach ($stories as $s): ?>
                    <div class="member-story-card p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold" style="color: var(--brand-teal);"><?php echo htmlspecialchars($s['username']); ?></span>
                            <form method="POST" onsubmit="return confirm('Supprimer ce sujet et ses réponses ?');">
                                <input type="hidden" name="action" value="supprimer">
                                <input type="hidden" name="story_id" value="<?php echo $s['story_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer Sujet</button>
                            </form>
                        </div>
                        
                        <p style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($s['content'])); ?></p>
                        
                        <button class="btn btn-sm btn-primary-nafas mt-2" onclick="prepareReply(<?php echo $s['story_id']; ?>, '<?php echo addslashes($s['username']); ?>')">Répondre en Admin</button>

                        <div class="responses-container mt-4">
                            <?php
                            $stmt_rep = $conn->prepare("SELECT s.*, m.username FROM storytelling s JOIN members m ON s.member_id = m.member_id WHERE s.parent_id = ? ORDER BY s.created_at ASC");
                            $stmt_rep->execute([$s['story_id']]);
                            $reponses = $stmt_rep->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($reponses as $r): ?>
                                <div class="admin-reply-item">
                                    <div>
                                        <small class="fw-bold d-block text-dark"><?php echo htmlspecialchars($r['username']); ?></small>
                                        <span class="small"><?php echo nl2br(htmlspecialchars($r['content'])); ?></span>
                                    </div>
                                    <form method="POST" onsubmit="return confirm('Supprimer ce commentaire ?');">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="story_id" value="<?php echo $r['story_id']; ?>">
                                        <button type="submit" class="btn btn-link text-danger p-0 ms-3" title="Supprimer">
                                            <ion-icon name="trash-outline" style="font-size: 1.2rem;"></ion-icon>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="replyModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content border-0 shadow-lg">
                <div class="modal-header text-white" style="background-color: var(--brand-teal);">
                    <h5 class="modal-title">Réponse Administrative</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="repondre">
                    <input type="hidden" name="parent_id" id="parent_id_field">
                    <p class="text-muted small">Réponse à : <span id="replyToUser" class="fw-bold"></span></p>
                    <textarea name="content" class="form-control" rows="4" required placeholder="Votre message officiel..."></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary-nafas px-4">Envoyer la réponse</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let toggle = document.querySelector('.toggle');
        let navigation = document.querySelector('.navigation');
        let main = document.querySelector('.main');
        toggle.onclick = function () { navigation.classList.toggle('active'); main.classList.toggle('active'); }

        function prepareReply(id, username) {
            document.getElementById('parent_id_field').value = id;
            document.getElementById('replyToUser').innerText = username;
            new bootstrap.Modal(document.getElementById('replyModal')).show();
        }
    </script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>