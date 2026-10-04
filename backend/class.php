<?php

/**
 * SmartMatch - Sprint 2
 * Recruitment Platform with Smart Matching.
 *
 * All backend classes live in this single file (required once by api.php).
 * Data is persisted in JSON files - no database is used in Sprint 2.
 *
 *  - JsonStorage : generic CRUD over one JSON file (base class)
 *  - Offre       : extends JsonStorage (data/offres.json)
 *  - Competence  : extends JsonStorage (data/competences.json)
 *  - User        : extends JsonStorage (data/users.json) + auth helpers
 */

class JsonStorage
{
    /** Absolute path to the JSON file backing this storage. */
    protected string $file;

    public function __construct(string $file)
    {
        $this->file = $file;

        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!file_exists($this->file)) {
            file_put_contents($this->file, '[]');
        }
    }

    /**
     * Return every row as an associative array.
     */
    public function all(): array
    {
        return $this->read();
    }

    /**
     * Find a single row by its integer id, or null when not found.
     */
    public function find(int $id): ?array
    {
        foreach ($this->read() as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Append a new row, assigning it the next available id.
     */
    public function create(array $data): array
    {
        $rows = $this->read();
        $data['id'] = $this->nextId($rows);
        $rows[] = $data;
        $this->save($rows);

        return $data;
    }

    /**
     * Merge $data into the row with the given id and persist it.
     * Returns the updated row, or null when the id does not exist.
     */
    public function update(int $id, array $data): ?array
    {
        $rows = $this->read();

        foreach ($rows as $index => $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                $rows[$index] = array_merge($row, $data, ['id' => $id]);
                $this->save($rows);

                return $rows[$index];
            }
        }

        return null;
    }

    /**
     * Remove the row with the given id. Returns true when a row was removed.
     */
    public function delete(int $id): bool
    {
        $rows = $this->read();
        $found = false;
        $kept = [];

        foreach ($rows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                $found = true;
                continue;
            }
            $kept[] = $row;
        }

        if ($found) {
            $this->save($kept);
        }

        return $found;
    }

    /**
     * Read the JSON file using a shared lock and decode it to an array.
     */
    protected function read(): array
    {
        if (!file_exists($this->file)) {
            return [];
        }

        $handle = fopen($this->file, 'r');
        if ($handle === false) {
            return [];
        }

        $contents = '';
        if (flock($handle, LOCK_SH)) {
            $contents = stream_get_contents($handle) ?: '';
            flock($handle, LOCK_UN);
        }
        fclose($handle);

        if (trim($contents) === '') {
            return [];
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Compute the next id (max existing id + 1).
     */
    protected function nextId(array $rows): int
    {
        $max = 0;

        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > $max) {
                $max = $id;
            }
        }

        return $max + 1;
    }

    /**
     * Persist the rows with an exclusive lock (flock) to avoid corruption
     * when several requests write at the same time.
     */
    protected function save(array $rows): void
    {
        $json = json_encode(
            array_values($rows),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            return;
        }

        $handle = fopen($this->file, 'c');
        if ($handle === false) {
            return;
        }

        if (flock($handle, LOCK_EX)) {
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, $json);
            fflush($handle);
            flock($handle, LOCK_UN);
        }

        fclose($handle);
    }
}

class Offre extends JsonStorage
{
    public function __construct(?string $file = null)
    {
        parent::__construct($file ?? __DIR__ . '/data/offres.json');
    }

    /**
     * Validate an offer payload.
     * Returns a list of error messages (empty array = valid).
     *
     * @param array    $data     The offer fields to validate.
     * @param int|null $ignoreId Row id to ignore (used on update).
     */
    public function validate(array $data, ?int $ignoreId = null): array
    {
        $errors = [];

        $titre = trim((string) ($data['titre'] ?? ''));
        if ($titre === '') {
            $errors[] = "Le titre de l'offre est obligatoire.";
        } elseif (mb_strlen($titre) > 150) {
            $errors[] = "Le titre ne doit pas dépasser 150 caractères.";
        }

        $domaine = trim((string) ($data['domaine'] ?? ''));
        if ($domaine === '') {
            $errors[] = "Le domaine est obligatoire.";
        } elseif (mb_strlen($domaine) > 100) {
            $errors[] = "Le domaine ne doit pas dépasser 100 caractères.";
        }

        $years = $data['years_required'] ?? null;
        if ($years === null || $years === '') {
            $errors[] = "Les années d'expérience requises sont obligatoires.";
        } elseif (filter_var($years, FILTER_VALIDATE_INT) === false || (int) $years < 0) {
            $errors[] = "Les années d'expérience doivent être un entier positif ou nul.";
        }

        $idRecruteur = $data['id_recruteur'] ?? null;
        if ($idRecruteur === null || $idRecruteur === '') {
            $errors[] = "Le recruteur est obligatoire.";
        } else {
            $recruteur = (new User())->find((int) $idRecruteur);
            if ($recruteur === null) {
                $errors[] = "Le recruteur sélectionné n'existe pas.";
            } elseif (!in_array($recruteur['role'] ?? '', ['recruteur', 'admin'], true)) {
                $errors[] = "L'utilisateur sélectionné n'a pas le rôle recruteur.";
            }
        }

        return $errors;
    }
}

class Competence extends JsonStorage
{
    public function __construct(?string $file = null)
    {
        parent::__construct($file ?? __DIR__ . '/data/competences.json');
    }

    /**
     * Validate a competence payload: nom required, max 80 chars, unique (case-insensitive).
     *
     * @param array    $data     The competence fields to validate.
     * @param int|null $ignoreId Row id to ignore (used on update).
     */
    public function validate(array $data, ?int $ignoreId = null): array
    {
        $errors = [];

        $nom = trim((string) ($data['nom'] ?? ''));

        if ($nom === '') {
            $errors[] = "Le nom de la compétence est obligatoire.";
            return $errors;
        }

        if (mb_strlen($nom) > 80) {
            $errors[] = "Le nom de la compétence ne doit pas dépasser 80 caractères.";
            return $errors;
        }

        $needle = mb_strtolower($nom);
        foreach ($this->all() as $row) {
            if ($ignoreId !== null && (int) ($row['id'] ?? 0) === $ignoreId) {
                continue;
            }
            if (mb_strtolower(trim((string) ($row['nom'] ?? ''))) === $needle) {
                $errors[] = "Cette compétence existe déjà dans le catalogue.";
                break;
            }
        }

        return $errors;
    }
}


class User extends JsonStorage
{
    public function __construct(?string $file = null)
    {
        parent::__construct($file ?? __DIR__ . '/data/users.json');
    }

    /**
     * Case-insensitive lookup of a user by email.
     */
    public function findByEmail(string $email): ?array
    {
        $needle = mb_strtolower(trim($email));

        foreach ($this->all() as $row) {
            if (mb_strtolower(trim((string) ($row['email'] ?? ''))) === $needle) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Verify the given credentials. Returns the user row on success, null otherwise.
     */
    public function verifyPassword(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if ($user === null) {
            return null;
        }

        if (!password_verify($password, (string) ($user['password'] ?? ''))) {
            return null;
        }

        return $user;
    }

    /**
     * Return the public representation of a user (password removed).
     */
    public static function publicView(array $user): array
    {
        unset($user['password']);

        return $user;
    }
}

