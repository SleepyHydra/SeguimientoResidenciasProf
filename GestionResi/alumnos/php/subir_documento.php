<?php
// Incluir la base de datos
require_once __DIR__ . "/../../config/database.php";
session_start();

if (!isset($_SESSION['id_usuario'])) {
    exit(json_encode(['ok' => false, 'error' => 'No estás autorizado']));
}

$db = conectar_db();
if (!$db) {
    exit(json_encode(['ok' => false, 'error' => 'Error de conexión a la base de datos']));
}

$matricula     = $_POST['matricula'];
$idSeguimiento = $_POST['idSeguimiento'];
$nombreDoc     = $_POST['nombreDoc'];

// Definir arreglo de mapeo para asignar el nombre interno (sin espacios y con guiones cuando corresponda)
$docMapping = [
    "SOLICITUD DE RESIDENCIAS PROFESIONALES"       => "SOLICITUD_RP",
    "LIBERACION DEL SERVICIO SOCIAL"               => "LIBERACION_SS",
    "ANTEPROYECTO FIRMADO"                         => "ANTEPROYECTO_FIRMADO",
    "CARTA PRESENTACIÓN"                           => "CARTA_PRESENTACIÓN",
    "CARTA ACEPTACION"                             => "CARTA_ACEPTACION",
    "LIBERACION Act Compl"                         => "LIBERACION_AC",
    "ASIGNACION ASESOR"                            => "ASIGNACION_ASESOR",
    "ANEXO 29 PRIMERO"                             => "ANEXO29-1",
    "ANEXO 29 SEGUNDO"                             => "ANEXO29-2",
    "ANEXO 30"                                     => "ANEXO30",
    "INFORME SEGUIMIENTO RESIDENCIAS PROFESIONALES"  => "INFORME_SEGUIMIENTO_RP",
    "INFORME TECNICO"                              => "INFORME_TECNICO",
    "CARTA DE TERMINO DE RESIDENCIAS PROFESIONALES"  => "CARTA_TERMINO",
    "PORTADA INFORME TECNICO"                        => "PORTADA_INFORME_TECNICO",
    "ACTA CALIFICACION"                            => "ACTA_CALIFICACION"
];

$internalName = isset($docMapping[$nombreDoc]) ? $docMapping[$nombreDoc] : str_replace(' ', '_', $nombreDoc);
$internalNameWithExt = $internalName . ".pdf";

// Verificar si se trata de una actualización (re-subida de archivo rechazado)
$update = false;
if (isset($_POST['idDocumento']) && !empty($_POST['idDocumento'])) {
    $idDocumento = $_POST['idDocumento'];
    $update = true;
}

// Verificar si se recibió el archivo
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    exit(json_encode(['ok' => false, 'error' => 'No se ha subido ningún archivo o hubo un error.']));
}

// Obtener información del archivo
$file         = $_FILES['file'];
$fileNameOrig = basename($file['name']);
$fileTmpPath  = $file['tmp_name'];
$fileType     = pathinfo($fileNameOrig, PATHINFO_EXTENSION);

// Verificar que el archivo sea un PDF
if (strtolower($fileType) !== 'pdf') {
    exit(json_encode(['ok' => false, 'error' => 'El archivo debe ser un PDF.']));
}

// Crear directorio para almacenar archivos del alumno si no existe
$dir = __DIR__ . "/../../subidas/alumnos/$matricula";
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Definir la ruta de destino usando el nombre interno con extensión .pdf
$destino = "$dir/$internalNameWithExt";

// Si es actualización, eliminar el archivo anterior si existe
if ($update && file_exists($destino)) {
    unlink($destino);
}

// Mover el archivo a la ubicación deseada
if (move_uploaded_file($fileTmpPath, $destino)) {
    if ($update) {
        // Actualizar registro existente: se reemplaza el archivo, se pone el estado a "Por revisar" y se actualiza la fecha de subida
        $sqlUpdate = "UPDATE Documentos SET NombrePDF = ?, Observacion = NULL, Estado = 'Por revisar', FechaSubida = NOW() WHERE IdDocumento = ?";
        $stmt = $db->prepare($sqlUpdate);
        $stmt->bind_param("si", $internalNameWithExt, $idDocumento);
        if ($stmt->execute()) {
            $stmt->close();
            exit(json_encode(['ok' => true, 'message' => 'Archivo actualizado y registro modificado correctamente.']));
        } else {
            exit(json_encode(['ok' => false, 'error' => 'Error al actualizar el registro en la base de datos.']));
        }
    } else {
        // Insertar un nuevo registro en la tabla Documentos
        $sqlInsert = "INSERT INTO Documentos (IdSeguimiento, NombreDoc, NombrePDF, Observacion, Estado, FechaSubida) VALUES (?, ?, ?, NULL, 'Por revisar', NOW())";
        $stmt = $db->prepare($sqlInsert);
        $stmt->bind_param("iss", $idSeguimiento, $nombreDoc, $internalNameWithExt);
        if ($stmt->execute()) {
            $stmt->close();
            exit(json_encode(['ok' => true, 'message' => 'Archivo subido y registro creado correctamente.']));
        } else {
            exit(json_encode(['ok' => false, 'error' => 'Error al registrar en la base de datos.']));
        }
    }
} else {
    exit(json_encode(['ok' => false, 'error' => 'Error al mover el archivo.']));
}
