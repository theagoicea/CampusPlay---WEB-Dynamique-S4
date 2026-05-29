-- BDD campus melody

DROP DATABASE IF EXISTS campusmelody;
CREATE DATABASE campusmelody;
USE campusmelody;

-- Table : UTILISATEUR
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
);

-- Table : RESSOURCE (Salles et Matériels)
CREATE TABLE ressource (
    id_resource INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100),
    description TEXT,
    type_ressource VARCHAR(50), -- 'Studio', 'Instrument', 'Matériel', 'Salle'
    quantite_totale INT DEFAULT 1,
    statut_actuel VARCHAR(50) DEFAULT 'Disponible'
);

-- Table : EVENEMENT 
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
);

-- Table : RESERVATION 
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
);

-- Table : NOTIFICATION
CREATE TABLE notification (
    id_notification INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100),
    message TEXT,
    type_notification VARCHAR(50), 
    statut_lecture BOOLEAN DEFAULT FALSE,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_destinataire INT,
    FOREIGN KEY (id_destinataire) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

-- Table : INSCRIPTION
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
);

-- Table : FORUM
CREATE TABLE forum (
    id_forum INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150),
    id_createur INT,
    categorie VARCHAR(50) DEFAULT 'Général',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    est_ferme BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_createur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

-- Table : MESSAGE_FORUM
CREATE TABLE message_forum (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT,
    id_auteur INT,
    id_forum INT,
    date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    signale BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_auteur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_forum) REFERENCES forum(id_forum) ON DELETE CASCADE
);



-- A. UTILISATEURS
INSERT INTO utilisateur (id_utilisateur, nom, prenom, email, mot_de_passe, role, est_membre_asso, statut_adhesion) VALUES 
(1, 'Martin', 'Julien', 'admin@campus.fr', 'root', 'Admin', TRUE, 'Validé'),
(2, 'Dubois', 'Sarah', 'sarah@campus.fr', 'root', 'Organisateur', TRUE, 'Validé'),
(3, 'Lemoine', 'Marc', 'marc@campus.fr', 'root', 'Membre', TRUE, 'Validé'),
(4, 'Robert', 'Lucie', 'lucie@campus.fr', 'root', 'Visiteur', FALSE, 'Non membre'),
(5, 'Petit', 'Thomas', 'thomas@campus.fr', 'root', 'Membre', TRUE, 'En attente'),
(6, 'Moreau', 'Emma', 'emma@campus.fr', 'root', 'Organisateur', TRUE, 'Validé');

-- B. RESSOURCES
INSERT INTO ressource (id_resource, nom, description, type_ressource, quantite_totale) VALUES 
(1, 'Studio A - Piano', 'Équipé Steinway & Sons', 'Studio', 1),
(2, 'Studio B - Rock', 'Équipé batterie DW et amplis Marshall', 'Studio', 1),
(3, 'Studio C - Voix', 'Isolation acoustique pro pour chant', 'Studio', 1),
(4, 'Studio M.A.O', 'Stations Ableton et enceintes Focal', 'Studio', 1),
(5, 'Guitare Fender Strat', 'Série American Professional II', 'Instrument', 1),
(6, 'Guitare Gibson Les Paul', 'Standard 60s Bourbon Burst', 'Instrument', 1),
(7, 'Basse Fender Jazz Bass', 'Basse électrique 4 cordes', 'Instrument', 1),
(8, 'Batterie Roland', 'Batterie électronique haut de gamme', 'Instrument', 1),
(9, 'Synthétiseur Korg', 'Polyphonic Analog Synthesizer', 'Instrument', 1),
(10, 'Clavier Maître', '88 touches lestées', 'Instrument', 2),
(11, 'Micro Shure SM58', 'Micro dynamique polyvalent', 'Matériel', 10),
(12, 'Micro Rode NT1-A', 'Pack enregistrement complet', 'Matériel', 4),
(13, 'Carte Son Focusrite', 'Interface audio USB', 'Matériel', 6),
(14, 'Casque studio DT770', 'Casque de studio fermé 80 Ohm', 'Matériel', 8),
(15, 'Enceinte Bose S1', 'Système de sonorisation portable', 'Matériel', 2),
(16, 'Contrôleur DJ', 'Pioneer DDJ-FLX4', 'Matériel', 1),
(17, 'Câble XLR 10m', 'Câble blindé Neutrik', 'Matériel', 15),
(18, 'Pied de Micro', 'Télescopique avec perche', 'Matériel', 10),
(19, 'Ampli Guitare', 'Fender Champion 100', 'Matériel', 2),
(20, 'Ampli Basse', 'Fender Rumble 100', 'Matériel', 1);

-- C. ÉVÉNEMENTS
INSERT INTO evenement (id_evenement, titre, description, date_debut, date_fin, lieu, capacite_max, type_evenement, statut_validation, id_organisateur, est_reserve_membres) VALUES 
-- 2024
(1, 'Nouvel An Musical', 'Jam session de rentrée.', '2024-01-10 18:00:00', '2024-01-10 22:00:00', 'Studio A', 30, 'JAM SESSION', 'Validé', 2, FALSE),
(2, 'Spring Rock Fest', 'Festival de rock extérieur.', '2024-04-15 14:00:00', '2024-04-15 23:00:00', 'Parc Campus', 500, 'CONCERT', 'Validé', 4, FALSE),
(3, 'Rock au Campus', 'Le grand concert de fin d\'année !', '2024-06-18 20:00:00', '2024-06-18 23:30:00', 'Amphi Central', 200, 'CONCERT', 'Validé', 2, FALSE),
(4, 'Atelier Mixage Été', 'Initiation à Ableton.', '2024-07-22 14:00:00', '2024-07-22 17:00:00', 'Studio M.A.O', 12, 'WORKSHOP', 'Validé', 4, TRUE),
(5, 'Halloween DJ Mix', 'Ambiance sombre.', '2024-10-31 21:00:00', '2024-11-01 02:00:00', 'Foyer', 100, 'DJ NIGHT', 'Validé', 2, FALSE),
(6, 'Winter Showcase', 'Démos M.A.O.', '2024-12-15 14:00:00', '2024-12-15 18:00:00', 'Studio M.A.O', 30, 'CONCERT', 'En attente', 4, TRUE),
-- 2025
(7, 'Acoustic Valentine', 'Soirée guitare/voix.', '2025-02-14 19:00:00', '2025-02-14 22:00:00', 'Cafétéria', 60, 'CONCERT', 'Validé', 6, FALSE),
(8, 'Jazz Impro Spring', 'Jazz Session.', '2025-03-20 19:00:00', '2025-03-20 22:00:00', 'Foyer Etudiant', 80, 'JAM SESSION', 'Validé', 2, FALSE),
(9, 'Battle de Rap', 'Flow et texte.', '2025-05-12 20:00:00', '2025-05-12 23:00:00', 'Amphi B', 120, 'OPEN MIC', 'Validé', 4, FALSE),
(10, 'Summer Electro', 'DJ Sets.', '2025-07-10 22:00:00', '2025-07-11 04:00:00', 'Gymnase', 400, 'DJ NIGHT', 'Validé', 6, FALSE),
(11, 'Masterclass Batterie', 'Cours intensif.', '2025-09-05 10:00:00', '2025-09-05 17:00:00', 'Studio B', 10, 'WORKSHOP', 'Validé', 2, TRUE),
(12, 'Audition Metal', 'Recherche chanteur.', '2025-11-15 18:00:00', '2025-11-15 20:00:00', 'Studio B', 5, 'AUDITION', 'En attente', 3, TRUE),
-- 2026
(13, 'Expo Musique', 'Conférences.', '2026-03-10 10:00:00', '2026-03-10 18:00:00', 'Amphi Audio', 150, 'WORKSHOP', 'Validé', 4, FALSE),
(14, 'Open Air Jam', 'Session pelouse.', '2026-06-21 14:00:00', '2026-06-21 20:00:00', 'Parc Campus', 300, 'JAM SESSION', 'Validé', 2, FALSE),
(15, 'Grand Gala', 'Orchestre campus.', '2026-05-30 20:30:00', '2026-05-30 23:00:00', 'Amphi Central', 400, 'CONCERT', 'Validé', 6, FALSE),
(16, 'Stage Production', 'Produire un titre.', '2026-08-10 09:00:00', '2026-08-14 17:00:00', 'Studio M.A.O', 8, 'WORKSHOP', 'En attente', 4, TRUE),
(17, 'Metal Night 2026', 'Gros son.', '2026-11-20 20:00:00', '2026-11-20 23:30:00', 'Gymnase', 300, 'CONCERT', 'Validé', 3, FALSE),
(18, 'Noël Gospel', 'Chorale.', '2026-12-20 18:00:00', '2026-12-20 20:00:00', 'Hall', 100, 'OPEN MIC', 'Validé', 6, FALSE),
-- 2027
(19, 'Rentrée 2027', 'Accueil.', '2027-01-15 19:00:00', '2027-01-15 22:00:00', 'Studio A', 40, 'JAM SESSION', 'Validé', 2, FALSE),
(20, 'Tech Music Summit', 'Innovation.', '2027-04-05 09:00:00', '2027-04-07 18:00:00', 'Amphi Central', 500, 'WORKSHOP', 'Validé', 4, FALSE),
(21, 'Concert 10 ans', 'Anniversaire.', '2027-06-12 19:00:00', '2027-06-13 01:00:00', 'Amphi Central', 600, 'CONCERT', 'Validé', 1, FALSE),
(22, 'Beatmaker Battle', 'Meilleure boucle.', '2027-09-18 20:00:00', '2027-09-18 23:00:00', 'Studio M.A.O', 20, 'OPEN MIC', 'Validé', 4, TRUE),
(23, 'Electro Frost', 'Techno d\'hiver.', '2027-11-12 22:00:00', '2027-11-13 04:00:00', 'Gymnase', 350, 'DJ NIGHT', 'En attente', 6, FALSE),
(24, 'Auditions Finales', 'Sélection album.', '2027-12-05 14:00:00', '2027-12-05 19:00:00', 'Studio C', 10, 'AUDITION', 'Validé', 3, TRUE);

-- D. INSCRIPTIONS (Lien Utilisateurs -> Evénements)
INSERT INTO inscription (id_utilisateur, id_evenement, statut_inscription) VALUES 
(3, 1, 'Confirmé'), (5, 1, 'Confirmé'), (6, 1, 'Confirmé'), 
(3, 2, 'Confirmé'), (5, 2, 'Confirmé'), 
(1, 3, 'Confirmé'), (3, 3, 'Confirmé'), (4, 3, 'Confirmé');

-- E. RÉSERVATIONS (Simulation de planning et conflits)
INSERT INTO reservation (date_debut, date_fin, statut, id_utilisateur, id_ressource) VALUES 
('2024-06-20 14:00:00', '2024-06-20 16:00:00', 'Approuvée', 3, 1),
('2024-06-20 10:00:00', '2024-06-20 12:00:00', 'Approuvée', 5, 2),
('2024-06-21 14:00:00', '2024-06-21 15:00:00', 'En attente', 3, 5),
('2024-06-21 14:00:00', '2024-06-21 16:00:00', 'En attente', 6, 1);

-- F. FORUM & NOTIFICATIONS
INSERT INTO forum (titre, id_createur, categorie) VALUES 
('Cherche chanteur Rock', 3, 'Collaborations'),
('Conseils Ableton', 6, 'Technique'),
('Médiatrice perdue au Studio B', 5, 'Général'),
('Covoiturage Concert Lucie', 4, 'Événements');

INSERT INTO message_forum (contenu, id_auteur, id_forum) VALUES 
('Salut Marc, je suis chaud !', 5, 1),
('Top ! On se voit quand ?', 3, 1),
('Utilise l\'EQ Eight.', 1, 2);

INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES 
('Bienvenue !', 'Bienvenue sur Campus Melody.', 'forum', 1),
('Nouvel événement', 'Rock au Campus a été ajouté.', 'rappel-événement', 3),
('Demande adhésion', 'Thomas Petit veut devenir membre.', 'adhésion-association', 1);
