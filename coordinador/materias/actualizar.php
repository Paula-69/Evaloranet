<?php

require_once("../../config/seguridad/coordinador.php");

$id=$_POST["id"];

$nombre=$_POST["nombre"];

$stmt=$conexion->prepare("UPDATE materias
SET nombre=?
WHERE id=?");

$stmt->bind_param("si",$nombre,$id);

$stmt->execute();

header("Location:index.php");