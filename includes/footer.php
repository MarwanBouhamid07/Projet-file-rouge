<?php
/**
 * Public Footer Include
 * Sprint 1 - Recruitment Platform
 */
?>
    <footer class="public-footer">
        <div class="container footer-container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="brand-inline">
                        <div class="brand-badge">SM</div>
                        <span class="brand-name">SmartMatch</span>
                    </div>
                    <p class="footer-description">
                        La plateforme de recrutement nouvelle génération connectant les talents aux meilleures opportunités professionnelles grâce au matching intelligent.
                    </p>
                </div>

                <div class="footer-col">
                    <h4 class="footer-title">Navigation</h4>
                    <ul class="footer-links">
                        <li><a href="#hero">Accueil</a></li>
                        <li><a href="#how-it-works">Comment ça marche</a></li>
                        <li><a href="#offers-preview">Offres d'emploi</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4 class="footer-title">Administration</h4>
                    <ul class="footer-links">
                        <li><a href="../admin/login.php">Connexion Administrateur</a></li>
                        <li><a href="../admin/dashboard.php">Tableau de bord</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h4 class="footer-title">Sprint 1</h4>
                    <p class="footer-info">
                        Réalisation &bull; Architecture PHP Vanilla + MySQL + CSS Moderne.
                    </p>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <strong>SmartMatch</strong> &mdash; Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
