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
    echo json_encode(['success' => false, 'error_msg' => "Erreur de connexion BDD"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mdp_brut = $_POST['mot_de_passe'] ?? '';
    
    // Si la case "membre" est cochée
    $demande_membre = isset($_POST['demande_membre']) ? 1 : 0;
    
    $response = [
        'success' => false,
        'error_msg' => '',
        'success_msg' => ''
    ];

    // Vérification de base des champs vides
    if (empty($nom) || empty($prenom) || empty($email) || empty($mdp_brut)) {
        $response['error_msg'] = "Veuillez remplir tous les champs obligatoires.";
        echo json_encode($response);
        exit();
    }

    $mdp = password_hash($mdp_brut, PASSWORD_DEFAULT);
    $statut_adhesion = $demande_membre ? 'En attente' : 'Non membre';

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = :email");
    $stmt->execute(['email' => $email]);
    
    if ($stmt->fetch()) {
        $response['error_msg'] = "Cet email est déjà utilisé.";
    } else {
        // Créer l'utilisateur
        $insertUser = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, est_membre_asso, statut_adhesion) VALUES (:nom, :prenom, :email, :mdp, :membre, :statut)");
        $insertUser->execute([
            'nom' => $nom, 
            'prenom' => $prenom, 
            'email' => $email, 
            'mdp' => $mdp, 
            'membre' => 0, /* Reste à 0 tant que l'admin ne valide pas */
            'statut' => $statut_adhesion
        ]);

        // Si demande d'adhésion, envoyer une notification aux admins
        if ($demande_membre) {
            $admins = $pdo->query("SELECT id_utilisateur FROM utilisateur WHERE role = 'Admin'")->fetchAll();
            $insertNotif = $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, 'Demande adhésion', ?)");
            
            foreach ($admins as $admin) {
                $insertNotif->execute([
                    "Nouvelle demande d'adhésion", 
                    "$prenom $nom souhaite rejoindre l'association.", 
                    $admin['id_utilisateur']
                ]);
            }
            $response['success_msg'] = "Compte créé ! Votre demande d'adhésion est en attente de validation. Vous allez être redirigé vers la page de connexion.";
        } else {
            $response['success_msg'] = "Compte créé avec succès ! Vous allez être redirigé vers la page de connexion.";
        }
        
        $response['success'] = true;
    }
    
    echo json_encode($response);
    exit();
}
?>