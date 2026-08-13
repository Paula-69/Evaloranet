<?php

require_once("../config/config.php");

if(!isset($_SESSION["id"])){

    header("Location: ../auth/login.php");

    exit();

}

include("../includes/header.php");
include("../includes/navbar.php");

?>

<div class="container mt-4">

<h2>Panel del Coordinador</h2>

<h4>

Bienvenido

<?php

echo $_SESSION["nombre"];

?>

</h4>

</div>

<?php

include("../includes/footer.php");

?>

<?php

require_once("../config/seguridad/coordinador.php");

include("../includes/header.php");

?>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 p-0">

<?php

include("../includes/sidebar.php");

?>

</div>

<div class="col-md-10">

<?php

include("../includes/navbar.php");

?>

<div class="container mt-4">

<h2>

Panel del Coordinador

</h2>

<p>

Bienvenido

<strong>

<?php echo $_SESSION["nombre"]; ?>

</strong>

</p>

<?php

$sql=$conexion->query("SELECT COUNT(*) total FROM estudiantes");

$estudiantes=$sql->fetch_assoc();

$sql=$conexion->query("SELECT COUNT(*) total FROM docentes");

$docentes=$sql->fetch_assoc();

$sql=$conexion->query("SELECT COUNT(*) total FROM cursos");

$cursos=$sql->fetch_assoc();

$sql=$conexion->query("SELECT COUNT(*) total FROM reportes");

$reportes=$sql->fetch_assoc();

?>

<div class="row">

<div class="col-md-3">

<div class="card bg-primary text-white">

<div class="card-body">

<h5>

Estudiantes

</h5>

<h2>

<?php

echo $estudiantes["total"];

?>

</h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card bg-success text-white">

<div class="card-body">

<h5>

Docentes

</h5>

<h2>

<?php

echo $docentes["total"];

?>

</h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card bg-warning">

<div class="card-body">

<h5>

Cursos

</h5>

<h2>

<?php

echo $cursos["total"];

?>

</h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card bg-danger text-white">

<div class="card-body">

<h5>

Reportes

</h5>

<h2>

<?php

echo $reportes["total"];

?>

</h2>

</div>

</div>

</div>

</div>

<hr>

<h4>

Accesos rápidos

</h4>

<div class="row">

<div class="col-md-4">

<a href="importar/"

class="btn btn-primary w-100">

Importar estudiantes y docentes

</a>

</div>

<div class="col-md-4">

<a href="materias/"

class="btn btn-success w-100">

Administrar materias

</a>

</div>

<div class="col-md-4">

<a href="carga_academica/"

class="btn btn-warning w-100">

Asignar Carga académica

</a>

</div>

</div>

</div>

</div>

</div>

</div>

<?php

include("../includes/footer.php");

?>