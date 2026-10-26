
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EvaloraNet | Seguimiento Académico</title>

    <meta
        name="description"
        content="EvaloraNet facilita el seguimiento del desempeño académico de estudiantes y docentes."
    >

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Hoja de estilos: assets/style.css -->
    <link
        rel="stylesheet"
        href="/Evaloranet/assets/style.css?v=9"
    >
</head>

<body class="inicio-evalora">

    <!-- ======================================
         ENCABEZADO INSTITUCIONAL
    ======================================= -->
    <header class="inicio-navbar">
        <div class="inicio-navbar-contenido">

            <a
                href="/Evaloranet/index.php"
                class="inicio-marca"
                aria-label="Inicio de EvaloraNet"
            >
                <!-- El escudo se añadirá cuando esté disponible -->
                <span class="inicio-marca-texto">
                    <strong>EVALORA<span>NET</span></strong>
                    <small>Seguimiento Académico</small>
                </span>
            </a>

            <a
                href="/Evaloranet/auth/login.php"
                class="inicio-boton-login"
            >
                Iniciar sesión
            </a>

        </div>
    </header>


    <!-- ======================================
         PORTADA PRINCIPAL
    ======================================= -->
    <main class="inicio-hero">

        <!-- Fotografía de fondo -->
        <div
            class="inicio-hero-fondo"
            aria-hidden="true"
        ></div>

        <!-- Filtro azul sobre el collage -->
        <div
            class="inicio-overlay"
            aria-hidden="true"
        ></div>

        <!-- Mariposa flotante -->
        <div
            class="mariposa-flotante"
            aria-hidden="true"
        >🦋</div>

        <!-- Elementos decorativos -->
        <div class="inicio-decoraciones" aria-hidden="true">
            <span class="flotante flotante-circulo"></span>
            <span class="flotante flotante-anillo"></span>
            <span class="flotante flotante-rombo"></span>
            <span class="flotante flotante-punto"></span>
            <span class="flotante flotante-brillo">✦</span>
            <span class="flotante flotante-brillo brillo-dos">✧</span>
        </div>

        <!-- Contenido principal -->
        <section class="inicio-contenido">

            <div class="inicio-etiqueta">
                <span aria-hidden="true">🎓</span>
                SISTEMA DE SEGUIMIENTO ACADÉMICO
            </div>

            <h1 class="inicio-titulo">
                Acompañamos tu
                <br>
                <span>proceso académico</span>
            </h1>

            <div class="inicio-linea"></div>

            <h2 class="inicio-eslogan">
                Conéctate con algo seguro para lograr un triunfo.
            </h2>

            <p class="inicio-descripcion">
                EvaloraNet facilita el seguimiento del desempeño
                académico, permitiendo conectar estudiantes,
                docentes y coordinación en un solo lugar.
            </p>

            <div class="inicio-botones">
                <a
                    href="/Evaloranet/auth/login.php"
                    class="inicio-boton"
                >
                    <span aria-hidden="true">👤</span>
                    <span>Iniciar sesión</span>
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            <!-- Características de la plataforma -->
            <div class="inicio-info">

                <div class="inicio-info-item">
                    <span class="inicio-info-icono" aria-hidden="true">
                        🔒
                    </span>
                    <div>
                        <strong>Seguro</strong>
                        <small>Información protegida</small>
                    </div>
                </div>

                <div class="inicio-info-item">
                    <span class="inicio-info-icono" aria-hidden="true">
                        📊
                    </span>
                    <div>
                        <strong>Académico</strong>
                        <small>Seguimiento del desempeño</small>
                    </div>
                </div>

                <div class="inicio-info-item">
                    <span class="inicio-info-icono" aria-hidden="true">
                        ⚡
                    </span>
                    <div>
                        <strong>Fácil</strong>
                        <small>Acceso rápido y sencillo</small>
                    </div>
                </div>

            </div>

        </section>

    </main>


    <!-- ======================================
         PIE DE PÁGINA
    ======================================= -->
    <footer class="inicio-pie">

        <p>
            <strong>EvaloraNet</strong>
            · Sistema de Seguimiento Académico
        </p>

        <p>
            Institución Educativa Departamental
            Pbro. Carlos Garavito Acosta de Gachancipá
        </p>

        <p class="inicio-derechos">
            © 2026 EvaloraNet · Todos los derechos reservados.
        </p>

        <p class="inicio-autores">
            Desarrollado por:
            <strong>Dawer Felipe Diaz Rodriguez</strong> ·
            <strong>Paula Vanessa Jimenez Escobar</strong> ·
            <strong>Shary Yuliana Mayeton Cuesta</strong>
        </p>

    </footer>

</body>
</html>
