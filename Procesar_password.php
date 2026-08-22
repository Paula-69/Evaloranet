<?php

session_start();

require_once("config/config.php");

// Verificar que haya una sesión iniciada
if (!isset($_SESSION["id"])) {
    header("Location: auth/login.php");
    exit();
}

$id_usuario = $_SESSION["id"];

$password = $_POST["password"] ?? "";
$confirmar_password = $_POST["confirmar_password"] ?? "";

// Verificar que no estén vacías
if ($password === "" || $confirmar_password === "") {
    header("Location: cambiar_password.php?error=Debes completar todos los campos");
    exit();
}

// Verificar que coincidan
if ($password !== $confirmar_password) {
    header("Location: cambiar_password.php?error=Las contraseñas no coinciden");
    exit();
}

// Mínimo 6 caracteres
if (strlen($password) < 6) {
    header("Location: cambiar_password.php?error=La contraseña debe tener mínimo 6 caracteres");
    exit();
}

// Encriptar contraseña
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Actualizar contraseña y quitar obligación de cambio
$sql = "UPDATE usuarios
        SET password = ?,
            cambiar_password = 0
        WHERE id = ?";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    header("Location: cambiar_password.php?error=Error al preparar la consulta");
    exit();
}

$stmt->bind_param("si", $password_hash, $id_usuario);

if ($stmt->execute()) {

    // Redirigir al panel según el rol
    header("Location: " . $_SESSION["rol"] . "/index.php");
    exit();

} else {

    header("Location: cambiar_password.php?error=No se pudo cambiar la contraseña");
    exit();
}