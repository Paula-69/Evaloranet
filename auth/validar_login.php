<?php

session_start();

require_once("../config/config.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$documento = trim($_POST["documento"] ?? '');
$password = trim($_POST["password"] ?? '');

if ($documento === '' || $password === '') {
    header("Location: login.php?error=1");
    exit();
}

$sql = "SELECT * FROM usuarios
        WHERE documento = ?
        AND activo = 1
        LIMIT 1";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error en la consulta: " . $conexion->error);
}

$stmt->bind_param("s", $documento);

$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    header("Location: login.php?error=1");
    exit();
}

$usuario = $resultado->fetch_assoc();

if (!password_verify($password, $usuario["password"])) {
    header("Location: login.php?error=1");
    exit();
}

/*
|--------------------------------------------------------------------------
| GUARDAR SESIÓN
|--------------------------------------------------------------------------
*/

$_SESSION["id"] = $usuario["id"];
$_SESSION["nombre"] = $usuario["nombres"];
$_SESSION["rol"] = $usuario["rol"];

/*
|--------------------------------------------------------------------------
| CAMBIO DE CONTRASEÑA
|--------------------------------------------------------------------------
*/

if (
    ($usuario["rol"] === "coordinador" ||
     $usuario["rol"] === "docente")
    &&
    (int)$usuario["cambiar_password"] === 1
) {

    header("Location: ../cambiar_password.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| REDIRECCIÓN SEGÚN ROL
|--------------------------------------------------------------------------
*/

if ($usuario["rol"] === "coordinador") {

    header("Location: ../coordinador/index.php");
    exit();

}

if ($usuario["rol"] === "docente") {

    header("Location: ../docente/index.php");
    exit();

}

if ($usuario["rol"] === "estudiante") {

    header("Location: ../estudiante/index.php");
    exit();

}

header("Location: login.php?error=1");
exit();