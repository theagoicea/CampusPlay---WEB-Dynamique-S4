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
-- 1. UTILISATEURS 
-- --------------------------------------------------------
INSERT INTO utilisateur (id_utilisateur, nom, prenom, email, mot_de_passe, role, est_membre_asso, statut_adhesion) VALUES
(1, 'Martin', 'Julien', 'admin@campus.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 1, 'Validé'),
(2, 'Organisateur', 'Test', 'orga@campus.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Organisateur', 1, 'Validé'),
(3, 'Membre', 'Classique', 'membre@campus.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Membre', 1, 'Validé'),
(4, 'Visiteur', 'Curieux', 'visiteur@campus.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Visiteur', 0, 'Non membre'),
(5, 'Attente', 'Adhesion', 'attente@campus.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Visiteur', 0, 'En attente'),
(6, 'Refusé', 'Triste', 'refuse@campus.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Visiteur', 0, 'Refusé');

-- --------------------------------------------------------
-- 2. RESSOURCES (Salles & Matériel)
-- --------------------------------------------------------
INSERT INTO ressource (nom, description, type_ressource, quantite_totale) VALUES 
('Studio A - Piano', 'Équipé Steinway & Sons', 'Studio', 1),
('Studio B - Rock', 'Équipé batterie DW et amplis Marshall', 'Studio', 1),
('Studio C - Voix', 'Isolation acoustique pro pour chant', 'Studio', 1),
('Studio M.A.O', 'Stations Ableton et enceintes Focal', 'Studio', 1),
('Guitare Fender Stratocaster', 'Série American Professional II', 'Instrument', 1),
('Guitare Gibson Les Paul', 'Standard 60s Bourbon Burst', 'Instrument', 1),
('Basse Fender Jazz Bass', 'Basse électrique 4 cordes', 'Instrument', 1),
('Batterie Roland V-Drums', 'Batterie électronique haut de gamme', 'Instrument', 1),
('Synthétiseur Korg Minilogue', 'Polyphonic Analog Synthesizer', 'Instrument', 1),
('Clavier Maître Arturia', '88 touches lestées', 'Instrument', 2),
('Micro Shure SM58', 'Micro dynamique polyvalent', 'Matériel', 10),
('Micro Rode NT1-A', 'Pack enregistrement complet', 'Matériel', 4),
('Casque Beyerdynamic DT770', 'Casque de studio fermé 80 Ohm', 'Matériel', 8),
('Enceinte Bose S1 Pro', 'Système de sonorisation portable', 'Matériel', 2),
('Contrôleur DJ Pioneer', 'DDJ-FLX4 pour débutants et pros', 'Matériel', 1),
('Câble XLR 10m', 'Câble blindé Neutrik', 'Matériel', 15);

-- --------------------------------------------------------
-- 3. EVENEMENTS
-- --------------------------------------------------------
INSERT INTO evenement (id_evenement, titre, description, image_url, date_debut, date_fin, lieu, capacite_max, type_evenement, statut_validation, id_organisateur, est_reserve_membres) VALUES 

-- 2024
(1, 'Nouvel An Musical', 
'Lancez votre année sous le signe de la créativité ! Rejoignez-nous pour la toute première jam session de la rentrée. Que vous soyez un virtuose de la guitare, un débutant au piano ou simplement un passionné de chant, venez rencontrer les autres musiciens du campus. Le matériel (amplis, batterie, micros) est fourni sur place. C’est le moment idéal pour former vos futurs groupes !', 
'https://images.unsplash.com/photo-1511192336575-5a79af67a629?q=80&w=1000', '2024-01-10 18:00:00', '2024-01-10 22:00:00', 'Studio A', 30, 'JAM SESSION', 'Validé', 2, FALSE),

(2, 'Spring Rock Fest', 
'Le grand rendez-vous du printemps est enfin là ! Le Spring Rock Fest transforme le parc du campus en véritable festival en plein air. Au programme : 5 groupes locaux, des stands de nourriture locale et une ambiance électrique. Venez profiter du soleil et des riffs saturés pour fêter l’arrivée des beaux jours. Un espace de merchandising sera disponible pour soutenir les artistes.', 
'https://images.unsplash.com/photo-1498038432885-c6f3f1b912ee?q=80&w=1000', '2024-04-15 14:00:00', '2024-04-15 23:00:00', 'Parc Campus', 500, 'CONCERT', 'Validé', 4, FALSE),

(3, 'Rock au Campus', 
'L’événement phare de la fin d’année universitaire ! Les meilleurs groupes de l’association montent sur la scène de l’Amphi Central pour une soirée de clôture mémorable. Des jeux de lumières professionnels et un son puissant vous attendent pour célébrer la fin des examens. Venez encourager vos amis et découvrir les pépites musicales de notre école.', 
'https://images.unsplash.com/photo-1493225255756-d9584f8606e9?q=80&w=1000', '2024-06-18 20:00:00', '2024-06-18 23:30:00', 'Amphi Central', 200, 'CONCERT', 'Validé', 2, FALSE),

(4, 'Atelier Mixage Été', 
'Vous produisez de la musique mais vos morceaux manquent de clarté ? Cet atelier intensif sur Ableton Live vous apprendra les secrets du mixage professionnel. De la gestion de l’égalisation (EQ) à la compression, en passant par l’utilisation créative de la réverbération, repartez avec des techniques concrètes pour faire passer vos productions au niveau supérieur. Réservé aux membres pour garantir un suivi personnalisé.', 
'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?q=80&w=1000', '2024-07-22 14:00:00', '2024-07-22 17:00:00', 'Studio M.A.O', 12, 'WORKSHOP', 'Validé', 4, TRUE),

(5, 'Halloween DJ Mix', 
'Préparez vos meilleurs déguisements pour la nuit la plus sombre de l’année. Nos DJ résidents transformeront le foyer en un dancefloor hanté. Entre sets Techno industriels et remixes House obscurs, l’immersion sera totale. Concours de déguisement avec des lots à gagner et bar à jus de citrouille sur place. Sensations fortes garanties !', 
'https://images.unsplash.com/photo-1509248961158-e54f6934749c?q=80&w=1000', '2024-10-31 21:00:00', '2024-11-01 02:00:00', 'Foyer', 100, 'DJ NIGHT', 'Validé', 2, FALSE),

(6, 'Winter Showcase', 
'Découvrez les projets musicaux nés durant le premier semestre. Ce showcase met en avant les étudiants en M.A.O (Musique Assistée par Ordinateur) qui présenteront leurs créations originales. Une écoute critique mais bienveillante, suivie d’un moment d’échange sur les techniques de composition. Idéal pour ceux qui souhaitent découvrir l’envers du décor de la création électronique.', 
'https://images.unsplash.com/photo-1514525253361-bee8a187499b?q=80&w=1000', '2024-12-15 14:00:00', '2024-12-15 18:00:00', 'Studio M.A.O', 30, 'CONCERT', 'En attente', 4, TRUE),

-- 2025
(7, 'Acoustic Valentine', 
'Pour la Saint-Valentin, Campus Melody vous propose une soirée intimiste et chaleureuse. Au programme : des performances acoustiques (guitare-voix, piano, violoncelle) dans un cadre tamisé. Que vous soyez en couple, entre amis ou en solo, venez profiter de la douceur de la musique acoustique et des reprises de standards jazz et pop.', 
'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?q=80&w=1000', '2025-02-14 19:00:00', '2025-02-14 22:00:00', 'Cafétéria', 60, 'CONCERT', 'Validé', 6, FALSE),

(8, 'Jazz Impro Spring', 
'Libérez votre créativité lors de notre soirée d’improvisation Jazz. Ouvert à tous les niveaux, cet événement permet d’explorer les structures standards du jazz tout en laissant place à l’expression individuelle. Une section rythmique de base sera là pour accompagner tous les solistes qui souhaitent tenter l’expérience du "lead". La liberté musicale est le seul mot d’ordre !', 
'https://images.unsplash.com/photo-1511379938547-c1f69419868d?q=80&w=1000', '2025-03-20 19:00:00', '2025-03-20 22:00:00', 'Foyer Etudiant', 80, 'JAM SESSION', 'Validé', 2, FALSE),

(9, 'Battle de Rap', 
'Préparez vos punchlines ! Le tournoi annuel de rap revient au campus. Organisé en plusieurs rounds (acapella, sur instru, improvisation), cet événement mettra à l’épreuve votre flow, votre écriture et votre présence scénique. Un jury de professionnels du milieu hip-hop sera présent pour désigner le vainqueur. Ambiance survoltée garantie dans l’Amphi B.', 
'https://images.unsplash.com/photo-1520523839897-bd0b52f945a0?q=80&w=1000', '2025-05-12 20:00:00', '2025-05-12 23:00:00', 'Amphi B', 120, 'OPEN MIC', 'Validé', 4, FALSE),

(10, 'Summer Electro', 
'Célébrons l’été avec une nuit dédiée aux musiques électroniques. Du coucher du soleil jusqu’à l’aube, plusieurs DJ se succéderont pour vous offrir un voyage entre Melodic Techno, House et Trance. Un système de sonorisation haut de gamme et des installations lumineuses immersives transformeront le gymnase en véritable club éphémère.', 
'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?q=80&w=1000', '2025-07-10 22:00:00', '2025-07-11 04:00:00', 'Gymnase', 400, 'DJ NIGHT', 'Validé', 6, FALSE),

(11, 'Masterclass Batterie', 
'Travaillez votre technique de caisse claire, votre indépendance et votre groove lors de cette masterclass exceptionnelle. Animée par un batteur de session professionnel, cette session de 7 heures couvrira aussi bien les rudiments fondamentaux que les techniques de double pédale ou le jeu aux balais. Apportez vos baguettes et votre pad de pratique !', 
'https://images.unsplash.com/photo-1519892300165-cb5542fb47c7?q=80&w=1000', '2025-09-05 10:00:00', '2025-09-05 17:00:00', 'Studio B', 10, 'WORKSHOP', 'Validé', 2, TRUE),

(12, 'Audition Metal', 
'Le groupe résident de l’association, "Iron Campus", cherche sa nouvelle voix ! Si vous maîtrisez le chant saturé (growl, scream) ou si vous avez une voix puissante capable d’affronter des murs de guitares, c’est votre chance. Préparez deux morceaux de votre choix et venez nous montrer votre énergie scénique. Réservé aux membres sérieux et motivés par un projet d’album.', 
'https://images.unsplash.com/photo-1525413183955-f95fd2990c5e?q=80&w=1000', '2025-11-15 18:00:00', '2025-11-15 20:00:00', 'Studio B', 5, 'AUDITION', 'En attente', 3, TRUE),

-- 2026
(13, 'Expo Musique', 
'Une journée d’exploration sur l’histoire et l’évolution de la technologie musicale. De l’invention du synthétiseur aux dernières innovations en Intelligence Artificielle pour la création sonore. Plusieurs stands interactifs vous permettront de tester du matériel vintage (TB-303, TR-808) et des logiciels futuristes. Des mini-conférences auront lieu toutes les heures.', 
'https://images.unsplash.com/photo-1508700115892-45ecd05ae2ad?q=80&w=1000', '2026-03-10 10:00:00', '2026-03-10 18:00:00', 'Amphi Audio', 150, 'WORKSHOP', 'Validé', 4, FALSE),

(15, 'Grand Gala', 
'La soirée la plus élégante de l’année. L’orchestre du campus et la chorale s’unissent pour vous proposer un répertoire allant des grands classiques du cinéma aux standards de la chanson française réarrangés. Une tenue correcte est exigée pour cet événement prestigieux qui clôture la saison musicale. Champagne et petits fours seront servis lors de l’entracte.', 
'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?q=80&w=1000', '2026-05-30 20:30:00', '2026-05-30 23:00:00', 'Amphi Central', 400, 'CONCERT', 'Validé', 6, FALSE),

(16, 'Stage Production', 
'Cinq jours pour produire un titre de A à Z. Ce stage intensif est une immersion totale dans le monde de la production musicale : composition de la mélodie, arrangement de la rythmique, enregistrement des voix et finalisation du mixage. Chaque participant repartira avec son propre morceau masterisé. Un niveau intermédiaire en MAO est requis pour participer.', 
'https://images.unsplash.com/photo-1524368535928-5b5e00ddc76b?q=80&w=1000', '2026-08-10 09:00:00', '2026-08-14 17:00:00', 'Studio M.A.O', 8, 'WORKSHOP', 'En attente', 4, TRUE),

(18, 'Noël Gospel', 
'Réchauffez les cœurs avec notre concert de Noël. La chorale Gospel du campus interprétera des chants traditionnels et des chants d’espoir dans une ambiance feutrée et conviviale. Un moment de partage ouvert à tous, idéal pour se plonger dans la magie des fêtes avant le départ en vacances. Vin chaud et chocolat chaud offerts après le concert.', 
'https://images.unsplash.com/photo-1543807535-eceef0bc6599?q=80&w=1000', '2026-12-20 18:00:00', '2026-12-20 20:00:00', 'Hall', 100, 'OPEN MIC', 'Validé', 6, FALSE),

-- 2027
(19, 'Rentrée 2027', 
'Bienvenue aux nouveaux arrivants ! Cette jam session d’accueil est spécialement conçue pour intégrer les nouveaux étudiants musiciens. C’est l’occasion de découvrir les locaux de l’association, de rencontrer les membres actuels et de trouver des partenaires de jeu. Buffet d’accueil et présentation des activités de l’année au programme.', 
'https://images.unsplash.com/photo-1453738773917-9c3eff1db985?q=80&w=1000', '2027-01-15 19:00:00', '2027-01-15 22:00:00', 'Studio A', 40, 'JAM SESSION', 'Validé', 2, FALSE),

(21, 'Concert 10 ans', 
'Campus Melody fête ses 10 ans d’existence ! Pour cet anniversaire exceptionnel, nous organisons un concert géant réunissant les membres actuels et les anciens de l’association ("Alumni"). Un voyage musical retraçant une décennie de créativité sur le campus. De nombreuses surprises et des invités spéciaux sont attendus pour cette soirée historique.', 
'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?q=80&w=1000', '2027-06-12 19:00:00', '2027-06-13 01:00:00', 'Amphi Central', 600, 'CONCERT', 'Validé', 1, FALSE),

(22, 'Beatmaker Battle', 
'Le défi ultime pour les créateurs de beats. Chaque participant dispose d’un sample imposé et de 20 minutes pour créer une boucle percutante devant le public. Performance en direct sur MPC, Maschine ou Ableton Push. Le gagnant repartira avec un contrôleur MIDI de dernière génération et une visibilité accrue sur nos réseaux sociaux. Venez montrer votre dextérité !', 
'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=1000', '2027-09-18 20:00:00', '2027-09-18 23:00:00', 'Studio M.A.O', 20, 'OPEN MIC', 'Validé', 4, TRUE),

(23, 'Electro Frost', 
'Affrontez le froid de l’hiver avec la chaleur de la Techno. Cet événement nocturne mise sur une scénographie "glaciale" avec des lasers bleus et des machines à fumée pour une ambiance unique. Programmation pointue alternant entre Techno mélodique et rythmes plus industriels. Une boisson chaude sera offerte à tous les participants déguisés sur le thème "Hiver Futuriste".', 
'https://images.unsplash.com/photo-1429962714451-bb934ecbb4ec?q=80&w=1000', '2027-11-12 22:00:00', '2027-11-13 04:00:00', 'Gymnase', 350, 'DJ NIGHT', 'En attente', 6, FALSE),

(24, 'Auditions Finales', 
'Le moment de vérité pour les groupes souhaitant figurer sur la compilation annuelle de Campus Melody. Venez présenter votre meilleur morceau devant un comité de sélection composé de professeurs de musique et de membres du bureau. Seuls les 10 meilleurs projets auront la chance d’être enregistrés et mixés professionnellement dans nos studios durant l’été.', 
'https://images.unsplash.com/photo-1516280440614-37939bbacd81?q=80&w=1000', '2027-12-05 14:00:00', '2027-12-05 19:00:00', 'Studio C', 10, 'AUDITION', 'Validé', 3, TRUE);


-- --------------------------------------------------------
-- 4. INSCRIPTIONS
-- --------------------------------------------------------
INSERT INTO inscription (id_utilisateur, id_evenement, statut_inscription) VALUES
(3, 1, 'Confirmé'), 
(5, 1, 'Confirmé'), 
(1, 3, 'Confirmé'), 
(3, 3, 'Confirmé');

-- --------------------------------------------------------
-- 5. RÉSERVATIONS
-- --------------------------------------------------------
INSERT INTO reservation (id_reservation, date_debut, date_fin, id_utilisateur, id_ressource, statut) VALUES
(1, '2026-06-05 14:00:00', '2026-06-05 16:00:00', 3, 1, 'En attente'), 
(2, '2026-06-01 10:00:00', '2026-06-01 12:00:00', 3, 4, 'Approuvée'), 
(3, '2026-05-20 09:00:00', '2026-05-20 11:00:00', 3, 2, 'Approuvée'); 

-- --------------------------------------------------------
-- 6. FORUM & MESSAGES
-- --------------------------------------------------------
INSERT INTO forum (id_forum, titre, id_createur, categorie, est_ferme) VALUES
(1, 'Bienvenue sur le nouveau forum !', 1, 'general', 0),
(2, 'Conseils pour débuter Ableton', 3, 'tech', 0),
(3, 'Cherche batteur pour groupe de Rock', 4, 'collab', 0),
(4, 'Sujet fermé (Modération)', 1, 'general', 1);

INSERT INTO message_forum (id_message, contenu, id_auteur, id_forum, signale) VALUES
(1, 'N\'hésitez pas à vous présenter ici !', 1, 1, 0),
(2, 'Salut, moi c\'est le membre test.', 3, 1, 0),
(3, 'Message posté par un visiteur non connecté (Anonyme).', NULL, 1, 0),
(4, 'Regarde des tutos sur YouTube c\'est le mieux.', 2, 2, 0),
(5, 'Ce message est insultant et a été signalé.', 6, 3, 1), -- TEST : L'admin verra ce signalement
(6, 'Ce sujet a été verrouillé suite à des débordements.', 1, 4, 0);

-- --------------------------------------------------------
-- 7. NOTIFICATIONS
-- --------------------------------------------------------
INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES
('Bienvenue !', 'Votre compte a bien été créé.', 'forum', 3),
('Demande Adhésion', 'L\'utilisateur "Attente Adhesion" souhaite rejoindre l\'asso.', 'adhesion-association', 1),
('Nouvel Événement', 'Le projet "Festival test" attend votre validation.', 'creation-evenement', 1);