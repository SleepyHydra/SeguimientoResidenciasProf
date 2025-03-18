<?php

/**
 * Valida si el usuario inició sesión.
 * Si no ha iniciado sesión, redirige a la raíz del proyecto, en donde se
 * encuentra el archivo `index.php`, que contiene el formulario
 * para iniciar sesión.
 *
 * @return bool `true` si inició sesión, `false` si no es así.
 */
function is_logged_in()
{
    // Iniciar sesión si no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    /**
     * Ruta de la raíz del proyecto.
     * @var string
     */
    $ruta_login = "../";

    /**
     * Validar si existe la key `logged_in` en el arreglo global
     * `$_SESSION`. Si sí está definido, retornará `true`, de lo contrario
     * retornará `false`.
     */
    if (!isset($_SESSION["logged_in"]) || empty($_SESSION["logged_in"])) {
        header("Location: " . $ruta_login);
        exit(); // Aseguramos que el flujo se detenga después de la redirección
    }
    
    return true;
}
