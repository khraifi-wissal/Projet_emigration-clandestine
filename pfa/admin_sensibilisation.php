<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
require_once 'connexion.php';

// --- 1. CONFIGURATION ADMINISTRATIVE ---
$admin_id = $_SESSION['admin_id']; 
$message = '';

// --- 2. LOGIQUE D'AJOUT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image'])) {
        $titre = trim($_POST['titre']);
        $description = trim($_POST['description']);
        
        $upload_dir = "uploads/sensibilisation/";
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

        $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $filename = "sensi_admin_" . time() . "." . $file_ext;
        $target_path = $upload_dir . $filename;

        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_path)) {
                try {
                    $stmt = $conn->prepare("INSERT INTO sensibilisation (titre, description, image_path, created_by) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$titre, $description, $target_path, $admin_id]);
                    $message = '<div class="alert alert-success">✅ Contenu publié avec succès.</div>';
                } catch (PDOException $e) {
                    $message = '<div class="alert alert-danger">Erreur SQL : ' . $e->getMessage() . '</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Erreur upload.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Format invalide (JPG, PNG, WEBP).</div>';
        }
    } elseif (isset($_POST['delete_content'])) {
        $id = $_POST['id'];
        try {
            $stmt = $conn->prepare("DELETE FROM sensibilisation WHERE id = ?");
            if ($stmt->execute([$id])) {
                $message = '<div class="alert alert-success">Contenu supprimé.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
        }
    }
}

// --- 3. RÉCUPÉRATION ---
$contents = [];
try {
    $sql = "SELECT s.*, a.username as admin_name FROM sensibilisation s JOIN admins a ON s.created_by = a.admin_id ORDER BY s.date_publication DESC";
    $contents = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $contents = []; }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Sensibilisation - Nafas Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root { --brand-teal: #0F766E; --brand-dark: #0f172a; --brand-light: #f1f5f9; --white: #ffffff; --card-radius: 20px; }
        body { background: var(--brand-light); font-family: 'Ubuntu', sans-serif; overflow-x: hidden; }
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
        .navigation ul li:hover a::before, .navigation ul li.hovered a::before { content: ''; position: absolute; right: 0; top: -50px; width: 50px; height: 50px; background-color: transparent; border-radius: 50%; box-shadow: 35px 35px 0 10px var(--brand-light); pointer-events: none; }
        .navigation ul li:hover a::after, .navigation ul li.hovered a::after { content: ''; position: absolute; right: 0; bottom: -50px; width: 50px; height: 50px; background-color: transparent; border-radius: 50%; box-shadow: 35px -35px 0 10px var(--brand-light); pointer-events: none; }
        .main { position: absolute; width: calc(100% - 300px); left: 300px; min-height: 100vh; background: var(--brand-light); transition: 0.5s; }
        .navigation.active ~ .main { width: calc(100% - 80px); left: 80px; }
        .topbar { width: 100%; height: 60px; display: flex; justify-content: space-between; align-items: center; padding: 0 10px; margin-bottom: 20px;}
        .toggle { width: 60px; height: 60px; display: flex; justify-content: center; align-items: center; font-size: 2.5rem; cursor: pointer; color: var(--brand-dark); }
        .user { width: 40px; height: 40px; border-radius: 50%; overflow: hidden; margin-right: 20px; border: 2px solid var(--brand-teal); }
        .user img { width: 100%; height: 100%; object-fit: cover; }
        .details { position: relative; width: 100%; padding: 20px; display: grid; grid-template-columns: 1fr; grid-gap: 30px; }
        .recentOrders { position: relative; display: grid; background: var(--white); padding: 20px; box-shadow: 0 7px 25px rgba(0, 0, 0, 0.08); border-radius: var(--card-radius); }
        .cardHeader { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        .cardHeader h2 { font-weight: 600; color: var(--brand-teal); }
        .thumb-img { width: 60px; height: 40px; object-fit: cover; border-radius: 6px; }
        @media (max-width: 991px) { .navigation { left: -300px; } .navigation.active { width: 300px; left: 0; } .main { width: 100%; left: 0; } .main.active { left: 300px; } }
    </style>
</head>
<body>
    <div class="container-fluid-custom">
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
                <div class="toggle"><ion-icon name="menu-outline"></ion-icon></div>
                <div class="user"><img src="admin.jpg"  alt="Admin"></div>
            </div>

            <div class="details">
                <h1 class="mb-4" style="color: var(--brand-teal);">Gestion de la Sensibilisation</h1>
                <?php echo $message; ?>

                <div class="recentOrders mb-4">
                    <div class="cardHeader"><h2>Publier un nouveau contenu</h2></div>
                    <form action="admin_sensibilisation.php" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Titre</label>
                                <input type="text" name="titre" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Image (JPG, PNG)</label>
                                <input type="file" name="image" class="form-control" accept="image/*" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Texte</label>
                                <textarea name="description" class="form-control" rows="5" required></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-success px-4" style="background-color: var(--brand-teal); border:none;">Publier</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="recentOrders">
                    <div class="cardHeader"><h2>Contenus publiés</h2></div>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <td>Image</td>
                                <td>Titre</td>
                                <td>Auteur</td>
                                <td>Date</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($contents)): ?>
                                <?php foreach ($contents as $c): ?>
                                    <tr>
                                        <td><img src="<?php echo htmlspecialchars($c['image_path']); ?>" class="thumb-img" alt="img"></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($c['titre']); ?></td>
                                        <td><?php echo htmlspecialchars($c['admin_name']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($c['date_publication'])); ?></td>
                                        <td>
                                            <form action="admin_sensibilisation.php" method="POST" onsubmit="return confirm('Supprimer ce contenu ?');">
                                                <input type="hidden" name="delete_content" value="1">
                                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">Aucun contenu.</td></tr>
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