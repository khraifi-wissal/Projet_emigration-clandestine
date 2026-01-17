<?php
session_start();
require_once "connexion.php";

$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id <= 0) {
    header("Location: sensibilisation.php");
    exit();
}

// Initialisation utilisateur
$isLoggedIn = false;
$username = "Membre";
$userImage = "https://cdn-icons-png.flaticon.com/512/149/149071.png";

if (isset($_SESSION["member_id"])) {
    $isLoggedIn = true;
    $member_id = $_SESSION['member_id'];
    $username = $_SESSION['username'] ?? "Membre";

    try {
        $stmt_user = $conn->prepare("SELECT profile_image FROM members WHERE member_id = :id");
        $stmt_user->execute([':id' => $member_id]);
        $row_user = $stmt_user->fetch(PDO::FETCH_ASSOC);
        if ($row_user && !empty($row_user['profile_image'])) {
            $userImage = $row_user['profile_image'];
        }
    } catch (PDOException $e) { }
}

// Fetch de l'article (Utilisation de 'id' suite à votre erreur SQL précédente)
try {
    $stmt = $conn->prepare("SELECT * FROM sensibilisation WHERE id = :id");
    $stmt->execute([':id' => $article_id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        die("Article introuvable.");
    }
} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['titre']) ?> | Nafas</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --brand-teal: #0F766E;
            --brand-dark: #0f172a;
        }

        body {
            font-family: 'Poppins', sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* --- NAVBAR --- */
        .navbar {
            padding: 1rem 2rem;
            transition: all 0.3s ease;
            background-color: rgba(15, 23, 42, 0.95);
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            text-transform: uppercase;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .nav-link:hover, .nav-link.active { color: var(--brand-teal) !important; }

        /* --- ARTICLE LAYOUT --- */
        .main-image-container {
            width: 100%;
            height: 60vh;
            overflow: hidden;
            background-color: #000;
            margin-top: 70px;
        }
        .main-image { width: 100%; height: 100%; object-fit: cover; opacity: 0.9; }

        .article-content {
            max-width: 850px;
            margin: -60px auto 100px;
            padding: 4rem 3rem;
            border-radius: 20px;
            position: relative;
            z-index: 10;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        /* Adaptations Thèmes */
        [data-bs-theme="light"] .article-content { background: #ffffff; color: #1e293b; }
        [data-bs-theme="dark"] .article-content { background: #1e293b; color: #f1f5f9; border: 1px solid rgba(255,255,255,0.05); }

        h1 {
            font-family: 'Cairo', sans-serif;
            font-weight: 900;
            font-size: clamp(2rem, 5vw, 3rem);
            margin-bottom: 1.5rem;
        }

        .full-text {
            font-size: 1.15rem;
            line-height: 1.9;
            white-space: pre-line;
            text-align: justify;
        }

        .btn-back {
            text-decoration: none;
            color: var(--brand-teal);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            margin-bottom: 2rem;
            transition: 0.3s;
        }
        .btn-back:hover { transform: translateX(-5px); }

        /* --- FOOTER --- */
        footer { background-color: var(--brand-dark); color: white; }
        .social-btn { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 50%; border: 1px solid rgba(255,255,255,0.2); color: white; text-decoration: none; transition: 0.3s; }
        .social-btn:hover { background: var(--brand-teal); border-color: var(--brand-teal); }

        @media (max-width: 768px) {
            .article-content { padding: 2rem 1.5rem; margin-top: -30px; border-radius: 0; }
            .main-image-container { height: 40vh; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container-fluid">
            <a class="navbar-brand" href="index1.php">
                <img src="logo-nafas.png" alt="Logo Nafas" height="45"> 
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                   <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item"><a class="nav-link " href="index1.php">À Propos</a></li>
                    <li class="nav-item"><a class="nav-link active" href="sensibilisation.php">Sensibilisation</a></li>
                    <li class="nav-item"><a class="nav-link" href="opportunites.php">Opportunités</a></li>
                    <li class="nav-item"><a class="nav-link" href="quiz.php">Quiz</a></li>
                    <li class="nav-item"><a class="nav-link" href="story_telling.php">Storytelling</a></li>
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
                                <img src="<?php echo htmlspecialchars($userImage); ?>" class="rounded-circle border border-2" width="40" height="40" style="object-fit: cover; border-color: var(--brand-teal) !important;">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Mon Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logoutt.php"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="signup.php" class="nav-link" style="background-color: var(--brand-teal); color: white !important; padding: 8px 20px; border-radius: 5px;">Je m'engage</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="main-image-container">
        <img src="<?= htmlspecialchars($article['image_path']) ?>" alt="Header" class="main-image">
    </div>

    <article class="container">
        <div class="article-content shadow">
            <a href="sensibilisation.php" class="btn-back">
                <i class="fas fa-arrow-left me-2"></i> Retour aux articles
            </a>

            <h1><?= htmlspecialchars($article['titre']) ?></h1>
            

            <div class="full-text">
                <?= nl2br(htmlspecialchars($article['description'])) ?>
            </div>
        </div>
    </article>

    <footer class="py-5">
        <div class="container pt-4">
            <div class="row g-4">
                <div class="col-lg-4">
                    <img src="logo-nafas.png" alt="Nafas" height="35" class="mb-3">
                    <p class="text-white-50">Une plateforme dédiée à la jeunesse tunisienne. Bâtissons un avenir ici.</p>
                </div>
                <div class="col-6 col-lg-2 offset-lg-2">
                    <h5 class="fw-bold mb-3">Navigation</h5>
                    <ul class="list-unstyled">
                        <li><a href="index1.php" class="text-white-50 text-decoration-none small">À Propos</a></li>
                        <li><a href="contact.php" class="text-white-50 text-decoration-none small">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5 class="fw-bold mb-3">Suivez-nous</h5>
                    <div class="d-flex gap-2">
                        <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-top border-secondary mt-5 pt-4 text-center text-white-50 small">
                &copy; 2025 Nafas. Tous droits réservés.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- GESTION DU MODE SOMBRE (Copie de index1.php) ---
        const themeBtn = document.getElementById('theme-toggle');
        const icon = themeBtn.querySelector('i');
        
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        icon.className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';

        themeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            icon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
        });
    </script>
</body>
</html>