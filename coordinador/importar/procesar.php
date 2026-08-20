<?php

require_once("../../config/seguridad/coordinador.php");

require_once("../../vendor/autoload.php");

use PhpOffice\PhpSpreadsheet\IOFactory;

if($_SERVER["REQUEST_METHOD"] != "POST"){

    header("Location: index.php");

    exit();

}


if(!isset($_FILES["archivo"])){

    die("No se recibió ningún archivo.");

}


$tipo = $_POST["tipo"];

$archivo = $_FILES["archivo"];


if($archivo["error"] != 0){

    die("Ocurrió un error al subir el archivo.");

}


$extension = strtolower(
    pathinfo($archivo["name"], PATHINFO_EXTENSION)
);


$extensionesPermitidas = ["xlsx", "xls"];


if(!in_array($extension, $extensionesPermitidas)){

    die("El archivo debe ser Excel (.xlsx o .xls).");

}


try{

    $documentoExcel = IOFactory::load(
        $archivo["tmp_name"]
    );

    $hoja = $documentoExcel->getActiveSheet();

    $filas = $hoja->toArray(
        null,
        true,
        true,
        true
    );


}catch(Exception $e){

    die(
        "No se pudo leer el archivo Excel: "
        .$e->getMessage()
    );

}


/*
==================================================
IMPORTAR ESTUDIANTES
==================================================
*/

if($tipo == "estudiantes"){

    $importados = 0;
    $repetidos = 0;
    $errores = 0;


    foreach($filas as $numeroFila => $fila){

        /*
        Saltar la primera fila porque contiene
        los nombres de las columnas.
        */

        if($numeroFila == 1){

            continue;

        }


        $documento = trim($fila["A"]);
        $nombres = trim($fila["B"]);
        $apellidos = trim($fila["C"]);
        $curso = trim($fila["D"]);


        /*
        Ignorar filas completamente vacías
        */

        if(
            $documento == "" &&
            $nombres == "" &&
            $apellidos == "" &&
            $curso == ""
        ){

            continue;

        }


        /*
        Validar información obligatoria
        */

        if(
            $documento == "" ||
            $nombres == "" ||
            $apellidos == "" ||
            $curso == ""
        ){

            $errores++;

            continue;

        }


        /*
        Verificar si el documento ya existe
        */

        $buscar = $conexion->prepare(
            "SELECT id
             FROM usuarios
             WHERE documento=?"
        );

        $buscar->bind_param(
            "s",
            $documento
        );

        $buscar->execute();

        $resultado = $buscar->get_result();


        if($resultado->num_rows > 0){

            $repetidos++;

            continue;

        }


        /*
        Verificar que el curso exista
        */

        $buscarCurso = $conexion->prepare(
            "SELECT id
             FROM cursos
             WHERE nombre=?"
        );

        $buscarCurso->bind_param(
            "s",
            $curso
        );

        $buscarCurso->execute();

        $resultadoCurso =
            $buscarCurso->get_result();


        if($resultadoCurso->num_rows == 0){

            $errores++;

            continue;

        }


        $cursoDatos =
            $resultadoCurso->fetch_assoc();

        $cursoId = $cursoDatos["id"];


        /*
        Crear contraseña inicial.
        Será el documento.
        */

        $password =
            password_hash(
                $documento,
                PASSWORD_DEFAULT
            );


        /*
        Insertar usuario
        */

        $insertarUsuario = $conexion->prepare(
            "INSERT INTO usuarios
            (
                documento,
                nombres,
                apellidos,
                password,
                rol,
                cambiar_password,
                activo
            )
            VALUES
            (?, ?, ?, ?, 'estudiante', 0, 1)"
        );


        $insertarUsuario->bind_param(
            "ssss",
            $documento,
            $nombres,
            $apellidos,
            $password
        );


        if(!$insertarUsuario->execute()){

            $errores++;

            continue;

        }


        $usuarioId =
            $conexion->insert_id;


        /*
        Crear registro de estudiante
        */

        $insertarEstudiante =
            $conexion->prepare(
                "INSERT INTO estudiantes
                (
                    usuario_id,
                    curso_id
                )
                VALUES
                (?, ?)"
            );


        $insertarEstudiante->bind_param(
            "ii",
            $usuarioId,
            $cursoId
        );


        if($insertarEstudiante->execute()){

            $importados++;

        }else{

            $errores++;

        }

    }


    include("../../includes/header.php");

    ?>

    <div class="container mt-5">

        <div class="card shadow">

            <div class="card-header bg-primary text-white">

                Resultado de importación

            </div>

            <div class="card-body">

                <div class="alert alert-success">

                    <strong>
                        Estudiantes importados:
                    </strong>

                    <?php echo $importados; ?>

                </div>


                <div class="alert alert-warning">

                    <strong>
                        Registros repetidos:
                    </strong>

                    <?php echo $repetidos; ?>

                </div>


                <div class="alert alert-danger">

                    <strong>
                        Registros con errores:
                    </strong>

                    <?php echo $errores; ?>

                </div>


                <a
                    href="index.php"
                    class="btn btn-primary"
                >

                    Volver

                </a>

            </div>

        </div>

    </div>

    <?php

    include("../../includes/footer.php");

    exit();

}


/*
==================================================
IMPORTAR DOCENTES
==================================================
*/

if($tipo == "docentes"){

    $importados = 0;
    $repetidos = 0;
    $errores = 0;


    foreach($filas as $numeroFila => $fila){

        if($numeroFila == 1){

            continue;

        }


        $documento = trim($fila["A"]);
        $nombres = trim($fila["B"]);
        $apellidos = trim($fila["C"]);
        $correo = trim($fila["D"]);
        $telefono = trim($fila["E"]);


        if(
            $documento == "" &&
            $nombres == "" &&
            $apellidos == ""
        ){

            continue;

        }


        if(
            $documento == "" ||
            $nombres == "" ||
            $apellidos == ""
        ){

            $errores++;

            continue;

        }


        /*
        Verificar documento existente
        */

        $buscar = $conexion->prepare(
            "SELECT id
             FROM usuarios
             WHERE documento=?"
        );


        $buscar->bind_param(
            "s",
            $documento
        );


        $buscar->execute();


        $resultado =
            $buscar->get_result();


        if($resultado->num_rows > 0){

            $repetidos++;

            continue;

        }


        /*
        Crear contraseña inicial
        */

        $password =
            password_hash(
                $documento,
                PASSWORD_DEFAULT
            );


        /*
        Insertar usuario docente
        */

        $insertarUsuario =
            $conexion->prepare(
                "INSERT INTO usuarios
                (
                    documento,
                    nombres,
                    apellidos,
                    correo,
                    telefono,
                    password,
                    rol,
                    cambiar_password,
                    activo
                )
                VALUES
                (?, ?, ?, ?, ?, ?, 'docente', 1, 1)"
            );


        $insertarUsuario->bind_param(
            "ssssss",
            $documento,
            $nombres,
            $apellidos,
            $correo,
            $telefono,
            $password
        );


        if(!$insertarUsuario->execute()){

            $errores++;

            continue;

        }


        $usuarioId =
            $conexion->insert_id;


        /*
        Crear registro docente
        */

        $insertarDocente =
            $conexion->prepare(
                "INSERT INTO docentes
                (
                    usuario_id
                )
                VALUES
                (?)"
            );


        $insertarDocente->bind_param(
            "i",
            $usuarioId
        );


        if($insertarDocente->execute()){

            $importados++;

        }else{

            $errores++;

        }

    }


    include("../../includes/header.php");

    ?>

    <div class="container mt-5">

        <div class="card shadow">

            <div class="card-header bg-success text-white">

                Resultado de importación

            </div>


            <div class="card-body">

                <div class="alert alert-success">

                    <strong>
                        Docentes importados:
                    </strong>

                    <?php echo $importados; ?>

                </div>


                <div class="alert alert-warning">

                    <strong>
                        Registros repetidos:
                    </strong>

                    <?php echo $repetidos; ?>

                </div>


                <div class="alert alert-danger">

                    <strong>
                        Registros con errores:
                    </strong>

                    <?php echo $errores; ?>

                </div>


                <a
                    href="index.php"
                    class="btn btn-primary"
                >

                    Volver

                </a>

            </div>

        </div>

    </div>

    <?php

    include("../../includes/footer.php");

    exit();

}


die("Tipo de importación no válido.");



/*
|--------------------------------------------------------------------------
| SEGURIDAD
|--------------------------------------------------------------------------
*/

require_once(__DIR__ . "/../../config/seguridad/coordinador.php");


/*
|--------------------------------------------------------------------------
| VERIFICAR MÉTODO
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");

    exit();

}


/*
|--------------------------------------------------------------------------
| VERIFICAR ARCHIVO
|--------------------------------------------------------------------------
*/

if (!isset($_FILES["archivo"])) {

    die("No se recibió ningún archivo.");

}


/*
|--------------------------------------------------------------------------
| DATOS DEL FORMULARIO
|--------------------------------------------------------------------------
*/

$tipo = $_POST["tipo"] ?? "";


/*
|--------------------------------------------------------------------------
| VALIDAR TIPO
|--------------------------------------------------------------------------
*/

if ($tipo !== "estudiantes" && $tipo !== "docentes") {

    die("El tipo de información seleccionado no es válido.");

}


/*
|--------------------------------------------------------------------------
| INFORMACIÓN DEL ARCHIVO
|--------------------------------------------------------------------------
*/

$archivo = $_FILES["archivo"];


/*
|--------------------------------------------------------------------------
| COMPROBAR ERROR DE CARGA
|--------------------------------------------------------------------------
*/

if ($archivo["error"] !== UPLOAD_ERR_OK) {

    die("Ocurrió un error al cargar el archivo.");

}


/*
|--------------------------------------------------------------------------
| NOMBRE DEL ARCHIVO
|--------------------------------------------------------------------------
*/

$nombreArchivo = $archivo["name"];


/*
|--------------------------------------------------------------------------
| EXTENSIÓN
|--------------------------------------------------------------------------
*/

$extension = strtolower(
    pathinfo($nombreArchivo, PATHINFO_EXTENSION)
);


/*
|--------------------------------------------------------------------------
| VALIDAR EXTENSIÓN
|--------------------------------------------------------------------------
*/

$extensionesPermitidas = [
    "xlsx",
    "xls"
];


if (!in_array($extension, $extensionesPermitidas)) {

    die("El archivo debe ser Excel (.xlsx o .xls).");

}


/*
|--------------------------------------------------------------------------
| MOSTRAR INFORMACIÓN
|--------------------------------------------------------------------------
*/

echo "<h1>Archivo recibido correctamente</h1>";

echo "<p><strong>Archivo:</strong> "
    . htmlspecialchars($nombreArchivo)
    . "</p>";

echo "<p><strong>Tipo:</strong> "
    . htmlspecialchars($tipo)
    . "</p>";

echo "<p><strong>Extensión:</strong> "
    . htmlspecialchars($extension)
    . "</p>";

echo "<br>";

echo '<a href="index.php">Volver a importar</a>';


