<?php

require_once("../../config/seguridad/coordinador.php");

$id=$_GET["id"];

$stmt=$conexion->prepare("SELECT * FROM materias WHERE id=?");

$stmt->bind_param("i",$id);

$stmt->execute();

$resultado=$stmt->get_result();

$materia=$resultado->fetch_assoc();

include("../../includes/header.php");

?>

<div class="container mt-5">

<div class="card">

<div class="card-header bg-warning">

Editar Materia

</div>

<div class="card-body">

<form action="actualizar.php" method="POST">

<input
type="hidden"
name="id"
value="<?php echo $materia["id"]; ?>">

<div class="mb-3">

<label>Materia</label>

<input
type="text"
name="nombre"
class="form-control"
value="<?php echo $materia["nombre"]; ?>"
required>

</div>

<button class="btn btn-primary">

Actualizar

</button>

<a href="index.php" class="btn btn-secondary">

Cancelar

</a>

</form>

</div>

</div>

</div>

<?php include("../../includes/footer.php"); ?>