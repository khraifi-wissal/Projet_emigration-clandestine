<?php
// modifier_story_post.php

session_start();
// Vérification simple de l'authentification Admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require 'connexion.php'; 

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$message = '';
$post_data = null;
$redirect_to_thread = 'gestion_storytelling.php'; // Redirection par défaut

if ($post_id) {
    // 1. Récupération du post à modifier
    $stmt_fetch = $conn->prepare("SELECT content, parent_id FROM storytelling WHERE story_id = ?");
    $stmt_fetch->bind_param("i", $post_id);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    $post_data = $result->fetch_assoc();
    $stmt_fetch->close();

    // Définir la page de retour (vers le fil de discussion ou la liste principale)
    if ($post_data && $post_data['parent_id'] !== null) {
        $redirect_to_thread = 'gestion_reponses_story.php?story_id=' . $post_data['parent_id'];
    } elseif ($post_data && $post_data['parent_id'] === null) {
        $redirect_to_thread = 'gestion_storytelling.php';
    }

    if (!$post_data) {
        $message = '<div class="alert alert-warning">Post introuvable.</div>';
    }
}

// 2. Traitement de la modification (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id_to_edit'])) {
    $post_id_to_edit = (int)$_POST['post_id_to_edit'];
    $new_content = trim($_POST['new_content']);

    if (empty($new_content)) {
        $message = '<div class="alert alert-danger">Le contenu ne peut pas être vide.</div>';
    } else {
        $sql = "UPDATE storytelling SET content = ? WHERE story_id = ?";
        $stmt_update = $conn->prepare($sql);
        
        if ($stmt_update) {
            $stmt_update->bind_param("si", $new_content, $post_id_to_edit);
            
            if ($stmt_update->execute()) {
                // Redirection après succès
                header("Location: {$redirect_to_thread}&success=updated");
                exit;
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de la mise à jour: ' . $conn->error . '</div>';
            }
            $stmt_update->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Post Storytelling</title>
    <link rel="stylesheet" href="assets/css/style.css"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container p-5">
        <h1 style="color: var(--blue);">Modifier un Post Storytelling</h1>
        <p><a href="<?php echo htmlspecialchars($redirect_to_thread); ?>">← Annuler et Retourner</a></p>

        <?php echo $message; ?>

        <?php if ($post_data): ?>
            <div class="card p-4 shadow">
                <form action="modifier_story_post.php?id=<?php echo $post_id; ?>" method="POST">
                    <input type="hidden" name="post_id_to_edit" value="<?php echo $post_id; ?>">
                    
                    <div class="mb-3">
                        <label for="new_content" class="form-label">Contenu actuel du Post (ID: <?php echo $post_id; ?>)</label>
                        <textarea class="form-control" id="new_content" name="new_content" rows="8" required><?php echo htmlspecialchars($post_data['content']); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn status inProgress mt-3">Enregistrer la Modification</button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>