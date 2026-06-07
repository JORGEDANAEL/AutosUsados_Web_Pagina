<?php
// Credenciales de tu base de datos en la nube (Clever Cloud)
$host = "bufszghvpbhtymn9cm5q-mysql.services.clever-cloud.com"; 
$user = "uhpacjadtfxeuwfu";       // Cámbialo por el "User" que te dio Clever Cloud
$password = "3esXD0rq9bGhiVRNGXF7"; // Cámbialo por el "Password" que te dio Clever Cloud
$database = "bufszghvpbhtymn9cm5q";         // El nombre de tu base de datos en la nube
$port = 3306;                                // Puerto estándar de MySQL

// Crear la conexión usando mysqli
$conexion = new mysqli($host, $user, $password, $database, $port);

// Verificar si la conexión falló
if ($conexion->connect_error) {
    die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

// Configurar caracteres para que no se rompan los acentos ni la 'ñ'
$conexion->set_charset("utf8mb4");
?>