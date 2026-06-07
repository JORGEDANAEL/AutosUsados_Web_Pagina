<?php
session_start();
require 'conexion.php';

header('Content-Type: application/json');

// 1. Verificamos que al menos haya iniciado sesión (sea cliente o admin)
// if (!isset($_SESSION['id_usuario'])) {
//     echo json_encode(["success" => false, "mensaje" => "No tienes sesión activa"]);
//     exit;
// }

// 2. Si la petición es GET (mostrar catálogo o un auto específico)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['id'])) {
            // Si nos piden un ID, buscamos solo ese auto
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            
            // MySQLi usa '?' en lugar de marcadores con nombre (:id)
            $query = $conexion->prepare("SELECT * FROM autos WHERE id = ?");
            $query->bind_param("i", $id);
            $query->execute();
            
            // Obtenemos el resultado para poder extraer la fila
            $resultado = $query->get_result();
            $auto = $resultado->fetch_assoc();
            
            if($auto) {
                echo json_encode(["success" => true, "datos" => $auto]);
            } else {
                echo json_encode(["success" => false, "mensaje" => "Auto no encontrado"]);
            }
        } else {
            // Si no hay ID, devolvemos todo el catálogo disponible
            $query = $conexion->prepare("SELECT id, marca, modelo, anio, precio, cantidad, imagen_url FROM autos WHERE estado = 'disponible'");
            $query->execute();
            
            // Obtenemos el resultado y extraemos todas las filas juntas (Reemplazo de fetchAll)
            $resultado = $query->get_result();
            $autos = $resultado->fetch_all(MYSQLI_ASSOC);
            
            echo json_encode(["success" => true, "datos" => $autos]);
        }
    } catch(Exception $e) {
        echo json_encode(["success" => false, "mensaje" => "Error al obtener autos: " . $e->getMessage()]);
    }
    exit; 
}

// 3. Si la petición es POST (Agregar auto - exclusivo de admin)
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

        if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $nombre_archivo = time() . '_' . basename($_FILES['imagen']['name']);
            $ruta_destino = '../uploads/' . $nombre_archivo; 
            
            if(move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
                $ruta_imagen_bd = 'uploads/' . $nombre_archivo; 
            }
        }

        try {
            // El INSERT también cambia a '?' para MySQLi
            $query = $conexion->prepare("INSERT INTO autos (marca, modelo, anio, precio, cantidad, imagen_url) VALUES (?, ?, ?, ?, ?, ?)");
            
            // Definimos los tipos de datos en orden: 
            // s = string (marca, modelo, imagen_url)
            // i = integer (anio, cantidad)
            // d = double/float (precio)
            // El orden es: marca (s), modelo (s), anio (i), precio (d), cantidad (i), imagen_url (s) -> "ssidis"
            $query->bind_param("ssidis", $marca, $modelo, $anio, $precio, $cantidad, $ruta_imagen_bd);
            
            $query->execute();
            
            echo json_encode(["success" => true, "mensaje" => "Auto guardado en inventario correctamente"]);
        } catch(Exception $e) {
            echo json_encode(["success" => false, "mensaje" => "Error de BD: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "mensaje" => "Faltan datos obligatorios"]);
    }
}
?>