<?php

$servidor = "localhost";
$usuario = "root";
$password = "";
$bd = "seguimiento_academico";

$conexion = new mysqli($servidor, $usuario, $password, $bd);

if($conexion->connect_errno){
    die("Error de conexión: ".$conexion->connect_error);
}

$conexion->set_charset("utf8");