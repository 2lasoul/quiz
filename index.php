<?php
// Inclure le fichier de connexion à la base de données
require 'admin/includes/db_connect.php';
require 'includes/functions.php';

// Vérifier la présence de hash et question_id dans l'URL
if (!isset($_GET['hash']) || !isset($_GET['question_id'])) {
    // Rediriger vers une page d'erreur
    header('Location: erreur.php');
    exit; // Arrêter l'exécution du script
}

// Récupérer le hash du quiz et l'identifiant de la question actuelle à partir de l'URL
$quiz_hash = $_GET['hash'];
$current_question_id = (int)$_GET['question_id'];

// Sélectionner les informations du quiz et des questions
$stmt = $pdo->prepare("SELECT q.title AS quiz_title, q.description AS quiz_description, qu.id AS question_id, qu.question_text AS question_text, qu.question_type AS question_type, a.id AS answer_id, a.answer_text AS answer_text, a.is_correct AS is_correct
                      FROM quizzes q
                      LEFT JOIN questions qu ON q.id = qu.quiz_id
                      LEFT JOIN answers a ON qu.id = a.question_id
                      WHERE q.hash = ?
                      ORDER BY qu.id, a.id");
$stmt->execute([$quiz_hash]);
$quiz_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si des données de quiz ont été trouvées
if (empty($quiz_data)) {
    // Rediriger vers une page d'erreur ou autre gestion d'erreur appropriée
    header('Location: erreur.php');
    exit; // Arrêter l'exécution du script
}

// Obtenir le nombre total de questions du quiz
$stmt_total = $pdo->prepare("SELECT COUNT(*) AS total_questions FROM questions WHERE quiz_id = (SELECT id FROM quizzes WHERE hash = ?)");
$stmt_total->execute([$quiz_hash]);
$total_questions = $stmt_total->fetchColumn();

// Extraire les questions uniques et déterminer la question actuelle
$questions = [];
foreach ($quiz_data as $row) {
    if (!isset($questions[$row['question_id']])) {
        $questions[$row['question_id']] = [
            'text' => $row['question_text'],
            'type' => $row['question_type']
        ];
    }
}
$question_ids = array_keys($questions);

// Vérifier si l'identifiant de la question actuelle est valide
if (!in_array($current_question_id, $question_ids)) {
    // Rediriger vers une page d'erreur ou autre gestion d'erreur appropriée
    header('Location: erreur.php');
    exit; // Arrêter l'exécution du script
}

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
    <h1><?php echo htmlspecialchars($quiz_title); ?></h1>
    <p><?php echo htmlspecialchars($quiz_description); ?></p>
    
    <div class="question">
        <h2><?php echo htmlspecialchars($questions[$current_question_id]['text']); ?></h2>
        <p>Question <?php echo $current_question_number; ?> sur <?php echo $total_questions; ?></p>
        <ul>
            <?php foreach ($quiz_data as $row): ?>
                <?php if ($row['question_id'] == $current_question_id): ?>
                    <?php if ($questions[$current_question_id]['type'] === 'single'): ?>
                        <li>
                            <label>
                                <input type="radio" name="answer" value="<?php echo htmlspecialchars($row['answer_id']); ?>" data-correct="<?php echo $row['is_correct'] ? 'true' : 'false'; ?>">
                                <?php echo htmlspecialchars($row['answer_text']); ?>
                            </label>
                        </li>
                    <?php elseif ($questions[$current_question_id]['type'] === 'multiple'): ?>
                        <li>
                            <label>
                                <input type="checkbox" name="answer[]" value="<?php echo htmlspecialchars($row['answer_id']); ?>" data-correct="<?php echo $row['is_correct'] ? 'true' : 'false'; ?>">
                                <?php echo htmlspecialchars($row['answer_text']); ?>
                            </label>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>

    <button id="btn-next">Suivant</button>
</div>

<script>
$(document).ready(function() {
    var score = 0; // Ajouter une variable pour stocker le score

    // Récupérer le paramètre score de l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const urlScore = urlParams.get('score');

    // Mettre à jour la variable score
    if (urlScore) {
        score = parseInt(urlScore);
    }

    $('#btn-next').on('click', function() {
        // Récupérer le type de question
        var questionType = <?php echo json_encode($questions[$current_question_id]['type']); ?>;

        // Initialiser les variables
        var selectedAnswer = $('input[name="answer"]:checked').val();
        var selectedCheckboxes = $('input[name="answer[]"]:checked');
        var atLeastOneChecked = selectedCheckboxes.length > 0;

        // Vérification de la sélection
        if (questionType === 'single' && !selectedAnswer) {
            Swal.fire({
                icon: 'question',
                title: 'Oops...',
                text: 'Vous devez sélectionner une réponse',
            });
            return;
        }

        if (questionType === 'multiple' && !atLeastOneChecked) {
            Swal.fire({
                icon: 'question',
                title: 'Oops...',
                text: 'Vous devez sélectionner au moins une réponse',
            });
            return;
        }

        // Vérification des réponses
        var isCorrect = false;
        var correctCount = 0;
        var incorrectCount = 0;
        var nextQuestionId = <?php echo json_encode($next_question_id); ?>;
        var quizHash = <?php echo json_encode($quiz_hash); ?>;

        var alertOptions = {
            timer: 3000,
            showConfirmButton: false,
            willClose: () => {
                // Rediriger vers la question suivante ou vers le résultat
                if (nextQuestionId) {
                    window.location.href = 'index.php?hash=' + quizHash + '&question_id=' + nextQuestionId + '&score=' + score;
                } else {
                    // Afficher un message de fin de quiz avec le score total
                    Swal.fire({
                        icon: 'info',
                        title: 'Quiz terminé',
                        confirmButtonText: "Découvrir mon score",
                        showClass: {
                            popup: `
                                animate__animated
                                animate__fadeInUp
                                animate__faster
                            `
                        },
                    }).then((result) => {
                        window.location.href = 'resultat.php?hash=' + encodeURIComponent(quizHash) + '&score=' + encodeURIComponent(score);
                    });
                }
            }
        };

        if (questionType === 'single') {
            isCorrect = $('input[name="answer"]:checked').data('correct') === true;
            if (isCorrect) {
                score += 1; // Ajouter 1 point au score
            }
            // Mettre à jour l'affichage du score
            alertOptions.icon = isCorrect ? 'success' : 'error';
            alertOptions.title = isCorrect ? 'Bonne réponse !' : 'Mauvaise réponse...';
            alertOptions.text = isCorrect ? 'Vous avez sélectionné la bonne réponse.' : 'Vous avez sélectionné la mauvaise réponse.';
        }
        else if (questionType === 'multiple') {
            selectedCheckboxes.each(function() {
                if ($(this).data('correct') === true) {
                    correctCount++;
                } else {
                    incorrectCount++;
                }
            });
            var totalCorrectAnswers = $('input[name="answer[]"][data-correct="true"]').length;

            if (correctCount === totalCorrectAnswers) {
                isCorrect = true;
                score += 0.5 * correctCount; // Ajouter 0.5 point par réponse correcte
            } else if (correctCount > 0) {
                score += 0.5 * correctCount; // Ajouter 0.5 point par réponse correcte, mais ne pas compter la question comme complètement correcte
            }
            // Mettre à jour l'affichage du score

            if (isCorrect) {
                alertOptions.icon = 'success';
                alertOptions.title = 'Bonne réponse !';
                alertOptions.text = 'Vous avez sélectionné toutes les bonnes réponses.';
            } else if (correctCount > 0) {
                alertOptions.icon = 'info';
                alertOptions.title = 'Pas tout à fait...';
                alertOptions.text = 'Vous avez bien sélectionné ' + correctCount + ' bonne(s) réponse(s), mais il en manque une ou plusieurs.';
            } else {
                alertOptions.icon = 'error';
                alertOptions.title = 'Mauvaise réponse...';
                alertOptions.text = 'Vous avez sélectionné une ou plusieurs mauvaises réponses.';
            }
        }

        // Afficher une alerte ou une modal avec le résultat
        Swal.fire(alertOptions);
    });
});
</script>

</body>
</html>
