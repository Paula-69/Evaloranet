
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");

$mensaje = "";
$tipo_mensaje = "";

// =====================================================
// PROCESAR CAMBIO DE ESTADO
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $periodo_id = (int) ($_POST["periodo_id"] ?? 0);
    $nuevo_estado = (int) ($_POST["nuevo_estado"] ?? -1);

    if ($periodo_id <= 0) {
        $mensaje = "El período seleccionado no es válido.";
        $tipo_mensaje = "danger";

    } elseif (!in_array($nuevo_estado, [0, 1], true)) {
        $mensaje = "El estado seleccionado no es válido.";
        $tipo_mensaje = "danger";

    } else {
        $stmt = $conexion->prepare("
            SELECT id, nombre
            FROM periodos
            WHERE id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            $mensaje = "No se pudo consultar el período.";
            $tipo_mensaje = "danger";

        } else {
            $stmt->bind_param("i", $periodo_id);
            $stmt->execute();

            $resultado = $stmt->get_result();
            $periodo = $resultado->fetch_assoc();

            $stmt->close();

            if (!$periodo) {
                $mensaje = "El período no existe.";
                $tipo_mensaje = "danger";

            } else {
                $stmtUpdate = $conexion->prepare("
                    UPDATE periodos
                    SET habilitado = ?
                    WHERE id = ?
                ");

                if (!$stmtUpdate) {
                    $mensaje = "No se pudo actualizar el período.";
                    $tipo_mensaje = "danger";

                } else {
                    $stmtUpdate->bind_param(
                        "ii",
                        $nuevo_estado,
                        $periodo_id
                    );

                    if ($stmtUpdate->execute()) {
                        $nombrePeriodo = htmlspecialchars(
                            $periodo["nombre"],
                            ENT_QUOTES,
                            "UTF-8"
                        );

                        if ($nuevo_estado === 1) {
                            $mensaje = "El período " . $nombrePeriodo .
                                " fue habilitado correctamente.";
                        } else {
                            $mensaje = "El período " . $nombrePeriodo .
                                " fue deshabilitado correctamente.";
                        }

                        $tipo_mensaje = "success";

                    } else {
                        $mensaje = "No se pudo cambiar el estado del período.";
                        $tipo_mensaje = "danger";
                    }

                    $stmtUpdate->close();
                }
            }
        }
    }
}

// =====================================================
// OBTENER PERÍODOS
// =====================================================

$resultadoPeriodos = $conexion->query("
    SELECT id, nombre, habilitado
    FROM periodos
    ORDER BY id ASC
");

$periodos = [];

if ($resultadoPeriodos) {
    while ($fila = $resultadoPeriodos->fetch_assoc()) {
        $periodos[] = $fila;
    }
}

// =====================================================
// ESTRUCTURA COMPARTIDA
// =====================================================

include("../../includes/header.php");
include("../../includes/navbar.php");
?>

<div class="evalora-layout">

    <?php include("../../includes/sidebar.php"); ?>

    <main class="evalora-main periodos-page">

        <div class="periodos-contenido">

            <!-- ENCABEZADO -->

            <header class="periodos-heading">
                <div>
                    <span class="periodos-eyebrow">
                        ADMINISTRACIÓN ACADÉMICA
                    </span>

                    <h1>Administración de períodos</h1>

                    <p>
                        Controla qué períodos están disponibles para
                        registrar y modificar desempeños.
                    </p>
                </div>

                <a href="../index.php" class="periodos-btn-volver">
                    ← Volver al dashboard
                </a>
            </header>

            <!-- MENSAJE -->

            <?php if ($mensaje !== ""): ?>
                <div class="periodos-alerta periodos-alerta-<?= htmlspecialchars(
                    $tipo_mensaje,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>">
                    <span>
                        <?= $tipo_mensaje === "success" ? "✓" : "⚠" ?>
                    </span>

                    <div><?= $mensaje ?></div>
                </div>
            <?php endif; ?>

            <!-- RESUMEN -->

            <?php
            $totalPeriodos = count($periodos);
            $totalHabilitados = 0;

            foreach ($periodos as $p) {
                if ((int) $p["habilitado"] === 1) {
                    $totalHabilitados++;
                }
            }

            $totalCerrados = $totalPeriodos - $totalHabilitados;
            ?>

            <section class="periodos-resumen">

                <article class="periodos-stat">
                    <div class="periodos-stat-icono icono-azul">📚</div>
                    <div>
                        <span>Total de períodos</span>
                        <strong><?= $totalPeriodos ?></strong>
                    </div>
                </article>

                <article class="periodos-stat">
                    <div class="periodos-stat-icono icono-verde">✓</div>
                    <div>
                        <span>Habilitados</span>
                        <strong><?= $totalHabilitados ?></strong>
                    </div>
                </article>

                <article class="periodos-stat">
                    <div class="periodos-stat-icono icono-rojo">🔒</div>
                    <div>
                        <span>Deshabilitados</span>
                        <strong><?= $totalCerrados ?></strong>
                    </div>
                </article>

            </section>

            <!-- INFORMACIÓN -->

            <section class="periodos-info">
                <div class="periodos-info-icono">ℹ</div>

                <div>
                    <h2>¿Cómo funciona?</h2>
                    <p>
                        Los períodos habilitados permiten que los docentes
                        registren y modifiquen desempeños. Al deshabilitar
                        un período, se cierra el registro para los docentes.
                        Los estudiantes pueden seguir consultando los
                        resultados que ya tienen registrados.
                    </p>
                </div>
            </section>

            <!-- TABLA -->

            <section class="periodos-panel">

                <div class="periodos-panel-heading">
                    <div>
                        <span class="periodos-eyebrow">
                            CONFIGURACIÓN
                        </span>

                        <h2>Períodos académicos</h2>

                        <p>
                            Administra la disponibilidad de cada período.
                        </p>
                    </div>

                    <span class="periodos-contador">
                        <?= $totalPeriodos ?>
                        <?= $totalPeriodos === 1 ? "período" : "períodos" ?>
                    </span>
                </div>

                <?php if ($totalPeriodos > 0): ?>

                    <div class="table-responsive">
                        <table class="periodos-tabla">

                            <thead>
                                <tr>
                                    <th class="periodos-col-numero">#</th>
                                    <th>Período académico</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($periodos as $indice => $periodo): ?>

                                    <?php
                                    $habilitado = (int) $periodo["habilitado"] === 1;
                                    ?>

                                    <tr>
                                        <td class="periodos-numero">
                                            <?= $indice + 1 ?>
                                        </td>

                                        <td>
                                            <div class="periodos-nombre">
                                                <span class="periodos-fila-icono">
                                                    📅
                                                </span>

                                                <strong>
                                                    <?= htmlspecialchars(
                                                        $periodo["nombre"],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ) ?>
                                                </strong>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($habilitado): ?>
                                                <span class="periodos-estado estado-habilitado">
                                                    <span></span>
                                                    Habilitado
                                                </span>
                                            <?php else: ?>
                                                <span class="periodos-estado estado-deshabilitado">
                                                    <span></span>
                                                    Deshabilitado
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <form
                                                method="POST"
                                                action="index.php"
                                                class="periodos-form-accion"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="periodo_id"
                                                    value="<?= (int) $periodo["id"] ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="nuevo_estado"
                                                    value="<?= $habilitado ? 0 : 1 ?>"
                                                >

                                                <?php if ($habilitado): ?>
                                                    <button
                                                        type="submit"
                                                        class="periodos-btn periodos-btn-cerrar"
                                                        onclick="return confirm('¿Seguro que deseas deshabilitar este período?');"
                                                    >
                                                        🔒 Deshabilitar
                                                    </button>
                                                <?php else: ?>
                                                    <button
                                                        type="submit"
                                                        class="periodos-btn periodos-btn-abrir"
                                                        onclick="return confirm('¿Seguro que deseas habilitar este período?');"
                                                    >
                                                        🔓 Habilitar
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>
                    </div>

                <?php else: ?>

                    <div class="periodos-vacio">
                        <span>📚</span>
                        <h3>No hay períodos académicos</h3>
                        <p>
                            Todavía no se han registrado períodos en el sistema.
                        </p>
                    </div>

                <?php endif; ?>

            </section>

        </div>

    </main>
</div>

<style>
/* =====================================================
   CONTENIDO PRINCIPAL
===================================================== */

.periodos-page {
    min-width: 0;
}

.periodos-contenido {
    width: 100%;
    max-width: 1450px;
    box-sizing: border-box;
    margin: 0 auto;
    padding: 28px;
    color: #334155;
}

.periodos-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 25px;
}

.periodos-eyebrow {
    color: #7041c5;
    font-size: 0.74rem;
    font-weight: 800;
    letter-spacing: 1px;
}

.periodos-heading h1 {
    margin: 8px 0;
    color: #1e3a8a;
    font-size: clamp(1.6rem, 2.4vw, 2.1rem);
    font-weight: 800;
}

.periodos-heading p {
    max-width: 720px;
    margin: 0;
    color: #64748b;
    line-height: 1.65;
}

.periodos-btn-volver {
    flex-shrink: 0;
    padding: 11px 16px;
    border: 1px solid #dbe3ef;
    border-radius: 10px;
    background: #fff;
    color: #334155;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.2s ease;
}

.periodos-btn-volver:hover {
    background: #f1f5fb;
    color: #1d4ed8;
}

/* =====================================================
   MENSAJES
===================================================== */

.periodos-alerta {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 22px;
    padding: 15px 18px;
    border: 1px solid;
    border-radius: 12px;
}

.periodos-alerta-success {
    border-color: #bbf7d0;
    background: #f0fdf4;
    color: #166534;
}

.periodos-alerta-danger {
    border-color: #fecdd3;
    background: #fff1f2;
    color: #9f1239;
}

/* =====================================================
   TARJETAS RESUMEN
===================================================== */

.periodos-resumen {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 22px;
}

.periodos-stat {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 21px;
    border: 1px solid #e7edf5;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 7px 22px rgba(30, 58, 138, 0.045);
}

.periodos-stat-icono {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    border-radius: 14px;
    font-size: 1.3rem;
    font-weight: 800;
}

.icono-azul {
    background: #e7efff;
    color: #2458df;
}

.icono-verde {
    background: #dcfce7;
    color: #15803d;
}

.icono-rojo {
    background: #ffe4e6;
    color: #be123c;
}

.periodos-stat span {
    display: block;
    margin-bottom: 5px;
    color: #64748b;
    font-size: 0.86rem;
}

.periodos-stat strong {
    color: #1e293b;
    font-size: 1.7rem;
    font-weight: 800;
}

/* =====================================================
   INFORMACIÓN
===================================================== */

.periodos-info {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 24px;
    padding: 20px;
    border: 1px solid #dbeafe;
    border-radius: 14px;
    background: #eff6ff;
}

.periodos-info-icono {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #dbeafe;
    color: #1d4ed8;
    font-size: 1.1rem;
    font-weight: 800;
}

.periodos-info h2 {
    margin: 2px 0 7px;
    color: #1e40af;
    font-size: 1rem;
    font-weight: 800;
}

.periodos-info p {
    margin: 0;
    color: #475569;
    line-height: 1.7;
}

/* =====================================================
   PANEL Y TABLA
===================================================== */

.periodos-panel {
    overflow: hidden;
    border: 1px solid #e7edf5;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 8px 25px rgba(30, 58, 138, 0.05);
}

.periodos-panel-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 23px 25px;
    border-bottom: 1px solid #edf1f7;
}

.periodos-panel-heading h2 {
    margin: 6px 0;
    color: #1e293b;
    font-size: 1.25rem;
    font-weight: 800;
}

.periodos-panel-heading p {
    margin: 0;
    color: #64748b;
}

.periodos-contador {
    flex-shrink: 0;
    padding: 9px 12px;
    border-radius: 9px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 0.84rem;
    font-weight: 800;
}

.periodos-tabla {
    width: 100%;
    border-collapse: collapse;
}

.periodos-tabla thead th {
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #475569;
    font-size: 0.83rem;
    font-weight: 800;
    text-align: left;
    white-space: nowrap;
}

.periodos-tabla thead th.text-center,
.periodos-tabla td.text-center {
    text-align: center;
}

.periodos-tabla tbody td {
    padding: 17px 20px;
    border-bottom: 1px solid #edf1f7;
    vertical-align: middle;
}

.periodos-tabla tbody tr:last-child td {
    border-bottom: 0;
}

.periodos-tabla tbody tr {
    transition: background 0.15s ease;
}

.periodos-tabla tbody tr:hover {
    background: #f8faff;
}

.periodos-col-numero {
    width: 70px;
}

.periodos-numero {
    color: #94a3b8;
    font-weight: 700;
}

.periodos-nombre {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #1e293b;
}

.periodos-fila-icono {
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border-radius: 11px;
    background: #eff6ff;
    font-size: 1rem;
}

/* =====================================================
   ESTADOS
===================================================== */

.periodos-estado {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 8px 12px;
    border: 1px solid;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 700;
    white-space: nowrap;
}

.periodos-estado > span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.estado-habilitado {
    border-color: #bbf7d0;
    background: #f0fdf4;
    color: #166534;
}

.estado-habilitado > span {
    background: #16a34a;
}

.estado-deshabilitado {
    border-color: #fecdd3;
    background: #fff1f2;
    color: #9f1239;
}

.estado-deshabilitado > span {
    background: #e11d48;
}

/* =====================================================
   BOTONES DE ACCIÓN
===================================================== */

.periodos-form-accion {
    display: inline-block;
    margin: 0;
}

.periodos-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-width: 135px;
    padding: 10px 14px;
    border: 1px solid;
    border-radius: 9px;
    font-size: 0.84rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}

.periodos-btn-cerrar {
    border-color: #cbd5e1;
    background: #fff;
    color: #475569;
}

.periodos-btn-cerrar:hover {
    border-color: #dc3545;
    background: #dc3545;
    color: #fff;
}

.periodos-btn-abrir {
    border-color: #198754;
    background: #fff;
    color: #198754;
}

.periodos-btn-abrir:hover {
    background: #198754;
    color: #fff;
}

/* =====================================================
   ESTADO VACÍO
===================================================== */

.periodos-vacio {
    padding: 48px 20px;
    text-align: center;
}

.periodos-vacio > span {
    display: block;
    margin-bottom: 12px;
    font-size: 2.2rem;
}

.periodos-vacio h3 {
    margin-bottom: 8px;
    color: #1e293b;
    font-size: 1.15rem;
    font-weight: 800;
}

.periodos-vacio p {
    margin: 0;
    color: #64748b;
}

/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1000px) {
    .periodos-resumen {
        grid-template-columns: 1fr;
    }

    .periodos-heading {
        align-items: flex-start;
        flex-direction: column;
    }
}

@media (max-width: 700px) {
    .periodos-contenido {
        padding: 18px 13px;
    }

    .periodos-panel-heading {
        align-items: flex-start;
        flex-direction: column;
        padding: 19px;
    }

    .periodos-info {
        padding: 16px;
    }

    .periodos-tabla {
        min-width: 650px;
    }
}
</style>

<?php include("../../includes/footer.php"); ?>
