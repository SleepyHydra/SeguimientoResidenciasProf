<?php
// Incluir la base de datos y funciones de autenticación
require_once __DIR__ . "/../../config/database.php";
require __DIR__ . "/../../auth/php/functions.php";

// Iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar sesión
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    exit('Error: Sesión no válida. Inicia sesión nuevamente.');
}
is_logged_in();

$db = conectar_db();
if (!$db) {
    exit('Error de conexión a la base de datos');
}

// Obtener datos del usuario logueado
$idUsuario = $_SESSION['id_usuario'];
$sqlUsuario = "SELECT Matricula, IdTipo FROM Usuarios WHERE IdUsuario = ?";
$stmtUsuario = $db->prepare($sqlUsuario);
$stmtUsuario->bind_param("i", $idUsuario);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();
$usuario = $resultUsuario->fetch_assoc();
$stmtUsuario->close();

if (!$usuario || $usuario['IdTipo'] != 1) { // Solo acceso a profesores
    exit('Error: No autorizado.');
}

$matricula = $usuario['Matricula'];

// Obtener datos del profesor incluyendo IdCarrera
$sqlProfesor = "SELECT IdProfesor, IdCarrera FROM Profesores WHERE Matricula = ?";
$stmtProfesor = $db->prepare($sqlProfesor);
$stmtProfesor->bind_param("s", $matricula);
$stmtProfesor->execute();
$resultProfesor = $stmtProfesor->get_result();
$profesor = $resultProfesor->fetch_assoc();
$stmtProfesor->close();

if (!$profesor) {
    exit('Error: No se encontraron datos del profesor.');
}

$idProfesor = $profesor['IdProfesor'];
$idCarrera = $profesor['IdCarrera'];

// Determinar el nombre de la carpeta según IdCarrera
$carreras = [
    1 => "Ing_Electromecanica",
    2 => "Ing_Electronica",
    3 => "Ing_Industrial",
    4 => "Ing_Informatica",
    5 => "Ing_Sistemas",
    6 => "Ing_Administracion"
];
$nombreCarpeta = isset($carreras[$idCarrera]) ? $carreras[$idCarrera] : "Sin_Carrera";

// Manejar la subida de archivos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && isset($_POST['document'])) {
    $document = $_POST['document'];
    $baseUploadDir = __DIR__ . "/../../../GestionResi/subidas/coordinacion/";
    $uploadDir = $baseUploadDir . $nombreCarpeta . "/";

    // Verificar si el directorio de subida existe, si no, crearlo
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Obtener la extensión del archivo original y validar
    $fileOriginalName = basename($_FILES['file']['name']);
    $validExtensions = ['pdf', 'doc', 'docx'];
    $fileExtension = strtolower(pathinfo($fileOriginalName, PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $validExtensions)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Solo se permiten PDF y Word.']);
        exit;
    }

    // Renombrar el archivo según el apartado correspondiente agregando "formato_"
    $newFileName = "formato_" . $document . "." . $fileExtension;
    $uploadFile = $uploadDir . $newFileName;

    // Subir el archivo
    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadFile)) {
        $sqlInsert = "INSERT INTO DocsSubidos (IdProfesor, Nombre, NombrePDF) VALUES (?, ?, ?)";
        $stmtInsert = $db->prepare($sqlInsert);
        $stmtInsert->bind_param("iss", $idProfesor, $document, $newFileName);
        $stmtInsert->execute();
        $stmtInsert->close();

        echo json_encode(['success' => true, 'message' => 'Archivo subido correctamente', 'file' => $newFileName]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al subir el archivo']);
    }
    exit; // Terminar el script después de procesar la solicitud
}

// Manejar la eliminación de archivos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['document']) && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $document = $_POST['document'];

    // Obtener el nombre del archivo asociado al documento para el profesor
    $sqlGet = "SELECT NombrePDF FROM DocsSubidos WHERE IdProfesor = ? AND Nombre = ?";
    $stmtGet = $db->prepare($sqlGet);
    $stmtGet->bind_param("is", $idProfesor, $document);
    $stmtGet->execute();
    $resultGet = $stmtGet->get_result();
    $fileData = $resultGet->fetch_assoc();
    $stmtGet->close();

    if (!$fileData) {
        echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
        exit;
    }

    $fileName = $fileData['NombrePDF'];

    // Eliminar el registro de la base de datos
    $sqlDelete = "DELETE FROM DocsSubidos WHERE IdProfesor = ? AND Nombre = ?";
    $stmtDelete = $db->prepare($sqlDelete);
    $stmtDelete->bind_param("is", $idProfesor, $document);
    $stmtDelete->execute();
    $stmtDelete->close();

    $uploadDir = __DIR__ . "/../../../GestionResi/subidas/coordinacion/{$nombreCarpeta}/";
    if (file_exists($uploadDir . $fileName) && unlink($uploadDir . $fileName)) {
        echo json_encode(['success' => true, 'message' => 'Archivo eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el archivo o archivo no encontrado']);
    }
    exit;
}
