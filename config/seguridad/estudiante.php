<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["id"])) {

    header("Location: ../auth/login.php");
    exit();

}

if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "estudiante") {

    header("Location: ../auth/login.php");
    exit();

}