<?php
// Incluir la configuración de la base de datos
require_once __DIR__ . "/../../config/database.php";

// Conectar a la base de datos
$db = conectar_db();
if (!$db) {
    echo json_encode(["ok" => false, "error" => "Error de conexión a la base de datos"]);
    exit;
}

// Configurar el encabezado para devolver JSON
header('Content-Type: application/json');

// Leer el cuerpo de la solicitud
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Obtener los datos enviados desde el frontend
$idSeguimiento = isset($data['idSeguimiento']) ? $data['idSeguimiento'] : null;
$nombreDoc = isset($data['nombreDoc']) ? $data['nombreDoc'] : null;

// Validar que los datos sean válidos
if (!$idSeguimiento || !$nombreDoc) {
    echo json_encode(["ok" => false, "error" => "Faltan parámetros requeridos"]);
    exit;
}

// Preparar la consulta para obtener las observaciones
$sql = "SELECT Observacion FROM Documentos WHERE IdSeguimiento = ? AND NombreDoc = ?";
$stmt = $db->prepare($sql);
if (!$stmt) {
    echo json_encode(["ok" => false, "error" => "Error al preparar la consulta"]);
    exit;
}

// Vincular parámetros y ejecutar
$stmt->bind_param("is", $idSeguimiento, $nombreDoc);
$stmt->execute();
$result = $stmt->get_result();

// Obtener el resultado
if ($row = $result->fetch_assoc()) {
    $Observacion = $row['Observacion'] !== null ? $row['Observacion'] : null;
    echo json_encode(["ok" => true, "Observacion" => $Observacion]);
} else {
    // Si no se encuentra el documento, devolver observaciones como null
    echo json_encode(["ok" => true, "Observacion" => null]);
}

// Cerrar la declaración y la conexión
$stmt->close();
$db->close();
