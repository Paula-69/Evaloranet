<?php

require_once("../../config/seguridad/coordinador.php");

$nombre=trim($_POST["nombre"]);

$sql="INSERT INTO materias(nombre)
VALUES(?)";

$stmt=$conexion->prepare($sql);

$stmt->bind_param("s",$nombre);

$stmt->execute();

header("Location:index.php");