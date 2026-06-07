<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

// Validar que sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(["success" => false, "mensaje" => "No tienes permisos"]);
    exit;
}

try {
    // --- LÓGICA DE PAGINACIÓN ---
    $pagina = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_VALIDATE_INT) : 1;
    $limite = isset($_GET['limit']) ? filter_var($_GET['limit'], FILTER_VALIDATE_INT) : 10;
    
    if($pagina < 1) $pagina = 1;
    $offset = ($pagina - 1) * $limite;

    // 1. Contamos el total de registros (Traducción a MySQLi)
    $query_total = $conexion->query("SELECT COUNT(*) AS total FROM registro_conexiones");
    $row_total = $query_total->fetch_assoc();
    $total_registros = $row_total['total'];
    $total_paginas = ceil($total_registros / $limite);

    // 2. Traemos solo los registros usando LIMIT y OFFSET con '?'
    $sql = "
        SELECT r.id, u.nombre, r.fecha_hora_login, r.ip 
        FROM registro_conexiones r
        JOIN usuarios u ON r.id_usuario = u.id
        ORDER BY r.fecha_hora_login DESC
        LIMIT ? OFFSET ?
    ";
    
    $query = $conexion->prepare($sql);
    
    // "ii" significa que le vamos a pasar dos valores de tipo Integer (Enteros)
    $query->bind_param("ii", $limite, $offset);
    $query->execute();
    
    $resultado = $query->get_result();
    $logs = $resultado->fetch_all(MYSQLI_ASSOC);
    
    // Devolvemos los datos y la info de paginación
    echo json_encode([
        "success" => true, 
        "datos" => $logs,
        "paginacion" => [
            "total_registros" => $total_registros,
            "total_paginas" => $total_paginas,
            "pagina_actual" => $pagina,
            "limite" => $limite
        ]
    ]);

} catch(Exception $e) {
    echo json_encode(["success" => false, "mensaje" => "Error de BD: " . $e->getMessage()]);
}
?>