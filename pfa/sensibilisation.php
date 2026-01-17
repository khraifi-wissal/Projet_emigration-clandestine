<?php
/* ===== DEBUG MODE (Remove lines 3-4 when live) ===== */
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "connexion.php";

// --- FETCH DATA ---
try {
    $sql = "SELECT * FROM sensibilisation ORDER BY date_publication DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $sensibilisation_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sensibilisation_data = [];
}

// --- USER CHECK & PROFILE LOGIC (Adapted for PDO) ---
$isLoggedIn = isset($_SESSION["member_id"]);
$username = $_SESSION['username'] ?? "Membre";
$userImage = "https://cdn-icons-png.flaticon.com/512/149/149071.png"; 

if ($isLoggedIn && isset($conn)) {
    try {
        $member_id = $_SESSION['member_id'];
        $stmt_user = $conn->prepare("SELECT profile_image FROM members WHERE member_id = :id");
        $stmt_user->execute([':id' => $member_id]);
        $row_user = $stmt_user->fetch(PDO::FETCH_ASSOC);
        
        if ($row_user && !empty($row_user['profile_image'])) {
            $userImage = $row_user['profile_image'];
        }
    } catch (Exception $e) {
        // Silent error logic if needed
    }
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensibilisation | Nafas</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* --- THEME VARIABLES --- */
        :root {
            --font-main: 'Poppins', sans-serif;
            --font-heading: 'Cairo', sans-serif;
            --brand-teal: #0F766E;
            --brand-red: #E11D48;
            --brand-dark: #0f172a;
            
            /* Dark Mode Defaults */
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.08);
            --shadow-soft: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
            --shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.4);
        }

        /* Light Mode Overrides */
        [data-bs-theme="light"] {
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #475569;
            --border-color: rgba(0, 0, 0, 0.08);
            --shadow-soft: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            --shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        /* Accessibility: Global Focus Styles */
        :focus-visible {
            outline: 2px solid var(--brand-teal);
            outline-offset: 4px;
            border-radius: 4px;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-body);
            color: var(--text-main);
            transition: background-color 0.3s ease, color 0.3s ease;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        /* --- NAVIGATION --- */
        .navbar {
            padding: 1rem 2rem;
            transition: all 0.3s ease;
            background: transparent;
        }
        .navbar.scrolled {
            background-color: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            padding: 0.8rem 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            margin: 0 5px;
            transition: color 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--brand-teal) !important;
        }
        .nav-link.cta-button {
            background-color: var(--brand-teal);
            color: white !important;
            padding: 8px 20px;
            border-radius: 5px;
        }
        .nav-link.cta-button:hover {
            background-color: #201f1dff;
            color: white !important;
        }

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

        /* --- HERO SECTION --- */
        .hero-section {
            position: relative;
            height: 75vh;
            min-height: 550px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=1920&auto=format&fit=crop') center/cover no-repeat;
            margin-top: -80px;
            padding-top: 80px;
        }
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.6) 0%, rgba(15, 23, 42, 0.85) 60%, var(--bg-body) 100%);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 850px;
            padding: 0 20px;
        }
        .hero-title {
            font-family: var(--font-heading);
            font-weight: 900;
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            line-height: 1.1;
            margin-bottom: 1.5rem;
            color: #fff;
            text-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .hero-subtitle {
            font-size: clamp(1rem, 2vw, 1.25rem);
            color: #e2e8f0;
            margin-bottom: 2.5rem;
            font-weight: 300;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        /* --- ARTICLE CARDS --- */
        .article-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s;
            margin-bottom: 3.5rem;
            box-shadow: var(--shadow-soft);
        }
        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
            border-color: rgba(15, 118, 110, 0.4);
        }
        .article-image-col {
            min-height: 300px;
            overflow: hidden;
            position: relative;
        }
        .article-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .article-card:hover .article-img { transform: scale(1.05); }

        .article-body { padding: 2.5rem; display: flex; flex-direction: column; height: 100%; }
        @media (max-width: 768px) { .article-body { padding: 1.5rem; } }

        .article-date {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            background: rgba(15, 118, 110, 0.1);
            color: var(--brand-teal);
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 1rem;
            letter-spacing: 0.05em;
        }
        .article-title {
            font-family: var(--font-heading);
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 1rem;
            color: var(--text-main);
            line-height: 1.2;
        }
        .article-text {
            color: var(--text-muted);
            line-height: 1.7;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }

        /* --- ACTIONS & AUTHOR --- */
        .author-box {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            margin-top: auto;
        }
        .author-icon {
            width: 36px; height: 36px;
            background: var(--bg-body);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-teal);
            border: 1px solid var(--border-color);
            font-size: 0.9rem;
        }
        .btn-read-more {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1.25rem;
            border: 1px solid var(--border-color);
            border-radius: 50px;
            color: var(--text-main);
            font-size: 0.85rem;
            font-weight: 500;
            background: transparent;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-read-more:hover {
            background: var(--brand-teal);
            border-color: var(--brand-teal);
            color: white;
        }

        /* --- MISSION STATEMENT --- */
        .mission-card {
            background: var(--bg-card);
            border-left: 5px solid var(--brand-red);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
        }
        .mission-card::after {
            content: '';
            position: absolute;
            top: 0; right: 0; bottom: 0; width: 30%;
            background: linear-gradient(90deg, transparent, rgba(225, 29, 72, 0.05));
            pointer-events: none;
        }

        /* --- ANIMATIONS --- */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.5, 0, 0, 1);
        }
        .reveal.active { opacity: 1; transform: translateY(0); }
        
        /* Footer & Utils */
        .bg-dark-footer { background-color: var(--brand-dark); border-top: 1px solid var(--border-color); }
        .social-btn { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; transition: 0.3s; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
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

    <header class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content reveal">
            <span class="d-inline-flex align-items-center gap-2 bg-dark bg-opacity-50 border border-danger text-danger px-3 py-1 rounded-pill mb-4 fs-6 fw-bold">
                <i class="fas fa-exclamation-circle"></i> Réalité & Prévention
            </span>
            
            <h1 class="hero-title">Face à la Mer,<br>Face à la Réalité</h1>
            
            <p class="hero-subtitle">
                L'immigration clandestine n'est pas une solution, c'est une impasse. 
                Informez-vous sur les risques réels et découvrez les alternatives concrètes qui s'offrent à vous.
            </p>
            
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="#content" class="btn btn-outline-light rounded-pill px-4 py-2 fw-medium">
                    Lire les articles
                </a>
                <a href="opportunites.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold border-0 shadow-lg" style="background-color: var(--brand-teal);">
                    Voir les alternatives
                </a>
            </div>
        </div>
    </header>

    <section id="content" class="container py-5" style="position: relative; z-index: 2;">
        
        <?php if (!empty($sensibilisation_data)): ?>
            <?php foreach($sensibilisation_data as $index => $row): ?>
                
                <article class="article-card reveal">
                    <div class="row g-0">
                        <div class="col-lg-5 article-image-col <?= ($index % 2 != 0) ? 'order-lg-2' : '' ?>">
                            <?php 
                                $imgSrc = (!empty($row['image_path']) && file_exists($row['image_path'])) 
                                          ? $row['image_path'] 
                                          : 'https://images.unsplash.com/photo-1518544976451-f762746487e9?q=80&w=1000&auto=format&fit=crop'; 
                            ?>
                            <img src="<?= htmlspecialchars($imgSrc) ?>" class="article-img" alt="Illustration pour <?= htmlspecialchars($row['titre']); ?>" loading="lazy">
                        </div>

                        <div class="col-lg-7">
                            <div class="article-body">
                                <div class="mb-3">
                                    <span class="article-date">
                                        <i class="far fa-calendar-alt me-2"></i>
                                        <?= date('d M Y', strtotime($row['date_publication'])); ?>
                                    </span>
                                    
                                    <h2 class="article-title"><?= htmlspecialchars($row['titre']); ?></h2>
                                    
                                    <div class="article-text">
                                        <?= nl2br(htmlspecialchars(substr($row['description'], 0, 350))) ?>...
                                    </div>
                                </div>

                                <div class="author-box">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="author-icon"><i class="fas fa-feather-alt"></i></div>
                                        <div class="d-flex flex-column">
                                            <small class="text-muted text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Publié par</small>
                                            <span class="fw-bold fs-6" style="color: var(--text-main);">Équipe Nafas</span>
                                        </div>
                                    </div>
                                    <div class="ms-auto">
                                        <button class="btn-read-more">
                                            Lire la suite <i class="fas fa-arrow-right ms-2"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

            <?php endforeach; ?>
        <?php else: ?>
            
            <div class="text-center py-5 reveal">
                <div class="mb-4 text-secondary opacity-50">
                    <i class="fas fa-newspaper fa-5x"></i>
                </div>
                <h3 class="fw-bold mb-2" style="color: var(--text-main);">Aucun article pour le moment</h3>
                <p class="text-muted">Nos équipes travaillent sur des contenus de sensibilisation percutants.</p>
            </div>

        <?php endif; ?>

        <div class="mission-card mt-5 reveal shadow-lg">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center text-md-start mb-3 mb-md-0">
                        <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle" style="width: 80px; height: 80px;">
                            <i class="fas fa-life-ring fa-2x"></i>
                        </div>
                    </div>
                    <div class="col-md-10 text-center text-md-start">
                        <h2 class="fw-bold mb-3" style="font-family: var(--font-heading); color: var(--text-main);">Notre Mission</h2>
                        <p class="fs-5 text-muted mb-0" style="line-height: 1.6;">
                            La méconnaissance des risques est un danger mortel. 
                            <strong style="color: var(--text-main);">Nous fournissons des informations vérifiées</strong> pour briser les mythes de l'eldorado européen 
                            et mettre en lumière les succès possibles en Tunisie.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <footer class="bg-dark-footer text-white py-5">
        <div class="container pt-4">
            <div class="row g-4">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-3">
                        <img src="Asset 1.png" alt="Nafas" height="30" class="me-2" >
                    </div>
                    <p class="text-white-50">
                        Une plateforme dédiée à la jeunesse tunisienne. Nous croyons que ta réussite est possible ici, chez toi.
                    </p>
                </div>
                <div class="col-6 col-lg-2 offset-lg-2">
                    <h5 class="fw-bold mb-3">Navigation</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index1.php" class="text-white-50 text-decoration-none">À Propos</a></li>
                        <li class="mb-2"><a href="media.php" class="text-white-50 text-decoration-none">Médias</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-white-50 text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5 class="fw-bold mb-3">Suivez-nous</h5>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle social-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle social-btn"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle social-btn"><i class="fab fa-linkedin-in"></i></a>
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
        // --- Navbar Scroll Effect ---
        const navbar = document.getElementById('mainNav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // --- SCROLL REVEAL ANIMATION ---
        document.addEventListener('DOMContentLoaded', function() {
            const reveals = document.querySelectorAll('.reveal');
            const revealOnScroll = () => {
                const windowHeight = window.innerHeight;
                const elementVisible = 100;
                reveals.forEach((reveal) => {
                    const elementTop = reveal.getBoundingClientRect().top;
                    if (elementTop < windowHeight - elementVisible) {
                        reveal.classList.add('active');
                    }
                });
            };
            window.addEventListener('scroll', revealOnScroll);
            revealOnScroll(); // Trigger once on load
        });

        // --- THEME TOGGLE LOGIC ---
        const themeBtn = document.getElementById('theme-toggle');
        const icon = themeBtn.querySelector('i');
        
        // 1. Check saved theme from LocalStorage
        const savedTheme = localStorage.getItem('theme') || 'dark'; // Default to dark
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        updateIcon(savedTheme);

        // 2. Toggle on click
        themeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });

        function updateIcon(theme) {
            if (theme === 'dark') {
                icon.className = 'fas fa-moon'; 
            } else {
                icon.className = 'fas fa-sun'; 
            }
        }
    </script>
</body>
</html>