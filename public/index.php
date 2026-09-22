<?php
/**
 * Main Page (Landing Page)
 * Sprint 1 - Recruitment Platform
 */

require_once __DIR__ . '/../config/db.php';

// Fetch 3 or 4 latest offers with recruiter info
$stmt = $pdo->query("
    SELECT o.id_offre, o.titre, o.domaine, o.years_required, o.image, o.created_at,
           u.first_name, u.last_name
    FROM offres o
    JOIN users u ON o.id_recruteur = u.id
    ORDER BY o.created_at DESC
    LIMIT 3
");
$latestOffres = $stmt->fetchAll();

// Check if image file exists, return placeholder if not
function getOfferImageUrl($imageFileName) {
    if (!$imageFileName) return 'assets/images/placeholder.svg';
    $path = __DIR__ . '/assets/uploads/offres/' . $imageFileName;
    if (file_exists($path)) {
        return 'assets/uploads/offres/' . e($imageFileName);
    }
    return 'assets/images/placeholder.svg';
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section id="hero" class="hero-section">
    <div class="container hero-container">
        <div class="hero-content">
            <h1 class="hero-headline">Trouvez l'offre qui correspond <span class="highlight">vraiment</span> à votre profil</h1>
            <p class="hero-subheadline">
                Grâce à notre système de Smart Matching, gagnez du temps et postulez aux opportunités
                les plus pertinentes selon vos compétences et votre expérience.
            </p>
            <div class="hero-actions">
                <a href="#offers-preview" class="btn btn-primary btn-lg">Découvrir les offres</a>
                <a href="../admin/login.php" class="btn btn-secondary btn-lg">Espace Recruteur</a>
            </div>
        </div>
        <div class="hero-illustration">
            <div class="hero-blob"></div>
            <!-- Decorative Dashboard/Profile Illustration Mockup -->
            <div class="hero-card">
                <div class="hero-card-header">
                    <div class="mock-avatar"></div>
                    <div class="mock-lines">
                        <div class="line short"></div>
                        <div class="line"></div>
                    </div>
                    <div class="mock-match-badge">95% Match</div>
                </div>
                <div class="hero-card-body">
                    <div class="mock-title">Développeur Fullstack</div>
                    <div class="mock-tags">
                        <span class="mock-tag">PHP</span>
                        <span class="mock-tag">React</span>
                        <span class="mock-tag">MySQL</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How it works Section -->
<section id="how-it-works" class="how-it-works-section">
    <div class="container">
        <div class="section-heading text-center">
            <span class="section-subtitle">Processus simple et efficace</span>
            <h2 class="section-title">Comment ça fonctionne ?</h2>
        </div>

        <div class="hitw-grid">
            <!-- Step 1 -->
            <div class="hitw-card">
                <div class="hitw-icon">
                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </div>
                <h3 class="hitw-title">1. Créez votre profil</h3>
                <p class="hitw-text">Inscrivez-vous, remplissez vos informations, votre domaine d'expertise et listez vos compétences techniques.</p>
            </div>

            <!-- Step 2 -->
            <div class="hitw-card">
                <div class="hitw-icon">
                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <h3 class="hitw-title">2. Calcul du Smart Match</h3>
                <p class="hitw-text">Notre algorithme croise instantanément votre profil avec les exigences des recruteurs pour calculer un score de matching précis.</p>
            </div>

            <!-- Step 3 -->
            <div class="hitw-card">
                <div class="hitw-icon">
                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h3 class="hitw-title">3. Postulez aux meilleures offres</h3>
                <p class="hitw-text">Ne perdez plus votre temps à chercher, concentrez-vous sur les opportunités où vous avez les meilleures chances de réussite.</p>
            </div>
        </div>
    </div>
</section>

<!-- Job Offers Preview Section -->
<section id="offers-preview" class="offers-preview-section">
    <div class="container">
        <div class="section-heading text-center">
            <span class="section-subtitle">Opportunités récentes</span>
            <h2 class="section-title">Dernières offres d'emploi</h2>
            <p class="section-desc">Découvrez une sélection de nos offres récemment publiées par nos recruteurs partenaires.</p>
        </div>

        <?php if(empty($latestOffres)): ?>
            <div class="empty-state">
                <div class="empty-state-icon text-muted">
                    <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                </div>
                <p>Aucune offre disponible pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="offers-grid">
                <?php foreach($latestOffres as $offre): ?>
                    <div class="offer-card">
                        <div class="offer-cover" style="background-image: url('<?= getOfferImageUrl($offre['image']) ?>');">
                            <span class="offer-domain-badge"><?= e($offre['domaine']) ?></span>
                        </div>
                        <div class="offer-content">
                            <h3 class="offer-title"><?= e($offre['titre']) ?></h3>
                            <div class="offer-meta">
                                <span class="meta-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                                    Expérience: <?= (int)$offre['years_required'] ?> an<?= $offre['years_required'] > 1 ? 's' : '' ?>
                                </span>
                                <span class="meta-item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    <?= e($offre['first_name'] . ' ' . $offre['last_name']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="offer-footer">
                            <span class="offer-date">Publié le <?= date('d/m/Y', strtotime($offre['created_at'])) ?></span>
                            <!-- Button is non-functional in Sprint 1 as per spec -->
                            <button class="btn btn-outline btn-sm action-disabled" title="Disponible dans une prochaine version" onclick="alert('L\'inscription et la candidature seront disponibles dans le prochain sprint !');">Voir / Postuler</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
