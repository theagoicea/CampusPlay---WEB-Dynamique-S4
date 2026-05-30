<?php
// On désactive l'affichage des erreurs système pour ne pas polluer le JSON
error_reporting(0); 
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

// 1. Vérification connexion
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Vous devez être connecté pour réserver.']);
    exit;
}

require_once 'configuration.php'; // Utilisons ton fichier de config pour plus de cohérence

try {
    $id_user = $_SESSION['user_id'];
    $id_event = isset($_POST['id_evenement']) ? intval($_POST['id_evenement']) : 0;

    if ($id_event <= 0) {
        echo json_encode(['success' => false, 'error' => 'Événement invalide.']);
        exit;
    }

    // 3. Vérifier si déjà inscrit
    $check = $pdo->prepare("SELECT COUNT(*) FROM inscription WHERE id_utilisateur = ? AND id_evenement = ?");
    $check->execute([$id_user, $id_event]);
    if ($check->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'Vous êtes déjà inscrit à cet événement.']);
        exit;
    }

    // 4. Récupérer les infos de l'événement (et de l'organisateur)
    $stmt = $pdo->prepare("SELECT titre, id_organisateur, capacite_max, besoin_validation_inscription FROM evenement WHERE id_evenement = ?");
    $stmt->execute([$id_event]);
    $event = $stmt->fetch();

    if (!$event) {
        echo json_encode(['success' => false, 'error' => 'Événement introuvable.']);
        exit;
    }

    // Vérifier les places
    $count = $pdo->prepare("SELECT COUNT(*) FROM inscription WHERE id_evenement = ? AND statut_inscription != 'Refusé'");
    $count->execute([$id_event]);
    if ($count->fetchColumn() >= $event['capacite_max']) {
        echo json_encode(['success' => false, 'error' => 'Événement complet.']);
        exit;
    }

    // 5. Inscription
    $statut = ($event['besoin_validation_inscription'] == 1) ? 'En attente' : 'Confirmé';
    $insert = $pdo->prepare("INSERT INTO inscription (id_utilisateur, id_evenement, statut_inscription) VALUES (?, ?, ?)");
    $insert->execute([$id_user, $id_event, $statut]);

    // --- NOUVEAU : ENVOI DES NOTIFICATIONS ---

    // A. Récupérer le prénom de l'utilisateur qui s'inscrit (pour l'organisateur)
    $stmtU = $pdo->prepare("SELECT prenom FROM utilisateur WHERE id_utilisateur = ?");
    $stmtU->execute([$id_user]);
    $user_name = $stmtU->fetchColumn();

    // B. Notification pour l'UTILISATEUR qui s'inscrit
    $msg_user = ($statut === 'En attente') 
        ? "Ta demande pour '" . $event['titre'] . "' est en attente de validation par l'organisateur."
        : "Inscription confirmée pour '" . $event['titre'] . "'. À bientôt !";
    
    $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, ?, ?)")
        ->execute(["Inscription", $msg_user, "inscription-evenement", $id_user]);

    // C. Notification pour l'ORGANISATEUR (seulement si besoin de validation ou pour info)
    $msg_orga = "$user_name vient de s'inscrire à ton événement '" . $event['titre'] . "'.";
    if ($statut === 'En attente') {
        $msg_orga = "$user_name attend ta validation pour l'événement '" . $event['titre'] . "'.";
    }

    $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, ?, ?)")
        ->execute(["Nouvelle inscription", $msg_orga, "inscription-evenement", $event['id_organisateur']]);

    echo json_encode(['success' => true, 'message' => 'Inscription réussie !']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur technique : ' . $e->getMessage()]);
}
?>
