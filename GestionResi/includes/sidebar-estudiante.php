<?php
// Obtener la URL base dinámicamente
$base_url = "http://" . ($_SERVER['SERVER_ADDR'] === "::1" ? "localhost" : $_SERVER['SERVER_ADDR']) . "/resi/GestionResi/";

/**
 * Obtener la ruta actual.
 */
$current_url = $_SERVER['REQUEST_URI'];

/**
 * Añadir dinámicamente la clase de CSS, dependiendo de la ruta actual.
 */
$inicio_class = (strpos($current_url, "alumnos") !== false) ? "bg-primary" : "";
$descarga_class = (strpos($current_url, "descarga_archivos.php") !== false) ? "bg-primary" : "";
?>

<!-- Button to toggle sidebar (Hamburger icon) -->
<button class="btn btn-dark d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas" aria-controls="offcanvas">
    <i class="fa-solid fa-bars"></i> <!-- Icono de hamburguesa -->
</button>

<!-- Sidebar -->
<div class="offcanvas offcanvas-start bg-dark" tabindex="-1" id="offcanvas" aria-labelledby="offcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title text-light" id="offcanvasLabel">
            SEYBT
        </h5>
        <button type="button" class="btn-close bg-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body text-light">
        <ul class="text-light p-0">
            <a href="<?php echo $base_url; ?>alumnos/" class="text-decoration-none text-light <?php echo $inicio_class ?>">
                <li class="py-3 px-4 my-3 rounded-pill" role="button">
                    <i class="fa-solid fa-house me-2"></i>
                    Inicio
                </li>
            </a>
            <a href="<?php echo $base_url; ?>alumnos/php/descarga_archivos.php" class="text-decoration-none text-light <?php echo $descarga_class ?>">
                <li class="py-3 px-4 my-3 rounded-pill" role="button">
                    <i class="fa-solid fa-download me-2"></i> <!-- Icono de descarga -->
                    Descargar formatos
                </li>
            </a>
        </ul>
    </div>
</div>
<!-- Sidebar -->