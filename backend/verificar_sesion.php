<?php
session_start();
header('Content-Type: application/json');

// Revisamos si existe una sesión y devolvemos su rol
if (isset($_SESSION['id_usuario'])) {
    echo json_encode(["activa" => true, "rol" => $_SESSION['rol']]);
} else {
    echo json_encode(["activa" => false]);
}
?>