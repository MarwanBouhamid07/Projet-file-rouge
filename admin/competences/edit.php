<?php
/**
 * Admin Compétences - Edit Form
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

// Fetch existing competence
$stmt = $pdo->prepare("SELECT * FROM competences WHERE id_competence = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$competence = $stmt->fetch();

if (!$competence) {
    $_SESSION['error_message'] = "Compétence introuvable.";
    header('Location: index.php');
    exit;
}

// Retrieve errors and old input if redirected back from update.php
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? $competence;
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$pageTitle = "Modifier la Compétence #" . $id;
$activePage = "competences";
$breadcrumbSub = "Modifier la compétence";

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Modifier la Compétence</h1>
        <p class="page-subtitle">Modifiez le libellé de la compétence</p>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Retour à la liste
        </a>
    </div>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-header">
        <h2 class="card-title">Modification de la compétence #<?= (int)$competence['id_competence'] ?></h2>
    </div>
    <div class="card-body">
        <form action="update.php" method="POST" novalidate>
            <input type="hidden" name="id_competence" value="<?= (int)$competence['id_competence'] ?>">

            <!-- Nom de la compétence -->
            <div class="form-group">
                <label for="nom">Nom de la compétence <span class="required-star">*</span></label>
                <input type="text"
                       name="nom"
                       id="nom"
                       class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
                       value="<?= e($old['nom'] ?? '') ?>"
                       placeholder="ex: PHP, Docker, React, SEO..."
                       maxlength="80"
                       required
                       autofocus>
                <?php if (isset($errors['nom'])): ?>
                    <span class="invalid-feedback"><?= e($errors['nom']) ?></span>
                <?php endif; ?>
                <div class="form-help">Maximum 80 caractères. Le nom doit être unique.</div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Mettre à jour la compétence
                </button>
                <a href="index.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>
