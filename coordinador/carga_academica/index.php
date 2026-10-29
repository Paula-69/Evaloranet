
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");

// =========================================================
// GUARDAR NUEVA CARGA ACADÉMICA
// =========================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $docente_id = (int) ($_POST["docente_id"] ?? 0);
    $curso_id = (int) ($_POST["curso_id"] ?? 0);
    $materia_id = (int) ($_POST["materia_id"] ?? 0);

    if ($docente_id <= 0 || $curso_id <= 0 || $materia_id <= 0) {
        header("Location: index.php?error=1");
        exit();
    }

    $verificar = $conexion->prepare("
        SELECT id
        FROM carga_academica
        WHERE docente_id = ?
          AND curso_id = ?
          AND materia_id = ?
    ");

    if (!$verificar) {
        header("Location: index.php?error=2");
        exit();
    }

    $verificar->bind_param("iii", $docente_id, $curso_id, $materia_id);
    $verificar->execute();

    $resultadoVerificar = $verificar->get_result();

    if ($resultadoVerificar->num_rows > 0) {
        $verificar->close();
        header("Location: index.php?duplicado=1");
        exit();
    }

    $verificar->close();

    $insertar = $conexion->prepare("
        INSERT INTO carga_academica (docente_id, curso_id, materia_id)
        VALUES (?, ?, ?)
    ");

    if (!$insertar) {
        header("Location: index.php?error=2");
        exit();
    }

    $insertar->bind_param("iii", $docente_id, $curso_id, $materia_id);

    if ($insertar->execute()) {
        $insertar->close();
        header("Location: index.php?success=1");
        exit();
    }

    $insertar->close();
    header("Location: index.php?error=2");
    exit();
}

// =========================================================
// ELIMINAR CARGA ACADÉMICA
// =========================================================

if (isset($_GET["eliminar"]) && is_numeric($_GET["eliminar"])) {

    $id = (int) $_GET["eliminar"];

    $eliminar = $conexion->prepare("
        DELETE FROM carga_academica
        WHERE id = ?
    ");

    if ($eliminar) {
        $eliminar->bind_param("i", $id);
        $eliminar->execute();
        $eliminar->close();
    }

    header("Location: index.php?eliminado=1");
    exit();
}

// =========================================================
// CONSULTAS
// =========================================================

$docentes = $conexion->query("
    SELECT d.id, u.nombres, u.apellidos, u.documento
    FROM docentes d
    INNER JOIN usuarios u ON d.usuario_id = u.id
    WHERE d.estado = 'Activo'
    ORDER BY u.apellidos, u.nombres
");

$cursos = $conexion->query("
    SELECT id, nombre
    FROM cursos
    ORDER BY nombre
");

$materias = $conexion->query("
    SELECT id, nombre
    FROM materias
    ORDER BY nombre
");

$asignaciones = $conexion->query("
    SELECT
        ca.id,
        ca.docente_id,
        ca.curso_id,
        ca.materia_id,
        u.nombres AS docente_nombres,
        u.apellidos AS docente_apellidos,
        u.documento AS docente_documento,
        c.nombre AS curso_nombre,
        m.nombre AS materia_nombre
    FROM carga_academica ca
    INNER JOIN docentes d ON ca.docente_id = d.id
    INNER JOIN usuarios u ON d.usuario_id = u.id
    INNER JOIN cursos c ON ca.curso_id = c.id
    INNER JOIN materias m ON ca.materia_id = m.id
    ORDER BY c.nombre, m.nombre, u.apellidos, u.nombres
");

$filtroDocentes = $conexion->query("
    SELECT d.id, u.nombres, u.apellidos
    FROM docentes d
    INNER JOIN usuarios u ON d.usuario_id = u.id
    WHERE d.estado = 'Activo'
    ORDER BY u.apellidos, u.nombres
");

$filtroCursos = $conexion->query("
    SELECT id, nombre
    FROM cursos
    ORDER BY nombre
");

$filtroMaterias = $conexion->query("
    SELECT id, nombre
    FROM materias
    ORDER BY nombre
");

include("../../includes/header.php");
include("../../includes/navbar.php");

?>

<div class="evalora-layout">

    <!-- MENÚ LATERAL -->
    <?php include("../../includes/sidebar.php"); ?>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="evalora-main carga-page">

        <div class="carga-contenido">

            <!-- ENCABEZADO -->
            <header class="carga-heading">
                <div class="carga-heading-icon" aria-hidden="true">▦</div>

                <div>
                    <h1>Carga académica</h1>
                    <p>
                        Administra las asignaciones de docentes, cursos y materias.
                    </p>
                </div>
            </header>

            <!-- MENSAJES -->
            <?php if (isset($_GET["success"])): ?>
                <div class="alert alert-success" role="alert">
                    La carga académica fue asignada correctamente.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET["duplicado"])): ?>
                <div class="alert alert-warning" role="alert">
                    Esta asignación ya existe.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET["error"])): ?>
                <div class="alert alert-danger" role="alert">
                    <?php
                    if ($_GET["error"] == "1") {
                        echo "Debe seleccionar docente, curso y materia.";
                    } else {
                        echo "No fue posible guardar la asignación.";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET["eliminado"])): ?>
                <div class="alert alert-success" role="alert">
                    La asignación fue eliminada correctamente.
                </div>
            <?php endif; ?>

            <!-- NUEVA ASIGNACIÓN -->
            <section class="card carga-card">

                <div class="card-header carga-card-header">
                    <div class="carga-section-icon" aria-hidden="true">＋</div>

                    <div>
                        <h2>Nueva asignación</h2>
                        <p>Selecciona el docente, el curso y la materia.</p>
                    </div>
                </div>

                <div class="card-body">

                    <form method="POST" action="index.php">

                        <div class="row g-3">

                            <div class="col-12 col-md-4">
                                <label for="docente_id" class="form-label">
                                    Docente
                                </label>

                                <select
                                    name="docente_id"
                                    id="docente_id"
                                    class="form-select"
                                    required
                                >
                                    <option value="">Seleccione un docente</option>

                                    <?php if ($docentes): ?>
                                        <?php while ($docente = $docentes->fetch_assoc()): ?>
                                            <option value="<?= (int) $docente["id"] ?>">
                                                <?= htmlspecialchars(
                                                    $docente["nombres"] . " " .
                                                    $docente["apellidos"] . " — " .
                                                    $docente["documento"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="curso_id" class="form-label">
                                    Curso
                                </label>

                                <select
                                    name="curso_id"
                                    id="curso_id"
                                    class="form-select"
                                    required
                                >
                                    <option value="">Seleccione un curso</option>

                                    <?php if ($cursos): ?>
                                        <?php while ($curso = $cursos->fetch_assoc()): ?>
                                            <option value="<?= (int) $curso["id"] ?>">
                                                <?= htmlspecialchars(
                                                    $curso["nombre"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="materia_id" class="form-label">
                                    Materia
                                </label>

                                <select
                                    name="materia_id"
                                    id="materia_id"
                                    class="form-select"
                                    required
                                >
                                    <option value="">Seleccione una materia</option>

                                    <?php if ($materias): ?>
                                        <?php while ($materia = $materias->fetch_assoc()): ?>
                                            <option value="<?= (int) $materia["id"] ?>">
                                                <?= htmlspecialchars(
                                                    $materia["nombre"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                        </div>

                        <div class="carga-form-actions">
                            <button type="submit" class="btn btn-primary">
                                <span aria-hidden="true">＋</span>
                                Asignar carga académica
                            </button>
                        </div>

                    </form>

                </div>
            </section>

            <!-- FILTROS -->
            <section class="card carga-card">

                <div class="card-header carga-card-header">
                    <div class="carga-section-icon" aria-hidden="true">⌕</div>

                    <div>
                        <h2>Filtrar carga académica</h2>
                        <p>Busca asignaciones por texto, docente, curso o materia.</p>
                    </div>
                </div>

                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-12">
                            <label for="buscarCarga" class="form-label">
                                Buscar
                            </label>

                            <input
                                type="search"
                                id="buscarCarga"
                                class="form-control"
                                placeholder="Docente, documento, curso o materia..."
                            >
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="filtroDocente" class="form-label">
                                Docente
                            </label>

                            <select id="filtroDocente" class="form-select">
                                <option value="">Todos los docentes</option>

                                <?php if ($filtroDocentes): ?>
                                    <?php while ($docenteFiltro = $filtroDocentes->fetch_assoc()): ?>
                                        <option value="<?= (int) $docenteFiltro["id"] ?>">
                                            <?= htmlspecialchars(
                                                $docenteFiltro["nombres"] . " " .
                                                $docenteFiltro["apellidos"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="filtroCurso" class="form-label">
                                Curso
                            </label>

                            <select id="filtroCurso" class="form-select">
                                <option value="">Todos los cursos</option>

                                <?php if ($filtroCursos): ?>
                                    <?php while ($cursoFiltro = $filtroCursos->fetch_assoc()): ?>
                                        <option value="<?= (int) $cursoFiltro["id"] ?>">
                                            <?= htmlspecialchars(
                                                $cursoFiltro["nombre"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="filtroMateria" class="form-label">
                                Materia
                            </label>

                            <select id="filtroMateria" class="form-select">
                                <option value="">Todas las materias</option>

                                <?php if ($filtroMaterias): ?>
                                    <?php while ($materiaFiltro = $filtroMaterias->fetch_assoc()): ?>
                                        <option value="<?= (int) $materiaFiltro["id"] ?>">
                                            <?= htmlspecialchars(
                                                $materiaFiltro["nombre"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                    </div>

                    <div class="carga-form-actions carga-filter-actions">

                        <button
                            type="button"
                            id="aplicarFiltrosCarga"
                            class="btn btn-primary"
                        >
                            Filtrar
                        </button>

                        <button
                            type="button"
                            id="limpiarFiltrosCarga"
                            class="btn btn-outline-secondary"
                        >
                            Limpiar filtros
                        </button>

                    </div>

                </div>
            </section>

            <!-- TABLA -->
            <section class="card carga-card">

                <div class="card-header carga-table-header">
                    <div>
                        <h2>Carga académica asignada</h2>
                        <p>Consulta las asignaciones registradas.</p>
                    </div>

                    <span id="contadorCarga" class="carga-count">
                        0 resultados
                    </span>
                </div>

                <div class="card-body p-0">

                    <?php if ($asignaciones && $asignaciones->num_rows > 0): ?>

                        <div class="table-responsive">

                            <table
                                id="tablaCargaAcademica"
                                class="table table-hover align-middle mb-0 carga-table"
                            >

                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Docente</th>
                                        <th scope="col">Documento</th>
                                        <th scope="col">Curso</th>
                                        <th scope="col">Materia</th>
                                        <th scope="col" class="text-center">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php
                                    $numero = 1;

                                    while ($asignacion = $asignaciones->fetch_assoc()):
                                        $nombreDocente =
                                            $asignacion["docente_nombres"] . " " .
                                            $asignacion["docente_apellidos"];
                                    ?>

                                        <tr
                                            class="fila-carga"
                                            data-docente-id="<?= (int) $asignacion["docente_id"] ?>"
                                            data-curso-id="<?= (int) $asignacion["curso_id"] ?>"
                                            data-materia-id="<?= (int) $asignacion["materia_id"] ?>"
                                        >
                                            <td class="numero-fila">
                                                <?= $numero++ ?>
                                            </td>

                                            <td class="dato-docente">
                                                <?= htmlspecialchars(
                                                    $nombreDocente,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ) ?>
                                            </td>

                                            <td class="dato-documento">
                                                <?= htmlspecialchars(
                                                    $asignacion["docente_documento"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ) ?>
                                            </td>

                                            <td class="dato-curso">
                                                <?= htmlspecialchars(
                                                    $asignacion["curso_nombre"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ) ?>
                                            </td>

                                            <td class="dato-materia">
                                                <?= htmlspecialchars(
                                                    $asignacion["materia_nombre"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ) ?>
                                            </td>

                                            <td class="text-center carga-actions">

                                                <a
                                                    href="estudiantes.php?curso_id=<?= (int) $asignacion["curso_id"] ?>"
                                                    class="btn btn-primary btn-sm"
                                                >
                                                    Ver estudiantes
                                                </a>

                                                <a
                                                    href="index.php?eliminar=<?= (int) $asignacion["id"] ?>"
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="return confirm('¿Está seguro de eliminar esta asignación?');"
                                                >
                                                    Eliminar
                                                </a>

                                            </td>
                                        </tr>

                                    <?php endwhile; ?>

                                </tbody>
                            </table>

                        </div>

                    <?php else: ?>

                        <div class="carga-empty">
                            <div class="carga-empty-icon" aria-hidden="true">▦</div>
                            <h3>No hay asignaciones todavía</h3>
                            <p>Cuando crees una carga académica, aparecerá aquí.</p>
                        </div>

                    <?php endif; ?>

                </div>
            </section>

        </div>
    </main>
</div>

<?php include("../../includes/footer.php"); ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const tabla = document.getElementById("tablaCargaAcademica");
    const buscador = document.getElementById("buscarCarga");
    const filtroDocente = document.getElementById("filtroDocente");
    const filtroCurso = document.getElementById("filtroCurso");
    const filtroMateria = document.getElementById("filtroMateria");
    const botonFiltrar = document.getElementById("aplicarFiltrosCarga");
    const botonLimpiar = document.getElementById("limpiarFiltrosCarga");
    const contador = document.getElementById("contadorCarga");

    if (!tabla || !contador) {
        return;
    }

    const filas = Array.from(tabla.querySelectorAll("tbody .fila-carga"));

    function aplicarFiltros() {
        const texto = (buscador?.value || "").trim().toLocaleLowerCase();
        const docenteId = filtroDocente?.value || "";
        const cursoId = filtroCurso?.value || "";
        const materiaId = filtroMateria?.value || "";

        let visibles = 0;

        filas.forEach(function (fila) {
            const coincideTexto = fila.textContent
                .toLocaleLowerCase()
                .includes(texto);

            const coincideDocente =
                !docenteId || fila.dataset.docenteId === docenteId;

            const coincideCurso =
                !cursoId || fila.dataset.cursoId === cursoId;

            const coincideMateria =
                !materiaId || fila.dataset.materiaId === materiaId;

            const mostrar =
                coincideTexto &&
                coincideDocente &&
                coincideCurso &&
                coincideMateria;

            fila.hidden = !mostrar;

            if (mostrar) {
                visibles++;
            }
        });

        contador.textContent =
            visibles + (visibles === 1 ? " resultado" : " resultados");
    }

    botonFiltrar?.addEventListener("click", aplicarFiltros);

    buscador?.addEventListener("input", aplicarFiltros);
    filtroDocente?.addEventListener("change", aplicarFiltros);
    filtroCurso?.addEventListener("change", aplicarFiltros);
    filtroMateria?.addEventListener("change", aplicarFiltros);

    botonLimpiar?.addEventListener("click", function () {
        if (buscador) buscador.value = "";
        if (filtroDocente) filtroDocente.value = "";
        if (filtroCurso) filtroCurso.value = "";
        if (filtroMateria) filtroMateria.value = "";

        aplicarFiltros();
    });

    aplicarFiltros();
});
</script>

</body>
</html>
```