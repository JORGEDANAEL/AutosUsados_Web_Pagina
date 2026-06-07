<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "mensaje" => "Debes iniciar sesión"]);
    exit;
}

$datos = json_decode(file_get_contents("php://input"));

if (isset($datos->id_auto)) {
    try {
        // Iniciamos una transacción en MySQLi para que, si algo falla, no se descuenten carros a lo tonto
        $conexion->begin_transaction();

        $id_usuario = $_SESSION['id_usuario'];
        $id_auto = $datos->id_auto;

        // 1. Revisamos el inventario actual (Cambiamos :id por ?)
        $query_stock = $conexion->prepare("SELECT cantidad FROM autos WHERE id = ? FOR UPDATE");
        $query_stock->bind_param("i", $id_auto);
        $query_stock->execute();
        
        $resultado = $query_stock->get_result();
        $auto = $resultado->fetch_assoc();

        if ($auto && $auto['cantidad'] > 0) {
            // 2. Si hay stock, insertamos la compra en la lista (Cambiamos :id_usuario, :id_auto por ?, ?)
            $query_compra = $conexion->prepare("INSERT INTO intenciones_compra (id_usuario, id_auto, estatus) VALUES (?, ?, 'contactado')");
            $query_compra->bind_param("ii", $id_usuario, $id_auto);
            $query_compra->execute();

            // 3. Restamos 1 a la cantidad del inventario
            $query_update = $conexion->prepare("UPDATE autos SET cantidad = cantidad - 1 WHERE id = ?");
            $query_update->bind_param("i", $id_auto);
            $query_update->execute();

            // Guardamos los cambios definitivamente
            $conexion->commit();
            echo json_encode(["success" => true, "mensaje" => "Pago aprobado. Auto apartado."]);
        } else {
            // Si ya no hay stock, cancelamos todo
            $conexion->rollback();
            echo json_encode(["success" => false, "mensaje" => "Lo sentimos, este auto acaba de agotarse."]);
        }

    } catch(Exception $e) {
        $conexion->rollback();
        echo json_encode(["success" => false, "mensaje" => "Error al procesar: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "mensaje" => "Faltan datos de la compra."]);
}
?>