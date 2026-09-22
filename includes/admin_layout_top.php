<?php
/**
 * Admin Layout Top Include
 * Sprint 1 - Recruitment Platform
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Compute relative path to admin root
$currentDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));
$inSubfolder = (strpos($currentDir, '/admin/') !== false && basename($currentDir) !== 'admin');
$adminRoot = $inSubfolder ? '..' : '.';
$publicRoot = $inSubfolder ? '../../public' : '../public';

// Active page detection
if (!isset($activePage)) {
    $scriptName = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
    $dirName = basename($currentDir);
    if ($dirName === 'offres') {
        $activePage = 'offres';
    } elseif ($dirName === 'competences') {
        $activePage = 'competences';
    } elseif ($dirName === 'candidats') {
        $activePage = 'candidats';
    } else {
        $activePage = 'dashboard';
    }
}

$pageTitle = isset($pageTitle) ? $pageTitle . ' | Administration' : 'Administration | SmartMatch';
$adminName = $_SESSION['user_name'] ?? 'Administrateur';
$adminEmail = $_SESSION['user_email'] ?? 'admin@recrutement.com';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="<?= $adminRoot ?>/assets/css/admin.css">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <a href="<?= $adminRoot ?>/dashboard.php" class="brand-logo">
                    <span class="logo-badge">SM</span>
                    <div class="logo-text">
                        <span class="logo-title">SmartMatch</span>
                        <span class="logo-subtitle">Espace Admin</span>
                    </div>
                </a>
                <button class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Fermer le menu">&times;</button>
            </div>

            <div class="sidebar-user-mini">
                <div class="user-avatar-initials"><?= strtoupper(substr($adminName, 0, 2)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="user-role-badge">Admin</div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section-title">Navigation principale</div>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?= $adminRoot ?>/dashboard.php" class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
                            <svg class="nav-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="9" rx="1"></rect>
                                <rect x="14" y="3" width="7" height="5" rx="1"></rect>
                                <rect x="14" y="12" width="7" height="9" rx="1"></rect>
                                <rect x="3" y="16" width="7" height="5" rx="1"></rect>
                            </svg>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $adminRoot ?>/offres/index.php" class="nav-link <?= $activePage === 'offres' ? 'active' : '' ?>">
                            <svg class="nav-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                            <span>Gestion des Offres</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $adminRoot ?>/competences/index.php" class="nav-link <?= $activePage === 'competences' ? 'active' : '' ?>">
                            <svg class="nav-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                            <span>Compétences</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $adminRoot ?>/candidats/index.php" class="nav-link <?= $activePage === 'candidats' ? 'active' : '' ?>">
                            <svg class="nav-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span>Candidats</span>
                        </a>
                    </li>
                </ul>

                <div class="nav-section-title">Liens externes</div>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?= $publicRoot ?>/index.php" target="_blank" class="nav-link nav-external">
                            <svg class="nav-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                <polyline points="15 3 21 3 21 9"></polyline>
                                <line x1="10" y1="14" x2="21" y2="3"></line>
                            </svg>
                            <span>Voir le site public</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= $adminRoot ?>/logout.php" class="nav-link nav-logout">
                            <svg class="nav-icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            <span>Déconnexion</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="admin-main-wrapper">
            <!-- Top Navbar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Ouvrir le menu">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <div class="topbar-breadcrumb">
                        <span class="breadcrumb-item"><a href="<?= $adminRoot ?>/dashboard.php">Admin</a></span>
                        <?php if (isset($breadcrumbSub)): ?>
                            <span class="breadcrumb-separator">/</span>
                            <span class="breadcrumb-item active"><?= htmlspecialchars($breadcrumbSub, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="topbar-right">
                    <div class="topbar-user">
                        <div class="topbar-avatar"><?= strtoupper(substr($adminName, 0, 2)) ?></div>
                        <span class="topbar-username"><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></span>
                        <a href="<?= $adminRoot ?>/logout.php" class="btn-logout-small" title="Déconnexion">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Main Page Content Starts -->
            <main class="admin-content">
                <?php if (!empty($_SESSION['success_message'])): ?>
                    <div class="alert alert-success" id="flashSuccess">
                        <div class="alert-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        </div>
                        <div class="alert-content"><?= htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8') ?></div>
                        <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['error_message'])): ?>
                    <div class="alert alert-error" id="flashError">
                        <div class="alert-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        </div>
                        <div class="alert-content"><?= htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8') ?></div>
                        <button type="button" class="alert-close" onclick="this.parentElement.remove();">&times;</button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
