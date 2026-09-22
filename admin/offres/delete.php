<?php
/**
 * Admin Offres - Delete handler
 * Sprint 1 - Recruitment Platform
 */

require_once __DIR__ . '/../../includes/admin_auth_check.php';
require_once __DIR__ . '/../../config/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error_message'] = "Identifiant d'offre invalide.";
    header('Location: index.php');
    exit;
}

// 1. Fetch offer to get image filename
$stmt = $pdo->prepare("SELECT * FROM offres WHERE id_offre = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$offre = $stmt->fetch();

if (!$offre) {
    $_SESSION['error_message'] = "Offre introuvable ou déjà supprimée.";
    header('Location: index.php');
    exit;
}

// 2. Delete from database
try {
    $stmtDelete = $pdo->prepare("DELETE FROM offres WHERE id_offre = :id");
    $stmtDelete->execute(['id' => $id]);

    // 3. If DB delete succeeded, delete image file from disk
    if (!empty($offre['image'])) {
        $imagePath = __DIR__ . '/../../public/assets/uploads/offres/' . $offre['image'];
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }

    $_SESSION['success_message'] = "L'offre « " . $offre['titre'] . " » et son image associée ont été supprimées avec succès.";
} catch (PDOException $e) {
    // If foreign key constraint prevents deletion (error code 23000)
    if ($e->getCode() == 23000 || strpos($e->getMessage(), 'foreign key') !== false) {
        $_SESSION['error_message'] = "Impossible de supprimer cette offre car elle est actuellement liée à des compétences ou des candidatures existantes.";
    } else {
        $_SESSION['error_message'] = "Erreur lors de la suppression de l'offre : " . $e->getMessage();
    }
}

header('Location: index.php');
exit;
