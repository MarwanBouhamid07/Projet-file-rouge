<?php
/**
 * Admin Offres - Store (Create handler)
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

$titre = trim($_POST['titre'] ?? '');
$domaine = trim($_POST['domaine'] ?? '');
$yearsRequired = trim($_POST['years_required'] ?? '');
$idRecruteur = trim($_POST['id_recruteur'] ?? '');
$description = trim($_POST['description'] ?? '');

// 1. Validate Titre
if (empty($titre)) {
    $errors['titre'] = "Le titre de l'offre est obligatoire.";
} elseif (strlen($titre) > 150) {
    $errors['titre'] = "Le titre ne doit pas dépasser 150 caractères.";
}

// 2. Validate Domaine
if (empty($domaine)) {
    $errors['domaine'] = "Le domaine d'activité est obligatoire.";
} elseif (strlen($domaine) > 100) {
    $errors['domaine'] = "Le domaine ne doit pas dépasser 100 caractères.";
}

// 3. Validate Years Required
if ($yearsRequired === '' || !is_numeric($yearsRequired) || (int)$yearsRequired < 0) {
    $errors['years_required'] = "Les années d'expérience requises doivent être un entier positif ou zéro.";
} else {
    $yearsRequired = (int)$yearsRequired;
}

// 4. Validate Recruiter (must exist and have role = recruteur or admin)
if (empty($idRecruteur) || !is_numeric($idRecruteur)) {
    $errors['id_recruteur'] = "Veuillez sélectionner un recruteur valide.";
} else {
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE id = :id AND role IN ('recruteur', 'admin') LIMIT 1");
    $stmtUser->execute(['id' => (int)$idRecruteur]);
    if (!$stmtUser->fetch()) {
        $errors['id_recruteur'] = "Le recruteur sélectionné est invalide ou n'a pas les droits nécessaires.";
    }
}

// 5. Validate and Process Image Upload
$uploadedFileName = null;
$maxFileSize = 2 * 1024 * 1024; // 2 MB
$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/pjpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors['image'] = "L'image d'illustration est obligatoire lors de la création d'une offre.";
} elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errors['image'] = "Une erreur est survenue lors de l'envoi de l'image (code: " . $_FILES['image']['error'] . ").";
} else {
    $fileTmp = $_FILES['image']['tmp_name'];
    $fileSize = $_FILES['image']['size'];

    if ($fileSize > $maxFileSize) {
        $errors['image'] = "L'image est trop volumineuse (" . round($fileSize / (1024 * 1024), 2) . " Mo). La taille maximale autorisée est de 2 Mo.";
    } else {
        // Validate real image type using getimagesize()
        $imageInfo = @getimagesize($fileTmp);
        if ($imageInfo === false || !isset($imageInfo['mime']) || !array_key_exists($imageInfo['mime'], $allowedMimes)) {
            $errors['image'] = "Format d'image non supporté. Veuillez envoyer un fichier JPG, PNG ou WEBP valide.";
        } else {
            $extension = $allowedMimes[$imageInfo['mime']];
            $uploadedFileName = uniqid('offre_', true) . '.' . $extension;
        }
    }
}

// If validation errors exist, redirect back to create.php
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['old_input'] = $old;
    header('Location: create.php');
    exit;
}

// Move uploaded file to target directory
$uploadDir = __DIR__ . '/../../public/assets/uploads/offres/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$targetFilePath = $uploadDir . $uploadedFileName;
if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
    $_SESSION['form_errors'] = ['image' => "Échec du déplacement de l'image sur le serveur."];
    $_SESSION['old_input'] = $old;
    header('Location: create.php');
    exit;
}

// Insert into Database
try {
    $stmtInsert = $pdo->prepare("
        INSERT INTO offres (titre, description, domaine, years_required, image, id_recruteur)
        VALUES (:titre, :description, :domaine, :years_required, :image, :id_recruteur)
    ");
    $stmtInsert->execute([
        'titre'          => $titre,
        'description'    => !empty($description) ? $description : null,
        'domaine'        => $domaine,
        'years_required' => $yearsRequired,
        'image'          => $uploadedFileName,
        'id_recruteur'   => (int)$idRecruteur
    ]);

    $_SESSION['success_message'] = "L'offre d'emploi « " . $titre . " » a été créée avec succès.";
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    // If DB insert fails, clean up the uploaded image
    if (file_exists($targetFilePath)) {
        @unlink($targetFilePath);
    }
    $_SESSION['error_message'] = "Erreur lors de l'enregistrement de l'offre : " . $e->getMessage();
    header('Location: create.php');
    exit;
}
