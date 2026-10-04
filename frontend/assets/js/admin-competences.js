/**
 * SmartMatch - Sprint 2
 * Admin > Competences : list, create, edit, delete through api.php (no reload).
 */
(function () {
    'use strict';

    if (!document.getElementById('competencesTableBody')) {
        return;
    }

    var state = { competences: [], editingId: null };

    document.addEventListener('DOMContentLoaded', init);

    async function init() {
        var authorized = await SmartMatchAuth.requireAdmin();
        if (!authorized) {
            return;
        }

        document.getElementById('openCreateBtn').addEventListener('click', function () { openModal(null); });
        document.getElementById('cancelModalBtn').addEventListener('click', closeModal);
        document.getElementById('closeModalBtn').addEventListener('click', closeModal);
        document.getElementById('competenceForm').addEventListener('submit', onSubmit);
        document.getElementById('competencesTableBody').addEventListener('click', onTableClick);

        await loadCompetences();
    }

    async function loadCompetences() {
        var result = await SmartMatch.api.list('competences');
        if (!result.ok) {
            showToast('error', SmartMatch.errorMessages(result).join(' '));
            return;
        }
        state.competences = Array.isArray(result.payload.data) ? result.payload.data : [];
        renderTable();
    }

    function renderTable() {
        var tbody = document.getElementById('competencesTableBody');
        var count = document.getElementById('competencesCount');
        if (count) {
            count.textContent = state.competences.length;
        }
        if (!state.competences.length) {
            tbody.innerHTML = '<tr><td colspan="3" class="px-4 py-10 text-center text-slate-400">' +
                'Aucune competence dans le catalogue. Cliquez sur "Ajouter une competence".</td></tr>';
            return;
        }
        tbody.innerHTML = state.competences.map(row).join('');
    }

    function row(competence) {
        return '' +
            '<tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">' +
                '<td class="px-4 py-3"><span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">#' +
                    SmartMatch.escapeHtml(competence.id) + '</span></td>' +
                '<td class="px-4 py-3 font-semibold text-slate-800">' +
                    SmartMatch.escapeHtml(competence.nom) + '</td>' +
                '<td class="px-4 py-3"><div class="flex justify-end gap-2">' +
                    '<button type="button" data-edit="' + competence.id + '" ' +
                        'class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">Modifier</button>' +
                    '<button type="button" data-delete="' + competence.id + '" ' +
                        'class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">Supprimer</button>' +
                '</div></td>' +
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
            deleteCompetence(Number(deleteButton.getAttribute('data-delete')));
        }
    }

    function openModal(id) {
        state.editingId = (id === null || isNaN(id)) ? null : id;
        var competence = null;
        if (state.editingId !== null) {
            var found = state.competences.filter(function (item) {
                return Number(item.id) === Number(state.editingId);
            });
            competence = found.length ? found[0] : null;
        }

        clearFormErrors();
        document.getElementById('competenceForm').reset();
        document.getElementById('modalTitle').textContent = competence ? 'Modifier la competence' : 'Ajouter une competence';
        document.getElementById('nom').value = competence ? (competence.nom || '') : '';
        document.getElementById('modalCompetence').classList.remove('hidden');
        document.getElementById('modalCompetence').classList.add('flex');
        document.getElementById('nom').focus();
    }

    function closeModal() {
        document.getElementById('modalCompetence').classList.add('hidden');
        document.getElementById('modalCompetence').classList.remove('flex');
        document.getElementById('competenceForm').reset();
        clearFormErrors();
        state.editingId = null;
    }

    async function onSubmit(event) {
        event.preventDefault();
        clearFormErrors();

        var isEdit = state.editingId !== null;
        var body = { nom: document.getElementById('nom').value.trim() };
        var submitButton = document.getElementById('submitBtn');
        submitButton.disabled = true;

        var result = isEdit
            ? await SmartMatch.api.updateJson('competences', state.editingId, body)
            : await SmartMatch.api.createJson('competences', body);

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
        showToast('success', isEdit ? 'Competence mise a jour avec succes.' : 'Competence creee avec succes.');
        await loadCompetences();
    }

    async function deleteCompetence(id) {
        if (!window.confirm('Etes-vous certain de vouloir supprimer cette competence ?')) {
            return;
        }

        var result = await SmartMatch.api.remove('competences', id);
        if (result.status === 401 || result.status === 403) {
            window.location.replace('login.html');
            return;
        }

        if (!result.ok) {
            showToast('error', SmartMatch.errorMessages(result).join(' '));
            return;
        }

        showToast('success', 'Competence supprimee avec succes.');
        await loadCompetences();
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
