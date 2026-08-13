<?php

require_once("../../config/seguridad/coordinador.php");

include("../../includes/header.php");

?>

<div class="container mt-5">

<div class="card">

<div class="card-header bg-primary text-white">

Nueva Materia

</div>

<div class="card-body">

<form action="guardar.php" method="POST">

<div class="mb-3">

<label>Nombre de la materia</label>

<input
type="text"
name="nombre"
class="form-control"
required>

</div>

<button class="btn btn-success">

Guardar

</button>

<a href="index.php" class="btn btn-secondary">

Cancelar

</a>

</form>

</div>

</div>

</div>

<?php include("../../includes/footer.php"); ?>