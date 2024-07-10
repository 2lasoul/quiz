<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz en ligne</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="quiz-container">
    <div class="quiz-header">
        <h1 id="quiz-title">Quiz sur le Soleil</h1>
        <p id="quiz-description">Testez vos connaissances sur le Soleil avec notre quiz interactif !</p>
    </div>

    <div id="quiz-questions" class="quiz-body">
        <!-- Les questions du quiz seront chargées ici dynamiquement depuis le serveur -->
    </div>

    <div id="quiz-result" class="quiz-result" style="display: none;">
        <!-- Résultat du quiz sera affiché ici après soumission -->
    </div>

    <div id="quiz-buttons" class="quiz-footer">
        <button id="next-button" class="btn btn-primary">Suivante</button>
        <button id="submit-button" class="btn btn-success" style="display: none;">Soumettre</button>
        <button id="restart-button" class="btn btn-secondary" style="display: none;">Recommencer</button>
    </div>
</div>

<script src="quiz.js"></script>
</body>
</html>
