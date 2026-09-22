<?php
/**
 * Admin Compétences - List (Read)
 * Sprint 1 - Recruitment Platform
 */

require_once __DIR__ . '/../../includes/admin_auth_check.php';
require_once __DIR__ . '/../../config/db.php';

// Fetch all competences
$stmt = $pdo->query("SELECT * FROM competences ORDER BY nom ASC");
$competences = $stmt->fetchAll();

$pageTitle = "Gestion des Compétences";
$activePage = "competences";
$breadcrumbSub = "Compétences";

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Référentiel des Compétences</h1>
        <p class="page-subtitle">Gérez le catalogue des compétences techniques et professionnelles</p>
    </div>
    <div class="page-actions">
        <a href="create.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Ajouter une compétence
        </a>
    </div>
</div>

<div class="card" style="max-width: 800px;">
    <div class="card-header">
        <h2 class="card-title">Catalogue des Compétences (<?= count($competences) ?>)</h2>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($competences)): ?>
            <div class="empty-table-state">
                <div class="empty-table-icon">
                    <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                </div>
                <p>Aucune compétence dans le catalogue.</p>
                <div style="margin-top: 15px;">
                    <a href="create.php" class="btn btn-primary btn-sm">Ajouter une compétence</a>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">#ID</th>
                            <th>Nom de la compétence</th>
                            <th style="text-align: right; width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($competences as $comp): ?>
                            <tr>
                                <td><span class="badge badge-info">#<?= (int)$comp['id_competence'] ?></span></td>
                                <td>
                                    <strong><?= e($comp['nom']) ?></strong>
                                </td>
                                <td style="text-align: right;">
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        <a href="edit.php?id=<?= (int)$comp['id_competence'] ?>" class="btn-icon edit" title="Modifier la compétence">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                        <a href="delete.php?id=<?= (int)$comp['id_competence'] ?>" class="btn-icon delete" title="Supprimer la compétence" data-confirm-delete="Êtes-vous certain de vouloir supprimer cette compétence ?">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>
