<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nafas Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
</head>

<body>
    <div class="container">
        <div class="navigation">
            <ul>
                <li>
                    <a href="#">
        
                        <span class="title">Nafas</span>
                    </a>
                </li>

                <li class="hovered">
                    <a href="#">
                        <span class="icon">
                            <ion-icon name="home-outline"></ion-icon>
                        </span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>
                
                <li>
                    <a href="gestion_membres.php">
                        <span class="icon">
                            <ion-icon name="people-outline"></ion-icon>
                        </span>
                        <span class="title">Membres</span>
                    </a>
                </li>

                <li>
                    <a href="gestion_opportunites.php">
                        <span class="icon">
                            <ion-icon name="briefcase-outline"></ion-icon>
                        </span>
                        <span class="title">Opportunités</span>
                    </a>
                </li>

                <li>
                    <a href="gerer_quiz_complet.php">
                        <span class="icon">
                            <ion-icon name="help-circle-outline"></ion-icon>
                        </span>
                        <span class="title">Quiz</span>
                    </a>
                </li>
                
                <li>
                    <a href="gestion_storytelling.php">
                        <span class="icon">
                            <ion-icon name="book-outline"></ion-icon>
                        </span>
                        <span class="title">Storytelling</span>
                    </a>
                </li>

                 <li>
                    <a href="">
                        <span class="icon">
                            <ion-icon name="document-text-outline"></ion-icon>
                        </span>
                        <span class="title">contenus</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <span class="icon">
                            <ion-icon name="document-text-outline"></ion-icon>
                        </span>
                        <span class="title">Brochures</span>
                    </a>
                </li>

                <li>
    <a href="logout.php">
        <span class="icon">
            <ion-icon name="log-out-outline"></ion-icon>
        </span>
        <span class="title">Déconnexion</span>
    </a>
</li>

            </ul>
        </div>

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
                <div class="user">
                    <img src="image.png" alt=""> 
                </div>
            </div>

            <div class="cardBox">
                
                <div class="card">
                    <div>
                        <div class="numbers">0</div> <div class="cardName">Total Membres </div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="people-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers">0</div> <div class="cardName">Opportunités Publiées</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="briefcase-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers">0</div> <div class="cardName">Réponses Quiz Soumises</div>
                    </div>

                    <div class="iconBx">
                        <ion-icon name="bulb-outline"></ion-icon>
                    </div>
                </div>

                <div class="card">
                    <div>
                        <div class="numbers">0</div> <div class="cardName">Histoires Publiées</div>
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
                        <a href="#" class="btn">Voir Tout</a>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <td>Nom d'utilisateur</td>
                                <td>Email</td>
                                <td>Date d'Inscription</td>
                                <td>Statut (Actif)</td>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td> 
                            </tr>

                            <tr>
                                <td>Fatima Ali</td>
                                <td>f.ali@mail.com</td>
                                <td>2025-11-28</td>
                                <td><span class="status pending">Inactif (depuis 30j)</span></td>
                            </tr>

                            <tr>
                                <td>Mehdi B.</td>
                                <td>mehdi.b@mail.com</td>
                                <td>2025-11-20</td>
                                <td><span class="status delivered">Actif</span></td>
                            </tr>
                            
                            <tr>
                                <td>Samira</td>
                                <td>samira@mail.com</td>
                                <td>2025-11-15</td>
                                <td><span class="status return">Bloqué</span></td>
                            </tr>
                            </tbody>
                    </table>
                </div>

                <div class="recentCustomers">
                    <div class="cardHeader">
                        <h2>Dernières Opportunités Publiées</h2>
                    </div>

                    <table>
                        <tr>
                            
                            <td>
                                <h4>Formation DevOps <br> <span>Catégorie: Formation</span></h4>
                            </td>
                        </tr>

                        <tr>
                           
                            <td>
                                <h4>Offre Emploi Senior <br> <span>Catégorie: Emploi</span></h4>
                            </td>
                        </tr>

                        <tr>
                            
                            <td>
                                <h4>Stage d'Été RH <br> <span>Catégorie: Stage</span></h4>
                            </td>
                        </tr>
                        
                        <tr>
                           
                            <td>
                                <h4>Projet Open Source <br> <span>Catégorie: Projet</span></h4>
                            </td>
                        </tr>
                        </table>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>