<?php
/**
 * Admin Compétences - Update handler
 * Sprint 1 - Recruitment Platform
 */

require_once __DIR__ . '/../../includes/admin_auth_check.php';
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id_competence', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error_message'] = "Identifiant de compétence invalide.";
    header('Location: index.php');
    exit;
}

// Fetch existing record
$stmt = $pdo->prepare("SELECT * FROM competences WHERE id_competence = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$currentComp = $stmt->fetch();

if (!$currentComp) {
    $_SESSION['error_message'] = "Compétence introuvable.";
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
    // 2. Check Uniqueness excluding current ID
    $stmtCheck = $pdo->prepare("SELECT id_competence FROM competences WHERE LOWER(nom) = LOWER(:nom) AND id_competence != :id LIMIT 1");
    $stmtCheck->execute(['nom' => $nom, 'id' => $id]);
    if ($stmtCheck->fetch()) {
        $errors['nom'] = "Une autre compétence avec ce nom existe déjà dans le catalogue.";
    }
}

// If errors exist, redirect back to edit.php
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['old_input'] = $old;
    header('Location: edit.php?id=' . $id);
    exit;
}

// Execute Update
try {
    $stmtUpdate = $pdo->prepare("UPDATE competences SET nom = :nom WHERE id_competence = :id");
    $stmtUpdate->execute(['nom' => $nom, 'id' => $id]);

    $_SESSION['success_message'] = "La compétence « " . $nom . " » a été mise à jour avec succès.";
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        $_SESSION['form_errors'] = ['nom' => "Une compétence avec ce nom existe déjà."];
        $_SESSION['old_input'] = $old;
        header('Location: edit.php?id=' . $id);
    } else {
        $_SESSION['error_message'] = "Erreur lors de la mise à jour : " . $e->getMessage();
        header('Location: edit.php?id=' . $id);
    }
    exit;
}
