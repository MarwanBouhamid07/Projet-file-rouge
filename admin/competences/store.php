<?php
/**
 * Admin Compétences - Store (Create handler)
 * Sprint 1 - Recruitment Platform
 */

require_once __DIR__ . '/../../includes/admin_auth_check.php';
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$errors = [];
$old = $_POST;
$nom = trim($_POST['nom'] ?? '');

// 1. Validate Nom
if (empty($nom)) {
    $errors['nom'] = "Le nom de la compétence est obligatoire.";
} elseif (strlen($nom) > 80) {
    $errors['nom'] = "Le nom de la compétence ne doit pas dépasser 80 caractères.";
} else {
    // 2. Check Uniqueness via SELECT
    $stmtCheck = $pdo->prepare("SELECT id_competence FROM competences WHERE LOWER(nom) = LOWER(:nom) LIMIT 1");
    $stmtCheck->execute(['nom' => $nom]);
    if ($stmtCheck->fetch()) {
        $errors['nom'] = "Cette compétence existe déjà dans le catalogue.";
    }
}

// If errors exist, redirect back to create.php
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['old_input'] = $old;
    header('Location: create.php');
    exit;
}

// Insert into DB
try {
    $stmtInsert = $pdo->prepare("INSERT INTO competences (nom) VALUES (:nom)");
    $stmtInsert->execute(['nom' => $nom]);

    $_SESSION['success_message'] = "La compétence « " . $nom . " » a été ajoutée avec succès.";
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        $_SESSION['form_errors'] = ['nom' => "Cette compétence existe déjà dans le catalogue."];
        $_SESSION['old_input'] = $old;
        header('Location: create.php');
    } else {
        $_SESSION['error_message'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        header('Location: create.php');
    }
    exit;
}
