<?php
session_start();
// Destruimos toda la información de la sesión
session_destroy();

// Devolvemos un JSON confirmando que se cerró
header('Content-Type: application/json');
echo json_encode(["success" => true, "mensaje" => "Sesión cerrada"]);
?>