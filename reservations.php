<?php
// 1. Activation des erreurs pour le développement (à enlever en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'configuration.php'; 

// 2. Sécurité : redirection vers l'authentification si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: b_authentification.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// 3. Récupérer les informations de l'utilisateur pour la TopBar (Initiales)
$stmtUser = $pdo->prepare("SELECT prenom, nom FROM utilisateur WHERE id_utilisateur = ?");
$stmtUser->execute([$user_id]);
$user_data = $stmtUser->fetch();

// On crée les initiales (ex: "Julien Martin" -> "JM")
$initiales = "??";
if ($user_data) {
    $p = !empty($user_data['prenom']) ? substr($user_data['prenom'], 0, 1) : "";
    $n = !empty($user_data['nom']) ? substr($user_data['nom'], 0, 1) : "";
    $initiales = strtoupper($p . $n);
}

// 4. Traitement du formulaire de réservation (Action POST)
$message_success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reserver') {
    $res_id = $_POST['id_ressource']; 
    $date_val = $_POST['selected_date']; 
    $slot_time = $_POST['slot']; 
    
    // On sépare "09:00 - 11:00" en heure de début et heure de fin
    list($start_time, $end_time) = explode(' - ', $slot_time);
    $dt_start = $date_val . ' ' . $start_time . ':00';
    $dt_end = $date_val . ' ' . $end_time . ':00';

    try {
        // id_ressource (avec deux s) est le nom de la colonne dans ta table 'reservation'
        $stmt = $pdo->prepare("INSERT INTO reservation (date_debut, date_fin, id_utilisateur, id_ressource, statut) VALUES (?, ?, ?, ?, 'En attente')");
        $stmt->execute([$dt_start, $dt_end, $user_id, $res_id]);
        $message_success = "Ta demande de réservation a bien été envoyée !";
    } catch (Exception $e) {
        $message_success = "Erreur lors de la réservation : " . $e->getMessage();
    }
}

// 5. Gestion des filtres et calcul des 30 prochains jours (Action GET)
$activeTab = $_GET['tab'] ?? 'salles'; 
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedResourceId = $_GET['id_ressource'] ?? null;

// Génération dynamique des 10 prochains jours pour le sélecteur horizontal
$dates = [];
for ($i = 0; $i < 30; $i++) {
    $d = new DateTime();
    $d->modify("+$i day");
	
    $daysFR = ['Mon'=>'Lun','Tue'=>'Mar','Wed'=>'Mer','Thu'=>'Jeu','Fri'=>'Ven','Sat'=>'Sam','Sun'=>'Dim'];
    $dayNameStr = $d->format('D');
    $dayLabel = ($i === 0 ? "Auj." : ($i === 1 ? "Dem." : ($daysFR[$dayNameStr] ?? $dayNameStr)));

    $dates[] = [
        'full' => $d->format('Y-m-d'),
        'dayName' => $dayLabel,
        'dayNum' => $d->format('d'),
        'month' => $d->format('M')
    ];
}

// 6. Récupération des ressources selon l'onglet choisi (Salles ou Matériel)
$type_filter = ($activeTab === 'salles') ? "('Studio', 'Salle')" : "('Instrument', 'Matériel')";

// Note : id_resource (un seul s) est le nom dans ta table 'ressource'
$stmtRes = $pdo->prepare("SELECT id_resource, nom, type_ressource FROM ressource WHERE type_ressource IN $type_filter AND statut_actuel = 'Disponible'");
$stmtRes->execute();
$resources = $stmtRes->fetchAll();

// 7. Vérification des créneaux déjà réservés en BDD
$slots = ['09:00 - 11:00', '11:00 - 13:00', '14:00 - 16:00', '16:00 - 18:00'];
$unavailableSlots = [];

if ($selectedResourceId) {
    // On cherche les réservations existantes pour cette ressource précise à cette date précise
    $stmtCheck = $pdo->prepare("SELECT date_debut FROM reservation WHERE id_ressource = ? AND DATE(date_debut) = ? AND statut != 'Refusée'");
    $stmtCheck->execute([$selectedResourceId, $selectedDate]);
    while ($row = $stmtCheck->fetch()) {
        // On récupère juste l'heure (ex: "14:00") pour comparer avec nos créneaux
        $unavailableSlots[] = date('H:i', strtotime($row['date_debut']));
    }
}

// 8. APPEL DE LA VUE (Le fichier HTML)
require_once 'reservations_view.php';
