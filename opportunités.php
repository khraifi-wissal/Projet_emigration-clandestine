<?php
session_start();
require_once "connexion.php";

// Check if a member is logged in
$isUser = isset($_SESSION['member_id']);

// Initialize filters
$search = "";
$order = "DESC";
$region = "";
$category = "";

// Get filter values from GET parameters (only if user is logged in)
if ($isUser) {
    if (!empty($_GET['search'])) $search = $_GET['search'];
    if (!empty($_GET['order'])) $order = $_GET['order'];
    if (!empty($_GET['region'])) $region = $_GET['region'];
    if (!empty($_GET['category'])) $category = $_GET['category'];
}

// Build query based on user status and filters
if ($isUser) {
    // Base query with filters
    $sql = "SELECT * FROM opportunities WHERE 1=1";
    $params = [];
    
    // Add search filter
    if (!empty($search)) {
        $sql .= " AND (title LIKE :search OR description LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    // Add region filter
    if (!empty($region)) {
        $sql .= " AND region = :region";
        $params[':region'] = $region;
    }
    
    // Add category filter (validate against enum values)
    if (!empty($category)) {
        $allowedCategories = ['formation', 'emploi', 'stage', 'projet'];
        if (in_array($category, $allowedCategories)) {
            $sql .= " AND category = :category";
            $params[':category'] = $category;
        }
    }
    
    // Add sorting
    $sql .= " ORDER BY opp_id $order";
    
    $query = $conn->prepare($sql);
    $query->execute($params);
    
    // Get unique regions from database
    $regionQuery = $conn->prepare("SELECT DISTINCT region FROM opportunities WHERE region IS NOT NULL AND region != '' ORDER BY region");
    $regionQuery->execute();
    $regions = $regionQuery->fetchAll(PDO::FETCH_COLUMN);
    
    // Category values from enum definition
    $categories = ['formation', 'emploi', 'stage', 'projet'];
    
} else {
    // Public view (no filtering)
    $query = $conn->prepare("SELECT * FROM opportunities ORDER BY opp_id DESC");
    $query->execute();
    $regions = [];
    $categories = ['formation', 'emploi', 'stage', 'projet'];
}

$opps = $query->fetchAll(PDO::FETCH_ASSOC);

// Map category values to French labels
$categoryLabels = [
    'formation' => 'Formation',
    'emploi' => 'Emploi', 
    'stage' => 'Stage',
    'projet' => 'Projet'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="style3.css">
<title>Nos Opportunités - Nafas</title>
<style>
    /* Main Content Styles */
    .main-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }
    
    .page-container {
        display: flex;
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
        gap: 30px;
    }
    
    .sidebar {
        width: 280px;
        flex-shrink: 0;
    }
    
    .main-content {
        flex: 1;
    }
    
    .filters-sidebar {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 20px;
        border: 1px solid #e0e0e0;
    }
    
    .filter-group {
        margin-bottom: 25px;
    }
    
    .filter-group h3 {
        margin-top: 0;
        margin-bottom: 15px;
        color: #2c3e50;
        font-size: 1.1rem;
        border-bottom: 2px solid #3498db;
        padding-bottom: 8px;
    }
    
    .filter-input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    
    .filter-input:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }
    
    .filter-select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        background: white;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }
    
    .filter-select:focus {
        outline: none;
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }
    
    .filter-btn {
        width: 100%;
        padding: 14px;
        background: #3498db;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .filter-btn:hover {
        background: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    }
    
    .reset-btn {
        width: 100%;
        padding: 12px;
        background: #95a5a6;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        cursor: pointer;
        margin-top: 10px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .reset-btn:hover {
        background: #7f8c8d;
        transform: translateY(-1px);
    }
    
    .results-count {
        color: #7f8c8d;
        margin-bottom: 20px;
        font-style: italic;
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .main-content h1 {
        color: #2c3e50;
        margin-top: 0;
        margin-bottom: 30px;
        font-size: 2.2rem;
        padding-bottom: 15px;
        border-bottom: 3px solid #3498db;
    }
    
    .opp-container {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }
    
    .opp-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border-left: 5px solid #3498db;
    }
    
    .opp-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    
    .opp-card h2 {
        margin-top: 0;
        color: #2c3e50;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }
    
    .opp-card p {
        color: #34495e;
        line-height: 1.7;
        margin-bottom: 20px;
    }
    
    .opp-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #7f8c8d;
        font-size: 0.9rem;
        background: #f8f9fa;
        padding: 6px 12px;
        border-radius: 20px;
    }
    
    .meta-item i {
        color: #3498db;
        font-size: 0.8rem;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        background: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 0.95rem;
    }
    
    .btn:hover {
        background: #2980b9;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
    }
    
    .btn.disabled {
        background: #95a5a6;
        cursor: not-allowed;
    }
    
    .btn.disabled:hover {
        background: #95a5a6;
        transform: none;
        box-shadow: none;
    }
    
    .blur-text {
        color: #7f8c8d;
        font-style: italic;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        text-align: center;
        border: 1px dashed #ddd;
    }
    
    .connect-container {
        text-align: center;
        margin-top: 50px;
        padding: 40px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }
    
    .btn.connect {
        display: inline-flex;
        width: auto;
        margin: 0;
        text-align: center;
        font-size: 1.1rem;
        padding: 15px 40px;
    }
    
    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 60px 30px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }
    
    .empty-state h2 {
        color: #2c3e50;
        margin-bottom: 15px;
    }
    
    .empty-state p {
        color: #7f8c8d;
        max-width: 500px;
        margin: 0 auto 30px;
    }
    

</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo"><img src="Asset 1.png" alt="Logo Nafas"></div>
        <ul class="nav-links">
            <li><a href="index.php">À Propos</a></li>
            <li><a href="sensibilisation.php">Sensibilisation</a></li>
            <li><a href="nos-opportunites.php" class="active">Opportunités</a></li>
            <li><a href="quiz.php">Quiz</a></li>
            <li><a href="storytelling.php">Storytelling</a></li>
            <li><a href="medias.php">Médias</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
        <a href="engagement.php" class="cta-button">Je m'engage</a>
    </nav>
    
    <div class="main-wrapper">
        <div class="page-container">
            
            <?php if ($isUser): ?>
            <!-- Sidebar with filters -->
            <div class="sidebar">
                <div class="filters-sidebar">
                    <form method="GET" id="filterForm">
                        <div class="filter-group">
                            <h3><i class="fas fa-search"></i> Recherche</h3>
                            <input type="text" 
                                   name="search" 
                                   placeholder="Mot-clé, titre..." 
                                   value="<?= htmlspecialchars($search) ?>"
                                   class="filter-input">
                        </div>
                        
                        <div class="filter-group">
                            <h3><i class="fas fa-filter"></i> Filtres</h3>
                            
                            <label for="region">Région:</label>
                            <select name="region" id="region" class="filter-select">
                                <option value="">Toutes les régions</option>
                                <?php foreach ($regions as $reg): ?>
                                    <option value="<?= htmlspecialchars($reg) ?>" 
                                        <?= $region == $reg ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($reg) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <label for="category">Catégorie:</label>
                            <select name="category" id="category" class="filter-select">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat) ?>" 
                                        <?= $category == $cat ? 'selected' : '' ?>>
                                        <?= $categoryLabels[$cat] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <label for="order">Trier par:</label>
                            <select name="order" id="order" class="filter-select">
                                <option value="DESC" <?= $order == "DESC" ? "selected" : "" ?>>Plus récentes</option>
                                <option value="ASC" <?= $order == "ASC" ? "selected" : "" ?>>Plus anciennes</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="filter-btn">
                            <i class="fas fa-filter"></i> Appliquer les filtres
                        </button>
                        
                        <button type="button" onclick="resetFilters()" class="reset-btn">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </button>
                    </form>
                </div>
                
                <div class="results-count">
                    <i class="fas fa-chart-bar"></i>
                    <?= count($opps) ?> opportunité<?= count($opps) > 1 ? 's' : '' ?> trouvée<?= count($opps) > 1 ? 's' : '' ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Main content -->
            <div class="main-content">
                <h1>Nos Opportunités</h1>
                
                <div class="opp-container">
                <?php if (empty($opps)): ?>
                    <div class="empty-state">
                        <h2>Aucune opportunité trouvée</h2>
                        <p>Aucune opportunité ne correspond à vos critères de recherche. Essayez d'autres filtres ou élargissez votre recherche.</p>
                        <?php if ($isUser): ?>
                            <button onclick="resetFilters()" class="btn">
                                <i class="fas fa-redo"></i> Réinitialiser les filtres
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($opps as $o): ?>
                        <div class="opp-card">
                            <h2><?= htmlspecialchars($o['title']) ?></h2>
                            
                            <div class="opp-meta">
                                <?php if (!empty($o['region'])): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?= htmlspecialchars($o['region']) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($o['category'])): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-tag"></i>
                                        <span><?= $categoryLabels[$o['category']] ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($o['created_at'])): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?= date('d/m/Y', strtotime($o['created_at'])) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($isUser): ?>
                                <p><?= nl2br(htmlspecialchars($o['description'])) ?></p>
                                <?php if (!empty($o['link'])): ?>
                                    <a class="btn" href="<?= htmlspecialchars($o['link']) ?>" target="_blank">
                                        <i class="fas fa-external-link-alt"></i> Voir l'opportunité
                                    </a>
                                <?php endif; ?>
                                
                            <?php else: ?>
                                <p class="blur-text">Connectez-vous pour voir la description complète et accéder au lien</p>
                                <a class="btn disabled">
                                    <i class="fas fa-lock"></i> Connexion requise
                                </a>
                            <?php endif; ?>
                            
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
                
                <?php if (!$isUser): ?>
                    <div class="connect-container">
                        <h3 style="color: #2c3e50; margin-bottom: 15px;">Accédez à toutes les opportunités</h3>
                        <p style="color: #7f8c8d; margin-bottom: 25px;">Connectez-vous pour voir les descriptions complètes et accéder aux liens des opportunités.</p>
                        <a class="btn connect" href="admin_login.php">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">
                <img src="log.png" alt="Logo">
            </div>
            <div class="footer-links">
                <a href="#">Home</a>
                <a href="#">Contact</a>
                <a href="#">Media</a>
            </div>
            <div class="social-icons">
                <a href="#"><img src="" alt=""></a> 
                <a href="#">i</a> 
            </div>
        </div>
        <div class="copyright">
            © 2025 All Rights Reserved By Nafas
        </div>
    </footer>
    </div>

    <script>
        function resetFilters() {
            // Reset form fields
            document.querySelectorAll('#filterForm input, #filterForm select').forEach(element => {
                if (element.tagName === 'INPUT') {
                    element.value = '';
                } else if (element.tagName === 'SELECT') {
                    element.selectedIndex = 0;
                }
            });
            
            // Submit the form to reload with default values
            document.getElementById('filterForm').submit();
        }
        
        // Add active class to current page link
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.nav-links a');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPage) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>