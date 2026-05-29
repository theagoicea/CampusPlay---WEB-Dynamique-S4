<?php
// On désactive l'affichage des erreurs système pour ne pas polluer le JSON
error_reporting(0); 
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

// 1. Vérification connexion
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Vous devez être connecté pour réserver.']);
    exit;
}

// 2. Connexion BDD (Identifiants MAMP)
$host = 'localhost';
$dbname = 'campusmelody';
$username = 'root';
$password = 'root'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id_user = $_SESSION['user_id'];
    $id_event = isset($_POST['id_evenement']) ? intval($_POST['id_evenement']) : 0;

    if ($id_event <= 0) {
        echo json_encode(['success' => false, 'error' => 'Événement invalide.']);
        exit;
    }

    // 3. Vérifier si déjà inscrit
    $check = $pdo->prepare("SELECT COUNT(*) FROM inscription WHERE id_utilisateur = ? AND id_evenement = ?");
    $check->execute([$id_user, $id_event]);
    if ($check->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'Vous êtes déjà inscrit à cet événement.']);
        exit;
    }

    // 4. Vérifier les places
    $stmt = $pdo->prepare("SELECT capacite_max, besoin_validation_inscription FROM evenement WHERE id_evenement = ?");
    $stmt->execute([$id_event]);
    $event = $stmt->fetch();

    $count = $pdo->prepare("SELECT COUNT(*) FROM inscription WHERE id_evenement = ? AND statut_inscription != 'Annulé'");
    $count->execute([$id_event]);
    if ($count->fetchColumn() >= $event['capacite_max']) {
        echo json_encode(['success' => false, 'error' => 'Événement complet.']);
        exit;
    }

    // 5. Inscription
    $statut = ($event['besoin_validation_inscription'] == 1) ? 'En attente' : 'Confirmé';
    $insert = $pdo->prepare("INSERT INTO inscription (id_utilisateur, id_evenement, statut_inscription) VALUES (?, ?, ?)");
    $insert->execute([$id_user, $id_event, $statut]);

    echo json_encode(['success' => true, 'message' => 'Inscription réussie !']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur technique : ' . $e->getMessage()]);
}
?>
