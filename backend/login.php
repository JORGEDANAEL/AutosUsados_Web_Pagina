<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

$datos = json_decode(file_get_contents("php://input"));

if(isset($datos->email) && isset($datos->password)) {
    $email = $datos->email;
    $password_ingresada = $datos->password;

    // MySQLi usa '?' en lugar de ':email'
    $query = $conexion->prepare("SELECT id, nombre, rol, password FROM usuarios WHERE email = ?");
    
    // La "s" significa que el dato es un string (texto)
    $query->bind_param("s", $email);
    $query->execute();
    
    // Obtenemos el resultado de la consulta en MySQLi
    $resultado = $query->get_result();
    $usuario = $resultado->fetch_assoc();

    // Verificamos que el usuario exista y que la contraseña coincida con el hash
    if($usuario && password_verify($password_ingresada, $usuario['password'])) {
        
        $_SESSION['id_usuario'] = $usuario['id'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['nombre'] = $usuario['nombre'];

        $ip = $_SERVER['REMOTE_ADDR'];
        
        // El insert también usa '?' para los valores
        $log_query = $conexion->prepare("INSERT INTO registro_conexiones (id_usuario, ip) VALUES (?, ?)");
        
        // "is" significa: i = integer (id_usuario), s = string (ip)
        $log_query->bind_param("is", $usuario['id'], $ip);
        $log_query->execute();

        echo json_encode(["success" => true, "mensaje" => "Login exitoso", "rol" => $usuario['rol']]);
    } else {
        echo json_encode(["success" => false, "mensaje" => "Correo o contraseña incorrectos"]);
    }
} else {
    echo json_encode(["success" => false, "mensaje" => "Faltan datos"]);
}
?>