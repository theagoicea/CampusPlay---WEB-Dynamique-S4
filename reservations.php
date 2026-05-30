
<?php
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
        // 1. On récupère d'abord le nom de la ressource pour que la notif soit claire
        $stmtName = $pdo->prepare("SELECT nom FROM ressource WHERE id_resource = ?");
        $stmtName->execute([$res_id]);
        $res_info = $stmtName->fetch();
        $nom_ressource = $res_info ? $res_info['nom'] : "Ressource";

        // 2. On insère la réservation (ton code existant)
        $stmt = $pdo->prepare("INSERT INTO reservation (date_debut, date_fin, id_utilisateur, id_ressource, statut) VALUES (?, ?, ?, ?, 'En attente')");
        $stmt->execute([$dt_start, $dt_end, $user_id, $res_id]);

        // --- NOUVEAU : CRÉATION DE LA NOTIFICATION ---
        
        // A. Notification pour l'utilisateur (Confirmation de sa demande)
        $notif_titre = "Demande envoyée";
        $notif_msg = "Ta demande pour '$nom_ressource' le " . date('d/m', strtotime($date_val)) . " à $start_time est en attente de validation.";
        
        // On choisit le type en fonction de l'onglet pour avoir la bonne couleur
        $notif_type = ($activeTab === 'salles') ? 'rappel-evenement' : 'rappel-materiel';

        $stmtNotifUser = $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, ?, ?)");
        $stmtNotifUser->execute([$notif_titre, $notif_msg, $notif_type, $user_id]);

        // B. Notification pour les Admins (Pour qu'ils sachent qu'ils doivent valider)
        // Selon ton image, les admins doivent être notifiés des demandes.
        $msg_admin = "Nouvelle demande de réservation : '$nom_ressource' par " . ($user_data['prenom'] ?? 'Un utilisateur');
        
        $stmtAdmins = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE role = 'Admin'");
        $stmtAdmins->execute();
        $admins = $stmtAdmins->fetchAll();

        foreach ($admins as $admin) {
            $stmtNotifAdmin = $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, ?, ?)");
            // On utilise 'creation-evenement' car il a le label "Admin" (orange/jaune) dans ton style
            $stmtNotifAdmin->execute(["Validation requise", $msg_admin, "creation-evenement", $admin['id_utilisateur']]);
        }

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
