<?php
session_start();
require_once "connexion.php";

// Check if a member is logged in
$isUser = isset($_SESSION['member_id']);

$search = "";
$order = "DESC";

if ($isUser) {
    if (!empty($_GET['search'])) $search = $_GET['search'];
    if (!empty($_GET['order'])) $order = $_GET['order'];
}

// Query when user is logged in
if ($isUser) {

    $sql = "SELECT * FROM opportunities
            WHERE title LIKE :search OR description LIKE :search
            ORDER BY opp_id $order";

    $query = $conn->prepare($sql);
    $query->execute([
        ':search' => "%$search%"
    ]);

} else {
    // Public view (no filtering)
    $query = $conn->prepare("SELECT * FROM opportunities ORDER BY opp_id DESC");
    $query->execute();
}

$opps = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="style2.css">
<title>Nos Opportunités</title>
</head>
<body>

<h1>Nos Opportunités</h1>

<?php if ($isUser): ?>
<form method="GET" class="filters">
    <input type="text" name="search" placeholder="Recherche" value="<?htmlspecialchars($search) ?>">
    <select name="order">
        <option value="DESC">Plus récentes</option>
        <option value="ASC" <?= $order == "ASC" ? "selected" : "" ?>>Plus anciennes</option>
    </select>
    <button class="btn" type="submit">Filtrer</button>
</form>
<?php endif; ?>

<div class="opp-container">
<?php foreach ($opps as $o): ?>
    <div class="opp-card">
        <h2><?= htmlspecialchars($o['title']) ?></h2>

        <?php if ($isUser): ?>
            <p><?= nl2br(htmlspecialchars($o['description'])) ?></p>
            <a class="btn" href="<?= htmlspecialchars($o['link']) ?>" target="_blank">Voir l'opportunité</a>

        <?php else: ?>
            <p class="blur-text">Connectez-vous pour voir la description complète</p>
            <a class="btn disabled">Lien inaccessible</a>
        <?php endif; ?>

    </div>
<?php endforeach; ?>
</div>

<?php if (!$isUser): ?>
<a class="btn connect" href="login.php?redirect=nos-opportunites.php">Se connecter</a>
<?php endif; ?>

</body>
</html>
