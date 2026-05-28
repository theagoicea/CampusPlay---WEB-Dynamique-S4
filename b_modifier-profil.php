<?php
session_start();
header('Content-Type: application/json');

// Vérification si l'utilisateur est connecté
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
    echo json_encode(['success' => false, 'error' => 'db_error', 'message' => "Erreur BDD"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. REQUÊTE GET : Récupérer les infos pour pré-remplir le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmtUser = $pdo->prepare("SELECT nom, prenom, email, est_membre_asso, statut_adhesion, photo_profil FROM utilisateur WHERE id_utilisateur = ?");
    $stmtUser->execute([$user_id]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'user' => $user]);
    exit();
}

// 2. REQUÊTES POST : Gérer les actions (Mise à jour, Adhésion, Suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action A : Supprimer le compte
    if ($action === 'delete_account') {
        $pdo->prepare("DELETE FROM utilisateur WHERE id_utilisateur = ?")->execute([$user_id]);
        session_unset();
        session_destroy();
        echo json_encode(['success' => true, 'redirect' => 'authentification.html']);
        exit();
    }
    
    // Action B : Demander l'adhésion
    if ($action === 'request_membership') {
        $pdo->prepare("UPDATE utilisateur SET statut_adhesion = 'En attente' WHERE id_utilisateur = ?")->execute([$user_id]);
        
        $admins = $pdo->query("SELECT id_utilisateur FROM utilisateur WHERE role = 'Admin'")->fetchAll();
        $insertNotif = $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, 'Demande adhésion', ?)");
        
        foreach ($admins as $admin) {
            $insertNotif->execute(["Nouvelle demande d'adhésion", "Un utilisateur souhaite rejoindre l'association.", $admin['id_utilisateur']]);
        }
        
        echo json_encode(['success' => true, 'message' => "Votre demande d'adhésion a été envoyée aux administrateurs."]);
        exit();
    }
    
    // Action C : Mettre à jour le profil
    if ($action === 'update_profile') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mdp = $_POST['mot_de_passe'] ?? '';

        // Vérifier si l'email existe
        $stmtCheck = $pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = ? AND id_utilisateur != ?");
        $stmtCheck->execute([$email, $user_id]);
        
        if ($stmtCheck->fetch()) {
            echo json_encode(['success' => false, 'error_msg' => "Cet email est déjà utilisé par un autre compte."]);
            exit();
        }

        $sql_update = "UPDATE utilisateur SET nom = ?, prenom = ?, email = ?";
        $params = [$nom, $prenom, $email];

        if (!empty($mdp)) {
            $sql_update .= ", mot_de_passe = ?";
            $params[] = password_hash($mdp, PASSWORD_DEFAULT);
        }

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            if (!is_dir('uploads')) mkdir('uploads', 0777, true);
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $nom_photo = "user_" . $user_id . "_" . time() . "." . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $nom_photo);
            
            $sql_update .= ", photo_profil = ?";
            $params[] = $nom_photo;
        }

        $sql_update .= " WHERE id_utilisateur = ?";
        $params[] = $user_id;

        $pdo->prepare($sql_update)->execute($params);
        $_SESSION['prenom'] = $prenom; // Mettre à jour la session
        
        echo json_encode(['success' => true, 'message' => "Profil mis à jour avec succès !"]);
        exit();
    }
}
?>