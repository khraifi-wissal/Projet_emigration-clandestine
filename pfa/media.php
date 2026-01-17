<?php
/* ===== DEBUG MODE (Remove lines 3-4 when live) ===== */
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

/* ===== PROTECT PAGE ===== */
if (!isset($_SESSION["member_id"])) {
    header("Location: login.php");
    exit;
}

/* ===== DATABASE CONNECTION ===== */
$conn = mysqli_connect("127.0.0.1", "root", "", "nafas");
if (!$conn) {
    die("<div style='color:red; padding:20px;'>Erreur de connexion : " . mysqli_connect_error() . "</div>");
}

/* ===== USER PROFILE LOGIC ===== */
$isLoggedIn = true; 
$member_id = $_SESSION['member_id'];
$username = $_SESSION['username'] ?? "Membre";
$userImage = "https://cdn-icons-png.flaticon.com/512/149/149071.png"; 

$query_user = "SELECT * FROM members WHERE member_id = '$member_id'";
$result_user = mysqli_query($conn, $query_user);
if ($result_user && mysqli_num_rows($result_user) > 0) {
    $row_user = mysqli_fetch_assoc($result_user);
    if (!empty($row_user['profile_image'])) {
        $userImage = $row_user['profile_image'];
    }
}

/* ===== FETCH BROCHURES ===== */
$query_brochures = "SELECT b.brochure_id, b.title, b.file_path, a.username AS author
                    FROM brochures b
                    JOIN admins a ON b.created_by = a.admin_id
                    ORDER BY b.created_at DESC";
$result_brochures = mysqli_query($conn, $query_brochures);
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centre de Ressources | Nafas</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="media.css">
    <style>
        :root {
            --brand-teal: #0F766E;
            --brand-dark: #0f172a;
        }

        /* --- NAVIGATION STYLES --- */
        .navbar {
            padding: 1rem 2rem;
            transition: all 0.3s ease;
            background: transparent; 
        }

        .navbar.scrolled {
            background-color: rgba(15, 23, 42, 0.95); /* Dark background on scroll */
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

        /* Ensure hero has padding for fixed nav */
        .mini-hero {
            padding-top: 140px;
        }
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
                    <li class="nav-item"><a class="nav-link" href="sensibilisation.php">Sensibilisation</a></li>
                    <li class="nav-item"><a class="nav-link" href="opportunites.php">Opportunités</a></li>
                    <li class="nav-item"><a class="nav-link" href="quiz.php">Quiz</a></li>
                    <li class="nav-item"><a class="nav-link" href="story_telling.php">Storytelling</a></li>
                    <li class="nav-item"><a class="nav-link active" href="media.php">Médias</a></li>
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

    <header class="mini-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-2">Centre de <span class="text-primary">Documentation</span></h1>
            <p class="lead opacity-75">Accédez à nos guides officiels et brochures de sensibilisation.</p>
        </div>
    </header>

    <main class="container pb-5">
        
        <div class="row g-4 justify-content-center mb-5" style="position: relative; z-index: 2;">
            <?php if(mysqli_num_rows($result_brochures) > 0): ?>
                <?php while($b = mysqli_fetch_assoc($result_brochures)): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card brochure-card h-100 p-4">
                            <div class="card-body">
                                <div class="icon-box">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <h4 class="card-title fw-bold mb-2"><?= htmlspecialchars($b['title']) ?></h4>
                                <p class="card-text text-muted small mb-4">
                                    <i class="fas fa-user-tie me-1"></i> Publication Officielle • <?= htmlspecialchars($b['author']) ?>
                                </p>
                                <a href="track_download.php?id=<?= $b['brochure_id'] ?>" download class="btn btn-primary w-100 rounded-pill fw-bold">
                                    <i class="fas fa-download me-2"></i> Télécharger PDF
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-folder-open fa-4x text-muted opacity-25 mb-3"></i>
                    <p class="text-muted">Aucun document disponible pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="card awareness-card mb-5">
            <div class="row g-0 align-items-center">
                <div class="col-md-5">
                    <img src="storyimg.jpg" class="img-fluid banner-img w-100" alt="Sensibilisation">
                </div>
                <div class="col-md-7">
                    <div class="card-body p-5">
                        <h2 class="fw-bold mb-3 display-6">Votre sécurité est <span class="text-primary">notre priorité</span></h2>
                        <p class="card-text text-muted mb-4 lead fs-6">
                            Ne risquez pas votre vie pour un mirage. Nos experts ont compilé des informations cruciales pour vous aider à comprendre les réalités de la migration et les opportunités qui s'offrent à vous ici.
                        </p>
                        <a href="contact.php" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold">
                            Parler à un conseiller <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <footer class="bg-dark-footer text-white py-5">
        <div class="container pt-4">
            <div class="row g-4">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-3">
                        <img src="Asset 1.png" alt="Nafas" height="30" class="me-2">
                        
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
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 32px; height: 32px; display:flex; align-items:center; justify-content:center;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 32px; height: 32px; display:flex; align-items:center; justify-content:center;"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-top border-secondary mt-5 pt-4 text-center text-white-50 small">
                &copy; 2025 Nafas. Tous droits réservés.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="media.js"></script>
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
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>