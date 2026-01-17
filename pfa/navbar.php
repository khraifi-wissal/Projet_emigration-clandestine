<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$conn = mysqli_connect("127.0.0.1", "root", "", "nafas");

$isLoggedIn = isset($_SESSION['user_id']);
$userImage = 'assets/img/default-avatar.png'; // Chemin par défaut

if ($isLoggedIn) {
    $m_id = $_SESSION['user_id'];
    $res = mysqli_query($conn, "SELECT profile_image, username FROM members WHERE id = '$m_id'");
    if ($row = mysqli_fetch_assoc($res)) {
        if (!empty($row['profile_image'])) { $userImage = $row['profile_image']; }
        $currentUserName = $row['username'];
    }
}
?>
<nav class="navbar">
    <div class="nav-container">
        <a href="index1.php" class="logo">NAFAS</a>
        
        <div class="nav-links">
            <a href="index1.php">Accueil</a>
            <a href="media.php">Média</a>
            <a href="storytelling.php">Storytelling</a>
            <a href="quiz.php">Quiz</a>

            <?php if (!$isLoggedIn): ?>
                <a href="signup.php" class="engage-btn">Je m'engage</a>
            <?php else: ?>
                <div class="profile-dropdown">
                    <img src="<?php echo $userImage; ?>" class="nav-profile-img" onclick="toggleDropdown()">
                    <div id="dropdownMenu" class="dropdown-content">
                        <p class="dropdown-user">Salut, <?php echo htmlspecialchars($currentUserName); ?></p>
                        <hr>
                        <a href="profile.php">Mon Profil</a>
                        <a href="logout.php" class="logout-link">Déconnexion</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
    /* Design cohérent avec votre thème bleu */
    .navbar { background: #fff; padding: 1rem 5%; box-shadow: 0 2px 15px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 1000; }
    .nav-container { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; }
    .nav-links { display: flex; align-items: center; gap: 25px; }
    .nav-links a { text-decoration: none; color: #1f2937; font-weight: 500; transition: 0.3s; }
    .nav-links a:hover { color: #4F46E5; }
    
    .engage-btn { background: #4F46E5; color: white !important; padding: 0.7rem 1.4rem; border-radius: 12px; font-weight: 700 !important; }
    
    .profile-dropdown { position: relative; }
    .nav-profile-img { width: 42px; height: 42px; border-radius: 50%; cursor: pointer; border: 2px solid #4F46E5; object-fit: cover; }
    
    .dropdown-content { 
        display: none; position: absolute; right: 0; top: 55px; background: white; 
        min-width: 200px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
        overflow: hidden; border: 1px solid #f3f4f6; animation: slideIn 0.3s ease;
    }
    .dropdown-content a { padding: 12px 20px; display: block; font-size: 0.9rem; }
    .dropdown-user { padding: 12px 20px; font-weight: 800; color: #4F46E5; font-size: 0.85rem; text-transform: uppercase; }
    .logout-link { color: #ef4444 !important; background: #fff1f2; }
    .show { display: block; }

    @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
    function toggleDropdown() { document.getElementById("dropdownMenu").classList.toggle("show"); }
    window.onclick = function(e) { 
        if (!e.target.matches('.nav-profile-img')) {
            var d = document.getElementById("dropdownMenu");
            if (d && d.classList.contains('show')) d.classList.remove('show');
        }
    }
</script>