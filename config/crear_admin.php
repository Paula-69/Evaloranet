<?php

require_once "conexion.php";

$documento = "1234567890";
$password = password_hash($documento, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios
(
documento,
nombres,
apellidos,
correo,
telefono,
password,
rol,
cambiar_password,
activo
)

VALUES
(
?,
'Administrador',
'Sistema',
'admin@colegio.com',
'',
?,
'coordinador',
1,
1
)";

$stmt = $conexion->prepare($sql);

$stmt->bind_param("ss",$documento,$password);

if($stmt->execute()){

    echo "Administrador creado correctamente.";

}else{

    echo $stmt->error;

}