<?php
// On vérifie que la session et le PDO sont dispos
if (!isset($pdo) || !isset($user_id)) return;

// ==========================================================
// 1. GESTION DYNAMIQUE DU RÔLE (Membre <-> Organisateur)
// ==========================================================

try {
    // A. Un Membre devient Organisateur s'il a un événement VALIDÉ qui n'est pas encore fini
    $pdo->query("UPDATE utilisateur 
                 SET role = 'Organisateur' 
                 WHERE role = 'Membre' 
                 AND id_utilisateur IN (
                     SELECT id_organisateur FROM evenement 
                     WHERE statut_validation = 'Validé' AND date_fin > NOW()
                 )");

    // B. Un Organisateur redevient Membre s'il n'a PLUS d'événements futurs validés
    $pdo->query("UPDATE utilisateur 
                 SET role = 'Membre' 
                 WHERE role = 'Organisateur' 
                 AND id_utilisateur NOT IN (
                     SELECT id_organisateur FROM evenement 
                     WHERE statut_validation = 'Validé' AND date_fin > NOW() 
                     AND id_organisateur IS NOT NULL
                 )");

    // C. Mise à jour de la session pour que le menu change immédiatement
    $stmtRole = $pdo->prepare("SELECT role FROM utilisateur WHERE id_utilisateur = ?");
    $stmtRole->execute([$user_id]);
    $nouveauRole = $stmtRole->fetchColumn();
    if ($nouveauRole) {
        $_SESSION['role'] = $nouveauRole;
    }

} catch (Exception $e) {
    // Si une erreur SQL survient, on l'ignore pour ne pas bloquer la page
}

// ==========================================================
// 2. RAPPELS AUTOMATIQUES (1 jour avant)
// ==========================================================

// Rappel Événement (BLEU)
$stmtEv = $pdo->prepare("
    SELECT e.titre, e.date_debut 
    FROM inscription i
    JOIN evenement e ON i.id_evenement = e.id_evenement
    WHERE i.id_utilisateur = ? 
    AND i.statut_inscription = 'Confirmé'
    AND DATE(e.date_debut) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
");
$stmtEv->execute([$user_id]);
$events = $stmtEv->fetchAll();

foreach ($events as $ev) {
    $msg = "Préparez-vous ! Votre événement '" . $ev['titre'] . "' commence demain à " . date('H:i', strtotime($ev['date_debut'])) . ".";
    $check = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE id_destinataire = ? AND message = ?");
    $check->execute([$user_id, $msg]);
    
    if ($check->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES ('Rappel d\'événement', ?, 'rappel-evenement', ?)")
            ->execute([$msg, $user_id]);
    }
}

// Rappel Matériel (ORANGE)
$stmtRes = $pdo->prepare("
    SELECT res.nom, r.date_debut 
    FROM reservation r
    JOIN ressource res ON r.id_ressource = res.id_resource
    WHERE r.id_utilisateur = ? 
    AND r.statut = 'Approuvée'
    AND DATE(r.date_debut) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
");
$stmtRes->execute([$user_id]);
$resas = $stmtRes->fetchAll();

foreach ($resas as $ra) {
    $msg = "Rappel : Votre réservation pour '" . $ra['nom'] . "' est prévue pour demain à " . date('H:i', strtotime($ra['date_debut'])) . ".";
    $check = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE id_destinataire = ? AND message = ?");
    $check->execute([$user_id, $msg]);
    
    if ($check->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES ('Rappel retour matériel', ?, 'rappel-materiel', ?)")
            ->execute([$msg, $user_id]);
    }
}
