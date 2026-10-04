/**
 * SmartMatch - Sprint 2
 * Admin > Offres : list, create, edit, delete through api.php (no page reload).
 */
(function () {
    'use strict';

    if (!document.getElementById('offresTableBody')) {
        return; // script only runs on offres.html
    }

    var state = {
        offres: [],
        recruteurs: [],
        editingId: null
    };

    document.addEventListener('DOMContentLoaded', init);

    async function init() {
        var authorized = await SmartMatchAuth.requireAdmin();
        if (!authorized) {
            return;
        }

        document.getElementById('openCreateBtn').addEventListener('click', function () { openModal(null); });
        document.getElementById('cancelModalBtn').addEventListener('click', closeModal);
        document.getElementById('closeModalBtn').addEventListener('click', closeModal);
        document.getElementById('offreForm').addEventListener('submit', onSubmit);
        document.getElementById('image').addEventListener('change', onImageChange);

        document.getElementById('offresTableBody').addEventListener('click', onTableClick);

        await Promise.all([loadRecruteurs(), loadOffres()]);
    }

    /* --------------------------- data loading --------------------------- */

    async function loadRecruteurs() {
        var select = document.getElementById('id_recruteur');
        var result = await SmartMatch.api.list('users');

        if (result.ok && Array.isArray(result.payload.data)) {
            state.recruteurs = result.payload.data.filter(function (user) {
                return user.role === 'recruteur' || user.role === 'admin';
            });
        } else {
            state.recruteurs = [];
        }

        var options = state.recruteurs.map(function (user) {
            var label = SmartMatch.escapeHtml((user.first_name || '') + ' ' + (user.last_name || '')) +
                ' (' + user.role + ')';
            return '<option value="' + user.id + '">' + label + '</option>';
        }).join('');

        select.innerHTML = '<option value="">-- Selectionner un recruteur --</option>' + options;
    }

    async function loadOffres() {
        var result = await SmartMatch.api.list('offres');

        if (!result.ok) {
            showToast('error', SmartMatch.errorMessages(result).join(' '));
            return;
        }

        state.offres = Array.isArray(result.payload.data) ? result.payload.data : [];
        renderTable();
    }

    /* ----------------------------- rendering ---------------------------- */

    function renderTable() {
        var tbody = document.getElementById('offresTableBody');
        var count = document.getElementById('offresCount');

        if (count) {
            count.textContent = state.offres.length;
        }

        if (!state.offres.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">' +
                'Aucune offre pour le moment. Cliquez sur "Ajouter une offre".</td></tr>';
            return;
        }

        tbody.innerHTML = state.offres.map(row).join('');
    }

    function recruteurLabel(idRecruteur) {
        var found = state.recruteurs.filter(function (user) {
            return Number(user.id) === Number(idRecruteur);
        })[0];

        if (found) {
            return (found.first_name || '') + ' ' + (found.last_name || '');
        }
        return 'Recruteur #' + idRecruteur;
    }

    function formatDate(value) {
        if (!value) {
            return '-';
        }
        var date = new Date(value);
        return isNaN(date.getTime()) ? '-' : date.toLocaleDateString('fr-FR');
    }

    function row(offre) {
        var image = SmartMatch.offreImageUrl(offre.image);
        var years = Number(offre.years_required || 0);

        return '' +
            '<tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">' +
                '<td class="px-4 py-3">' +
                    '<img src="' + image + '" alt="" class="h-12 w-16 rounded-lg object-cover"' +
                        ' onerror="this.onerror=null;this.src=\'' + SmartMatch.PLACEHOLDER_IMG + '\'">' +
                '</td>' +
                '<td class="px-4 py-3">' +
                    '<div class="font-semibold text-slate-800">' + SmartMatch.escapeHtml(offre.titre) + '</div>' +
                    '<div class="max-w-xs truncate text-xs text-slate-400">' + SmartMatch.escapeHtml(offre.description || '') + '</div>' +
                '</td>' +
                '<td class="px-4 py-3"><span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">' +
                    SmartMatch.escapeHtml(offre.domaine) + '</span></td>' +
                '<td class="px-4 py-3"><span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">' +
                    years + ' an' + (years > 1 ? 's' : '') + '</span></td>' +
                '<td class="px-4 py-3 text-sm text-slate-600">' + SmartMatch.escapeHtml(recruteurLabel(offre.id_recruteur)) + '</td>' +
                '<td class="px-4 py-3 text-sm text-slate-500">' + formatDate(offre.created_at) + '</td>' +
                '<td class="px-4 py-3">' +
                    '<div class="flex justify-end gap-2">' +
                        '<button type="button" data-edit="' + offre.id + '" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">Modifier</button>' +
                        '<button type="button" data-delete="' + offre.id + '" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">Supprimer</button>' +
                    '</div>' +
                '</td>' +
            '</tr>';
    }
    function onTableClick(event) {
        var editButton = event.target.closest('[data-edit]');
        if (editButton) {
            openModal(Number(editButton.getAttribute('data-edit')));
            return;
        }

        var deleteButton = event.target.closest('[data-delete]');
        if (deleteButton) {
            deleteOffre(Number(deleteButton.getAttribute('data-delete')));
        }
    }

    function onImageChange(event) {
        var file = event.target.files && event.target.files[0];
        if (file) {
            setImagePreview(URL.createObjectURL(file), true);
        }
    }

    function openModal(id) {
        state.editingId = (id === null || isNaN(id)) ? null : id;
        var offre = null;
        if (state.editingId !== null) {
            var found = state.offres.filter(function (item) {
                return Number(item.id) === Number(state.editingId);
            });
            offre = found.length ? found[0] : null;
        }

        clearFormErrors();
        document.getElementById('offreForm').reset();

        if (offre) {
            document.getElementById('modalTitle').textContent = "Modifier l'offre";
            document.getElementById('titre').value = offre.titre || '';
            document.getElementById('description').value = offre.description || '';
            document.getElementById('domaine').value = offre.domaine || '';
            document.getElementById('years_required').value = offre.years_required != null ? offre.years_required : 0;
            document.getElementById('id_recruteur').value = offre.id_recruteur != null ? String(offre.id_recruteur) : '';
            document.getElementById('imageRequiredHint').classList.add('hidden');
            setImagePreview(SmartMatch.offreImageUrl(offre.image), true);
        } else {
            document.getElementById('modalTitle').textContent = 'Ajouter une offre';
            document.getElementById('years_required').value = 0;
            document.getElementById('imageRequiredHint').classList.remove('hidden');
            setImagePreview(null, false);
        }

        document.getElementById('modalOffre').classList.remove('hidden');
        document.getElementById('modalOffre').classList.add('flex');
        document.getElementById('titre').focus();
    }

    function closeModal() {
        document.getElementById('modalOffre').classList.add('hidden');
        document.getElementById('modalOffre').classList.remove('flex');
        document.getElementById('offreForm').reset();
        clearFormErrors();
        setImagePreview(null, false);
        state.editingId = null;
    }

    function setImagePreview(src, show) {
        var image = document.getElementById('imagePreview');
        var wrap = document.getElementById('imagePreviewWrap');
        if (show && src) {
            image.src = src;
            wrap.classList.remove('hidden');
        } else {
            wrap.classList.add('hidden');
            image.removeAttribute('src');
        }
    }
    async function onSubmit(event) {
        event.preventDefault();
        clearFormErrors();

        var isEdit = state.editingId !== null;
        var fileInput = document.getElementById('image');
        var hasFile = !!(fileInput.files && fileInput.files.length);
        var submitButton = document.getElementById('submitBtn');
        submitButton.disabled = true;

        var result;
        if (!isEdit) {
            result = await SmartMatch.api.create('offres', new FormData(event.target));
        } else if (hasFile) {
            result = await SmartMatch.api.update('offres', state.editingId, new FormData(event.target));
        } else {
            result = await SmartMatch.api.updateJson('offres', state.editingId, {
                titre: document.getElementById('titre').value,
                description: document.getElementById('description').value,
                domaine: document.getElementById('domaine').value,
                years_required: document.getElementById('years_required').value,
                id_recruteur: document.getElementById('id_recruteur').value
            });
        }

        submitButton.disabled = false;
        if (result.status === 401 || result.status === 403) {
            window.location.replace('login.html');
            return;
        }

        if (!result.ok) {
            showFormErrors(SmartMatch.errorMessages(result));
            return;
        }

        closeModal();
        showToast('success', isEdit ? 'Offre mise a jour avec succes.' : 'Offre creee avec succes.');
        await loadOffres();
    }

    async function deleteOffre(id) {
        var confirmMessage = "Etes-vous certain de vouloir supprimer cette offre ? L'image associee sera egalement supprimee.";
        if (!window.confirm(confirmMessage)) {
            return;
        }

        var result = await SmartMatch.api.remove('offres', id);
        if (result.status === 401 || result.status === 403) {
            window.location.replace('login.html');
            return;
        }

        if (!result.ok) {
            showToast('error', SmartMatch.errorMessages(result).join(' '));
            return;
        }

        showToast('success', 'Offre supprimee avec succes.');
        await loadOffres();
    }
    function showFormErrors(errors) {
        var box = document.getElementById('formErrors');
        var items = errors.map(function (message) {
            return '<li>' + SmartMatch.escapeHtml(message) + '</li>';
        }).join('');
        box.innerHTML = items;
        box.classList.remove('hidden');
    }

    function clearFormErrors() {
        var box = document.getElementById('formErrors');
        box.innerHTML = '';
        box.classList.add('hidden');
    }

    var toastTimer = null;
    function showToast(type, message) {
        var toast = document.getElementById('toast');
        if (!toast) {
            return;
        }
        toast.textContent = message;
        toast.className = 'fixed bottom-6 right-6 z-50 rounded-xl px-5 py-3 text-sm font-semibold text-white shadow-lg ' +
            (type === 'success' ? 'bg-emerald-600' : 'bg-red-600');
        toast.classList.remove('hidden');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            toast.classList.add('hidden');
        }, 3500);
    }



})();
