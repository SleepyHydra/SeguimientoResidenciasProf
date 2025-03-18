<?php
// Obtener la URL base dinámicamente
$base_url = "http://" . ($_SERVER['SERVER_ADDR'] === "::1" ? "localhost" : $_SERVER['SERVER_ADDR']) . "/resi/GestionResi/";
?>

<header class="color-fondo text-center py-2 px-5 sticky-top">
    <div class="row">
        <!-- Botón del menú de la barra lateral -->
        <div class="col-2 d-flex align-items-center">
            <button class="btn btn-primary bg-oficial" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas" aria-controls="offcanvas">
                <i role="button" class="fa-solid fa-bars text-light fs-5"></i>
            </button>
        </div>

        <!-- Logo y título -->
        <div class="col d-flex align-items-center justify-content-center gap-5">
            <img src="<?php echo $base_url; ?>assets/logo-header.png" alt="Logo" width="200px">
            <h1 class="color-oficial fw-bold text-center fs-3">
                Seguimiento de Residencias Profesionales
            </h1>
        </div>

        <!-- Icono de usuario y menú de cierre de sesión -->
        <div class="col-2 d-flex align-items-center justify-content-end text-white gap-2">
            <div class="dropdown">
                <button class="btn bg-white p-2 d-flex align-items-center justify-content-center dropdown-toggle rounded-pill" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-user color-oficial"></i>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <!-- Enlace que redirige a logout.php para cerrar sesión -->
                        <a href="<?php echo $base_url; ?>auth/php/logout.php" class="dropdown-item">Cerrar sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>