<?php
require 'includes/db_connect.php';
include 'includes/header.php';

// Vérifier si un quiz ID est fourni
if (!isset($_GET['id'])) {
    echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Aucun ID de quiz fourni!',
            }).then(() => {
                window.location = 'manage_quizzes.php';
            });
          </script>";
    exit;
}

$quiz_id = $_GET['id'];

// Ajouter une question
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_question'])) {
    $question_text = $_POST['question_text'];
    $question_type = $_POST['question_type']; // 'single' ou 'multiple'

    $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, question_type) VALUES (?, ?, ?)");
    $stmt->execute([$quiz_id, $question_text, $question_type]);

    $question_id = $pdo->lastInsertId();

    // Ajouter les réponses
    foreach ($_POST['answers'] as $answer) {
        $is_correct = isset($answer['is_correct']) ? 1 : 0;
        $stmt = $pdo->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
        $stmt->execute([$question_id, $answer['text'], $is_correct]);
    }

    echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Question ajoutée avec succès!',
            }).then(() => {
                window.location = 'manage_questions.php?id=$quiz_id';
            });
          </script>";
}

// Supprimer une question
if (isset($_GET['delete_id'])) {
    $question_id = $_GET['delete_id'];

    // Supprimer les réponses associées
    $stmt = $pdo->prepare("DELETE FROM answers WHERE question_id = ?");
    $stmt->execute([$question_id]);

    // Supprimer la question
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$question_id]);

    echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Question supprimée avec succès!',
            }).then(() => {
                window.location = 'manage_questions.php?id=$quiz_id';
            });
          </script>";
}

// Récupérer la liste des questions et réponses pour ce quiz
$stmt = $pdo->prepare("SELECT q.id, q.question_text, q.question_type, a.id as answer_id, a.answer_text, a.is_correct 
                       FROM questions q
                       LEFT JOIN answers a ON q.id = a.question_id
                       WHERE q.quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Grouper les réponses par question
$grouped_questions = [];
foreach ($questions as $question) {
    if (!isset($grouped_questions[$question['id']])) {
        $grouped_questions[$question['id']] = [
            'question_text' => $question['question_text'],
            'question_type' => $question['question_type'],
            'answers' => []
        ];
    }
    if ($question['answer_id']) {
        $grouped_questions[$question['id']]['answers'][] = [
            'answer_id' => $question['answer_id'],
            'answer_text' => $question['answer_text'],
            'is_correct' => $question['is_correct']
        ];
    }
}

// Récupérer le titre du quiz
$stmt = $pdo->prepare("SELECT title FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">Ajouter une Question au Quiz: <?php echo htmlspecialchars($quiz['title']); ?></div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="form-group">
                <label for="question_text">Texte de la question</label>
                <input type="text" class="form-control" id="question_text" name="question_text" required>
            </div>
            <div class="form-group">
                <label for="question_type">Type de réponse</label>
                <select class="form-control" id="question_type" name="question_type" required onchange="handleQuestionTypeChange(this.value)">
                    <option value="single">Choix unique</option>
                    <option value="multiple">Choix multiple</option>
                </select>
            </div>
            <div class="form-group">
                <label>Réponses</label>
                <div id="answers">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="answers[0][text]" placeholder="Texte de la réponse" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <input type="checkbox" class="is-correct" name="answers[0][is_correct]" onclick="handleSingleChoice(this)">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" onclick="addAnswer()">Ajouter une réponse</button>
            </div>
            <button type="submit" name="add_question" class="btn btn-primary">Ajouter une Question</button>
        </form>
    </div>
</div>

<div class="mt-5">
    <h2>Liste des Questions</h2>
    <?php foreach ($grouped_questions as $question_id => $question): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($question['question_text']); ?> (<?php echo $question['question_type'] == 'single' ? 'Choix unique' : 'Choix multiple'; ?>)</h5>
                <ul class="list-group list-group-flush">
                    <?php foreach ($question['answers'] as $answer): ?>
                        <li class="list-group-item">
                            <?php echo htmlspecialchars($answer['answer_text']); ?>
                            <?php if ($answer['is_correct']): ?>
                                <span class="badge badge-success">Correcte</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-3">
                    <a href="edit_question.php?id=<?php echo $question_id; ?>&quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary btn-sm">Éditer</a>
                    <button class="btn btn-danger btn-sm" onclick="deleteQuestion(<?php echo $question_id; ?>)">Supprimer</button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function addAnswer() {
        const answersDiv = document.getElementById('answers');
        const index = answersDiv.children.length;
        const answerHtml = `
            <div class="input-group mb-3">
                <input type="text" class="form-control" name="answers[${index}][text]" placeholder="Texte de la réponse" required>
                <div class="input-group-append">
                    <div class="input-group-text">
                        <input type="checkbox" class="is-correct" name="answers[${index}][is_correct]" onclick="handleSingleChoice(this)">
                    </div>
                </div>
            </div>
        `;
        answersDiv.insertAdjacentHTML('beforeend', answerHtml);
    }

    function handleSingleChoice(checkbox) {
        const questionType = document.getElementById('question_type').value;
        if (questionType === 'single' && checkbox.checked) {
            const checkboxes = document.querySelectorAll('.is-correct');
            checkboxes.forEach((cb) => {
                if (cb !== checkbox) {
                    cb.checked = false;
                }
            });
        }
    }

    function handleQuestionTypeChange(type) {
        const checkboxes = document.querySelectorAll('.is-correct');
        if (type === 'single') {
            let checkedFound = false;
            checkboxes.forEach((cb) => {
                if (checkedFound) {
                    cb.checked = false;
                } else if (cb.checked) {
                    checkedFound = true;
                }
            });
        }
    }

    function deleteQuestion(id) {
        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: 'Vous ne pourrez pas revenir en arrière !',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, supprimer !'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location = 'manage_questions.php?id=<?php echo $quiz_id; ?>&delete_id=' + id;
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>
