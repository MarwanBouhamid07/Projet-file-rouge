# Sprint 1 — Technical Specification
## Recruitment Platform with Smart Matching (Réalisation)

**Scope of this sprint:** Build ONLY two things — (1) the public **Main Page** and (2) the **Admin Dashboard with full CRUD**. Nothing else described below (candidate self-registration, job application flow, the matching score algorithm, recruiter-only dashboard) is part of this sprint. Do not implement it yet.

---

## 1. Tech Stack

| Layer | Technology |
|---|---|
| Backend | **Plain PHP** (procedural or simple OOP), no framework |
| Database access | PDO with prepared statements |
| Database | MySQL |
| Frontend | HTML + **plain CSS** (no SASS) + **plain JavaScript** (no framework) |
| Version control | Git |

---

## 2. Database (implement exactly as-is)

### Table: `users`
Single auth table, shared across roles via the `role` column.

| Column | Type | Constraints |
|---|---|---|
| id | INT | PK, AUTO_INCREMENT |
| last_name | VARCHAR(50) | NOT NULL |
| first_name | VARCHAR(50) | NOT NULL |
| email | VARCHAR(100) | NOT NULL, UNIQUE |
| password | VARCHAR(255) | NOT NULL |
| role | ENUM('candidat','recruteur','admin') | NOT NULL, DEFAULT 'candidat' |
| domaine | VARCHAR(100) | NULLABLE (candidate's field) |
| years_experience | INT | NULLABLE (candidate's experience) |
| created_at / updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP / ON UPDATE CURRENT_TIMESTAMP |

### Table: `offres`
| Column | Type | Constraints |
|---|---|---|
| id_offre | INT | PK, AUTO_INCREMENT |
| titre | VARCHAR(150) | NOT NULL |
| description | TEXT | NULLABLE |
| domaine | VARCHAR(100) | NOT NULL |
| years_required | INT | NOT NULL, DEFAULT 0 |
| image | VARCHAR(255) | NULLABLE — stores the uploaded file name (e.g. `offre_65f1a2.jpg`) |
| id_recruteur | INT | NOT NULL, FK → users.id (role = recruteur or admin) |
| created_at / updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP / ON UPDATE CURRENT_TIMESTAMP |

### Table: `competences`
| Column | Type | Constraints |
|---|---|---|
| id_competence | INT | PK, AUTO_INCREMENT |
| nom | VARCHAR(80) | NOT NULL, UNIQUE |

### Junction table: `candidat_competence` (schema only — no UI in Sprint 1)
| Column | Type | Constraints |
|---|---|---|
| id_user | INT | PK (composite), FK → users.id |
| id_competence | INT | PK (composite), FK → competences.id_competence |

### Junction table: `offre_competence` (schema only — no UI in Sprint 1)
| Column | Type | Constraints |
|---|---|---|
| id_offre | INT | PK (composite), FK → offres.id_offre |
| id_competence | INT | PK (composite), FK → competences.id_competence |

### Table: `candidatures` (schema only — NOT used in Sprint 1)
Will later store each application plus its computed `match_score`. Out of scope for this sprint — create the table only, no page/logic for it yet.

---

## 3. Page 1 — Main Page (Landing Page)

**File:** `public/index.php` (public, no login required)

**Purpose:** Present the platform to a first-time visitor. Static/informational only — no forms, no database writes except the read-only offer preview.

**Required sections:**
1. **Header / Navbar** — logo, link to "Login" (placeholder link is fine for now)
2. **Hero section** — platform name, one-line pitch ("Find the job that truly matches your profile"), call-to-action button (can be non-functional for this sprint)
3. **"How it works" section** — 3 short blocks: create your profile → we compute your match score → apply to the best-fit offers (text + icon, no dynamic data)
4. **Preview of job offers** — fetch 3–4 offers from the `offres` table with PDO and display **each with its image** (`<img>` pointing to `assets/uploads/offres/{image}`, with a placeholder image if `image` is NULL), title, domain, years required — read-only, no filters, no pagination needed yet
5. **Footer** — basic links, copyright

**Explicitly out of scope for this page in Sprint 1:**
- Candidate registration / login forms (functional)
- Any filtering or search of offers
- Displaying any match score (the algorithm does not exist yet)

---

## 4. Page 2 — Admin Dashboard (Full CRUD)

**Folder:** `admin/` (protected — every file in this folder must start with an include that checks `$_SESSION['role'] === 'admin'`, otherwise redirect to `admin/login.php`)

**Layout:** Sidebar navigation + main content area (same layout pattern as the Fitness prototype dashboard — sidebar left, stat cards + tables on the right). Build one shared `includes/admin_layout_top.php` and `includes/admin_layout_bottom.php` (or a sidebar include) so every admin page shares the same look without repeating HTML.

**Sidebar links:**
- Dashboard (overview / stats)
- Offres
- Compétences
- Candidats (read-only list)

### 4.1 Dashboard Overview (`admin/dashboard.php`)
Simple stat cards, read-only (plain `SELECT COUNT(*)` queries):
- Total offres
- Total candidats (`users` WHERE `role = 'candidat'`)
- Total recruteurs
- Total compétences in the catalog

### 4.2 Offres CRUD (`admin/offres/`)

| File | Behavior |
|---|---|
| `index.php` | Read (list): table with image thumbnail, titre, domaine, years_required, recruteur name, actions (edit/delete) |
| `create.php` | Form (GET): titre, description, domaine, years_required, id_recruteur (select — only users with role=recruteur or admin), **image** (`<input type="file" name="image" accept="image/jpeg,image/png,image/webp">`). Form must use `enctype="multipart/form-data"` and method POST, submitting to `store.php` |
| `store.php` | Handles the POST from `create.php`: validates input, uploads the image (see §6), inserts the row with PDO prepared statement, redirects to `index.php` |
| `edit.php?id=` | Same form as create, pre-filled with existing values; shows the current image with an option to replace it; image field is optional here (keep current image if none uploaded) |
| `update.php` | Handles the POST from `edit.php`: if a new image was uploaded, delete the old file and save the new one; otherwise keep the existing `image` value; updates the row |
| `delete.php?id=` | Confirm (JS `confirm()`) before deleting; delete the image file from disk as well as the database row |

### 4.3 Compétences CRUD (`admin/competences/`)

| File | Behavior |
|---|---|
| `index.php` | Simple table: nom, actions |
| `create.php` + `store.php` | Form: nom (must be unique — show a clear validation error on duplicate, checked with a `SELECT` before inserting) |
| `edit.php?id=` + `update.php` | Same field, pre-filled |
| `delete.php?id=` | Confirm before deleting |

### 4.4 Candidats (`admin/candidats/index.php`) — read-only in Sprint 1

Table: name, email, domaine, years_experience — no create/edit/delete yet (candidates self-register in a later sprint).

---

## 5. Required Folder Structure

```
/config/
  db.php                    (PDO connection, reused via require)

/includes/
  admin_auth_check.php      (session/role check — required at the top of every admin/* file)
  admin_layout_top.php      (opens <html>, sidebar, opens <main>)
  admin_layout_bottom.php   (closes <main>, </html>)
  header.php                (public site navbar)
  footer.php                (public site footer)

/public/
  index.php                 (main page)
  assets/
    css/
      style.css              (public site styles)
    js/
      main.js                (public site scripts — e.g. mobile nav toggle)
    uploads/
      offres/                (uploaded offer images land here)

/admin/
  login.php
  logout.php
  dashboard.php
  assets/
    css/
      admin.css
    js/
      admin.js               (e.g. delete-confirmation dialogs)
  offres/
    index.php
    create.php
    store.php
    edit.php
    update.php
    delete.php
  competences/
    index.php
    create.php
    store.php
    edit.php
    update.php
    delete.php
  candidats/
    index.php

/sql/
  schema.sql                 (CREATE TABLE statements for all tables listed in §2)
```

---

## 6. Image Upload Rules (for `offres.image`)

- Accepted formats: `.jpg`, `.jpeg`, `.png`, `.webp`
- Max file size: 2 MB
- Validate the real file type server-side (e.g. `finfo` / `getimagesize()`), never trust the file extension alone
- Store the file under `public/assets/uploads/offres/` with a **generated unique name** (e.g. `uniqid('offre_') . '.' . $extension`) — never trust or reuse the original uploaded file name
- Save only the generated file name (not the full path) in the `offres.image` column
- On the main page and in `admin/offres/index.php`, if `image` is NULL or the file is missing, show a placeholder image instead of a broken `<img>` tag
- On `delete.php` and on `update.php` (when a new image replaces an old one), delete the old file from disk with `unlink()` after confirming the database operation succeeded

---

## 7. Validation Rules (minimum required)

- `titre` (offre): required, max 150 characters
- `domaine` (offre): required, max 100 characters
- `years_required`: required, integer, min 0
- `id_recruteur`: required, must exist in `users` table with `role` in (`recruteur`, `admin`) — check with a `SELECT` before inserting
- `image`: see §6 for upload-specific rules; required on create, optional on edit
- `nom` (competence): required, max 80 characters, unique — check with a `SELECT` before inserting

All forms must re-display entered values and show validation errors inline when the form is redisplayed after a failed submission (store the errors and old input in `$_SESSION` or pass them back before the redirect, since this is plain PHP with no framework helpers).

---

## 8. Explicitly OUT of scope for Sprint 1

Do not build any of the following yet — they belong to later sprints:

- Candidate self-registration / login (public side)
- Job offer browsing/filtering page for candidates
- Application ("postuler") flow and the `candidatures` table logic
- The match-score calculation algorithm itself
- Linking competences to candidates or offers through a UI (junction tables exist in the schema only)
- Recruiter-only dashboard (recruiter managing only their own offers)
- Any styling framework (Bootstrap/Tailwind) — plain CSS only, as specified in §1

---

## 9. Definition of Done for Sprint 1

- [ ] `sql/schema.sql` creates `users`, `offres` (with `image`), `competences`, `candidat_competence`, `offre_competence`, `candidatures` (schema only) successfully
- [ ] Main page renders and pulls 3–4 real offers from the database, each showing its image (or a placeholder)
- [ ] Admin can log in (`admin/login.php`) and reach `admin/dashboard.php`
- [ ] Admin can fully Create, Read, Update, Delete: Offres (with image upload/replace/delete) and Compétences
- [ ] Admin can view a read-only list of Candidats
- [ ] All forms validate input and show errors, including image type/size errors
- [ ] Deleting a Compétence or a Recruteur that is still referenced elsewhere does not crash the app — it shows a clear message instead
- [ ] Every `admin/*` page is unreachable without an admin session (redirects to `admin/login.php`)
