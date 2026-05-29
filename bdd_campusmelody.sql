
-- BASE DE DONNÉES CAMPUS MELODY

DROP DATABASE IF EXISTS campusmelody;
CREATE DATABASE campusmelody;
USE campusmelody;

-- STRUCTURE DES TABLES
CREATE TABLE utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100), prenom VARCHAR(100), email VARCHAR(150) UNIQUE, mot_de_passe VARCHAR(255),
    photo_profil VARCHAR(255), role VARCHAR(50) DEFAULT 'Visiteur', 
    est_membre_asso BOOLEAN DEFAULT FALSE, statut_adhesion VARCHAR(50) DEFAULT 'Non membre',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ressource (
    id_resource INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100), description TEXT, type_ressource VARCHAR(50), -- 'Studio', 'Instrument', 'Matériel'
    quantite_totale INT DEFAULT 1, statut_actuel VARCHAR(50) DEFAULT 'Disponible'
);

CREATE TABLE evenement (
    id_evenement INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150), description TEXT, image_url VARCHAR(255),
    date_debut DATETIME, date_fin DATETIME, lieu VARCHAR(100),
    capacite_max INT, type_evenement VARCHAR(50), 
    est_reserve_membres BOOLEAN DEFAULT FALSE, besoin_validation_inscription BOOLEAN DEFAULT FALSE,
    statut_validation VARCHAR(50) DEFAULT 'En attente',
    id_organisateur INT, id_validateur INT, date_decision DATETIME,
    FOREIGN KEY (id_organisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE reservation (
    id_reservation INT AUTO_INCREMENT PRIMARY KEY,
    date_debut DATETIME, date_fin DATETIME, statut VARCHAR(50) DEFAULT 'En attente',
    id_utilisateur INT, id_ressource INT,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_ressource) REFERENCES ressource(id_resource) ON DELETE CASCADE
);

CREATE TABLE notification (
    id_notification INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100), message TEXT, type_notification VARCHAR(50), 
    statut_lecture BOOLEAN DEFAULT FALSE, date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_destinataire INT, FOREIGN KEY (id_destinataire) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE inscription (
    id_utilisateur INT, id_evenement INT, date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut_inscription VARCHAR(50) DEFAULT 'En attente',
    PRIMARY KEY (id_utilisateur, id_evenement),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_evenement) REFERENCES evenement(id_evenement) ON DELETE CASCADE
);

CREATE TABLE forum (
    id_forum INT AUTO_INCREMENT PRIMARY KEY, titre VARCHAR(150), id_createur INT,
    categorie VARCHAR(50) DEFAULT 'Général', date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_createur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE message_forum (
    id_message INT AUTO_INCREMENT PRIMARY KEY, contenu TEXT, id_auteur INT, id_forum INT,
    date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP, signale BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_auteur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_forum) REFERENCES forum(id_forum) ON DELETE CASCADE
);

-- Création utilisateurs
INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, est_membre_asso, statut_adhesion) VALUES 
('Martin', 'Julien', 'admin@campus.fr', 'root', 'Admin', TRUE, 'Validé'),
('Dubois', 'Sarah', 'sarah@campus.fr', 'root', 'Organisateur', TRUE, 'Validé'),
('Lemoine', 'Marc', 'marc@campus.fr', 'root', 'Membre', TRUE, 'Validé'),
('Moreau', 'Emma', 'emma@campus.fr', 'root', 'Organisateur', TRUE, 'Validé'),
('Lucas', 'Fontaine', 'lucas@campus.fr', 'root', 'Membre', TRUE, 'Validé'),
('Sophie', 'Vasseur', 'sophie@campus.fr', 'root', 'Membre', TRUE, 'Validé'),
('Thomas', 'Petit', 'thomas@campus.fr', 'root', 'Membre', TRUE, 'En attente'), -- À VALIDER
('Lucie', 'Robert', 'lucie@campus.fr', 'root', 'Visiteur', FALSE, 'Non membre');

-- Création ressources
INSERT INTO ressource (nom, description, type_ressource, quantite_totale) VALUES 
('Studio A - Piano', 'Équipé Steinway & Sons', 'Studio', 1),
('Studio B - Rock', 'Équipé batterie DW et amplis Marshall', 'Studio', 1),
('Studio C - Voix', 'Isolation acoustique pro pour chant', 'Studio', 1),
('Studio M.A.O', 'Stations Ableton et enceintes Focal', 'Studio', 1),
('Studio Podcasting', '4 micros Shure SM7B et mixeur Rode', 'Studio', 1),
('Amphi Audio', 'Grande salle pour conférences musicales', 'Studio', 1),
('Guitare Fender Stratocaster', 'Série American Professional II', 'Instrument', 1),
('Guitare Gibson Les Paul', 'Standard 60s Bourbon Burst', 'Instrument', 1),
('Basse Fender Jazz Bass', 'Basse électrique 4 cordes', 'Instrument', 1),
('Batterie Roland V-Drums', 'Batterie électronique haut de gamme', 'Instrument', 1),
('Synthétiseur Korg Minilogue', 'Polyphonic Analog Synthesizer', 'Instrument', 1),
('Clavier Maître Arturia', '88 touches lestées', 'Instrument', 2),
('Violon électrique Yamaha', 'Série Silent Violin', 'Instrument', 1),
('Micro Shure SM58', 'Micro dynamique polyvalent', 'Matériel', 10),
('Micro Rode NT1-A', 'Pack enregistrement complet', 'Matériel', 4),
('Carte Son Focusrite 2i2', 'Interface audio USB', 'Matériel', 6),
('Casque Beyerdynamic DT770', 'Casque de studio fermé 80 Ohm', 'Matériel', 8),
('Enceinte Bose S1 Pro', 'Système de sonorisation portable', 'Matériel', 2),
('Contrôleur DJ Pioneer', 'DDJ-FLX4 pour débutants et pros', 'Matériel', 1),
('Câble XLR 10m', 'Câble blindé Neutrik', 'Matériel', 15);


-- Création événements
INSERT INTO evenement (titre, description, date_debut, date_fin, lieu, capacite_max, type_evenement, statut_validation, id_organisateur, est_reserve_membres) VALUES 
('Rock au Campus', 'Le grand concert de fin d\'année !', '2024-06-18 20:00:00', '2024-06-18 23:30:00', 'Amphi Central', 200, 'CONCERT', 'Validé', 2, FALSE),
('Atelier Mixage', 'Initiation à la production sur Ableton.', '2024-06-22 14:00:00', '2024-06-22 17:00:00', 'Studio M.A.O', 12, 'WORKSHOP', 'Validé', 4, TRUE),
('Jazz & Blues Jam', 'Session improvisation ouverte.', '2024-07-05 19:00:00', '2024-07-05 22:00:00', 'Foyer Etudiant', 80, 'JAM SESSION', 'Validé', 2, FALSE),
('Electro Pulse', 'DJ sets toute la nuit.', '2024-07-12 22:00:00', '2024-07-13 04:00:00', 'Gymnase', 300, 'DJ NIGHT', 'Validé', 4, FALSE),
('Slam Session', 'Partage de textes et poésie.', '2024-06-25 18:30:00', '2024-06-25 21:00:00', 'Cafétéria', 50, 'OPEN MIC', 'Validé', 2, FALSE),
('Audition Groupe Funk', 'On cherche un bassiste motivé !', '2024-06-30 16:00:00', '2024-06-30 19:00:00', 'Studio B', 5, 'AUDITION', 'Validé', 3, TRUE),
('Piano Masterclass', 'Cours public avec un pianiste pro.', '2024-09-15 10:00:00', '2024-09-15 12:00:00', 'Studio A', 20, 'WORKSHOP', 'Validé', 4, FALSE),
('Back to School Jam', 'Jam de rentrée pour les nouveaux.', '2024-09-20 18:00:00', '2024-09-20 21:00:00', 'Parc Campus', 150, 'JAM SESSION', 'Validé', 2, FALSE),
('Acoustic Night', 'Soirée guitare/voix.', '2024-10-10 19:00:00', '2024-10-10 22:00:00', 'Cafétéria', 60, 'CONCERT', 'Validé', 4, FALSE),
('Halloween DJ Mix', 'Ambiance sombre et kicks puissants.', '2024-10-31 21:00:00', '2024-11-01 02:00:00', 'Foyer', 100, 'DJ NIGHT', 'Validé', 2, FALSE),
('Winter Showcase', 'Démonstration des projets M.A.O.', '2024-12-15 14:00:00', '2024-12-15 18:00:00', 'Studio M.A.O', 30, 'CONCERT', 'En attente', 4, TRUE), -- À VALIDER
('Projet Metal', 'Recherche chanteur pour projet studio.', '2024-11-05 18:00:00', '2024-11-05 20:00:00', 'Studio B', 5, 'AUDITION', 'En attente', 3, FALSE), -- À VALIDER
('Rap & Trap Contest', 'Compétition de flow.', '2024-08-12 20:00:00', '2024-08-12 23:00:00', 'Amphi B', 120, 'OPEN MIC', 'Refusé', 2, FALSE), -- CAS PARTICULIER
('Concert Passé 1', 'Souvenir de début d\'année.', '2024-01-10 20:00:00', '2024-01-10 22:00:00', 'Amphi Central', 100, 'CONCERT', 'Validé', 4, FALSE),
('Concert Passé 2', 'Session acoustique hiver.', '2024-02-15 18:00:00', '2024-02-15 20:00:00', 'Studio A', 20, 'CONCERT', 'Validé', 2, FALSE);

-- ============================================================
-- 4. INSCRIPTIONS & RÉSERVATIONS (Simulation d'activité)
-- ============================================================
    
INSERT INTO inscription (id_utilisateur, id_evenement, statut_inscription) VALUES 
(3, 1, 'Confirmé'), (5, 1, 'Confirmé'), (6, 1, 'Confirmé'), -- Rock Night
(3, 2, 'En attente'), (5, 2, 'En attente'), -- MAO (Validation manuelle)
(1, 3, 'Confirmé'), (3, 3, 'Confirmé'), (4, 3, 'Confirmé');

INSERT INTO reservation (date_debut, date_fin, statut, id_utilisateur, id_ressource) VALUES 
('2024-06-20 14:00:00', '2024-06-20 16:00:00', 'Approuvée', 3, 1),
('2024-06-20 10:00:00', '2024-06-20 12:00:00', 'Approuvée', 5, 2),
('2024-06-21 14:00:00', '2024-06-21 15:00:00', 'En attente', 3, 7), -- Demande de Guitare
('2024-06-21 14:00:00', '2024-06-21 16:00:00', 'En attente', 6, 1); -- CONFLIT Studio A

-- Création Forum et notifs
INSERT INTO forum (titre, id_createur, categorie) VALUES 
('Cherche chanteur pour groupe Rock', 3, 'Collaborations'),
('Conseils pour mixer sur Ableton', 6, 'Technique'),
('Perdu ma médiatrice préférée au Studio B', 5, 'Général'),
('Covoiturage pour le concert de Lucie', 4, 'Événements');

INSERT INTO message_forum (contenu, id_auteur, id_forum) VALUES 
('Salut Marc, je suis chaud pour le projet rock !', 5, 1),
('Top ! On se voit quand ?', 3, 1),
('Utilise l\'EQ Eight, c\'est la base.', 1, 2),
('Merci pour le conseil admin !', 6, 2);

INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES 
('Bienvenue !', 'Bienvenue sur Campus Melody, Julien.', 'forum', 1),
('Nouvel événement', 'Sarah a créé Rock au Campus.', 'rappel-événement', 3),
('Demande en attente', 'Thomas Petit veut devenir membre.', 'adhésion-association', 1);
