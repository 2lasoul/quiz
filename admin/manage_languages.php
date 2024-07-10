<?php
require 'includes/db_connect.php';
include 'includes/header.php';

// Ajouter une langue
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_language'])) {
    $name = $_POST['name'];
    $language_code = $_POST['language_code'];

    $stmt = $pdo->prepare("INSERT INTO languages (name, language_code) VALUES (?, ?)");
    $stmt->execute([$name, $language_code]);

    echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Langue ajoutée avec succès!',
            }).then(() => {
                window.location = 'manage_languages.php';
            });
          </script>";
}

// Supprimer une langue
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // Préparer les données de la langue pour afficher dans SweetAlert
    $stmt = $pdo->prepare("SELECT name FROM languages WHERE id = ?");
    $stmt->execute([$id]);
    $language = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($language) {
        // Afficher la boîte de dialogue SweetAlert pour confirmation de suppression
        echo "<script>confirmDelete($id);</script>";
    }
}

// Confirmer la suppression après l'utilisation de SweetAlert
if (isset($_GET['confirmed_delete_id'])) {
    $id = $_GET['confirmed_delete_id'];
    
    $stmt = $pdo->prepare("DELETE FROM languages WHERE id = ?");
    $stmt->execute([$id]);

    echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Langue supprimée avec succès!',
            }).then(() => {
                window.location = 'manage_languages.php';
            });
          </script>";
}

// Récupérer les langues existantes
$stmt = $pdo->query("SELECT id, name, language_code FROM languages");
$languages = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">Ajouter une Langue</div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Nom de la langue</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="language_code">Code de la langue</label>
                <input type="text" class="form-control" id="language_code" name="language_code" required>
            </div>
            <button type="submit" name="add_language" class="btn btn-primary">Ajouter</button>
        </form>
    </div>
</div>

<div class="mt-5">
    <h2>Liste des Langues</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Code</th>
                <th>Nom</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($languages as $language) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($language['language_code']); ?></td>
                    <td><?php echo htmlspecialchars($language['name']); ?></td>
                    <td>
                        <a href="edit_language.php?id=<?php echo $language['id']; ?>" class="btn btn-primary btn-sm">Éditer</a>
                        <button class="btn btn-danger btn-sm" onclick="deleteLanguage(<?php echo $language['id']; ?>)">Supprimer</button>
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
                window.location = 'manage_languages.php?confirmed_delete_id=' + id;
            }
        });
    }

    function deleteLanguage(id) {
        confirmDelete(id); // Utilisation de la fonction confirmDelete pour la suppression
    }
</script>

<?php include 'includes/footer.php'; ?>
