<?php
require_once __DIR__ . "/../../config/database.php";
require __DIR__ . "/../../auth/php/functions.php";


// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
is_logged_in();

$db = conectar_db();
if (!$db) {
    exit(json_encode(['status' => 'error', 'message' => 'Error de conexión a la base de datos']));
}

// Obtener datos de la solicitud
$idAsesor = $_POST['idAsesor'] ?? null;
$matriculaAlumno = $_POST['matricula'] ?? null;

// Validar que se recibieron ambos datos
if (!$idAsesor || !$matriculaAlumno) {
    exit(json_encode(['status' => 'error', 'message' => 'Datos incompletos']));
}

// Actualizar el IdAsesor en la tabla Alumnos
$sql = "UPDATE Alumnos SET IdAsesor = ? WHERE Matricula = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("is", $idAsesor, $matriculaAlumno);

if ($stmt->execute()) {
    $response = ['status' => 'success', 'message' => 'Asesor asignado correctamente.'];
} else {
    $response = ['status' => 'error', 'message' => 'Error al asignar el asesor.'];
}

$stmt->close();
$db->close();

echo json_encode($response);
