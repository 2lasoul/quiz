<?php
require 'includes/db_connect.php';
include 'includes/header.php';

// Fonction pour générer un hash unique pour chaque quiz
function generate_quiz_hash() {
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
}

// Ajouter un quiz
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_quiz'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $language_id = $_POST['language_id'];
    $hash = generate_quiz_hash();

    $stmt = $pdo->prepare("INSERT INTO quizzes (title, description, language_id, hash) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $language_id, $hash]);

    echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Quiz ajouté avec succès!',
            }).then(() => {
                window.location = 'manage_quizzes.php';
            });
          </script>";
}

// Supprimer un quiz
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // Préparer les données du quiz pour afficher dans SweetAlert
    $stmt = $pdo->prepare("SELECT title FROM quizzes WHERE id = ?");
    $stmt->execute([$id]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($quiz) {
        // Afficher la boîte de dialogue SweetAlert pour confirmation de suppression
        echo "<script>confirmDelete($id);</script>";
    }
}

// Confirmer la suppression après l'utilisation de SweetAlert
if (isset($_GET['confirmed_delete_id'])) {
    $id = $_GET['confirmed_delete_id'];

    // Supprimer le quiz de la base de données
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->execute([$id]);

    // Affichage d'une alerte SweetAlert après suppression
    echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Quiz supprimé avec succès!',
            }).then(() => {
                window.location = 'manage_quizzes.php';
            });
          </script>";
}

// Récupérer la liste des quizzes existants avec leur langue et URL
$stmt = $pdo->query("SELECT q.id, q.title, q.description, q.hash, l.language_code 
                     FROM quizzes q 
                     JOIN languages l ON q.language_id = l.id");
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des langues disponibles pour le formulaire d'ajout
$stmt_languages = $pdo->query("SELECT id, name FROM languages");
$languages = $stmt_languages->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header">Ajouter un Quiz</div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="form-group">
                <label for="title">Titre du quiz</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Description du quiz</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="language_id">Langue du quiz</label>
                <select class="form-control" id="language_id" name="language_id" required>
                    <?php foreach ($languages as $language) : ?>
                        <option value="<?php echo $language['id']; ?>"><?php echo htmlspecialchars($language['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="add_quiz" class="btn btn-primary">Ajouter un Quiz</button>
        </form>
    </div>
</div>

<div class="mt-5">
    <h2>Liste des Quizzes</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Langue</th>
                <th>Titre</th>
                <th>Description</th>
                <th>URL</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($quizzes as $quiz) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($quiz['language_code']); ?></td>
                    <td><?php echo htmlspecialchars(strlen($quiz['title']) > 50 ? substr($quiz['title'], 0, 30) . '...' : $quiz['title']); ?></td>
                    <td><?php echo htmlspecialchars(strlen($quiz['description']) > 50 ? substr($quiz['description'], 0, 50) . '...' : $quiz['description']); ?></td>
                    <td>

                        <a href="/quiz.php?hash=<?php echo $quiz['hash']; ?>" class="copy-link">Voir le Quiz</a>
                        <button class="btn btn-sm btn-outline-secondary" data-target=".copy-link">Copier</button>

                    </td>
                    <td>
                        <a href="edit_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary btn-sm">Éditer</a>
                        <button class="btn btn-danger btn-sm" onclick="deleteQuiz(<?php echo $quiz['id']; ?>)">Supprimer</button>
                        <a href="manage_questions.php?id=<?php echo $quiz['id']; ?>" class="btn btn-info btn-sm">Éditer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function confirmDelete(id) {
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
                window.location = 'manage_quizzes.php?confirmed_delete_id=' + id;
            }
        });
    }

    function deleteQuiz(id) {
        confirmDelete(id); // Appeler la fonction confirmDelete pour la suppression du quiz
    }

    var copyButtons = document.querySelectorAll('button[data-target=".copy-link"]');

// Parcourir tous les boutons "Copier" et ajouter un écouteur d'événements pour le clic
copyButtons.forEach(function(button) {
  button.addEventListener('click', function() {
    // Récupérer le lien hypertexte correspondant au bouton "Copier" cliqué
    var link = this.previousElementSibling;
    var url = link.href;

    // Créer un élément temporaire pour copier le texte
    var tempInput = document.createElement('input');
    tempInput.value = url;
    document.body.appendChild(tempInput);

    // Sélectionner le texte et le copier dans le presse-papiers
    tempInput.select();
    document.execCommand('copy');

    // Supprimer l'élément temporaire
    document.body.removeChild(tempInput);

    // Utiliser SweetAlert pour afficher un message de confirmation
    Swal.fire({
        icon: 'success',
        title: 'Copié !',
        text: 'URL copiée dans le presse-papiers.',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    });
  });
});

</script>

<?php include 'includes/footer.php'; ?>
