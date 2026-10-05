/**
 * SmartMatch - Sprint 2
 * Admin authentication helper.
 *
 * There is no client-side token: the PHP session cookie is the single source
 * of truth. Every admin page pings a protected route and redirects to the
 * login page when the API answers 401 / 403.
 */
(function (global) {
    'use strict';

    var LOGIN_PAGE = 'login.html';
    var DASHBOARD_PAGE = 'dashboard.html';

    /**
     * Lightweight session check: GET resource=session is auth-free and only
     * reports { authenticated, user } — never any protected data — so it is
     * safe to call from any page (public or admin).
     */
    async function isAuthorized() {
        var result = await SmartMatch.apiRequest('session');
        if (!result.ok) {
            return false;
        }
        var data = (result.payload && result.payload.data) || {};
        if (data.authenticated === true) {
            return true;
        }
        // Fallback: a protected route (competences is always admin-only)
        // answering 401/403 proves the session is not elevated.
        var probe = await SmartMatch.apiRequest('competences');
        if (probe.status === 401 || probe.status === 403) {
            return false;
        }
        return probe.ok;
    }

    /**
     * Redirect to the login page when the current visitor is not an admin.
     * Resolves true when the session is valid.
     */
    async function requireAdmin() {
        var authorized = await isAuthorized();
        if (!authorized) {
            global.location.replace(LOGIN_PAGE);
            return false;
        }
        bindLogoutButtons();
        return true;
    }

    /**
     * Used on login.html: when already authenticated, jump to the dashboard.
     */
    async function redirectIfAuthenticated() {
        var authorized = await isAuthorized();
        if (authorized) {
            global.location.replace(DASHBOARD_PAGE);
            return true;
        }
        return false;
    }

    /** Destroy the session server-side then go back to the login page. */
    async function logout() {
        await SmartMatch.api.logout();
        global.location.replace(LOGIN_PAGE);
    }

    /** Wire up every element carrying the data-logout attribute. */
    function bindLogoutButtons() {
        document.querySelectorAll('[data-logout]').forEach(function (element) {
            element.addEventListener('click', function (event) {
                event.preventDefault();
                logout();
            });
        });
    }

    global.SmartMatchAuth = {
        isAuthorized: isAuthorized,
        requireAdmin: requireAdmin,
        redirectIfAuthenticated: redirectIfAuthenticated,
        logout: logout
    };
})(window);
