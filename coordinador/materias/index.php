<?php

require_once("../../config/seguridad/coordinador.php");

include("../../includes/header.php");

?>

<div class="container-fluid">

<div class="row">

<div class="col-md-2 p-0">

<?php include("../../includes/sidebar.php"); ?>

</div>

<div class="col-md-10">

<?php include("../../includes/navbar.php"); ?>

<div class="container mt-4">

<div class="d-flex justify-content-between">

<h3>Materias</h3>

<a href="crear.php" class="btn btn-primary">

Nueva Materia

</a>

</div>

<hr>

<form method="GET">

<div class="row">

<div class="col-md-6">

<input
type="text"
name="buscar"
class="form-control"
placeholder="Buscar materia"
value="<?php echo isset($_GET['buscar'])?$_GET['buscar']:'';?>">

</div>

<div class="col-md-2">

<button class="btn btn-success">

Buscar

</button>

</div>

</div>

</form>

<br>

<?php

$buscar="";

if(isset($_GET["buscar"])){

$buscar=$_GET["buscar"];

}

$sql="SELECT * FROM materias
WHERE nombre LIKE ?
ORDER BY nombre";

$stmt=$conexion->prepare($sql);

$texto="%".$buscar."%";

$stmt->bind_param("s",$texto);

$stmt->execute();

$resultado=$stmt->get_result();

?>

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>ID</th>

<th>Materia</th>

<th width="180">Acciones</th>

</tr>

</thead>

<tbody>

<?php while($fila=$resultado->fetch_assoc()){ ?>

<tr>

<td><?php echo $fila["id"]; ?></td>

<td><?php echo $fila["nombre"]; ?></td>

<td>

<a
href="editar.php?id=<?php echo $fila["id"]; ?>"
class="btn btn-warning btn-sm">

Editar

</a>

<a
href="eliminar.php?id=<?php echo $fila["id"]; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('¿Eliminar esta materia?')">

Eliminar

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<?php include("../../includes/footer.php"); ?>