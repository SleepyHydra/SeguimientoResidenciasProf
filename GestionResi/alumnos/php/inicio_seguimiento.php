<?php
// Incluir la base de datos y funciones de autenticación
require_once __DIR__ . "/../../config/database.php";
require __DIR__ . "/../../auth/php/functions.php";

// Iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que la sesión esté iniciada
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    echo json_encode(['ok' => false, 'error' => 'Sesión no válida.']);
    exit();
}

$db = conectar_db();
if (!$db) {
    echo json_encode(['ok' => false, 'error' => 'Error de conexión a la base de datos.']);
    exit();
}

// Obtener la matrícula desde la solicitud POST
$matricula = $_POST['matricula'] ?? '';

if (empty($matricula)) {
    echo json_encode(['ok' => false, 'error' => 'Matrícula no proporcionada.']);
    exit();
}

// Consultar la tabla Alumnos para obtener el IdAlumno usando la matrícula
$sqlAlumno = "SELECT IdAlumno FROM Alumnos WHERE Matricula = ?";
$stmtAlumno = $db->prepare($sqlAlumno);
$stmtAlumno->bind_param("s", $matricula);
$stmtAlumno->execute();
$resultAlumno = $stmtAlumno->get_result();
$alumno = $resultAlumno->fetch_assoc();
$stmtAlumno->close();

if (!$alumno) {
    echo json_encode(['ok' => false, 'error' => 'No se encontró el alumno.']);
    exit();
}

// Extraer el IdAlumno
$idAlumno = $alumno['IdAlumno'];

// Crear el registro en la tabla Seguimientos
$sqlSeguimiento = "INSERT INTO Seguimientos (IdAlumno, FechaInicio) VALUES (?, NOW())";
$stmtSeguimiento = $db->prepare($sqlSeguimiento);
$stmtSeguimiento->bind_param("i", $idAlumno);

if ($stmtSeguimiento->execute()) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'error' => 'Error al crear el seguimiento.']);
}

$stmtSeguimiento->close();
$db->close();
