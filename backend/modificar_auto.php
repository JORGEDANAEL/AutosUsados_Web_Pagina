<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(["success" => false, "mensaje" => "No tienes permisos"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $marca = htmlspecialchars(strip_tags($_POST['marca'] ?? ''));
    $modelo = htmlspecialchars(strip_tags($_POST['modelo'] ?? ''));
    $anio = filter_var($_POST['anio'] ?? null, FILTER_VALIDATE_INT);
    $precio = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_FLOAT);
    $cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT);

    if ($id && $marca && $modelo && $anio && $precio && $cantidad !== false) {
        try {
            // Verificamos si subió una nueva imagen
            $subir_imagen = false;
            $ruta_imagen_bd = '';

            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $nombre_archivo = time() . '_' . basename($_FILES['imagen']['name']);
                $ruta_destino = '../uploads/' . $nombre_archivo;
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                    $ruta_imagen_bd = 'uploads/' . $nombre_archivo;
                    $subir_imagen = true;
                }
            }

            // Armamos el SQL dinámico usando los signos de interrogación '?' de MySQLi
            if ($subir_imagen) {
                $sql = "UPDATE autos SET marca = ?, modelo = ?, anio = ?, precio = ?, cantidad = ?, imagen_url = ? WHERE id = ?";
                $query = $conexion->prepare($sql);
                
                // Tipos: s (string), s (string), i (int), d (double/float), i (int), s (string), i (int) -> "ssidisi"
                $query->bind_param("ssidisi", $marca, $modelo, $anio, $precio, $cantidad, $ruta_imagen_bd, $id);
            } else {
                $sql = "UPDATE autos SET marca = ?, modelo = ?, anio = ?, precio = ?, cantidad = ? WHERE id = ?";
                $query = $conexion->prepare($sql);
                
                // Tipos: s (string), s (string), i (int), d (double/float), i (int), i (int) -> "ssidii"
                $query->bind_param("ssidii", $marca, $modelo, $anio, $precio, $cantidad, $id);
            }

            $query->execute();
            echo json_encode(["success" => true, "mensaje" => "Vehículo actualizado con éxito"]);

        } catch (Exception $e) {
            echo json_encode(["success" => false, "mensaje" => "Error al actualizar: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "mensaje" => "Datos inválidos o incompletos."]);
    }
}
?>