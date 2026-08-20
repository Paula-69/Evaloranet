<?php

session_start();

/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/seguridad/coordinador.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;


/*
|--------------------------------------------------------------------------
| FUNCIÓN PARA LIMPIAR DATOS
|--------------------------------------------------------------------------
*/

function limpiarDato($dato)
{
    if ($dato === null) {
        return '';
    }

    return trim((string)$dato);
}


/*
|--------------------------------------------------------------------------
| FUNCIÓN PARA NORMALIZAR TEXTO
|--------------------------------------------------------------------------
*/

function normalizarTexto($texto)
{
    $texto = trim((string)$texto);

    return mb_strtolower($texto, 'UTF-8');
}


/*
|--------------------------------------------------------------------------
| REDIRECCIÓN CON ERROR
|--------------------------------------------------------------------------
*/

function errorImportacion($mensaje)
{
    header(
        'Location: index.php?error=' .
        urlencode($mensaje)
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| REDIRECCIÓN CON ÉXITO
|--------------------------------------------------------------------------
*/

function exitoImportacion($mensaje)
{
    header(
        'Location: index.php?success=' .
        urlencode($mensaje)
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| VERIFICAR ARCHIVO
|--------------------------------------------------------------------------
*/

if (!isset($_FILES['archivo_excel'])) {

    errorImportacion(
        'No se recibió ningún archivo Excel.'
    );
}


$archivo = $_FILES['archivo_excel'];


/*
|--------------------------------------------------------------------------
| VERIFICAR ERROR DE SUBIDA
|--------------------------------------------------------------------------
*/

if ($archivo['error'] !== UPLOAD_ERR_OK) {

    errorImportacion(
        'Ocurrió un error al subir el archivo.'
    );
}


/*
|--------------------------------------------------------------------------
| VERIFICAR EXTENSIÓN
|--------------------------------------------------------------------------
*/

$extension = strtolower(
    pathinfo(
        $archivo['name'],
        PATHINFO_EXTENSION
    )
);


if (!in_array($extension, ['xlsx', 'xls'])) {

    errorImportacion(
        'El archivo debe ser Excel (.xlsx o .xls).'
    );
}


/*
|--------------------------------------------------------------------------
| CARGAR EXCEL
|--------------------------------------------------------------------------
*/

try {

    $documentoExcel = IOFactory::load(
        $archivo['tmp_name']
    );

} catch (Exception $e) {

    errorImportacion(
        'No fue posible leer el archivo Excel: ' .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| OBTENER HOJAS
|--------------------------------------------------------------------------
*/

$hojas = $documentoExcel->getSheetNames();


/*
|--------------------------------------------------------------------------
| BUSCAR HOJA ESTUDIANTES
|--------------------------------------------------------------------------
*/

$nombreHojaEstudiantes = null;

foreach ($hojas as $nombreHoja) {

    if (
        normalizarTexto($nombreHoja) ===
        normalizarTexto('Estudiantes')
    ) {

        $nombreHojaEstudiantes = $nombreHoja;

        break;
    }
}


/*
|--------------------------------------------------------------------------
| BUSCAR HOJA DOCENTES
|--------------------------------------------------------------------------
*/

$nombreHojaDocentes = null;

foreach ($hojas as $nombreHoja) {

    if (
        normalizarTexto($nombreHoja) ===
        normalizarTexto('Docentes')
    ) {

        $nombreHojaDocentes = $nombreHoja;

        break;
    }
}


/*
|--------------------------------------------------------------------------
| VALIDAR HOJAS
|--------------------------------------------------------------------------
*/

if ($nombreHojaEstudiantes === null) {

    errorImportacion(
        'El Excel no contiene una hoja llamada "Estudiantes".'
    );
}


if ($nombreHojaDocentes === null) {

    errorImportacion(
        'El Excel no contiene una hoja llamada "Docentes".'
    );
}


/*
|--------------------------------------------------------------------------
| CONTADORES
|--------------------------------------------------------------------------
*/

$estudiantesImportados = 0;
$docentesImportados = 0;
$cursosCreados = 0;

$errores = [];


/*
|--------------------------------------------------------------------------
| INICIAR TRANSACCIÓN
|--------------------------------------------------------------------------
|
| IMPORTANTE:
| Tu config.php utiliza mysqli.
| Por eso aquí usamos $conexion y NO $pdo.
|
*/

$conexion->begin_transaction();


try {


    /*
    |--------------------------------------------------------------------------
    | PROCESAR ESTUDIANTES
    |--------------------------------------------------------------------------
    */

    $hojaEstudiantes =
        $documentoExcel->getSheetByName(
            $nombreHojaEstudiantes
        );


    $filasEstudiantes =
        $hojaEstudiantes
            ->toArray(
                null,
                true,
                true,
                true
            );


    foreach ($filasEstudiantes as $numeroFila => $fila) {


        /*
        |--------------------------------------------------------------------------
        | SALTAR ENCABEZADO
        |--------------------------------------------------------------------------
        */

        if ($numeroFila == 1) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | LEER COLUMNAS
        |--------------------------------------------------------------------------
        |
        | A = documento
        | B = nombres
        | C = apellidos
        | D = correo
        | E = telefono
        | F = curso
        |
        */

        $documento =
            limpiarDato($fila['A'] ?? '');

        $nombres =
            limpiarDato($fila['B'] ?? '');

        $apellidos =
            limpiarDato($fila['C'] ?? '');

        $correo =
            limpiarDato($fila['D'] ?? '');

        $telefono =
            limpiarDato($fila['E'] ?? '');

        $cursoNombre =
            limpiarDato($fila['F'] ?? '');


        /*
        |--------------------------------------------------------------------------
        | IGNORAR FILA COMPLETAMENTE VACÍA
        |--------------------------------------------------------------------------
        */

        if (
            $documento === '' &&
            $nombres === '' &&
            $apellidos === '' &&
            $correo === '' &&
            $telefono === '' &&
            $cursoNombre === ''
        ) {

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDAR DATOS OBLIGATORIOS
        |--------------------------------------------------------------------------
        */

        if ($documento === '') {

            $errores[] =
                "Estudiantes - fila $numeroFila: falta el documento.";

            continue;
        }


        if ($nombres === '') {

            $errores[] =
                "Estudiantes - fila $numeroFila: faltan los nombres.";

            continue;
        }


        if ($apellidos === '') {

            $errores[] =
                "Estudiantes - fila $numeroFila: faltan los apellidos.";

            continue;
        }


        if ($cursoNombre === '') {

            $errores[] =
                "Estudiantes - fila $numeroFila: falta el curso.";

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | BUSCAR / CREAR CURSO
        |--------------------------------------------------------------------------
        */

        $sqlCurso = "
            SELECT id
            FROM cursos
            WHERE nombre = ?
            LIMIT 1
        ";

        $stmtCurso =
            $conexion->prepare($sqlCurso);

        if (!$stmtCurso) {

            throw new Exception(
                'Error preparando consulta de cursos: ' .
                $conexion->error
            );
        }


        $stmtCurso->bind_param(
            's',
            $cursoNombre
        );


        $stmtCurso->execute();


        $resultadoCurso =
            $stmtCurso->get_result();


        if ($resultadoCurso->num_rows > 0) {

            $curso =
                $resultadoCurso->fetch_assoc();

            $cursoId =
                (int)$curso['id'];

        } else {

            /*
            |--------------------------------------------------------------------------
            | CREAR CURSO
            |--------------------------------------------------------------------------
            */

            $sqlCrearCurso = "
                INSERT INTO cursos (nombre)
                VALUES (?)
            ";

            $stmtCrearCurso =
                $conexion->prepare(
                    $sqlCrearCurso
                );

            if (!$stmtCrearCurso) {

                throw new Exception(
                    'Error preparando creación de curso: ' .
                    $conexion->error
                );
            }


            $stmtCrearCurso->bind_param(
                's',
                $cursoNombre
            );


            if (!$stmtCrearCurso->execute()) {

                throw new Exception(
                    'No se pudo crear el curso "' .
                    $cursoNombre .
                    '": ' .
                    $stmtCrearCurso->error
                );
            }


            $cursoId =
                $conexion->insert_id;

            $cursosCreados++;
        }


        /*
        |--------------------------------------------------------------------------
        | BUSCAR USUARIO POR DOCUMENTO
        |--------------------------------------------------------------------------
        */

        $sqlUsuario = "
            SELECT id, rol
            FROM usuarios
            WHERE documento = ?
            LIMIT 1
        ";

        $stmtUsuario =
            $conexion->prepare($sqlUsuario);


        if (!$stmtUsuario) {

            throw new Exception(
                'Error preparando búsqueda de usuario: ' .
                $conexion->error
            );
        }


        $stmtUsuario->bind_param(
            's',
            $documento
        );


        $stmtUsuario->execute();


        $resultadoUsuario =
            $stmtUsuario->get_result();


        /*
        |--------------------------------------------------------------------------
        | USUARIO YA EXISTE
        |--------------------------------------------------------------------------
        */

        if ($resultadoUsuario->num_rows > 0) {

            $usuario =
                $resultadoUsuario->fetch_assoc();

            $usuarioId =
                (int)$usuario['id'];

            $rolActual =
                $usuario['rol'];


            /*
            |--------------------------------------------------------------------------
            | SI YA ES DOCENTE, NO CONVERTIRLO
            |--------------------------------------------------------------------------
            */

            if ($rolActual !== 'estudiante') {

                $errores[] =
                    "Estudiantes - fila $numeroFila: " .
                    "el documento $documento ya pertenece a otro rol.";

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR DATOS DEL USUARIO
            |--------------------------------------------------------------------------
            */

            $sqlActualizar = "
                UPDATE usuarios
                SET
                    nombres = ?,
                    apellidos = ?,
                    correo = ?,
                    telefono = ?,
                    activo = 1
                WHERE id = ?
            ";

            $stmtActualizar =
                $conexion->prepare(
                    $sqlActualizar
                );


            if (!$stmtActualizar) {

                throw new Exception(
                    'Error preparando actualización de estudiante: ' .
                    $conexion->error
                );
            }


            $stmtActualizar->bind_param(
                'ssssi',
                $nombres,
                $apellidos,
                $correo,
                $telefono,
                $usuarioId
            );


            $stmtActualizar->execute();


        } else {


            /*
            |--------------------------------------------------------------------------
            | CREAR USUARIO ESTUDIANTE
            |--------------------------------------------------------------------------
            */

            $passwordHash =
                password_hash(
                    $documento,
                    PASSWORD_DEFAULT
                );


            $rol =
                'estudiante';


            $sqlInsertarUsuario = "
                INSERT INTO usuarios
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
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    0,
                    1
                )
            ";


            $stmtInsertarUsuario =
                $conexion->prepare(
                    $sqlInsertarUsuario
                );


            if (!$stmtInsertarUsuario) {

                throw new Exception(
                    'Error preparando inserción de estudiante: ' .
                    $conexion->error
                );
            }


            $stmtInsertarUsuario->bind_param(
                'sssssss',
                $documento,
                $nombres,
                $apellidos,
                $correo,
                $telefono,
                $passwordHash,
                $rol
            );


            if (!$stmtInsertarUsuario->execute()) {

                throw new Exception(
                    'No se pudo crear el usuario estudiante ' .
                    $documento .
                    ': ' .
                    $stmtInsertarUsuario->error
                );
            }


            $usuarioId =
                $conexion->insert_id;
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFICAR SI YA EXISTE EN ESTUDIANTES
        |--------------------------------------------------------------------------
        */

        $sqlEstudiante = "
            SELECT id
            FROM estudiantes
            WHERE usuario_id = ?
            LIMIT 1
        ";


        $stmtEstudiante =
            $conexion->prepare(
                $sqlEstudiante
            );


        $stmtEstudiante->bind_param(
            'i',
            $usuarioId
        );


        $stmtEstudiante->execute();


        $resultadoEstudiante =
            $stmtEstudiante->get_result();


        if ($resultadoEstudiante->num_rows > 0) {


            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR ESTUDIANTE
            |--------------------------------------------------------------------------
            */

            $sqlActualizarEstudiante = "
                UPDATE estudiantes
                SET
                    curso_id = ?,
                    estado = 'Activo'
                WHERE usuario_id = ?
            ";


            $stmtActualizarEstudiante =
                $conexion->prepare(
                    $sqlActualizarEstudiante
                );


            $stmtActualizarEstudiante->bind_param(
                'ii',
                $cursoId,
                $usuarioId
            );


            $stmtActualizarEstudiante->execute();


        } else {


            /*
            |--------------------------------------------------------------------------
            | INSERTAR ESTUDIANTE
            |--------------------------------------------------------------------------
            */

            $estado =
                'Activo';


            $sqlInsertarEstudiante = "
                INSERT INTO estudiantes
                (
                    usuario_id,
                    curso_id,
                    estado
                )
                VALUES
                (
                    ?,
                    ?,
                    ?
                )
            ";


            $stmtInsertarEstudiante =
                $conexion->prepare(
                    $sqlInsertarEstudiante
                );


            if (!$stmtInsertarEstudiante) {

                throw new Exception(
                    'Error preparando estudiante: ' .
                    $conexion->error
                );
            }


            $stmtInsertarEstudiante->bind_param(
                'iis',
                $usuarioId,
                $cursoId,
                $estado
            );


            if (!$stmtInsertarEstudiante->execute()) {

                throw new Exception(
                    'No se pudo crear el registro de estudiante: ' .
                    $stmtInsertarEstudiante->error
                );
            }
        }


        $estudiantesImportados++;
    }



    /*
    |--------------------------------------------------------------------------
    | PROCESAR DOCENTES
    |--------------------------------------------------------------------------
    |
    | Para docentes usamos:
    |
    | A = documento
    | B = nombres
    | C = apellidos
    | D = correo
    | E = telefono
    |
    | La columna F puede existir, pero no se utiliza.
    |
    */

    $hojaDocentes =
        $documentoExcel->getSheetByName(
            $nombreHojaDocentes
        );


    $filasDocentes =
        $hojaDocentes
            ->toArray(
                null,
                true,
                true,
                true
            );


    foreach ($filasDocentes as $numeroFila => $fila) {


        /*
        |--------------------------------------------------------------------------
        | SALTAR ENCABEZADO
        |--------------------------------------------------------------------------
        */

        if ($numeroFila == 1) {
            continue;
        }


        $documento =
            limpiarDato($fila['A'] ?? '');

        $nombres =
            limpiarDato($fila['B'] ?? '');

        $apellidos =
            limpiarDato($fila['C'] ?? '');

        $correo =
            limpiarDato($fila['D'] ?? '');

        $telefono =
            limpiarDato($fila['E'] ?? '');


        /*
        |--------------------------------------------------------------------------
        | FILA VACÍA
        |--------------------------------------------------------------------------
        */

        if (
            $documento === '' &&
            $nombres === '' &&
            $apellidos === '' &&
            $correo === '' &&
            $telefono === ''
        ) {

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDACIONES
        |--------------------------------------------------------------------------
        */

        if ($documento === '') {

            $errores[] =
                "Docentes - fila $numeroFila: falta el documento.";

            continue;
        }


        if ($nombres === '') {

            $errores[] =
                "Docentes - fila $numeroFila: faltan los nombres.";

            continue;
        }


        if ($apellidos === '') {

            $errores[] =
                "Docentes - fila $numeroFila: faltan los apellidos.";

            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | BUSCAR USUARIO
        |--------------------------------------------------------------------------
        */

        $sqlUsuario = "
            SELECT id, rol
            FROM usuarios
            WHERE documento = ?
            LIMIT 1
        ";


        $stmtUsuario =
            $conexion->prepare(
                $sqlUsuario
            );


        if (!$stmtUsuario) {

            throw new Exception(
                'Error buscando docente: ' .
                $conexion->error
            );
        }


        $stmtUsuario->bind_param(
            's',
            $documento
        );


        $stmtUsuario->execute();


        $resultadoUsuario =
            $stmtUsuario->get_result();


        if ($resultadoUsuario->num_rows > 0) {


            /*
            |--------------------------------------------------------------------------
            | USUARIO EXISTENTE
            |--------------------------------------------------------------------------
            */

            $usuario =
                $resultadoUsuario->fetch_assoc();

            $usuarioId =
                (int)$usuario['id'];

            $rolActual =
                $usuario['rol'];


            if ($rolActual !== 'docente') {

                $errores[] =
                    "Docentes - fila $numeroFila: " .
                    "el documento $documento ya pertenece a otro rol.";

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR DOCENTE
            |--------------------------------------------------------------------------
            */

            $sqlActualizar = "
                UPDATE usuarios
                SET
                    nombres = ?,
                    apellidos = ?,
                    correo = ?,
                    telefono = ?,
                    activo = 1,
                    cambiar_password = 1
                WHERE id = ?
            ";


            $stmtActualizar =
                $conexion->prepare(
                    $sqlActualizar
                );


            if (!$stmtActualizar) {

                throw new Exception(
                    'Error actualizando docente: ' .
                    $conexion->error
                );
            }


            $stmtActualizar->bind_param(
                'ssssi',
                $nombres,
                $apellidos,
                $correo,
                $telefono,
                $usuarioId
            );


            $stmtActualizar->execute();


        } else {


            /*
            |--------------------------------------------------------------------------
            | CREAR DOCENTE
            |--------------------------------------------------------------------------
            */

            $passwordHash =
                password_hash(
                    $documento,
                    PASSWORD_DEFAULT
                );


            $rol =
                'docente';


            $sqlInsertarUsuario = "
                INSERT INTO usuarios
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
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    1,
                    1
                )
            ";


            $stmtInsertarUsuario =
                $conexion->prepare(
                    $sqlInsertarUsuario
                );


            if (!$stmtInsertarUsuario) {

                throw new Exception(
                    'Error preparando docente: ' .
                    $conexion->error
                );
            }


            $stmtInsertarUsuario->bind_param(
                'sssssss',
                $documento,
                $nombres,
                $apellidos,
                $correo,
                $telefono,
                $passwordHash,
                $rol
            );


            if (!$stmtInsertarUsuario->execute()) {

                throw new Exception(
                    'No se pudo crear el docente ' .
                    $documento .
                    ': ' .
                    $stmtInsertarUsuario->error
                );
            }


            $usuarioId =
                $conexion->insert_id;
        }


        /*
        |--------------------------------------------------------------------------
        | VERIFICAR TABLA DOCENTES
        |--------------------------------------------------------------------------
        */

        $sqlDocente = "
            SELECT id
            FROM docentes
            WHERE usuario_id = ?
            LIMIT 1
        ";


        $stmtDocente =
            $conexion->prepare(
                $sqlDocente
            );


        if (!$stmtDocente) {

            throw new Exception(
                'Error buscando registro docente: ' .
                $conexion->error
            );
        }


        $stmtDocente->bind_param(
            'i',
            $usuarioId
        );


        $stmtDocente->execute();


        $resultadoDocente =
            $stmtDocente->get_result();


        if ($resultadoDocente->num_rows > 0) {


            /*
            |--------------------------------------------------------------------------
            | ACTUALIZAR DOCENTE
            |--------------------------------------------------------------------------
            */

            $sqlActualizarDocente = "
                UPDATE docentes
                SET estado = 'Activo'
                WHERE usuario_id = ?
            ";


            $stmtActualizarDocente =
                $conexion->prepare(
                    $sqlActualizarDocente
                );


            $stmtActualizarDocente->bind_param(
                'i',
                $usuarioId
            );


            $stmtActualizarDocente->execute();


        } else {


            /*
            |--------------------------------------------------------------------------
            | INSERTAR DOCENTE
            |--------------------------------------------------------------------------
            */

            $estado =
                'Activo';


            $sqlInsertarDocente = "
                INSERT INTO docentes
                (
                    usuario_id,
                    estado
                )
                VALUES
                (
                    ?,
                    ?
                )
            ";


            $stmtInsertarDocente =
                $conexion->prepare(
                    $sqlInsertarDocente
                );


            if (!$stmtInsertarDocente) {

                throw new Exception(
                    'Error preparando inserción docente: ' .
                    $conexion->error
                );
            }


            $stmtInsertarDocente->bind_param(
                'is',
                $usuarioId,
                $estado
            );


            if (!$stmtInsertarDocente->execute()) {

                throw new Exception(
                    'No se pudo crear el registro docente: ' .
                    $stmtInsertarDocente->error
                );
            }
        }


        $docentesImportados++;
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIRMAR TODO
    |--------------------------------------------------------------------------
    */

    $conexion->commit();


} catch (Exception $e) {


    /*
    |--------------------------------------------------------------------------
    | CANCELAR CAMBIOS SI OCURRIÓ UN ERROR
    |--------------------------------------------------------------------------
    */

    $conexion->rollback();


    errorImportacion(
        'La importación fue cancelada. Error: ' .
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| MENSAJE FINAL
|--------------------------------------------------------------------------
*/

$mensaje =
    'Importación terminada. ' .
    'Estudiantes procesados: ' .
    $estudiantesImportados .
    '. Docentes procesados: ' .
    $docentesImportados .
    '. Cursos creados: ' .
    $cursosCreados;


/*
|--------------------------------------------------------------------------
| MOSTRAR ERRORES DE FILAS
|--------------------------------------------------------------------------
*/

if (count($errores) > 0) {

    $mensaje .=
        ' Filas con problemas: ' .
        count($errores) .
        '.';
}


exitoImportacion($mensaje);