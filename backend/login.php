<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

$datos = json_decode(file_get_contents("php://input"));

if(isset($datos->email) && isset($datos->password)) {
    $email = $datos->email;
    $password_ingresada = $datos->password;

    // Solo buscamos por el correo
    $query = $conexion->prepare("SELECT id, nombre, rol, password FROM usuarios WHERE email = :email");
    $query->bindParam(':email', $email);
    $query->execute();

    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    // Verificamos que el usuario exista y que la contraseña coincida con el hash
    if($usuario && password_verify($password_ingresada, $usuario['password'])) {
        
        $_SESSION['id_usuario'] = $usuario['id'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['nombre'] = $usuario['nombre'];

        $ip = $_SERVER['REMOTE_ADDR'];
        $log_query = $conexion->prepare("INSERT INTO registro_conexiones (id_usuario, ip) VALUES (:id_usuario, :ip)");
        $log_query->bindParam(':id_usuario', $usuario['id']);
        $log_query->bindParam(':ip', $ip);
        $log_query->execute();

        echo json_encode(["success" => true, "mensaje" => "Login exitoso", "rol" => $usuario['rol']]);
    } else {
        echo json_encode(["success" => false, "mensaje" => "Correo o contraseña incorrectos"]);
    }
} else {
    echo json_encode(["success" => false, "mensaje" => "Faltan datos"]);
}
?>