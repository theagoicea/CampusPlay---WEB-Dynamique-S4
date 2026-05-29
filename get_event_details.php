<?php
require_once 'configuration.php';
header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception("ID manquant");
    }

    $id = intval($_GET['id']);

    // Récupérer l'événement + Infos organisateur
    $sql = "SELECT e.*, u.nom as nom_orga, u.prenom as prenom_orga 
            FROM evenement e 
            JOIN utilisateur u ON e.id_organisateur = u.id_utilisateur 
            WHERE e.id_evenement = :id";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        throw new Exception("Événement introuvable");
    }

    // Calcul des places restantes (Capacité - Inscriptions confirmées)
    $sql_count = "SELECT COUNT(*) FROM inscription WHERE id_evenement = :id AND statut_inscription = 'Confirmé'";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute(['id' => $id]);
    $inscrits = $stmt_count->fetchColumn();

    $event['places_restantes'] = max(0, $event['capacite_max'] - $inscrits);
    
    echo json_encode($event);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
