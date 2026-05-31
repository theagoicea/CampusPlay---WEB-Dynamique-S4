<?php
session_start();
require_once 'configuration.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: authentification.html"); // CORRECTION ICI
    exit();
}

$user_id = $_SESSION['user_id'];

$stmtUser = $pdo->prepare("SELECT prenom, nom FROM utilisateur WHERE id_utilisateur = ?");
$stmtUser->execute([$user_id]);
$user_data = $stmtUser->fetch();

$initiales = "??";
if ($user_data) {
    $p = !empty($user_data['prenom']) ? substr($user_data['prenom'], 0, 1) : "";
    $n = !empty($user_data['nom']) ? substr($user_data['nom'], 0, 1) : "";
    $initiales = strtoupper($p . $n);
}

$message_success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reserver') {
    $res_id = $_POST['id_ressource']; 
    $date_val = $_POST['selected_date']; 
    $slot_time = $_POST['slot']; 
    
    list($start_time, $end_time) = explode(' - ', $slot_time);
    $dt_start = $date_val . ' ' . $start_time . ':00';
    $dt_end = $date_val . ' ' . $end_time . ':00';

    try {
        $stmtName = $pdo->prepare("SELECT nom FROM ressource WHERE id_resource = ?");
        $stmtName->execute([$res_id]);
        $res_info = $stmtName->fetch();
        $nom_ressource = $res_info ? $res_info['nom'] : "Ressource";

        $stmt = $pdo->prepare("INSERT INTO reservation (date_debut, date_fin, id_utilisateur, id_ressource, statut) VALUES (?, ?, ?, ?, 'En attente')");
        $stmt->execute([$dt_start, $dt_end, $user_id, $res_id]);

        $activeTab = $_GET['tab'] ?? 'salles';
        $notif_titre = "Demande envoyée";
        $notif_msg = "Ta demande pour '$nom_ressource' le " . date('d/m', strtotime($date_val)) . " à $start_time est en attente de validation.";
        $notif_type = ($activeTab === 'salles') ? 'rappel-evenement' : 'rappel-materiel';

        $stmtNotifUser = $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, ?, ?)");
        $stmtNotifUser->execute([$notif_titre, $notif_msg, $notif_type, $user_id]);

        $msg_admin = "Nouvelle demande de réservation : '$nom_ressource' par " . ($user_data['prenom'] ?? 'Un utilisateur');
        
        $stmtAdmins = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE role = 'Admin'");
        $stmtAdmins->execute();
        $admins = $stmtAdmins->fetchAll();

        foreach ($admins as $admin) {
            $stmtNotifAdmin = $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, ?, ?)");
            $stmtNotifAdmin->execute(["Validation requise", $msg_admin, "creation-evenement", $admin['id_utilisateur']]);
        }

        $message_success = "Ta demande de réservation a bien été envoyée !";
    } catch (Exception $e) {
        $message_success = "Erreur lors de la réservation : " . $e->getMessage();
    }
}

$activeTab = $_GET['tab'] ?? 'salles'; 
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$selectedResourceId = $_GET['id_ressource'] ?? null;

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

$type_filter = ($activeTab === 'salles') ? "('Studio', 'Salle')" : "('Instrument', 'Matériel')";

$stmtRes = $pdo->prepare("SELECT id_resource, nom, type_ressource FROM ressource WHERE type_ressource IN $type_filter AND statut_actuel = 'Disponible'");
$stmtRes->execute();
$resources = $stmtRes->fetchAll();

$slots = ['09:00 - 11:00', '11:00 - 13:00', '14:00 - 16:00', '16:00 - 18:00'];
$unavailableSlots = [];

if ($selectedResourceId) {
    $stmtCheck = $pdo->prepare("SELECT date_debut FROM reservation WHERE id_ressource = ? AND DATE(date_debut) = ? AND statut != 'Refusée'");
    $stmtCheck->execute([$selectedResourceId, $selectedDate]);
    while ($row = $stmtCheck->fetch()) {
        $unavailableSlots[] = date('H:i', strtotime($row['date_debut']));
    }
}

require_once 'reservations_view.php';
?>