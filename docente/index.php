
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../config/config.php");
require_once("../config/seguridad/docente.php");

// =====================================================
// DATOS DEL DOCENTE
// =====================================================

$nombre = $_SESSION["nombre"] ?? "Docente";

$usuario_id = (int) (
    $_SESSION["usuario_id"]
    ?? $_SESSION["id"]
    ?? $_SESSION["user_id"]
    ?? 0
);

$docente_id = 0;
$cargas = [];
$error = "";
$perfilEncontrado = false;

// =====================================================
// BUSCAR PERFIL DEL DOCENTE
// =====================================================

if ($usuario_id <= 0) {

    $error = "La sesión de usuario no es válida.";

} else {

    $stmtDocente = $conexion->prepare("
        SELECT id
        FROM docentes
        WHERE usuario_id = ?
        LIMIT 1
    ");

    if (!$stmtDocente) {

        $error = "No fue posible consultar el perfil del docente.";

    } else {

        $stmtDocente->bind_param("i", $usuario_id);
        $stmtDocente->execute();

        $resultadoDocente = $stmtDocente->get_result();
        $datosDocente = $resultadoDocente->fetch_assoc();

        $stmtDocente->close();

        if ($datosDocente) {

            $perfilEncontrado = true;
            $docente_id = (int) $datosDocente["id"];

        } else {

            $error = "No se encontró un perfil de docente asociado a tu usuario.";

        }
    }
}

// =====================================================
// CONSULTAR CARGA ACADÉMICA
// =====================================================

if ($perfilEncontrado) {

    $stmtCargas = $conexion->prepare("
        SELECT
            ca.id,
            c.nombre AS curso,
            m.nombre AS materia
        FROM carga_academica ca
        INNER JOIN cursos c
            ON ca.curso_id = c.id
        INNER JOIN materias m
            ON ca.materia_id = m.id
        WHERE ca.docente_id = ?
        ORDER BY c.nombre ASC, m.nombre ASC
    ");

    if (!$stmtCargas) {

        $error = "No fue posible consultar tus cursos y materias.";

    } else {

        $stmtCargas->bind_param("i", $docente_id);
        $stmtCargas->execute();

        $resultadoCargas = $stmtCargas->get_result();

        while ($fila = $resultadoCargas->fetch_assoc()) {
            $cargas[] = $fila;
        }

        $stmtCargas->close();
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

    <main class="evalora-main docente-page">

        <div class="docente-contenido">

            <!-- ENCABEZADO -->

            <header class="docente-heading">
                <div>
                    <span class="docente-eyebrow">
                        SEGUIMIENTO ACADÉMICO
                    </span>

                    <h1>Panel del docente</h1>

                    <p>
                        Bienvenido,
                        <strong>
                            <?= htmlspecialchars(
                                $nombre,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </strong>.
                        Aquí puedes consultar tus cursos y materias asignados.
                    </p>
                </div>

                <div class="docente-heading-icon" aria-hidden="true">
                    🎓
                </div>
            </header>

            <?php if ($error !== ""): ?>

                <section class="docente-mensaje docente-mensaje-error">
                    <span aria-hidden="true">⚠️</span>

                    <div>
                        <strong>No se pudo cargar el panel</strong>
                        <p>
                            <?= htmlspecialchars(
                                $error,
                                ENT_QUOTES,
                                "UTF-8"
                            ) ?>
                        </p>
                    </div>
                </section>

            <?php elseif ($perfilEncontrado): ?>

                <!-- TARJETAS DE RESUMEN -->

                <section class="docente-estadisticas">

                    <article class="docente-stat">
                        <div class="docente-stat-icon morado">📚</div>

                        <div>
                            <p>Mis asignaciones</p>
                            <h2><?= count($cargas) ?></h2>
                            <small>
                                Cursos y materias asignados
                            </small>
                        </div>
                    </article>

                    <article class="docente-stat">
                        <div class="docente-stat-icon azul">👨‍🏫</div>

                        <div>
                            <p>Mi perfil</p>
                            <h2 class="docente-nombre-stat">
                                <?= htmlspecialchars(
                                    $nombre,
                                    ENT_QUOTES,
                                    "UTF-8"
                                ) ?>
                            </h2>
                            <small>
                                Sesión de docente activa
                            </small>
                        </div>
                    </article>

                </section>

                <!-- TABLA DE CURSOS Y MATERIAS -->

                <section class="docente-panel">

                    <div class="docente-panel-header">
                        <div>
                            <span class="docente-eyebrow">
                                ORGANIZACIÓN ACADÉMICA
                            </span>

                            <h2>Mis cursos y materias</h2>

                            <p>
                                Accede al listado de estudiantes de cada asignación.
                            </p>
                        </div>

                        <span class="docente-total">
                            <?= count($cargas) ?>
                            <?= count($cargas) === 1
                                ? "asignación"
                                : "asignaciones" ?>
                        </span>
                    </div>

                    <?php if (count($cargas) > 0): ?>

                        <div class="table-responsive docente-tabla-contenedor">

                            <table class="table docente-tabla align-middle">

                                <thead>
                                    <tr>
                                        <th class="docente-col-numero">#</th>
                                        <th>Curso</th>
                                        <th>Materia</th>
                                        <th class="docente-col-accion">Acción</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php foreach ($cargas as $indice => $fila): ?>

                                        <tr>
                                            <td class="docente-numero">
                                                <?= $indice + 1 ?>
                                            </td>

                                            <td>
                                                <span class="docente-curso">
                                                    <?= htmlspecialchars(
                                                        $fila["curso"],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ) ?>
                                                </span>
                                            </td>

                                            <td>
                                                <span class="docente-materia">
                                                    <?= htmlspecialchars(
                                                        $fila["materia"],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ) ?>
                                                </span>
                                            </td>

                                            <td class="docente-accion">
                                                <a
                                                    href="desempeno/index.php?id=<?= (int) $fila["id"] ?>"
                                                    class="docente-btn-ver"
                                                >
                                                    <span aria-hidden="true">👁️</span>
                                                    Ver estudiantes
                                                    <span aria-hidden="true">→</span>
                                                </a>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="docente-vacio">
                            <div class="docente-vacio-icono">📚</div>

                            <h3>No tienes asignaciones todavía</h3>

                            <p>
                                Cuando el coordinador te asigne cursos y materias,
                                aparecerán en esta sección.
                            </p>
                        </div>

                    <?php endif; ?>

                </section>

            <?php endif; ?>

        </div>

    </main>

</div>

<style>
/* =====================================================
   PANEL DOCENTE - ESTILO EVALORANET
===================================================== */

.docente-page {
    min-width: 0;
}

.docente-contenido {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 28px;
    box-sizing: border-box;
}

.docente-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 26px;
}

.docente-eyebrow {
    display: inline-block;
    color: #7041c5;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 1.1px;
}

.docente-heading h1 {
    margin: 7px 0 9px;
    color: #1e3a8a;
    font-size: clamp(1.7rem, 2.5vw, 2.2rem);
    font-weight: 800;
}

.docente-heading p {
    margin: 0;
    color: #64748b;
    line-height: 1.7;
}

.docente-heading p strong {
    color: #334155;
}

.docente-heading-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 64px;
    height: 64px;
    border-radius: 18px;
    background: #eee8ff;
    font-size: 1.9rem;
}

.docente-estadisticas {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 26px;
}

.docente-stat {
    display: flex;
    align-items: center;
    gap: 18px;
    min-width: 0;
    padding: 23px;
    border: 1px solid #e8edf5;
    border-top: 4px solid #2458df;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 8px 26px rgba(30, 58, 138, 0.06);
}

.docente-stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 50px;
    height: 50px;
    border-radius: 15px;
    font-size: 1.45rem;
}

.docente-stat-icon.morado {
    background: #eee8ff;
}

.docente-stat-icon.azul {
    background: #e4edff;
}

.docente-stat p {
    margin: 0 0 5px;
    color: #64748b;
    font-size: 0.9rem;
}

.docente-stat h2 {
    margin: 0 0 5px;
    color: #1e293b;
    font-size: 1.9rem;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.docente-stat .docente-nombre-stat {
    font-size: 1.15rem;
}

.docente-stat small {
    color: #64748b;
}

.docente-panel {
    overflow: hidden;
    border: 1px solid #e8edf5;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 8px 26px rgba(30, 58, 138, 0.06);
}

.docente-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 25px;
    border-bottom: 1px solid #edf1f7;
}

.docente-panel-header h2 {
    margin: 7px 0;
    color: #1e293b;
    font-size: 1.3rem;
    font-weight: 800;
}

.docente-panel-header p {
    margin: 0;
    color: #64748b;
}

.docente-total {
    flex-shrink: 0;
    padding: 9px 13px;
    border-radius: 10px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 0.85rem;
    font-weight: 700;
}

.docente-tabla-contenedor {
    width: 100%;
}

.docente-tabla {
    min-width: 650px;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.docente-tabla thead th {
    padding: 17px 20px;
    border-bottom: 1px solid #e2e8f0;
    background: #f1f5fb;
    color: #334155;
    font-size: 0.87rem;
    font-weight: 800;
    white-space: nowrap;
}

.docente-tabla tbody td {
    padding: 18px 20px;
    border-bottom: 1px solid #edf1f7;
    color: #475569;
}

.docente-tabla tbody tr:last-child td {
    border-bottom: 0;
}

.docente-tabla tbody tr:hover {
    background: #f8faff;
}

.docente-col-numero {
    width: 65px;
}

.docente-col-accion {
    width: 205px;
}

.docente-numero {
    color: #94a3b8 !important;
    font-weight: 700;
}

.docente-curso {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 8px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 0.88rem;
    font-weight: 700;
}

.docente-materia {
    color: #1e293b;
    font-weight: 600;
}

.docente-btn-ver {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 13px;
    border-radius: 10px;
    background: #7041c5;
    color: #fff !important;
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none !important;
    white-space: nowrap;
    transition: background 0.2s ease, transform 0.2s ease;
}

.docente-btn-ver:hover {
    background: #5d32aa;
    color: #fff !important;
    transform: translateY(-1px);
}

.docente-vacio {
    padding: 48px 22px;
    text-align: center;
}

.docente-vacio-icono {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 65px;
    height: 65px;
    margin: 0 auto 16px;
    border-radius: 20px;
    background: #eff6ff;
    font-size: 1.8rem;
}

.docente-vacio h3 {
    margin-bottom: 8px;
    color: #1e293b;
    font-size: 1.15rem;
    font-weight: 800;
}

.docente-vacio p {
    max-width: 460px;
    margin: 0 auto;
    color: #64748b;
    line-height: 1.7;
}

.docente-mensaje {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 20px;
    border-radius: 14px;
}

.docente-mensaje > span {
    font-size: 1.5rem;
}

.docente-mensaje strong {
    display: block;
    margin-bottom: 5px;
}

.docente-mensaje p {
    margin: 0;
}

.docente-mensaje-error {
    border: 1px solid #fecdd3;
    background: #fff1f2;
    color: #9f1239;
}

@media (max-width: 900px) {
    .docente-contenido {
        padding: 22px;
    }

    .docente-estadisticas {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .docente-contenido {
        padding: 17px 13px;
    }

    .docente-heading-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        font-size: 1.4rem;
    }

    .docente-panel-header {
        align-items: flex-start;
        flex-direction: column;
        padding: 20px;
    }

    .docente-stat {
        padding: 19px;
    }

    .docente-tabla thead th,
    .docente-tabla tbody td {
        padding: 14px;
    }
}
</style>

<?php include("../includes/footer.php"); ?>
