<?php

require_once("../config/config.php");

$documento = trim($_POST["documento"]);
$password = trim($_POST["password"]);

$sql = "SELECT * FROM usuarios
WHERE documento=?
AND activo=1";

$stmt = $conexion->prepare($sql);

$stmt->bind_param("s",$documento);

$stmt->execute();

$resultado = $stmt->get_result();

if($resultado->num_rows==1){

    $usuario = $resultado->fetch_assoc();

    if(password_verify($password,$usuario["password"])){

        $_SESSION["id"] = $usuario["id"];

        $_SESSION["nombre"] = $usuario["nombres"];

        $_SESSION["rol"] = $usuario["rol"];

        if(

            ($usuario["rol"]=="coordinador" ||

             $usuario["rol"]=="docente")

             &&

             $usuario["cambiar_password"]==1

        ){

            header("Location: ../cambiar_password.php");

            exit();

        }

        header("Location: ../".$usuario["rol"]."/index.php");

        exit();

    }

}

header("Location: login.php?error=1");