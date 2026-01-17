<?php
session_start();

/* ===== DB CONNECTION ===== */
$conn = mysqli_connect("127.0.0.1", "root", "", "nafas");
if (!$conn) {
    die("Erreur connexion : " . mysqli_connect_error());
}

$error = "";

/* ===== SIGNUP LOGIC ===== */
if (isset($_POST['signup'])) {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT * FROM members WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "❌ Cet email est déjà utilisé.";
    } else {

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $insert = mysqli_query($conn,
            "INSERT INTO members (username, email, password)
             VALUES ('$username', '$email', '$hashedPassword')"
        );

        if ($insert) {

            // Auto login
            $_SESSION['member_id'] = mysqli_insert_id($conn);
            $_SESSION['username']  = $username;
            $_SESSION['email']     = $email;

            // Redirect to quiz
            header("Location: quiz.php");
            exit();
        } else {
            $error = "❌ Erreur lors de l'inscription.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S'inscrire - Nafas</title>
    <style>
        :root { 
            --primary: #0F766E; 
            --white: #ffffff; 
            --bezier: cubic-bezier(0.65, 0, 0.35, 1);
            --img-url: url('Untitled-4.jpg');
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { height: 100vh; display: flex; overflow: hidden; background: #000; }

        #master-wipe {
            position: fixed; top: 0; left: 0;
            width: 45%; height: 100%; z-index: 100;
            overflow: hidden; transition: width 0.9s var(--bezier);
        }
        .wipe-bg {
            position: absolute; top: 0; left: 0;
            width: 100vw; height: 100%;
            background: linear-gradient(rgba(79,70,229,.3), rgba(0,0,0,.6)), var(--img-url) center/cover;
            transition: transform 0.9s var(--bezier);
        }
        .animating #master-wipe { width: 100%; }
        .animating .wipe-bg { transform: scale(1.1); }

        #main-wrapper {
            display: flex; width: 100%; height: 100%;
            background: var(--white);
            transition: transform 0.9s var(--bezier), filter 0.9s var(--bezier);
        }
        .animating #main-wrapper { transform: scale(.9) translateX(100px); filter: blur(10px); }

        .sidebar-space { flex: 0 0 45%; }
        .form-section { flex: 1; display: flex; align-items: center; justify-content: center; padding: 4rem; }
        .form-container { width: 100%; max-width: 400px; }

        .input-group { margin-bottom: 1.5rem; }
        .input-group label { display: block; margin-bottom: .6rem; font-weight: 700; font-size: .75rem; color: #9CA3AF; }
        .input-group input { width: 100%; padding: 1.1rem; border: 2px solid #F3F4F6; border-radius: 16px; background: #F9FAFB; }

        .submit-btn { width: 100%; padding: 1.1rem; background: var(--primary); color: white; border: none; border-radius: 16px; font-weight: 700; cursor: pointer; }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }

        @media (max-width: 1000px) { .sidebar-space, #master-wipe { display: none; } }
    </style>
</head>
<body id="body-ctx">

<div id="master-wipe">
    <div class="wipe-bg">
        <div style="padding:6rem;height:100%;display:flex;flex-direction:column;justify-content:center;color:white;">
            <h1 style="font-size:4.5rem;font-weight:900;">Nafas.</h1>
            <p style="font-size:1.2rem;opacity:.8;">Expérience digitale nouvelle génération.</p>
        </div>
    </div>
</div>

<div id="main-wrapper">
    <div class="sidebar-space"></div>
    <section class="form-section">
        <div class="form-container">

            <h2 style="font-size:2.5rem;font-weight:800;">Inscription</h2>
            <p style="margin-bottom:30px;">Déjà parmi nous ?
                <a href="login.php" id="link-login" style="color:var(--primary);font-weight:700;">Se connecter</a>
            </p>

            <?php if ($error): ?>
                <div class="error"><?= $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Utilisateur</label>
                    <input type="text" name="username" required>
                </div>
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="input-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="signup" class="submit-btn">CRÉER UN COMPTE</button>
            </form>

        </div>
    </section>
</div>

<script>
document.getElementById('link-login').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('body-ctx').classList.add('animating');
    setTimeout(() => { window.location.href = this.href; }, 850);
});
</script>
</body>
</html>
