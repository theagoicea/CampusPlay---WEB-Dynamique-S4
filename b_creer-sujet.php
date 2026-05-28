<?php
session_start();
header('Content-Type: application/json');

// Vérification de sécurité
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit();
}

// Simple vérification en GET pour autoriser l'affichage de la page HTML
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['success' => true]);
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
    echo json_encode(['success' => false, 'error_msg' => "Erreur de connexion BDD"]);
    exit();
}

// Traitement du formulaire en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $categorie = $_POST['categorie'] ?? '';
    $contenu = trim($_POST['contenu'] ?? '');
    $id_createur = $_SESSION['user_id'];

    if (empty($titre) || empty($categorie) || empty($contenu)) {
        echo json_encode(['success' => false, 'error_msg' => "Tous les champs sont obligatoires."]);
        exit();
    } 

    try {
        // On commence une transaction (Sujet + 1er message)
        $pdo->beginTransaction();

        // 1. Création du sujet
        $stmtForum = $pdo->prepare("INSERT INTO forum (titre, categorie, id_createur) VALUES (?, ?, ?)");
        $stmtForum->execute([$titre, $categorie, $id_createur]);
        
        $id_forum = $pdo->lastInsertId();

        // 2. Création du premier message
        $stmtMessage = $pdo->prepare("INSERT INTO message_forum (contenu, id_auteur, id_forum) VALUES (?, ?, ?)");
        $stmtMessage->execute([$contenu, $id_createur, $id_forum]);

        // On valide la transaction
        $pdo->commit();

        echo json_encode(['success' => true, 'redirect' => 'forums.html']);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack(); // En cas d'erreur, on annule l'insertion
        echo json_encode(['success' => false, 'error_msg' => "Une erreur est survenue lors de la création du sujet."]);
        exit();
    }
}
?>