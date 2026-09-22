<?php
/**
 * Admin Dashboard Overview
 * Sprint 1 - Recruitment Platform
 */

require_once __DIR__ . '/../includes/admin_auth_check.php';
require_once __DIR__ . '/../config/db.php';

// 1. Fetch counts
$totalOffres = $pdo->query("SELECT COUNT(*) FROM offres")->fetchColumn();
$totalCandidats = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'candidat'")->fetchColumn();
$totalRecruteurs = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'recruteur'")->fetchColumn();
$totalCompetences = $pdo->query("SELECT COUNT(*) FROM competences")->fetchColumn();

// 2. Fetch latest 5 offers for quick overview
$stmt = $pdo->query("
    SELECT o.id_offre, o.titre, o.domaine, o.years_required, o.image, o.created_at,
           u.first_name, u.last_name
    FROM offres o
    JOIN users u ON o.id_recruteur = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
");
$recentOffres = $stmt->fetchAll();

$pageTitle = "Tableau de bord";
$activePage = "dashboard";
$breadcrumbSub = "Tableau de bord";

require_once __DIR__ . '/../includes/admin_layout_top.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Tableau de bord</h1>
        <p class="page-subtitle">Vue d'ensemble de la plateforme et indicateurs clés</p>
    </div>
    <div class="page-actions">
        <a href="offres/create.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Nouvelle offre
        </a>
    </div>
</div>

<!-- 4 Stat Cards as per Section 4.1 -->
<div class="stats-grid">
    <!-- Stat 1: Total Offres -->
    <div class="stat-card">
        <div class="stat-icon-wrapper icon-blue">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= (int)$totalOffres ?></div>
            <div class="stat-label">Total Offres</div>
        </div>
    </div>

    <!-- Stat 2: Total Candidats -->
    <div class="stat-card">
        <div class="stat-icon-wrapper icon-green">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= (int)$totalCandidats ?></div>
            <div class="stat-label">Total Candidats</div>
        </div>
    </div>

    <!-- Stat 3: Total Recruteurs -->
    <div class="stat-card">
        <div class="stat-icon-wrapper icon-purple">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= (int)$totalRecruteurs ?></div>
            <div class="stat-label">Total Recruteurs</div>
        </div>
    </div>

    <!-- Stat 4: Total Compétences -->
    <div class="stat-card">
        <div class="stat-icon-wrapper icon-amber">
            <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            </svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= (int)$totalCompetences ?></div>
            <div class="stat-label">Compétences</div>
        </div>
    </div>
</div>

<!-- Recent Offers Table -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Dernières offres publiées</h2>
        <a href="offres/index.php" class="btn btn-secondary btn-sm">Voir toutes les offres</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($recentOffres)): ?>
            <div class="empty-table-state">
                <p>Aucune offre enregistrée pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Visuel</th>
                            <th>Titre de l'offre</th>
                            <th>Domaine</th>
                            <th>Exp. requise</th>
                            <th>Recruteur</th>
                            <th>Date d'ajout</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOffres as $offre): ?>
                            <?php
                            $imgPath = $offre['image'] && file_exists(__DIR__ . '/../public/assets/uploads/offres/' . $offre['image'])
                                ? '../public/assets/uploads/offres/' . e($offre['image'])
                                : '../public/assets/images/placeholder.svg';
                            ?>
                            <tr>
                                <td>
                                    <img src="<?= $imgPath ?>" alt="Aperçu" class="table-thumbnail">
                                </td>
                                <td>
                                    <strong><?= e($offre['titre']) ?></strong>
                                </td>
                                <td><span class="badge badge-primary"><?= e($offre['domaine']) ?></span></td>
                                <td><?= (int)$offre['years_required'] ?> an<?= $offre['years_required'] > 1 ? 's' : '' ?></td>
                                <td><?= e($offre['first_name'] . ' ' . $offre['last_name']) ?></td>
                                <td><?= date('d/m/Y', strtotime($offre['created_at'])) ?></td>
                                <td style="text-align: right;">
                                    <div class="table-actions" style="justify-content: flex-end;">
                                        <a href="offres/edit.php?id=<?= (int)$offre['id_offre'] ?>" class="btn-icon edit" title="Modifier">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                        <a href="offres/delete.php?id=<?= (int)$offre['id_offre'] ?>" class="btn-icon delete" title="Supprimer" data-confirm-delete="Êtes-vous sûr de vouloir supprimer cette offre ?">
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

<?php require_once __DIR__ . '/../includes/admin_layout_bottom.php'; ?>
