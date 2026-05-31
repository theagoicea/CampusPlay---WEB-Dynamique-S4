<?php
session_start();
header('Content-Type: application/json');

require_once 'configuration.php'; // On utilise ton fichier centralisé

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mdp_brut = $_POST['mot_de_passe'] ?? '';
        
        $demande_membre = isset($_POST['demande_membre']) ? 1 : 0;
        
        $response = ['success' => false, 'error_msg' => '', 'success_msg' => ''];

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
            // 1. Créer l'utilisateur
            $insertUser = $pdo->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, est_membre_asso, statut_adhesion) VALUES (:nom, :prenom, :email, :mdp, :membre, :statut)");
            $insertUser->execute([
                'nom' => $nom, 
                'prenom' => $prenom, 
                'email' => $email, 
                'mdp' => $mdp, 
                'membre' => 0, 
                'statut' => $statut_adhesion
            ]);

            $new_user_id = $pdo->lastInsertId();

            // --- NOUVEAU : NOTIFICATIONS ---

            if ($demande_membre) {
                // A. Notification de bienvenue "en attente" pour l'UTILISATEUR (Rose)
                $msg_user = "Bienvenue ! Ton compte est créé. Ton adhésion à l'association est en cours de validation.";
                $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, 'adhesion-association', ?)")
                    ->execute(["Bienvenue !", $msg_user, $new_user_id]);

                // B. Notification pour TOUS LES ADMINS (Rose)
                $msg_admin = "$prenom $nom souhaite rejoindre l'association.";
                $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) 
                               SELECT 'Nouvelle adhésion', ?, 'adhesion-association', id_utilisateur FROM utilisateur WHERE role = 'Admin'")
                    ->execute([$msg_admin]);

                $response['success_msg'] = "Compte créé ! Votre demande d'adhésion est en attente de validation.";
            } else {
                // Bienvenue simple (Bleu) si pas de demande membre
                $pdo->prepare("INSERT INTO notification (titre, message, type_notification, id_destinataire) VALUES (?, ?, 'rappel-evenement', ?)")
                    ->execute(["Bienvenue !", "Bienvenue sur Campus Melody ! Ton compte a été créé avec succès.", $new_user_id]);
                
                $response['success_msg'] = "Compte créé avec succès !";
            }
            
            $response['success'] = true;
        }
        echo json_encode($response);
        exit();
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error_msg' => "Erreur technique : " . $e->getMessage()]);
}
?>