<?php
/**
 * Admin Compétences - Delete handler
 * Sprint 1 - Recruitment Platform
 */

require_once __DIR__ . '/../../includes/admin_auth_check.php';
require_once __DIR__ . '/../../config/db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['error_message'] = "Identifiant de compétence invalide.";
    header('Location: index.php');
    exit;
}

// 1. Fetch competence
$stmt = $pdo->prepare("SELECT * FROM competences WHERE id_competence = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$comp = $stmt->fetch();

if (!$comp) {
    $_SESSION['error_message'] = "Compétence introuvable ou déjà supprimée.";
    header('Location: index.php');
    exit;
}

// 2. Check if competence is referenced in junction tables before deletion
$stmtOffres = $pdo->prepare("SELECT COUNT(*) FROM offre_competence WHERE id_competence = :id");
$stmtOffres->execute(['id' => $id]);
$countOffres = (int)$stmtOffres->fetchColumn();

$stmtCandidats = $pdo->prepare("SELECT COUNT(*) FROM candidat_competence WHERE id_competence = :id");
$stmtCandidats->execute(['id' => $id]);
$countCandidats = (int)$stmtCandidats->fetchColumn();

if ($countOffres > 0 || $countCandidats > 0) {
    $_SESSION['error_message'] = "Impossible de supprimer la compétence « " . $comp['nom'] . " » car elle est actuellement assignée à " . ($countOffres > 0 ? "$countOffres offre(s)" : "") . ($countOffres > 0 && $countCandidats > 0 ? " et " : "") . ($countCandidats > 0 ? "$countCandidats candidat(s)" : "") . ".";
    header('Location: index.php');
    exit;
}

// 3. Delete from DB
try {
    $stmtDelete = $pdo->prepare("DELETE FROM competences WHERE id_competence = :id");
    $stmtDelete->execute(['id' => $id]);

    $_SESSION['success_message'] = "La compétence « " . $comp['nom'] . " » a été supprimée avec succès.";
} catch (PDOException $e) {
    if ($e->getCode() == 23000 || strpos($e->getMessage(), 'foreign key') !== false) {
        $_SESSION['error_message'] = "Impossible de supprimer cette compétence car elle est liée à d'autres enregistrements.";
    } else {
        $_SESSION['error_message'] = "Erreur lors de la suppression de la compétence : " . $e->getMessage();
    }
}

header('Location: index.php');
exit;
