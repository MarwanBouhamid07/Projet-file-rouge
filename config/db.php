<?php
/**
 * Database Connection using PDO
 * Sprint 1 - Recruitment Platform
 */

$host = 'localhost';
$port = '3306';
$dbname = 'recrutement_db';
$username = 'root';
$password = '12345678';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    $conn = $pdo; // Alias for flexibility
} catch (PDOException $e) {
    // In development show message, in production log
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

/**
 * Helper: Sanitize string output for HTML
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}
