<?php
/**
 * Admin Authentication Guard
 * Protects admin area: checks if user is logged in as 'admin'.
 * If not, redirects to admin/login.php.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $currentFile = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');

    // Check if we are inside a subfolder under admin/ (e.g., admin/offres/, admin/competences/, etc.)
    $currentDir = dirname($currentFile);
    if (basename(dirname($currentDir)) === 'admin' || (strpos($currentFile, '/admin/') !== false && basename($currentDir) !== 'admin')) {
        header('Location: ../login.php');
    } else {
        header('Location: login.php');
    }
    exit;
}
