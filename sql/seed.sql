-- Sprint 1 - Seed Data
-- Recruitment Platform with Smart Matching

USE `recrutement_db`;

-- 1. Insert Users (Admin, Recruteurs, Candidats)
-- Password for all seed users is 'admin123' for admin, and 'password123' for others.
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `domaine`, `years_experience`) VALUES
(1, 'Admin', 'Système', 'admin@recrutement.com', '$2y$12$.9QP9jgpzcb4QyxnPQs/wu2nEIDt4cwXSmDi0SfqKls2Ok5DsdwGS', 'admin', 'Management & IT', 8),
(2, 'Sarah', 'Mansouri', 'sarah.recruteur@techcorp.ma', '$2y$12$mYl0uYndr5GFzXP3xyEw/u4NnlsI8Pj13FstzCks2DsnuvxvbpMIa', 'recruteur', 'Ressources Humaines IT', 5),
(3, 'Karim', 'Alami', 'karim.rh@innovate.ma', '$2y$12$mYl0uYndr5GFzXP3xyEw/u4NnlsI8Pj13FstzCks2DsnuvxvbpMIa', 'recruteur', 'Tech & Innovation', 6),
(4, 'Youssef', 'Benali', 'youssef.benali@gmail.com', '$2y$12$mYl0uYndr5GFzXP3xyEw/u4NnlsI8Pj13FstzCks2DsnuvxvbpMIa', 'candidat', 'Développement Web', 3),
(5, 'Fatima-Zahra', 'Tahiri', 'fatima.tahiri@gmail.com', '$2y$12$mYl0uYndr5GFzXP3xyEw/u4NnlsI8Pj13FstzCks2DsnuvxvbpMIa', 'candidat', 'Data Science & IA', 4),
(6, 'Mehdi', 'Chraibi', 'mehdi.chraibi@gmail.com', '$2y$12$mYl0uYndr5GFzXP3xyEw/u4NnlsI8Pj13FstzCks2DsnuvxvbpMIa', 'candidat', 'Cloud & DevOps', 2),
(7, 'Salma', 'Idrissi', 'salma.idrissi@gmail.com', '$2y$12$mYl0uYndr5GFzXP3xyEw/u4NnlsI8Pj13FstzCks2DsnuvxvbpMIa', 'candidat', 'Design UI/UX & Web', 1);

-- 2. Insert Competences
INSERT INTO `competences` (`id_competence`, `nom`) VALUES
(1, 'PHP / Laravel'),
(2, 'MySQL / Base de données'),
(3, 'JavaScript / TypeScript'),
(4, 'React / Vue.js'),
(5, 'Docker / Kubernetes'),
(6, 'Git / CI-CD'),
(7, 'Python / Data Analysis'),
(8, 'HTML5 / CSS3 Modern'),
(9, 'API REST / GraphQL'),
(10, 'Linux / Administration Système');

-- 3. Insert Offres
INSERT INTO `offres` (`id_offre`, `titre`, `description`, `domaine`, `years_required`, `image`, `id_recruteur`) VALUES
(1, 'Développeur Fullstack PHP & JavaScript', 'Nous recherchons un développeur Fullstack talentueux pour concevoir et maintenir des applications web modernes et scalables.', 'Développement Web', 3, 'offre_fullstack.jpg', 2),
(2, 'Ingénieur Cloud & DevOps Senior', 'Rejoignez notre équipe d\'infrastructure pour orchestrer nos déploiements automatisés et conteneurs Docker/K8s.', 'Cloud & DevOps', 5, 'offre_devops.jpg', 2),
(3, 'Développeur Frontend React / UI', 'Création d\'interfaces interactives, réactives et accessibles au sein d\'une équipe agile et dynamique.', 'Développement Web', 2, 'offre_frontend.jpg', 3),
(4, 'Data Analyst & BI Specialist', 'Analyse approfondie de données métiers, conception de tableaux de bord décisionnels et modélisation SQL.', 'Data & Intelligence Décisionnelle', 4, 'offre_data.jpg', 3);
