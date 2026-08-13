<?php

require_once("../../config/seguridad/coordinador.php");

$id=$_GET["id"];

$verificar=$conexion->prepare("
SELECT COUNT(*) total
FROM carga_academica
WHERE materia_id=?
");

$verificar->bind_param("i",$id);

$verificar->execute();

$total=$verificar->get_result()->fetch_assoc();

if($total["total"]>0){

die("No puede eliminar esta materia porque ya está asignada a un docente.");

}

$stmt=$conexion->prepare("DELETE FROM materias
WHERE id=?");

$stmt->bind_param("i",$id);

$stmt->execute();

header("Location:index.php");