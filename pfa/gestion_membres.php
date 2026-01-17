<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
// On inclut la connexion qui utilise PDO
require_once 'connexion.php'; 

$message = '';

// --- AJOUT D'UN MEMBRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_member'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password']; 

        if (empty($username) || empty($email) || empty($password)) {
            $message = '<div class="alert alert-danger">Veuillez remplir tous les champs.</div>';
        } else {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO members (username, email, password) VALUES (:username, :email, :password)";
                $stmt = $conn->prepare($sql);
                
                $stmt->execute([
                    ':username' => $username,
                    ':email'    => $email,
                    ':password' => $hashed_password
                ]);

                $message = '<div class="alert alert-success">Membre <b>' . htmlspecialchars($username) . '</b> ajouté avec succès!</div>';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { 
                    $message = '<div class="alert alert-danger">Erreur : L\'email existe déjà.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Erreur lors de l\'ajout: ' . $e->getMessage() . '</div>';
                }
            }
        }
    } elseif (isset($_POST['delete_member'])) {
        $id = $_POST['id'];
        try {
            $sql = "DELETE FROM members WHERE member_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([':id' => $id]);
            $message = '<div class="alert alert-success">Membre supprimé avec succès.</div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
        }
    }
}

// --- LECTURE DES MEMBRES ---
$members = [];
try {
    $sql_select = "SELECT member_id, username, email, created_at, last_login FROM members ORDER BY created_at DESC";
    $stmt_select = $conn->query($sql_select);
    $members = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message .= '<div class="alert alert-danger">Erreur de lecture : ' . $e->getMessage() . '</div>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Membres - Nafas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --brand-teal: #0F766E;
            --brand-dark: #0f172a;
            --brand-light: #f1f5f9;
            --white: #ffffff;
            --card-radius: 20px;
        }
        body { background: var(--brand-light); font-family: 'Ubuntu', sans-serif; overflow-x: hidden; }
        
        /* Layout overrides */
        .container-fluid-custom { position: relative; width: 100%; }
        .navigation { position: fixed; width: 300px; height: 100%; background: var(--brand-dark); border-left: 10px solid var(--brand-dark); transition: 0.5s; overflow: hidden; z-index: 100; }
        .navigation.active { width: 80px; }
        .navigation ul { position: absolute; top: 0; left: 0; width: 100%; padding-left: 0; }
        .navigation ul li { position: relative; width: 100%; list-style: none; border-top-left-radius: 30px; border-bottom-left-radius: 30px; }
        .navigation ul li:hover, .navigation ul li.hovered { background-color: var(--brand-light); }
        .navigation ul li:nth-child(1) { margin-bottom: 40px; pointer-events: none; }
        .navigation ul li a { position: relative; display: block; width: 100%; display: flex; text-decoration: none; color: var(--white); }
        .navigation ul li:hover a, .navigation ul li.hovered a { color: var(--brand-teal); }
        .navigation ul li a .icon { position: relative; display: block; min-width: 60px; height: 60px; line-height: 75px; text-align: center; }
        .navigation ul li a .icon ion-icon { font-size: 1.75rem; }
        .navigation ul li a .title { position: relative; display: block; padding: 0 10px; height: 60px; line-height: 60px; text-align: start; white-space: nowrap; }
        
        /* Curve Effect */
        .navigation ul li:hover a::before, .navigation ul li.hovered a::before { content: ''; position: absolute; right: 0; top: -50px; width: 50px; height: 50px; background-color: transparent; border-radius: 50%; box-shadow: 35px 35px 0 10px var(--brand-light); pointer-events: none; }
        .navigation ul li:hover a::after, .navigation ul li.hovered a::after { content: ''; position: absolute; right: 0; bottom: -50px; width: 50px; height: 50px; background-color: transparent; border-radius: 50%; box-shadow: 35px -35px 0 10px var(--brand-light); pointer-events: none; }

        .main { position: absolute; width: calc(100% - 300px); left: 300px; min-height: 100vh; background: var(--brand-light); transition: 0.5s; }
        .navigation.active ~ .main { width: calc(100% - 80px); left: 80px; }
        
        .topbar { width: 100%; height: 60px; display: flex; justify-content: space-between; align-items: center; padding: 0 10px; margin-bottom: 20px;}
        .toggle { width: 60px; height: 60px; display: flex; justify-content: center; align-items: center; font-size: 2.5rem; cursor: pointer; color: var(--brand-dark); }
        .user { width: 40px; height: 40px; border-radius: 50%; overflow: hidden; margin-right: 20px; border: 2px solid var(--brand-teal); }
        .user img { width: 100%; height: 100%; object-fit: cover; }

        /* Card & Table Styles */
        .details { position: relative; width: 100%; padding: 20px; display: grid; grid-template-columns: 1fr; grid-gap: 30px; }
        .recentOrders { position: relative; display: grid; background: var(--white); padding: 20px; box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08); border-radius: var(--card-radius); }
        .cardHeader { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .cardHeader h2 { font-weight: 600; color: var(--brand-teal); }
        
        /* Status Badges */
        .status.return { padding: 2px 4px; background: #f00; color: var(--white); border-radius: 4px; font-size: 14px; font-weight: 500; border: none;}
        .status.delivered { padding: 2px 4px; background: #8de02c; color: var(--white); border-radius: 4px; font-size: 14px; font-weight: 500; }
        
        @media (max-width: 991px) {
            .navigation { left: -300px; }
            .navigation.active { width: 300px; left: 0; }
            .main { width: 100%; left: 0; }
            .main.active { left: 300px; }
        }
    </style>
</head>

<body>
    <div class="container-fluid-custom">
     <div class="navigation">
    <ul>
        <li><a href="#"><span class="title" style="font-weight: 700; font-size: 1.2rem;">Nafas</span></a></li>
        <li><a href="index.php"><span class="icon"><ion-icon name="home-outline"></ion-icon></span><span class="title">Dashboard</span></a></li>
        <li class="hovered"><a href="gestion_membres.php"><span class="icon"><ion-icon name="people-outline"></ion-icon></span><span class="title">Membres</span></a></li>
        <li><a href="gestion_opportunites.php"><span class="icon"><ion-icon name="briefcase-outline"></ion-icon></span><span class="title">Opportunités</span></a></li>
        <li><a href="gestion_quiz.php"><span class="icon"><ion-icon name="help-circle-outline"></ion-icon></span><span class="title">Quiz</span></a></li>
        <li><a href="gestion_storytelling.php"><span class="icon"><ion-icon name="book-outline"></ion-icon></span><span class="title">Storytelling</span></a></li>
        <li><a href="admin_sensibilisation.php"><span class="icon"><ion-icon name="megaphone-outline"></ion-icon></span><span class="title">Contenus</span></a></li>
        <li><a href="gestion_brochures.php"><span class="icon"><ion-icon name="document-text-outline"></ion-icon></span><span class="title">Brochures</span></a></li>
        <li><a href="admin_login.php"><span class="icon"><ion-icon name="log-out-outline"></ion-icon></span><span class="title">Déconnexion</span></a></li>
    </ul>
</div>

        <div class="main">
            <div class="topbar">
                <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
                <div class="user"><img src="admin.jpg"  alt="Admin"></div>
            </div>

            <div class="details">
                <?php echo $message; ?>

                <div class="recentOrders mb-4">
                    <div class="cardHeader">
                        <h2>Ajouter un Nouveau Membre</h2>
                    </div>
                    <form action="gestion_membres.php" method="POST">
                        <input type="hidden" name="add_member" value="1">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nom d'utilisateur</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Mot de passe</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-success px-4" style="background-color: var(--brand-teal); border:none;">Ajouter le Membre</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="recentOrders">
                    <div class="cardHeader">
                        <h2>Liste des Membres (<?php echo count($members); ?>)</h2>
                    </div>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <td>ID</td>
                                <td>Nom d'utilisateur</td>
                                <td>Email</td>
                                <td>Inscrit le</td>
                                <td>Dernière Connexion</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($members) > 0): ?>
                                <?php foreach ($members as $member): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($member['member_id']); ?></td>
                                        <td><?php echo htmlspecialchars($member['username']); ?></td>
                                        <td><?php echo htmlspecialchars($member['email']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($member['created_at'])); ?></td>
                                        <td>
                                            <?php echo $member['last_login'] ? date('d/m/Y H:i', strtotime($member['last_login'])) : '<span class="badge bg-secondary">Jamais</span>'; ?>
                                        </td>
                                        <td>
                                            <form action="gestion_membres.php" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce membre ?');">
                                                <input type="hidden" name="delete_member" value="1">
                                                <input type="hidden" name="id" value="<?php echo $member['member_id']; ?>">
                                                <button type="submit" class="status return" style="cursor:pointer;">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">Aucun membre trouvé.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let toggle = document.querySelector('.toggle');
        let navigation = document.querySelector('.navigation');
        let main = document.querySelector('.main');
        toggle.onclick = function () { navigation.classList.toggle('active'); main.classList.toggle('active'); }
    </script>
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>