<?php
session_start();
session_unset();    // Vide toutes les variables de session
session_destroy();  // Détruit la session

// Redirige vers la page d'authentification
header("Location: Authentification.php");
exit();
?>
