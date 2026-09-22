<?php
/**
 * Public Header / Navbar Include
 * Sprint 1 - Recruitment Platform
 */

$siteTitle = $siteTitle ?? "SmartMatch - Plateforme de Recrutement Intelligent";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="public-navbar">
        <div class="container nav-container">
            <a href="index.php" class="nav-brand">
                <div class="brand-badge">SM</div>
                <span class="brand-name">SmartMatch</span>
            </a>

            <button class="nav-toggle" id="navToggle" aria-label="Menu mobile">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav class="nav-menu" id="navMenu">
                <ul class="nav-links">
                    <li><a href="#hero" class="nav-link active">Accueil</a></li>
                    <li><a href="#how-it-works" class="nav-link">Comment ça marche</a></li>
                    <li><a href="#offers-preview" class="nav-link">Dernières Offres</a></li>
                </ul>

                <div class="nav-actions">
                    <a href="../admin/login.php" class="btn btn-outline">Espace Admin / Login</a>
                </div>
            </nav>
        </div>
    </header>
