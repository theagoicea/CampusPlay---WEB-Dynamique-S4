<?php
date_default_timezone_set('Europe/Paris');
$host = 'localhost'; 
$db   = 'campusmelody'; 
$user = 'root';
$pass = 'root'; 

try {
    // On essaie la connexion standard
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Si ça rate, on tente avec le port 8889 (le port classique de MAMP)
    try {
        $pdo = new PDO("mysql:host=$host;port=8889;dbname=$db;charset=utf8", $user, $pass);
    } catch (PDOException $e2) {
        header('Content-Type: application/json');
        echo json_encode(['error' => "Erreur de connexion : " . $e2->getMessage()]);
        exit;
    }
}
?>
