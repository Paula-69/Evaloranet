<?php

require_once __DIR__ . '/config/config.php';

$stmt = $conexion->prepare(
    "SELECT password FROM usuarios WHERE id = 2 LIMIT 1"
);

$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario) {
    exit("No se encontró el usuario con ID 2.");
}

if (password_verify('1234567890', $usuario['password'])) {
    echo "CORRECTO: el hash coincide con el documento.";
} else {
    echo "NO COINCIDE: el hash guardado no corresponde al documento.";
}

$stmt->close();