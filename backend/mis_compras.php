<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

// Verificamos que haya sesión
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "mensaje" => "No has iniciado sesión"]);
    exit;
}

try {
    $id_usuario = $_SESSION['id_usuario'];

    // Consultamos solo las compras de este usuario específico (Cambiamos :id_usuario por ?)
    $query = $conexion->prepare("
        SELECT 
            i.id AS folio, 
            a.marca, 
            a.modelo, 
            a.precio, 
            i.fecha_solicitud, 
            i.estatus,
            a.imagen_url
        FROM intenciones_compra i
        JOIN autos a ON i.id_auto = a.id
        WHERE i.id_usuario = ?
        ORDER BY i.fecha_solicitud DESC
    ");
    
    // "i" indica que el parámetro es de tipo entero (Integer)
    $query->bind_param("i", $id_usuario);
    $query->execute();
    
    // Obtenemos el resultado en un bloque de memoria de MySQLi
    $resultado = $query->get_result();
    
    // Extraemos todas las filas juntas (Reemplazo exacto de fetchAll)
    $compras = $resultado->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode(["success" => true, "datos" => $compras]);

} catch(Exception $e) {
    echo json_encode(["success" => false, "mensaje" => "Error: " . $e->getMessage()]);
}
?>