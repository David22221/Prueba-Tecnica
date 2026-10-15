<?php
require_once __DIR__ . '/conexion.php';

function fallar(string $mensaje, int $codigo = 422): void
{
    responderJson(['ok' => false, 'mensaje' => $mensaje], $codigo);
}

function campo(string $clave): string
{
    $valor = $_POST[$clave] ?? '';
    return is_string($valor) ? $valor : '';
}

function longitud(string $texto): int
{
    return (int) preg_match_all('/./us', $texto);
}

function entero(string $clave): ?int
{
    $valor = filter_var(campo($clave), FILTER_VALIDATE_INT);
    return ($valor !== false && $valor >= 1) ? $valor : null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fallar('Método no permitido.', 405);
}

// validaciones

$codigo = trim(campo('codigo'));
if ($codigo === '') {
    fallar('El código del producto no puede estar en blanco.');
}
if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z0-9]+\z/', $codigo)) {
    fallar('El código del producto debe contener letras y números');
}
if (longitud($codigo) < 5 || longitud($codigo) > 15) {
    fallar('El código del producto debe tener entre 5 y 15 caracteres.');
}

$nombre = trim(campo('nombre'));
if ($nombre === '') {
    fallar('El nombre del producto no puede estar en blanco.');
}
if (longitud($nombre) < 2 || longitud($nombre) > 50) {
    fallar('El nombre del producto debe tener entre 2 y 50 caracteres.');
}

$bodegaId = entero('bodega_id');
if ($bodegaId === null) {
    fallar('Debe seleccionar una bodega.');
}

$sucursalId = entero('sucursal_id');
if ($sucursalId === null) {
    fallar('Debe seleccionar una sucursal para la bodega seleccionada.');
}

$monedaId = entero('moneda_id');
if ($monedaId === null) {
    fallar('Debe seleccionar una moneda para el producto.');
}

$precio = trim(campo('precio'));
if ($precio === '') {
    fallar('El precio del producto no puede estar en blanco.');
}
if (!preg_match('/^\d+(\.\d{1,2})?\z/', $precio) || (float) $precio <= 0) {
    fallar('El precio del producto debe ser un número positivo con hasta dos decimales.');
}
if (strlen(explode('.', $precio)[0]) > 10) {
    fallar('El precio del producto excede el valor máximo permitido.');
}

$materiales = [];
$recibidos = $_POST['materiales'] ?? [];
if (is_array($recibidos)) {
    foreach ($recibidos as $valor) {
        $id = filter_var($valor, FILTER_VALIDATE_INT);
        if ($id !== false && $id >= 1) {
            $materiales[] = $id;
        }
    }
}
$materiales = array_values(array_unique($materiales));
if (count($materiales) < 2) {
    fallar('Debe seleccionar al menos dos materiales para el producto.');
}

$descripcion = str_replace(["\r\n", "\r"], "\n", campo('descripcion'));
if (trim($descripcion) === '') {
    fallar('La descripción del producto no puede estar en blanco.');
}
if (longitud($descripcion) < 10 || longitud($descripcion) > 1000) {
    fallar('La descripción del producto debe tener entre 10 y 1000 caracteres.');
}

try {
    $pdo = obtenerConexion();
    $stmt = $pdo->prepare(
        'SELECT registrar_producto(
            :codigo, :nombre, :bodega, :sucursal, :moneda,
            :precio, :descripcion, CAST(:materiales AS INT[])
        )'
    );
    $stmt->execute([
        ':codigo'      => $codigo,
        ':nombre'      => $nombre,
        ':bodega'      => $bodegaId,
        ':sucursal'    => $sucursalId,
        ':moneda'      => $monedaId,
        ':precio'      => $precio,
        ':descripcion' => $descripcion,
        ':materiales'  => '{' . implode(',', $materiales) . '}',
    ]);

    responderJson([
        'ok'      => true,
        'mensaje' => 'Producto guardado correctamente.',
        'id'      => (int) $stmt->fetchColumn(),
    ]);
} catch (PDOException $e) {
    $sqlstate = $e->errorInfo[0] ?? '';

    if ($sqlstate === '23505') {
        fallar('El código del producto ya está registrado.', 409);
    }
    if ($sqlstate === 'P0001') {
        fallar('Los datos enviados no son válidos.');
    }
    if ($sqlstate === '23503') {                 
        fallar('Los datos enviados no son válidos.');
    }

    error_log($e->getMessage());
    fallar('Error interno al guardar el producto.', 500);
}