<?php
// Datos de conexión a la base de datos
$host = 'localhost';
$dbname = 'concesionaria_db'; // La base de datos que acabamos de crear
$username = 'root';           // Usuario por defecto en XAMPP
$password = '';               // XAMPP no tiene contraseña por defecto

try {
    // Conectamos usando PDO (la forma más segura y moderna en PHP)
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configuramos PDO para que nos avise si hay errores
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mensaje de prueba (puedes borrar esta línea después de probar)
    //echo "¡Conexión exitosa desde el servidor PHP!"; 

} catch(PDOException $e) {
    // Si algo falla, el servidor PHP nos mostrará el error
    die("Error en el servidor: " . $e->getMessage());
}
?>