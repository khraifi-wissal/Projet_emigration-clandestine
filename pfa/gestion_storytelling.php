<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
require_once 'connexion.php'; 

$message = '';
$current_admin_member_id = $_SESSION['member_id'] ?? 22; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'repondre') {
            $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            $content = trim($_POST['content']);
            if (!empty($content)) {
                $check = $conn->prepare("SELECT member_id FROM members WHERE member_id = ?");
                $check->execute([$current_admin_member_id]);
                if ($check->rowCount() > 0) {
                    $stmt = $conn->prepare("INSERT INTO storytelling (member_id, content, parent_id, status) VALUES (?, ?, ?, 'approved')");
                    $stmt->execute([$current_admin_member_id, $content, $parent_id]);
                    header("Location: gestion_storytelling.php?msg=rep_ok");
                    exit;
                }
            }
        }
        if ($_POST['action'] === 'supprimer') {
            $story_id = (int)$_POST['story_id'];
            $stmt = $conn->prepare("DELETE FROM storytelling WHERE story_id = ? OR parent_id = ?");
            $stmt->execute([$story_id, $story_id]);
            header("Location: gestion_storytelling.php?msg=del_ok");
            exit;
        }
    } catch (PDOException $e) { $message = '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>'; }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'rep_ok') $message = '<div class="alert alert-success">✅ Réponse publiée.</div>';
    if ($_GET['msg'] == 'del_ok') $message = '<div class="alert alert-success">🗑️ Discussion supprimée.</div>';
}

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
        .details { position: relative; width: 100%; padding: 20px; }
        @media (max-width: 991px) { .navigation { left: -300px; } .navigation.active { width: 300px; left: 0; } .main { width: 100%; left: 0; } .main.active { left: 300px; } }

        /* Custom Story Styles */
        .member-story-card { background: white; border: none; border-radius: 15px; box-shadow: 0 7px 25px rgba(0,0,0,0.08); margin-bottom: 30px; transition: transform 0.3s ease; }
        .member-story-card:hover { transform: translateY(-5px); }
        .card-header-custom { background-color: #fff; border-bottom: 1px solid #eee; padding: 15px 25px; border-radius: 15px 15px 0 0 !important; display: flex; justify-content: space-between; align-items: center; }
        .author-name { color: var(--brand-teal); font-weight: 600; font-size: 1.1rem; }
        .admin-reply-box { background-color: #F0F7FF; border-left: 5px solid var(--brand-teal); padding: 15px; margin-left: 40px; border-radius: 0 10px 10px 0; margin-top: 15px; }
        .btn-primary-nafas { background-color: var(--brand-teal); color: white; border: none; }
        .btn-primary-nafas:hover { background-color: #0d635d; color:white; }
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
        <li><a href="gestion_quiz.php"><span class="icon"><ion-icon name="help-circle-outline"></ion-icon></span><span class="title">Quiz</span></a></li>
        <li class="hovered"><a href="gestion_storytelling.php"><span class="icon"><ion-icon name="book-outline"></ion-icon></span><span class="title">Storytelling</span></a></li>
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
                <h1 class="mb-4" style="color: var(--brand-teal);">Gestion du Storytelling</h1>
                <?php echo $message; ?>

                <?php foreach ($stories as $s): ?>
                    <div class="member-story-card">
                        <div class="card-header-custom">
                            <span class="author-name">👤 <?php echo htmlspecialchars($s['username']); ?></span>
                            <span class="badge rounded-pill bg-light text-dark shadow-sm">ID #<?php echo $s['story_id']; ?></span>
                        </div>
                        <div class="card-body p-4">
                            <p class="lead" style="font-size: 1rem; line-height: 1.8;">
                                <?php echo nl2br(htmlspecialchars($s['content'])); ?>
                            </p>
                            <div class="d-flex gap-3 mt-4">
                                <button class="btn btn-primary-nafas px-3 py-1 rounded-3" onclick="prepareReply(<?php echo $s['story_id']; ?>, '<?php echo addslashes($s['username']); ?>')">Répondre</button>
                                <form method="POST" onsubmit="return confirm('Voulez-vous supprimer ce témoignage ?');">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="story_id" value="<?php echo $s['story_id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm px-3 py-1 rounded-3">Supprimer</button>
                                </form>
                            </div>
                            <?php
                            $stmt_rep = $conn->prepare("SELECT s.*, m.username FROM storytelling s JOIN members m ON s.member_id = m.member_id WHERE s.parent_id = ? ORDER BY s.created_at ASC");
                            $stmt_rep->execute([$s['story_id']]);
                            $reponses = $stmt_rep->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($reponses as $r):
                                $isAdmin = ($r['member_id'] == $current_admin_member_id);
                            ?>
                                <div class="<?php echo $isAdmin ? 'admin-reply-box' : 'ms-5 p-3 border-start border-3'; ?> mt-3">
                                    <p class="mb-1 fw-bold" style="font-size: 0.9rem;"><?php echo $isAdmin ? htmlspecialchars($r['username']) : '💬 ' . htmlspecialchars($r['username']); ?></p>
                                    <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($r['content'])); ?></p>
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
            <form method="POST" class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header bg-primary text-white border-0" style="background-color: var(--brand-teal)!important;">
                    <h5 class="modal-title">Répondre à <span id="replyToUser"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="repondre">
                    <input type="hidden" name="parent_id" id="parent_id_field">
                    <textarea name="content" class="form-control" rows="5" required placeholder="Votre message..."></textarea>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary-nafas px-4">Publier</button>
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
            var myModal = new bootstrap.Modal(document.getElementById('replyModal'));
            myModal.show();
        }
    </script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>