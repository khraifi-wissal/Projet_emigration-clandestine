<?php
// Start session (if needed for future features)
session_start();

// Include database connection
require_once "../connexion.php";

// Fetch sensibilisation data from database using PDO
$sql = "SELECT * FROM sensibilisation ORDER BY date_publication DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();

// Fetch all results as associative array
$sensibilisation_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensibilisation - Le Souffle qui Inspire l'Avenir</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style3.css"> <!-- Updated path to match your structure -->
    <style>
        /* Additional specific styles for this page */
        .sensibilisation-hero {
            position: relative;
            height: 60vh;
            background: linear-gradient(rgba(0, 68, 106, 0.9), rgba(0, 68, 106, 0.7)), 
                        url('uploads/sensibilisation-bg.jpg') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-top: 70px; /* Account for fixed navbar */
        }
        
        .sensibilisation-hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
            font-weight: 800;
        }
        
        .sensibilisation-hero-content p {
            font-size: 1.3rem;
            max-width: 800px;
            margin: 0 auto 30px;
        }
        
        .sensibilisation-hero-content .main-cta {
            background-color: white;
            color: #1C79B4;
            border: none;
        }
        
        .sensibilisation-hero-content .main-cta:hover {
            background-color: #f4f4f9;
        }
        
        .sensibilisation-container {
            max-width: 1200px;
            margin: 80px auto;
            padding: 0 5%;
        }
        
        .sensibilisation-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            margin-bottom: 100px;
            padding: 40px;
            border-radius: 15px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
        
        .sensibilisation-content {
            padding-right: 20px;
        }
        
        .sensibilisation-content h2 {
            font-size: 2.5rem;
            color: var(--color-dark-bg);
            margin-bottom: 25px;
            font-weight: 800;
            line-height: 1.3;
        }
        
        .sensibilisation-content .content-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--color-text-dark);
            margin-bottom: 25px;
            white-space: pre-line;
        }
        
        .sensibilisation-image {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            height: 400px;
            position: relative;
        }
        
        .sensibilisation-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }
        
        .sensibilisation-image:hover img {
            transform: scale(1.05);
        }
        
        .sensibilisation-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 30px;
            font-size: 0.9rem;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .meta-icon {
            font-size: 1.2rem;
            color: var(--color-primary);
        }
        
        .sensibilisation-date {
            background: var(--color-primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .sensibilisation-author {
            font-weight: 600;
            color: var(--color-dark-bg);
        }
        
        /* Responsive design */
        @media (max-width: 992px) {
            .sensibilisation-grid {
                grid-template-columns: 1fr;
                gap: 40px;
                padding: 30px;
            }
            
            .sensibilisation-content {
                padding-right: 0;
            }
            
            .sensibilisation-hero-content h1 {
                font-size: 2.8rem;
            }
            
            .sensibilisation-content h2 {
                font-size: 2.2rem;
            }
        }
        
        @media (max-width: 768px) {
            .sensibilisation-hero {
                height: 50vh;
                margin-top: 60px;
            }
            
            .sensibilisation-hero-content h1 {
                font-size: 2.3rem;
            }
            
            .sensibilisation-hero-content p {
                font-size: 1.1rem;
            }
            
            .sensibilisation-container {
                margin: 60px auto;
            }
            
            .sensibilisation-grid {
                padding: 20px;
                margin-bottom: 60px;
            }
            
            .sensibilisation-image {
                height: 300px;
            }
        }
        
        /* Add reveal animation */
        .sensibilisation-grid {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.9s ease;
        }
        
        .sensibilisation-grid.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-state h3 {
            color: var(--color-dark-bg);
            margin-bottom: 20px;
        }
        
        .empty-state p {
            color: #666;
            max-width: 600px;
            margin: 0 auto 30px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
            <div class="logo"><img src="Asset 1.png" alt="Logo Nafas"></div>
            <ul class="nav-links">
                <li><a href="index.php">À Propos</a></li>
                <li><a href="sensibilisation.php">Sensibilisation</a></li>
                <li><a href="nos-opportunites.php">Opportunités</a></li>
                <li><a href="quiz.php">Quiz</a></li>
                <li><a href="storytelling.php">Storytelling</a></li>
                <li><a href="medias.php">Médias</a></li>
                <li><a href="contact.php" class="active">Contact</a></li>
            </ul>
            <a href="engagement.php" class="cta-button">Je m'engage</a>
        </nav>
        

    <!-- Hero Section -->
    <section class="sensibilisation-hero">
        <div class="sensibilisation-hero-content">
            <h1>Sensibilisation & Prévention</h1>
            <p>Comprendre les réalités pour faire des choix éclairés. Informations, témoignages et ressources sur les risques de l'émigration clandestine.</p>
            <a href="#sensibilisation-content" class="main-cta">Découvrir les articles</a>
        </div>
    </section>

    <!-- Main Content -->
    <div class="sensibilisation-container" id="sensibilisation-content">
        <?php if (!empty($sensibilisation_data)): ?>
            <?php foreach($sensibilisation_data as $row): ?>
            <div class="sensibilisation-grid reveal">
                <div class="sensibilisation-content">
                    <h2><?php echo htmlspecialchars($row['titre']); ?></h2>
                    <div class="content-text">
                        <?php echo nl2br(htmlspecialchars($row['description'])); ?>
                    </div>
                    
                    <div class="sensibilisation-meta">
                        <div class="meta-item">
                            <span class="meta-icon">📅</span>
                            <span class="sensibilisation-date">
                                Publié le: <?php echo date('d/m/Y', strtotime($row['date_publication'])); ?>
                            </span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-icon">👤</span>
                            <span class="sensibilisation-author">
                                Auteur: Admin #<?php echo $row['created_by']; ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="sensibilisation-image">
                    <?php if(file_exists($row['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($row['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($row['titre']); ?>">
                    <?php else: ?>
                        <img src="uploads/default-sensibilisation.jpg" 
                             alt="Image par défaut">
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="sensibilisation-grid empty-state">
                <div class="sensibilisation-content">
                    <h3>Aucun contenu disponible pour le moment</h3>
                    <p>Les articles de sensibilisation sont en cours de préparation. Revenez bientôt pour découvrir du contenu informatif sur les risques de l'émigration clandestine et les alternatives en Tunisie.</p>
                    <a href="index.php" class="card-cta">Retour à l'accueil</a>
                </div>
                <div class="sensibilisation-image">
                    <img src="uploads/coming-soon.jpg" alt="À venir">
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Additional informational section -->
        <div class="sensibilisation-grid reveal">
            <div class="sensibilisation-content">
                <h2>Notre Mission de Sensibilisation</h2>
                <div class="content-text">
                    La méconnaissance des risques réels de la traversée clandestine de la Méditerranée est l'un des principaux facteurs qui poussent de nombreux jeunes à tenter l'aventure. Notre objectif est de fournir une information transparente, basée sur des faits et des témoignages réels.
                    
                    Chaque année, des milliers de vies sont perdues en mer. La sensibilisation n'est pas destinée à décourager les rêves, mais à éclairer les décisions avec des informations précises sur:
                    
                    • Les dangers concrets de la traversée
                    • Les alternatives locales viables
                    • Les ressources disponibles en Tunisie
                    • Les programmes de formation et d'emploi
                    
                    Ensemble, construisons un avenir meilleur ici, en Tunisie.
                </div>
                <a href="contact.php" class="card-cta">Nous contacter pour plus d'info</a>
            </div>
            <div class="sensibilisation-image">
                <img src="uploads/mission-sensibilisation.jpg" alt="Notre mission de sensibilisation">
            </div>
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

    <script>
        // Navbar scroll effect
        const navbar = document.querySelector('.navbar');
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('nav-scrolled');
            } else {
                navbar.classList.remove('nav-scrolled');
            }
        });
        
        // Scroll reveal effect
        const elements = document.querySelectorAll('.sensibilisation-grid');
        
        const revealOptions = {
            threshold: 0.15,
            rootMargin: "0px 0px -50px 0px"
        };
        
        const revealOnScroll = new IntersectionObserver(function (entries, revealOnScroll) {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add("visible");
                revealOnScroll.unobserve(entry.target);
            });
        }, revealOptions);
        
        elements.forEach(el => {
            revealOnScroll.observe(el);
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>