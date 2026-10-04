<?php

/**
 * SmartMatch - Sprint 2
 * Single API entry point (router).
 *
 * Routing is based on the ?resource= query parameter and the HTTP method:
 *
 *   GET/POST/PUT/DELETE  api.php?resource=offres[&id=]      (GET is public, writes need admin)
 *   GET/POST/PUT/DELETE  api.php?resource=competences[&id=]  (admin only)
 *   GET                  api.php?resource=users            (admin only)
 *   GET                  api.php?resource=session          (no auth required)
 *   POST                 api.php?resource=login
 *   POST                 api.php?resource=logout
 *
 * Every response is JSON: { "success": true, "data": ... }
 *                    or: { "success": false, "errors": [ ... ] }
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/class.php';

define('DATA_DIR', __DIR__ . '/data');
define('UPLOADS_DIR', __DIR__ . '/data/uploads/offres');

/**
 * Send a JSON response with the given HTTP status and stop execution.
 */
function respond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Success response.
 */
function success($data, int $status = 200): void
{
    respond($status, ['success' => true, 'data' => $data]);
}

/**
 * Error response.
 */
function fail(array $errors, int $status = 400): void
{
    respond($status, ['success' => false, 'errors' => array_values($errors)]);
}

/**
 * Is the current request authenticated as an admin?
 */
function isAdmin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Guard every admin-only route (offres, competences, users).
 */
function requireAdmin(): void
{
    if (!isAdmin()) {
        fail(['Non autorisé'], 401);
    }
}

/**
 * Resolve the effective HTTP method, honouring an optional override header.
 */
function currentMethod(): string
{
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

    $override = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '';
    if ($override !== '') {
        $method = strtoupper((string) $override);
    }

    return $method;
}

/**
 * Read the request payload as an associative array.
 * Supports JSON bodies and regular form/multipart POST data.
 */
function readRequestBody(): array
{
    // PHP does not always populate CONTENT_TYPE (it can live in HTTP_CONTENT_TYPE,
    // or include a charset suffix), so inspect every plausible source header.
    $contentType = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '');
    if ($contentType === '' && function_exists('getallheaders')) {
        foreach (getallheaders() as $name => $value) {
            if (strcasecmp((string) $name, 'Content-Type') === 0) {
                $contentType = (string) $value;
                break;
            }
        }
    }

    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $decoded = json_decode($raw === false ? '' : $raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    if (!empty($_POST)) {
        return $_POST;
    }

    // Last resort: try to decode a raw JSON body even when the header is missing.
    $raw = file_get_contents('php://input');
    if ($raw !== false && $raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }

    return [];
}

/**
 * PHP does not populate $_POST / $_FILES for PUT requests, so we parse the
 * multipart body ourselves when an update sends an image replacement.
 */
function parseMultipartPut(): void
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (stripos($contentType, 'multipart/form-data') === false) {
        return;
    }

    if (!preg_match('/boundary=(?:"([^"]+)"|([^;]+))/i', $contentType, $matches)) {
        return;
    }

    $boundary = $matches[1] !== '' ? $matches[1] : trim($matches[2]);
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return;
    }

    $delimiter = '--' . $boundary;
    $parts = explode($delimiter, $raw);

    foreach ($parts as $part) {
        $part = ltrim($part, "\r\n");
        if ($part === '' || $part === '--' || strpos($part, '--') === 0) {
            continue;
        }

        $separator = strpos($part, "\r\n\r\n");
        if ($separator === false) {
            continue;
        }

        $rawHeaders = substr($part, 0, $separator);
        $body = substr($part, $separator + 4);
        $body = (string) preg_replace("/\r\n$/", '', $body);

        if (!preg_match('/name="([^"]*)"/i', $rawHeaders, $nameMatch)) {
            continue;
        }
        $name = $nameMatch[1];

        if (preg_match('/filename="([^"]*)"/i', $rawHeaders, $fileMatch)) {
            if ($fileMatch[1] === '') {
                continue; // empty file input
            }

            preg_match('/Content-Type:\s*([^\r\n]+)/i', $rawHeaders, $typeMatch);
            $type = isset($typeMatch[1]) ? trim($typeMatch[1]) : 'application/octet-stream';

            $tmp = tempnam(sys_get_temp_dir(), 'smatch_');
            if ($tmp === false) {
                continue;
            }
            file_put_contents($tmp, $body);

            $_FILES[$name] = [
                'name'     => $fileMatch[1],
                'type'     => $type,
                'tmp_name' => $tmp,
                'error'    => UPLOAD_ERR_OK,
                'size'     => strlen($body),
            ];
        } else {
            $_POST[$name] = $body;
        }
    }
}


/**
 * Map a real image MIME type (verified with getimagesize + finfo) to a safe
 * file extension. Returns null when the file is not an accepted image.
 */
function allowedImageExtension(string $path): ?string
{
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    $info = @getimagesize($path);
    if ($info === false || empty($info['mime'])) {
        return null;
    }

    $mime = $info['mime'];

    // Reinforce with finfo when the extension is available.
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $path);
            finfo_close($finfo);
            if (is_string($detected) && $detected !== '') {
                $mime = $detected;
            }
        }
    }

    return $allowed[$mime] ?? null;
}

/**
 * Validate an uploaded image against the sprint rules (format + size).
 * Appends human readable messages to $errors.
 */
function validateImageUpload(array $file, array &$errors): void
{
    $errorCode = $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($errorCode === UPLOAD_ERR_NO_FILE) {
        return;
    }

    if ($errorCode !== UPLOAD_ERR_OK) {
        $errors[] = "Échec du téléchargement de l'image (code : " . (int) $errorCode . ").";
        return;
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0) {
        $errors[] = "Le fichier image est vide.";
        return;
    }

    if ($size > 2 * 1024 * 1024) {
        $errors[] = "L'image ne doit pas dépasser 2 Mo.";
        return;
    }

    $clientExt = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
    if (!in_array($clientExt, ['jpg', 'jpeg', 'png', 'webp'], true)) {
        $errors[] = "Format d'image non autorisé. Utilisez .jpg, .jpeg, .png ou .webp.";
        return;
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_file($tmp) || allowedImageExtension($tmp) === null) {
        $errors[] = "Le fichier téléchargé n'est pas une image valide.";
    }
}

/**
 * Store an uploaded image under backend/data/uploads/offres/ with a unique
 * generated name. Returns the generated file name, or null on failure.
 */
function storeImageFile(array $file): ?string
{
    if (!is_dir(UPLOADS_DIR)) {
        mkdir(UPLOADS_DIR, 0777, true);
    }

    $tmp = (string) ($file['tmp_name'] ?? '');
    $ext = allowedImageExtension($tmp);
    if ($ext === null) {
        return null;
    }

    $name = uniqid('offre_') . '.' . $ext;
    $destination = UPLOADS_DIR . '/' . $name;

    if (is_uploaded_file($tmp)) {
        return move_uploaded_file($tmp, $destination) ? $name : null;
    }

    if (@rename($tmp, $destination)) {
        return $name;
    }

    if (@copy($tmp, $destination)) {
        @unlink($tmp);
        return $name;
    }

    return null;
}

/**
 * Delete an offer image from disk (basename guards against path traversal).
 */
function removeOffreImage(?string $image): void
{
    if ($image === null || $image === '') {
        return;
    }

    $path = UPLOADS_DIR . '/' . basename($image);
    if (is_file($path)) {
        @unlink($path);
    }
}


/* ------------------------------------------------------------------ */
/* Handlers                                                            */
/* ------------------------------------------------------------------ */

/**
 * Current session information (no auth required).
 * GET api.php?resource=session -> { authenticated, user } used by admin-auth.js
 * and by the public main page without leaking any protected data.
 */
function handleSession(string $method): void
{
    if ($method !== 'GET') {
        fail(['Méthode non autorisée.'], 405);
    }

    if (!isAdmin()) {
        success(['authenticated' => false, 'user' => null]);
        return;
    }

    success([
        'authenticated' => true,
        'user' => [
            'id' => $_SESSION['user_id'] ?? null,
            'role' => $_SESSION['role'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
        ],
    ]);
}

function handleLogin(string $method): void
{
    if ($method !== 'POST') {
        fail(['Méthode non autorisée.'], 405);
    }

    $body = readRequestBody();
    $email = trim((string) ($body['email'] ?? ''));
    $password = (string) ($body['password'] ?? '');

    if ($email === '' || $password === '') {
        fail(["L'email et le mot de passe sont obligatoires."], 422);
    }

    $user = (new User())->verifyPassword($email, $password);
    if ($user === null) {
        fail(['Identifiants invalides. Vérifiez votre email et votre mot de passe.'], 401);
    }

    if (($user['role'] ?? '') !== 'admin') {
        fail(["Accès refusé : ce compte ne dispose pas des privilèges administrateur."], 403);
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user_name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    $_SESSION['user_email'] = $user['email'];

    success(User::publicView($user));
}

function handleLogout(string $method): void
{
    if ($method !== 'POST') {
        fail(['Méthode non autorisée.'], 405);
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();

    success(['message' => 'Déconnexion réussie.']);
}

/**
 * Shared create/update logic for offers (with image upload handling).
 */
function handleOffreSave(Offre $model, array $data, ?int $id): void
{
    $file = $_FILES['image'] ?? null;
    $hasUpload = is_array($file) && (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE);

    $payload = [
        'titre'          => trim((string) ($data['titre'] ?? '')),
        'description'    => trim((string) ($data['description'] ?? '')),
        'domaine'        => trim((string) ($data['domaine'] ?? '')),
        'years_required' => $data['years_required'] ?? null,
        'id_recruteur'   => $data['id_recruteur'] ?? null,
    ];

    if ($payload['years_required'] !== null && $payload['years_required'] !== ''
        && filter_var($payload['years_required'], FILTER_VALIDATE_INT) !== false) {
        $payload['years_required'] = (int) $payload['years_required'];
    }
    if ($payload['id_recruteur'] !== null && $payload['id_recruteur'] !== '') {
        $payload['id_recruteur'] = (int) $payload['id_recruteur'];
    }

    // Validation lives in class.php, re-checked here before saving.
    $errors = $model->validate($payload, $id);

    if ($id === null && !$hasUpload) {
        $errors[] = "L'image est obligatoire lors de la création d'une offre.";
    }

    if ($hasUpload) {
        validateImageUpload($file, $errors);
    }

    if (!empty($errors)) {
        fail($errors, 422);
    }

    // --- Create -----------------------------------------------------
    if ($id === null) {
        $payload['image'] = null;
        $payload['created_at'] = gmdate('Y-m-d\TH:i:s\Z');

        if ($hasUpload) {
            $name = storeImageFile($file);
            if ($name === null) {
                fail(["Impossible d'enregistrer l'image téléchargée."], 500);
            }
            $payload['image'] = $name;
        }

        success($model->create($payload), 201);
        return;
    }

    // --- Update -----------------------------------------------------
    $existing = $model->find($id);
    if ($existing === null) {
        fail(['Offre introuvable.'], 404);
    }

    if ($hasUpload) {
        $name = storeImageFile($file);
        if ($name === null) {
            fail(["Impossible d'enregistrer l'image téléchargée."], 500);
        }
        removeOffreImage($existing['image'] ?? null);
        $payload['image'] = $name;
    }

    success($model->update($id, $payload));
}


function handleOffres(string $method, ?int $id): void
{
    $model = new Offre();

    switch ($method) {
        case 'GET':
            if ($id !== null) {
                $row = $model->find($id);
                if ($row === null) {
                    fail(['Offre introuvable.'], 404);
                }
                success($row);
            }

            $rows = $model->all();
            usort($rows, static function (array $a, array $b): int {
                $cmp = strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
                return $cmp !== 0 ? $cmp : ((int) ($b['id'] ?? 0) <=> (int) ($a['id'] ?? 0));
            });
            success($rows);
            break;

        case 'POST':
            handleOffreSave($model, readRequestBody(), null);
            break;

        case 'PUT':
            if ($id === null) {
                fail(['Paramètre id manquant.'], 422);
            }
            if ($model->find($id) === null) {
                fail(['Offre introuvable.'], 404);
            }
            handleOffreSave($model, readRequestBody(), $id);
            break;

        case 'DELETE':
            if ($id === null) {
                fail(['Paramètre id manquant.'], 422);
            }
            $row = $model->find($id);
            if ($row === null) {
                fail(['Offre introuvable.'], 404);
            }
            removeOffreImage($row['image'] ?? null);
            $model->delete($id);
            success(['id' => $id]);
            break;

        default:
            fail(['Méthode non autorisée.'], 405);
    }
}

function handleCompetences(string $method, ?int $id): void
{
    $model = new Competence();

    switch ($method) {
        case 'GET':
            if ($id !== null) {
                $row = $model->find($id);
                if ($row === null) {
                    fail(['Compétence introuvable.'], 404);
                }
                success($row);
            }

            $rows = $model->all();
            usort($rows, static fn(array $a, array $b): int
                => strcasecmp((string) ($a['nom'] ?? ''), (string) ($b['nom'] ?? '')));
            success($rows);
            break;

        case 'POST':
            $payload = ['nom' => trim((string) (readRequestBody()['nom'] ?? ''))];
            $errors = $model->validate($payload);
            if (!empty($errors)) {
                fail($errors, 422);
            }
            success($model->create($payload), 201);
            break;

        case 'PUT':
            if ($id === null) {
                fail(['Paramètre id manquant.'], 422);
            }
            if ($model->find($id) === null) {
                fail(['Compétence introuvable.'], 404);
            }
            $payload = ['nom' => trim((string) (readRequestBody()['nom'] ?? ''))];
            $errors = $model->validate($payload, $id);
            if (!empty($errors)) {
                fail($errors, 422);
            }
            success($model->update($id, $payload));
            break;

        case 'DELETE':
            if ($id === null) {
                fail(['Paramètre id manquant.'], 422);
            }
            if ($model->find($id) === null) {
                fail(['Compétence introuvable.'], 404);
            }
            $model->delete($id);
            success(['id' => $id]);
            break;

        default:
            fail(['Méthode non autorisée.'], 405);
    }
}

/**
 * Read-only user list (used to populate the recruiter selector).
 * Passwords are never returned.
 */
function handleUsers(string $method): void
{
    if ($method !== 'GET') {
        fail(['Méthode non autorisée.'], 405);
    }

    $rows = array_map(
        static fn(array $user): array => User::publicView($user),
        (new User())->all()
    );

    success($rows);
}


/* ------------------------------------------------------------------ */
/* Router                                                              */
/* ------------------------------------------------------------------ */

$method = currentMethod();
$resource = strtolower(trim((string) ($_GET['resource'] ?? '')));
$id = (isset($_GET['id']) && $_GET['id'] !== '') ? (int) $_GET['id'] : null;

if ($method === 'PUT') {
    parseMultipartPut();
}

try {
    switch ($resource) {
        case 'login':
            handleLogin($method);
            break;

        case 'logout':
            handleLogout($method);
            break;

        case 'offres':
            // Spec §5 / §8 / §10: every offres route requires an admin session.
            // The public main page (index.html) fetches through an admin session
            // cookie: this single rule is what admin-auth.js probes for 401.
            requireAdmin();
            handleOffres($method, $id);
            break;

        case 'competences':
            requireAdmin();
            handleCompetences($method, $id);
            break;

        case 'users':
            requireAdmin();
            handleUsers($method);
            break;

        case 'session':
            handleSession($method);
            break;

        default:
            fail(['Ressource inconnue'], 404);
    }
} catch (Throwable $e) {
    fail(['Erreur serveur : ' . $e->getMessage()], 500);
}

