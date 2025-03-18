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

// =======================
// DATOS DEL ASESOR LOGUEADO
// =======================
$idUsuario = $_SESSION['id_usuario'];
$sqlUsuario = "SELECT Matricula, IdTipo FROM Usuarios WHERE IdUsuario = ?";
$stmtUsuario = $db->prepare($sqlUsuario);
$stmtUsuario->bind_param("i", $idUsuario);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();
$usuario = $resultUsuario->fetch_assoc();
$stmtUsuario->close();

if (!$usuario || $usuario['IdTipo'] != 3) { // Solo acceso a asesores
    exit('Error: No autorizado.');
}

$matriculaAsesor = $usuario['Matricula'];

$sqlAsesor = "SELECT IdAsesor, Nombre, ApellidoPaterno, ApellidoMaterno FROM Asesores WHERE Matricula = ?";
$stmtAsesor = $db->prepare($sqlAsesor);
$stmtAsesor->bind_param("s", $matriculaAsesor);
$stmtAsesor->execute();
$resultAsesor = $stmtAsesor->get_result();
$Asesor = $resultAsesor->fetch_assoc();
$stmtAsesor->close();

if (!$Asesor) {
    exit('Error: No se encontraron datos del Asesor.');
}

$nombreAsesor = "{$Asesor['Nombre']} {$Asesor['ApellidoPaterno']} {$Asesor['ApellidoMaterno']}";

// =======================
// DATOS DEL ALUMNO Y SEGUIMIENTO
// =======================

// Obtener la matrícula del alumno desde la URL
if (!isset($_GET['matricula']) || empty($_GET['matricula'])) {
    exit('Error: Matrícula de alumno no especificada.');
}
$matriculaAlumno = $_GET['matricula'];

// Obtener datos del alumno
$stmtAlumno = $db->prepare("SELECT IdAlumno, Nombre, ApellidoPaterno, ApellidoMaterno, IdAsesor, Matricula FROM Alumnos WHERE Matricula = ?");
$stmtAlumno->bind_param("s", $matriculaAlumno);
$stmtAlumno->execute();
$resultAlumno = $stmtAlumno->get_result();
$alumnoData = $resultAlumno->fetch_assoc();
$stmtAlumno->close();

if (!$alumnoData) {
    exit('Error: Alumno no encontrado.');
}

$nombreAlumno = "{$alumnoData['Nombre']} {$alumnoData['ApellidoPaterno']} {$alumnoData['ApellidoMaterno']}";
$idAlumno = $alumnoData['IdAlumno'];

// Obtener datos del asesor interno (buscando en la tabla Asesores)
$stmtAsesor = $db->prepare("SELECT Nombre, ApellidoPaterno, ApellidoMaterno FROM Asesores WHERE IdAsesor = ?");
$stmtAsesor->bind_param("i", $alumnoData['IdAsesor']);
$stmtAsesor->execute();
$resultAsesor = $stmtAsesor->get_result();
$asesor = $resultAsesor->fetch_assoc();
$stmtAsesor->close();

$nombreAsesor = $asesor ? "{$asesor['Nombre']} {$asesor['ApellidoPaterno']} {$asesor['ApellidoMaterno']}" : "No asignado";

// Obtener el seguimiento del alumno
$stmtSeg = $db->prepare("SELECT IdSeguimiento FROM Seguimientos WHERE IdAlumno = ?");
$stmtSeg->bind_param("i", $idAlumno);
$stmtSeg->execute();
$resultSeg = $stmtSeg->get_result();
$seguimientoData = $resultSeg->fetch_assoc();
$stmtSeg->close();

if (!$seguimientoData) {
    exit('Error: Seguimiento no encontrado para el alumno.');
}
$idSeguimiento = $seguimientoData['IdSeguimiento'];

// =======================
// DOCUMENTOS DEL SEGUIMIENTO
// =======================

// Lista de nombres de documentos (según la tabla Documentos)
$documentosList = [
    "SOLICITUD DE RESIDENCIAS PROFESIONALES",
    "LIBERACION DEL SERVICIO SOCIAL",
    "ANTEPROYECTO FIRMADO",
    "CARTA PRESENTACIÓN",
    "CARTA ACEPTACION",
    "LIBERACION Act Compl",
    "ASIGNACION ASESOR",
    "ANEXO 29 PRIMERO",
    "ANEXO 29 SEGUNDO",
    "ANEXO 30",
    "INFORME SEGUIMIENTO RESIDENCIAS PROFESIONALES",
    "INFORME TECNICO",
    "CARTA DE TERMINO DE RESIDENCIAS PROFESIONALES",
    "PORTADA INFORME TECNICO",
    "ACTA CALIFICACION"
];

// Obtener todos los documentos asociados al seguimiento del alumno
$stmtDocs = $db->prepare("SELECT * FROM Documentos WHERE IdSeguimiento = ?");
$stmtDocs->bind_param("i", $idSeguimiento);
$stmtDocs->execute();
$resultDocs = $stmtDocs->get_result();
$documentosSubidos = [];
while ($row = $resultDocs->fetch_assoc()) {
    $documentosSubidos[$row['NombreDoc']] = $row;
}
$stmtDocs->close();
?>
<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualización de Seguimiento - Coordinador</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="../../assets/js/jquery.js"></script>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .hero {
            background-color: #8a2036;
            color: white;
            padding: 3rem 0;
            text-align: center;
        }

        .container-card {
            max-width: 600px;
            margin: auto;
        }

        .card {
            border-radius: 8px;
            background-color: white;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .btn-primary {
            background-color: #8a2036;
            border-color: #8a2036;
        }

        .btn-primary:hover {
            background-color: #7a1c30;
            border-color: #7a1c30;
        }

        .alert {
            display: none;
        }
    </style>
</head>

<body>
    <?php include_once __DIR__ . '/../../includes/header-asesor.php'; ?>

    <div class="hero">
        <h2 class="fw-bold" style="font-size: 2rem;">Visualización de Seguimiento de Alumno</h2>
    </div>

    <main class="container mt-4">
        <!-- Datos del Asesor -->
        <div class="container-card">
            <div class="card">
                <h5><strong>Asesor:</strong> <?= htmlspecialchars($nombreAsesor) ?></h5>
                <p><strong>Matrícula:</strong> <?= htmlspecialchars($matriculaAsesor) ?></p>
            </div>
        </div>
        <!-- Datos del Alumno -->
        <div class="container-card">
            <div class="card">
                <h5><strong>Alumno:</strong> <?= htmlspecialchars($nombreAlumno) ?></h5>
                <p><strong>Matrícula:</strong> <?= htmlspecialchars($matriculaAlumno) ?></p>
                <p><strong>Asesor Interno:</strong> <?= htmlspecialchars($nombreAsesor) ?></p>
            </div>
        </div>

        <!-- Contenedores de Documentos -->
        <div class="row mt-4">
            <?php foreach ($documentosList as $docName): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Documento: <?= htmlspecialchars($docName) ?></h5>
                            <?php if (isset($documentosSubidos[$docName])):
                                $docData = $documentosSubidos[$docName];
                                $NombreDoc = $docData['NombreDoc'];
                                $estado = $docData['Estado'];
                                $NombrePDF = $docData['NombrePDF'];
                                $observacion = isset($docData['Observacion']) ? $docData['Observacion'] : '';
                            ?>
                                <p class="text-success">Archivo subido:<br><?= htmlspecialchars($NombreDoc) ?></p>
                                <p><strong>Estado:</strong> <?= htmlspecialchars($estado) ?></p>
                                <a href="<?= "../../subidas/alumnos/" . rawurlencode($matriculaAlumno) . "/" . rawurlencode($NombrePDF) ?>" target="_blank" class="btn btn-info mb-2">Ver archivo</a>
                                <?php if (!empty($observacion)): ?>
                                    <button class="btn btn-primary mb-2" onclick="abrirModalInformacion('<?= addslashes($observacion) ?>')">Información</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-danger">No se ha subido este archivo</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Modal para Información de Observación -->
    <div class="modal fade" id="modalInformacion" tabindex="-1" aria-labelledby="modalInformacionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalInformacionLabel" class="modal-title">Información</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p id="infoObservacionTexto"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas (opcional, se mantienen en caso de necesitar notificaciones) -->
    <div class="alert alert-success" id="alertSuccess"></div>
    <div class="alert alert-danger" id="alertError"></div>

    <script>
        function abrirModalInformacion(observation) {
            $('#infoObservacionTexto').text(observation);
            $('#modalInformacion').modal('show');
        }

        function mostrarAlerta(tipo, mensaje) {
            const alert = tipo === 'success' ? $('#alertSuccess') : $('#alertError');
            alert.text(mensaje).fadeIn().delay(3000).fadeOut();
        }
    </script>

    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html>