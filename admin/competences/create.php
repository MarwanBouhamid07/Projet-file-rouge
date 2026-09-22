<?php
/**
 * Admin Compétences - Create Form
 * Sprint 1 - Recruitment Platform
 */

require_once __DIR__ . '/../../includes/admin_auth_check.php';
require_once __DIR__ . '/../../config/db.php';

// Retrieve errors and old input if redirected back from store.php
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$pageTitle = "Ajouter une Compétence";
$activePage = "competences";
$breadcrumbSub = "Nouvelle compétence";

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Ajouter une Compétence</h1>
        <p class="page-subtitle">Ajoutez une nouvelle compétence technique ou fonctionnelle au référentiel</p>
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
        <h2 class="card-title">Détails de la compétence</h2>
    </div>
    <div class="card-body">
        <form action="store.php" method="POST" novalidate>
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
                <div class="form-help">Maximum 80 caractères. Le nom doit être unique dans le référentiel.</div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Enregistrer la compétence
                </button>
                <a href="index.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>
