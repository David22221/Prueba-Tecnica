<?php
require_once __DIR__ . '/conexion.php';

try {
    $pdo = obtenerConexion();
    $filas = $pdo->query("SELECT id, nombre FROM material ORDER BY id")->fetchAll();
    responderJson($filas);
} catch (PDOException $e) {
    responderJson(['error' => 'No se pudieron cargar los materiales.'], 500);
}