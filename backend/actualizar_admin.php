<?php
require 'conexion.php';

// Generamos el hash para la contraseña "admin"
$nuevo_hash = password_hash('admin', PASSWORD_DEFAULT);

// Actualizamos todos los usuarios que tengan la contraseña vieja 'admin' o '1234'
$query = $conexion->prepare("UPDATE usuarios SET password = :hash WHERE email = 'admin@admin.com'");
$query->bindParam(':hash', $nuevo_hash);

if ($query->execute()) {
    echo "<h1>¡Éxito!</h1><p>La contraseña del administrador ha sido encriptada. Ya puedes borrar este archivo.</p>";
} else {
    echo "Hubo un error al actualizar.";
}
?>