<?php
session_start();
require_once "connexion.php"; // Uses PDO

// Redirect if not logged in
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$member_id = $_SESSION['member_id'];

/* ======================================================
   1. FETCH USER INFO
   ====================================================== */
$stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ?");
$stmt->execute([$member_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$username = $user['username'] ?? 'Membre';
$email = $user['email'] ?? '';
// Use specific image or fallback
$userImage = !empty($user['profile_image']) ? $user['profile_image'] : "https://cdn-icons-png.flaticon.com/512/149/149071.png";
$joinDate = isset($user['created_at']) ? date('d F Y', strtotime($user['created_at'])) : 'N/A';

/* ======================================================
   2. FETCH QUIZ RESULTS (For Timeline)
   ====================================================== */
$stmt_quiz = $conn->prepare("
    SELECT 
        q.title,
        COUNT(qr.question_id) as total_answered,
        SUM(CASE WHEN qr.selected_option = qq.correct_option THEN 1 ELSE 0 END) as score,
        (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.quiz_id) as total_questions,
        MAX(qr.created_at) as last_taken
    FROM quiz_responses qr
    JOIN quiz_questions qq ON qr.question_id = qq.question_id
    JOIN quiz q ON qq.quiz_id = q.quiz_id
    WHERE qr.member_id = ?
    GROUP BY q.quiz_id
    ORDER BY last_taken DESC
");
$stmt_quiz->execute([$member_id]);
$quizzes = $stmt_quiz->fetchAll(PDO::FETCH_ASSOC);

/* ======================================================
   3. FETCH DOWNLOADED BROCHURES (All)
   ====================================================== */
$stmt_dl = $conn->prepare("
    SELECT DISTINCT b.brochure_id, b.title, b.file_path, MAX(bd.downloaded_at) as last_downloaded
    FROM brochure_downloads bd 
    JOIN brochures b ON bd.brochure_id = b.brochure_id 
    WHERE bd.member_id = ? 
    GROUP BY b.brochure_id
    ORDER BY last_downloaded DESC
");
$stmt_dl->execute([$member_id]);
$downloads = $stmt_dl->fetchAll(PDO::FETCH_ASSOC);

/* ======================================================
   4. FETCH FAVORITE OPPORTUNITIES (All)
   ====================================================== */
$stmt_fav = $conn->prepare("
    SELECT o.*, ofav.created_at as fav_date
    FROM opportunity_favorites ofav 
    JOIN opportunities o ON ofav.opp_id = o.opp_id 
    WHERE ofav.member_id = ? 
    ORDER BY ofav.created_at DESC
");
$stmt_fav->execute([$member_id]);
$favorites = $stmt_fav->fetchAll(PDO::FETCH_ASSOC);

/* ======================================================
   5. FETCH LIKED STORIES (All)
   ====================================================== */
$stmt_likes = $conn->prepare("
    SELECT s.story_id, s.content, s.created_at, m.username as author 
    FROM story_likes sl 
    JOIN storytelling s ON sl.story_id = s.story_id 
    JOIN members m ON s.member_id = m.member_id 
    WHERE sl.member_id = ? 
    ORDER BY sl.created_at DESC
");
$stmt_likes->execute([$member_id]);
$liked_stories = $stmt_likes->fetchAll(PDO::FETCH_ASSOC);

// Counts for Badges
$count_quiz = count($quizzes);
$count_dl = count($downloads);
$count_fav = count($favorites);
$count_likes = count($liked_stories);
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Espace | Nafas</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --brand-teal: #0F766E;
            --brand-dark: #0f172a;
            --brand-bg: #f3f4f6;
            --sidebar-width: 280px;
            --card-radius: 20px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--brand-bg);
            margin: 0;
            overflow-x: hidden;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background-color: var(--brand-dark);
            position: fixed;
            top: 0; left: 0;
            color: white;
            padding: 2rem 1.5rem;
            display: flex; flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            display: flex; align-items: center; gap: 10px;
            font-size: 1.5rem; font-weight: 700;
            margin-bottom: 3rem; color: white; text-decoration: none;
        }

        .nav-link-custom {
            display: flex; align-items: center;
            padding: 14px 16px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            margin-bottom: 5px;
        }

        .nav-link-custom i { width: 24px; margin-right: 12px; text-align: center; }
        .nav-link-custom:hover { background-color: rgba(255, 255, 255, 0.05); color: white; }
        
        .nav-link-custom.active {
            background-color: var(--brand-teal);
            color: white;
            box-shadow: 0 4px 12px rgba(15, 118, 110, 0.3);
        }

        .nav-link-custom .badge {
            margin-left: auto;
            background: rgba(255,255,255,0.2);
            color: white;
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem 3rem;
            min-height: 100vh;
        }

        .tab-section { display: none; animation: fadeIn 0.4s ease; }
        .tab-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* --- CARDS --- */
        .custom-card {
            background: white;
            border-radius: var(--card-radius);
            padding: 2rem;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        /* Profile Hero */
        .profile-hero { display: flex; align-items: center; gap: 2rem; }
        
        .profile-hero img {
            width: 120px; height: 120px;
            border-radius: 50%; object-fit: cover;
            border: 4px solid #f1f5f9;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .social-pills a {
            display: inline-flex; width: 32px; height: 32px;
            background: #f1f5f9; border-radius: 50%;
            align-items: center; justify-content: center;
            color: var(--brand-dark); margin-right: 5px; transition: 0.2s;
        }
        .social-pills a:hover { background: var(--brand-teal); color: white; }

        /* Timeline (Quiz) */
        .timeline-item { position: relative; padding-left: 35px; margin-bottom: 25px; }
        .timeline-item::before {
            content: ''; position: absolute; left: 7px; top: 0; bottom: -25px;
            width: 2px; background: #e2e8f0;
        }
        .timeline-item:last-child::before { display: none; }
        .timeline-dot {
            position: absolute; left: 0; top: 5px;
            width: 16px; height: 16px; border-radius: 50%;
            background: white; border: 3px solid var(--brand-teal);
            z-index: 2;
        }
        .timeline-content {
            background: #f8fafc; padding: 15px; border-radius: 12px;
            display: flex; justify-content: space-between; align-items: center;
        }

        /* Item Lists (Downloads/Favs) */
        .item-row {
            display: flex; align-items: center;
            padding: 15px; border-bottom: 1px solid #f1f5f9;
            transition: 0.2s;
        }
        .item-row:hover { background-color: #f8fafc; }
        .item-row:last-child { border-bottom: none; }
        .icon-box {
            width: 45px; height: 45px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin-right: 15px; font-size: 1.2rem;
        }
        
        /* Colors */
        .bg-teal-soft { background: rgba(15, 118, 110, 0.1); color: var(--brand-teal); }
        .bg-blue-soft { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .bg-red-soft { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .bg-orange-soft { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1.5rem; }
            .profile-hero { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

    <aside class="sidebar" id="sidebar">
        <a class="navbar-brand" href="#">
                <img src="logo-nafas.png" alt="Logo Nafas" height="45"> 
            </a>
        
        <nav>
            
            <br><br><br>
            <button class="nav-link-custom active" onclick="showTab('dashboard', this)">
                <i class="fas fa-th-large"></i> Tableau de bord
            </button>

            
            <button class="nav-link-custom" onclick="showTab('favorites', this)">
                <i class="fas fa-heart"></i> Mes Favoris
                <span class="badge"><?= $count_fav ?></span>
            </button>
            
            <button class="nav-link-custom" onclick="showTab('downloads', this)">
                <i class="fas fa-file-download"></i> Téléchargements
                <span class="badge"><?= $count_dl ?></span>
            </button>
            
            <button class="nav-link-custom" onclick="showTab('likes', this)">
                <i class="fas fa-thumbs-up"></i> Histoires Aimées
                <span class="badge"><?= $count_likes ?></span>
            </button>

            <div style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                <a href="index1.php" class="nav-link-custom">
                    <i class="fas fa-arrow-left"></i> Retour Accueil
                </a>
                <a href="logoutt.php" class="nav-link-custom text-danger">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </nav>
    </aside>

    <button class="btn btn-dark d-lg-none position-fixed top-0 start-0 m-3 z-3" onclick="document.getElementById('sidebar').classList.toggle('active')">
        <i class="fas fa-bars"></i>
    </button>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0">Espace Membre</h3>
                <p class="text-muted small">Gérez vos activités et ressources sauvegardées.</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="d-none d-md-block text-end">
                    <div class="fw-bold"><?= htmlspecialchars($username) ?></div>
                    <div class="small text-muted">Membre</div>
                </div>
                <img src="<?= htmlspecialchars($userImage) ?>" width="45" height="45" class="rounded-circle border border-2 border-secondary" style="object-fit: cover;">
            </div>
        </div>

        <div id="dashboard" class="tab-section active">
            <div class="row g-4">
                
                <div class="col-12">
                    <div class="custom-card">
                        <div class="profile-hero">
                            <div class="position-relative">
                                <img src="<?= htmlspecialchars($userImage) ?>" alt="Profile">
                                
                                <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle p-2"></span>
                            </div>
                            
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h3 class="fw-bold mb-1"><?= htmlspecialchars($username) ?></h3>
                                        <p class="text-muted mb-2"><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($email) ?></p>
                                        <p class="text-muted small mb-3">Membre depuis le <?= $joinDate ?></p>
                                    </div>

                                    <div>
                                        <form id="avatarForm" action="update_avatar.php" method="POST" enctype="multipart/form-data" style="display: none;">
                                            <input type="file" name="avatar" id="avatarInput" accept="image/*" onchange="document.getElementById('avatarForm').submit();">
                                        </form>
                                        <button class="btn btn-light rounded-circle shadow-sm" onclick="document.getElementById('avatarInput').click();" title="Changer la photo">
                                            <i class="fas fa-camera text-primary"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="social-pills mt-2">
                                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                    <a href="#"><i class="fab fa-github"></i></a>
                                    <a href="#"><i class="fab fa-twitter"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="custom-card">
                        <h5 class="fw-bold mb-4">Vos résultats aux Quiz</h5>
                        
                        <?php if (empty($quizzes)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i>
                                <p>Aucun quiz passé pour le moment.</p>
                                <a href="quiz.php" class="btn btn-outline-primary btn-sm rounded-pill">Commencer un quiz</a>
                            </div>
                        <?php else: ?>
                            <div class="timeline-container">
                                <?php foreach ($quizzes as $q): 
                                    $percent = ($q['total_questions'] > 0) ? round(($q['score'] / $q['total_questions']) * 100) : 0;
                                    $color = ($percent >= 70) ? '#10b981' : (($percent >= 40) ? '#f59e0b' : '#ef4444');
                                ?>
                                    <div class="timeline-item">
                                        <div class="timeline-dot" style="border-color: <?= $color ?>"></div>
                                        <div class="timeline-content">
                                            <div>
                                                <h6 class="fw-bold mb-1"><?= htmlspecialchars($q['title']) ?></h6>
                                                <small class="text-muted"><i class="far fa-clock me-1"></i> <?= date('d M Y', strtotime($q['last_taken'])) ?></small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge rounded-pill" style="background-color: <?= $color ?>"><?= $percent ?>%</span>
                                                <div class="small text-muted mt-1"><?= $q['score'] ?>/<?= $q['total_questions'] ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="custom-card">
                        <h5 class="fw-bold mb-4">Aperçu rapide</h5>
                        
                        <div class="item-row p-2 rounded-3 mb-2" style="background: #f8fafc;">
                            <div class="icon-box bg-orange-soft"><i class="fas fa-star"></i></div>
                            <div>
                                <h5 class="mb-0 fw-bold"><?= $count_fav ?></h5>
                                <small class="text-muted">Favoris</small>
                            </div>
                        </div>

                        <div class="item-row p-2 rounded-3 mb-2" style="background: #f8fafc;">
                            <div class="icon-box bg-blue-soft"><i class="fas fa-download"></i></div>
                            <div>
                                <h5 class="mb-0 fw-bold"><?= $count_dl ?></h5>
                                <small class="text-muted">Téléchargements</small>
                            </div>
                        </div>

                        <div class="item-row p-2 rounded-3" style="background: #f8fafc;">
                            <div class="icon-box bg-red-soft"><i class="fas fa-heart"></i></div>
                            <div>
                                <h5 class="mb-0 fw-bold"><?= $count_likes ?></h5>
                                <small class="text-muted">Likes</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="favorites" class="tab-section">
            <div class="custom-card">
                <h4 class="fw-bold mb-4">Opportunités Sauvegardées</h4>
                <?php if (empty($favorites)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="far fa-star fa-3x mb-3 opacity-25"></i>
                        <p>Vous n'avez aucune opportunité en favori.</p>
                        <a href="opportunites.php" class="btn btn-primary rounded-pill">Explorer</a>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($favorites as $fav): ?>
                            <div class="col-md-6">
                                <div class="item-row border rounded-3">
                                    <div class="icon-box bg-orange-soft"><i class="fas fa-briefcase"></i></div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($fav['title']) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($fav['region']) ?> • <?= ucfirst($fav['category']) ?></small>
                                    </div>
                                    <a href="<?= htmlspecialchars($fav['link']) ?>" target="_blank" class="btn btn-sm btn-light rounded-circle"><i class="fas fa-external-link-alt"></i></a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="downloads" class="tab-section">
            <div class="custom-card">
                <h4 class="fw-bold mb-4">Fichiers Téléchargés</h4>
                <?php if (empty($downloads)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-cloud-download-alt fa-3x mb-3 opacity-25"></i>
                        <p>Aucun fichier dans votre historique.</p>
                        <a href="media.php" class="btn btn-primary rounded-pill">Voir les médias</a>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($downloads as $dl): ?>
                            <div class="list-group-item d-flex align-items-center px-0 py-3 border-bottom">
                                <div class="icon-box bg-blue-soft"><i class="fas fa-file-pdf"></i></div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($dl['title']) ?></h6>
                                    <small class="text-muted">Téléchargé le <?= date('d/m/Y à H:i', strtotime($dl['last_downloaded'])) ?></small>
                                </div>
                                <a href="<?= htmlspecialchars($dl['file_path']) ?>" download class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="fas fa-download me-2"></i>PDF
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div id="likes" class="tab-section">
            <div class="custom-card">
                <h4 class="fw-bold mb-4">Histoires Aimées</h4>
                <?php if (empty($liked_stories)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="far fa-heart fa-3x mb-3 opacity-25"></i>
                        <p>Vous n'avez aimé aucune histoire pour l'instant.</p>
                        <a href="story_telling.php" class="btn btn-primary rounded-pill">Lire des histoires</a>
                    </div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($liked_stories as $story): ?>
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 bg-light h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="badge bg-secondary me-2"><?= substr($story['author'], 0, 1) ?></div>
                                        <small class="fw-bold"><?= htmlspecialchars($story['author']) ?></small>
                                        <small class="ms-auto text-muted"><?= date('d M', strtotime($story['created_at'])) ?></small>
                                    </div>
                                    <p class="small text-muted mb-0 fst-italic">"<?= substr(htmlspecialchars($story['content']), 0, 120) ?>..."</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showTab(tabId, btnElement) {
            // 1. Hide all tabs
            document.querySelectorAll('.tab-section').forEach(el => el.classList.remove('active'));
            // 2. Deactivate all sidebar buttons
            document.querySelectorAll('.nav-link-custom').forEach(el => el.classList.remove('active'));
            
            // 3. Show selected tab
            document.getElementById(tabId).classList.add('active');
            // 4. Activate clicked button
            btnElement.classList.add('active');
        }
    </script>
</body>
</html>