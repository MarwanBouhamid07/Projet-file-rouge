-- Sprint 1 - Database Schema
-- Recruitment Platform with Smart Matching

CREATE DATABASE IF NOT EXISTS `recrutement_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `recrutement_db`;

-- Drop tables in reverse order of dependencies if re-running
DROP TABLE IF EXISTS `candidatures`;
DROP TABLE IF EXISTS `offre_competence`;
DROP TABLE IF EXISTS `candidat_competence`;
DROP TABLE IF EXISTS `competences`;
DROP TABLE IF EXISTS `offres`;
DROP TABLE IF EXISTS `users`;

-- 1. Table: users
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `last_name` VARCHAR(50) NOT NULL,
    `first_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('candidat', 'recruteur', 'admin') NOT NULL DEFAULT 'candidat',
    `domaine` VARCHAR(100) NULL,
    `years_experience` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Table: offres
CREATE TABLE `offres` (
    `id_offre` INT AUTO_INCREMENT PRIMARY KEY,
    `titre` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `domaine` VARCHAR(100) NOT NULL,
    `years_required` INT NOT NULL DEFAULT 0,
    `image` VARCHAR(255) NULL,
    `id_recruteur` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_offres_recruteur` FOREIGN KEY (`id_recruteur`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Table: competences
CREATE TABLE `competences` (
    `id_competence` INT AUTO_INCREMENT PRIMARY KEY,
    `nom` VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Junction table: candidat_competence (schema only)
CREATE TABLE `candidat_competence` (
    `id_user` INT NOT NULL,
    `id_competence` INT NOT NULL,
    PRIMARY KEY (`id_user`, `id_competence`),
    CONSTRAINT `fk_candidat_comp_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_candidat_comp_competence` FOREIGN KEY (`id_competence`) REFERENCES `competences` (`id_competence`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Junction table: offre_competence (schema only)
CREATE TABLE `offre_competence` (
    `id_offre` INT NOT NULL,
    `id_competence` INT NOT NULL,
    PRIMARY KEY (`id_offre`, `id_competence`),
    CONSTRAINT `fk_offre_comp_offre` FOREIGN KEY (`id_offre`) REFERENCES `offres` (`id_offre`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_offre_comp_competence` FOREIGN KEY (`id_competence`) REFERENCES `competences` (`id_competence`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Table: candidatures (schema only)
CREATE TABLE `candidatures` (
    `id_candidature` INT AUTO_INCREMENT PRIMARY KEY,
    `id_user` INT NOT NULL,
    `id_offre` INT NOT NULL,
    `match_score` DECIMAL(5,2) NULL,
    `statut` ENUM('en attente', 'accepté', 'refusé') NOT NULL DEFAULT 'en attente',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_candidatures_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_candidatures_offre` FOREIGN KEY (`id_offre`) REFERENCES `offres` (`id_offre`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
