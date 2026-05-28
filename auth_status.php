<?php
session_start();
header('Content-Type: application/json');

$response = [
    'is_logged' => isset($_SESSION['user_id']),
    'prenom' => $_SESSION['prenom'] ?? null,
    'role' => $_SESSION['role'] ?? null
];

echo json_encode($response);
exit();
?>