<?php
session_start();
require_once 'connexion.php'; 

$message = '';
$current_admin_member_id = $_SESSION['member_id'] ?? 18; 

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
    if ($_GET['msg'] == 'rep_ok') $message = '<div class="alert alert-success">✅ Réponse publiée avec succès.</div>';
    if ($_GET['msg'] == 'del_ok') $message = '<div class="alert alert-success">🗑️ Discussion supprimée.</div>';
}

$sql = "SELECT s.*, m.username FROM storytelling s JOIN members m ON s.member_id = m.member_id WHERE s.parent_id IS NULL ORDER BY s.created_at DESC";
$stories = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nafas Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
</head>

<body>
    <div class="container">
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
                    <a href="gerer_quiz_complet.php">
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
                    <a href="">
                        <span class="icon">
                            <ion-icon name="document-text-outline"></ion-icon>
                        </span>
                        <span class="title">contenus</span>
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
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
                <div class="user">
                    <img src="image.png" alt=""> 
                </div>
            </div>

            <div class="cardBox">
                
                <div class="card">
                    <div>
                        <div class="numbers">0</div> <div class="cardName">Total Membres </div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="people-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers">0</div> <div class="cardName">Opportunités Publiées</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="briefcase-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers">0</div> <div class="cardName">Réponses Quiz Soumises</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="bulb-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers">0</div> <div class="cardName">Histoires Publiées</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="chatbubbles-outline"></ion-icon>
                    </div>
                </div>
            </div>

    <div class="details">
        <?php echo $message; ?>

        <div class="row justify-content-center">
            <div class="col-lg-10">
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
                                <button class="btn btn-primary-nafas" onclick="prepareReply(<?php echo $s['story_id']; ?>, '<?php echo addslashes($s['username']); ?>')">
                                    Répondre au membre
                                </button>
                                
                                <form method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer ce témoignage ?');">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="story_id" value="<?php echo $s['story_id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">Supprimer</button>
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
                                    <p class="mb-1 fw-bold" style="font-size: 0.9rem;">
                                        <?php echo $isAdmin ? htmlspecialchars($r['username']) : '💬 ' . htmlspecialchars($r['username']); ?>
                                    </p>
                                    <p class="mb-0" style="font-size: 0.95rem; opacity: 0.9;"><?php echo nl2br(htmlspecialchars($r['content'])); ?></p>
                                    <small class="text-muted d-block mt-2" style="font-size: 0.7rem;"><?php echo date('d/m/Y à H:i', strtotime($r['created_at'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" class="modal-content shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Répondre à <?php echo '<span id="replyToUser" class="text-white"></span>'; ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="repondre">
                    <input type="hidden" name="parent_id" id="parent_id_field">
                    <div class="mb-3">
                        <label class="form-label fw-600">Votre message d'inspiration :</label>
                        <textarea name="content" class="form-control" rows="6" required style="border-radius: 12px; border: 1px solid #ddd;" placeholder="Partagez un conseil ou un encouragement..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary-nafas px-4 py-2">Publier la réponse</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function prepareReply(id, username) {
            document.getElementById('parent_id_field').value = id;
            document.getElementById('replyToUser').innerText = username;
            var myModal = new bootstrap.Modal(document.getElementById('replyModal'));
            myModal.show();
        }
    </script>
</body>
</html>
