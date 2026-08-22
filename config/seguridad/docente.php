<?php

if (!isset($_SESSION["id"])) {

    header("Location: ../auth/login.php");

    exit();

}


if (!isset($_SESSION["rol"]) || $_SESSION["rol"] !== "docente") {

    header("Location: ../auth/login.php");

    exit();

}