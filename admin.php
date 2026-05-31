<?php
session_start();
require_once 'configuration.php'; 

// Sécurité : Vérification du rôle Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: accueil.html"); 
    exit();
}

$user_id = $_SESSION['user_id'];

// ==========================================
// ACTIONS POST : Traitement des validations
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = $_POST['item_id'];
    $type = $_POST['item_type'];
    $decision_action = $_POST['action']; // 'approuver' ou 'refuser'

    try {
        // CAS 1 : Création d'événement
        if ($type === 'creation') {
            $decision = ($decision_action === 'approuver') ? 'Validé' : 'Refusé';
            
            $stmt = $pdo->prepare("UPDATE evenement SET statut_validation = ?, id_validateur = ?, date_decision = NOW() WHERE id_evenement = ?");
            $stmt->execute([$decision, $user_id, $id]);

            $stmtInfo = $pdo->prepare("SELECT titre, id_organisateur FROM evenement WHERE id_evenement = ?");
            $stmtInfo->execute([$id]);
            $ev = $stmtInfo->fetch();

            if ($ev) {
                $msg = ($decision === 'Validé') 
                    ? "Génial ! Ton événement '".$ev['titre']."' a été validé." 
                    : "Malheureusement, ton projet '".$ev['titre']."' n'a pas été accepté.";

                $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, 'creation-evenement', ?)")
                    ->execute(["Résultat validation", $msg, $ev['id_organisateur']]);
            }

        // CAS 2 : Inscription à un événement
        } elseif ($type === 'inscription') {
            $decision = ($decision_action === 'approuver') ? 'Confirmé' : 'Refusé';
            $parts = explode('-', $id);
            $target_user = $parts[0];
            $target_event = $parts[1];

            $stmt = $pdo->prepare("UPDATE inscription SET statut_inscription = ?, id_validateur = ?, date_decision = NOW() WHERE id_utilisateur = ? AND id_evenement = ?");
            $stmt->execute([$decision, $user_id, $target_user, $target_event]);

            $stmtEv = $pdo->prepare("SELECT titre FROM evenement WHERE id_evenement = ?");
            $stmtEv->execute([$target_event]);
            $titreEv = $stmtEv->fetchColumn();
            
            $msg = ($decision === 'Confirmé') ? "Ton inscription à '$titreEv' est validée." : "Ton inscription à '$titreEv' a été refusée.";
            $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, 'inscription-evenement', ?)")
                ->execute(["Inscription", $msg, $target_user]);

        // CAS 3 : Réservation Salle / Matériel
        } elseif ($type === 'reservation') {
            $decision_resa = ($decision_action === 'approuver') ? 'Approuvée' : 'Refusée';
            $stmt = $pdo->prepare("UPDATE reservation SET statut = ?, id_validateur = ?, date_decision = NOW() WHERE id_reservation = ?");
            $stmt->execute([$decision_resa, $user_id, $id]);

            $stmtInfo = $pdo->prepare("SELECT r.id_utilisateur, res.nom FROM reservation r JOIN ressource res ON r.id_ressource = res.id_resource WHERE r.id_reservation = ?");
            $stmtInfo->execute([$id]);
            $res = $stmtInfo->fetch();
            if ($res) {
                $msg = ($decision_resa === 'Approuvée') ? "Ta réservation pour '".$res['nom']."' est validée." : "Ta demande pour '".$res['nom']."' a été refusée.";
                $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, 'rappel-materiel', ?)")
                    ->execute(["Réservation", $msg, $res['id_utilisateur']]);
            }

        // CAS 4 : Adhésion à l'association
        } elseif ($type === 'adhesion') {
            $decision = ($decision_action === 'approuver') ? 'Validé' : 'Refusé';
            $is_membre = ($decision === 'Validé') ? 1 : 0;
            $new_role = ($decision === 'Validé') ? 'Membre' : 'Visiteur';

            $stmt = $pdo->prepare("UPDATE utilisateur SET statut_adhesion = ?, est_membre_asso = ?, role = ? WHERE id_utilisateur = ?");
            $stmt->execute([$decision, $is_membre, $new_role, $id]);

            $msg = ($decision === 'Validé') ? "Félicitations ! Ton adhésion a été validée." : "Désolé, ton adhésion n'a pas été acceptée.";
            $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, 'adhesion-association', ?)")
                ->execute(["Statut Adhésion", $msg, $id]);
        }
    } catch (Exception $e) { $error = $e->getMessage(); }
}

// ==========================================
// RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE
// ==========================================
$requests = [];

// 1. Créations d'événements
$stmtEv = $pdo->query("SELECT e.*, u.nom, u.prenom FROM evenement e JOIN utilisateur u ON e.id_organisateur = u.id_utilisateur WHERE e.statut_validation = 'En attente'");
while ($row = $stmtEv->fetch()) { 
    $requests[] = ['id' => $row['id_evenement'], 'title' => "Création : ".$row['titre'], 'user' => $row['prenom']." ".$row['nom'], 'type' => 'creation', 'detail' => "Projet ".$row['type_evenement']]; 
}

// 2. Inscriptions
$stmtInscr = $pdo->query("SELECT i.*, u.nom, u.prenom, e.titre FROM inscription i JOIN utilisateur u ON i.id_utilisateur = u.id_utilisateur JOIN evenement e ON i.id_evenement = e.id_evenement WHERE i.statut_inscription = 'En attente'");
while ($row = $stmtInscr->fetch()) { 
    $requests[] = ['id' => $row['id_utilisateur'].'-'.$row['id_evenement'], 'title' => "Inscription : ".$row['titre'], 'user' => $row['prenom']." ".$row['nom'], 'type' => 'inscription', 'detail' => "Demande de participation"]; 
}

// 3. Réservations
$stmtResa = $pdo->query("SELECT r.*, u.nom, u.prenom, res.nom as res_nom FROM reservation r JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur JOIN ressource res ON r.id_ressource = res.id_resource WHERE r.statut = 'En attente'");
while ($row = $stmtResa->fetch()) { 
    $requests[] = ['id' => $row['id_reservation'], 'title' => "Réservation : ".$row['res_nom'], 'user' => $row['prenom']." ".$row['nom'], 'type' => 'reservation', 'detail' => "Demande de créneau"]; 
}

// 4. Adhésions
$stmtAdh = $pdo->query("SELECT id_utilisateur, nom, prenom, date_inscription FROM utilisateur WHERE statut_adhesion = 'En attente'");
while ($row = $stmtAdh->fetch()) { 
    $requests[] = ['id' => $row['id_utilisateur'], 'title' => "Adhésion : ".$row['prenom']." ".$row['nom'], 'user' => "Nouveau compte", 'type' => 'adhesion', 'detail' => "Inscrit le ".date('d/m', strtotime($row['date_inscription']))]; 
}

// Statistiques du Dashboard
$stats = [
    'membres' => $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE role='Membre' OR role='Admin'")->fetchColumn(), 
    'inscriptions' => $pdo->query("SELECT COUNT(*) FROM inscription WHERE statut_inscription='Confirmé'")->fetchColumn(), 
    'en_attente' => count($requests)
];

// Infos pour la TopBar
$stmtUser = $pdo->prepare("SELECT prenom, nom FROM utilisateur WHERE id_utilisateur = ?");
$stmtUser->execute([$user_id]);
$u = $stmtUser->fetch();
$initiales = strtoupper(substr($u['prenom']??'A',0,1).substr($u['nom']??'D',0,1));

include 'admin_view.php';