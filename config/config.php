<?php

// =====================================================
// CONFIGURACIÓN GENERAL DEL SISTEMA
// =====================================================

// Iniciar sesión solamente si todavía no existe
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión a la base de datos
require_once __DIR__ . "/conexion.php";

// Zona horaria
date_default_timezone_set("America/Bogota");