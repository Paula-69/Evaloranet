
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

$usuario_id = (int) (
    $_SESSION["usuario_id"]
    ?? $_SESSION["id"]
    ?? $_SESSION["user_id"]
    ?? 0
);

// =====================================================
// VALORES INICIALES
// =====================================================

$estudiante_id = 0;
$seguimientos = 0;
$bajo = 0;
$basico = 0;
$alto = 0;

$periodo_id = isset($_GET["periodo_id"])
    ? (int) $_GET["periodo_id"]
    : 1;

$periodo_nombre = "Periodo 1";
$estudianteEncontrado = false;
$errorConsulta = false;

// =====================================================
// OBTENER PERÍODOS
// =====================================================

$periodos = [];

$periodosResultado = $conexion->query("
    SELECT id, nombre
    FROM periodos
    ORDER BY id ASC
");

if ($periodosResultado) {
    while ($filaPeriodo = $periodosResultado->fetch_assoc()) {
        $periodos[] = $filaPeriodo;
    }
}

// Validar período seleccionado.
$periodoValido = false;

foreach ($periodos as $p) {
    if ((int) $p["id"] === $periodo_id) {
        $periodoValido = true;
        $periodo_nombre = $p["nombre"];
        break;
    }
}

// Si el período no existe, seleccionar el primero disponible.
if (!$periodoValido && count($periodos) > 0) {
    $periodo_id = (int) $periodos[0]["id"];
    $periodo_nombre = $periodos[0]["nombre"];
}

// =====================================================
// BUSCAR ESTUDIANTE
// =====================================================

if ($usuario_id > 0) {

    $stmtEstudiante = $conexion->prepare("
        SELECT id
        FROM estudiantes
        WHERE usuario_id = ?
        LIMIT 1
    ");

    if ($stmtEstudiante) {
        $stmtEstudiante->bind_param("i", $usuario_id);
        $stmtEstudiante->execute();

        $resultadoEstudiante = $stmtEstudiante->get_result();
        $datosEstudiante = $resultadoEstudiante->fetch_assoc();

        $stmtEstudiante->close();

        if ($datosEstudiante) {
            $estudianteEncontrado = true;
            $estudiante_id = (int) $datosEstudiante["id"];
        }
    }
}

// =====================================================
// RESUMEN DEL PERÍODO SELECCIONADO
// =====================================================

if ($estudianteEncontrado) {

    $stmtResumen = $conexion->prepare("
        SELECT
            COUNT(*) AS total,
            COALESCE(
                SUM(CASE WHEN color_id = 1 THEN 1 ELSE 0 END),
                0
            ) AS bajo,
            COALESCE(
                SUM(CASE WHEN color_id = 2 THEN 1 ELSE 0 END),
                0
            ) AS basico,
            COALESCE(
                SUM(CASE WHEN color_id = 3 THEN 1 ELSE 0 END),
                0
            ) AS alto
        FROM desempeno_estudiantes
        WHERE estudiante_id = ?
          AND periodo_id = ?
    ");

    if ($stmtResumen) {
        $stmtResumen->bind_param(
            "ii",
            $estudiante_id,
            $periodo_id
        );

        $stmtResumen->execute();

        $resultadoResumen = $stmtResumen->get_result();
        $resumen = $resultadoResumen->fetch_assoc();

        if ($resumen) {
            $seguimientos = (int) $resumen["total"];
            $bajo = (int) $resumen["bajo"];
            $basico = (int) $resumen["basico"];
            $alto = (int) $resumen["alto"];
        }

        $stmtResumen->close();

    } else {
        $errorConsulta = true;
    }
}

// =====================================================
// COMPARACIÓN PROGRESIVA DE PERÍODOS
// Período 1: solo el primero.
// Período 2: primero y segundo.
// Período 3: primero, segundo y tercero.
// =====================================================

$comparacion = [];

// Construir la lista en el orden real de la tabla periodos.
foreach ($periodos as $indice => $p) {

    // Incluir períodos hasta la posición seleccionada.
    $idPeriodo = (int) $p["id"];

    $comparacion[$idPeriodo] = [
        "periodo" => $p["nombre"],
        "bajo" => 0,
        "basico" => 0,
        "alto" => 0
    ];

    // Detenerse cuando se llega al período elegido.
    if ($idPeriodo === $periodo_id) {
        break;
    }
}

// Cargar los registros de los períodos incluidos.
if ($estudianteEncontrado && count($comparacion) > 0) {

    $idsPeriodosComparacion = array_keys($comparacion);
    $marcadores = implode(
        ",",
        array_fill(0, count($idsPeriodosComparacion), "?")
    );

    $tipos = "i" . str_repeat("i", count($idsPeriodosComparacion));

    $sqlComparacion = "
        SELECT
            periodo_id,
            SUM(CASE WHEN color_id = 1 THEN 1 ELSE 0 END) AS bajo,
            SUM(CASE WHEN color_id = 2 THEN 1 ELSE 0 END) AS basico,
            SUM(CASE WHEN color_id = 3 THEN 1 ELSE 0 END) AS alto
        FROM desempeno_estudiantes
        WHERE estudiante_id = ?
          AND periodo_id IN ($marcadores)
        GROUP BY periodo_id
    ";

    $stmtComparacion = $conexion->prepare($sqlComparacion);

    if ($stmtComparacion) {

        $parametros = array_merge(
            [$estudiante_id],
            $idsPeriodosComparacion
        );

        $stmtComparacion->bind_param($tipos, ...$parametros);
        $stmtComparacion->execute();

        $resultadoComparacion = $stmtComparacion->get_result();

        while ($filaComparacion = $resultadoComparacion->fetch_assoc()) {

            $idPeriodo = (int) $filaComparacion["periodo_id"];

            if (isset($comparacion[$idPeriodo])) {
                $comparacion[$idPeriodo]["bajo"] =
                    (int) $filaComparacion["bajo"];

                $comparacion[$idPeriodo]["basico"] =
                    (int) $filaComparacion["basico"];

                $comparacion[$idPeriodo]["alto"] =
                    (int) $filaComparacion["alto"];
            }
        }

        $stmtComparacion->close();

    } else {
        $errorConsulta = true;
    }
}

// =====================================================
// PREPARAR DATOS PARA CHART.JS
// =====================================================

$etiquetasGrafica = [];
$datosBajo = [];
$datosBasico = [];
$datosAlto = [];

foreach ($comparacion as $dato) {
    $etiquetasGrafica[] = $dato["periodo"];
    $datosBajo[] = $dato["bajo"];
    $datosBasico[] = $dato["basico"];
    $datosAlto[] = $dato["alto"];
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

                    <h1>Panel del estudiante</h1>

                    <p>
                        Bienvenido,
                        <strong>
                            <?= htmlspecialchars(
                                $nombre,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </strong>
                    </p>
                </div>
            </header>

            <?php if (!$estudianteEncontrado): ?>

                <section class="card shadow border-0 estudiante-card">
                    <div class="card-body p-4">
                        <div class="alert alert-warning mb-0">
                            No se encontró un estudiante asociado a tu cuenta.
                            Contacta al coordinador para revisar tu registro.
                        </div>
                    </div>
                </section>

            <?php else: ?>

                <!-- SELECTOR DE PERÍODO -->

                <section class="card shadow border-0 mb-4 estudiante-card">

                    <div class="card-body p-4">

                        <div class="estudiante-seccion-encabezado">
                            <div>
                                <span class="estudiante-eyebrow">
                                    CONSULTA ACADÉMICA
                                </span>

                                <h2>Período académico</h2>

                                <p>
                                    Selecciona un período para consultar
                                    tus resultados y la evolución académica.
                                </p>
                            </div>
                        </div>

                        <form
                            method="GET"
                            action="index.php"
                            class="estudiante-periodo-form"
                        >

                            <div class="estudiante-periodo-campo">
                                <label
                                    for="periodo_id"
                                    class="form-label"
                                >
                                    Selecciona el período
                                </label>

                                <select
                                    name="periodo_id"
                                    id="periodo_id"
                                    class="form-select"
                                    required
                                >
                                    <?php foreach ($periodos as $p): ?>

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

                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary estudiante-btn-consultar"
                            >
                                Ver período
                            </button>

                        </form>

                        <div class="estudiante-periodo-actual">
                            Estás consultando:
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

                <!-- TARJETAS DE RESUMEN DEL PERÍODO SELECCIONADO -->

                <section class="estudiante-estadisticas">

                    <article class="card shadow border-0 h-100 estudiante-stat">
                        <div class="card-body">
                            <div class="estudiante-stat-icon morado">📚</div>
                            <p class="estudiante-stat-label">Seguimientos</p>
                            <h2><?= $seguimientos ?></h2>
                            <small>Registros del período seleccionado</small>
                        </div>
                    </article>

                    <article class="card shadow border-0 h-100 estudiante-stat">
                        <div class="card-body">
                            <div class="estudiante-stat-icon rojo">↓</div>
                            <p class="estudiante-stat-label">Desempeño bajo</p>
                            <h2><?= $bajo ?></h2>
                            <small>
                                En <?= htmlspecialchars(
                                    $periodo_nombre,
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </small>
                        </div>
                    </article>

                    <article class="card shadow border-0 h-100 estudiante-stat">
                        <div class="card-body">
                            <div class="estudiante-stat-icon amarillo">＝</div>
                            <p class="estudiante-stat-label">Desempeño básico</p>
                            <h2><?= $basico ?></h2>
                            <small>
                                En <?= htmlspecialchars(
                                    $periodo_nombre,
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </small>
                        </div>
                    </article>

                    <article class="card shadow border-0 h-100 estudiante-stat">
                        <div class="card-body">
                            <div class="estudiante-stat-icon verde">↑</div>
                            <p class="estudiante-stat-label">Desempeño alto</p>
                            <h2><?= $alto ?></h2>
                            <small>
                                En <?= htmlspecialchars(
                                    $periodo_nombre,
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </small>
                        </div>
                    </article>

                </section>

                <!-- GRÁFICA COMPARATIVA -->

                <section class="card shadow border-0 mb-4 estudiante-card">

                    <div class="card-body p-4">

                        <div class="estudiante-seccion-encabezado">
                            <div>
                                <span class="estudiante-eyebrow">
                                    EVOLUCIÓN ACADÉMICA
                                </span>

                                <h2>Comparación entre períodos</h2>

                                <p>
                                    Consulta la cantidad de registros Bajo,
                                    Básico y Alto acumulados hasta el período
                                    seleccionado.
                                </p>
                            </div>
                        </div>

                        <div class="estudiante-leyenda">

                            <div class="estudiante-leyenda-item">
                                <span class="estudiante-punto rojo"></span>
                                <strong>Bajo</strong>
                            </div>

                            <div class="estudiante-leyenda-item">
                                <span class="estudiante-punto amarillo"></span>
                                <strong>Básico</strong>
                            </div>

                            <div class="estudiante-leyenda-item">
                                <span class="estudiante-punto verde"></span>
                                <strong>Alto</strong>
                            </div>

                        </div>

                        <?php if ($errorConsulta): ?>

                            <div class="alert alert-warning mt-3 mb-0">
                                No fue posible obtener todos los datos de la
                                gráfica. Comprueba la conexión y la estructura
                                de la base de datos.
                            </div>

                        <?php elseif (count($periodos) === 0): ?>

                            <div class="estudiante-grafica-vacia">
                                No hay períodos académicos registrados.
                            </div>

                        <?php else: ?>

                            <div class="estudiante-grafica-barras">
                                <canvas id="graficaComparacionPeriodos"></canvas>
                            </div>

                            <p class="estudiante-grafica-nota">
                                Cada grupo representa un período académico.
                                Las barras muestran el número de registros
                                por nivel de desempeño.
                            </p>

                        <?php endif; ?>

                    </div>
                </section>

                <!-- ACCESO AL DETALLE -->

                <section class="card shadow border-0 mb-4 estudiante-card">

                    <div class="card-body p-4">

                        <div class="estudiante-desempeno-grid">
                            <div>
                                <span class="estudiante-eyebrow">
                                    SEGUIMIENTO ACADÉMICO
                                </span>

                                <h2>Mi desempeño académico</h2>

                                <p>
                                    Consulta el detalle de tus resultados
                                    por materia en el período seleccionado.
                                </p>
                            </div>

                            <div class="estudiante-desempeno-accion">
                                <a
                                    href="desempeno.php?periodo_id=<?= $periodo_id ?>"
                                    class="btn btn-primary"
                                >
                                    Ver mi desempeño →
                                </a>
                            </div>
                        </div>

                    </div>
                </section>

            <?php endif; ?>

        </div>
    </main>
</div>

<!-- CHART.JS -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const canvas = document.getElementById(
        "graficaComparacionPeriodos"
    );

    if (!canvas || typeof Chart === "undefined") {
        return;
    }

    const etiquetas = <?= json_encode(
        $etiquetasGrafica,
        JSON_UNESCAPED_UNICODE |
        JSON_HEX_TAG |
        JSON_HEX_APOS |
        JSON_HEX_QUOT |
        JSON_HEX_AMP
    ) ?>;

    const datosBajo = <?= json_encode($datosBajo) ?>;
    const datosBasico = <?= json_encode($datosBasico) ?>;
    const datosAlto = <?= json_encode($datosAlto) ?>;

    const totalRegistros =
        datosBajo.reduce((a, b) => a + b, 0) +
        datosBasico.reduce((a, b) => a + b, 0) +
        datosAlto.reduce((a, b) => a + b, 0);

    if (totalRegistros === 0) {
        canvas.parentElement.innerHTML = `
            <div class="estudiante-grafica-vacia">
                <span>📊</span>
                <p>Aún no hay registros para los períodos incluidos.</p>
            </div>
        `;
        return;
    }

    new Chart(canvas, {
        type: "bar",

        data: {
            labels: etiquetas,

            datasets: [
                {
                    label: "Bajo",
                    data: datosBajo,
                    backgroundColor: "#DC3545",
                    borderColor: "#DC3545",
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 48
                },
                {
                    label: "Básico",
                    data: datosBasico,
                    backgroundColor: "#FFC107",
                    borderColor: "#FFC107",
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 48
                },
                {
                    label: "Alto",
                    data: datosAlto,
                    backgroundColor: "#198754",
                    borderColor: "#198754",
                    borderWidth: 1,
                    borderRadius: 6,
                    maxBarThickness: 48
                }
            ]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            interaction: {
                mode: "index",
                intersect: false
            },

            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: "#64748B"
                    }
                },

                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                        color: "#64748B"
                    },
                    grid: {
                        color: "#E9EEF5"
                    },
                    title: {
                        display: true,
                        text: "Cantidad de registros",
                        color: "#64748B"
                    }
                }
            },

            plugins: {
                legend: {
                    display: false
                },

                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.dataset.label + ": " +
                                context.parsed.y;
                        }
                    }
                }
            }
        }
    });

});
</script>

<style>
/* =====================================================
   PANEL DEL ESTUDIANTE
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
    margin: 6px 0 8px;
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
    font-size: .75rem;
    font-weight: 800;
    letter-spacing: 1.1px;
}

/* =====================================================
   TARJETAS DE RESUMEN
===================================================== */

.estudiante-estadisticas {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
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

.estudiante-stat-icon.morado { background: #eee8ff; }
.estudiante-stat-icon.rojo { background: #ffe5e8; }
.estudiante-stat-icon.amarillo { background: #fff5cf; }
.estudiante-stat-icon.verde { background: #d9f5e5; }

.estudiante-stat-label {
    margin-bottom: 7px;
    color: #64748b;
    font-size: .9rem;
}

.estudiante-stat h2 {
    margin: 0 0 8px;
    color: #1e293b;
    font-size: 1.7rem;
    font-weight: 800;
}

.estudiante-stat small {
    color: #64748b;
}

/* =====================================================
   TARJETAS GENERALES
===================================================== */

.estudiante-card {
    margin-bottom: 24px;
    border: 1px solid #e8edf5 !important;
    box-shadow: 0 8px 26px rgba(30,58,138,.06) !important;
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

/* =====================================================
   SELECTOR DE PERÍODO
===================================================== */

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

/* =====================================================
   LEYENDA DE LA GRÁFICA
===================================================== */

.estudiante-leyenda {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}

.estudiante-leyenda-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border: 1px solid #e8edf5;
    border-radius: 13px;
    background: #fff;
    color: #334155;
}

.estudiante-punto {
    display: inline-block;
    flex-shrink: 0;
    width: 13px;
    height: 13px;
    border-radius: 50%;
}

.estudiante-punto.rojo { background: #dc3545; }
.estudiante-punto.amarillo { background: #ffc107; }
.estudiante-punto.verde { background: #198754; }

/* =====================================================
   GRÁFICA DE BARRAS
===================================================== */

.estudiante-grafica-barras {
    position: relative;
    width: 100%;
    height: 360px;
}

.estudiante-grafica-nota {
    margin: 18px 0 0;
    color: #64748b;
    font-size: .85rem;
}

.estudiante-grafica-vacia {
    padding: 40px 15px;
    color: #64748b;
    text-align: center;
}

.estudiante-grafica-vacia span {
    display: block;
    margin-bottom: 12px;
    font-size: 2rem;
}

/* =====================================================
   ACCESO AL DETALLE
===================================================== */

.estudiante-desempeno-grid {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
}

.estudiante-desempeno-grid h2 {
    margin: 8px 0;
    color: #1e293b;
    font-size: 1.3rem;
    font-weight: 800;
}

.estudiante-desempeno-grid p {
    margin: 0;
    color: #64748b;
}

.estudiante-desempeno-accion {
    flex-shrink: 0;
}

.estudiante-desempeno-accion .btn {
    padding: 13px 20px;
    border: none;
    border-radius: 10px;
    background: #7041c5;
    font-weight: 700;
}

.estudiante-desempeno-accion .btn:hover {
    background: #5d32aa;
}

/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1050px) {
    .estudiante-estadisticas {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 768px) {
    .estudiante-contenido {
        padding: 18px 14px;
    }

    .estudiante-periodo-form {
        align-items: stretch;
        flex-direction: column;
    }

    .estudiante-btn-consultar {
        width: 100%;
    }

    .estudiante-leyenda {
        grid-template-columns: 1fr;
    }

    .estudiante-grafica-barras {
        height: 300px;
    }

    .estudiante-desempeno-grid {
        align-items: stretch;
        flex-direction: column;
    }

    .estudiante-desempeno-accion .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .estudiante-estadisticas {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include("../includes/footer.php"); ?>

