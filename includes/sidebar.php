<?php

// ======================================================
// OBTENER ROL DEL USUARIO
// ======================================================

$rol = $_SESSION["rol"] ?? "";

?>

<div class="bg-dark text-white min-vh-100 p-3">

    <h4 class="text-center mb-4">
        Seguimiento
    </h4>


    <div class="list-group">


        <?php if ($rol === "coordinador"): ?>


            <!-- ==================================================
                 MENÚ COORDINADOR
            ================================================== -->


            <a
                href="/Evaloranet/coordinador/index.php"
                class="list-group-item list-group-item-action"
            >
                🏠 Dashboard
            </a>


            <a
                href="/Evaloranet/coordinador/importar/index.php"
                class="list-group-item list-group-item-action"
            >
                📄 Importar Excel
            </a>


            <a
                href="/Evaloranet/coordinador/materias/index.php"
                class="list-group-item list-group-item-action"
            >
                📚 Materias
            </a>


            <a
                href="/Evaloranet/coordinador/cursos/index.php"
                class="list-group-item list-group-item-action"
            >
                🏫 Cursos
            </a>


            <a
                href="/Evaloranet/coordinador/carga_academica/index.php"
                class="list-group-item list-group-item-action"
            >
                👨‍🏫 Carga Académica
            </a>


            <a
                href="/Evaloranet/coordinador/reportes/index.php"
                class="list-group-item list-group-item-action"
            >
                📊 Reportes
            </a>


            <a
                href="/Evaloranet/coordinador/restaurar_password/index.php"
                class="list-group-item list-group-item-action"
            >
                🔑 Restaurar contraseña
            </a>


            <a
                href="/Evaloranet/auth/logout.php"
                class="list-group-item list-group-item-action text-danger"
            >
                🚪 Cerrar sesión
            </a>


        <?php elseif ($rol === "docente"): ?>


            <!-- ==================================================
                 MENÚ DOCENTE
            ================================================== -->


            <a
                href="/Evaloranet/docente/index.php"
                class="list-group-item list-group-item-action"
            >
                🏠 Dashboard
            </a>


            <a
                href="/Evaloranet/auth/logout.php"
                class="list-group-item list-group-item-action text-danger"
            >
                🚪 Cerrar sesión
            </a>


        <?php elseif ($rol === "estudiante"): ?>


            <!-- ==================================================
                 MENÚ ESTUDIANTE
            ================================================== -->


            <a
                href="/Evaloranet/estudiante/index.php"
                class="list-group-item list-group-item-action"
            >
                🏠 Dashboard
            </a>


            <a
                href="/Evaloranet/auth/logout.php"
                class="list-group-item list-group-item-action text-danger"
            >
                🚪 Cerrar sesión
            </a>


        <?php endif; ?>


    </div>

</div>