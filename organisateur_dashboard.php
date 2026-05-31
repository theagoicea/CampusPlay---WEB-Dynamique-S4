<?php
session_start();
require_once 'configuration.php'; 

// Sécurité : Réservé strictement aux Organisateurs
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Organisateur') {
    header("Location: accueil.html"); 
    exit();
}

$user_id = $_SESSION['user_id'];

// ACTION : Valider ou Refuser une inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $parts = explode('-', $_POST['item_id']); // "id_user-id_event"
    $target_user = $parts[0];
    $target_event = $parts[1];
    $decision = ($_POST['action'] === 'approuver') ? 'Confirmé' : 'Refusé';

    // Vérification de sécurité : l'événement appartient-il bien à cet organisateur ?
    $check = $pdo->prepare("SELECT id_organisateur FROM evenement WHERE id_evenement = ?");
    $check->execute([$target_event]);
    if ($check->fetchColumn() == $user_id) {
        $stmt = $pdo->prepare("UPDATE inscription SET statut_inscription = ?, id_validateur = ?, date_decision = NOW() WHERE id_utilisateur = ? AND id_evenement = ?");
        $stmt->execute([$decision, $user_id, $target_user, $target_event]);
        
        // Notif pour le participant
        $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES ('Inscription', 'Ton inscription a été validée par l\'organisateur.', 'inscription-evenement', ?)")
            ->execute([$target_user]);
    }
}

// RÉCUPÉRATION : Uniquement les inscriptions pour MES événements
$requests = [];
$stmt = $pdo->prepare("SELECT i.*, u.nom, u.prenom, e.titre FROM inscription i 
                       JOIN utilisateur u ON i.id_utilisateur = u.id_utilisateur 
                       JOIN evenement e ON i.id_evenement = e.id_evenement 
                       WHERE e.id_organisateur = ? AND i.statut_inscription = 'En attente'");
$stmt->execute([$user_id]);

while ($row = $stmt->fetch()) {
    $requests[] = [
        'id' => $row['id_utilisateur'].'-'.$row['id_evenement'],
        'title' => $row['titre'],
        'user' => $row['prenom'].' '.$row['nom'],
        'type' => 'INSCRIPTION'
    ];
}

// Infos pour la TopBar
$stmtUser = $pdo->prepare("SELECT prenom, nom FROM utilisateur WHERE id_utilisateur = ?");
$stmtUser->execute([$user_id]);
$u = $stmtUser->fetch();
$initiales = strtoupper(substr($u['prenom'],0,1).substr($u['nom'],0,1));

include 'organisateur_view.php';