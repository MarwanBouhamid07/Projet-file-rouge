<?php
/**
 * Admin Offres - List (Read)
 * Sprint 1 - Recruitment Platform
 */

require_once __DIR__ . '/../../includes/admin_auth_check.php';
require_once __DIR__ . '/../../config/db.php';

// Fetch all offers with recruiter names
$stmt = $pdo->query("
    SELECT o.id_offre, o.titre, o.description, o.domaine, o.years_required, o.image, o.created_at,
           u.first_name, u.last_name, u.email as recruteur_email
    FROM offres o
    JOIN users u ON o.id_recruteur = u.id
    ORDER BY o.created_at DESC
");
$offres = $stmt->fetchAll();

$pageTitle = "Gestion des Offres";
$activePage = "offres";
$breadcrumbSub = "Offres d'emploi";

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Gestion des Offres d'Emploi</h1>
        <p class="page-subtitle">Consultez, ajoutez, modifiez ou supprimez les offres de recrutement</p>
    </div>
    <div class="page-actions">
        <a href="create.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Ajouter une offre
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Catalogue des Offres (<?= count($offres) ?>)</h2>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($offres)): ?>
            <div class="empty-table-state">
                <div class="empty-table-icon">
                    <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                </div>
                <p>Aucune offre d'emploi trouvée.</p>
                <div style="margin-top: 15px;">
                    <a href="create.php" class="btn btn-primary btn-sm">Créer la première offre</a>
                </div>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Visuel</th>
                            <th>Titre du poste</th>
                            <th>Domaine</th>
                            <th>Exp. requise</th>
                            <th>Recruteur / Auteur</th>
                            <th>Date de création</th>
                            <th style="text-align: right; width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($offres as $offre): ?>
                            <?php
                            $imgPath = $offre['image'] && file_exists(__DIR__ . '/../../public/assets/uploads/offres/' . $offre['image'])
                                ? '../../public/assets/uploads/offres/' . e($offre['image'])
                                : '../../public/assets/images/placeholder.svg';
                            ?>
                            <tr>
                                <td>
                                    <img src="<?= $imgPath ?>" alt="Aperçu <?= e($offre['titre']) ?>" class="table-thumbnail">
                                </td>
                                <td>
                                    <strong><?= e($offre['titre']) ?></strong>
                                    <?php if (!empty($offre['description'])): ?>
                                        <div style="color: #64748b; font-size: 0.82rem; margin-top: 2px;">
                                            <?= e(strlen($offre['description']) > 75 ? substr($offre['description'], 0, 75) . '...' : $offre['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-primary"><?= e($offre['domaine']) ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-warning"><?= (int)$offre['years_required'] ?> an<?= $offre['years_required'] > 1 ? 's' : '' ?></span>
                                </td>
                                <td>
                                    <div><?= e($offre['first_name'] . ' ' . $offre['last_name']) ?></div>
                                    <div style="font-size: 0.78rem; color: #94a3b8;"><?= e($offre['recruteur_email']) ?></div>
                                </td>
                                <td><?= date('d/m/Y', strtotime($offre['created_at'])) ?></td>
                                <td style="text-align: right;">
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        <a href="edit.php?id=<?= (int)$offre['id_offre'] ?>" class="btn-icon edit" title="Modifier l'offre">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                        <a href="delete.php?id=<?= (int)$offre['id_offre'] ?>" class="btn-icon delete" title="Supprimer l'offre" data-confirm-delete="Êtes-vous certain de vouloir supprimer cette offre ? L'image associée sera également supprimée.">
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
