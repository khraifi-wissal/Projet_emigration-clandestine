<?php
/* ===== DEBUG MODE (Remove lines 3-4 when live) ===== */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// START THE SESSION
session_start();

/* ======================================================
   STEP 1: CONNECT TO THE DATABASE
   ====================================================== */
$conn = mysqli_connect("127.0.0.1", "root", "", "nafas");

if (!$conn) {
    die("<div style='color:red; padding:20px;'>Erreur critique de connexion : " . mysqli_connect_error() . "</div>");
}

/* ======================================================
   STEP 2: CHECK WHO IS LOGGED IN
   ====================================================== */
$isLoggedIn = false;
$username = "Membre";
$userImage = "https://cdn-icons-png.flaticon.com/512/149/149071.png"; 

if (isset($_SESSION["member_id"])) {
    $isLoggedIn = true;
    $member_id = $_SESSION['member_id'];
    $username = $_SESSION['username'] ?? "Membre";

    $query_user = "SELECT * FROM members WHERE member_id = '$member_id'";
    $result_user = mysqli_query($conn, $query_user);

    if ($result_user && mysqli_num_rows($result_user) > 0) {
        $row_user = mysqli_fetch_assoc($result_user);
        if (!empty($row_user['profile_image'])) {
            $userImage = $row_user['profile_image'];
        }
    }
}

/* ======================================================
   STEP 3: HANDLE ACTIONS
   ====================================================== */
if (isset($_POST['toggle_like'])) {
    if (isset($_SESSION['member_id'])) {
        $story_id = mysqli_real_escape_string($conn, $_POST['story_id']);
        $member_id = $_SESSION['member_id'];
        $check_like = mysqli_query($conn, "SELECT * FROM story_likes WHERE member_id = '$member_id' AND story_id = '$story_id'");
        if ($check_like && mysqli_num_rows($check_like) > 0) {
            mysqli_query($conn, "DELETE FROM story_likes WHERE member_id = '$member_id' AND story_id = '$story_id'");
        } else {
            mysqli_query($conn, "INSERT INTO story_likes (member_id, story_id, created_at) VALUES ('$member_id', '$story_id', NOW())");
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST["submit_story"])) {
    if (!isset($_SESSION["member_id"])) {
        $_SESSION["error"] = "Vous devez être connecté pour partager une histoire.";
    } else {
        $member_id = $_SESSION["member_id"];
        $story_content = mysqli_real_escape_string($conn, $_POST["story_content"]);
        if (empty($story_content)) {
            $_SESSION["error"] = "L'histoire ne peut pas être vide.";
        } else {
            $insert_query = "INSERT INTO storytelling (member_id, content, parent_id, status, created_at) VALUES (?, ?, NULL, 'approved', NOW())";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "is", $member_id, $story_content);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION["success"] = "Votre histoire a été publiée avec succès!";
            } else {
                $_SESSION["error"] = "Erreur lors de la publication : " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST["delete_story"])) {
    if (!isset($_SESSION["member_id"])) {
        $_SESSION["error"] = "Vous devez être connecté pour supprimer une histoire.";
    } else {
        $member_id = $_SESSION["member_id"];
        $story_id = mysqli_real_escape_string($conn, $_POST["story_id"]);
        $check_owner = mysqli_query($conn, "SELECT * FROM storytelling WHERE story_id = '$story_id' AND member_id = '$member_id'");
        if (mysqli_num_rows($check_owner) > 0) {
            mysqli_query($conn, "DELETE FROM story_likes WHERE story_id = '$story_id'");
            mysqli_query($conn, "DELETE FROM storytelling WHERE parent_id = '$story_id'");
            if (mysqli_query($conn, "DELETE FROM storytelling WHERE story_id = '$story_id'")) {
                $_SESSION["success"] = "Histoire supprimée avec succès.";
            } else {
                $_SESSION["error"] = "Erreur lors de la suppression : " . mysqli_error($conn);
            }
        } else {
            $_SESSION["error"] = "Vous n'avez pas l'autorisation de supprimer cette histoire.";
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST["submit_comment"])) {
    if (!isset($_SESSION["member_id"])) {
        $_SESSION["error"] = "Vous devez être connecté pour commenter.";
    } else {
        $member_id = $_SESSION["member_id"];
        $parent_id = mysqli_real_escape_string($conn, $_POST["parent_id"]);
        $comment_content = mysqli_real_escape_string($conn, $_POST["comment_content"]);
        if (empty($comment_content)) {
            $_SESSION["error"] = "Le commentaire ne peut pas être vide.";
        } else {
            $insert_query = "INSERT INTO storytelling (member_id, content, parent_id, status, created_at) VALUES (?, ?, ?, 'approved', NOW())";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "isi", $member_id, $comment_content, $parent_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION["success"] = "Votre commentaire a été publié avec succès!";
            } else {
                $_SESSION["error"] = "Erreur lors de l'ajout du commentaire : " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* ======================================================
   STEP 4: FETCH DATA
   ====================================================== */
$stories_query = "SELECT s.*, m.username
                  FROM storytelling s
                  JOIN members m ON s.member_id = m.member_id
                  WHERE s.status = 'approved' AND s.parent_id IS NULL
                  ORDER BY s.created_at DESC";
$stories_result = mysqli_query($conn, $stories_query);
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histoires de Réussite - Nafas</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="story_telling.css?v=<?php echo time(); ?>">
    <style>
        /* --- MOBILE MENU FIX (COMPACT & CENTERED) --- */
        @media (max-width: 991px) {
            .navbar-collapse {
                background-color: rgba(15, 23, 42, 0.98); /* Solid dark background */
                backdrop-filter: blur(10px);
                padding: 1rem; /* Smaller padding */
                border-radius: 12px;
                margin-top: 8px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                text-align: center; /* Center text */
            }
            
            .navbar-nav {
                align-items: center !important; /* Center align items */
                justify-content: center;
                width: 100%;
            }

            .nav-item {
                width: 100%;
                text-align: center;
                margin-bottom: 2px; /* Minimal spacing between links */
            }

            .nav-link {
                display: block;
                padding: 6px 0; /* Smaller padding for links */
                font-size: 0.85rem; /* Slightly smaller font for mobile */
            }

            /* Compact User Profile / Login Button area */
            .d-flex.align-items-center {
                justify-content: center;
                margin-top: 0.8rem; /* Reduced top margin */
                width: 100%;
                padding-top: 0.8rem;
                border-top: 1px solid rgba(255,255,255,0.05); /* Subtle separator */
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg fixed-top" id="mainNav">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="Asset 1.png" alt="Logo Nafas" height="45"> 
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item"><a class="nav-link " href="index1.php">À Propos</a></li>
                    <li class="nav-item"><a class="nav-link" href="sensibilisation.php">Sensibilisation</a></li>
                    <li class="nav-item"><a class="nav-link" href="opportunites.php">Opportunités</a></li>
                    <li class="nav-item"><a class="nav-link" href="quiz.php">Quiz</a></li>
                    <li class="nav-item"><a class="nav-link active" href="story_telling.php">Storytelling</a></li>
                    <li class="nav-item"><a class="nav-link" href="media.php">Médias</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" id="theme-toggle"><i class="fas fa-sun"></i></a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-white" href="#" role="button" data-bs-toggle="dropdown">
                                <span class="fw-bold d-none d-lg-block"><?php echo htmlspecialchars($username); ?></span>
                                <img src="<?php echo htmlspecialchars($userImage); ?>" class="rounded-circle border border-2" width="40" height="40" style="object-fit: cover; border-color: #0d635d !important;">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2">
                                <li><div class="dropdown-header px-3 py-2 text-muted small">Connecté en tant que <strong><?php echo htmlspecialchars($username); ?></strong></div></li>
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2" style="color: #0d635d;"></i> Mon Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logoutt.php"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="signup.php" class="nav-link cta-button">Je m'engage</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-container-full">
        <div class="main-content">
            <div class="feed-header">
                <div><h1>Fil d'actualité</h1><p>Découvrez les parcours inspirants de la communauté</p></div>
                <div class="feed-filters"><span class="filter-btn active">Récents</span><span class="filter-btn">Populaires</span></div>
            </div>

            <?php if (isset($_SESSION["success"])): ?>
                <div class="alert alert-success custom-alert"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION["success"]); ?></div>
                <?php unset($_SESSION["success"]); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION["error"])): ?>
                <div class="alert alert-error custom-alert"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION["error"]); ?></div>
                <?php unset($_SESSION["error"]); ?>
            <?php endif; ?>

            <?php if (mysqli_num_rows($stories_result) == 0): ?>
                <div class="empty-state">
                    <div class="icon-box"><i class="fas fa-pen-fancy"></i></div>
                    <h3>C'est calme ici...</h3>
                    <p>Soyez le premier à partager votre histoire de réussite !</p>
                    <?php if ($isLoggedIn): ?><button onclick="openStoryModal()" class="btn-primary-soft mt-3">Écrire une histoire</button><?php endif; ?>
                </div>
            <?php else: ?>
                <div class="stories-stack">
                    <?php while ($story = mysqli_fetch_assoc($stories_result)): 
                        $story_id = $story['story_id'];
                        $story_content = $story['content'];
                        $story_author = $story['username'];
                        $author_initial = strtoupper(substr($story_author, 0, 1));
                        $date = date('d M Y', strtotime($story['created_at']));
                        $likes_count = 0;
                        $count_likes_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM story_likes WHERE story_id = $story_id");
                        if ($count_likes_res) { $likes_count = mysqli_fetch_assoc($count_likes_res)['total']; }
                        $user_has_liked = false;
                        if ($isLoggedIn) {
                            $check_user_like = mysqli_query($conn, "SELECT * FROM story_likes WHERE member_id = '$member_id' AND story_id = '$story_id'");
                            if ($check_user_like && mysqli_num_rows($check_user_like) > 0) { $user_has_liked = true; }
                        }
                        $comments_result = mysqli_query($conn, "SELECT s.*, m.username FROM storytelling s JOIN members m ON s.member_id = m.member_id WHERE s.parent_id = $story_id AND s.status = 'approved' ORDER BY s.created_at ASC");
                        $comment_count = mysqli_num_rows($comments_result);
                    ?>
                        <article class="story-card">
                            <div class="card-header">
                                <div class="author-group">
                                    <div class="avatar-circle"><?php echo $author_initial; ?></div>
                                    <div class="user-meta"><h3><?php echo htmlspecialchars($story_author); ?></h3><span class="story-date"><?php echo $date; ?></span></div>
                                </div>
                                <?php if ($isLoggedIn && $story['member_id'] == $member_id): ?>
                                    <form method="POST" onsubmit="return confirm('Supprimer ?');">
                                        <input type="hidden" name="story_id" value="<?php echo $story_id; ?>">
                                        <button type="submit" name="delete_story" class="btn-icon-danger"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="card-body"><p><?php echo nl2br(htmlspecialchars($story_content)); ?></p></div>
                            <div class="card-stats">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="story_id" value="<?php echo $story_id; ?>">
                                    <button type="submit" name="toggle_like" class="btn-stat <?php echo $user_has_liked ? 'liked' : ''; ?>">
                                        <i class="<?php echo $user_has_liked ? 'fas' : 'far'; ?> fa-heart"></i><span><?php echo $likes_count; ?></span>
                                    </button>
                                </form>
                                <div class="btn-stat"><i class="far fa-comment-alt"></i> <span><?php echo $comment_count; ?></span></div>
                            </div>
                            <div class="comments-section-wide">
                                <?php if ($comment_count > 0): ?>
                                    <?php $shown_comments = 0; mysqli_data_seek($comments_result, 0); while ($comment = mysqli_fetch_assoc($comments_result)): if($shown_comments >= 3) break; $shown_comments++; ?>
                                        <div class="comment-row"><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong><span><?php echo htmlspecialchars($comment['content']); ?></span></div>
                                    <?php endwhile; ?>
                                    <?php if($comment_count > 3): ?><div class="view-more-comments">Voir <?php echo $comment_count - 3; ?> autres commentaires...</div><?php endif; ?>
                                <?php endif; ?>
                                <?php if (isset($_SESSION["member_id"])): ?>
                                    <form method="POST" class="wide-comment-form">
                                        <input type="hidden" name="parent_id" value="<?php echo $story_id; ?>">
                                        <input type="text" name="comment_content" placeholder="Écrivez un commentaire..." required autocomplete="off"><button type="submit"><i class="fas fa-paper-plane"></i></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="sidebar">
            <div class="widget cta-widget-pro">
                <div class="widget-bg-icon"><i class="fas fa-bullhorn"></i></div>
                <h3>Votre voix compte</h3><p>Inspirez la prochaine génération.</p>
                <?php if ($isLoggedIn): ?><button onclick="openStoryModal()" class="btn-glass"><i class="fas fa-plus-circle"></i> Partager</button><?php else: ?><a href="login.php" class="btn-glass">Se connecter</a><?php endif; ?>
            </div>
            <div class="widget">
                <div class="widget-header"><h4>Favoris</h4></div>
                <?php if ($isLoggedIn) { $likes_query = "SELECT s.*, m.username FROM story_likes l JOIN storytelling s ON l.story_id = s.story_id JOIN members m ON s.member_id = m.member_id WHERE l.member_id = '$member_id' ORDER BY l.created_at DESC LIMIT 5"; $likes_result = mysqli_query($conn, $likes_query); } ?>
                <div class="widget-content">
                    <?php if (!$isLoggedIn || mysqli_num_rows($likes_result) == 0): ?><div class="text-muted text-center small py-2">Aucun favori.</div><?php else: ?><ul class="fav-list"><?php while ($fav = mysqli_fetch_assoc($likes_result)): ?><li><a href="#" class="fav-link"><span class="fav-author"><?php echo htmlspecialchars($fav['username']); ?></span><span class="fav-excerpt"><?php echo htmlspecialchars(substr($fav['content'], 0, 30)); ?>...</span></a></li><?php endwhile; ?></ul><?php endif; ?>
                </div>
            </div>
        </aside>
    </div>

    <div id="storyModal" class="modal-overlay">
        <div class="modal-content-pro">
            <div class="modal-header-pro"><h2>Nouvelle histoire</h2><button onclick="closeStoryModal()" class="close-btn-pro"><i class="fas fa-times"></i></button></div>
            <div class="modal-body-pro">
                <form method="POST">
                    <div class="textarea-wrapper"><textarea name="story_content" id="storyTextarea" placeholder="Partagez votre expérience..." required></textarea><button type="button" class="mic-btn-floating" id="micBtn"><i class="fas fa-microphone"></i></button></div>
                    <span class="mic-status" id="micStatus">Écoute...</span>
                    <div class="modal-actions"><button type="button" onclick="closeStoryModal()" class="btn-text">Annuler</button><button type="submit" name="submit_story" class="btn-primary-pro">Publier</button></div>
                </form>
            </div>
        </div>
    </div>

    <footer class="py-5">
        <div class="container pt-4">
            <div class="row g-4">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <a href="#" class="d-flex align-items-center text-decoration-none mb-3">
                        <img src="Asset 1.png" alt="Nafas" height="35" class="me-2">
                    </a>
                    <p class="text-muted">
                        Une plateforme dédiée à la jeunesse tunisienne, offrant des alternatives concrètes et un espace d'expression pour construire un avenir meilleur, ici.
                    </p>
                </div>
                <div class="col-6 col-lg-2 offset-lg-2">
                    <h5 class="fw-bold mb-3">Navigation</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index1.php" class="text-muted text-decoration-none">À Propos</a></li>
                        <li class="mb-2"><a href="media.php" class="text-muted text-decoration-none">Médias</a></li>
                        <li class="mb-2"><a href="story_telling.php" class="text-muted text-decoration-none">Storytelling</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5 class="fw-bold mb-3">Suivez-nous</h5>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm rounded-circle social-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-sm rounded-circle social-btn"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="btn btn-sm rounded-circle social-btn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="btn btn-sm rounded-circle social-btn"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-top border-secondary mt-5 pt-4 text-center text-muted small" style="border-color: var(--border-color) !important;">
                &copy; 2025 Nafas. Tous droits réservés.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="story_telling.js"></script>
</body>
</html>
<?php mysqli_close($conn); ?>