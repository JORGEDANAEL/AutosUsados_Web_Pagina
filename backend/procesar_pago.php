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
        // Iniciamos una transacción para que, si algo falla, no se descuenten carros a lo tonto
        $conexion->beginTransaction();

        $id_usuario = $_SESSION['id_usuario'];
        $id_auto = $datos->id_auto;

        // 1. Revisamos el inventario actual
        $query_stock = $conexion->prepare("SELECT cantidad FROM autos WHERE id = :id FOR UPDATE");
        $query_stock->bindParam(':id', $id_auto);
        $query_stock->execute();
        $auto = $query_stock->fetch(PDO::FETCH_ASSOC);

        if ($auto && $auto['cantidad'] > 0) {
            // 2. Si hay stock, insertamos la compra en la lista
            $query_compra = $conexion->prepare("INSERT INTO intenciones_compra (id_usuario, id_auto, estatus) VALUES (:id_usuario, :id_auto, 'contactado')");
            $query_compra->bindParam(':id_usuario', $id_usuario);
            $query_compra->bindParam(':id_auto', $id_auto);
            $query_compra->execute();

            // 3. Restamos 1 a la cantidad del inventario
            $query_update = $conexion->prepare("UPDATE autos SET cantidad = cantidad - 1 WHERE id = :id");
            $query_update->bindParam(':id', $id_auto);
            $query_update->execute();

            // Guardamos los cambios definitivamente
            $conexion->commit();
            echo json_encode(["success" => true, "mensaje" => "Pago aprobado. Auto apartado."]);
        } else {
            // Si ya no hay stock, cancelamos todo
            $conexion->rollBack();
            echo json_encode(["success" => false, "mensaje" => "Lo sentimos, este auto acaba de agotarse."]);
        }

    } catch(PDOException $e) {
        $conexion->rollBack();
        echo json_encode(["success" => false, "mensaje" => "Error al procesar: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "mensaje" => "Faltan datos de la compra."]);
}
?>