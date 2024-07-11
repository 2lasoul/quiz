<?php
require 'includes/db_connect.php';
require 'includes/functions.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quiz'])) {
    $id = $_GET['id']; // Récupérer l'ID du quiz à partir de l'URL
    $title = $_POST['title'];
    $description = $_POST['description'];
    $language_id = $_POST['language_id'];

    // Mettre à jour le quiz dans la base de données
    $stmt = $pdo->prepare("UPDATE quizzes SET title = ?, description = ?, language_id = ? WHERE id = ?");
    $stmt->execute([$title, $description, $language_id, $id]);

    // Redirection vers la page de gestion des quizzes après la mise à jour
    header('Location: manage_quizzes.php');
    exit;
}

// Récupérer les détails du quiz à éditer à partir de la base de données
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer la liste des langues disponibles pour le formulaire d'édition
$stmt_languages = $pdo->query("SELECT id, name FROM languages");
$languages = $stmt_languages->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">Éditer le Quiz</div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="form-group">
                <label for="title">Titre du quiz</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($quiz['title']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description du quiz</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($quiz['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="language_id">Langue du quiz</label>
                <select class="form-control" id="language_id" name="language_id" required>
                    <?php foreach ($languages as $language) : ?>
                        <option value="<?php echo $language['id']; ?>" <?php echo ($language['id'] == $quiz['language_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($language['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="update_quiz" class="btn btn-primary">Mettre à jour</button>
            <a href="manage_quizzes.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</div>


<?php include 'includes/footer.php'; ?>
