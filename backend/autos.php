<?php
session_start();
require 'conexion.php';

header('Content-Type: application/json');

// 1. Verificamos que al menos haya iniciado sesión (sea cliente o admin)
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "mensaje" => "No tienes sesión activa"]);
    exit;
}

// ... código anterior
// 2. Si la petición es GET (mostrar catálogo o un auto específico)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['id'])) {
            // Si nos piden un ID, buscamos solo ese auto
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            $query = $conexion->prepare("SELECT * FROM autos WHERE id = :id");
            $query->bindParam(':id', $id);
            $query->execute();
            $auto = $query->fetch(PDO::FETCH_ASSOC);
            
            if($auto) {
                echo json_encode(["success" => true, "datos" => $auto]);
            } else {
                echo json_encode(["success" => false, "mensaje" => "Auto no encontrado"]);
            }
        } else {
            // Si no hay ID, devolvemos todo el catálogo disponible
            $query = $conexion->prepare("SELECT id, marca, modelo, anio, precio, cantidad, imagen_url FROM autos WHERE estado = 'disponible'");
            $query->execute();
            $autos = $query->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(["success" => true, "datos" => $autos]);
        }
    } catch(PDOException $e) {
        echo json_encode(["success" => false, "mensaje" => "Error al obtener autos: " . $e->getMessage()]);
    }
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
        echo json_encode(["success" => false, "mensaje" => "Solo el administrador puede agregar autos"]);
        exit;
    }

    // --- SANITIZACIÓN DE DATOS ---
    $marca = htmlspecialchars(strip_tags($_POST['marca'] ?? ''));
    $modelo = htmlspecialchars(strip_tags($_POST['modelo'] ?? ''));
    
    // Forzamos que sean enteros o flotantes (números reales)
    $anio = filter_var($_POST['anio'] ?? null, FILTER_VALIDATE_INT);
    $precio = filter_var($_POST['precio'] ?? null, FILTER_VALIDATE_FLOAT);
    $cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT);
    // -----------------------------
    
    if($marca && $modelo && $anio && $precio && $cantidad !== false) {
        
        $ruta_imagen_bd = '';

// ... (el resto del código de subir la imagen y el INSERT queda exactamente igual)

        if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $nombre_archivo = time() . '_' . basename($_FILES['imagen']['name']);
            $ruta_destino = '../uploads/' . $nombre_archivo; 
            
            if(move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                $ruta_imagen_bd = 'uploads/' . $nombre_archivo; 
            }
        }

        try {
            // Actualizamos el INSERT para incluir la cantidad
            $query = $conexion->prepare("INSERT INTO autos (marca, modelo, anio, precio, cantidad, imagen_url) VALUES (:marca, :modelo, :anio, :precio, :cantidad, :imagen_url)");
            
            $query->bindParam(':marca', $marca);
            $query->bindParam(':modelo', $modelo);
            $query->bindParam(':anio', $anio);
            $query->bindParam(':precio', $precio);
            $query->bindParam(':cantidad', $cantidad); // Pasamos la cantidad a la BD
            $query->bindParam(':imagen_url', $ruta_imagen_bd);
            
            $query->execute();
            
            echo json_encode(["success" => true, "mensaje" => "Auto guardado en inventario correctamente"]);
        } catch(PDOException $e) {
            echo json_encode(["success" => false, "mensaje" => "Error de BD: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "mensaje" => "Faltan datos obligatorios"]);
    }
}
?>