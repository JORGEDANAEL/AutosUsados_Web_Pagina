<?php
require 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = json_decode(file_get_contents("php://input"));

    if(isset($datos->nombre) && isset($datos->email) && isset($datos->password)) {
        
        // --- SANITIZACIÓN DE DATOS ---
        // Quitamos etiquetas HTML del nombre para evitar inyección de código
        $nombre_limpio = htmlspecialchars(strip_tags($datos->nombre));
        // Limpiamos y validamos que el formato de correo sea real
        $email_limpio = filter_var($datos->email, FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email_limpio, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "mensaje" => "Formato de correo inválido."]);
            exit;
        }
        // -----------------------------

        try {
            // 1. Revisar si el correo ya existe usando '?' para MySQLi
            $check = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
            $check->bind_param("s", $email_limpio);
            $check->execute();
            
            // Obtenemos el resultado para verificar las filas existentes (reemplazo de rowCount)
            $resultado_check = $check->get_result();

            if($resultado_check->num_rows > 0) {
                echo json_encode(["success" => false, "mensaje" => "Este correo ya está registrado."]);
                exit; 
            }

            $password_encriptada = password_hash($datos->password, PASSWORD_DEFAULT);

            // 2. Insertar el nuevo usuario con marcadores '?'
            $query = $conexion->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'cliente')");
            
            // "sss" indica que los tres parámetros son de tipo string (texto)
            $query->bind_param("sss", $nombre_limpio, $email_limpio, $password_encriptada);
            $query->execute();

            echo json_encode(["success" => true, "mensaje" => "Registro exitoso. Redirigiendo al login..."]);

        } catch(Exception $e) {
            echo json_encode(["success" => false, "mensaje" => "Error de base de datos: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "mensaje" => "Faltan datos obligatorios."]);
    }
}
?>