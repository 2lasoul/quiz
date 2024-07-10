<?php
require 'includes/db_connect.php';
include 'includes/header.php';

if (isset($_GET['quiz_id'])) {
    $quiz_id = $_GET['quiz_id'];
    echo "Quiz ID: " . $quiz_id;
} else {
    echo "quiz id existe pas";
    die('Aucun ID de quiz fourni!');
}

// Assurez-vous que $quiz_id est un nombre entier valide
$quiz_id = filter_var($quiz_id, FILTER_VALIDATE_INT);
if ($quiz_id === false || $quiz_id <= 0) {
    die('ID de quiz invalide!');
}

// Récupérer l'ID de la question et de l'ID du quiz à partir de l'URL
$question_id = isset($_GET['id']) ? $_GET['id'] : null;
$quiz_id = isset($_GET['quiz_id']) ? $_GET['quiz_id'] : null;

// Vérifier si les ID sont présents
if (!$question_id || !$quiz_id) {
    die('ID de question ou ID de quiz manquant.');
}

// Récupérer la question existante
$stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->execute([$question_id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

// Si la question n'existe pas, afficher une erreur
if (!$question) {
    die('Question introuvable.');
}

// Récupérer les réponses existantes pour cette question
$stmt_answers = $pdo->prepare("SELECT * FROM answers WHERE question_id = ?");
$stmt_answers->execute([$question_id]);
$answers = $stmt_answers->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_question'])) {
    $question_text = $_POST['question_text'];
    $question_type = $_POST['question_type'];

    // Mettre à jour la question
    $stmt = $pdo->prepare("UPDATE questions SET question_text = ?, question_type = ? WHERE id = ?");
    $stmt->execute([$question_text, $question_type, $question_id]);

    // Mettre à jour les réponses
    foreach ($_POST['answers'] as $answer_id => $answer_data) {
        $answer_text = $answer_data['text'];
        $is_correct = isset($answer_data['is_correct']) ? 1 : 0;
        $stmt = $pdo->prepare("UPDATE answers SET answer_text = ?, is_correct = ? WHERE id = ?");
        $stmt->execute([$answer_text, $is_correct, $answer_id]);
    }

    // Afficher un message de succès et rediriger
    echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Question mise à jour avec succès!',
            }).then(() => {
                window.location = 'manage_questions.php?quiz_id={$quiz_id}';
            });
          </script>";
    exit;
}
?>

<div class="card">
    <div class="card-header">Modifier une Question</div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="form-group">
                <label for="question_text">Texte de la Question</label>
                <textarea class="form-control" id="question_text" name="question_text" rows="3" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="question_type">Type de Question</label>
                <select class="form-control" id="question_type" name="question_type" required>
                    <option value="single" <?php if ($question['question_type'] == 'single') echo 'selected'; ?>>Choix Unique</option>
                    <option value="multiple" <?php if ($question['question_type'] == 'multiple') echo 'selected'; ?>>Choix Multiple</option>
                </select>
            </div>
            <hr>
            <h4>Réponses</h4>
            <?php foreach ($answers as $answer) : ?>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="answers[<?php echo $answer['id']; ?>][text]" value="<?php echo htmlspecialchars($answer['answer_text']); ?>" placeholder="Texte de la réponse" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <input type="checkbox" class="is-correct" name="answers[<?php echo $answer['id']; ?>][is_correct]" <?php if ($answer['is_correct']) echo 'checked'; ?>>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="form-group">
            <button onclick="event.preventDefault();history.back(-1)" class="btn btn-secondary">Annuler</button>
            <button type="submit" name="update_question" class="btn btn-primary">Mettre à Jour la Question</button>
            </div>

        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
