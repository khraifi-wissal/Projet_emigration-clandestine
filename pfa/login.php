<?php
session_start();

$conn = mysqli_connect("127.0.0.1", "root", "", "nafas");
if (!$conn) {
    die("Erreur connexion : " . mysqli_connect_error());
}

$error = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM members WHERE email = '$email'");

    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            // ✅ CORRECT SESSION VARIABLES
            $_SESSION['member_id'] = $user['member_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['email']     = $user['email'];

            // ✅ REDIRECT DIRECTLY TO QUIZ
            header("Location: index1.php");
            exit();

        } else {
            $error = "❌ Mot de passe incorrect.";
        }
    } else {
        $error = "❌ Aucun compte trouvé.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Nafas</title>
    <style>
        :root { 
            --primary: #4F46E5; 
            --primary-hover: #4338ca;
            --white: #ffffff; 
            --bezier: cubic-bezier(0.65, 0, 0.35, 1);
            --img-url: url('Untitled-4.jpg');;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Inter', sans-serif; }
        body { height: 100vh; display: flex; overflow: hidden; background: #000; }

        #master-wipe {
            position: fixed; top: 0; right: 0; width: 100%; height: 100%; z-index: 100;
            overflow: hidden; transition: width 0.9s var(--bezier);
            box-shadow: -10px 0 30px rgba(0,0,0,0.5);
        }
        .wipe-bg {
            position: absolute; top: 0; right: 0; width: 100vw; height: 100%;
            background: linear-gradient(rgba(79, 70, 229, 0.4), rgba(0, 0, 0, 0.7)), var(--img-url) center/cover;
            transition: transform 0.9s var(--bezier); transform: scale(1.1);
        }
        .ready #master-wipe { width: 45%; }
        .ready .wipe-bg { transform: scale(1); }

        #main-wrapper {
            display: flex; width: 100%; height: 100%; background: #fff;
            transform: scale(0.9) translateX(-100px); filter: blur(10px);
            transition: transform 1s var(--bezier), filter 1s var(--bezier);
        }
        .ready #main-wrapper { transform: scale(1) translateX(0); filter: blur(0); }

        .form-section { flex: 1; display: flex; align-items: center; justify-content: center; padding: 4rem; }
        .sidebar-space { flex: 0 0 45%; } 
        .form-container { width: 100%; max-width: 400px; opacity: 0; transition: 0.6s ease 0.5s; }
        .ready .form-container { opacity: 1; }

        .input-group { margin-bottom: 1.5rem; }
        .input-group label { display: block; margin-bottom: 0.6rem; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; color: #6B7280; }
        .input-group input { width: 100%; padding: 1.1rem; border: 2px solid #F3F4F6; border-radius: 16px; background: #F9FAFB; }
        .input-group input:focus { border-color: var(--primary); outline: none; }
        
        .submit-btn { width: 100%; padding: 1.1rem; background: var(--primary); color: white; border: none; border-radius: 16px; font-weight: 700; cursor: pointer; }
        .error-msg { background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem; border: 1px solid #fecaca; }

        @media (max-width: 1000px) { .sidebar-space, #master-wipe { display: none; } }
    </style>
</head>
<body id="body-ctx">
    <div id="master-wipe"><div class="wipe-bg"><div style="padding: 6rem; height: 100%; display: flex; flex-direction: column; justify-content: center; color: white;"><h1 style="font-size: 4rem; font-weight: 900;">NAFAS.</h1><p style="font-size: 1.1rem; opacity: 0.9; border-left: 3px solid var(--primary); padding-left: 20px; margin-top: 15px;">Bienvenue à nouveau.</p></div></div></div>
    <div id="main-wrapper">
        <section class="form-section">
            <div class="form-container">
                <h2 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 10px;">Connexion</h2>
                <p style="margin-bottom: 40px; color: #6B7280;">Nouveau ? <a href="signup.php" id="link-signup" style="color: var(--primary); text-decoration: none; font-weight: 700;">Créer un compte</a></p>

                <?php if ($error): ?>
                    <div class="error-msg"><?= $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="input-group">
                        <label>Mot de passe</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="submit-btn">SE CONNECTER</button>
                </form>
            </div>
        </section>
        <div class="sidebar-space"></div>
    </div>

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('body-ctx').classList.add('ready');
            }, 50);
        });

        document.getElementById('link-signup').addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.remove('ready');
            setTimeout(() => {
                window.location.href = this.href;
            }, 850);
        });
    </script>
</body>
</html>
