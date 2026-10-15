<?php
require_once __DIR__ . '/conexion.php';

$bodegaId = filter_input(INPUT_GET, 'bodega_id', FILTER_VALIDATE_INT);

if (!$bodegaId || $bodegaId < 1) {
    responderJson(['error' => 'Bodega no válida.'], 400);
}

try {
    $pdo = obtenerConexion();
    $stmt = $pdo->prepare(
        "SELECT id, nombre FROM sucursal WHERE bodega_id = :bodega ORDER BY id"
    );
    $stmt->execute([':bodega' => $bodegaId]);
    responderJson($stmt->fetchAll());
} catch (PDOException $e) {
    responderJson(['error' => 'No se pudieron cargar las sucursales.'], 500);
}