<?php
// Inclure le fichier de connexion à la base de données
require 'admin/includes/db_connect.php';

// Récupérer le hash du quiz et l'identifiant de la question actuelle à partir de l'URL
$quiz_hash = isset($_GET['hash']) ? $_GET['hash'] : '';
$current_question_id = isset($_GET['question_id']) ? (int)$_GET['question_id'] : 0;

// Sélectionner les informations du quiz et des questions
$stmt = $pdo->prepare("SELECT q.title AS quiz_title, q.description AS quiz_description, qu.id AS question_id, qu.question_text AS question_text, qu.question_type AS question_type, a.id AS answer_id, a.answer_text AS answer_text, a.is_correct AS is_correct
                      FROM quizzes q
                      LEFT JOIN questions qu ON q.id = qu.quiz_id
                      LEFT JOIN answers a ON qu.id = a.question_id
                      WHERE q.hash = ?
                      ORDER BY qu.id, a.id");
$stmt->execute([$quiz_hash]);
$quiz_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtenir le nombre total de questions du quiz
$stmt_total = $pdo->prepare("SELECT COUNT(*) AS total_questions FROM questions WHERE quiz_id = (SELECT id FROM quizzes WHERE hash = ?)");
$stmt_total->execute([$quiz_hash]);
$total_questions = $stmt_total->fetchColumn();

// Extraire les questions uniques et déterminer la question actuelle
$questions = [];
foreach ($quiz_data as $row) {
    if (!isset($questions[$row['question_id']])) {
        $questions[$row['question_id']] = $row['question_text'];
    }
}
$question_ids = array_keys($questions);

// Déterminer le numéro de la question actuelle et l'identifiant de la question suivante
$current_question_index = array_search($current_question_id, $question_ids);
$current_question_number = $current_question_index !== false ? $current_question_index + 1 : 1;
$next_question_id = $current_question_index !== false && isset($question_ids[$current_question_index + 1]) ? $question_ids[$current_question_index + 1] : 0;

// Récupérer le titre et la description du quiz pour affichage
$quiz_title = isset($quiz_data[0]['quiz_title']) ? $quiz_data[0]['quiz_title'] : '';
$quiz_description = isset($quiz_data[0]['quiz_description']) ? $quiz_data[0]['quiz_description'] : '';
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
        <h1><?php echo htmlspecialchars($quiz_title); ?></h1>
        <p><?php echo htmlspecialchars($quiz_description); ?></p>
        
        <div class="question">
            <h2><?php echo htmlspecialchars($questions[$current_question_id]); ?></h2>
            <p>Question <?php echo $current_question_number; ?> sur <?php echo $total_questions; ?></p>
            <ul>
                <?php foreach ($quiz_data as $row): ?>
                    <?php if ($row['question_id'] == $current_question_id): ?>
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
        var nextQuestionId = <?php echo json_encode($next_question_id); ?>;
        var quizHash = <?php echo json_encode($quiz_hash); ?>;
        var alertOptions = {
            icon: isCorrect ? 'success' : 'error',
            title: isCorrect ? 'Bonne réponse !' : 'Mauvaise réponse...',
            text: isCorrect ? 'Vous avez sélectionné la bonne réponse.' : 'Ce n\'est pas la bonne réponse.',
            timer: 3000,
            showConfirmButton: false,
            willClose: () => {
                // Rediriger vers la question suivante
                if (nextQuestionId) {
                    window.location.href = 'quiz.php?hash=' + quizHash + '&question_id=' + nextQuestionId;
                } else {
                    // Afficher un message de fin de quiz
                    Swal.fire({
                        icon: 'info',
                        title: 'Quiz terminé',
                        text: 'Vous avez terminé le quiz.',
                    });
                }
            }
        };

        Swal.fire(alertOptions);
    });
});
</script>

</body>
</html>
