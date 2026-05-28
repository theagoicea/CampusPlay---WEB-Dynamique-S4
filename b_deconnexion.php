<?php
session_start();
session_unset();    // Vide toutes les variables de session
session_destroy();  // Détruit la session

header('Content-Type: application/json');
echo json_encode(['success' => true]);
exit();
?>