<?php
session_start();
require_once "connexion.php"; 

// --- USER CHECK & PROFILE LOGIC ---
// Updated to match quiz.php variable naming ($isLoggedIn instead of $isUser)
$isLoggedIn = isset($_SESSION['member_id']);
$username = $_SESSION['username'] ?? "Membre";
$userImage = "https://cdn-icons-png.flaticon.com/512/149/149071.png"; // Default

if ($isLoggedIn && isset($conn)) {
    try {
        $member_id = $_SESSION['member_id'];
        $stmt = $conn->prepare("SELECT profile_image FROM members WHERE member_id = :id");
        $stmt->execute([':id' => $member_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['profile_image'])) {
            $userImage = $row['profile_image'];
        }
    } catch (Exception $e) {}
}

// --- FILTERING LOGIC ---
$search = "";
$order = "DESC";
$region = "";
$category = "";

if ($isLoggedIn) {
    if (!empty($_GET['search'])) $search = $_GET['search'];
    if (!empty($_GET['order'])) $order = $_GET['order'];
    if (!empty($_GET['region'])) $region = $_GET['region'];
    if (!empty($_GET['category'])) $category = $_GET['category'];

    $sql = "SELECT o.*, 
            (SELECT COUNT(*) FROM opportunity_favorites WHERE opp_id = o.opp_id AND member_id = :uid) as is_fav 
            FROM opportunities o WHERE 1=1";
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (title LIKE :search OR description LIKE :search)";
        $params[':search'] = "%$search%";
    }
    if (!empty($region)) {
        $sql .= " AND region = :region";
        $params[':region'] = $region;
    }
    if (!empty($category)) {
        $allowedCategories = ['formation', 'emploi', 'stage', 'projet'];
        if (in_array($category, $allowedCategories)) {
            $sql .= " AND category = :category";
            $params[':category'] = $category;
        }
    }
    
    $sql .= " ORDER BY opp_id $order";
    $params[':uid'] = $member_id;
    $query = $conn->prepare($sql);
    $query->execute($params);
    
    $regionQuery = $conn->prepare("SELECT DISTINCT region FROM opportunities WHERE region IS NOT NULL AND region != '' ORDER BY region");
    $regionQuery->execute();
    $regions = $regionQuery->fetchAll(PDO::FETCH_COLUMN);
    
} else {
    $query = $conn->prepare("SELECT * FROM opportunities ORDER BY opp_id DESC");
    $query->execute();
    $regions = [];
}

$opps = $query->fetchAll(PDO::FETCH_ASSOC);
$categories = ['formation', 'emploi', 'stage', 'projet'];
$categoryLabels = ['formation' => 'Formation', 'emploi' => 'Emploi', 'stage' => 'Stage', 'projet' => 'Projet'];

// --- HELPER FOR UDEMY STYLE IMAGES ---
function getCategoryThumb($cat) {
    switch($cat) {
        case 'formation': return 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=500&q=80'; 
        case 'emploi': return 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=500&q=80'; 
        case 'stage': return 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=500&q=80'; 
        case 'projet': return 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?w=500&q=80'; 
        default: return 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=500&q=80'; 
    }
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opportunités | Nafas</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
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
            
            /* Dark Mode Defaults (Deep Sea Theme) */
            --bg-body: #0f172a;
            --bg-card: rgba(30, 41, 59, 0.4);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.05);
            --navbar-bg: var(--bg-body); 
            --input-bg: #1e293b; 
        }

        /* Light Mode Overrides */
        [data-bs-theme="light"] {
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: rgba(0, 0, 0, 0.05);
            --input-bg: #ffffff;
            --navbar-bg: var(--bg-body); 
        }

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

        body {
            font-family: var(--font-main);
            background-color: var(--bg-body); 
            color: var(--text-main);
            padding-top: 80px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* --- NAVIGATION STYLES --- */
        .navbar {
            padding: 1rem 2rem;
            transition: all 0.3s ease;
            background-color: var(--brand-dark); 
            border-bottom: 1px solid transparent;
        }

        .navbar.scrolled {
            background-color: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            padding: 0.8rem 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.85) !important;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            margin: 0 5px;
            transition: color 0.3s;
        }
        .nav-link:hover, .nav-link.active { color: var(--brand-teal) !important; }
        
        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.1);
        }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.7)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
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

        /* --- FOOTER STYLES  --- */
        footer {
            background-color: var(--brand-dark) !important;
            color: #f8fafc;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            transition: background-color 0.3s ease, color 0.3s ease;
            margin-top: auto; /* Pushes footer to bottom */
        }
        
        .social-btn {
            color: var(--text-main);
            border: 1px solid var(--border-color);
            transition: all 0.3s;
        }
        .social-btn:hover {
            background-color: var(--brand-teal);
            color: white;
            border-color: var(--brand-teal);
        }

        footer .text-muted, footer p.text-muted { color: #94a3b8 !important; }
        footer .social-btn { 
            color: #f8fafc; 
            border-color: rgba(255, 255, 255, 0.05); 
        }

        /* --- UDEMY STYLE CARDS --- */
        .udemy-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid var(--border-color);
            border-radius: 0; 
            height: 100%;
            background: var(--bg-card);
            display: flex;
            flex-direction: column;
        }
        
        .udemy-card:hover {
            box-shadow: 0 2px 8px 2px rgba(0,0,0,.15);
            cursor: pointer;
        }

        .card-img-wrapper {
            position: relative;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
            overflow: hidden;
            background: #eee;
        }

        .card-img-top {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-body {
            padding: 1rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-title {
            font-weight: 700;
            font-size: 1rem;
            line-height: 1.2;
            margin-bottom: 0.5rem;
            color: var(--text-main);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .instructor-text {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 0.2rem;
        }
        
        .card-text {
             color: var(--text-muted) !important;
        }

        .meta-row {
            margin-top: auto; 
            padding-top: 10px;
        }

        /* --- SIDEBAR & INPUTS --- */
        .filter-sidebar {
            position: sticky;
            top: 100px;
        }
        
        .filter-group {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        
        .filter-header {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: block;
            color: var(--text-main);
        }

        .form-control, .form-select, .input-group-text {
            background-color: var(--input-bg);
            border-color: var(--border-color);
            color: var(--text-main);
        }
        .form-control:focus, .form-select:focus {
            background-color: var(--input-bg);
            color: var(--text-main);
            border-color: var(--brand-teal);
        }
        
        h2.fw-bold, p.text-muted {
            color: var(--text-main) !important;
        }
        p.text-muted {
            color: var(--text-muted) !important;
        }

        .blur-content {
            filter: blur(5px);
            opacity: 0.7;
            pointer-events: none;
            user-select: none;
        }
    </style>
</head>
<body>
    
 <nav class="navbar navbar-expand-lg fixed-top" id="mainNav">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="logo-nafas.png" alt="Logo Nafas" height="45"> 
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item"><a class="nav-link " href="index1.php">À Propos</a></li>
                    <li class="nav-item"><a class="nav-link" href="sensibilisation.php">Sensibilisation</a></li>
                    <li class="nav-item"><a class="nav-link active" href="opportunites.php">Opportunités</a></li>
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

    <div class="container-fluid px-lg-5 py-4">
        
        <div class="row">
            <div class="col-12 mb-4">
                <h2 class="fw-bold">Explorer les opportunités</h2>
                <p class="text-muted">Découvrez des formations, des stages et des emplois pour construire votre avenir en Tunisie.</p>
            </div>
        </div>

        <div class="row g-4">
            
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <?php if ($isLoggedIn): ?>
                    <form method="GET" id="filterForm">
                        
                        <div class="filter-group">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control border-end-0" placeholder="Rechercher..." value="<?= htmlspecialchars($search) ?>">
                                <span class="input-group-text border-start-0"><i class="fas fa-search text-muted"></i></span>
                            </div>
                        </div>

                        <div class="filter-group">
                            <span class="filter-header">Catégorie</span>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" value="" id="catAll" <?= $category == '' ? 'checked' : '' ?> onchange="this.form.submit()">
                                <label class="form-check-label" for="catAll">Toutes</label>
                            </div>
                            <?php foreach ($categories as $cat): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" value="<?= $cat ?>" id="cat<?= $cat ?>" <?= $category == $cat ? 'checked' : '' ?> onchange="this.form.submit()">
                                <label class="form-check-label" for="cat<?= $cat ?>">
                                    <?= $categoryLabels[$cat] ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="filter-group">
                            <span class="filter-header">Région</span>
                            <select name="region" class="form-select" onchange="this.form.submit()">
                                <option value="">Toutes les régions</option>
                                <?php foreach ($regions as $reg): ?>
                                    <option value="<?= htmlspecialchars($reg) ?>" <?= $region == $reg ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($reg) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <span class="filter-header">Trier par</span>
                            <select name="order" class="form-select" onchange="this.form.submit()">
                                <option value="DESC" <?= $order == "DESC" ? "selected" : "" ?>>Plus récents</option>
                                <option value="ASC" <?= $order == "ASC" ? "selected" : "" ?>>Plus anciens</option>
                            </select>
                        </div>
                        
                        <a href="opportunites.php" class="btn btn-outline-secondary w-100 btn-sm">Effacer les filtres</a>
                    </form>
                    <?php else: ?>
                        <div class="card border-0 text-center p-3" style="background-color: var(--input-bg);">
                            <i class="fas fa-lock text-muted mb-2"></i>
                            <h6 class="fw-bold">Filtres verrouillés</h6>
                            <p class="small text-muted">Connectez-vous pour filtrer par région et catégorie.</p>
                            <a href="signup.php" class="btn btn-primary btn-sm w-100">Se connecter</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-9">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold"><?= count($opps) ?> résultat(s)</span>
                </div>

                <?php if (empty($opps)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3 opacity-25"></i>
                        <h4>Aucun résultat</h4>
                        <p class="text-muted">Essayez d'ajuster vos filtres.</p>
                    </div>
                <?php else: ?>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                        <?php foreach ($opps as $o): ?>
                            <div class="col">
                                <div class="udemy-card h-100 position-relative">
                                    
                                    <div class="card-img-wrapper">
                                        <img src="<?= getCategoryThumb($o['category'] ?? 'default') ?>" class="card-img-top" alt="Thumbnail">
                                        <?php if (!empty($o['category'])): ?>
                                            <span class="position-absolute top-0 end-0 m-2 badge bg-dark opacity-75">
                                                <?= $categoryLabels[$o['category']] ?? $o['category'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($o['title']) ?></h5>
                                        
                                        <div class="instructor-text">
                                            <?php if (!empty($o['region'])): ?>
                                                <i class="fas fa-map-marker-alt me-1 text-danger"></i> <?= htmlspecialchars($o['region']) ?>
                                            <?php else: ?>
                                                Nafas Official
                                            <?php endif; ?>
                                        </div>

                                        <p class="card-text mt-2 small <?= !$isLoggedIn ? 'blur-content' : '' ?>">
                                            <?= substr(htmlspecialchars($o['description']), 0, 80) ?>...
                                        </p>

                                        <div class="meta-row d-flex align-items-center justify-content-between">
                                            <div class="text-warning small">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <span class="text-muted ms-1">(Nouveau)</span>
                                            </div>
                                            
                                            <?php if ($isLoggedIn): ?>
                                                <form action="toggle_favorite.php" method="POST" class="d-inline me-2">
                                                    <input type="hidden" name="opp_id" value="<?= $o['opp_id'] ?>">
                                                    <button type="submit" class="btn btn-sm p-0 border-0" title="<?= !empty($o['is_fav']) ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>">
                                                        <i class="<?= !empty($o['is_fav']) ? 'fas text-danger' : 'far' ?> fa-heart fa-lg"></i>
                                                    </button>
                                                </form>

                                                <?php if (!empty($o['link'])): ?>
                                                    <a href="<?= htmlspecialchars($o['link']) ?>" target="_blank" class="fw-bold text-decoration-none text-primary">
                                                        Voir <i class="fas fa-arrow-right"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <i class="fas fa-lock text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!$isLoggedIn): ?>
                                    <a href="signup.php" class="position-absolute top-0 start-0 w-100 h-100 text-decoration-none" style="z-index: 5;"></a>
                                    <?php endif; ?>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <footer class="py-5">
        <div class="container pt-4">
            <div class="row g-4">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <a href="#" class="d-flex align-items-center text-decoration-none mb-3">
                        <img src="logo-nafas.png" alt="Nafas" height="35" class="me-2">
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

        // Theme Toggle Logic
        const themeBtn = document.getElementById('theme-toggle');
        const icon = themeBtn.querySelector('i');
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        icon.className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';

        themeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const html = document.documentElement;
            const newTheme = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            icon.className = newTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
        });
    </script>
</body>
</html>