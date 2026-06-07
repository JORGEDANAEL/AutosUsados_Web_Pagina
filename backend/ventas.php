<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(["success" => false, "mensaje" => "No tienes permisos"]);
    exit;
}

try {
    // Unimos las 3 tablas para armar el reporte completo
    $query = $conexion->prepare("
        SELECT 
            i.id AS folio, 
            u.nombre AS cliente, 
            u.email, 
            a.marca, 
            a.modelo, 
            i.fecha_solicitud 
        FROM intenciones_compra i
        JOIN usuarios u ON i.id_usuario = u.id
        JOIN autos a ON i.id_auto = a.id
        ORDER BY i.fecha_solicitud DESC
    ");
    $query->execute();
    
    // Extraemos la información con sintaxis MySQLi
    $resultado = $query->get_result();
    $ventas = $resultado->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(["success" => true, "datos" => $ventas]);

} catch(Exception $e) {
    echo json_encode(["success" => false, "mensaje" => "Error de BD: " . $e->getMessage()]);
}
?>