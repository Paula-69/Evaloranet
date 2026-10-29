
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rol = $_SESSION["rol"] ?? '';
$nombre = $_SESSION["nombre"] ?? '';

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

<nav class="navbar navbar-dark bg-primary evalora-navbar">
    <div class="container-fluid">

        <a
            class="navbar-brand"
            href="<?= htmlspecialchars($inicio, ENT_QUOTES, 'UTF-8') ?>"
        >
            Seguimiento Académico
        </a>

        <?php if ($nombre !== ''): ?>
            <div class="d-flex align-items-center">
                <span class="text-white">
                    Hola, <?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>
        <?php endif; ?>

    </div>
</nav>
