<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rol = $_SESSION["rol"] ?? '';
$nombre = $_SESSION["nombre"] ?? '';

/*
|--------------------------------------------------------------------------
| PANEL SEGÚN ROL
|--------------------------------------------------------------------------
*/

$inicio = "/Evaloranet/";

if ($rol === "coordinador") {

    $inicio .= "coordinador/index.php";

} elseif ($rol === "docente") {

    $inicio .= "docente/index.php";

} elseif ($rol === "estudiante") {

    $inicio .= "estudiante/index.php";

} else {

    $inicio .= "auth/login.php";

}

?>

<nav class="navbar navbar-dark bg-primary">

    <div class="container-fluid">

        <a
            class="navbar-brand"
            href="<?= htmlspecialchars($inicio) ?>"
        >
            Seguimiento Académico
        </a>


        <div class="d-flex align-items-center">

            <?php if ($nombre !== ''): ?>

                <span class="text-white me-3">

                    Bienvenido,
                    <?= htmlspecialchars($nombre) ?>

                </span>

            <?php endif; ?>


            <a
                href="/Evaloranet/auth/logout.php"
                class="btn btn-danger btn-sm"
            >
                Cerrar sesión
            </a>

        </div>

    </div>

</nav>