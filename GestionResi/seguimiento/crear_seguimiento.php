<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../config/database.php";

// Obtener la matrícula enviada por POST
$matricula = $_POST['matricula'] ?? null;
if (!$matricula) {
    echo json_encode(['ok' => false, 'error' => 'Matrícula no proporcionada.']);
    exit;
}

// Conectar a la base de datos
$db = conectar_db();
if (!$db) {
    echo json_encode(['ok' => false, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

// Obtener el IdAlumno usando la matrícula
$sqlAlumno = "SELECT IdAlumno FROM Alumnos WHERE Matricula = ?";
$stmtAlumno = $db->prepare($sqlAlumno);
$stmtAlumno->bind_param("s", $matricula);
$stmtAlumno->execute();
$resultAlumno = $stmtAlumno->get_result();
$alumno = $resultAlumno->fetch_assoc();
$stmtAlumno->close();

if (!$alumno) {
    echo json_encode(['ok' => false, 'error' => 'Alumno no encontrado.']);
    exit;
}

// Verificar si ya existe un seguimiento para este alumno
$sqlSeguimientoExistente = "SELECT IdSeguimiento FROM Seguimientos WHERE IdAlumno = ?";
$stmtSeguimientoExistente = $db->prepare($sqlSeguimientoExistente);
$stmtSeguimientoExistente->bind_param("i", $alumno['IdAlumno']);
$stmtSeguimientoExistente->execute();
$resultSeguimientoExistente = $stmtSeguimientoExistente->get_result();
if ($resultSeguimientoExistente->num_rows > 0) {
    echo json_encode(['ok' => false, 'error' => 'Ya existe un seguimiento para este alumno.']);
    exit;
}

// Insertar un nuevo registro en Seguimientos (registro inicial sin documento)
$sqlSeguimiento = "INSERT INTO Seguimientos (IdAlumno, FechaInicio) VALUES (?, NOW())";
$stmtSeguimiento = $db->prepare($sqlSeguimiento);
$stmtSeguimiento->bind_param("i", $alumno['IdAlumno']);
if ($stmtSeguimiento->execute()) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'error' => 'Error al insertar el seguimiento.']);
}
$stmtSeguimiento->close();
?>
