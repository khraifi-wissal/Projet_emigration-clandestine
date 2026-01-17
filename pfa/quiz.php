<?php
session_start();

/* ===== PROTECT PAGE ===== */
if (!isset($_SESSION["member_id"])) {
    header("Location: login.php");
    exit;
}

/* ===== DB CONNECTION ===== */
$conn = mysqli_connect("127.0.0.1", "root", "", "nafas");
if (!$conn) {
    die("Erreur connexion : " . mysqli_connect_error());
}

$member_id = $_SESSION["member_id"];

/* ===== USER PROFILE LOGIC ===== */
$isLoggedIn = true;
$username = $_SESSION['username'] ?? "Membre";
$userImage = "https://cdn-icons-png.flaticon.com/512/149/149071.png"; // Default

// Fetch specific profile image
$query_user = "SELECT * FROM members WHERE member_id = '$member_id'";
$result_user = mysqli_query($conn, $query_user);
if ($result_user && mysqli_num_rows($result_user) > 0) {
    $row_user = mysqli_fetch_assoc($result_user);
    if (!empty($row_user['profile_image'])) {
        $userImage = $row_user['profile_image'];
    }
}

/* ===== QUIZ LOGIC VARIABLES ===== */
$show_result = false;
$score = 0;
$wrong_answers = [];

/* ===== SUBMIT QUIZ LOGIC ===== */
if (isset($_POST["submit_quiz"])) {
    $show_result = true;
    
    foreach ($_POST["answers"] as $question_id => $selected_option) {
        $q = mysqli_query($conn, "SELECT question_text, option_a, option_b, option_c, option_d, correct_option FROM quiz_questions WHERE question_id = $question_id");
        $row = mysqli_fetch_assoc($q);

        if ($selected_option === $row["correct_option"]) {
            $score++;
        } else {
            $correct_text = $row["option_" . strtolower($row["correct_option"])];
            $wrong_answers[] = [
                "question" => $row["question_text"],
                "correct"  => $correct_text
            ];
        }

        // Save response
        $stmt = mysqli_prepare($conn, "INSERT INTO quiz_responses (member_id, question_id, selected_option) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $member_id, $question_id, $selected_option);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

/* ===== GET QUIZ DATA (If not result) ===== */
$quiz_result = mysqli_query($conn, "SELECT * FROM quiz ORDER BY quiz_id ASC LIMIT 1");
$quiz = mysqli_fetch_assoc($quiz_result);
$quiz_id = $quiz["quiz_id"] ?? 1;
$questions = mysqli_query($conn, "SELECT * FROM quiz_questions WHERE quiz_id = $quiz_id");
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz | Nafas</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="quiz.css">
    <style>
        /* --- MOBILE MENU FIX (COMPACT & CENTERED) --- */
        @media (max-width: 991px) {
            .navbar-collapse {
                background-color: rgba(15, 23, 42, 0.98); /* Solid dark background */
                backdrop-filter: blur(10px);
                padding: 1rem; /* Smaller padding */
                border-radius: 12px;
                margin-top: 8px;
                border: 1px solid rgba(255, 255, 255, 0.1);
                box-shadow: 0 10px 30px rgba(0,0,0,0.5);
                text-align: center; /* Center text */
            }
            
            .navbar-nav {
                align-items: center !important; /* Center align items */
                justify-content: center;
                width: 100%;
            }

            .nav-item {
                width: 100%;
                text-align: center;
                margin-bottom: 2px; /* Minimal spacing between links */
            }

            .nav-link {
                display: block;
                padding: 6px 0; /* Smaller padding for links */
                font-size: 0.85rem; /* Slightly smaller font for mobile */
            }

            /* Compact User Profile / Login Button area */
            .d-flex.align-items-center {
                justify-content: center;
                margin-top: 0.8rem; /* Reduced top margin */
                width: 100%;
                padding-top: 0.8rem;
                border-top: 1px solid rgba(255,255,255,0.05); /* Subtle separator */
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="Asset 1.png" alt="Logo Nafas" height="45"> 
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item"><a class="nav-link " href="index1.php">À Propos</a></li>
                    <li class="nav-item"><a class="nav-link" href="sensibilisation.php">Sensibilisation</a></li>
                    <li class="nav-item"><a class="nav-link" href="opportunites.php">Opportunités</a></li>
                    <li class="nav-item"><a class="nav-link active" href="quiz.php">Quiz</a></li>
                    <li class="nav-item"><a class="nav-link" href="story_telling.php">Storytelling</a></li>
                    <li class="nav-item"><a class="nav-link" href="media.php">Médias</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" id="theme-toggle"><i class="fas fa-sun"></i></a>
                    </li>
                </ul>

                <div class="d-flex align-items-center">
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-white" href="#" role="button" data-bs-toggle="dropdown">
                                <span class="fw-bold d-none d-lg-block"><?php echo htmlspecialchars($username); ?></span>
                                <img src="<?php echo htmlspecialchars($userImage); ?>" class="rounded-circle border border-2" width="40" height="40" style="object-fit: cover; border-color: #0d635d !important;">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 mt-2">
                                <li><div class="dropdown-header px-3 py-2 text-muted small">Connecté en tant que <strong><?php echo htmlspecialchars($username); ?></strong></div></li>
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2" style="color: #0d635d;"></i> Mon Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logoutt.php"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="signup.php" class="nav-link cta-button">Je m'engage</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <header class="mini-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-2 ">Quiz <span style="color: #0d635d;">Entrepreneur</span></h1>
            <p class="lead opacity-75"><?= htmlspecialchars($quiz["content"] ?? 'Découvrez votre potentiel et vos alternatives.'); ?></p>
        </div>
    </header>

    <main class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="quiz-card">
                    
                    <?php if ($show_result): ?>
                        <div class="text-center">
                            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-4" style="width: 80px; height: 80px;">
                                <i class="fas fa-clipboard-check fa-3x"></i>
                            </div>
                            <h2 class="fw-bold mb-4">Résultat du Quiz</h2>

                            <div class="d-inline-block p-4 border border-primary rounded-4 bg-primary bg-opacity-10 mb-5">
                                <span class="display-2 fw-black text-primary d-block lh-1"><?= $score ?></span>
                                <span class="text-uppercase small fw-bold text-muted">Points</span>
                            </div>

                            <?php if (!empty($wrong_answers)): ?>
                                <div class="text-start">
                                    <h5 class="text-danger fw-bold mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Points à revoir</h5>
                                    <?php foreach ($wrong_answers as $wa): ?>
                                        <div class="alert alert-light border-start border-4 border-danger shadow-sm mb-3">
                                            <p class="mb-1 fw-bold text-dark"><?= $wa["question"]; ?></p>
                                            <small class="text-success"><i class="fas fa-check me-1"></i> Réponse correcte : <?= $wa["correct"]; ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-success border-success text-center">
                                    <h4 class="alert-heading fw-bold"><i class="fas fa-trophy me-2"></i>Excellent !</h4>
                                    <p class="mb-0">Vous maîtrisez parfaitement le sujet. Continuez ainsi !</p>
                                </div>
                            <?php endif; ?>

                            <div class="mt-5 d-flex gap-3 justify-content-center">
                                <a href="quiz.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold">Recommencer</a>
                                <a href="index1.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold">Retour Accueil</a>
                            </div>
                        </div>

                    <?php else: ?>
                        <form method="POST">
                            <?php 
                            $count = 1;
                            while ($q = mysqli_fetch_assoc($questions)): 
                            ?>
                            <div class="question-block">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="fw-bold mb-0">
                                        <span class="q-number">0<?= $count ?>.</span>
                                        <span id="qtext-<?= $q['question_id'] ?>"><?= htmlspecialchars($q["question_text"]); ?></span>
                                    </h5>
                                    <div class="d-flex">
                                        <button type="button" class="voice-btn" onclick="speakQuestion('<?= $q['question_id'] ?>')" title="Lire"><i class="fas fa-volume-up"></i></button>
                                        <button type="button" class="voice-btn" id="mic-<?= $q['question_id'] ?>" onclick="listenAnswer('<?= $q['question_id'] ?>')" title="Répondre"><i class="fas fa-microphone"></i></button>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <?php foreach(['A','B','C','D'] as $opt): ?>
                                        <div class="col-md-6">
                                            <label class="custom-option">
                                                <input type="radio" name="answers[<?= $q["question_id"]; ?>]" value="<?= $opt ?>" id="opt-<?= $q['question_id'] ?>-<?= $opt ?>" required>
                                                <div class="option-content">
                                                    <?= htmlspecialchars($q["option_" . strtolower($opt)]); ?>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php 
                            $count++;
                            endwhile; 
                            ?>
                           <div class="text-center mt-5">
                             <button type="submit" name="submit_quiz" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow" style="background-color: #0d635d; border-color: #0d635d; color: white;">
                            Soumettre mes réponses <i class="fas fa-arrow-right ms-2"></i>
                           </button>
                          </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark-footer text-white py-5">
        <div class="container pt-4">
            <div class="row g-4">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="d-flex align-items-center mb-3">
                        <img src="Asset 1.png" alt="Nafas" height="30" class="me-2" >
                        
                    </div>
                    <p class="text-white-50">
                        Une plateforme dédiée à la jeunesse tunisienne. Nous croyons que ta réussite est possible ici, chez toi.
                    </p>
                </div>
                <div class="col-6 col-lg-2 offset-lg-2">
                    <h5 class="fw-bold mb-3">Navigation</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index1.php" class="text-white-50 text-decoration-none">À Propos</a></li>
                        <li class="mb-2"><a href="media.php" class="text-white-50 text-decoration-none">Médias</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-white-50 text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5 class="fw-bold mb-3">Suivez-nous</h5>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle social-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle social-btn"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-top border-secondary mt-5 pt-4 text-center text-white-50 small">
                &copy; 2025 Nafas. Tous droits réservés.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="quiz.js"></script>
</body>
</html>