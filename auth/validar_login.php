
<?php
session_start();

require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Recibir los datos del formulario
|--------------------------------------------------------------------------
| login.php envía "usuario" y "contrasena".
| Permitimos iniciar sesión usando el número de documento o el correo.
*/

$usuarioIngresado = trim($_POST['usuario'] ?? '');
$passwordIngresada = $_POST['contrasena'] ?? '';

if ($usuarioIngresado === '' || $passwordIngresada === '') {
    header('Location: login.php?error=1');
    exit;
}

/*
|--------------------------------------------------------------------------
| Buscar usuario activo por documento o correo
|--------------------------------------------------------------------------
*/

$sql = "SELECT *
        FROM usuarios
        WHERE (documento = ? OR correo = ?)
          AND activo = 1
        LIMIT 1";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    error_log('Error preparando login: ' . $conexion->error);
    header('Location: login.php?error=1');
    exit;
}

$stmt->bind_param(
    'ss',
    $usuarioIngresado,
    $usuarioIngresado
);

$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    $stmt->close();
    header('Location: login.php?error=1');
    exit;
}

$usuario = $resultado->fetch_assoc();
$stmt->close();

/*
|--------------------------------------------------------------------------
| Verificar contraseña
|--------------------------------------------------------------------------
*/

if (!password_verify($passwordIngresada, $usuario['password'])) {
    header('Location: login.php?error=1');
    exit;
}

/*
|--------------------------------------------------------------------------
| Crear sesión
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);

$_SESSION['id'] = (int) $usuario['id'];
$_SESSION['usuario_id'] = (int) $usuario['id'];
$_SESSION['nombre'] = $usuario['nombres'];
$_SESSION['rol'] = strtolower(trim($usuario['rol']));

/*
|--------------------------------------------------------------------------
| Forzar cambio de contraseña
|--------------------------------------------------------------------------
*/

if (
    in_array($_SESSION['rol'], ['coordinador', 'docente'], true)
    && (int) $usuario['cambiar_password'] === 1
) {
    header('Location: ../cambiar_password.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Redirigir según el rol
|--------------------------------------------------------------------------
*/

$rutas = [
    'administrador' => '../admin/index.php',
    'admin'         => '../admin/index.php',
    'coordinador'   => '../coordinador/index.php',
    'docente'       => '../docente/index.php',
    'estudiante'    => '../estudiante/index.php',
];

if (isset($rutas[$_SESSION['rol']])) {
    header('Location: ' . $rutas[$_SESSION['rol']]);
    exit;
}

session_unset();
session_destroy();

header('Location: login.php?error=1');
exit;

