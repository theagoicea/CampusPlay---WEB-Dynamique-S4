<?php
session_start();
require_once 'configuration.php'; // Ou db.php selon ton nom
header('Content-Type: application/json');

$response = [
    'is_logged' => false,
    'prenom' => '',
    'nom' => '',
    'role' => 'Visiteur'
];

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT prenom, nom, role FROM utilisateur WHERE id_utilisateur = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        $response = [
            'is_logged' => true,
            'prenom' => $user['prenom'],
            'nom' => $user['nom'],
            'role' => $user['role']
        ];
    }
}
echo json_encode($response);
