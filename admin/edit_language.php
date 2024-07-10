<?php
require 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'ID de langue non spécifié!',
            }).then(() => {
                window.location = 'manage_languages.php';
            });
          </script>";
    exit;
}

$id = $_GET['id'];

// Récupérer les informations de la langue à éditer
$stmt = $pdo->prepare("SELECT id, name, language_code FROM languages WHERE id = ?");
$stmt->execute([$id]);
$language = $stmt->fetch();

if (!$language) {
    echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Langue non trouvée!',
            }).then(() => {
                window.location = 'manage_languages.php';
            });
          </script>";
    exit;
}

// Mettre à jour les informations de la langue
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_language'])) {
    $name = $_POST['name'];
    $language_code = $_POST['language_code'];

    $stmt = $pdo->prepare("UPDATE languages SET name = ?, language_code = ? WHERE id = ?");
    $stmt->execute([$name, $language_code, $id]);

    echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Langue mise à jour avec succès!',
            }).then(() => {
                window.location = 'manage_languages.php';
            });
          </script>";
}
?>

<div class="card">
    <div class="card-header">Éditer une Langue</div>
    <div class="card-body">
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Nom de la langue</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($language['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="language_code">Code de la langue</label>
                <input type="text" class="form-control" id="language_code" name="language_code" value="<?php echo htmlspecialchars($language['language_code']); ?>" required>
            </div>
            <button type="submit" name="update_language" class="btn btn-primary">Mettre à jour</button>
            <a href="manage_languages.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
