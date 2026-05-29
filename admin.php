<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'configuration.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: accueil.html"); 
    exit();
}

$user_id = $_SESSION['user_id'];

// --- TRAITEMENT DES ACTIONS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = $_POST['item_id'];
    $type = $_POST['item_type'];
    
    try {
        if ($type === 'creation') {
            $decision = ($_POST['action'] === 'approuver') ? 'Validé' : 'Refusé';
            $stmt = $pdo->prepare("UPDATE evenement SET statut_validation = ?, id_validateur = ?, date_decision = NOW() WHERE id_evenement = ?");
            $stmt->execute([$decision, $user_id, $id]);
        } 
        elseif ($type === 'inscription') {
            $decision = ($_POST['action'] === 'approuver') ? 'Confirmé' : 'Refusé';
            $parts = explode('-', $id);
            $stmt = $pdo->prepare("UPDATE inscription SET statut_inscription = ?, id_validateur = ?, date_decision = NOW() WHERE id_utilisateur = ? AND id_evenement = ?");
            $stmt->execute([$decision, $user_id, $parts[0], $parts[1]]);
        } 
        elseif ($type === 'reservation') {
            $decision_resa = ($_POST['action'] === 'approuver') ? 'Approuvée' : 'Refusée';
            $stmt = $pdo->prepare("UPDATE reservation SET statut = ?, id_validateur = ?, date_decision = NOW() WHERE id_reservation = ?");
            $stmt->execute([$decision_resa, $user_id, $id]);
        }
    } catch (Exception $e) { die("Erreur action : " . $e->getMessage()); }
}

$requests = [];

try {
    // 1. Créations d'événements
    $stmtEv = $pdo->query("SELECT e.*, u.nom, u.prenom FROM evenement e 
                           JOIN utilisateur u ON e.id_organisateur = u.id_utilisateur 
                           WHERE e.statut_validation = 'En attente'");
    while ($row = $stmtEv->fetch()) {
        $date_info = date('d/m', strtotime($row['date_debut'])) . " à " . date('H:i', strtotime($row['date_debut']));
        $requests[] = [
            'id' => $row['id_evenement'],
            'title' => "Création : " . $row['titre'],
            'user' => $row['prenom'] . " " . $row['nom'],
            'type' => 'creation',
            'detail' => "Projet " . $row['type_evenement'] . " prévu le " . $date_info . " à " . $row['lieu']
        ];
    }

    // 2. Inscriptions 
    $stmtInscr = $pdo->query("SELECT i.*, u.nom, u.prenom, e.titre, e.date_debut FROM inscription i 
                              JOIN utilisateur u ON i.id_utilisateur = u.id_utilisateur 
                              JOIN evenement e ON i.id_evenement = e.id_evenement 
                              WHERE i.statut_inscription = 'En attente'");
    while ($row = $stmtInscr->fetch()) {
        $date_info = date('d/m', strtotime($row['date_debut'])) . " à " . date('H:i', strtotime($row['date_debut']));
        $requests[] = [
            'id' => $row['id_utilisateur'].'-'.$row['id_evenement'],
            'title' => "Inscription : " . $row['titre'],
            'user' => $row['prenom'] . " " . $row['nom'],
            'type' => 'inscription',
            'detail' => "Demande pour l'événement du " . $date_info
        ];
    }

    // 3. Réservations
    $stmtResa = $pdo->query("SELECT r.*, u.nom, u.prenom, res.nom as res_nom FROM reservation r 
                             JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur 
                             JOIN ressource res ON r.id_ressource = res.id_resource 
                             WHERE r.statut = 'En attente'");
    while ($row = $stmtResa->fetch()) {
        $date_info = date('d/m', strtotime($row['date_debut'])) . " de " . date('H:i', strtotime($row['date_debut'])) . " à " . date('H:i', strtotime($row['date_fin']));
        $requests[] = [
            'id' => $row['id_reservation'],
            'title' => "Réservation : " . $row['res_nom'],
            'user' => $row['prenom'] . " " . $row['nom'],
            'type' => 'reservation',
            'detail' => "Créneau demandé : " . $date_info
        ];
    }

    $stats = [
        'membres' => $pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn(),
        'inscriptions' => $pdo->query("SELECT COUNT(*) FROM inscription")->fetchColumn(),
        'en_attente' => count($requests)
    ];

    $stmtUser = $pdo->prepare("SELECT prenom, nom FROM utilisateur WHERE id_utilisateur = ?");
    $stmtUser->execute([$user_id]);
    $u_data = $stmtUser->fetch();
    $initiales = strtoupper(substr($u_data['prenom']??'J',0,1).substr($u_data['nom']??'M',0,1));

} catch (PDOException $e) { die("Erreur SQL : " . $e->getMessage()); }

include 'admin_view.php';
