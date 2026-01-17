<?php
/* ===== DEBUG MODE (Remove lines 3-4 when live) ===== */
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

/* ===== DB CONNECTION ===== */
$conn = mysqli_connect("127.0.0.1", "root", "", "nafas");

if (!$conn) {
    die("<div style='color:red; padding:20px;'>Erreur de connexion : " . mysqli_connect_error() . "</div>");
}

/* ===== USER PROFILE LOGIC ===== */
$isLoggedIn = false;
$username = "Membre";
$userImage = "https://cdn-icons-png.flaticon.com/512/149/149071.png"; // Default

if (isset($_SESSION["member_id"])) {
    $isLoggedIn = true;
    $member_id = $_SESSION['member_id'];
    $username = $_SESSION['username'] ?? "Membre";

    // Fetch specific profile image
    $query_user = "SELECT * FROM members WHERE member_id = '$member_id'";
    $result_user = mysqli_query($conn, $query_user);
    if ($result_user && mysqli_num_rows($result_user) > 0) {
        $row_user = mysqli_fetch_assoc($result_user);
        if (!empty($row_user['profile_image'])) {
            $userImage = $row_user['profile_image'];
        }
    }
}

$message_status = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $body = $_POST['message'] ?? '';

    if (!empty($name) && !empty($email) && !empty($subject) && !empty($body)) {
        // Mail logic here
        $message_status = '<div class="alert alert-success rounded-4 border-0 shadow-sm d-flex align-items-center mb-4"><i class="fas fa-check-circle me-2"></i> Merci ! Votre message a été envoyé avec succès.</div>';
    } else {
        $message_status = '<div class="alert alert-danger rounded-4 border-0 shadow-sm d-flex align-items-center mb-4"><i class="fas fa-exclamation-circle me-2"></i> Veuillez remplir tous les champs.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactez-Nous | Nafas</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* --- THEME VARIABLES --- */
        :root {
            /* Brand Colors */
            --brand-blue: #0F766E;
            --brand-teal: #0F766E;
            --brand-red: #E11D48;
            --brand-dark: #0f172a;
            
            /* Light Mode Defaults */
            --bg-body: #f1f5f9; /* Slightly darker than pure white for contrast */
            --bg-card: #ffffff;
            --text-main: #212529;
            --text-muted: #6c757d;
            --nav-bg: rgba(15, 23, 42, 0.95);
            --border-color: rgba(0, 0, 0, 0.08);
            --input-bg: #f8f9fa;
            --input-border: #e2e8f0;
            --shadow-soft: 0 20px 40px rgba(0,0,0,0.08);
        }

        /* Dark Mode Overrides */
        [data-bs-theme="dark"] {
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.08);
            --input-bg: #0f172a;
            --input-border: #334155;
            --shadow-soft: 0 20px 40px rgba(0,0,0,0.3);
        }

        /* Global Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main); 
            line-height: 1.6; 
            transition: background-color 0.3s ease, color 0.3s ease; 
        }

        /* --- NAVIGATION STYLES --- */
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
            background-color: #0d635d;
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
        /* Increased height for the "Floating Card" effect */
        .header-bg {
            background-color: var(--brand-dark);
            height: 400px;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 0;
            border-bottom-left-radius: 50px;
            border-bottom-right-radius: 50px;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            padding-top: 140px;
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }

        .hero-title {
            font-weight: 800;
            letter-spacing: -1px;
        }

        /* --- UNIFIED CONTACT CARD --- */
        .main-wrapper {
            position: relative;
            z-index: 2;
            margin-bottom: 80px;
        }

        .contact-unified-card {
            background-color: var(--bg-card);
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        /* Left Side: Info Panel (Teal Background) */
        .info-panel {
            background: linear-gradient(135deg, var(--brand-teal) 0%, #0d635d 100%);
            color: white;
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        /* Decorative Circles in Info Panel */
        .info-panel::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
        }
        .info-panel::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 2;
        }

        .icon-circle {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.2rem;
            font-size: 1.2rem;
            transition: 0.3s;
        }
        
        .info-item:hover .icon-circle {
            background: white;
            color: var(--brand-teal);
            transform: scale(1.1);
        }

        /* Right Side: Form Panel */
        .form-panel {
            padding: 3rem;
            background-color: var(--bg-card);
        }

        /* Floating Labels & Inputs */
        .form-floating > .form-control {
            border: 1px solid var(--input-border);
            background-color: var(--input-bg);
            border-radius: 12px;
        }
        
        .form-floating > .form-control:focus {
            box-shadow: none;
            border-color: var(--brand-teal);
            background-color: var(--bg-card);
        }

        .form-floating > label {
            color: var(--text-muted);
        }

        .btn-submit {
            background-color: var(--brand-teal);
            border: none;
            padding: 15px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            background-color: #0d635d;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(15, 118, 110, 0.2);
        }

        /* --- FOOTER --- */
        .bg-dark-footer { background-color: var(--brand-dark); }
        .social-btn { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; color: white; text-decoration: none; transition: 0.3s; }
        .social-btn:hover { background: var(--brand-blue); transform: translateY(-3px); }
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
                    <li class="nav-item"><a class="nav-link" href="media.php">Médias</a></li>
                    <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
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

    <div class="header-bg"></div>

    <div class="hero-content">
        <div class="container">
            <h1 class="display-4 hero-title">Entrer en <span style="color: var(--brand-teal); text-shadow: 0 0 15px rgba(0,0,0,0.5);">Contact</span></h1>
            <p class="lead opacity-75">Une question, une idée ou besoin de soutien ?<br>L'équipe Nafas est à votre écoute.</p>
        </div>
    </div>

    <main class="container main-wrapper">
        
        <?php echo $message_status; ?>

        <div class="contact-unified-card">
            <div class="row g-0">
                
                <div class="col-lg-5 info-panel d-flex flex-column justify-content-between">
                    <div>
                        <h3 class="fw-bold mb-4">Nos Coordonnées</h3>
                        <p class="mb-5 opacity-75">Retrouvez-nous à notre bureau ou contactez-nous directement via les canaux suivants.</p>

                        <div class="info-item">
                            <div class="icon-circle"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Notre Bureau</h6>
                                <small class="opacity-75">123 Avenue de l'Avenir, Tunis</small>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="icon-circle"><i class="fas fa-envelope"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Email</h6>
                                <small class="opacity-75">contact@nafas.tn</small>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="icon-circle"><i class="fas fa-phone-alt"></i></div>
                            <div>
                                <h6 class="fw-bold mb-0">Téléphone</h6>
                                <small class="opacity-75">+216 22 188 199</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="fw-bold mb-3">Suivez-nous</h6>
                        <div class="d-flex gap-2">
                            <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-btn"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 form-panel">
                    <h3 class="fw-bold mb-2" style="color: var(--brand-teal);">Envoyez un message</h3>
                    <p class="text-muted mb-4">Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais.</p>

                    <form action="contact.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Votre nom" required>
                                    <label for="name">Nom complet</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                                    <label for="email">Email</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="subject" name="subject" placeholder="Sujet" required>
                                    <label for="subject">Sujet</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea class="form-control" id="message" name="message" placeholder="Message" style="height: 150px" required></textarea>
                                    <label for="message">Votre message</label>
                                </div>
                            </div>
                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary btn-submit w-100 rounded-pill shadow-sm">
                                    Envoyer le message <i class="fas fa-paper-plane ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
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
                        <li class="mb-2"><a href="story_telling.php" class="text-white-50 text-decoration-none">Storytelling</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-white-50 text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5 class="fw-bold mb-3">Suivez-nous</h5>
                    <div class="d-flex gap-2">
                        <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-youtube"></i></a>
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
        // Navbar Scroll Logic
        const navbar = document.getElementById('mainNav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('shadow-sm');
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('shadow-sm');
                navbar.classList.remove('scrolled');
            }
        });

        // Theme Toggle Logic
        const themeBtn = document.getElementById('theme-toggle');
        const icon = themeBtn.querySelector('i');
        
        // Init
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        updateIcon(savedTheme);

        themeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateIcon(newTheme);
        });

        function updateIcon(theme) {
            icon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
        }
    </script>
</body>
</html>