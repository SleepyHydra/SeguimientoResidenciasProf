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

// Procesar acciones (Aceptar, Rechazar, Agregar observación) vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $document = isset($_POST['document']) ? $_POST['document'] : '';
        $alumnoMatricula = isset($_POST['alumno_matricula']) ? $_POST['alumno_matricula'] : '';

        // Obtener el IdAlumno basado en la matrícula del alumno
        $stmtAlumno = $db->prepare("SELECT IdAlumno FROM Alumnos WHERE Matricula = ?");
        $stmtAlumno->bind_param("s", $alumnoMatricula);
        $stmtAlumno->execute();
        $resultAlumno = $stmtAlumno->get_result();
        $alumno = $resultAlumno->fetch_assoc();
        $stmtAlumno->close();
        if (!$alumno) {
            echo json_encode(["success" => false, "message" => "Alumno no encontrado"]);
            exit;
        }
        $idAlumno = $alumno['IdAlumno'];

        // Obtener el seguimiento del alumno
        $stmtSeg = $db->prepare("SELECT IdSeguimiento FROM Seguimientos WHERE IdAlumno = ?");
        $stmtSeg->bind_param("i", $idAlumno);
        $stmtSeg->execute();
        $resultSeg = $stmtSeg->get_result();
        $seguimiento = $resultSeg->fetch_assoc();
        $stmtSeg->close();
        if (!$seguimiento) {
            echo json_encode(["success" => false, "message" => "Seguimiento no encontrado"]);
            exit;
        }
        $idSeguimiento = $seguimiento['IdSeguimiento'];

        if ($action == 'accept' || $action == 'reject') {
            $newEstado = ($action == 'accept') ? 'Aceptado' : 'Rechazado';
            $stmtUpd = $db->prepare("UPDATE Documentos SET Estado = ? WHERE IdSeguimiento = ? AND NombreDoc = ?");
            $stmtUpd->bind_param("sis", $newEstado, $idSeguimiento, $document);
            if ($stmtUpd->execute()) {
                echo json_encode(["success" => true, "message" => "Documento actualizado a $newEstado"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error al actualizar el documento"]);
            }
            $stmtUpd->close();
            exit;
        } elseif ($action == 'addObservation') {
            $observation = isset($_POST['observation']) ? $_POST['observation'] : '';
            $stmtObs = $db->prepare("UPDATE Documentos SET Observacion = ? WHERE IdSeguimiento = ? AND NombreDoc = ?");
            $stmtObs->bind_param("sis", $observation, $idSeguimiento, $document);
            if ($stmtObs->execute()) {
                echo json_encode(["success" => true, "message" => "Observación agregada"]);
            } else {
                echo json_encode(["success" => false, "message" => "Error al agregar la observación"]);
            }
            $stmtObs->close();
            exit;
        }
    }
}

// =======================
// DATOS DEL PROFESOR LOGUEADO
// =======================
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

$matriculaProfesor = $usuario['Matricula'];

$sqlProfesor = "SELECT IdProfesor, Nombre, ApellidoPaterno, ApellidoMaterno FROM Profesores WHERE Matricula = ?";
$stmtProfesor = $db->prepare($sqlProfesor);
$stmtProfesor->bind_param("s", $matriculaProfesor);
$stmtProfesor->execute();
$resultProfesor = $stmtProfesor->get_result();
$profesor = $resultProfesor->fetch_assoc();
$stmtProfesor->close();

if (!$profesor) {
    exit('Error: No se encontraron datos del profesor.');
}

$nombreProfesor = "{$profesor['Nombre']} {$profesor['ApellidoPaterno']} {$profesor['ApellidoMaterno']}";

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
    <?php include_once __DIR__ . '/../../includes/header.php'; ?>
    <?php include_once __DIR__ . '/../../includes/sidebar-profesor.php'; ?>

    <div class="hero">
        <h2 class="fw-bold" style="font-size: 2rem;">Visualización de Seguimiento de Alumno</h2>
    </div>

    <main class="container mt-4">
        <!-- Datos del Profesor -->
        <div class="container-card">
            <div class="card">
                <h5><strong>Profesor:</strong> <?= htmlspecialchars($nombreProfesor) ?></h5>
                <p><strong>Matrícula:</strong> <?= htmlspecialchars($matriculaProfesor) ?></p>
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
                                <?php if (!empty($observacion)): ?>
                                    <!-- Si hay observación, el botón "Ver archivo" se muestra en guinda -->
                                    <a href="<?= "../../subidas/alumnos/" . rawurlencode($matriculaAlumno) . "/" . rawurlencode($NombrePDF) ?>" target="_blank" class="btn mb-2" style="background-color: #8a2036; border-color: #8a2036; color: white;">Ver archivo</a>
                                <?php else: ?>
                                    <a href="<?= "../../subidas/alumnos/" . rawurlencode($matriculaAlumno) . "/" . rawurlencode($NombrePDF) ?>" target="_blank" class="btn btn-info mb-2">Ver archivo</a>
                                <?php endif; ?>
                                <button class="btn btn-success mb-2" onclick="actualizarEstado('<?= addslashes($docName) ?>', 'accept')">Aceptar</button>
                                <button class="btn btn-danger mb-2" onclick="actualizarEstado('<?= addslashes($docName) ?>', 'reject')">Rechazar</button>
                                <button class="btn btn-warning mb-2" onclick="abrirModalObservacion('<?= addslashes($docName) ?>')">Agregar observación</button>
                                <?php if (!empty($observacion)): ?>
                                    <button class="btn btn-primary mb-2" onclick="abrirModalInformacion('<?= addslashes($observacion) ?>')">Ver observacion</button>
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

    <!-- Modal para Agregar Observación -->
    <div class="modal fade" id="modalObservacion" tabindex="-1" aria-labelledby="modalObservacionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalObservacionLabel" class="modal-title">Agregar Observación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <textarea id="textareaObservacion" class="form-control" placeholder="Escribe tu observación aquí..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarObservacion">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Información de Observación -->
    <div class="modal fade" id="modalInformacion" tabindex="-1" aria-labelledby="modalInformacionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalInformacionLabel" class="modal-title">Información de Observación</h5>
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

    <!-- Alertas -->
    <div class="alert alert-success" id="alertSuccess"></div>
    <div class="alert alert-danger" id="alertError"></div>

    <script>
        let documentoSeleccionado = '';

        function actualizarEstado(documento, accion) {
            $.post("ver_seguimiento.php", {
                document: documento,
                action: accion,
                alumno_matricula: "<?= htmlspecialchars($matriculaAlumno) ?>"
            }, function(response) {
                try {
                    const result = JSON.parse(response);
                    mostrarAlerta(result.success ? 'success' : 'danger', result.message);
                    if (result.success) {
                        setTimeout(() => {
                            location.reload();
                        }, 250);
                    }
                } catch (e) {
                    mostrarAlerta('danger', 'Respuesta inválida del servidor');
                }
            }).fail(function() {
                mostrarAlerta('danger', 'Error al actualizar el estado del documento');
            });
        }

        function abrirModalObservacion(documento) {
            documentoSeleccionado = documento;
            $('#textareaObservacion').val('');
            $('#modalObservacion').modal('show');
        }

        document.getElementById('btnGuardarObservacion').addEventListener('click', function() {
            const observacion = $('#textareaObservacion').val().trim();
            if (observacion === "") {
                mostrarAlerta('danger', 'Escribe una observación');
                return;
            }
            $.post("ver_seguimiento.php", {
                document: documentoSeleccionado,
                action: 'addObservation',
                observation: observacion,
                alumno_matricula: "<?= htmlspecialchars($matriculaAlumno) ?>"
            }, function(response) {
                try {
                    const result = JSON.parse(response);
                    mostrarAlerta(result.success ? 'success' : 'danger', result.message);
                    $('#modalObservacion').modal('hide');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } catch (e) {
                    mostrarAlerta('danger', 'Respuesta inválida del servidor');
                }
            }).fail(function() {
                mostrarAlerta('danger', 'Error al agregar la observación');
            });
        });

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