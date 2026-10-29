
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/seguridad/coordinador.php';

/*
|--------------------------------------------------------------------------
| CONSULTAS DE LA BASE DE DATOS
|--------------------------------------------------------------------------
*/

$estudiantes = ['total' => 0];
$docentes    = ['total' => 0];
$cursos      = ['total' => 0];
$reportes    = ['total' => 0];
$periodos    = ['total' => 0];

$consultas = [
    'estudiantes' => 'SELECT COUNT(*) AS total FROM estudiantes',
    'docentes'    => 'SELECT COUNT(*) AS total FROM docentes',
    'cursos'      => 'SELECT COUNT(*) AS total FROM cursos',
    'reportes'    => 'SELECT COUNT(*) AS total FROM desempeno_estudiantes',
    'periodos'    => 'SELECT COUNT(*) AS total FROM periodos',
];

foreach ($consultas as $clave => $consulta) {
    $resultado = $conexion->query($consulta);

    if ($resultado instanceof mysqli_result) {
        $fila = $resultado->fetch_assoc();

        if ($fila !== null) {
            $$clave = $fila;
        }

        $resultado->free();
    }
}

/*
|--------------------------------------------------------------------------
| ENCABEZADO Y NAVEGACIÓN
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';

$nombreUsuario = $_SESSION['nombre'] ?? 'Coordinador';
?>

<div class="evalora-layout">

    <?php require __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="evalora-main">

        <div class="dashboard-coordinador">

            <!-- ENCABEZADO -->
            <header class="dc-heading">
                <h1 class="dc-title">Panel del coordinador</h1>

                <p class="dc-subtitle">
                    Hola,
                    <strong>
                        <?= htmlspecialchars(
                            (string) $nombreUsuario,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </strong>.
                    Aquí tienes un resumen de la gestión académica.
                </p>
            </header>

            <!-- TARJETAS DE RESUMEN -->
            <section aria-label="Resumen general">
                <div class="dc-stats">

                    <article class="dc-stat blue">
                        <div>
                            <div class="dc-stat-icon" aria-hidden="true">🎓</div>
                            <div class="dc-stat-label">Estudiantes</div>
                            <div class="dc-stat-number">
                                <?= (int) $estudiantes['total'] ?>
                            </div>
                        </div>
                        <div class="dc-stat-note">Registrados</div>
                    </article>

                    <article class="dc-stat purple">
                        <div>
                            <div class="dc-stat-icon" aria-hidden="true">🧑‍🏫</div>
                            <div class="dc-stat-label">Docentes</div>
                            <div class="dc-stat-number">
                                <?= (int) $docentes['total'] ?>
                            </div>
                        </div>
                        <div class="dc-stat-note">Registrados</div>
                    </article>

                    <article class="dc-stat cyan">
                        <div>
                            <div class="dc-stat-icon" aria-hidden="true">🏫</div>
                            <div class="dc-stat-label">Cursos</div>
                            <div class="dc-stat-number">
                                <?= (int) $cursos['total'] ?>
                            </div>
                        </div>
                        <div class="dc-stat-note">Disponibles</div>
                    </article>

                    <article class="dc-stat purple">
                        <div>
                            <div class="dc-stat-icon" aria-hidden="true">📊</div>
                            <div class="dc-stat-label">Reportes</div>
                            <div class="dc-stat-number">
                                <?= (int) $reportes['total'] ?>
                            </div>
                        </div>
                        <div class="dc-stat-note">Desempeños registrados</div>
                    </article>

                </div>
            </section>

            <!-- ACCESOS RÁPIDOS -->
            <section class="dc-section" aria-labelledby="dc-accesos-title">

                <h2 class="dc-section-title" id="dc-accesos-title">
                    Accesos rápidos
                </h2>

                <div class="dc-quick-grid">

                    <a href="importar/" class="dc-quick-link blue">
                        <span class="dc-quick-icon" aria-hidden="true">📄</span>
                        <span class="dc-quick-copy">
                            <span class="dc-quick-title">
                                Importar estudiantes y docentes
                            </span>
                            <span class="dc-quick-description">
                                Gestión de usuarios
                            </span>
                        </span>
                    </a>

                    <a href="materias/" class="dc-quick-link purple">
                        <span class="dc-quick-icon" aria-hidden="true">📚</span>
                        <span class="dc-quick-copy">
                            <span class="dc-quick-title">
                                Administrar materias
                            </span>
                            <span class="dc-quick-description">
                                Organizar materias
                            </span>
                        </span>
                    </a>

                    <a href="carga_academica/" class="dc-quick-link cyan">
                        <span class="dc-quick-icon" aria-hidden="true">🧑‍🏫</span>
                        <span class="dc-quick-copy">
                            <span class="dc-quick-title">
                                Asignar carga académica
                            </span>
                            <span class="dc-quick-description">
                                Asignaciones docentes
                            </span>
                        </span>
                    </a>

                    <a href="periodos/" class="dc-quick-link purple">
                        <span class="dc-quick-icon" aria-hidden="true">📅</span>
                        <span class="dc-quick-copy">
                            <span class="dc-quick-title">
                                Administrar períodos
                            </span>
                            <span class="dc-quick-description">
                                Configurar períodos académicos
                            </span>
                        </span>
                    </a>

                </div>
            </section>

            <!-- PERÍODOS ACADÉMICOS -->
            <section class="dc-period-card" aria-labelledby="dc-periodos-title">

                <h2 class="dc-period-title" id="dc-periodos-title">
                    <span aria-hidden="true">📅</span>
                    Períodos académicos
                </h2>

                <p class="dc-period-description">
                    Actualmente existen
                    <strong>
                        <?= (int) $periodos['total'] ?>
                        períodos académicos
                    </strong>
                    registrados. Desde la administración de períodos
                    puedes habilitar o deshabilitar el registro de
                    desempeños para los docentes.
                </p>

                <a href="periodos/" class="dc-period-button">
                    ⚙️ Gestionar períodos
                </a>

            </section>

        </div>

    </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```