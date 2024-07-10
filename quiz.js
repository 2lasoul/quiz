document.addEventListener('DOMContentLoaded', function() {
    const quizContainer = document.getElementById('quiz-questions');
    const nextButton = document.getElementById('next-button');
    const submitButton = document.getElementById('submit-button');
    const restartButton = document.getElementById('restart-button');
    const quizResult = document.getElementById('quiz-result');
    let currentQuestionIndex = 0;
    let score = 0;
    let questions = [];

    // Fonction pour charger les questions depuis le serveur
    function loadQuestions() {
        // Exemple de requête AJAX pour récupérer les questions depuis le serveur
        fetch('get_quiz_questions.php')
            .then(response => response.json())
            .then(data => {
                questions = data.questions;
                showQuestion(currentQuestionIndex);
            })
            .catch(error => console.error('Erreur lors du chargement des questions:', error));
    }

    // Fonction pour afficher une question spécifique
    function showQuestion(index) {
        const question = questions[index];
        quizContainer.innerHTML = `
            <h2>${question.question_text}</h2>
            <ul>
                ${question.answers.map(answer => `
                    <li>
                        <input type="radio" name="answer" value="${answer.id}">
                        <label>${answer.answer_text}</label>
                    </li>
                `).join('')}
            </ul>
        `;
    }

    // Fonction pour vérifier la réponse de l'utilisateur
    function checkAnswer(answerId) {
        const question = questions[currentQuestionIndex];
        const correctAnswer = question.answers.find(answer => answer.is_correct);
        return answerId === correctAnswer.id;
    }

    // Fonction pour afficher le résultat du quiz
    function showResult() {
        quizContainer.style.display = 'none';
        nextButton.style.display = 'none';
        submitButton.style.display = 'none';

        const resultPercentage = Math.round((score / questions.length) * 100);
        quizResult.innerHTML = `
            <h2>Votre score : ${score} / ${questions.length} (${resultPercentage}%)</h2>
            <button class="btn btn-primary" onclick="restartQuiz()">Recommencer</button>
        `;
        quizResult.style.display = 'block';
        restartButton.style.display = 'block';
    }

    // Fonction pour recommencer le quiz
    function restartQuiz() {
        currentQuestionIndex = 0;
        score = 0;
        quizResult.style.display = 'none';
        restartButton.style.display = 'none';
        quizContainer.style.display = 'block';
        nextButton.style.display = 'block';
        submitButton.style.display = 'none';
        loadQuestions();
    }

    // Événement lorsque l'utilisateur clique sur le bouton "Suivante"
    nextButton.addEventListener('click', function() {
        const selectedAnswer = document.querySelector('input[name="answer"]:checked');
        if (!selectedAnswer) {
            alert('Veuillez sélectionner une réponse.');
            return;
        }

        const isCorrect = checkAnswer(parseInt(selectedAnswer.value, 10));
        if (isCorrect) {
            score++;
        }

        currentQuestionIndex++;
        if (currentQuestionIndex < questions.length) {
            showQuestion(currentQuestionIndex);
        } else {
            showResult();
        }
    });

    // Chargement initial des questions au chargement de la page
    loadQuestions();
});
