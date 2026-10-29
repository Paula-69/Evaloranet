
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../../config/config.php");
require_once("../../config/seguridad/docente.php");

// ======================================================
// VALIDAR CARGA ACADÉMICA
// ======================================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: ../index.php");
    exit();
}

$carga_id = (int) $_GET["id"];

$usuario_id = (int) (
    $_SESSION["usuario_id"]
    ?? $_SESSION["id"]
    ?? $_SESSION["user_id"]
    ?? 0
);

if ($usuario_id <= 0) {
    header("Location: ../index.php");
    exit();
}

// ======================================================
// OBTENER DOCENTE
// ======================================================

$stmt = $conexion->prepare("
    SELECT id
    FROM docentes
    WHERE usuario_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("No fue posible consultar el docente.");
}

$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$docente = $resultado->fetch_assoc();
$stmt->close();

if (!$docente) {
    die("No se encontró el docente.");
}

$docente_id = (int) $docente["id"];

// ======================================================
// OBTENER CARGA ACADÉMICA
// ======================================================

$stmt = $conexion->prepare("
    SELECT
        ca.id,
        ca.docente_id,
        ca.curso_id,
        ca.materia_id,
        c.nombre AS curso,
        m.nombre AS materia
    FROM carga_academica ca
    INNER JOIN cursos c ON ca.curso_id = c.id
    INNER JOIN materias m ON ca.materia_id = m.id
    WHERE ca.id = ?
      AND ca.docente_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Error preparando la carga académica.");
}

$stmt->bind_param("ii", $carga_id, $docente_id);
$stmt->execute();
$resultado = $stmt->get_result();
$carga = $resultado->fetch_assoc();
$stmt->close();

if (!$carga) {
    die("Esta carga académica no pertenece al docente.");
}

// ======================================================
// OBTENER PERÍODOS
// ======================================================

$periodos = [];

$resultadoPeriodos = $conexion->query("
    SELECT id, nombre, habilitado
    FROM periodos
    ORDER BY id ASC
");

if ($resultadoPeriodos) {
    while ($fila = $resultadoPeriodos->fetch_assoc()) {
        $periodos[] = $fila;
    }
}

$periodo_id = isset($_GET["periodo_id"])
    ? (int) $_GET["periodo_id"]
    : 0;

if ($periodo_id <= 0) {
    foreach ($periodos as $p) {
        if ((int) $p["habilitado"] === 1) {
            $periodo_id = (int) $p["id"];
            break;
        }
    }
}

// ======================================================
// VALIDAR PERÍODO
// ======================================================

$periodo_actual = null;
$periodo_bloqueado = true;

if ($periodo_id > 0) {
    $stmt = $conexion->prepare("
        SELECT id, nombre, habilitado
        FROM periodos
        WHERE id = ?
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->bind_param("i", $periodo_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $periodo_actual = $resultado->fetch_assoc();
        $stmt->close();
    }
}

if (
    $periodo_actual &&
    (int) $periodo_actual["habilitado"] === 1
) {
    $periodo_bloqueado = false;
}

// ======================================================
// OBTENER ESTUDIANTES ACTIVOS
// ======================================================

$estudiantes = [];

$stmt = $conexion->prepare("
    SELECT
        e.id,
        u.nombres,
        u.apellidos,
        u.documento
    FROM estudiantes e
    INNER JOIN usuarios u ON e.usuario_id = u.id
    WHERE e.curso_id = ?
      AND e.estado = 'Activo'
    ORDER BY u.apellidos ASC, u.nombres ASC
");

if (!$stmt) {
    die("Error preparando la consulta de estudiantes.");
}

$stmt->bind_param("i", $carga["curso_id"]);
$stmt->execute();
$resultado = $stmt->get_result();

while ($fila = $resultado->fetch_assoc()) {
    $estudiantes[] = $fila;
}

$stmt->close();

// ======================================================
// OBTENER DESEMPEÑOS EXISTENTES
// ======================================================

$desempenos = [];

if ($periodo_id > 0) {
    $stmt = $conexion->prepare("
        SELECT estudiante_id, color_id
        FROM desempeno_estudiantes
        WHERE carga_academica_id = ?
          AND periodo_id = ?
    ");

    if ($stmt) {
        $stmt->bind_param("ii", $carga_id, $periodo_id);
        $stmt->execute();
        $resultado = $stmt->get_result();

        while ($fila = $resultado->fetch_assoc()) {
            $desempenos[(int) $fila["estudiante_id"]] =
                (int) $fila["color_id"];
        }

        $stmt->close();
    }
}

// ======================================================
// ESTRUCTURA COMPARTIDA
// ======================================================

include("../../includes/header.php");
include("../../includes/navbar.php");
?>

<div class="evalora-layout">

    <?php include("../../includes/sidebar.php"); ?>

    <main class="evalora-main docente-desempeno-page">

        <div class="dd-contenido">

            <header class="dd-heading">
                <div>
                    <span class="dd-eyebrow">SEGUIMIENTO ACADÉMICO</span>
                    <h1>Desempeño de estudiantes</h1>
                    <p>Registra y consulta el desempeño de tus estudiantes.</p>
                </div>

                <a href="../index.php" class="dd-btn-volver">
                    ← Volver a mis cursos
                </a>
            </header>

            <!-- RESUMEN DEL CURSO -->

            <section class="dd-resumen">

                <article class="dd-resumen-card">
                    <div class="dd-icono dd-icono-azul">🎓</div>
                    <div>
                        <span>Curso asignado</span>
                        <h2><?= htmlspecialchars($carga["curso"], ENT_QUOTES, "UTF-8") ?></h2>
                    </div>
                </article>

                <article class="dd-resumen-card">
                    <div class="dd-icono dd-icono-morado">📚</div>
                    <div>
                        <span>Materia</span>
                        <h2><?= htmlspecialchars($carga["materia"], ENT_QUOTES, "UTF-8") ?></h2>
                    </div>
                </article>

                <article class="dd-resumen-card">
                    <div class="dd-icono dd-icono-verde">👥</div>
                    <div>
                        <span>Estudiantes activos</span>
                        <h2><?= count($estudiantes) ?></h2>
                    </div>
                </article>

            </section>

            <!-- MENSAJES -->

            <?php if (isset($_GET["error"])): ?>
                <div class="dd-alerta dd-alerta-error">
                    <?= htmlspecialchars($_GET["error"], ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET["success"])): ?>
                <div class="dd-alerta dd-alerta-exito">
                    ✓ <?= htmlspecialchars($_GET["success"], ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <!-- SELECTOR DE PERÍODO -->

            <section class="dd-panel">

                <div class="dd-panel-heading">
                    <div>
                        <span class="dd-eyebrow">CONFIGURACIÓN</span>
                        <h2>Período académico</h2>
                        <p>Selecciona un período habilitado para registrar.</p>
                    </div>

                    <span class="dd-heading-emoji">📅</span>
                </div>

                <?php
                $periodos_habilitados = array_filter(
                    $periodos,
                    fn($p) => (int) $p["habilitado"] === 1
                );
                ?>

                <?php if (count($periodos_habilitados) > 0): ?>

                    <form method="GET" action="index.php" class="dd-periodo-form">

                        <input type="hidden" name="id" value="<?= $carga_id ?>">

                        <div class="dd-periodo-campo">
                            <label for="periodo_id">Período disponible</label>

                            <select
                                name="periodo_id"
                                id="periodo_id"
                                class="form-select"
                                required
                            >
                                <?php foreach ($periodos_habilitados as $p): ?>
                                    <option
                                        value="<?= (int) $p["id"] ?>"
                                        <?= (int) $p["id"] === $periodo_id ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($p["nombre"], ENT_QUOTES, "UTF-8") ?>
                                        — Habilitado
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="dd-btn-consultar">
                            🔎 Consultar período
                        </button>

                    </form>

                <?php else: ?>

                    <div class="dd-alerta dd-alerta-aviso">
                        No hay períodos habilitados actualmente.
                    </div>

                <?php endif; ?>

                <?php if ($periodo_actual && !$periodo_bloqueado): ?>
                    <div class="dd-periodo-actual">
                        <span class="dd-punto-verde"></span>
                        Trabajando en:
                        <strong>
                            <?= htmlspecialchars($periodo_actual["nombre"], ENT_QUOTES, "UTF-8") ?>
                        </strong>
                    </div>
                <?php endif; ?>

            </section>

            <!-- TABLA DE DESEMPEÑOS -->

            <?php if ($periodo_bloqueado): ?>

                <div class="dd-alerta dd-alerta-aviso">
                    <strong>🔒 Período no disponible</strong>
                    <p>Selecciona un período habilitado para registrar o modificar desempeños.</p>
                </div>

            <?php else: ?>

                <section class="dd-panel">

                    <div class="dd-panel-heading">
                        <div>
                            <span class="dd-eyebrow">REGISTRO ACADÉMICO</span>
                            <h2>Seguimiento de estudiantes</h2>
                            <p>
                                Período:
                                <strong>
                                    <?= htmlspecialchars($periodo_actual["nombre"], ENT_QUOTES, "UTF-8") ?>
                                </strong>
                            </p>
                        </div>

                        <span class="dd-contador">
                            <?= count($estudiantes) ?>
                            <?= count($estudiantes) === 1 ? "estudiante" : "estudiantes" ?>
                        </span>
                    </div>

                    <?php if (count($estudiantes) > 0): ?>

                        <form
                            action="../seguimiento/guardar.php"
                            method="POST"
                            id="formDesempeno"
                        >

                            <input type="hidden" name="curso_id" value="<?= (int) $carga["curso_id"] ?>">
                            <input type="hidden" name="materia_id" value="<?= (int) $carga["materia_id"] ?>">
                            <input type="hidden" name="periodo_id" value="<?= $periodo_id ?>">

                            <div class="table-responsive dd-tabla-contenedor">
                                <table class="table dd-tabla align-middle">

                                    <thead>
                                        <tr>
                                            <th class="dd-col-numero">#</th>
                                            <th>Estudiante</th>
                                            <th>Documento</th>
                                            <th class="dd-col-desempeno">Desempeño</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($estudiantes as $numero => $estudiante): ?>

                                            <?php
                                            $idEstudiante = (int) $estudiante["id"];
                                            $colorActual = $desempenos[$idEstudiante] ?? 0;
                                            ?>

                                            <tr>
                                                <td class="dd-numero"><?= $numero + 1 ?></td>

                                                <td>
                                                    <strong class="dd-nombre">
                                                        <?= htmlspecialchars(
                                                            $estudiante["apellidos"] . ", " . $estudiante["nombres"],
                                                            ENT_QUOTES,
                                                            "UTF-8"
                                                        ) ?>
                                                    </strong>
                                                </td>

                                                <td class="dd-documento">
                                                    <?= htmlspecialchars($estudiante["documento"], ENT_QUOTES, "UTF-8") ?>
                                                </td>

                                                <td>
                                                    <div class="dd-opciones">

                                                        <button
                                                            type="button"
                                                            class="dd-opcion dd-bajo <?= $colorActual === 1 ? "seleccionado" : "" ?>"
                                                            data-color="1"
                                                            aria-pressed="<?= $colorActual === 1 ? "true" : "false" ?>"
                                                        >
                                                            <span class="dd-circulo"></span>
                                                            Bajo
                                                        </button>

                                                        <button
                                                            type="button"
                                                            class="dd-opcion dd-basico <?= $colorActual === 2 ? "seleccionado" : "" ?>"
                                                            data-color="2"
                                                            aria-pressed="<?= $colorActual === 2 ? "true" : "false" ?>"
                                                        >
                                                            <span class="dd-circulo"></span>
                                                            Básico
                                                        </button>

                                                        <button
                                                            type="button"
                                                            class="dd-opcion dd-alto <?= $colorActual === 3 ? "seleccionado" : "" ?>"
                                                            data-color="3"
                                                            aria-pressed="<?= $colorActual === 3 ? "true" : "false" ?>"
                                                        >
                                                            <span class="dd-circulo"></span>
                                                            Alto
                                                        </button>

                                                        <input
                                                            type="hidden"
                                                            name="color[<?= $idEstudiante ?>]"
                                                            value="<?= $colorActual ?>"
                                                            class="dd-input-color"
                                                        >

                                                    </div>
                                                </td>
                                            </tr>

                                        <?php endforeach; ?>
                                    </tbody>

                                </table>
                            </div>

                            <div class="dd-form-footer">
                                <span>Selecciona un nivel para cada estudiante.</span>

                                <button type="submit" class="dd-btn-guardar">
                                    💾 Guardar seguimiento
                                </button>
                            </div>

                        </form>

                    <?php else: ?>

                        <div class="dd-vacio">
                            <span>📚</span>
                            <h3>No hay estudiantes activos</h3>
                            <p>No se encontraron estudiantes activos en este curso.</p>
                        </div>

                    <?php endif; ?>

                </section>

            <?php endif; ?>

        </div>
    </main>
</div>

<style>
.docente-desempeno-page {
    min-width: 0;
}

.dd-contenido {
    box-sizing: border-box;
    width: 100%;
    max-width: 1450px;
    margin: 0 auto;
    padding: 28px;
    color: #334155;
}

.dd-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 25px;
}

.dd-eyebrow {
    color: #7041c5;
    font-size: .74rem;
    font-weight: 800;
    letter-spacing: 1px;
}

.dd-heading h1 {
    margin: 7px 0;
    color: #1e3a8a;
    font-size: clamp(1.65rem, 2.4vw, 2.15rem);
    font-weight: 800;
}

.dd-heading p,
.dd-panel-heading p {
    margin: 0;
    color: #64748b;
}

.dd-btn-volver {
    flex-shrink: 0;
    padding: 11px 15px;
    border: 1px solid #dbe3ef;
    border-radius: 10px;
    background: #fff;
    color: #334155;
    font-weight: 700;
    text-decoration: none;
}

.dd-btn-volver:hover {
    background: #f1f5fb;
    color: #1d4ed8;
}

.dd-resumen {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 24px;
}

.dd-resumen-card {
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 0;
    padding: 20px;
    border: 1px solid #e8edf5;
    border-top: 4px solid #2458df;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 8px 25px rgba(30,58,138,.05);
}

.dd-icono {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    border-radius: 14px;
    font-size: 1.35rem;
}

.dd-icono-azul { background: #e4edff; }
.dd-icono-morado { background: #eee8ff; }
.dd-icono-verde { background: #dcfce7; }

.dd-resumen-card span {
    color: #64748b;
    font-size: .85rem;
}

.dd-resumen-card h2 {
    margin: 5px 0 0;
    color: #1e293b;
    font-size: 1.12rem;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.dd-panel {
    margin-bottom: 24px;
    overflow: hidden;
    border: 1px solid #e8edf5;
    border-radius: 17px;
    background: #fff;
    box-shadow: 0 8px 25px rgba(30,58,138,.05);
}

.dd-panel-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 23px 25px;
    border-bottom: 1px solid #edf1f7;
}

.dd-panel-heading h2 {
    margin: 6px 0 7px;
    color: #1e293b;
    font-size: 1.25rem;
    font-weight: 800;
}

.dd-heading-emoji {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 46px;
    height: 46px;
    border-radius: 13px;
    background: #e8efff;
    font-size: 1.3rem;
}

.dd-periodo-form {
    display: flex;
    align-items: end;
    gap: 14px;
    padding: 23px 25px;
}

.dd-periodo-campo {
    flex: 1;
    min-width: 0;
}

.dd-periodo-campo label {
    display: block;
    margin-bottom: 7px;
    color: #334155;
    font-size: .9rem;
    font-weight: 700;
}

.dd-periodo-campo select {
    min-height: 45px;
    border-color: #dbe3ef;
    border-radius: 10px;
}

.dd-btn-consultar {
    min-height: 45px;
    padding: 10px 18px;
    border: 0;
    border-radius: 10px;
    background: #2458df;
    color: #fff;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
}

.dd-btn-consultar:hover { background: #1d46b4; }

.dd-periodo-actual {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0 25px 23px;
    padding: 13px 15px;
    border-radius: 10px;
    background: #eff6ff;
    color: #334155;
}

.dd-punto-verde {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #198754;
}

.dd-periodo-actual strong { color: #1d4ed8; }

.dd-alerta {
    margin-bottom: 20px;
    padding: 16px 18px;
    border-radius: 12px;
}

.dd-alerta p { margin: 7px 0 0; }
.dd-alerta-error { border: 1px solid #fecdd3; background: #fff1f2; color: #9f1239; }
.dd-alerta-exito { border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; }
.dd-alerta-aviso { border: 1px solid #fde68a; background: #fffbeb; color: #92400e; }

.dd-contador {
    flex-shrink: 0;
    padding: 8px 12px;
    border-radius: 9px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: .84rem;
    font-weight: 800;
}

.dd-tabla-contenedor { width: 100%; }

.dd-tabla {
    min-width: 760px;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.dd-tabla thead th {
    padding: 16px 18px;
    border-bottom: 1px solid #e2e8f0;
    background: #f1f5fb;
    color: #334155;
    font-size: .86rem;
    font-weight: 800;
    white-space: nowrap;
}

.dd-tabla tbody td {
    padding: 15px 18px;
    border-bottom: 1px solid #edf1f7;
    vertical-align: middle;
}

.dd-tabla tbody tr:last-child td { border-bottom: 0; }
.dd-tabla tbody tr:hover { background: #f8faff; }
.dd-col-numero { width: 55px; }
.dd-col-desempeno { min-width: 350px; }
.dd-numero { color: #94a3b8; font-weight: 700; }
.dd-nombre { color: #1e293b; }
.dd-documento { color: #64748b; white-space: nowrap; }

.dd-opciones {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 8px;
}

/* BOTONES: transparentes hasta pasar el cursor o seleccionar */

.dd-opciones .dd-opcion {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-width: 96px;
    padding: 9px 12px;
    border: 2px solid;
    border-radius: 10px;
    background: transparent;
    box-shadow: none;
    font-size: .85rem;
    font-weight: 700;
    cursor: pointer;
    transition: background-color .2s ease, color .2s ease;
}

.dd-opciones .dd-bajo {
    border-color: #dc3545;
    background: transparent;
    color: #dc3545;
}

.dd-opciones .dd-bajo:hover,
.dd-opciones .dd-bajo.seleccionado {
    background: #dc3545;
    color: #fff;
}

.dd-opciones .dd-basico {
    border-color: #ffc107;
    background: transparent;
    color: #b38600;
}

.dd-opciones .dd-basico:hover,
.dd-opciones .dd-basico.seleccionado {
    background: #ffc107;
    color: #212529;
}

.dd-opciones .dd-alto {
    border-color: #198754;
    background: transparent;
    color: #198754;
}

.dd-opciones .dd-alto:hover,
.dd-opciones .dd-alto.seleccionado {
    background: #198754;
    color: #fff;
}

.dd-opciones .dd-circulo {
    display: inline-block;
    width: 17px;
    height: 17px;
    flex-shrink: 0;
    border: 2px solid currentColor;
    border-radius: 50%;
    background: transparent;
}

/* CORRECCIÓN: círculo blanco en cualquier botón seleccionado */
.dd-opciones .dd-opcion.seleccionado .dd-circulo {
    background: #fff !important;
    border-color: #fff !important;
}

/* En hover sin seleccionar, el círculo permanece transparente */
.dd-opciones .dd-opcion:hover:not(.seleccionado) .dd-circulo {
    background: transparent;
}

.dd-form-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 20px 25px;
    border-top: 1px solid #edf1f7;
}

.dd-form-footer > span {
    color: #64748b;
    font-size: .88rem;
}

.dd-btn-guardar {
    padding: 12px 19px;
    border: 0;
    border-radius: 10px;
    background: #198754;
    color: #fff;
    font-weight: 800;
    cursor: pointer;
}

.dd-btn-guardar:hover { background: #157347; }

.dd-vacio {
    padding: 45px 20px;
    text-align: center;
}

.dd-vacio > span {
    display: block;
    margin-bottom: 12px;
    font-size: 2.1rem;
}

.dd-vacio h3 {
    color: #1e293b;
    font-size: 1.15rem;
    font-weight: 800;
}

.dd-vacio p {
    margin-bottom: 0;
    color: #64748b;
}

@media (max-width: 1000px) {
    .dd-resumen { grid-template-columns: 1fr; }
}

@media (max-width: 700px) {
    .dd-contenido { padding: 18px 13px; }

    .dd-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .dd-periodo-form {
        align-items: stretch;
        flex-direction: column;
        padding: 18px;
    }

    .dd-btn-consultar { width: 100%; }
    .dd-panel-heading { padding: 19px; }
    .dd-periodo-actual { margin: 0 18px 18px; }

    .dd-form-footer {
        align-items: stretch;
        flex-direction: column;
        padding: 18px;
    }

    .dd-btn-guardar { width: 100%; }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const botones = document.querySelectorAll(".dd-opciones .dd-opcion");

    botones.forEach(function (boton) {
        boton.addEventListener("click", function () {

            const fila = boton.closest("tr");
            if (!fila) return;

            fila.querySelectorAll(".dd-opcion").forEach(function (otro) {
                otro.classList.remove("seleccionado");
                otro.setAttribute("aria-pressed", "false");
            });

            boton.classList.add("seleccionado");
            boton.setAttribute("aria-pressed", "true");

            const input = fila.querySelector(".dd-input-color");

            if (input) {
                input.value = boton.dataset.color;
            }
        });
    });

    const formulario = document.getElementById("formDesempeno");

    if (formulario) {
        formulario.addEventListener("submit", function (evento) {

            const inputs = formulario.querySelectorAll(".dd-input-color");

            for (const input of inputs) {
                if (!["1", "2", "3"].includes(input.value)) {
                    evento.preventDefault();
                    alert("Selecciona Bajo, Básico o Alto para cada estudiante antes de guardar.");
                    return;
                }
            }
        });
    }
});
</script>

<?php include("../../includes/footer.php"); ?>
