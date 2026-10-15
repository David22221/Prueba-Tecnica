<?php
function obtenerConexion(): PDO
{
    $archivo = __DIR__ . '/config.php';
    $cfg = is_file($archivo) ? require $archivo : [];

    $host = $cfg['host'];
    $port = $cfg['port'];
    $db   = $cfg['db'];
    $user = $cfg['user'];
    $pass = $cfg['pass'];  

    return new PDO(
        "pgsql:host=$host;port=$port;dbname=$db",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
}

function responderJson($datos, int $codigo = 200): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}