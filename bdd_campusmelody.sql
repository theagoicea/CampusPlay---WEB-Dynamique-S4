-- BDD Campus Melody
DROP DATABASE IF EXISTS campusmelody;
CREATE DATABASE campusmelody;
USE campusmelody;

-- 1. Table : UTILISATEUR
CREATE TABLE utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(150) UNIQUE,
    mot_de_passe VARCHAR(255),
    photo_profil VARCHAR(255) DEFAULT NULL,
    role VARCHAR(50) DEFAULT 'Visiteur', 
    est_membre_asso BOOLEAN DEFAULT FALSE,
    statut_adhesion VARCHAR(50) DEFAULT 'Non membre',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Table : RESSOURCE
CREATE TABLE ressource (
    id_resource INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    description TEXT,
    type_ressource VARCHAR(50), -- 'Studio', 'Instrument', 'Matériel', 'Salle'
    quantite_totale INT DEFAULT 1,
    statut_actuel VARCHAR(50) DEFAULT 'Disponible'
) ENGINE=InnoDB;

-- 3. Table : EVENEMENT 
CREATE TABLE evenement (
    id_evenement INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150),
    description TEXT,
    image_url VARCHAR(255),
    date_debut DATETIME,
    date_fin DATETIME,
    lieu VARCHAR(100),
    capacite_max INT,
    type_evenement VARCHAR(50), 
    est_reserve_membres BOOLEAN DEFAULT FALSE,
    besoin_validation_inscription BOOLEAN DEFAULT FALSE,
    statut_validation VARCHAR(50) DEFAULT 'En attente',
    id_organisateur INT,
    id_validateur INT,
    date_decision DATETIME,
    FOREIGN KEY (id_organisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Table : RESERVATION 
CREATE TABLE reservation (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    date_debut DATETIME,
    date_fin DATETIME,
    statut VARCHAR(50) DEFAULT 'En attente',
    id_utilisateur INT,
    id_ressource INT,
    id_validateur INT,
    date_decision DATETIME,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_ressource) REFERENCES ressource(id_resource) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Table : NOTIFICATION
CREATE TABLE notification (
    id_notification INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100),
    message TEXT,
    type_notification VARCHAR(50), 
    statut_lecture BOOLEAN DEFAULT FALSE,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_destinataire INT,
    FOREIGN KEY (id_destinataire) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Table : INSCRIPTION
CREATE TABLE inscription (
    id_utilisateur INT,
    id_evenement INT,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut_inscription VARCHAR(50) DEFAULT 'En attente',
    id_validateur INT,
    date_decision DATETIME,
    PRIMARY KEY (id_utilisateur, id_evenement),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_evenement) REFERENCES evenement(id_evenement) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Table : FORUM
CREATE TABLE forum (
    id_forum INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150),
    id_createur INT,
    categorie VARCHAR(50) DEFAULT 'Général',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    est_ferme BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_createur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. Table : MESSAGE_FORUM
CREATE TABLE message_forum (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT,
    id_auteur INT,
    id_forum INT,
    date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    signale BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_auteur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_forum) REFERENCES forum(id_forum) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================================================
-- INSERTION DES DONNÉES
-- ==========================================================
-- --------------------------------------------------------
-- 1. UTILISATEURS (Mot de passe pour tous : password)
-- --------------------------------------------------------
INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `est_membre_asso`, `statut_adhesion`) VALUES
(1, 'Admin', 'Super', 'admin@campus.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 1, 'Validé'),
(2, 'Organisateur', 'Test', 'orga@campus.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Organisateur', 1, 'Validé'),
(3, 'Membre', 'Classique', 'membre@campus.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Membre', 1, 'Validé'),
(4, 'Visiteur', 'Curieux', 'visiteur@campus.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Visiteur', 0, 'Non membre'),
(5, 'Attente', 'Adhesion', 'attente@campus.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Visiteur', 0, 'En attente'),
(6, 'Refusé', 'Triste', 'refuse@campus.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Visiteur', 0, 'Refusé');

-- --------------------------------------------------------
-- 2. RESSOURCES (Salles & Matériel)
-- --------------------------------------------------------
INSERT INTO `ressource` (`id_resource`, `nom`, `type_ressource`, `statut_actuel`) VALUES
(1, 'Studio A (Musique Actuelle)', 'Studio', 'Disponible'),
(2, 'Studio B (Enregistrement)', 'Studio', 'Disponible'),
(3, 'Guitare Fender Stratocaster', 'Instrument', 'Disponible'),
(4, 'Micro Shure SM58', 'Matériel', 'Disponible');

-- --------------------------------------------------------
-- 3. EVENEMENTS (Simulés autour du 31 Mai 2026)
-- --------------------------------------------------------
INSERT INTO `evenement` (`id_evenement`, `titre`, `description`, `date_debut`, `date_fin`, `lieu`, `capacite_max`, `type_evenement`, `est_reserve_membres`, `besoin_validation_inscription`, `statut_validation`, `id_organisateur`) VALUES
(1, 'Concert de Juin (Normal)', 'Événement standard dans le futur.', '2026-06-15 20:00:00', '2026-06-15 23:00:00', 'Amphi Central', 100, 'CONCERT', 0, 0, 'Validé', 2),
(2, 'Masterclass Batterie (COMPLET)', 'Cet événement est plein pour tester le bouton "COMPLET".', '2026-06-20 14:00:00', '2026-06-20 16:00:00', 'Studio A', 2, 'WORKSHOP', 0, 0, 'Validé', 2),
(3, 'Jam Session (Membres Uniquement)', 'Test de la restriction d\'accès.', '2026-06-25 18:00:00', '2026-06-25 21:00:00', 'Studio B', 20, 'JAM SESSION', 1, 0, 'Validé', 3),
(4, 'Audition (Validation Requise)', 'L\'organisateur doit valider les inscrits.', '2026-07-05 10:00:00', '2026-07-05 12:00:00', 'Studio A', 10, 'AUDITION', 0, 1, 'Validé', 2),
(5, 'Projet de Festival (En attente Admin)', 'L\'admin doit valider cet événement.', '2026-07-15 14:00:00', '2026-07-16 23:00:00', 'Parc Campus', 500, 'CONCERT', 0, 0, 'En attente', 3),
(6, 'Concert du Printemps (Passé)', 'Événement terminé pour tester l\'historique profil.', '2026-05-10 20:00:00', '2026-05-10 23:00:00', 'Amphi Central', 50, 'CONCERT', 0, 0, 'Validé', 2),
(7, 'Répétition Générale (DEMAIN !)', 'Pour tester l\'envoi de notifications de rappel (J-1).', '2026-06-01 18:00:00', '2026-06-01 20:00:00', 'Studio A', 30, 'REPETITION', 0, 0, 'Validé', 2);

-- --------------------------------------------------------
-- 4. INSCRIPTIONS
-- --------------------------------------------------------
INSERT INTO `inscription` (`id_utilisateur`, `id_evenement`, `statut_inscription`) VALUES
(3, 1, 'Confirmé'), -- Membre inscrit au concert normal
(4, 2, 'Confirmé'), -- Remplissage de l'événement complet (1/2)
(5, 2, 'Confirmé'), -- Remplissage de l'événement complet (2/2) -> Capacité atteinte
(4, 4, 'En attente'), -- Visiteur attend la validation de l'Orga (User 2)
(3, 6, 'Confirmé'), -- Membre était à l'événement passé (pour son historique)
(3, 7, 'Confirmé'); -- Membre inscrit à l'événement de demain (pour la notif J-1)

-- --------------------------------------------------------
-- 5. RÉSERVATIONS
-- --------------------------------------------------------
INSERT INTO `reservation` (`id_reservation`, `date_debut`, `date_fin`, `id_utilisateur`, `id_ressource`, `statut`) VALUES
(1, '2026-06-05 14:00:00', '2026-06-05 16:00:00', 3, 1, 'En attente'), -- Test : L'admin doit approuver
(2, '2026-06-01 10:00:00', '2026-06-01 12:00:00', 3, 4, 'Approuvée'), -- Test : Rappel "demain" pour matériel
(3, '2026-05-20 09:00:00', '2026-05-20 11:00:00', 3, 2, 'Approuvée'); -- Test : Historique passé

-- --------------------------------------------------------
-- 6. FORUM & MESSAGES
-- --------------------------------------------------------
INSERT INTO `forum` (`id_forum`, `titre`, `id_createur`, `categorie`, `est_ferme`) VALUES
(1, 'Bienvenue sur le nouveau forum !', 1, 'general', 0),
(2, 'Conseils pour débuter Ableton', 3, 'tech', 0),
(3, 'Cherche batteur pour groupe de Rock', 4, 'collab', 0),
(4, 'Sujet fermé (Modération)', 1, 'general', 1);

INSERT INTO `message_forum` (`id_message`, `contenu`, `id_auteur`, `id_forum`, `signale`) VALUES
(1, 'N\'hésitez pas à vous présenter ici !', 1, 1, 0),
(2, 'Salut, moi c\'est le membre test.', 3, 1, 0),
(3, 'Message posté par un visiteur non connecté (Anonyme).', NULL, 1, 0),
(4, 'Regarde des tutos sur YouTube c\'est le mieux.', 2, 2, 0),
(5, 'Ce message est insultant et a été signalé.', 6, 3, 1), -- TEST : L'admin verra ce signalement
(6, 'Ce sujet a été verrouillé suite à des débordements.', 1, 4, 0);

-- --------------------------------------------------------
-- 7. NOTIFICATIONS (Simulations)
-- --------------------------------------------------------
INSERT INTO `notification` (`titre`, `message`, `type_notification`, `id_destinataire`) VALUES
('Bienvenue !', 'Votre compte a bien été créé.', 'forum', 3),
('Demande Adhésion', 'L\'utilisateur "Attente Adhesion" souhaite rejoindre l\'asso.', 'adhesion-association', 1),
('Nouvel Événement', 'Le projet "Festival test" attend votre validation.', 'creation-evenement', 1);