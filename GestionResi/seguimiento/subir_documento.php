<?php
header('Content-Type: application/json');
error_reporting(0); // Puedes activar la visualización de errores en desarrollo

require_once __DIR__ . "/../config/database.php";

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// (Opcional) Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['ok' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

// Validar la matrícula enviada por POST
$matricula = $_POST['matricula'] ?? null;
if (!$matricula) {
    echo json_encode(['ok' => false, 'error' => 'Matrícula no proporcionada']);
    exit;
}

// Obtener el nombre del documento (label) enviado por POST
$docName = $_POST['doc'] ?? null;
if (!$docName) {
    echo json_encode(['ok' => false, 'error' => 'Nombre del documento no proporcionado']);
    exit;
}

// Verificar que se haya enviado un archivo
if (!isset($_FILES['file'])) {
    echo json_encode(['ok' => false, 'error' => 'Archivo no proporcionado']);
    exit;
}

$file = $_FILES['file'];

// Verificar errores en la subida
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok' => false, 'error' => 'Error en la carga del archivo']);
    exit;
}

// Validar que el archivo sea PDF
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if ($mime !== 'application/pdf') {
    echo json_encode(['ok' => false, 'error' => 'Solo se permiten archivos PDF']);
    exit;
}

// Preparar la carpeta de subida (se espera que la carpeta uploads esté en resi/GestionResi/uploads/)
$uploadsDir = __DIR__ . "/../uploads/";
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
}
$uniqueName = uniqid() . '_' . basename($file['name']);
$destination = $uploadsDir . $uniqueName;
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['ok' => false, 'error' => 'Error al mover el archivo']);
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
    echo json_encode(['ok' => false, 'error' => 'Alumno no encontrado']);
    exit;
}

// Insertar en la tabla Seguimientos la información del documento subido
$sql = "INSERT INTO Seguimientos (IdAlumno, NombreDoc, NombrePDF, Estado, FechaSubida) VALUES (?, ?, ?, 'pendiente', NOW())";
$stmt = $db->prepare($sql);
$stmt->bind_param("iss", $alumno['IdAlumno'], $docName, $uniqueName);
if ($stmt->execute()) {
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'error' => 'Error al insertar en la base de datos']);
}
$stmt->close();
?>
