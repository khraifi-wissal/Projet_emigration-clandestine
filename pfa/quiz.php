<?php
session_start();

/* ===== PROTECTION PAGE ===== */
if (!isset($_SESSION["member_id"])) {
    header("Location: login.php");
    exit;
}

/* ===== CONNEXION DB ===== */
$conn = mysqli_connect("127.0.0.1", "root", "", "nafas");
if (!$conn) {
    die("Erreur connexion : " . mysqli_connect_error());
}

$member_id = $_SESSION["member_id"];

/* ===== LOGIQUE PROFIL UTILISATEUR ===== */
$isLoggedIn = true;
$username = $_SESSION['username'] ?? "Membre";
$userImage = "https://cdn-icons-png.flaticon.com/512/149/149071.png"; 

$query_user = "SELECT * FROM members WHERE member_id = '$member_id'";
$result_user = mysqli_query($conn, $query_user);
if ($result_user && mysqli_num_rows($result_user) > 0) {
    $row_user = mysqli_fetch_assoc($result_user);
    if (!empty($row_user['profile_image'])) {
        $userImage = $row_user['profile_image'];
    }
}

/* ===== VARIABLES LOGIQUE QUIZ ===== */
$show_result = false;
$score = 0;
$wrong_answers = [];
$selected_quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

/* ===== SOUMISSION DU QUIZ ===== */
if (isset($_POST["submit_quiz"])) {
    $show_result = true;
    $current_quiz_id = (int)$_POST['quiz_id'];
    
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

        $stmt = mysqli_prepare($conn, "INSERT INTO quiz_responses (member_id, question_id, selected_option) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $member_id, $question_id, $selected_option);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    $quiz_query = mysqli_query($conn, "SELECT * FROM quiz WHERE quiz_id = $current_quiz_id");
    $quiz = mysqli_fetch_assoc($quiz_query);
}

/* ===== RÉCUPÉRATION DES DONNÉES ===== */
if ($selected_quiz_id > 0 && !$show_result) {
    $quiz_query = mysqli_query($conn, "SELECT * FROM quiz WHERE quiz_id = $selected_quiz_id");
    $quiz = mysqli_fetch_assoc($quiz_query);
    $questions = mysqli_query($conn, "SELECT * FROM quiz_questions WHERE quiz_id = $selected_quiz_id");
} else {
    $all_quizzes = mysqli_query($conn, "SELECT * FROM quiz ORDER BY quiz_id DESC");
}
?>

<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz | Nafas</title>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root { 
            --brand-teal: #0F766E; 
            --brand-teal-light: #14b8a6;
            --brand-dark: #0f172a;
        }

        body { font-family: 'Poppins', sans-serif; transition: 0.3s; }
        
        /* Vert à la place du Bleu */
        .text-primary { color: var(--brand-teal) !important; }
        .btn-primary { background-color: var(--brand-teal) !important; border-color: var(--brand-teal) !important; color: white !important; }
        .btn-primary:hover { background-color: #0d635d !important; }
        .btn-outline-primary { color: var(--brand-teal) !important; border-color: var(--brand-teal) !important; }
        .btn-outline-primary:hover { background-color: var(--brand-teal) !important; color: white !important; }

        .navbar { background-color: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); }
        .nav-link:hover, .nav-link.active { color: var(--brand-teal-light) !important; }

        .quiz-selection-card { transition: 0.3s; border-left: 5px solid var(--brand-teal); cursor: pointer; border-radius: 15px; }
        .quiz-selection-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        
        /* Voice Buttons Style */
        .voice-btn { background: none; border: none; color: var(--brand-teal); font-size: 1.2rem; margin-left: 10px; transition: 0.2s; cursor: pointer; }
        .voice-btn:hover { transform: scale(1.2); color: var(--brand-teal-light); }
        .voice-btn.listening { color: #E11D48; animation: pulse 1.5s infinite; }
        
        .custom-option input[type="radio"] { display: none; }
        .option-content { border: 2px solid #e2e8f0; transition: 0.2s; cursor: pointer; }
        .custom-option input[type="radio"]:checked + .option-content { border-color: var(--brand-teal); background-color: rgba(15, 118, 110, 0.05); }

        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

        @media (max-width: 991px) {
            .navbar-collapse { background-color: rgba(15, 23, 42, 0.98); padding: 1rem; border-radius: 12px; text-align: center; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container-fluid">
            <a class="navbar-brand" href="index1.php"><img src="logo-nafas.png" alt="Logo" height="45"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 align-items-center text-uppercase">
                    <li class="nav-item"><a class="nav-link " href="index1.php" class="text-uppercase">À Propos</a></li>
                    <li class="nav-item"><a class="nav-link" href="sensibilisation.php">Sensibilisation</a></li>
                    <li class="nav-item"><a class="nav-link " href="opportunites.php">Opportunités</a></li>
                    <li class="nav-item"><a class="nav-link active" href="quiz.php">Quiz</a></li>
                    <li class="nav-item"><a class="nav-link" href="story_telling.php">Storytelling</a></li>
                    <li class="nav-item"><a class="nav-link" href="media.php">Médias</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" id="theme-toggle"><i class="fas fa-sun"></i></a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-white" href="#" role="button" data-bs-toggle="dropdown">
                            <span class="fw-bold d-none d-lg-block"><?php echo htmlspecialchars($username); ?></span>
                            <img src="<?php echo htmlspecialchars($userImage); ?>" class="rounded-circle border border-2" width="40" height="40" style="object-fit: cover; border-color: var(--brand-teal) !important;">
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logoutt.php"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <header class="mini-hero" style="padding-top: 130px; padding-bottom: 50px;">
        <div class="container text-center">
            <h1 class="display-4 fw-bold">Quiz <span style="color: var(--brand-teal);">Interactifs</span></h1>
            <p class="lead opacity-75">Explorez vos connaissances et découvrez des alternatives locales.</p>
        </div>
    </header>

    <main class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">

                <?php if ($selected_quiz_id === 0 && !$show_result): ?>
                    <div class="row g-4">
                        <?php while ($quiz_item = mysqli_fetch_assoc($all_quizzes)): ?>
                            <div class="col-md-6">
                                <div class="card quiz-selection-card h-100 shadow-sm p-4 border-0">
                                    <div class="card-body">
                                        <h4 class="fw-bold"><?= htmlspecialchars($quiz_item['title']); ?></h4>
                                        <p class="text-muted small"><?= htmlspecialchars($quiz_item['content']); ?></p>
                                        <a href="quiz.php?id=<?= $quiz_item['quiz_id']; ?>" class="btn btn-primary rounded-pill px-4 mt-3">Commencer</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                <?php elseif ($show_result): ?>
                    <div class="card p-5 shadow rounded-4 text-center border-0" style="background: var(--bs-tertiary-bg);">
                        <h2 class="fw-bold mb-4">Score : <?= htmlspecialchars($quiz['title']); ?></h2>
                        <div class="display-2 fw-bold text-primary mb-4"><?= $score ?></div>
                        <?php if (!empty($wrong_answers)): ?>
                            <div class="text-start mt-4">
                                <h5 class="text-danger fw-bold mb-3"><i class="fas fa-times-circle me-2"></i>Révisions nécessaires :</h5>
                                <?php foreach ($wrong_answers as $wa): ?>
                                    <div class="alert alert-secondary border-start border-4 border-danger mb-2">
                                        <p class="mb-1 fw-bold"><?= $wa["question"]; ?></p>
                                        <small class="text-success">La bonne réponse : <?= $wa["correct"]; ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="text-center">
                            <a href="quiz.php" class="btn btn-primary rounded-pill px-5 mt-4">Retour à la liste</a>
                        <a href="opportunites.php" class="btn btn-primary rounded-pill px-5 mt-4">Trouver une Opportunités</a>
                            </div>
                        
                    </div>

                <?php else: ?>
                    <div class="card p-4 p-md-5 shadow rounded-4 border-0" style="background: var(--bs-tertiary-bg);">
                        <a href="quiz.php" class="text-decoration-none text-muted small mb-4 d-block"><i class="fas fa-arrow-left"></i> Retour</a>
                        <h2 class="fw-bold mb-5" style="color: var(--brand-teal);"><?= htmlspecialchars($quiz['title']); ?></h2>
                        
                        <form method="POST" id="quizForm">
                            <input type="hidden" name="quiz_id" value="<?= $selected_quiz_id ?>">
                            <?php $count = 1; while ($q = mysqli_fetch_assoc($questions)): ?>
                                <div class="question-block mb-5">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="fw-bold mb-0">
                                            <span class="text-muted">0<?= $count ?>.</span> 
                                            <span id="qtext-<?= $q['question_id'] ?>"><?= htmlspecialchars($q["question_text"]); ?></span>
                                        </h5>
                                        <div>
                                            <button type="button" class="voice-btn" onclick="speakText('qtext-<?= $q['question_id'] ?>')" title="Écouter"><i class="fas fa-volume-up"></i></button>
                                            <button type="button" class="voice-btn mic-trigger" id="mic-<?= $q['question_id'] ?>" onclick="startVoiceRecord(<?= $q['question_id'] ?>)" title="Répondre par voix"><i class="fas fa-microphone"></i></button>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <?php foreach(['A','B','C','D'] as $opt): ?>
                                            <div class="col-md-6">
                                                <label class="custom-option w-100 h-100">
                                                    <input type="radio" name="answers[<?= $q["question_id"]; ?>]" value="<?= $opt ?>" id="opt-<?= $q['question_id'] ?>-<?= $opt ?>" required>
                                                    <div class="option-content p-3 rounded-3 h-100">
                                                        <span class="fw-bold me-2"><?= $opt ?>.</span>
                                                        <span id="label-<?= $q['question_id'] ?>-<?= $opt ?>"><?= htmlspecialchars($q["option_" . strtolower($opt)]); ?></span>
                                                    </div>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php $count++; endwhile; ?>
                            <div class="text-center">
                                <button type="submit" name="submit_quiz" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow">Valider le Quiz</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <footer class="bg-dark text-white py-5">
        <div class="container pt-4">
            <div class="row g-4">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none mb-3">
                        <img src="logo-nafas.png" alt="Nafas" height="35" class="me-2" >
                    </a>
                    <p class="text-white-50">
                        Une plateforme dédiée à la jeunesse tunisienne, offrant des alternatives concrètes et un espace d'expression pour construire un avenir meilleur, ici.
                    </p>
                </div>
                <div class="col-6 col-lg-2 offset-lg-2">
                    <h5 class="fw-bold mb-3">Navigation</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index1.php" class="text-white-50 text-decoration-none">À Propos</a></li>
                        <li class="mb-2"><a href="media.php" class="text-white-50 text-decoration-none">Médias</a></li>
                        <li class="mb-2"><a href="story_telling.php" class="text-white-50 text-decoration-none">Storytelling</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-white-50 text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5 class="fw-bold mb-3">Suivez-nous</h5>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle social-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle social-btn"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle social-btn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle social-btn"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-top border-secondary mt-5 pt-4 text-center text-white-50 small">
                &copy; 2025 Nafas. Tous droits réservés.
            </div>
        </div>
    </footer>

    <script>
        // --- THEME ---
        const themeBtn = document.getElementById('theme-toggle');
        const icon = themeBtn.querySelector('i');
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', savedTheme);
        icon.className = savedTheme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';

        themeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const current = document.documentElement.getAttribute('data-bs-theme');
            const target = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', target);
            localStorage.setItem('theme', target);
            icon.className = target === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
        });

        // --- LECTURE VOCALE ---
        function speakText(id) {
            const text = document.getElementById(id).innerText;
            const speech = new SpeechSynthesisUtterance(text);
            speech.lang = 'fr-FR';
            window.speechSynthesis.speak(speech);
        }

        // --- RECONNAISSANCE VOCALE (RECORD) ---
        function startVoiceRecord(qId) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    
    if (!SpeechRecognition) {
        alert("La reconnaissance vocale n'est pas supportée par votre navigateur (Utilisez Chrome ou Edge).");
        return;
    }

    const recognition = new SpeechRecognition();
    recognition.lang = 'fr-FR';
    recognition.continuous = false; // S'arrête dès qu'on a fini de parler
    recognition.interimResults = false;
    
    const btn = document.getElementById('mic-' + qId);
    btn.classList.add('listening');

    recognition.start();

    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript.toLowerCase();
        btn.classList.remove('listening');

        const options = ['a', 'b', 'c', 'd'];
        let found = false;

        options.forEach(opt => {
            const labelElement = document.getElementById('label-' + qId + '-' + opt.toUpperCase());
            if (labelElement) {
                const optionText = labelElement.innerText.toLowerCase();
                // Vérifie si l'utilisateur a dit la lettre seule ou le texte de la réponse
                if (transcript.includes('option ' + opt) || transcript.trim() === opt || transcript.includes(optionText)) {
                    document.getElementById('opt-' + qId + '-' + opt.toUpperCase()).checked = true;
                    found = true;
                }
            }
        });

        if (!found) {
            alert("J'ai entendu : '" + transcript + "'. Essayez de dire clairement 'Option A' ou 'B'.");
        }
    };

    recognition.onerror = function(event) {
    btn.classList.remove('listening');
    
    switch(event.error) {
        case 'network':
            alert("Erreur Réseau : La reconnaissance vocale nécessite une connexion internet stable sur Chrome/Edge.");
            break;
        case 'not-allowed':
            alert("Accès refusé : Veuillez autoriser l'utilisation du micro dans votre navigateur.");
            break;
        case 'no-speech':
            // On ne fait rien ou un petit message discret si personne n'a parlé
            console.log("Aucune voix détectée.");
            break;
        default:
            alert("Erreur de reconnaissance : " + event.error);
    }
};
}
    </script>
</body>
</html>