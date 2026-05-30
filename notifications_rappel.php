<?php
// On vérifie que la session et le PDO sont dispos
if (!isset($pdo) || !isset($user_id)) return;

// --- 1. RAPPEL ÉVÉNEMENT (1 jour avant) ---
// On cherche les inscriptions confirmées pour des événements commençant dans 24h environ
$stmtEv = $pdo->prepare("
    SELECT e.id_evenement, e.titre, e.date_debut 
    FROM inscription i
    JOIN evenement e ON i.id_evenement = e.id_evenement
    WHERE i.id_utilisateur = ? 
    AND i.statut_inscription = 'Confirmé'
    AND DATE(e.date_debut) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
");
$stmtEv->execute([$user_id]);
$eventsAvenir = $stmtEv->fetchAll();

foreach ($eventsAvenir as $ev) {
    $titreNotif = "Rappel d'événement";
    $msg = "Préparez-vous ! Votre événement '" . $ev['titre'] . "' commence demain à " . date('H:i', strtotime($ev['date_debut'])) . ".";
    
    // On vérifie si la notif n'a pas déjà été envoyée pour éviter les doublons
    $check = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE id_destinataire = ? AND message = ?");
    $check->execute([$user_id, $msg]);
    
    if ($check->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, 'rappel-evenement', ?)")
            ->execute([$titreNotif, $msg, $user_id]);
    }
}

// --- 2. RAPPEL MATÉRIEL/SALLE (1 jour avant) ---
// On cherche les réservations approuvées commençant demain
$stmtRes = $pdo->prepare("
    SELECT r.id_reservation, res.nom, r.date_debut 
    FROM reservation r
    JOIN ressource res ON r.id_ressource = res.id_resource
    WHERE r.id_utilisateur = ? 
    AND r.statut = 'Approuvée'
    AND DATE(r.date_debut) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
");
$stmtRes->execute([$user_id]);
$resAvenir = $stmtRes->fetchAll();

foreach ($resAvenir as $ra) {
    $titreNotif = "Rappel retour matériel"; // Selon ton image, même si c'est pour l'emprunt
    $msg = "Rappel : Votre réservation pour '" . $ra['nom'] . "' est prévue pour demain à " . date('H:i', strtotime($ra['date_debut'])) . ".";
    
    $check = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE id_destinataire = ? AND message = ?");
    $check->execute([$user_id, $msg]);
    
    if ($check->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, 'rappel-materiel', ?)")
            ->execute([$titreNotif, $msg, $user_id]);
    }
}
