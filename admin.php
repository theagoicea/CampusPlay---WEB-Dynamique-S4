<?php
session_start();
require_once 'configuration.php'; 

// 1. SÉCURITÉ : Vérifier Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: accueil.html"); 
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. TRAITEMENT DES ACTIONS (Approuver / Refuser)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = $_POST['item_id'];
    $type = $_POST['item_type'];
    $decision_inscr = ($_POST['action'] === 'approuver') ? 'Confirmé' : 'Refusé';
    $decision_resa  = ($_POST['action'] === 'approuver') ? 'Approuvée' : 'Refusée';

    try {
        if ($type === 'inscription') {
            $stmt = $pdo->prepare("UPDATE inscription SET statut_inscription = ?, id_validateur = ?, date_decision = NOW() WHERE id_utilisateur = ? AND id_evenement = ?");
            $parts = explode('-', $id);
            $stmt->execute([$decision_inscr, $user_id, $parts[0], $parts[1]]);
        } elseif ($type === 'reservation') {
            $stmt = $pdo->prepare("UPDATE reservation SET statut = ?, id_validateur = ?, date_decision = NOW() WHERE id_reservation = ?");
            $stmt->execute([$decision_resa, $user_id, $id]);
        }
    } catch (Exception $e) {
        $error_msg = $e->getMessage();
    }
}

// 3. STATISTIQUES (KPIs)
$stats = [
    'membres' => $pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn(),
    'inscriptions' => $pdo->query("SELECT COUNT(*) FROM inscription")->fetchColumn(),
    'en_attente' => $pdo->query("SELECT (SELECT COUNT(*) FROM inscription WHERE statut_inscription = 'En attente') + (SELECT COUNT(*) FROM reservation WHERE statut = 'En attente')")->fetchColumn()
];

// 4. DEMANDES EN ATTENTE
$requests = [];

// Inscriptions
$stmtInscr = $pdo->query("SELECT i.*, u.nom, u.prenom, e.titre FROM inscription i 
                          JOIN utilisateur u ON i.id_utilisateur = u.id_utilisateur 
                          JOIN evenement e ON i.id_evenement = e.id_evenement 
                          WHERE i.statut_inscription = 'En attente'");
while ($row = $stmtInscr->fetch()) {
    $requests[] = [
        'id' => $row['id_utilisateur'].'-'.$row['id_evenement'],
        'title' => "Inscription - " . $row['titre'],
        'user' => $row['prenom'] . " " . $row['nom'],
        'type' => 'inscription',
        'detail' => "Demande de participation à l'événement : " . $row['titre']
    ];
}

// Réservations
$stmtResa = $pdo->query("SELECT r.*, u.nom, u.prenom, res.nom as res_nom FROM reservation r 
                         JOIN utilisateur u ON r.id_utilisateur = u.id_utilisateur 
                         JOIN ressource res ON r.id_ressource = res.id_resource 
                         WHERE r.statut = 'En attente'");
while ($row = $stmtResa->fetch()) {
    $requests[] = [
        'id' => $row['id_reservation'],
        'title' => "Réservation - " . $row['res_nom'],
        'user' => $row['prenom'] . " " . $row['nom'],
        'type' => 'reservation',
        'detail' => "Réservation de " . $row['res_nom'] . " pour le " . date('d/m à H:i', strtotime($row['date_debut']))
    ];
}

// 5. INFOS TOPBAR
$stmtUser = $pdo->prepare("SELECT prenom, nom FROM utilisateur WHERE id_utilisateur = ?");
$stmtUser->execute([$user_id]);
$u_data = $stmtUser->fetch();
$initiales = strtoupper(substr($u_data['prenom'] ?? 'J', 0, 1) . substr($u_data['nom'] ?? 'M', 0, 1));

include 'admin_view.php';
