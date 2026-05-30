<?php
session_start();
require_once 'configuration.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: b_authentification.php");
    exit();
}

$user_id = $_SESSION['user_id'];
require_once 'notifications_rappel.php'; 


// Action : Supprimer une notification
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id_notif'])) {
    $stmt = $pdo->prepare("DELETE FROM notification WHERE id_notification = ? AND id_destinataire = ?");
    $stmt->execute([$_POST['id_notif'], $user_id]);
    header("Location: notifications.php"); // Refresh pour éviter de renvoyer le formulaire
    exit();
}

// Récupération des notifications
$stmt = $pdo->prepare("SELECT * FROM notification WHERE id_destinataire = ? ORDER BY date_envoi DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Infos utilisateur pour la TopBar
$stmtUser = $pdo->prepare("SELECT prenom, nom FROM utilisateur WHERE id_utilisateur = ?");
$stmtUser->execute([$user_id]);
$u = $stmtUser->fetch();
$initiales = strtoupper(substr($u['prenom'], 0, 1) . substr($u['nom'], 0, 1));

require_once 'notifications_view.php';
