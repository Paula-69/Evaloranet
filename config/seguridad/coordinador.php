<?php

/*
|--------------------------------------------------------------------------
| SEGURIDAD DEL COORDINADOR
|--------------------------------------------------------------------------
|
| Este archivo verifica que exista una sesión activa
| y que el usuario tenga el rol de coordinador.
|
*/

/*
|--------------------------------------------------------------------------
| Iniciar sesión solamente si todavía no está iniciada
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}


/*
|--------------------------------------------------------------------------
| Verificar que exista una sesión
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["id"])) {

    header("Location: /Evaloranet/auth/login.php");

    exit();

}


/*
|--------------------------------------------------------------------------
| Verificar que el usuario sea coordinador
|--------------------------------------------------------------------------
*/

if ($_SESSION["rol"] !== "coordinador") {

    header("Location: /Evaloranet/auth/login.php");

    exit();

}