<?php

session_start();

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/seguridad/coordinador.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


/*
|--------------------------------------------------------------------------
| CREAR ARCHIVO EXCEL
|--------------------------------------------------------------------------
*/

$spreadsheet = new Spreadsheet();


/*
|--------------------------------------------------------------------------
| HOJA DE ESTUDIANTES
|--------------------------------------------------------------------------
*/

$hojaEstudiantes = $spreadsheet->getActiveSheet();

$hojaEstudiantes->setTitle('Estudiantes');


/*
|--------------------------------------------------------------------------
| ENCABEZADOS
|--------------------------------------------------------------------------
*/

$encabezadosEstudiantes = [
    'Documento',
    'Nombres',
    'Apellidos',
    'Correo',
    'Teléfono',
    'Curso'
];


foreach ($encabezadosEstudiantes as $columna => $encabezado) {

    $letra = chr(65 + $columna);

    $hojaEstudiantes->setCellValue(
        $letra . '1',
        $encabezado
    );

}


/*
|--------------------------------------------------------------------------
| ESTILO DE ENCABEZADOS
|--------------------------------------------------------------------------
*/

$estiloEstudiantes = $hojaEstudiantes->getStyle('A1:F1');

$estiloEstudiantes
    ->getFont()
    ->setBold(true);

$estiloEstudiantes
    ->getFont()
    ->getColor()
    ->setARGB('FFFFFFFF');

$estiloEstudiantes
    ->getFill()
    ->setFillType(Fill::FILL_SOLID);

$estiloEstudiantes
    ->getFill()
    ->getStartColor()
    ->setARGB('2563EB');

$estiloEstudiantes
    ->getAlignment()
    ->setHorizontal(
        Alignment::HORIZONTAL_CENTER
    );


/*
|--------------------------------------------------------------------------
| EJEMPLOS DE ESTUDIANTES
|--------------------------------------------------------------------------
|
| Estos son solamente ejemplos para mostrar cómo llenar
| la plantilla.
|
*/

$estudiantesEjemplo = [

    [
        '1000000001',
        'Juan',
        'Pérez',
        'juan@ejemplo.com',
        '3000000001',
        '601'
    ],

    [
        '1000000002',
        'María',
        'Gómez',
        'maria@ejemplo.com',
        '3000000002',
        '602'
    ],

    [
        '1000000003',
        'Carlos',
        'Rodríguez',
        'carlos@ejemplo.com',
        '3000000003',
        '701'
    ]

];


$fila = 2;


foreach ($estudiantesEjemplo as $estudiante) {

    $hojaEstudiantes->fromArray(
        $estudiante,
        null,
        'A' . $fila
    );

    $fila++;

}


/*
|--------------------------------------------------------------------------
| ANCHO DE COLUMNAS
|--------------------------------------------------------------------------
*/

$anchosEstudiantes = [

    'A' => 18,
    'B' => 22,
    'C' => 22,
    'D' => 30,
    'E' => 18,
    'F' => 12

];


foreach ($anchosEstudiantes as $columna => $ancho) {

    $hojaEstudiantes
        ->getColumnDimension($columna)
        ->setWidth($ancho);

}


/*
|--------------------------------------------------------------------------
| NOTA PARA LA COLUMNA CURSO
|--------------------------------------------------------------------------
*/

$hojaEstudiantes
    ->getComment('F1')
    ->getText()
    ->createTextRun(
        'Ingrese el código del curso. Ejemplos: 601, 602, 603, 701, 702, 801, 901, 1001, 1101.'
    );


/*
|--------------------------------------------------------------------------
| HOJA DE DOCENTES
|--------------------------------------------------------------------------
*/

$hojaDocentes = $spreadsheet->createSheet();

$hojaDocentes->setTitle('Docentes');


/*
|--------------------------------------------------------------------------
| ENCABEZADOS DOCENTES
|--------------------------------------------------------------------------
*/

$encabezadosDocentes = [
    'Documento',
    'Nombres',
    'Apellidos',
    'Correo',
    'Teléfono'
];


foreach ($encabezadosDocentes as $columna => $encabezado) {

    $letra = chr(65 + $columna);

    $hojaDocentes->setCellValue(
        $letra . '1',
        $encabezado
    );

}


/*
|--------------------------------------------------------------------------
| ESTILO DE ENCABEZADOS DOCENTES
|--------------------------------------------------------------------------
*/

$estiloDocentes = $hojaDocentes->getStyle('A1:E1');

$estiloDocentes
    ->getFont()
    ->setBold(true);

$estiloDocentes
    ->getFont()
    ->getColor()
    ->setARGB('FFFFFFFF');

$estiloDocentes
    ->getFill()
    ->setFillType(Fill::FILL_SOLID);

$estiloDocentes
    ->getFill()
    ->getStartColor()
    ->setARGB('16A34A');

$estiloDocentes
    ->getAlignment()
    ->setHorizontal(
        Alignment::HORIZONTAL_CENTER
    );


/*
|--------------------------------------------------------------------------
| EJEMPLOS DE DOCENTES
|--------------------------------------------------------------------------
*/

$docentesEjemplo = [

    [
        '1000000010',
        'Carlos',
        'Rodríguez',
        'carlos@ejemplo.com',
        '3000000010'
    ],

    [
        '1000000011',
        'Ana',
        'Martínez',
        'ana@ejemplo.com',
        '3000000011'
    ],

    [
        '1000000012',
        'Luis',
        'García',
        'luis@ejemplo.com',
        '3000000012'
    ]

];


$fila = 2;


foreach ($docentesEjemplo as $docente) {

    $hojaDocentes->fromArray(
        $docente,
        null,
        'A' . $fila
    );

    $fila++;

}


/*
|--------------------------------------------------------------------------
| ANCHO DE COLUMNAS DOCENTES
|--------------------------------------------------------------------------
*/

$anchosDocentes = [

    'A' => 18,
    'B' => 22,
    'C' => 22,
    'D' => 30,
    'E' => 18

];


foreach ($anchosDocentes as $columna => $ancho) {

    $hojaDocentes
        ->getColumnDimension($columna)
        ->setWidth($ancho);

}


/*
|--------------------------------------------------------------------------
| SELECCIONAR LA PRIMERA HOJA
|--------------------------------------------------------------------------
*/

$spreadsheet->setActiveSheetIndex(0);


/*
|--------------------------------------------------------------------------
| NOMBRE DEL ARCHIVO
|--------------------------------------------------------------------------
*/

$nombreArchivo = 'plantilla_importacion_evaloranet.xlsx';


/*
|--------------------------------------------------------------------------
| LIMPIAR CUALQUIER SALIDA ANTERIOR
|--------------------------------------------------------------------------
*/

if (ob_get_length()) {

    ob_end_clean();

}


/*
|--------------------------------------------------------------------------
| PREPARAR DESCARGA
|--------------------------------------------------------------------------
*/

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment; filename="' .
    $nombreArchivo .
    '"'
);

header('Cache-Control: max-age=0');

header('Pragma: public');


/*
|--------------------------------------------------------------------------
| GENERAR EXCEL
|--------------------------------------------------------------------------
*/

$writer = new Xlsx($spreadsheet);

$writer->save('php://output');

exit;