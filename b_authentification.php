<?php
session_start();
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'campusmelody';
$username = 'root';
$password = 'root'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => "Erreur de connexion BDD"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $mdp = $_POST['mot_de_passe'] ?? '';

    $response = [
        'success' => false,
        'email_error' => '',
        'password_error' => ''
    ];

    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        $response['email_error'] = "Compte non-existant";
    } else {
        if ($mdp !== $user['mot_de_passe'] && !password_verify($mdp, $user['mot_de_passe'])) {
            $response['password_error'] = "Mot de passe incorrect";
        } else {
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['prenom'] = $user['prenom'];
            
            $response['success'] = true;
        }
    }
    
    echo json_encode($response);
    exit();
}
?>