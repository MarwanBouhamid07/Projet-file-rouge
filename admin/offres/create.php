<?php
/**
 * Admin Offres - Create Form
 * Sprint 1 - Recruitment Platform
 */

require_once __DIR__ . '/../../includes/admin_auth_check.php';
require_once __DIR__ . '/../../config/db.php';

// Fetch recruiters and admins for dropdown
$stmt = $pdo->query("SELECT id, first_name, last_name, role, email FROM users WHERE role IN ('recruteur', 'admin') ORDER BY first_name ASC");
$recruteurs = $stmt->fetchAll();

// Retrieve errors and old input if redirected back from store.php
$errors = $_SESSION['form_errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['form_errors'], $_SESSION['old_input']);

$pageTitle = "Ajouter une Offre";
$activePage = "offres";
$breadcrumbSub = "Nouvelle offre";

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Créer une nouvelle offre d'emploi</h1>
        <p class="page-subtitle">Renseignez les détails du poste et l'image d'illustration</p>
    </div>
    <div class="page-actions">
        <a href="index.php" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Retour à la liste
        </a>
    </div>
</div>

<div class="card" style="max-width: 860px;">
    <div class="card-header">
        <h2 class="card-title">Informations de l'offre</h2>
    </div>
    <div class="card-body">
        <form action="store.php" method="POST" enctype="multipart/form-data" novalidate>
            <!-- Titre -->
            <div class="form-group">
                <label for="titre">Titre du poste <span class="required-star">*</span></label>
                <input type="text"
                       name="titre"
                       id="titre"
                       class="form-control <?= isset($errors['titre']) ? 'is-invalid' : '' ?>"
                       value="<?= e($old['titre'] ?? '') ?>"
                       placeholder="ex: Développeur PHP / Symfony Senior"
                       maxlength="150"
                       required>
                <?php if (isset($errors['titre'])): ?>
                    <span class="invalid-feedback"><?= e($errors['titre']) ?></span>
                <?php endif; ?>
                <div class="form-help">Maximum 150 caractères.</div>
            </div>

            <!-- Domaine & Expérience -->
            <div class="form-row">
                <div class="form-group">
                    <label for="domaine">Domaine d'activité <span class="required-star">*</span></label>
                    <input type="text"
                           name="domaine"
                           id="domaine"
                           class="form-control <?= isset($errors['domaine']) ? 'is-invalid' : '' ?>"
                           value="<?= e($old['domaine'] ?? '') ?>"
                           placeholder="ex: Développement Web, Cloud & DevOps, Data"
                           maxlength="100"
                           required>
                    <?php if (isset($errors['domaine'])): ?>
                        <span class="invalid-feedback"><?= e($errors['domaine']) ?></span>
                    <?php endif; ?>
                    <div class="form-help">Maximum 100 caractères.</div>
                </div>

                <div class="form-group">
                    <label for="years_required">Années d'expérience requises <span class="required-star">*</span></label>
                    <input type="number"
                           name="years_required"
                           id="years_required"
                           class="form-control <?= isset($errors['years_required']) ? 'is-invalid' : '' ?>"
                           value="<?= e($old['years_required'] ?? '0') ?>"
                           min="0"
                           max="50"
                           required>
                    <?php if (isset($errors['years_required'])): ?>
                        <span class="invalid-feedback"><?= e($errors['years_required']) ?></span>
                    <?php endif; ?>
                    <div class="form-help">Indiquez 0 pour débutant / junior.</div>
                </div>
            </div>

            <!-- Recruteur assigné -->
            <div class="form-group">
                <label for="id_recruteur">Recruteur / Auteur de l'offre <span class="required-star">*</span></label>
                <select name="id_recruteur"
                        id="id_recruteur"
                        class="form-control <?= isset($errors['id_recruteur']) ? 'is-invalid' : '' ?>"
                        required>
                    <option value="">-- Sélectionnez un recruteur --</option>
                    <?php foreach ($recruteurs as $recruteur): ?>
                        <option value="<?= (int)$recruteur['id'] ?>" <?= (isset($old['id_recruteur']) && (int)$old['id_recruteur'] === (int)$recruteur['id']) ? 'selected' : '' ?>>
                            <?= e($recruteur['first_name'] . ' ' . $recruteur['last_name']) ?> (<?= e($recruteur['role']) ?> - <?= e($recruteur['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['id_recruteur'])): ?>
                    <span class="invalid-feedback"><?= e($errors['id_recruteur']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description">Description détaillée du poste</label>
                <textarea name="description"
                          id="description"
                          rows="5"
                          class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                          placeholder="Décrivez les missions, le contexte et les responsabilités..."><?= e($old['description'] ?? '') ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <span class="invalid-feedback"><?= e($errors['description']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Image Upload -->
            <div class="form-group">
                <label for="imageInput">Image d'illustration <span class="required-star">*</span></label>
                <div class="image-upload-wrapper">
                    <div class="image-preview-box">
                        <img id="imagePreview" src="../../public/assets/images/placeholder.svg" alt="Aperçu">
                    </div>
                    <div class="image-upload-input-group">
                        <input type="file"
                               name="image"
                               id="imageInput"
                               class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>"
                               accept="image/jpeg,image/png,image/webp"
                               required>
                        <?php if (isset($errors['image'])): ?>
                            <span class="invalid-feedback"><?= e($errors['image']) ?></span>
                        <?php endif; ?>
                        <div class="form-help">
                            Formats acceptés : <strong>JPG, JPEG, PNG, WEBP</strong>. Poids maximum : <strong>2 Mo</strong>.
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Enregistrer l'offre
                </button>
                <a href="index.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>
