<nav class="navbar navbar-expand-lg navbar-dark bg-primary">

<div class="container">

<a class="navbar-brand" href="#">Seguimiento Académico</a>

<div class="ms-auto">

<span class="text-white">

<?php
echo $_SESSION['nombre'];
?>

</span>

<a href="/seguimiento_academico/auth/logout.php"
class="btn btn-danger btn-sm ms-3">

Cerrar sesión

</a>

</div>

</div>

</nav>