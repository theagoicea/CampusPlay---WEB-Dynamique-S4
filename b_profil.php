<?php
session_start();
header('Content-Type: application/json');

// Vérification si connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit();
}

$host = 'localhost';
$dbname = 'campusmelody';
$username = 'root';
$password = 'root'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'db_error', 'message' => $e->getMessage()]);
    exit();
}

$user_id = $_SESSION['user_id'];
$response = ['success' => true];

// 1. Récupérer les infos de l'utilisateur
$stmtUser = $pdo->prepare("SELECT prenom, nom, email, est_membre_asso, photo_profil FROM utilisateur WHERE id_utilisateur = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

$user['initiales'] = strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1));
$response['user'] = $user;

// 2. Récupérer les réservations à venir
$stmtResa = $pdo->prepare("
    SELECT r.date_debut, r.date_fin, r.statut, res.nom, res.type_ressource 
    FROM reservation r 
    JOIN ressource res ON r.id_ressource = res.id_resource 
    WHERE r.id_utilisateur = ? AND r.date_debut >= NOW() 
    ORDER BY r.date_debut ASC
");
$stmtResa->execute([$user_id]);
$reservations = $stmtResa->fetchAll(PDO::FETCH_ASSOC);

// Formatage des dates pour JS
foreach ($reservations as &$res) {
    $res['date_formatee'] = date('d/m \à H:i', strtotime($res['date_debut']));
}
$response['reservations'] = $reservations;

// 3. Récupérer les événements à venir (inscriptions)
$stmtEvents = $pdo->prepare("
    SELECT e.titre, e.date_debut, e.type_evenement, i.statut_inscription 
    FROM inscription i 
    JOIN evenement e ON i.id_evenement = e.id_evenement 
    WHERE i.id_utilisateur = ? AND e.date_debut >= NOW() 
    ORDER BY e.date_debut ASC
");
$stmtEvents->execute([$user_id]);
$myEvents = $stmtEvents->fetchAll(PDO::FETCH_ASSOC);

foreach ($myEvents as &$ev) {
    $ev['date_formatee'] = date('d M.', strtotime($ev['date_debut']));
}
$response['myEvents'] = $myEvents;

// 4. Récupérer les événements passés
$stmtPastEvents = $pdo->prepare("
    SELECT e.titre, e.date_debut, e.type_evenement, i.statut_inscription 
    FROM inscription i 
    JOIN evenement e ON i.id_evenement = e.id_evenement 
    WHERE i.id_utilisateur = ? AND e.date_debut < NOW() 
    ORDER BY e.date_debut DESC
");
$stmtPastEvents->execute([$user_id]);
$pastEvents = $stmtPastEvents->fetchAll(PDO::FETCH_ASSOC);

foreach ($pastEvents as &$ev) {
    $ev['date_formatee'] = date('d M. Y', strtotime($ev['date_debut']));
}
$response['pastEvents'] = $pastEvents;

echo json_encode($response);
exit();
?>