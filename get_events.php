<?php
require_once 'configuration.php';

header('Content-Type: application/json');

try {
    // On fait une JOINTURE pour avoir le nom de l'organisateur en même temps
    $sql = "SELECT e.*, u.nom as nom_orga, u.prenom as prenom_orga 
            FROM evenement e 
            JOIN utilisateur u ON e.id_organisateur = u.id_utilisateur 
            WHERE e.statut_validation = 'Validé' 
            ORDER BY e.date_debut ASC";
            
    $stmt = $pdo->query($sql);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($events);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
