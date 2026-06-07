<?php
session_start();
require 'conexion.php';

header('Content-Type: application/json');

// Si no hay sesión iniciada, no puede comprar
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "mensaje" => "Debes iniciar sesión"]);
    exit;
}

$datos = json_decode(file_get_contents("php://input"));

if (isset($datos->id_auto)) {
    try {
        $id_usuario = $_SESSION['id_usuario'];
        $id_auto = $datos->id_auto;

        // Insertamos la relación en la base de datos
        $query = $conexion->prepare("INSERT INTO intenciones_compra (id_usuario, id_auto) VALUES (:id_usuario, :id_auto)");
        $query->bindParam(':id_usuario', $id_usuario);
        $query->bindParam(':id_auto', $id_auto);
        $query->execute();

        echo json_encode(["success" => true, "mensaje" => "Intención de compra registrada"]);
    } catch(PDOException $e) {
        echo json_encode(["success" => false, "mensaje" => "Error de BD: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "mensaje" => "Faltan datos del auto"]);
}
?>