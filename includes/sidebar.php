
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rol = $_SESSION["rol"] ?? "";

?>

<!-- Botón del menú para teléfonos -->
<button
    type="button"
    class="evalora-menu-toggle"
    id="evaloraMenuToggle"
    aria-controls="evaloraSidebar"
    aria-expanded="false"
    aria-label="Abrir menú"
>
    <span class="evalora-menu-toggle__icon">☰</span>
    <span>Menú</span>
</button>

<!-- Fondo oscuro del menú móvil -->
<div
    class="evalora-sidebar-overlay"
    id="evaloraSidebarOverlay"
></div>

<!-- Barra lateral -->
<aside class="evalora-sidebar" id="evaloraSidebar">

    <!-- Logo y nombre -->
    <div class="evalora-sidebar__brand">

        <div
    class="evalora-sidebar__logo"
    style="width:46px;height:46px;min-width:46px;max-width:46px;flex:0 0 46px;overflow:hidden;"
>
    <img
        src="/Evaloranet/img/logo.png"
        alt="Logo de EvaloraNet"
        style="display:block;width:100%;height:100%;max-width:46px;max-height:46px;object-fit:contain;"
    >
         </div>

        <div class="evalora-sidebar__brand-text">
            <div class="evalora-sidebar__brand-name">
                EvaloraNet
            </div>

            <div class="evalora-sidebar__brand-subtitle">
                Seguimiento académico
            </div>
        </div>

        <button
            type="button"
            class="evalora-sidebar__close"
            id="evaloraMenuClose"
            aria-label="Cerrar menú"
        >
            ×
        </button>

    </div>

    <!-- Navegación -->
    <div class="evalora-sidebar__section">
        MENÚ PRINCIPAL
    </div>

    <nav class="evalora-sidebar__nav">

        <?php if ($rol === "coordinador"): ?>

            <a
                class="evalora-sidebar__link"
                href="/Evaloranet/coordinador/index.php"
            >
                <span class="evalora-sidebar__icon">⌂</span>
                <span>Dashboard</span>
            </a>

            <a
                class="evalora-sidebar__link"
                href="/Evaloranet/coordinador/importar/index.php"
            >
                <span class="evalora-sidebar__icon">▤</span>
                <span>Importar Excel</span>
            </a>

            <a
                class="evalora-sidebar__link"
                href="/Evaloranet/coordinador/materias/index.php"
            >
                <span class="evalora-sidebar__icon">▣</span>
                <span>Materias</span>
            </a>

            <a
                class="evalora-sidebar__link"
                href="/Evaloranet/coordinador/cursos/index.php"
            >
                <span class="evalora-sidebar__icon">▦</span>
                <span>Cursos</span>
            </a>

            <a
                class="evalora-sidebar__link"
                href="/Evaloranet/coordinador/carga_academica/index.php"
            >
                <span class="evalora-sidebar__icon">♧</span>
                <span>Carga académica</span>
            </a>

            <a
                class="evalora-sidebar__link"
                href="/Evaloranet/coordinador/reportes/index.php"
            >
                <span class="evalora-sidebar__icon">▥</span>
                <span>Reportes</span>
            </a>

            <a
                class="evalora-sidebar__link"
                href="/Evaloranet/coordinador/restaurar_password/index.php"
            >
                <span class="evalora-sidebar__icon">⚿</span>
                <span>Restaurar contraseña</span>
            </a>

        <?php elseif ($rol === "docente"): ?>

            <a
                class="evalora-sidebar__link"
                href="/Evaloranet/docente/index.php"
            >
                <span class="evalora-sidebar__icon">⌂</span>
                <span>Dashboard</span>
            </a>

        <?php elseif ($rol === "estudiante"): ?>

            <a
                class="evalora-sidebar__link"
                href="/Evaloranet/estudiante/index.php"
            >
                <span class="evalora-sidebar__icon">⌂</span>
                <span>Dashboard</span>
            </a>

        <?php endif; ?>

    </nav>

    <!-- Cerrar sesión -->
    <div class="evalora-sidebar__footer">

        <a
            class="evalora-sidebar__link evalora-sidebar__logout"
            href="/Evaloranet/auth/logout.php"
        >
            <span class="evalora-sidebar__icon">↪</span>
            <span>Cerrar sesión</span>
        </a>

    </div>

</aside>
