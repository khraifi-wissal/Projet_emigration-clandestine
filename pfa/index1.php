<?php
/* ===== DEBUG MODE (Remove lines 3-4 when live) ===== */
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Database Connection
$conn = mysqli_connect("127.0.0.1", "root", "", "nafas");

if (!$conn) {
    die("<div style='color:red; padding:20px;'>Erreur de connexion : " . mysqli_connect_error() . "</div>");
}

// Initialize variables
$isLoggedIn = false;
$username = "Membre";
$userImage = "https://cdn-icons-png.flaticon.com/512/149/149071.png"; // Default generic avatar

// Check if user is logged in
if (isset($_SESSION["member_id"])) {
    $isLoggedIn = true;
    $member_id = $_SESSION['member_id'];
    $username = $_SESSION['username'] ?? "Membre";

    // Optional: Fetch specific profile image from DB if it exists
    if ($conn) {
        $query = "SELECT * FROM members WHERE member_id = '$member_id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if (!empty($row['profile_image'])) {
                $userImage = $row['profile_image'];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nafas | Le Souffle qui Inspire l'Avenir</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --bs-font-sans-serif: 'Poppins', sans-serif;
            --brand-teal: #0F766E;
            --brand-red: #E11D48;
            --brand-dark: #0f172a;
        }

        /* Override Bootstrap Colors */
        .text-primary { color: var(--brand-teal) !important; }
        .bg-primary { background-color: var(--brand-teal) !important; }
        .btn-primary { 
            background-color: var(--brand-teal); 
            border-color: var(--brand-teal); 
        }
        .btn-primary:hover { 
            background-color: #0d635d; 
            border-color: #0d635d; 
        }
        .text-danger { color: var(--brand-red) !important; }
        .btn-danger { background-color: var(--brand-red); border-color: var(--brand-red); }

        body {
            font-family: var(--bs-font-sans-serif);
            overflow-x: hidden;
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

        /* --- HERO SECTION --- */
        .hero-section {
            position: relative;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            color: white;
        }

        #background-video {
            position: absolute;
            top: 50%; left: 50%;
            min-width: 100%; min-height: 100%;
            width: auto; height: auto;
            transform: translateX(-50%) translateY(-50%);
            z-index: -1;
            filter: brightness(0.4) saturate(1.1);
        }

        /* --- CARDS & GLASSMORPHISM --- */
        .card {
            border: none;
            border-radius: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        [data-bs-theme="dark"] .glass-card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* --- IMAGE GRIDS --- */
        .img-cover-card {
            height: 300px;
            object-fit: cover;
            width: 100%;
        }

        /* --- ANIMATIONS --- */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out;
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="logo-nafas.png" alt="Logo Nafas" height="45"> 
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="index1.php">À Propos</a></li>
                    <li class="nav-item"><a class="nav-link" href="sensibilisation.php">Sensibilisation</a></li>
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
        <video autoplay muted loop playsinline id="background-video">
            <source src="vid.mp4" type="video/mp4">
        </video>
        <div class="container text-center position-relative z-2">
            
            <h1 class="display-3 fw-bold mb-3">
                Le souffle qui inspire <br>
                <span id="dynamic-word" class="text-primary fw-bold" style="background: -webkit-linear-gradient(45deg, #0F766E, #2dd4bf); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">l'Avenir</span>
            </h1>
            <p class="lead text-light mb-5 mx-auto" style="max-width: 700px; opacity: 0.9;">
                Construire notre force, ici. Des alternatives pour la jeunesse, des opportunités pour la Tunisie.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <a href="sensibilisation.php" class="btn btn-light rounded-4 px-4 py-3 fw-bold">Réalités et conseils</a>
                <a href="opportunites.php" class="btn btn-outline-light rounded-4 px-4 py-3 fw-bold backdrop-blur">Opportunités</a>
            </div>
        </div>
    </header>

    <section class="container" style="margin-top: -80px; position: relative; z-index: 10;">
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden reveal">
            <div class="card-body p-0">
                <div class="row g-0 text-center">
                    <div class="col-md-4 p-5 border-end border-light-subtle">
                        <h2 class="display-5 fw-bold text-primary mb-0 counter" data-target="240">0</h2>
                        <small class="text-uppercase text-muted fw-bold">Jeunesse Engagée</small>
                    </div>
                    <div class="col-md-4 p-5 border-end border-light-subtle">
                        <h2 class="display-5 fw-bold text-danger mb-0 counter" data-target="15">0</h2>
                        <small class="text-uppercase text-muted fw-bold">Programmes Locaux</small>
                    </div>
                    <div class="col-md-4 p-5">
                        <h2 class="display-5 fw-bold text-primary mb-0 counter" data-target="2">0</h2>
                        <small class="text-uppercase text-muted fw-bold">Ans d'Expérience</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="sensibilisation" class="py-5 mt-5">
        <div class="container py-5">
            <div class="text-center mb-5 reveal">
                <h2 class="fw-bold display-6">Face à la Réalité</h2>
                <div class="bg-danger mx-auto mt-3" style="width: 80px; height: 4px; border-radius: 2px;"></div>
                <p class="text-muted mt-3 fs-5 mx-auto" style="max-width: 700px;">
                    Comprendre les risques n'est pas dissuader, c'est informer. Voici des images qui témoignent de la dure réalité.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-4 reveal">
                    <div class="card bg-dark text-white overflow-hidden h-100 border-0">
                        <img src="i6.jpg" class="card-img img-cover-card opacity-75" alt="Danger mer">
                        <div class="card-img-overlay d-flex flex-column justify-content-end p-4">
                            <h5 class="card-title fw-bold">Précarité en mer</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <div class="card bg-dark text-white overflow-hidden h-100 border-0">
                        <img src="im1.jpg" class="card-img img-cover-card opacity-75" alt="Entassement">
                        <div class="card-img-overlay d-flex flex-column justify-content-end p-4">
                            <h5 class="card-title fw-bold">Conditions Inhumaines</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <div class="card bg-dark text-white overflow-hidden h-100 border-0">
                        <img src="im6.jpeg" class="card-img img-cover-card opacity-75" alt="Désillusion">
                        <div class="card-img-overlay d-flex flex-column justify-content-end p-4">
                            <h5 class="card-title fw-bold">Danger Imminent</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <div class="card bg-dark text-white overflow-hidden h-100 border-0">
                        <img src="im14.jpeg" class="card-img img-cover-card opacity-75" alt="Arrivée">
                        <div class="card-img-overlay d-flex flex-column justify-content-end p-4">
                            <h5 class="card-title fw-bold">Désillusion à l'arrivée</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <div class="card bg-dark text-white overflow-hidden h-100 border-0">
                        <img src="im5.jpeg" class="card-img img-cover-card opacity-75" alt="Exploitation">
                        <div class="card-img-overlay d-flex flex-column justify-content-end p-4">
                            <h5 class="card-title fw-bold">Exploitation</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 reveal">
                    <div class="card bg-dark text-white overflow-hidden h-100 border-0">
                        <img src="i5.jpeg" class="card-img img-cover-card opacity-75" alt="Conséquences">
                        <div class="card-img-overlay d-flex flex-column justify-content-end p-4">
                            <h5 class="card-title fw-bold">Conséquences</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="opportunities" class="py-5 bg-body-tertiary position-relative overflow-hidden">
        <div class="position-absolute top-0 end-0 bg-primary opacity-10 rounded-circle" style="width: 400px; height: 400px; filter: blur(80px); transform: translate(30%, -30%);"></div>

        <div class="container py-5 position-relative z-1">
            <div class="text-center mb-5 reveal">
                <h2 class="fw-bold display-6">Alternatives Concrètes</h2>
                <p class="text-muted fs-5">Il existe des chemins viables vers le succès en Tunisie.</p>
            </div>

            <div class="row g-4 justify-content-center">
                <div class="col-lg-5 reveal">
                    <div class="card glass-card h-100 p-4 border-primary border-opacity-25">
                        <div class="card-body text-start">
                            <div class="d-inline-block mb-4">
                                <img src="formation.jpg" width="450" height="200" alt="Formation">
                            </div>
                            <h3 class="fw-bold mb-3">Pôle Formation</h3>
                            <ul class="list-unstyled text-muted mb-4">
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Apprentissage des métiers</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Certifications reconnues</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Stages en entreprises</li>
                            </ul>
                            <a href="opportunites.php" class="btn btn-primary w-100 py-2 rounded-3 fw-bold text-white">Trouver ma Formation</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5 reveal">
                    <div class="card glass-card h-100 p-4">
                        <div class="card-body text-start">
                            <div class="d-inline-block mb-4">
                                <img src="social.jpeg" width="450" height="200" alt="Entrepreneur">
                            </div>
                            <h3 class="fw-bold mb-3">l'Impact Social</h3>
                            <ul class="list-unstyled text-muted mb-4">
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Investissement local</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Mythes et réalités</li>
                                <li class="mb-2"><i class="fas fa-check text-primary me-2"></i> Évaluez votre profil</li>
                            </ul>
                            <a href="quiz.php" class="btn btn-outline-dark w-100 py-2 rounded-3 fw-bold">Commencer le Quiz</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="py-5">
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 reveal">
                    <h2 class="display-5 fw-bold mb-4">Contactez-Nous</h2>
                    <p class="text-muted fs-5 mb-5">
                        L'équipe Nafas est disponible pour vous et pour votre avenir. Écrivez-nous pour toute question ou soutien.
                    </p>

                    <div class="d-flex align-items-center p-3 border rounded-4 mb-3 shadow-sm bg-body">
                        <div class="p-3 me-3">
                            <img src="circle (1).png" width="45" height="45" alt="Location">
                        </div>
                        <div>
                            <small class="text-uppercase text-muted fw-bold">Siège Principal</small>
                            <h5 class="fw-bold mb-0">Tunis, Avenue de la Création #0123</h5>
                        </div>
                    </div>

                    <div class="d-flex align-items-center p-3 border rounded-4 shadow-sm bg-body">
                        <div class="p-3 me-3">
                            <img src="telephone-call.png" width="45" height="45" alt="Phone">
                        </div>
                        <div>
                            <small class="text-uppercase text-muted fw-bold">Téléphone</small>
                            <h5 class="fw-bold mb-0">+216 22 188 199</h5>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 reveal">
                    <div class="ratio ratio-4x3 rounded-4 overflow-hidden shadow">
                        <img src="image.png" alt="Carte" style="object-fit: cover; width: 100%; height: 100%;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-5">
        <div class="container pt-4">
            <div class="row g-4">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none mb-3">
                        <img src="logo-nafas.png" alt="Nafas" height="35" class="me-2" >
                    </a>
                    <p class="text-white-50">
                        Une plateforme dédiée à la jeunesse tunisienne, offrant des alternatives concrètes et un espace d'expression pour construire un avenir meilleur, ici.
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
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle social-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle social-btn"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle social-btn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle social-btn"><i class="fab fa-youtube"></i></a>
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

        // --- Dynamic Word Animation ---
        const words = ["l'Avenir", "l'Espoir", "l'Inspiration", "l'Audace"];
        let wordIndex = 0;
        const wordElement = document.getElementById('dynamic-word');

        setInterval(() => {
            wordElement.style.opacity = '0';
            wordElement.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                wordIndex = (wordIndex + 1) % words.length;
                wordElement.textContent = words[wordIndex];
                wordElement.style.opacity = '1';
            }, 500);
        }, 3000);

        // --- Scroll Reveal ---
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // --- Number Counter ---
        let hasCounted = false;
        const statsSection = document.querySelector('.card.reveal'); // Target the stats card
        
        const countObserver = new IntersectionObserver((entries) => {
            if(entries[0].isIntersecting && !hasCounted) {
                document.querySelectorAll('.counter').forEach(counter => {
                    const target = +counter.getAttribute('data-target');
                    const duration = 2000;
                    const step = target / (duration / 16); 
                    
                    let current = 0;
                    const updateCount = () => {
                        current += step;
                        if(current < target) {
                            counter.innerText = Math.ceil(current) + (counter.nextElementSibling.innerText.includes('Ans') ? '' : '+');
                            requestAnimationFrame(updateCount);
                        } else {
                            counter.innerText = target + (counter.nextElementSibling.innerText.includes('Ans') ? '' : '+');
                        }
                    };
                    updateCount();
                });
                hasCounted = true;
            }
        });
        
        if(statsSection) countObserver.observe(statsSection);

        // --- Theme Toggle (Bootstrap 5.3) ---
        const themeBtn = document.getElementById('theme-toggle');
        const icon = themeBtn.querySelector('i');
        
        // Check saved theme
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