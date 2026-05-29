-- ============================================================
-- BASE DE DONNÉES CAMPUS MELODY - VERSION COMPLÈTE DE TEST
-- ============================================================

DROP DATABASE IF EXISTS campusmelody;
CREATE DATABASE campusmelody;
USE campusmelody;

-- [STRUCTURES DES TABLES - Identiques à précédemment]
CREATE TABLE utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    photo_profil VARCHAR(255) DEFAULT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Visiteur', 
    est_membre_asso BOOLEAN DEFAULT FALSE,
    statut_adhesion VARCHAR(50) DEFAULT 'Non membre', 
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ressource (
    id_resource INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    type_ressource VARCHAR(50) NOT NULL,
    quantite_totale INT NOT NULL DEFAULT 1,
    statut_actuel VARCHAR(50) NOT NULL DEFAULT 'Disponible'
);

CREATE TABLE evenement (
    id_evenement INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    lieu VARCHAR(100) NOT NULL,
    capacite_max INT NOT NULL,
    type_evenement VARCHAR(50) NOT NULL, 
    est_reserve_membres BOOLEAN DEFAULT FALSE,
    besoin_validation_inscription BOOLEAN DEFAULT FALSE,
    statut_validation VARCHAR(50) NOT NULL DEFAULT 'En attente',
    id_organisateur INT NOT NULL,
    id_validateur INT NULL,
    date_decision DATETIME NULL,
    FOREIGN KEY (id_organisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE reservation (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    statut VARCHAR(50) NOT NULL DEFAULT 'En attente',
    id_utilisateur INT NOT NULL,
    id_ressource INT NOT NULL,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_ressource) REFERENCES ressource(id_resource) ON DELETE CASCADE
);

CREATE TABLE notification (
    id_notification INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    type_notification VARCHAR(50) NOT NULL, 
    statut_lecture BOOLEAN DEFAULT FALSE,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_destinataire INT NOT NULL,
    FOREIGN KEY (id_destinataire) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE inscription (
    id_utilisateur INT NOT NULL,
    id_evenement INT NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut_inscription VARCHAR(50) NOT NULL DEFAULT 'En attente',
    PRIMARY KEY (id_utilisateur, id_evenement),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_evenement) REFERENCES evenement(id_evenement) ON DELETE CASCADE
);

CREATE TABLE forum (
    id_forum INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    id_createur INT NOT NULL,
    categorie VARCHAR(50) DEFAULT 'Général',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_createur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE message_forum (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    id_auteur INT NOT NULL,
    id_forum INT NOT NULL,
    date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_auteur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_forum) REFERENCES forum(id_forum) ON DELETE CASCADE
);

-- ============================================================
-- INSERTIONS POUR TESTER TOUS LES CAS DE FIGURE
-- ============================================================

-- 1. LES UTILISATEURS (Mdp: root)
INSERT INTO utilisateur (id_utilisateur, nom, prenom, email, mot_de_passe, role, est_membre_asso, statut_adhesion) VALUES 
(1, 'Admin', 'Admin', 'admin@campus.fr', 'root', 'Admin', TRUE, 'Validé'), -- L'Admin
(2, 'Dubois', 'Sarah', 'sarah@campus.fr', 'root', 'Organisateur', TRUE, 'Validé'), -- Une organisatrice
(3, 'Lemoine', 'Marc', 'marc@campus.fr', 'root', 'Membre', TRUE, 'Validé'), -- Un membre actif
(4, 'Robert', 'Lucie', 'lucie@campus.fr', 'root', 'Visiteur', FALSE, 'Non membre'), -- Un simple visiteur
(5, 'Petit', 'Thomas', 'thomas@campus.fr', 'root', 'Membre', TRUE, 'En attente'); -- CAS : Adhésion à valider par l'admin

-- 2. LES RESSOURCES
INSERT INTO ressource (id_resource, nom, type_ressource, quantite_totale) VALUES 
(1, 'Studio A', 'Studio', 1),
(2, 'Studio B', 'Studio', 1),
(3, 'Guitare Fender', 'Instrument', 1),
(4, 'Micro SM58', 'Matériel', 5);

-- 3. LES ÉVÉNEMENTS (Variété de statuts et de dates)
INSERT INTO evenement (id_evenement, titre, description, date_debut, date_fin, lieu, capacite_max, type_evenement, statut_validation, id_organisateur) VALUES 
(1, 'Rock Night', 'Concert de rock', '2024-06-20 20:00:00', '2024-06-20 23:00:00', 'Amphi A', 100, 'CONCERT', 'Validé', 2),
(2, 'Jazz Session', 'Jam impro', '2024-06-25 19:00:00', '2024-06-25 21:00:00', 'Studio A', 2, 'JAM SESSION', 'Validé', 2), -- CAS : Capacité très faible (2 pers)
(3, 'Workshop MAO', 'Apprendre Ableton', '2024-02-10 14:00:00', '2024-02-10 16:00:00', 'Studio MAO', 15, 'WORKSHOP', 'Validé', 1), -- CAS : Événement PASSÉ
(4, 'Futur Metal', 'Gros projet hiver', '2024-12-15 20:00:00', '2024-12-15 23:00:00', 'Gymnase', 500, 'CONCERT', 'En attente', 3); -- CAS : Projet à valider par l'admin

-- 4. LES INSCRIPTIONS (Tester les limites)
INSERT INTO inscription (id_utilisateur, id_evenement, statut_inscription) VALUES 
(1, 2, 'Confirmé'),
(3, 2, 'Confirmé'), -- CAS : L'événement ID 2 (Jazz Session) est maintenant COMPLET (2/2)
(4, 1, 'En attente'); -- CAS : Inscription visiteur à valider

-- 5. LES RÉSERVATIONS (Tester les conflits)
INSERT INTO reservation (date_debut, date_fin, statut, id_utilisateur, id_ressource) VALUES 
('2024-06-15 14:00:00', '2024-06-15 16:00:00', 'Approuvée', 3, 1), -- Marc réserve Studio A
('2024-06-15 15:00:00', '2024-06-15 17:00:00', 'En attente', 2, 1), -- CAS : Sarah demande le Studio A alors qu'il est déjà pris de 15h à 16h (CONFLIT)
('2024-06-15 10:00:00', '2024-06-15 12:00:00', 'Approuvée', 3, 4), -- Marc prend 1 Micro SM58
('2024-06-15 11:00:00', '2024-06-15 13:00:00', 'Approuvée', 2, 4); -- CAS : Sarah prend un 2ème Micro (Ok car il y en a 5)

-- 6. LE FORUM
INSERT INTO forum (id_forum, titre, id_createur, categorie) VALUES 
(1, 'Cherche Batteur', 3, 'Collaborations'),
(2, 'Problème de carte son', 4, 'Technique');

INSERT INTO message_forum (contenu, id_auteur, id_forum) VALUES 
('Je suis dispo !', 2, 1),
('Quel est le modèle ?', 1, 2);

-- 7. NOTIFICATIONS
INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES 
('Alerte Admin', 'Un nouveau projet d\'événement attend votre validation.', 'création-événement', 1),
('Inscription', 'Marc Lemoine s\'est inscrit à votre concert.', 'inscription-événement', 2);
