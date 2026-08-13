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

<h2>Panel del Estudiante</h2>

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