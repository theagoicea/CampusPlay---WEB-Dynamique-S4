<?php
session_start();
require_once 'configuration.php'; // Connexion BDD

// 1. Sécurité : redirection si non connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: authentification.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 2. Récupérer les infos utilisateur pour la TopBar
$stmtUser = $pdo->prepare("SELECT prenom, nom FROM utilisateur WHERE id_utilisateur = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();
$initiales = strtoupper(substr($user['prenom'] ?? 'J', 0, 1) . substr($user['nom'] ?? 'M', 0, 1));

// 3. Traitement du formulaire de réservation (Action POST)
$message_success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reserver') {
    $res_id = $_POST['id_ressource']; 
    $date_val = $_POST['selected_date']; 
    $slot_time = $_POST['slot']; 
    list($start_time, $end_time) = explode(' - ', $slot_time);
    $dt_start = $date_val . ' ' . $start_time . ':00';
    $dt_end = $date_val . ' ' . $end_time . ':00';

    try {
        $stmt = $pdo->prepare("INSERT INTO reservation (date_debut, date_fin, id_utilisateur, id_ressource, statut) VALUES (?, ?, ?, ?, 'En attente')");
        $stmt->execute([$dt_start, $dt_end, $user_id, $res_id]);
        $message_success = "Réservation enregistrée avec succès !";
    } catch (Exception $e) {
        $message_success = "Erreur : " . $e->getMessage();
    }
}

// 4. Gestion des filtres et calcul des 10 prochains jours (Action GET)
$activeTab = $_GET['tab'] ?? 'salles'; 
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedResourceId = $_GET['id_ressource'] ?? null;

$dates = [];
for ($i = 0; $i < 10; $i++) {
    $d = new DateTime(); $d->modify("+$i day");
    $dates[] = [
        'full' => $d->format('Y-m-d'),
        'dayName' => ($i === 0 ? "Auj." : ($i === 1 ? "Dem." : $d->format('D'))),
        'dayNum' => $d->format('d'),
        'month' => $d->format('M')
    ];
}

// 5. Récupération des ressources selon l'onglet choisi
$type_filter = ($activeTab === 'salles') ? "('Studio', 'Salle')" : "('Instrument', 'Matériel')";
$stmtRes = $pdo->prepare("SELECT id_resource, nom FROM ressource WHERE type_ressource IN $type_filter AND statut_actuel = 'Disponible'");
$stmtRes->execute();
$resources = $stmtRes->fetchAll();

// 6. Vérification des créneaux si une ressource est sélectionnée
$slots = ['09:00 - 11:00', '11:00 - 13:00', '14:00 - 16:00', '16:00 - 18:00'];
$unavailableSlots = [];
if ($selectedResourceId) {
    $stmtCheck = $pdo->prepare("SELECT date_debut FROM reservation WHERE id_ressource = ? AND DATE(date_debut) = ? AND statut != 'Refusée'");
    $stmtCheck->execute([$selectedResourceId, $selectedDate]);
    while ($row = $stmtCheck->fetch()) {
        $unavailableSlots[] = date('H:i', strtotime($row['date_debut']));
    }
}

// 7. CHARGEMENT DE LA VUE (Le HTML)
require_once 'reservations_view.php';
