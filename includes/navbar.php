<nav class="navbar navbar-dark bg-primary">

    <div class="container-fluid">

        <a
            class="navbar-brand"
            href="/Evaloranet/coordinador/index.php"
        >
            Seguimiento Académico
        </a>


        <div class="d-flex align-items-center">

            <span class="text-white me-3">

                <?php

                if (isset($_SESSION["nombres"])) {

                    echo htmlspecialchars($_SESSION["nombres"]);

                }

                ?>

            </span>


            <a
                href="/Evaloranet/auth/logout.php"
                class="btn btn-danger btn-sm"
            >
                Cerrar sesión
            </a>

        </div>

    </div>

</nav>