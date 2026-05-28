<?php
session_start();
header('Content-Type: application/json');

// Vérification
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
    echo json_encode(['success' => false, 'message' => "Erreur BDD"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'Utilisateur';

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return "À l'instant";
    if ($diff < 3600) return floor($diff / 60) . " min";
    if ($diff < 86400) return "Il y a " . floor($diff / 3600) . "h";
    return "Il y a " . floor($diff / 86400) . "j";
}

function getInitials($prenom, $nom) {
    return strtoupper(mb_substr($prenom, 0, 1) . mb_substr($nom, 0, 1));
}

// ==========================================
// REQUÊTES POST : GESTION DES ACTIONS
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id_forum = $_POST['id_forum'] ?? null;

    if (!$id_forum) {
        echo json_encode(['success' => false, 'message' => 'Sujet introuvable.']);
        exit();
    }

    // 1. Ajouter une réponse
    if ($action === 'add_reply') {
        $contenu = trim($_POST['contenu'] ?? '');
        if (!empty($contenu)) {
            $stmt = $pdo->prepare("INSERT INTO message_forum (contenu, id_auteur, id_forum) VALUES (?, ?, ?)");
            $stmt->execute([$contenu, $user_id, $id_forum]);
            echo json_encode(['success' => true, 'message' => "Votre réponse a été publiée."]);
        } else {
            echo json_encode(['success' => false, 'message' => "Le message ne peut pas être vide."]);
        }
        exit();
    }
    
    // 2. Signaler un message
    if ($action === 'report_msg') {
        $id_msg = $_POST['id_message'];
        $pdo->prepare("UPDATE message_forum SET signale = 1 WHERE id_message = ?")->execute([$id_msg]);
        
        $admins = $pdo->query("SELECT id_utilisateur FROM utilisateur WHERE role = 'Admin'")->fetchAll();
        $insertNotif = $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, 'Alerte', ?)");
        foreach ($admins as $admin) {
            $insertNotif->execute(["Signalement Forum", "Un message a été signalé comme inapproprié.", $admin['id_utilisateur']]);
        }
        echo json_encode(['success' => true, 'message' => "Le message a été signalé."]);
        exit();
    }

    // 3. Fermer le sujet
    if ($action === 'close_topic') {
        $pdo->prepare("UPDATE forum SET est_ferme = 1 WHERE id_forum = ?")->execute([$id_forum]);
        echo json_encode(['success' => true, 'message' => "La discussion a été fermée."]);
        exit();
    }

    // 4. Supprimer le sujet (Admin)
    if ($action === 'delete_topic' && $user_role === 'Admin') {
        $pdo->prepare("DELETE FROM forum WHERE id_forum = ?")->execute([$id_forum]);
        echo json_encode(['success' => true, 'redirect' => 'forums.html']);
        exit();
    }

    // 5. Supprimer un message (Admin)
    if ($action === 'delete_msg' && $user_role === 'Admin') {
        $id_msg = $_POST['id_message'];
        $pdo->prepare("DELETE FROM message_forum WHERE id_message = ?")->execute([$id_msg]);
        echo json_encode(['success' => true, 'message' => "Le message a été supprimé."]);
        exit();
    }
}

// ==========================================
// REQUÊTES GET : CHARGEMENT DU SUJET
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id_forum = $_GET['id'] ?? null;
    if (!$id_forum) {
        echo json_encode(['success' => false, 'redirect' => 'forums.html']);
        exit();
    }

    $stmtTopic = $pdo->prepare("
        SELECT f.*, u.prenom, u.nom, u.photo_profil 
        FROM forum f 
        JOIN utilisateur u ON f.id_createur = u.id_utilisateur 
        WHERE f.id_forum = ?
    ");
    $stmtTopic->execute([$id_forum]);
    $topic = $stmtTopic->fetch(PDO::FETCH_ASSOC);

    if (!$topic) {
        echo json_encode(['success' => false, 'redirect' => 'forums.html']);
        exit();
    }

    // Formatage du topic
    $topic['date_formatee'] = date('d/m/Y à H:i', strtotime($topic['date_creation']));
    $forumCategories = [
        'general' => ['name' => 'Général', 'icon' => '💬'],
        'events'  => ['name' => 'Événements', 'icon' => '📅'],
        'jam'     => ['name' => 'Jam Sessions', 'icon' => '🎸'],
        'tech'    => ['name' => 'Technique & Matériel', 'icon' => '🎧'],
        'collab'  => ['name' => 'Collaborations', 'icon' => '🤝'],
    ];
    $topic['catInfo'] = $forumCategories[$topic['categorie']] ?? $forumCategories['general'];

    // Récupérer les messages
    $stmtMsgs = $pdo->prepare("
        SELECT m.*, u.prenom, u.nom, u.role, u.photo_profil 
        FROM message_forum m 
        JOIN utilisateur u ON m.id_auteur = u.id_utilisateur 
        WHERE m.id_forum = ? 
        ORDER BY m.date_publication ASC
    ");
    $stmtMsgs->execute([$id_forum]);
    $messages = $stmtMsgs->fetchAll(PDO::FETCH_ASSOC);

    // Formatage des messages
    foreach ($messages as &$msg) {
        $msg['time_ago'] = timeAgo($msg['date_publication']);
        $msg['initiales'] = getInitials($msg['prenom'], $msg['nom']);
    }

    echo json_encode([
        'success' => true,
        'current_user' => ['id' => $user_id, 'role' => $user_role],
        'topic' => $topic,
        'messages' => $messages
    ]);
    exit();
}
?>