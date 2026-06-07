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
            // A partir de aquí usamos $email_limpio y $nombre_limpio
            $check = $conexion->prepare("SELECT id FROM usuarios WHERE email = :email");
            $check->bindParam(':email', $email_limpio);
            $check->execute();

            if($check->rowCount() > 0) {
                echo json_encode(["success" => false, "mensaje" => "Este correo ya está registrado."]);
                exit; 
            }

            $password_encriptada = password_hash($datos->password, PASSWORD_DEFAULT);

            $query = $conexion->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :password, 'cliente')");
            $query->bindParam(':nombre', $nombre_limpio);
            $query->bindParam(':email', $email_limpio);
            $query->bindParam(':password', $password_encriptada);
            
            $query->execute();

            echo json_encode(["success" => true, "mensaje" => "Registro exitoso. Redirigiendo al login..."]);
        // ... (el resto del código catch queda igual)
        } catch(PDOException $e) {
            echo json_encode(["success" => false, "mensaje" => "Error de base de datos: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "mensaje" => "Faltan datos obligatorios."]);
    }
}
?>