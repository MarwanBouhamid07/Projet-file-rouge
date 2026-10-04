/**
 * SmartMatch - Sprint 2
 * Shared API wrapper used by every frontend page.
 * All pages talk to the backend ONLY through this file (fetch -> api.php).
 */
(function (global) {
    'use strict';

    var path = global.location.pathname.replace(/\\/g, '/');
    var IS_ADMIN_PAGE = /\/admin\/[^/]*$/.test(path);

    // Resolve the backend entry point relative to the current page location.
    var API_BASE = IS_ADMIN_PAGE ? '../../backend/api.php' : '../backend/api.php';
    var UPLOADS_BASE = API_BASE.replace(/api\.php$/, 'data/uploads/offres/');
    var PLACEHOLDER_IMG = IS_ADMIN_PAGE
        ? '../assets/img/placeholder-offre.png'
        : 'assets/img/placeholder-offre.png';

    /**
     * Perform a request against the API and always resolve with
     * { ok, status, payload } (never throws).
     */
    async function apiRequest(resource, options) {
        options = options || {};
        var method = options.method || 'GET';

        var url = API_BASE + '?resource=' + encodeURIComponent(resource);
        if (options.id !== undefined && options.id !== null) {
            url += '&id=' + encodeURIComponent(options.id);
        }

        var fetchOptions = {
            method: method,
            credentials: 'same-origin',
            headers: {}
        };

        if (options.formData) {
            fetchOptions.body = options.formData; // browser sets the multipart boundary
        } else if (options.body !== undefined && options.body !== null) {
            fetchOptions.headers['Content-Type'] = 'application/json';
            fetchOptions.body = JSON.stringify(options.body);
        }

        var response;
        try {
            response = await fetch(url, fetchOptions);
        } catch (error) {
            return {
                ok: false,
                status: 0,
                payload: {
                    success: false,
                    errors: ['Erreur reseau : impossible de contacter le serveur.']
                }
            };
        }

        var payload = null;
        try {
            payload = await response.json();
        } catch (error) {
            payload = null;
        }

        if (!payload || typeof payload !== 'object') {
            payload = {
                success: response.ok,
                errors: response.ok ? [] : ['Reponse invalide du serveur.']
            };
        }

        return {
            ok: response.ok && payload.success !== false,
            status: response.status,
            payload: payload
        };
    }

    /** Extract a displayable list of error messages from a result. */
    function errorMessages(result) {
        if (result && result.payload && Array.isArray(result.payload.errors) && result.payload.errors.length) {
            return result.payload.errors;
        }
        return ['Une erreur inattendue est survenue.'];
    }

    /** Build the URL of an offer image (falls back to the placeholder). */
    function offreImageUrl(name) {
        return name ? UPLOADS_BASE + encodeURIComponent(name) : PLACEHOLDER_IMG;
    }

    /** Escape user supplied strings before injecting them in innerHTML. */
    function escapeHtml(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    var api = {
        list: function (resource) { return apiRequest(resource); },
        get: function (resource, id) { return apiRequest(resource, { id: id }); },
        create: function (resource, formData) { return apiRequest(resource, { method: 'POST', formData: formData }); },
        createJson: function (resource, body) { return apiRequest(resource, { method: 'POST', body: body }); },
        update: function (resource, id, formData) { return apiRequest(resource, { method: 'PUT', id: id, formData: formData }); },
        updateJson: function (resource, id, body) { return apiRequest(resource, { method: 'PUT', id: id, body: body }); },
        remove: function (resource, id) { return apiRequest(resource, { method: 'DELETE', id: id }); },
        login: function (email, password) { return apiRequest('login', { method: 'POST', body: { email: email, password: password } }); },
        logout: function () { return apiRequest('logout', { method: 'POST' }); }
    };

    global.SmartMatch = {
        API_BASE: API_BASE,
        IS_ADMIN_PAGE: IS_ADMIN_PAGE,
        PLACEHOLDER_IMG: PLACEHOLDER_IMG,
        api: api,
        apiRequest: apiRequest,
        errorMessages: errorMessages,
        offreImageUrl: offreImageUrl,
        escapeHtml: escapeHtml
    };
})(window);
