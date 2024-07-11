<?php
require 'admin/includes/db_connect.php';

// Récupérer le hash du quiz depuis l'URL
$quiz_hash = isset($_GET['hash']) ? $_GET['hash'] : '';

// Sélectionner les informations du quiz
$stmt = $pdo->prepare("SELECT q.title AS quiz_title, q.description AS quiz_description
                      FROM quizzes q
                      WHERE q.hash = ?");
$stmt->execute([$quiz_hash]);
$quiz_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si le quiz existe et le score est défini
if (!$quiz_data || !isset($_GET['score'])) {
    // Afficher une alerte d'erreur avec SweetAlert
    echo '<script>';
    echo 'alert("Une erreur s\'est produite. Redirection vers la première question du quiz...");';
    echo 'window.location.href = "index.php";'; // Redirection vers la première question du quiz
    echo '</script>';
    exit; // Arrêter l'exécution du reste du code
}

// Assurer que le score est un entier
$score = (float)$_GET['score'];

// Obtenir le nombre total de questions du quiz
$stmt_total = $pdo->prepare("SELECT COUNT(*) AS total_questions FROM questions WHERE quiz_id = (SELECT id FROM quizzes WHERE hash = ?)");
$stmt_total->execute([$quiz_hash]);
$total_questions = $stmt_total->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat du Quiz</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
            color: #333;
        }
        h1 {
            font-size: 24px;
        }
        h2 {
            font-size: 18px;
        }
        p {
            font-size: 14px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

<div class="quiz-container">
    <h1>Résultat du Quiz : <?php echo htmlspecialchars($quiz_data['quiz_title']); ?></h1>

    <h2>Votre score est de : <?php echo $score; ?> sur <?php echo $total_questions; ?></h2>

    <a href="index.php?hash=<?php echo htmlspecialchars($quiz_hash); ?>" class="btn">Refaire le Quiz</a>

</div>

</body>
</html>
