<?php
/**
 * Admin Candidats - Read-only List
 * Sprint 1 - Recruitment Platform
 */

require_once __DIR__ . '/../../includes/admin_auth_check.php';
require_once __DIR__ . '/../../config/db.php';

// Fetch candidates (users with role = candidat)
$stmt = $pdo->query("
    SELECT id, first_name, last_name, email, domaine, years_experience, created_at
    FROM users
    WHERE role = 'candidat'
    ORDER BY created_at DESC
");
$candidats = $stmt->fetchAll();

$pageTitle = "Liste des Candidats";
$activePage = "candidats";
$breadcrumbSub = "Candidats";

require_once __DIR__ . '/../../includes/admin_layout_top.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Liste des Candidats</h1>
        <p class="page-subtitle">Consultation des profils candidats inscrits sur la plateforme (Lecture seule - Sprint 1)</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Candidats Enregistrés (<?= count($candidats) ?>)</h2>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($candidats)): ?>
            <div class="empty-table-state">
                <div class="empty-table-icon">
                    <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <p>Aucun candidat inscrit pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 70px;">#ID</th>
                            <th>Nom complet</th>
                            <th>Email de contact</th>
                            <th>Domaine d'expertise</th>
                            <th>Expérience</th>
                            <th>Date d'inscription</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidats as $candidat): ?>
                            <tr>
                                <td><span class="badge badge-info">#<?= (int)$candidat['id'] ?></span></td>
                                <td>
                                    <strong><?= e($candidat['first_name'] . ' ' . $candidat['last_name']) ?></strong>
                                </td>
                                <td>
                                    <a href="mailto:<?= e($candidat['email']) ?>" style="color: #475569; display: inline-flex; align-items: center; gap: 6px;">
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                        <?= e($candidat['email']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($candidat['domaine'])): ?>
                                        <span class="badge badge-primary"><?= e($candidat['domaine']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($candidat['years_experience'] !== null): ?>
                                        <span class="badge badge-warning"><?= (int)$candidat['years_experience'] ?> an<?= $candidat['years_experience'] > 1 ? 's' : '' ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Non renseigné</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($candidat['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_layout_bottom.php'; ?>
