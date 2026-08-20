<?php

session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/seguridad/coordinador.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Importar Excel</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container mt-5">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">

            <h4 class="mb-0">
                Importar estudiantes y docentes
            </h4>

        </div>

        <div class="card-body">

            <div class="alert alert-info">

                <strong>Formato del archivo Excel</strong>

                <p class="mb-1 mt-2">
                    El archivo debe contener dos hojas:
                </p>

                <ul class="mb-0">

                    <li>
                        <strong>Estudiantes</strong>
                    </li>

                    <li>
                        <strong>Docentes</strong>
                    </li>

                </ul>

            </div>


            <?php if (isset($_GET['error'])): ?>

                <div class="alert alert-danger">

                    <?= htmlspecialchars($_GET['error']) ?>

                </div>

            <?php endif; ?>


            <?php if (isset($_GET['success'])): ?>

                <div class="alert alert-success">

                    <?= htmlspecialchars($_GET['success']) ?>

                </div>

            <?php endif; ?>


            <form
                action="procesar.php"
                method="POST"
                enctype="multipart/form-data"
            >

                <div class="mb-3">

                    <label class="form-label">

                        Seleccione el archivo Excel

                    </label>

                    <input
                        type="file"
                        name="archivo_excel"
                        class="form-control"
                        accept=".xlsx,.xls"
                        required
                    >

                </div>


                <div class="alert alert-warning">

                    <strong>Importante:</strong>

                    <br>

                    La contraseña inicial de estudiantes y docentes
                    será su número de documento.

                    <br><br>

                    Los docentes deberán cambiar obligatoriamente
                    su contraseña en el primer ingreso.

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    Importar archivo

                </button>


                <a
                    href="../index.php"
                    class="btn btn-secondary"
                >

                    Volver al panel

                </a>

            </form>

        </div>

    </div>

</div>

</body>

</html>