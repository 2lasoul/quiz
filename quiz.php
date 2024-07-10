<?php
// Inclure le fichier de connexion à la base de données
require 'admin/includes/db_connect.php';

// Récupérer le hash du quiz à partir de l'URL
$quiz_hash = isset($_GET['hash']) ? $_GET['hash'] : '';

// Sélectionner les informations du quiz et sa première question associée
$stmt = $pdo->prepare("SELECT q.title AS quiz_title, q.description AS quiz_description, qu.id AS question_id, qu.question_text AS question_text, qu.question_type AS question_type, a.id AS answer_id, a.answer_text AS answer_text, a.is_correct AS is_correct
                      FROM quizzes q
                      LEFT JOIN questions qu ON q.id = qu.quiz_id
                      LEFT JOIN answers a ON qu.id = a.question_id
                      WHERE q.hash = ?");
$stmt->execute([$quiz_hash]);
$quiz_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <link rel="stylesheet" href="styles.css">
    <script src="admin/js/jquery.min.js"></script>
    <script src="admin/js/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="admin/css/sweetalert2.min.css">
</head>
<body>

<div class="quiz-container">
    <?php if (!empty($quiz_data)): ?>
        <h1><?php echo htmlspecialchars($quiz_data[0]['quiz_title']); ?></h1>
        <p><?php echo htmlspecialchars($quiz_data[0]['quiz_description']); ?></p>
        
        <div class="question">
            <h2><?php echo htmlspecialchars($quiz_data[0]['question_text']); ?></h2>
            <ul>
                <?php 
                foreach ($quiz_data as $row): ?>
                    <?php if ($row['question_id'] == $quiz_data[0]['question_id']): ?>
                        <li>
                            <label>
                                <input type="radio" name="answer" value="<?php echo htmlspecialchars($row['answer_id']); ?>" data-correct="<?php echo $row['is_correct'] ? 'true' : 'false'; ?>">
                                <?php echo htmlspecialchars($row['answer_text']); ?>
                            </label>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>

        <button id="btn-next">Suivant</button>

    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    $('#btn-next').on('click', function() {
        // Vérifier si une réponse a été sélectionnée
        var selectedAnswer = $('input[name="answer"]:checked').val();
        if (!selectedAnswer) {
            Swal.fire({
                icon: 'question',
                title: 'Oops...',
                text: 'Vous devez sélectionner une réponse',
            });
            return;
        }

        // Vérifier si la réponse sélectionnée est correcte
        var isCorrect = $('input[name="answer"]:checked').data('correct') === true;

        // Afficher une alerte ou une modal avec le résultat
        if (isCorrect) {
            Swal.fire({
                icon: 'success',
                title: 'Bonne réponse !',
                text: 'Vous avez sélectionné la bonne réponse.'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Mauvaise réponse...',
                text: 'Ce n\'est pas la bonne réponse.'
            });
        }
    });
});
</script>

</body>
</html>
