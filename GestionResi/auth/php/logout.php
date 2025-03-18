<?php
session_start();  // Asegurarnos de que la sesión esté activa

// Eliminar todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Obtener la URL base dinámicamente
$base_url = "http://" . ($_SERVER['SERVER_ADDR'] === "::1" ? "localhost" : $_SERVER['SERVER_ADDR']) . "/resi/GestionResi/";

// Redirigir al login (página principal)
header("Location: " . $base_url);
exit();
