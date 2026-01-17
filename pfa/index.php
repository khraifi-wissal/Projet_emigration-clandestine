<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// On inclut la connexion (qui utilise PDO d'après votre fichier gestion_membres.php)
require_once 'connexion.php'; 

$totalMembres = 0;

try {
    // Requête pour compter les membres
    $queryMembres = $conn->query("SELECT COUNT(*) as total FROM members");
    
    // Correction de l'erreur : Utilisation de fetch() au lieu de fetch_assoc()
    $row = $queryMembres->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $totalMembres = $row['total'];
    }
} catch (PDOException $e) {
    // En cas d'erreur (ex: table inexistante), on garde 0
    $totalMembres = 0;

    

}

$totalopp= 0;

try {
    // Requête pour compter les membres
    $queryopp = $conn->query("SELECT COUNT(*) as total FROM opportunities");
    
    // Correction de l'erreur : Utilisation de fetch() au lieu de fetch_assoc()
    $row = $queryopp->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $totalopp = $row['total'];
    }
} catch (PDOException $e) {
    // En cas d'erreur (ex: table inexistante), on garde 0
    $totalopp = 0;

    

}

$totalrep= 0;

try {
    // Requête pour compter les membres
    $queryrep = $conn->query("SELECT COUNT(*) as total FROM quiz_responses");
    
    // Correction de l'erreur : Utilisation de fetch() au lieu de fetch_assoc()
    $row = $queryrep->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $totalrep = $row['total'];
    }
} catch (PDOException $e) {
    // En cas d'erreur (ex: table inexistante), on garde 0
    $totalrep = 0;

    

}

$totalhist= 0;

try {
    // Requête pour compter les membres
    $queryhist = $conn->query("SELECT COUNT(*) as total FROM storytelling");
    
    // Correction de l'erreur : Utilisation de fetch() au lieu de fetch_assoc()
    $row = $queryhist->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $totalhist = $row['total'];
    }
} catch (PDOException $e) {
    // En cas d'erreur (ex: table inexistante), on garde 0
    $totalhist = 0;

    

}

// --- RÉCUPÉRATION DES RÉCENTS MEMBRES ---
$recents_members = [];
try {
    // On récupère les 5 derniers membres inscrits
    $sql_recents = "SELECT username, email, created_at FROM members ORDER BY created_at DESC LIMIT 5";
    $stmt_recents = $conn->query($sql_recents);
    $recents_members = $stmt_recents->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recents_members = [];
}

// --- RÉCUPÉRATION DES DERNIÈRES OPPORTUNITÉS ---
$derniere_opps = [];
try {
    // On récupère les 4 dernières opportunités ajoutées
    $sql_opps = "SELECT title, category FROM opportunities ORDER BY created_at DESC LIMIT 4";
    $stmt_opps = $conn->query($sql_opps);
    $derniere_opps = $stmt_opps->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $derniere_opps = [];
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nafas Admin Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        /* =========================================
           1. ROOT VARIABLES & RESET
           ========================================= */
        :root {
            --brand-teal: #0F766E;
            --brand-dark: #0f172a;
            --brand-light: #f1f5f9;
            --white: #ffffff;
            --gray: #999;
            --black1: #222;
            --black2: #999;
            
            --card-radius: 20px;
            --shadow: 0 7px 25px rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Ubuntu', sans-serif;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            background: var(--brand-light);
        }

        .container {
            position: relative;
            width: 100%;
        }

        /* =========================================
           2. NAVIGATION (SIDEBAR)
           ========================================= */
        .navigation {
            position: fixed;
            width: 300px;
            height: 100%;
            background: var(--brand-dark);
            border-left: 10px solid var(--brand-dark);
            transition: 0.5s;
            overflow: hidden;
        }

        .navigation.active {
            width: 80px;
        }

        .navigation ul {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
        }

        .navigation ul li {
            position: relative;
            width: 100%;
            list-style: none;
            border-top-left-radius: 30px;
            border-bottom-left-radius: 30px;
        }

        .navigation ul li:hover,
        .navigation ul li.hovered {
            background-color: var(--brand-light);
        }

        .navigation ul li:nth-child(1) {
            margin-bottom: 40px;
            pointer-events: none;
        }

        .navigation ul li a {
            position: relative;
            display: block;
            width: 100%;
            display: flex;
            text-decoration: none;
            color: var(--white);
        }

        .navigation ul li:hover a,
        .navigation ul li.hovered a {
            color: var(--brand-teal);
        }

        .navigation ul li a .icon {
            position: relative;
            display: block;
            min-width: 60px;
            height: 60px;
            line-height: 75px;
            text-align: center;
        }

        .navigation ul li a .icon ion-icon {
            font-size: 1.75rem;
        }

        .navigation ul li a .title {
            position: relative;
            display: block;
            padding: 0 10px;
            height: 60px;
            line-height: 60px;
            text-align: start;
            white-space: nowrap;
        }

        /* Curve Effect */
        .navigation ul li:hover a::before,
        .navigation ul li.hovered a::before {
            content: '';
            position: absolute;
            right: 0;
            top: -50px;
            width: 50px;
            height: 50px;
            background-color: transparent;
            border-radius: 50%;
            box-shadow: 35px 35px 0 10px var(--brand-light);
            pointer-events: none;
        }

        .navigation ul li:hover a::after,
        .navigation ul li.hovered a::after {
            content: '';
            position: absolute;
            right: 0;
            bottom: -50px;
            width: 50px;
            height: 50px;
            background-color: transparent;
            border-radius: 50%;
            box-shadow: 35px -35px 0 10px var(--brand-light);
            pointer-events: none;
        }

        /* =========================================
           3. MAIN CONTENT
           ========================================= */
        .main {
            position: absolute;
            width: calc(100% - 300px);
            left: 300px;
            min-height: 100vh;
            background: var(--brand-light);
            transition: 0.5s;
        }

        .navigation.active ~ .main {
            width: calc(100% - 80px);
            left: 80px;
        }

        /* =========================================
           4. TOPBAR
           ========================================= */
        .topbar {
            width: 100%;
            height: 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 10px;
        }

        .toggle {
            position: relative;
            width: 60px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2.5rem;
            cursor: pointer;
            color: var(--brand-dark);
        }

        .user {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            margin-right: 20px;
            border: 2px solid var(--brand-teal);
        }

        .user img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* =========================================
           5. CARDBOX (STATS)
           ========================================= */
        .cardBox {
            position: relative;
            width: 100%;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            grid-gap: 30px;
        }

        .cardBox .card {
            position: relative;
            background: var(--white);
            padding: 30px;
            border-radius: var(--card-radius);
            display: flex;
            justify-content: space-between;
            cursor: pointer;
            box-shadow: var(--shadow);
            transition: 0.2s ease;
        }

        .cardBox .card:hover {
            background: var(--brand-teal);
        }

        .cardBox .card .numbers {
            position: relative;
            font-weight: 500;
            font-size: 2.5rem;
            color: var(--brand-teal);
        }

        .cardBox .card .cardName {
            color: var(--black2);
            font-size: 1.1rem;
            margin-top: 5px;
        }

        .cardBox .card .iconBx {
            font-size: 3.5rem;
            color: var(--black2);
        }

        .cardBox .card:hover .numbers,
        .cardBox .card:hover .cardName,
        .cardBox .card:hover .iconBx {
            color: var(--white);
        }

        /* =========================================
           6. DETAILS (TABLES)
           ========================================= */
        .details {
            position: relative;
            width: 100%;
            padding: 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            grid-gap: 30px;
        }

        .details .recentOrders {
            position: relative;
            display: grid;
            min-height: 500px;
            background: var(--white);
            padding: 20px;
            box-shadow: var(--shadow);
            border-radius: var(--card-radius);
        }

        .details .cardHeader {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .cardHeader h2 {
            font-weight: 600;
            color: var(--brand-teal);
        }

        .cardHeader .btn {
            position: relative;
            padding: 5px 10px;
            background: var(--brand-teal);
            text-decoration: none;
            color: var(--white);
            border-radius: 6px;
        }

        .details table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .details table thread td {
            font-weight: 600;
        }

        .details .recentOrders table tr {
            color: var(--black1);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .details .recentOrders table tr:last-child {
            border-bottom: none;
        }

        .details .recentOrders table tbody tr:hover {
            background: var(--brand-teal);
            color: var(--white);
        }

        .details .recentOrders table tr td {
            padding: 10px;
        }

        .details .recentOrders table tr td:last-child {
            text-align: end;
        }

        .details .recentOrders table tr td:nth-child(2) {
            text-align: end;
        }

        .details .recentOrders table tr td:nth-child(3) {
            text-align: center;
        }

        /* Status Colors */
        .status.delivered {
            padding: 2px 4px;
            background: #8de02c;
            color: var(--white);
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }

        .status.pending {
            padding: 2px 4px;
            background: #e9b10a;
            color: var(--white);
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }

        .status.return {
            padding: 2px 4px;
            background: #f00;
            color: var(--white);
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }

        /* Recent Customers Table */
        .recentCustomers {
            position: relative;
            display: grid;
            min-height: 500px;
            padding: 20px;
            background: var(--white);
            box-shadow: var(--shadow);
            border-radius: var(--card-radius);
        }

        .recentCustomers table tr td {
            padding: 12px 10px;
        }

        .recentCustomers table tr td h4 {
            font-size: 16px;
            font-weight: 500;
            line-height: 1.2rem;
        }

        .recentCustomers table tr td h4 span {
            font-size: 14px;
            color: var(--black2);
        }

        .recentCustomers table tr:hover {
            background: var(--brand-teal);
            color: var(--white);
        }

        .recentCustomers table tr:hover td h4 span {
            color: var(--white);
        }

        /* =========================================
           7. RESPONSIVE DESIGN
           ========================================= */
        @media (max-width: 991px) {
            .navigation {
                left: -300px;
            }
            .navigation.active {
                width: 300px;
                left: 0;
            }
            .main {
                width: 100%;
                left: 0;
            }
            .main.active {
                left: 300px;
            }
            .cardBox {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .details {
                grid-template-columns: 1fr;
            }
            .recentOrders {
                overflow-x: auto;
            }
            .status.inProgress {
                white-space: nowrap;
            }
        }

        @media (max-width: 480px) {
            .cardBox {
                grid-template-columns: repeat(1, 1fr);
            }
            .cardHeader h2 {
                font-size: 20px;
            }
            .user {
                min-width: 40px;
            }
            .navigation {
                width: 100%;
                left: -100%;
                z-index: 1000;
            }
            .navigation.active {
                width: 100%;
                left: 0;
            }
            .toggle {
                z-index: 10001;
            }
            .main.active .toggle {
                color: #fff;
                position: fixed;
                right: 0;
                left: initial;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="navigation">
            <ul>
                <li><a href="#"><span class="title" style="font-weight: 700; font-size: 1.2rem;">Nafas</span></a></li>
                <li><a href="index.php"><span class="icon"><ion-icon name="home-outline"></ion-icon></span><span class="title">Dashboard</span></a></li>
                <li><a href="gestion_membres.php"><span class="icon"><ion-icon name="people-outline"></ion-icon></span><span class="title">Membres</span></a></li>
                <li><a href="gestion_opportunites.php"><span class="icon"><ion-icon name="briefcase-outline"></ion-icon></span><span class="title">Opportunités</span></a></li>
                <li><a href="gestion_quiz.php"><span class="icon"><ion-icon name="help-circle-outline"></ion-icon></span><span class="title">Quiz</span></a></li>
                <li><a href="gestion_storytelling.php"><span class="icon"><ion-icon name="book-outline"></ion-icon></span><span class="title">Storytelling</span></a></li>
                <li class="hovered"><a href="admin_sensibilisation.php"><span class="icon"><ion-icon name="megaphone-outline"></ion-icon></span><span class="title">Contenus</span></a></li>
                <li><a href="gestion_brochures.php"><span class="icon"><ion-icon name="document-text-outline"></ion-icon></span><span class="title">Brochures</span></a></li>
                <li><a href="admin_login.php"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span><span class="title">Déconnexion</span></a></li>
            </ul>
        </div>

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
                <div class="user">
                    <img src="admin.jpg" alt="Admin"> 
                </div>
            </div>

            <div class="cardBox">
                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $totalMembres; ?></div> 
                        <div class="cardName">Total Membres</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="people-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $totalopp; ?></div> 
                        <div class="cardName">Opportunités</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="briefcase-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $totalrep; ?></div> 
                        <div class="cardName">Réponses Quiz</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="bulb-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers"><?php echo $totalhist; ?></div> 
                        <div class="cardName">Histoires</div>
                    </div>
                    <div class="iconBx">
                        <ion-icon name="chatbubbles-outline"></ion-icon>
                    </div>
                </div>
            </div>

            <div class="details">
                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Récents Membres</h2>
                        <a href="gestion_membres.php" class="btn">Voir Tout</a>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <td>Nom d'utilisateur</td>
                                <td>Email</td>
                                <td>Date d'Inscription</td>
                                <td>Statut</td>
                            </tr>
                        </thead>

                        <tbody>
    <?php if (!empty($recents_members)): ?>
        <?php foreach ($recents_members as $member): ?>
            <tr>
                <td><?php echo htmlspecialchars($member['username']); ?></td>
                <td><?php echo htmlspecialchars($member['email']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($member['created_at'])); ?></td>
                <td><span class="status delivered">Actif</span></td> 
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="4" style="text-align: center;">Aucun membre trouvé.</td>
        </tr>
    <?php endif; ?>
</tbody>
                    </table>
                </div>

                <div class="recentCustomers">
    <div class="cardHeader">
        <h2>Les Opportunités</h2>
        <a href="gestion_opportunites.php" class="btn">Voir Tout</a>
    </div>

    <table>
        <?php if (!empty($derniere_opps)): ?>
            <?php foreach ($derniere_opps as $opp): ?>
                <tr>
                    <td>
                        <h4>
                            <?php echo htmlspecialchars($opp['title']); ?> <br> 
                            <span>Catégorie: <?php echo ucfirst(htmlspecialchars($opp['category'])); ?></span>
                        </h4>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td><h4>Aucune opportunité disponible.</h4></td>
            </tr>
        <?php endif; ?>
    </table>
</div>
            </div>
        </div>
    </div>

    <script>
        // Menu Toggle
        let toggle = document.querySelector('.toggle');
        let navigation = document.querySelector('.navigation');
        let main = document.querySelector('.main');

        toggle.onclick = function () {
            navigation.classList.toggle('active');
            main.classList.toggle('active');
        }

        // Add hovered class to selected list item
        let list = document.querySelectorAll('.navigation li');

        function activeLink() {
            list.forEach((item) => {
                item.classList.remove('hovered');
            });
            this.classList.add('hovered');
        }

        list.forEach((item) => item.addEventListener('mouseover', activeLink));
    </script>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>