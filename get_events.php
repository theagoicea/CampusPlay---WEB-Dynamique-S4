<?php
require_once 'configuration.php';

header('Content-Type: application/json');

try {
    // Modification : ajout de "AND e.date_debut < NOW()" pour filtrer les événements passés
    // Et "ORDER BY e.date_debut DESC" pour afficher les plus récents en premier
    $sql = "SELECT e.*, u.nom as nom_orga, u.prenom as prenom_orga 
            FROM evenement e 
            JOIN utilisateur u ON e.id_organisateur = u.id_utilisateur 
            WHERE e.statut_validation = 'Validé' 
            AND e.date_debut > NOW() 
            ORDER BY e.date_debut DESC";
            
    $stmt = $pdo->query($sql);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($events);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
