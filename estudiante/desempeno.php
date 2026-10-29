
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../config/config.php");
require_once("../config/seguridad/estudiante.php");

// =====================================================
// DATOS DEL USUARIO
// =====================================================

$nombre = $_SESSION["nombre"] ?? "Estudiante";

$usuario_id = $_SESSION["usuario_id"]
    ?? $_SESSION["id"]
    ?? $_SESSION["user_id"]
    ?? 0;

if ((int) $usuario_id <= 0) {
    header("Location: index.php");
    exit();
}

$usuario_id = (int) $usuario_id;

// =====================================================
// VALORES INICIALES
// =====================================================

$estudiante_id = 0;
$curso_id = 0;
$curso = "Sin curso asignado";

$periodo_id = isset($_GET["periodo_id"])
    ? (int) $_GET["periodo_id"]
    : 1;

$periodo_nombre = "Periodo 1";
$estudianteEncontrado = false;

// =====================================================
// BUSCAR ESTUDIANTE Y CURSO
// =====================================================

$sqlEstudiante = "
    SELECT
        e.id AS estudiante_id,
        e.curso_id,
        c.nombre AS curso
    FROM estudiantes e
    LEFT JOIN cursos c ON e.curso_id = c.id
    WHERE e.usuario_id = ?
    LIMIT 1
";

$stmtEstudiante = $conexion->prepare($sqlEstudiante);

if ($stmtEstudiante) {
    $stmtEstudiante->bind_param("i", $usuario_id);
    $stmtEstudiante->execute();

    $resultadoEstudiante = $stmtEstudiante->get_result();
    $datosEstudiante = $resultadoEstudiante->fetch_assoc();

    $stmtEstudiante->close();

    if ($datosEstudiante) {
        $estudianteEncontrado = true;
        $estudiante_id = (int) $datosEstudiante["estudiante_id"];
        $curso_id = (int) ($datosEstudiante["curso_id"] ?? 0);
        $curso = $datosEstudiante["curso"] ?? "Sin curso asignado";
    }
}

// =====================================================
// OBTENER PERÍODOS DISPONIBLES
// =====================================================

$periodos = $conexion->query("
    SELECT id, nombre
    FROM periodos
    ORDER BY id ASC
");

// =====================================================
// VALIDAR PERÍODO SELECCIONADO
// =====================================================

$periodoExiste = false;

if ($periodos) {
    while ($p = $periodos->fetch_assoc()) {
        if ((int) $p["id"] === $periodo_id) {
            $periodo_nombre = $p["nombre"];
            $periodoExiste = true;
            break;
        }
    }

    $periodos->data_seek(0);
}

if (!$periodoExiste && $periodos && $periodos->num_rows > 0) {
    $primerPeriodo = $periodos->fetch_assoc();

    $periodo_id = (int) $primerPeriodo["id"];
    $periodo_nombre = $primerPeriodo["nombre"];

    $periodos->data_seek(0);
}

// =====================================================
// OBTENER DESEMPEÑO POR MATERIA
// =====================================================

$resultado = null;
$errorConsulta = false;

if ($estudianteEncontrado) {

    $sqlDesempeno = "
        SELECT
            m.nombre AS materia,
            cd.desempeno,
            cd.color,
            ds.fecha_registro
        FROM desempeno_estudiantes ds
        INNER JOIN carga_academica ca
            ON ds.carga_academica_id = ca.id
        INNER JOIN materias m
            ON ca.materia_id = m.id
        INNER JOIN colores_desempeno cd
            ON ds.color_id = cd.id
        WHERE ds.estudiante_id = ?
          AND ds.periodo_id = ?
        ORDER BY m.nombre ASC
    ";

    $stmt = $conexion->prepare($sqlDesempeno);

    if ($stmt) {
        $stmt->bind_param(
            "ii",
            $estudiante_id,
            $periodo_id
        );

        $stmt->execute();
        $resultado = $stmt->get_result();
    } else {
        $errorConsulta = true;
    }
}

// =====================================================
// ESTRUCTURA COMPARTIDA
// =====================================================

include("../includes/header.php");
include("../includes/navbar.php");

?>

<div class="evalora-layout">

    <?php include("../includes/sidebar.php"); ?>

    <main class="evalora-main estudiante-page">

        <div class="estudiante-contenido">

            <!-- ENCABEZADO -->

            <header class="estudiante-heading">
                <div>
                    <span class="estudiante-eyebrow">
                        SEGUIMIENTO ACADÉMICO
                    </span>

                    <h1>Mi desempeño académico</h1>

                    <p>
                        Consulta tus resultados por materia y período.
                    </p>
                </div>
            </header>

            <!-- INFORMACIÓN DEL ESTUDIANTE -->

            <section class="estudiante-estadisticas">

                <article class="card shadow border-0 h-100 estudiante-stat">
                    <div class="card-body">
                        <div class="estudiante-stat-icon morado">👤</div>

                        <p class="estudiante-stat-label">
                            Estudiante
                        </p>

                        <h2 class="estudiante-dato-texto">
                            <?= htmlspecialchars(
                                $nombre,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </h2>

                        <small>Tu cuenta académica</small>
                    </div>
                </article>

                <article class="card shadow border-0 h-100 estudiante-stat">
                    <div class="card-body">
                        <div class="estudiante-stat-icon azul">🎓</div>

                        <p class="estudiante-stat-label">
                            Curso
                        </p>

                        <h2 class="estudiante-dato-texto">
                            <?= htmlspecialchars(
                                $curso,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </h2>

                        <small>Curso asignado</small>
                    </div>
                </article>

            </section>

            <!-- SELECTOR DE PERÍODO -->

            <section class="card shadow border-0 mb-4 estudiante-card">

                <div class="card-body p-4">

                    <div class="estudiante-seccion-encabezado">
                        <div>
                            <span class="estudiante-eyebrow">
                                CONSULTA ACADÉMICA
                            </span>

                            <h2>Consultar período</h2>

                            <p>
                                Selecciona el período que deseas revisar.
                            </p>
                        </div>

                        <div class="estudiante-periodo-icono">
                            📅
                        </div>
                    </div>

                    <form
                        method="GET"
                        action="desempeno.php"
                        class="estudiante-periodo-form"
                    >

                        <div class="estudiante-periodo-campo">

                            <label
                                for="periodo_id"
                                class="form-label"
                            >
                                Período académico
                            </label>

                            <select
                                name="periodo_id"
                                id="periodo_id"
                                class="form-select"
                                required
                            >

                                <?php if ($periodos): ?>

                                    <?php while ($p = $periodos->fetch_assoc()): ?>

                                        <option
                                            value="<?= (int) $p["id"] ?>"
                                            <?= (
                                                (int) $p["id"] === $periodo_id
                                            ) ? "selected" : "" ?>
                                        >
                                            <?= htmlspecialchars(
                                                $p["nombre"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary estudiante-btn-consultar"
                        >
                            <span aria-hidden="true">🔎</span>
                            Ver período
                        </button>

                    </form>

                    <div class="estudiante-periodo-actual">
                        <span>Estás consultando:</span>

                        <strong>
                            <?= htmlspecialchars(
                                $periodo_nombre,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </strong>
                    </div>

                </div>

            </section>

            <!-- LEYENDA DE DESEMPEÑO -->

            <section class="card shadow border-0 mb-4 estudiante-card">

                <div class="card-body p-4">

                    <div class="estudiante-seccion-encabezado">
                        <div>
                            <span class="estudiante-eyebrow">
                                GUÍA DE COLORES
                            </span>

                            <h2>Semáforo de desempeño</h2>

                            <p>
                                Estos colores identifican tu nivel académico.
                            </p>
                        </div>
                    </div>

                    <div class="estudiante-leyenda estudiante-leyenda-tarjetas">

                        <div class="estudiante-leyenda-item">
                            <span class="estudiante-punto rojo"></span>

                            <div>
                                <strong>Bajo</strong>
                                <small>Rojo</small>
                            </div>
                        </div>

                        <div class="estudiante-leyenda-item">
                            <span class="estudiante-punto amarillo"></span>

                            <div>
                                <strong>Básico</strong>
                                <small>Amarillo</small>
                            </div>
                        </div>

                        <div class="estudiante-leyenda-item">
                            <span class="estudiante-punto verde"></span>

                            <div>
                                <strong>Alto</strong>
                                <small>Verde</small>
                            </div>
                        </div>

                    </div>

                </div>

            </section>

            <!-- TABLA DE DESEMPEÑO -->

            <section class="card shadow border-0 mb-4 estudiante-card">

                <div class="card-body p-4">

                    <div class="estudiante-seccion-encabezado">
                        <div>
                            <span class="estudiante-eyebrow">
                                RESULTADOS
                            </span>

                            <h2>Desempeño por materia</h2>

                            <p>
                                Período:
                                <strong>
                                    <?= htmlspecialchars(
                                        $periodo_nombre,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>
                                </strong>
                            </p>
                        </div>
                    </div>

                    <?php if (!$estudianteEncontrado): ?>

                        <div class="estudiante-mensaje estudiante-mensaje-error">
                            <span>⚠️</span>

                            <div>
                                <strong>No encontramos tu información académica.</strong>

                                <p>
                                    Tu cuenta no tiene un estudiante asociado.
                                    Contacta al coordinador para verificar tu registro.
                                </p>
                            </div>
                        </div>

                    <?php elseif ($errorConsulta): ?>

                        <div class="estudiante-mensaje estudiante-mensaje-error">
                            <span>⚠️</span>

                            <div>
                                <strong>No fue posible consultar tus resultados.</strong>

                                <p>
                                    Inténtalo nuevamente o contacta al administrador
                                    si el problema continúa.
                                </p>
                            </div>
                        </div>

                    <?php elseif ($resultado && $resultado->num_rows > 0): ?>

                        <div class="table-responsive estudiante-tabla-contenedor">

                            <table class="table estudiante-tabla align-middle">

                                <thead>
                                    <tr>
                                        <th class="estudiante-col-numero">#</th>
                                        <th>Materia</th>
                                        <th class="text-center">Desempeño</th>
                                        <th class="text-center">Último registro</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php
                                    $contador = 1;

                                    while ($fila = $resultado->fetch_assoc()):

                                        $colorOriginal = trim(
                                            (string) ($fila["color"] ?? "")
                                        );

                                        // Acepta colores hexadecimales.
                                        // Si la base guarda nombres, usa el color
                                        // correspondiente al nivel de desempeño.
                                        $colorCss = "#64748B";

                                        if (
                                            preg_match(
                                                '/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/',
                                                $colorOriginal
                                            )
                                        ) {
                                            $colorCss = $colorOriginal;
                                        } else {
                                            $nivel = mb_strtolower(
                                                trim(
                                                    (string) ($fila["desempeno"] ?? "")
                                                ),
                                                "UTF-8"
                                            );

                                            if ($nivel === "bajo") {
                                                $colorCss = "#DC3545";
                                            } elseif ($nivel === "básico" || $nivel === "basico") {
                                                $colorCss = "#FFC107";
                                            } elseif ($nivel === "alto") {
                                                $colorCss = "#198754";
                                            }
                                        }

                                        $nivelTexto = ucfirst(
                                            (string) ($fila["desempeno"] ?? "Sin dato")
                                        );

                                        $fechaTexto = "Sin fecha";

                                        if (!empty($fila["fecha_registro"])) {
                                            $marcaTiempo = strtotime(
                                                $fila["fecha_registro"]
                                            );

                                            if ($marcaTiempo !== false) {
                                                $fechaTexto = date(
                                                    "d/m/Y H:i",
                                                    $marcaTiempo
                                                );
                                            }
                                        }
                                    ?>

                                        <tr>
                                            <td class="estudiante-numero">
                                                <?= $contador++ ?>
                                            </td>

                                            <td>
                                                <strong class="estudiante-materia">
                                                    <?= htmlspecialchars(
                                                        $fila["materia"] ?? "",
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ) ?>
                                                </strong>
                                            </td>

                                            <td class="text-center">
                                                <span
                                                    class="estudiante-badge-desempeno"
                                                    style="
                                                        background-color: <?= htmlspecialchars(
                                                            $colorCss,
                                                            ENT_QUOTES,
                                                            "UTF-8"
                                                        ) ?>;
                                                        color: <?= $colorCss === "#FFC107"
                                                            ? "#212529"
                                                            : "#FFFFFF" ?>;
                                                    "
                                                >
                                                    <?= htmlspecialchars(
                                                        $nivelTexto,
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ) ?>
                                                </span>
                                            </td>

                                            <td class="text-center estudiante-fecha">
                                                <?= htmlspecialchars(
                                                    $fechaTexto,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ) ?>
                                            </td>
                                        </tr>

                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="estudiante-mensaje estudiante-mensaje-info">
                            <span>📊</span>

                            <div>
                                <strong>No hay registros para este período.</strong>

                                <p>
                                    Todavía no hay desempeños registrados para ti
                                    en
                                    <?= htmlspecialchars(
                                        $periodo_nombre,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>.
                                </p>
                            </div>
                        </div>

                    <?php endif; ?>

                </div>

            </section>

        </div>

    </main>

</div>

<style>
/* =====================================================
   ESTILOS DE DESEMPEÑO DEL ESTUDIANTE
   Reutiliza la estructura visual del dashboard.
===================================================== */

.estudiante-page {
    min-width: 0;
}

.estudiante-contenido {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 28px;
    box-sizing: border-box;
}

.estudiante-heading {
    margin-bottom: 26px;
}

.estudiante-heading h1 {
    margin: 5px 0 8px;
    color: #1e3a8a;
    font-size: clamp(1.7rem, 2.5vw, 2.2rem);
    font-weight: 800;
}

.estudiante-heading p {
    margin: 0;
    color: #64748b;
}

.estudiante-eyebrow {
    display: inline-block;
    color: #7041c5;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 1.1px;
}

.estudiante-estadisticas {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 26px;
}

.estudiante-stat,
.estudiante-card {
    min-width: 0;
    overflow: hidden;
    border-radius: 18px !important;
    background: #fff;
}

.estudiante-stat {
    border-top: 4px solid #2458df !important;
}

.estudiante-stat .card-body {
    padding: 22px;
}

.estudiante-stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    margin-bottom: 14px;
    border-radius: 13px;
    font-size: 1.3rem;
}

.estudiante-stat-icon.morado {
    background: #eee8ff;
}

.estudiante-stat-icon.azul {
    background: #e4edff;
}

.estudiante-stat-label {
    margin-bottom: 7px;
    color: #64748b;
    font-size: 0.9rem;
}

.estudiante-stat h2 {
    margin: 0 0 8px;
    color: #1e293b;
    font-size: 1.3rem;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.estudiante-stat .estudiante-dato-texto {
    font-size: 1.15rem;
}

.estudiante-stat small {
    color: #64748b;
}

.estudiante-card {
    margin-bottom: 24px;
    border: 1px solid #e8edf5 !important;
    box-shadow: 0 8px 26px rgba(30, 58, 138, 0.06) !important;
}

.estudiante-seccion-encabezado {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 22px;
}

.estudiante-seccion-encabezado h2 {
    margin: 7px 0;
    color: #1e293b;
    font-size: 1.3rem;
    font-weight: 800;
}

.estudiante-seccion-encabezado p {
    margin: 0;
    color: #64748b;
}

.estudiante-periodo-icono {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: #e8efff;
    font-size: 1.4rem;
}

.estudiante-periodo-form {
    display: flex;
    align-items: end;
    gap: 14px;
}

.estudiante-periodo-campo {
    flex: 1;
    min-width: 0;
}

.estudiante-periodo-campo label {
    color: #334155;
    font-weight: 600;
}

.estudiante-periodo-campo .form-select {
    min-height: 46px;
    border-color: #dbe3ef;
    border-radius: 10px;
}

.estudiante-btn-consultar {
    min-height: 46px;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 700;
}

.estudiante-periodo-actual {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 7px;
    margin-top: 18px;
    padding: 14px 16px;
    border-radius: 12px;
    background: #eff6ff;
    color: #334155;
}

.estudiante-periodo-actual strong {
    color: #1d4ed8;
}

.estudiante-leyenda-tarjetas {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}

.estudiante-leyenda-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    border: 1px solid #e8edf5;
    border-radius: 13px;
    background: #fff;
}

.estudiante-punto {
    display: inline-block;
    flex-shrink: 0;
    width: 13px;
    height: 13px;
    border-radius: 50%;
}

.estudiante-punto.rojo {
    background: #dc3545;
}

.estudiante-punto.amarillo {
    background: #ffc107;
}

.estudiante-punto.verde {
    background: #198754;
}

.estudiante-leyenda-item strong,
.estudiante-leyenda-item small {
    display: block;
}

.estudiante-leyenda-item strong {
    color: #1e293b;
}

.estudiante-leyenda-item small {
    margin-top: 3px;
    color: #64748b;
}

.estudiante-tabla-contenedor {
    width: 100%;
    border: 1px solid #e8edf5;
    border-radius: 13px;
}

.estudiante-tabla {
    min-width: 620px;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.estudiante-tabla thead th {
    padding: 16px;
    background: #f1f5fb;
    color: #334155;
    font-size: 0.88rem;
    font-weight: 800;
    white-space: nowrap;
    border-bottom: 1px solid #e2e8f0;
}

.estudiante-tabla tbody td {
    padding: 16px;
    color: #334155;
    border-bottom: 1px solid #edf1f7;
}

.estudiante-tabla tbody tr:last-child td {
    border-bottom: 0;
}

.estudiante-tabla tbody tr:hover {
    background: #f8faff;
}

.estudiante-col-numero {
    width: 60px;
}

.estudiante-numero {
    color: #64748b !important;
}

.estudiante-materia {
    color: #1e3a8a;
}

.estudiante-badge-desempeno {
    display: inline-block;
    padding: 7px 12px;
    border-radius: 8px;
    font-size: 0.83rem;
    font-weight: 700;
    white-space: nowrap;
}

.estudiante-fecha {
    color: #64748b !important;
    white-space: nowrap;
    font-size: 0.9rem;
}

.estudiante-mensaje {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 20px;
    border-radius: 13px;
}

.estudiante-mensaje > span {
    font-size: 1.5rem;
}

.estudiante-mensaje strong {
    display: block;
    margin-bottom: 5px;
}

.estudiante-mensaje p {
    margin: 0;
}

.estudiante-mensaje-info {
    background: #eff6ff;
    color: #1e40af;
}

.estudiante-mensaje-error {
    background: #fff1f2;
    color: #9f1239;
}

@media (max-width: 768px) {
    .estudiante-contenido {
        padding: 18px 14px;
    }

    .estudiante-estadisticas {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .estudiante-periodo-form {
        align-items: stretch;
        flex-direction: column;
    }

    .estudiante-btn-consultar {
        width: 100%;
    }

    .estudiante-leyenda-tarjetas {
        grid-template-columns: 1fr;
    }

    .estudiante-seccion-encabezado h2 {
        font-size: 1.15rem;
    }
}
</style>

<?php

if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}

include("../includes/footer.php");

?>
