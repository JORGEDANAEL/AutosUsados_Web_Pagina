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
    // Obtenemos la página actual (por defecto 1) y el límite (por defecto 10)
    $pagina = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_VALIDATE_INT) : 1;
    $limite = isset($_GET['limit']) ? filter_var($_GET['limit'], FILTER_VALIDATE_INT) : 10;
    
    // Evitar valores menores a 1
    if($pagina < 1) $pagina = 1;
    
    // Calculamos desde qué registro empezar (OFFSET)
    $offset = ($pagina - 1) * $limite;

    // 1. Contamos el total de registros para saber cuántas páginas hay en total
    $query_total = $conexion->query("SELECT COUNT(*) FROM registro_conexiones");
    $total_registros = $query_total->fetchColumn();
    $total_paginas = ceil($total_registros / $limite); // Redondeamos hacia arriba

    // 2. Traemos solo los registros de la página actual usando LIMIT y OFFSET
    // Unimos con la tabla usuarios para obtener el nombre
    $sql = "
        SELECT r.id, u.nombre, r.fecha_hora_login, r.ip 
        FROM registro_conexiones r
        JOIN usuarios u ON r.id_usuario = u.id
        ORDER BY r.fecha_hora_login DESC
        LIMIT :limite OFFSET :offset
    ";
    
    $query = $conexion->prepare($sql);
    // Debemos bindear LIMIT y OFFSET como enteros estrictos
    $query->bindValue(':limite', (int) $limite, PDO::PARAM_INT);
    $query->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
    $query->execute();
    
    $logs = $query->fetchAll(PDO::FETCH_ASSOC);
    
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

} catch(PDOException $e) {
    echo json_encode(["success" => false, "mensaje" => "Error de BD: " . $e->getMessage()]);
}
?>