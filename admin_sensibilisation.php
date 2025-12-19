<?php
session_start();
require_once 'connexion.php';

// --- 1. CONFIGURATION ADMINISTRATIVE ---
// On récupère l'ID de l'admin connecté via la session (par défaut 1 si test)
$admin_id = $_SESSION['admin_id'] ?? 1; 
$message = '';

// --- 2. LOGIQUE D'AJOUT PAR L'ADMINISTRATEUR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    
    // Dossier de stockage pour les images de sensibilisation
    $upload_dir = "uploads/sensibilisation/";
    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

    $file_ext = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $filename = "sensi_admin_" . time() . "." . $file_ext;
    $target_path = $upload_dir . $filename;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_path)) {
        try {
            // L'ADMIN crée le contenu : insertion du created_by lié à la table admins
            $stmt = $conn->prepare("INSERT INTO sensibilisation (titre, description, image_path, created_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$titre, $description, $target_path, $admin_id]);
            $message = '<div class="alert alert-success" style="margin-top:100px;">✅ Contenu de sensibilisation publié par l\'administration.</div>';
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Erreur SQL : ' . $e->getMessage() . '</div>';
        }
    }
}

// --- 3. RÉCUPÉRATION DES CONTENUS (Jointure avec la table admins) ---
try {
    $sql = "SELECT s.*, a.username as admin_name 
            FROM sensibilisation s 
            JOIN admins a ON s.created_by = a.admin_id 
            ORDER BY s.date_publication DESC";
    $contents = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si la table admins n'est pas encore prête, on évite le crash
    $contents = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Sensibilisation Nafas</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        :root { --color-primary: #1C79B4; --color-dark-bg: #00446A; }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f4f9; }
        
        /* NAVBAR INDEX DESIGN */
        .navbar { position: fixed; top: 0; width: 100%; padding: 18px 5%; z-index: 9999; backdrop-filter: blur(12px); background: rgba(0,0,0,0.8); display: flex; justify-content: space-between; align-items: center; }
        .logo img { max-height: 40px; }
        
        /* HERO SECTION */
        .hero-admin { background: var(--color-dark-bg); padding: 150px 5% 60px; text-align: center; color: white; }
        
        /* FORMULAIRE */
        .form-container { background: white; border-radius: 15px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-top: -40px; position: relative; z-index: 10; }
        .btn-nafas { background: var(--color-primary); color: white; border: none; padding: 12px 30px; border-radius: 5px; font-weight: bold; transition: 0.3s; }
        .btn-nafas:hover { background: #145984; transform: translateY(-3px); }

        /* GRID D'AFFICHAGE */
        .sensi-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); height: 100%; border: none; transition: 0.3s; }
        .sensi-card:hover { transform: translateY(-5px); }
        .sensi-card img { width: 100%; height: 200px; object-fit: cover; }
        
        .footer { background: var(--color-dark-bg); color: white; padding: 40px 5%; text-align: center; margin-top: 60px; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo"><img src="Asset 1.png" alt="Logo"></div>
        <div style="color:white;">Session Admin : <strong><?php echo $_SESSION['admin_username'] ?? 'Administrateur'; ?></strong></div>
        <a href="index.php" style="color:white; text-decoration:none; border:1px solid white; padding:5px 15px; border-radius:5px;">Retour au site</a>
    </nav>

    <header class="hero-admin">
        <h1>Espace de Gestion Administrative</h1>
        <p>Publiez les messages officiels et les médias de sensibilisation.</p>
    </header>

    <div class="container">
        <?php echo $message; ?>

        <div class="form-container mb-5">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Titre de l'information</label>
                        <input type="text" name="titre" class="form-control" required placeholder="Ex: Conséquences juridiques...">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Image (Explorer votre PC)</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                    </div>
                    <div class="col-12 mb-4">
                        <label class="form-label fw-bold">Contenu de sensibilisation</label>
                        <textarea name="description" class="form-control" rows="5" required placeholder="Rédigez le texte officiel ici..."></textarea>
                    </div>
                </div>
                <button type="submit" class="btn-nafas shadow">Publier le contenu Admin</button>
            </form>
        </div>

        
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>