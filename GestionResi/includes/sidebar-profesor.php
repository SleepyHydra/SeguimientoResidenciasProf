<?php

/**
 * Obtener la ruta actual.
 */
$current_url = $_SERVER['REQUEST_URI'];

/**
 * Añadir dinámicamente la clase de CSS, dependiendo de la ruta
 * actual.
 */
$seguimiento_class = (strpos($current_url, "index.php") !== false) ? "bg-primary" : "";
$cargar_alumnos_class = (strpos($current_url, "carga_alumnos.php") !== false) ? "bg-primary" : "";
$asesores_class = (strpos($current_url, "asesores.php") !== false) ? "bg-primary" : "";
$gestionar_archivos_class = (strpos($current_url, "admin_archivos.php") !== false) ? "bg-primary" : ""; // Clase para Gestionar archivos

// Obtener la URL base
$base_url = "http://" . ($_SERVER['SERVER_ADDR'] === "::1" ? "localhost" : $_SERVER['SERVER_ADDR']) . "/resi/GestionResi/";

?>

<!-- Sidebar -->
<div class="offcanvas offcanvas-start bg-dark" tabindex="-1" id="offcanvas" aria-labelledby="offcanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title text-light" id="offcanvasLabel">
            Gestion de Residencias Profesionales
        </h5>
        <button type="button" class="btn-close bg-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body text-light">
        <ul class="text-light p-0">
            <!-- Opción de Inicio -->
            <a href="<?php echo $base_url; ?>coordinador/index.php" class="text-decoration-none text-light">
                <li class="py-3 px-4 my-3 rounded-pill <?php echo $seguimiento_class ?>" role="button">
                    <i class="fa-solid fa-house me-2"></i>
                    Inicio
                </li>
            </a>

            <!-- Opción Cargar Alumnos -->
            <a href="<?php echo $base_url; ?>coordinador/php/carga_alumnos.php" class="text-decoration-none text-light">
                <li class="py-3 px-4 my-3 rounded-pill <?php echo $cargar_alumnos_class ?>" role="button">
                    <i class="fa-solid fa-upload me-2"></i>
                    Cargar Alumnos
                </li>
            </a>

            <!-- Opción Asesores -->
            <a href="<?php echo $base_url; ?>coordinador/php/admin_asesores.php" class="text-decoration-none text-light">
                <li class="py-3 px-4 my-3 rounded-pill <?php echo $asesores_class ?>" role="button">
                    <i class="fa-solid fa-user-tie me-2"></i>
                    Asesores
                </li>
            </a>

            <!-- Opción Gestionar Archivos -->
            <a href="<?php echo $base_url; ?>coordinador/php/admin_archivos.php" class="text-decoration-none text-light">
                <li class="py-3 px-4 my-3 rounded-pill <?php echo $gestionar_archivos_class ?>" role="button">
                    <i class="fa-solid fa-file-alt me-2"></i>
                    Gestionar Archivos
                </li>
            </a>
        </ul>
    </div>
</div>
<!-- Sidebar -->