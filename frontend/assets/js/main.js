/**
 * SmartMatch - Sprint 2
 * Main page logic: renders the latest offers fetched from api.php.
 */
document.addEventListener('DOMContentLoaded', function () {
    // Mobile navigation toggle
    var navToggle = document.getElementById('navToggle');
    var navMenu = document.getElementById('navMenu');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            navMenu.classList.toggle('open');
        });
        navMenu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                navMenu.classList.remove('open');
            });
        });
    }

    // Active section highlighting on scroll
    var sections = document.querySelectorAll('section[id]');
    var links = document.querySelectorAll('.nav-links .nav-link');

    window.addEventListener('scroll', function () {
        var current = '';
        sections.forEach(function (section) {
            var top = section.offsetTop - 100;
            if (window.scrollY >= top && window.scrollY < top + section.offsetHeight) {
                current = section.getAttribute('id');
            }
        });
        links.forEach(function (link) {
            link.classList.toggle('active', link.getAttribute('href') === '#' + current);
        });
    });

    loadLatestOffres();
});

/**
 * Fetch the offers from the API and render the first four as cards.
 */
async function loadLatestOffres() {
    var grid = document.getElementById('offersGrid');
    var loading = document.getElementById('offersLoading');
    var empty = document.getElementById('offersEmpty');
    if (!grid) {
        return;
    }

    var result = await SmartMatch.api.list('offres');

    if (loading) {
        loading.remove();
    }

    updateCounts(result);

    if (!result.ok || !Array.isArray(result.payload.data)) {
        if (empty) {
            empty.classList.remove('hidden');
            empty.textContent = SmartMatch.errorMessages(result).join(' ');
        }
        return;
    }

    var offres = result.payload.data.slice(0, 4);

    if (!offres.length) {
        if (empty) {
            empty.classList.remove('hidden');
        }
        return;
    }

    grid.innerHTML = offres.map(offreCard).join('');
}

/**
 * Update the hero counter and the section badge with live data.
 */
function updateCounts(result) {
    var total = (result.ok && Array.isArray(result.payload.data)) ? result.payload.data.length : 0;
    var hero = document.getElementById('heroOfferCount');
    if (hero) {
        hero.textContent = String(total);
    }
    var badge = document.getElementById('offersCountBadge');
    if (badge) {
        badge.textContent = total ? total + ' offre(s) en ligne' : 'Aucune offre';
    }
}

/**
 * Build the HTML markup of a single offer card.
 */
function offreCard(offre) {
    var image = SmartMatch.offreImageUrl(offre.image);
    var years = Number(offre.years_required || 0);
    var expLabel = years + (years > 1 ? ' ans' : ' an');
    var dateLabel = '';

    if (offre.created_at) {
        var date = new Date(offre.created_at);
        if (!isNaN(date.getTime())) {
            dateLabel = date.toLocaleDateString('fr-FR');
        }
    }

    var description = offre.description
        ? SmartMatch.escapeHtml(offre.description).slice(0, 120)
        : '';

    return '' +
        '<article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">' +
            '<div class="relative h-44 overflow-hidden bg-slate-100">' +
                '<img src="' + image + '" alt="' + SmartMatch.escapeHtml(offre.titre) + '"' +
                    ' class="h-full w-full object-cover transition duration-300 group-hover:scale-105"' +
                    ' onerror="this.onerror=null;this.src=\'' + SmartMatch.PLACEHOLDER_IMG + '\'">' +
                '<span class="absolute left-3 top-3 rounded-full bg-indigo-600/90 px-3 py-1 text-xs font-semibold text-white">' +
                    SmartMatch.escapeHtml(offre.domaine) +
                '</span>' +
            '</div>' +
            '<div class="flex flex-1 flex-col p-5">' +
                '<h3 class="text-lg font-semibold text-slate-900">' + SmartMatch.escapeHtml(offre.titre) + '</h3>' +
                '<p class="mt-2 flex-1 text-sm text-slate-500">' + description + '</p>' +
                '<div class="mt-4 flex flex-wrap items-center gap-3 text-sm text-slate-600">' +
                    '<span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-700">' +
                        '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>' +
                        'Experience : ' + expLabel +
                    '</span>' +
                '</div>' +
                '<div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4">' +
                    '<span class="text-xs text-slate-400">' + (dateLabel ? 'Publie le ' + dateLabel : '') + '</span>' +
                    '<button type="button" class="rounded-lg border border-indigo-200 px-4 py-2 text-sm font-semibold text-indigo-600 transition hover:bg-indigo-50"' +
                        ' onclick="alert(\'L\\\'inscription et la candidature seront disponibles dans un prochain sprint !\');">' +
                        'Voir / Postuler' +
                    '</button>' +
                '</div>' +
            '</div>' +
        '</article>';
}
