<?php
/**
 * Admin Offres - Update handler
 * Sprint 1 - Recruitment Platform
 */

require_once __DIR__ . '/../../includes/admin_auth_check.php';
require_once __DIR__ . '/../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id_offre', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error_message'] = "Identifiant d'offre invalide.";
    header('Location: index.php');
    exit;
}

// Fetch existing record
$stmt = $pdo->prepare("SELECT * FROM offres WHERE id_offre = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$currentOffer = $stmt->fetch();

if (!$currentOffer) {
    $_SESSION['error_message'] = "Offre introuvable.";
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

// 4. Validate Recruiter
if (empty($idRecruteur) || !is_numeric($idRecruteur)) {
    $errors['id_recruteur'] = "Veuillez sélectionner un recruteur valide.";
} else {
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE id = :id AND role IN ('recruteur', 'admin') LIMIT 1");
    $stmtUser->execute(['id' => (int)$idRecruteur]);
    if (!$stmtUser->fetch()) {
        $errors['id_recruteur'] = "Le recruteur sélectionné est invalide ou n'a pas les droits nécessaires.";
    }
}

// 5. Image Validation (Optional on Edit)
$newUploadedFileName = null;
$maxFileSize = 2 * 1024 * 1024; // 2 MB
$allowedMimes = [
    'image/jpeg'  => 'jpg',
    'image/pjpeg' => 'jpg',
    'image/png'   => 'png',
    'image/webp'  => 'webp'
];

$hasNewImage = isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

if ($hasNewImage) {
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors['image'] = "Erreur lors de l'envoi de la nouvelle image (code: " . $_FILES['image']['error'] . ").";
    } else {
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];

        if ($fileSize > $maxFileSize) {
            $errors['image'] = "La nouvelle image est trop volumineuse (" . round($fileSize / (1024 * 1024), 2) . " Mo). Max 2 Mo.";
        } else {
            $imageInfo = @getimagesize($fileTmp);
            if ($imageInfo === false || !isset($imageInfo['mime']) || !array_key_exists($imageInfo['mime'], $allowedMimes)) {
                $errors['image'] = "Format d'image non supporté. Veuillez envoyer un fichier JPG, PNG ou WEBP valide.";
            } else {
                $extension = $allowedMimes[$imageInfo['mime']];
                $newUploadedFileName = uniqid('offre_', true) . '.' . $extension;
            }
        }
    }
}

// If errors exist, redirect back to edit.php
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['old_input'] = $old;
    header('Location: edit.php?id=' . $id);
    exit;
}

$uploadDir = __DIR__ . '/../../public/assets/uploads/offres/';
$finalImageName = $currentOffer['image'];
$oldImageToDelete = null;

if ($hasNewImage && $newUploadedFileName) {
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $targetPath = $uploadDir . $newUploadedFileName;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $oldImageToDelete = $currentOffer['image'];
        $finalImageName = $newUploadedFileName;
    } else {
        $_SESSION['form_errors'] = ['image' => "Échec du téléchargement de la nouvelle image sur le serveur."];
        $_SESSION['old_input'] = $old;
        header('Location: edit.php?id=' . $id);
        exit;
    }
}

// Execute Update
try {
    $stmtUpdate = $pdo->prepare("
        UPDATE offres
        SET titre = :titre,
            description = :description,
            domaine = :domaine,
            years_required = :years_required,
            image = :image,
            id_recruteur = :id_recruteur
        WHERE id_offre = :id
    ");
    $stmtUpdate->execute([
        'titre'          => $titre,
        'description'    => !empty($description) ? $description : null,
        'domaine'        => $domaine,
        'years_required' => $yearsRequired,
        'image'          => $finalImageName,
        'id_recruteur'   => (int)$idRecruteur,
        'id'             => $id
    ]);

    // Clean up old image if replaced
    if ($oldImageToDelete) {
        $oldPath = $uploadDir . $oldImageToDelete;
        if (file_exists($oldPath)) {
            @unlink($oldPath);
        }
    }

    $_SESSION['success_message'] = "L'offre d'emploi « " . $titre . " » a été mise à jour avec succès.";
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    // If update failed and new image was saved, remove the new image
    if ($newUploadedFileName && file_exists($uploadDir . $newUploadedFileName)) {
        @unlink($uploadDir . $newUploadedFileName);
    }
    $_SESSION['error_message'] = "Erreur lors de la mise à jour : " . $e->getMessage();
    header('Location: edit.php?id=' . $id);
    exit;
}
