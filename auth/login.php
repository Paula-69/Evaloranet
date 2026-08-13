<?php

require_once("../config/config.php");

if(isset($_SESSION["id"])){
    header("Location: ../".$_SESSION["rol"]."/index.php");
    exit();
}

include("../includes/header.php");

?>

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-5">

<div class="card shadow">

<div class="card-header bg-primary text-white text-center">

<h3>Seguimiento Académico</h3>

</div>

<div class="card-body">

<form action="validar_login.php" method="POST">

<div class="mb-3">

<label>Número de documento</label>

<input
type="text"
name="documento"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Contraseña</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<button class="btn btn-primary w-100">

Ingresar

</button>

</form>

</div>

</div>

</div>

</div>

</div>

<?php

include("../includes/footer.php");

?>