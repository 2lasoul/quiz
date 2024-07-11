<?php
function getTranslations($pdo, $language_code) {
    $stmt = $pdo->prepare("SELECT t.key, t.value FROM translation t JOIN languages l ON t.language_id = l.id WHERE l.language_code = ?");
    $stmt->execute([$language_code]);
    $translations = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    return $translations;
}
