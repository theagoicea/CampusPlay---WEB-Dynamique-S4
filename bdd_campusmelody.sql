-- Création et sélection de la base de données
DROP DATABASE IF EXISTS campusmelody;
CREATE DATABASE campusmelody;
USE campusmelody;

DROP TABLE IF EXISTS inscription;
DROP TABLE IF EXISTS notification;
DROP TABLE IF EXISTS reservation;
DROP TABLE IF EXISTS evenement;
DROP TABLE IF EXISTS ressource;
DROP TABLE IF EXISTS utilisateur;


-- Table : UTILISATEUR
CREATE TABLE utilisateur (
    id_utilisateur INT AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
	photo_profil VARCHAR(255) DEFAULT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Visiteur', -- 'Visiteur', 'Membre', 'Organisateur' ou 'Admin'
    est_membre_asso BOOLEAN DEFAULT FALSE,
    statut_adhesion VARCHAR(50) DEFAULT 'Non membre', -- 'Non membre', 'En attente', 'Validé'
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT pk_utilisateur PRIMARY KEY (id_utilisateur),
    CONSTRAINT uq_utilisateur_email UNIQUE (email)
);

-- Table : RESSOURCE (Salles et Matériels)
CREATE TABLE ressource (
    id_resource INT AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    type_ressource VARCHAR(50) NOT NULL, -- 'Studio', 'Instrument', 'Matériel' ou 'Salle'
    quantite_totale INT NOT NULL DEFAULT 1,
    statut_actuel VARCHAR(50) NOT NULL DEFAULT 'Disponible', -- 'Disponible' ou 'Indisponible'
    CONSTRAINT pk_ressource PRIMARY KEY (id_resource)
);

-- Table : EVENEMENT 
CREATE TABLE evenement (
    id_evenement INT AUTO_INCREMENT,
    titre VARCHAR(150) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    lieu VARCHAR(100) NOT NULL,
    capacite_max INT NOT NULL,
    type_evenement VARCHAR(50) NOT NULL, 
	est_reserve_membres BOOLEAN DEFAULT FALSE,
	besoin_validation_inscription BOOLEAN DEFAULT FALSE, -- TRUE = Manuel, FALSE = Automatique
    statut_validation VARCHAR(50) NOT NULL DEFAULT 'En attente', -- 'En attente', 'Validé' ou 'Refusé'
    
    -- Clé étrangère : Organisateur de l'événement 
    id_organisateur INT NOT NULL,
    
    -- Clés de validation - nulle tant que non validé
    id_validateur INT NULL,
    date_decision DATETIME NULL,
    commentaire_validation TEXT NULL,
    
    CONSTRAINT pk_evenement PRIMARY KEY (id_evenement),
    
    CONSTRAINT fk_evenement_organisateur 
        FOREIGN KEY (id_organisateur) REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE ON UPDATE CASCADE,
        
    CONSTRAINT fk_evenement_validateur 
        FOREIGN KEY (id_validateur) REFERENCES utilisateur(id_utilisateur)
        ON DELETE SET NULL ON UPDATE CASCADE
);

-- Table : RESERVATION 
CREATE TABLE reservation (
    id_reservation INT AUTO_INCREMENT,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    date_retour DATETIME NULL, -- Nulle tant que le matériel n'est pas restitué
    statut VARCHAR(50) NOT NULL DEFAULT 'En attente', -- 'En attente', 'Approuvée', 'Refusée' ou 'Terminée'
    
    -- Clés étrangères 
    id_utilisateur INT NOT NULL,
    id_ressource INT NOT NULL,
    
    -- Clés de validation - nulles tant que non validé
    id_validateur INT NULL,
    date_decision DATETIME NULL,
    commentaire_validation TEXT NULL,
    
    CONSTRAINT pk_reservation PRIMARY KEY (id_reservation),
    
    CONSTRAINT fk_reservation_utilisateur 
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE ON UPDATE CASCADE,
        
    CONSTRAINT fk_reservation_ressource 
        FOREIGN KEY (id_ressource) REFERENCES ressource(id_resource)
        ON DELETE CASCADE ON UPDATE CASCADE,
        
    CONSTRAINT fk_reservation_validateur 
        FOREIGN KEY (id_validateur) REFERENCES utilisateur(id_utilisateur)
        ON DELETE SET NULL ON UPDATE CASCADE
);

-- Table : NOTIFICATION
CREATE TABLE notification (
    id_notification INT AUTO_INCREMENT,
    titre VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    type_notification VARCHAR(50) NOT NULL, 
    statut_lecture BOOLEAN DEFAULT FALSE,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Clé étrangère 
    id_destinataire INT NOT NULL,
    
    CONSTRAINT pk_notification PRIMARY KEY (id_notification),
    
    CONSTRAINT fk_notification_destinataire 
        FOREIGN KEY (id_destinataire) REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Table associative : INSCRIPTION (Gère la relation entre Utilisateur et Evénement)
CREATE TABLE inscription (
    id_utilisateur INT NOT NULL,
    id_evenement INT NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut_inscription VARCHAR(50) NOT NULL DEFAULT 'En attente', -- 'En attente', 'Confirmé' ou 'Annulé'
    id_validateur INT NULL,
    date_decision DATETIME NULL,
    commentaire_validation TEXT NULL,
    
    CONSTRAINT pk_inscription PRIMARY KEY (id_utilisateur, id_evenement),
	
	CONSTRAINT fk_inscription_validateur FOREIGN KEY (id_validateur) REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL ON UPDATE CASCADE,
    
    CONSTRAINT fk_inscription_utilisateur 
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE ON UPDATE CASCADE,
        
    CONSTRAINT fk_inscription_evenement 
        FOREIGN KEY (id_evenement) REFERENCES evenement(id_evenement)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Table : FORUM
CREATE TABLE forum (
    id_forum INT AUTO_INCREMENT,
    titre VARCHAR(150) NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    est_ferme BOOLEAN DEFAULT FALSE,
    id_createur INT NOT NULL,
	categorie VARCHAR(50) DEFAULT 'general',
    id_evenement_associe INT NULL, -- Permet d'avoir un fil de discussion lié à un événement
    CONSTRAINT pk_forum PRIMARY KEY (id_forum),
    CONSTRAINT fk_forum_createur FOREIGN KEY (id_createur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_forum_evenement FOREIGN KEY (id_evenement_associe) REFERENCES evenement(id_evenement) ON DELETE SET NULL ON UPDATE CASCADE
);

-- Table : MESSAGE_FORUM
CREATE TABLE message_forum (
    id_message INT AUTO_INCREMENT,
    contenu TEXT NOT NULL,
    date_publication TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    signale BOOLEAN DEFAULT FALSE,
    id_auteur INT NOT NULL,
    id_forum INT NOT NULL,
    CONSTRAINT pk_message_forum PRIMARY KEY (id_message),
    CONSTRAINT fk_message_auteur FOREIGN KEY (id_auteur) REFERENCES utilisateur(id_utilisateur) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_message_forum FOREIGN KEY (id_forum) REFERENCES forum(id_forum) ON DELETE CASCADE ON UPDATE CASCADE
);


-- pour créer 3 evenement

-- 1. On crée un utilisateur organisateur (obligatoire pour les événements)
INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, est_membre_asso, statut_adhesion) 
VALUES ('Martin', 'Julien', 'julien@campus.fr', 'root', 'Organisateur', TRUE, 'Validé');

-- 2. On crée 3 événements liés à cet organisateur (on suppose que son id est 1)
INSERT INTO evenement (titre, description, date_debut, date_fin, lieu, capacite_max, type_evenement, est_reserve_membres, besoin_validation_inscription, statut_validation, id_organisateur)
VALUES 
('Rock au Campus', 'Le grand concert de fin d\'année avec les meilleurs groupes.', '2024-06-18 20:00:00', '2024-06-18 23:30:00', 'Amphi Central', 200, 'CONCERT', FALSE, FALSE, 'Validé', 1),
('Initiation Mixage & M.A.O', 'Découvrez la production musicale sur Ableton.', '2024-06-22 14:00:00', '2024-06-22 17:00:00', 'Studio M.A.O', 15, 'WORKSHOP', TRUE, TRUE, 'Validé', 1),
('Jazz & Blues Impro', 'Rejoignez-nous pour une session d\'improvisation totale.', '2024-12-12 19:00:00', '2024-12-12 23:00:00', 'Studio A', 100, 'JAM SESSION', FALSE, FALSE, 'Validé', 1);

-- 3. On crée 4 ressources
INSERT INTO ressource (nom, description, type_ressource, statut_actuel) VALUES 
('Studio A', 'Grand studio avec piano', 'Studio', 'Disponible'),
('Studio B', 'Studio batterie', 'Studio', 'Disponible'),
('Micro Shure SM58', 'Micro chant', 'Matériel', 'Disponible'),
('Guitare Fender', 'Guitare électrique', 'Instrument', 'Disponible');
