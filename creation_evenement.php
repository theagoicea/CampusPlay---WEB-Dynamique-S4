<?php
session_start();
require_once 'configuration.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: authentification.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$message_success = "";
$message_error = "";

// Récupérer les salles
$stmtSalles = $pdo->query("SELECT id_resource, nom FROM ressource WHERE type_ressource IN ('Studio', 'Salle')");
$salles = $stmtSalles->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_name = null;
    if (isset($_FILES['image_event']) && $_FILES['image_event']['error'] === 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
        $image_name = time() . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($_FILES['image_event']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['image_event']['tmp_name'], $upload_dir . $image_name);
    }

    try {
        $pdo->beginTransaction();
        $sql = "INSERT INTO evenement (titre, description, type_evenement, lieu, date_debut, date_fin, capacite_max, image_url, est_reserve_membres, besoin_validation_inscription, id_organisateur, statut_validation) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'En attente')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['titre'], $_POST['description'], $_POST['categorie'], $_POST['lieu'], $_POST['date_debut'], $_POST['date_fin'], $_POST['capacite'], $image_name, isset($_POST['reserve_membres']) ? 1 : 0, isset($_POST['besoin_validation']) ? 1 : 0, $user_id]);

        $admins = $pdo->query("SELECT id_utilisateur FROM utilisateur WHERE role = 'Admin'")->fetchAll();
        $notif = $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, ?, ?)");
        foreach ($admins as $a) { $notif->execute(["Nouvel événement", "L'événement '".$_POST['titre']."' attend validation.", "création-événement", $a['id_utilisateur']]); }

        $pdo->commit();
        $message_success = "Événement envoyé en validation !";
    } catch (Exception $e) { $pdo->rollBack(); $message_error = "Erreur : " . $e->getMessage(); }
}

$stmtUser = $pdo->prepare("SELECT prenom, nom FROM utilisateur WHERE id_utilisateur = ?");
$stmtUser->execute([$user_id]);
$u = $stmtUser->fetch();
$initiales = strtoupper(substr($u['prenom']??'J',0,1).substr($u['nom']??'M',0,1));

include 'creation_evenement_view.php';
