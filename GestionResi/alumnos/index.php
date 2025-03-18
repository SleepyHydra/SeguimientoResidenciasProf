<?php
// Incluir la base de datos y funciones de autenticación
require_once __DIR__ . "/../config/database.php";
require __DIR__ . "/../auth/php/functions.php";

// Iniciar sesión si aún no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que la sesión esté iniciada
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    exit('Error: Sesión no válida. Inicia sesión nuevamente.');
}
is_logged_in();

$db = conectar_db();
if (!$db) {
    exit('Error de conexión a la base de datos');
}

// Obtener el id_usuario desde la sesión
$idUsuario = $_SESSION['id_usuario'];

// Consultar la tabla Usuarios para obtener la matrícula y el tipo de usuario
$sqlUsuario = "SELECT Matricula, IdTipo FROM Usuarios WHERE IdUsuario = ?";
$stmtUsuario = $db->prepare($sqlUsuario);
$stmtUsuario->bind_param("i", $idUsuario);
$stmtUsuario->execute();
$resultUsuario = $stmtUsuario->get_result();
$usuario = $resultUsuario->fetch_assoc();
$stmtUsuario->close();

if (!$usuario || $usuario['IdTipo'] != 2) {
    exit('Error: No autorizado.');
}

// Obtener la matrícula
$matricula = $usuario['Matricula'];

// Consultar la tabla Alumnos para obtener el IdAlumno usando la matrícula
$sqlAlumno = "SELECT IdAlumno, Nombre, ApellidoPaterno, ApellidoMaterno, IdProfesor, IdAsesor FROM Alumnos WHERE Matricula = ?";
$stmtAlumno = $db->prepare($sqlAlumno);
$stmtAlumno->bind_param("s", $matricula);
$stmtAlumno->execute();
$resultAlumno = $stmtAlumno->get_result();
$alumno = $resultAlumno->fetch_assoc();
$stmtAlumno->close();

if (!$alumno) {
    exit('Error: No se encontraron datos del alumno.');
}

$nombreCompleto = "{$alumno['Nombre']} {$alumno['ApellidoPaterno']} {$alumno['ApellidoMaterno']}";
$idAlumno = $alumno['IdAlumno'];
$idProfesor = $alumno['IdProfesor'];
$idAsesor = $alumno['IdAsesor'];

// Consultar la tabla Profesores para obtener el nombre completo del profesor a cargo
$sqlProfesor = "SELECT Nombre, ApellidoPaterno, ApellidoMaterno FROM Profesores WHERE IdProfesor = ?";
$stmtProfesor = $db->prepare($sqlProfesor);
$stmtProfesor->bind_param("i", $idProfesor);
$stmtProfesor->execute();
$resultProfesor = $stmtProfesor->get_result();
$profesor = $resultProfesor->fetch_assoc();
$stmtProfesor->close();

$nombreProfesor = $profesor
    ? "{$profesor['Nombre']} {$profesor['ApellidoPaterno']} {$profesor['ApellidoMaterno']}"
    : "No asignado";

// Consultar la tabla Asesores para obtener el nombre completo del asesor interno
$nombreAsesor = "No hay asesor asignado"; // Valor por defecto
if ($idAsesor) {
    $sqlAsesor = "SELECT Nombre, ApellidoPaterno, ApellidoMaterno FROM Asesores WHERE IdAsesor = ?";
    $stmtAsesor = $db->prepare($sqlAsesor);
    $stmtAsesor->bind_param("i", $idAsesor);
    $stmtAsesor->execute();
    $resultAsesor = $stmtAsesor->get_result();
    $asesor = $resultAsesor->fetch_assoc();
    $stmtAsesor->close();

    if ($asesor) {
        $nombreAsesor = "{$asesor['Nombre']} {$asesor['ApellidoPaterno']} {$asesor['ApellidoMaterno']}";
    }
}

// Consultar la tabla Seguimientos para ver si ya existe un registro para este alumno
$sqlSeguimientos = "SELECT IdSeguimiento, FechaInicio FROM Seguimientos WHERE IdAlumno = ?";
$stmtSeguimientos = $db->prepare($sqlSeguimientos);
$stmtSeguimientos->bind_param("i", $idAlumno);
$stmtSeguimientos->execute();
$resultSeguimientos = $stmtSeguimientos->get_result();
$seguimientos = $resultSeguimientos->fetch_all(MYSQLI_ASSOC);
$stmtSeguimientos->close();

// Si no existe ningún registro, se considera que aún NO se ha iniciado el seguimiento.
$seguimientoIniciado = !empty($seguimientos);
$segId = $seguimientoIniciado ? $seguimientos[0]['IdSeguimiento'] : null; // Usamos el primer registro

// Consultar los documentos subidos por el alumno, incluyendo el IdDocumento
$documentosSubidos = [];
if ($seguimientoIniciado) {
    $sqlDocumentos = "SELECT IdDocumento, NombreDoc, Estado, Observacion FROM Documentos WHERE IdSeguimiento = ?";
    $stmtDocumentos = $db->prepare($sqlDocumentos);
    $stmtDocumentos->bind_param("i", $segId);
    $stmtDocumentos->execute();
    $resultDocumentos = $stmtDocumentos->get_result();
    while ($doc = $resultDocumentos->fetch_assoc()) {
        $documentosSubidos[$doc['NombreDoc']] = [
            'IdDocumento'   => $doc['IdDocumento'],
            'Estado'        => $doc['Estado'],
            'Observacion'   => $doc['Observacion']
        ];
    }
    $stmtDocumentos->close();
}

// Definir el listado de documentos (utilizado también para la barra de progreso)
$documentos = [
    "SOLICITUD DE RESIDENCIAS PROFESIONALES",
    "LIBERACION DEL SERVICIO SOCIAL",
    "ANTEPROYECTO FIRMADO",
    "CARTA PRESENTACIÓN",
    "CARTA ACEPTACION",
    "LIBERACION ACTIVIDADES COMPLEMENTARIAS",
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
$totalDocs = count($documentos);
$acceptedCount = 0;
foreach ($documentos as $doc) {
    if (isset($documentosSubidos[$doc]) && $documentosSubidos[$doc]['Estado'] === 'Aceptado') {
        $acceptedCount++;
    }
}
$progressPercentage = ($totalDocs > 0) ? round(($acceptedCount / $totalDocs) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mi Seguimiento</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .hero {
            background-color: #8a2036;
            color: white;
            padding: 3rem 0;
        }

        .card {
            border-radius: 8px;
            border: 1px solid #e3e3e3;
            background-color: white;
            padding: 2rem;
            margin-bottom: 2rem;
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
            margin-top: 15px;
            border-radius: 5px;
            padding: 10px;
        }

        .estado-rechazado {
            color: red;
            font-weight: bold;
        }

        .estado-aceptado {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include_once __DIR__ . '/../includes/header.php'; ?>
    <?php include_once __DIR__ . '/../includes/sidebar-estudiante.php'; ?>

    <div class="hero container-fluid text-center">
        <div class="container">
            <div class="card p-3 shadow mb-5">
                <h4 class="fw-bold text-primary">Datos del Estudiante</h4>
                <p><strong>Nombre:</strong> <?= htmlspecialchars($nombreCompleto) ?></p>
                <p><strong>Matrícula:</strong> <?= htmlspecialchars($matricula) ?></p>
                <p><strong>Coordinador:</strong> <?= htmlspecialchars($nombreProfesor) ?></p>
                <p><strong>Asesor interno:</strong> <?= htmlspecialchars($nombreAsesor) ?></p>
            </div>
        </div>
    </div>

    <main class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold">Mi Seguimiento</h1>
            <?php if (!$seguimientoIniciado): ?>
                <button class="btn btn-primary" id="btn-crear-seguimiento">Iniciar Seguimiento</button>
            <?php endif; ?>
        </div>

        <!-- Barra de progreso -->
        <div class="mb-4">
            <small>Tu progreso</small>
            <div class="progress">
                <div class="progress-bar" role="progressbar" style="width: <?= $progressPercentage; ?>%;" aria-valuenow="<?= $acceptedCount; ?>" aria-valuemin="0" aria-valuemax="<?= $totalDocs; ?>">
                    <?= $progressPercentage; ?>%
                </div>
            </div>
        </div>

        <div id="mensaje" class="alert" style="display:none;"></div>

        <div id="lista-seguimientos">
            <?php if (empty($seguimientos)): ?>
                <p class="text-center text-secondary">No hay seguimientos registrados.</p>
            <?php else: ?>
                <?php foreach ($seguimientos as $seguimiento): ?>
                    <div class="card mb-4 shadow">
                        <div class="card-body">
                            <h5 class="card-subtitle mb-2 text-muted">
                                Seguimiento iniciado el: <?= date('d/m/Y', strtotime($seguimiento['FechaInicio'])) ?>
                            </h5>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($seguimientoIniciado): ?>
            <div id="contenedores-archivos" class="row mt-4">
                <?php foreach ($documentos as $docKey => $docValue):
                    $docSubido = isset($documentosSubidos[$docValue]);
                    $estadoClass = '';
                    if ($docSubido) {
                        $estado = $documentosSubidos[$docValue]['Estado'];
                        $estadoClass = ($estado === 'Rechazado') ? 'estado-rechazado' : (($estado === 'Aceptado') ? 'estado-aceptado' : '');
                    }
                ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <?php if ($docSubido): ?>
                                    <?php if ($documentosSubidos[$docValue]['Estado'] === 'Rechazado'): ?>
                                        <h5 class="card-title"><?= htmlspecialchars($docValue) ?></h5>
                                        <p>Archivo subido: <span class="estado-rechazado"><?= htmlspecialchars($documentosSubidos[$docValue]['Estado']) ?></span></p>
                                        <!-- Input y botón para actualizar el archivo rechazado -->
                                        <input type="file" class="form-control" name="file_<?= $docKey ?>" id="file_<?= $docKey ?>" data-doc="<?= htmlspecialchars($docValue) ?>" data-iddocumento="<?= htmlspecialchars($documentosSubidos[$docValue]['IdDocumento']) ?>" accept="application/pdf">
                                        <button class="btn btn-primary mt-2 btn-subir" data-doc="<?= htmlspecialchars($docValue) ?>" data-iddocumento="<?= htmlspecialchars($documentosSubidos[$docValue]['IdDocumento']) ?>">Actualizar archivo</button>
                                        <button class="btn btn-info mt-2 btn-info-doc" data-doc="<?= htmlspecialchars($docValue) ?>" data-bs-toggle="modal" data-bs-target="#modalObservaciones">
                                            <i class="fas fa-info-circle"></i> Información
                                        </button>
                                    <?php else: ?>
                                        <h5 class="card-title"><?= htmlspecialchars($docValue) ?></h5>
                                        <p>Archivo subido</p>
                                        <p><strong>Estado:</strong> <span class="<?= $estadoClass ?>"><?= htmlspecialchars($documentosSubidos[$docValue]['Estado']) ?></span></p>
                                        <button class="btn btn-info btn-info-doc" data-doc="<?= htmlspecialchars($docValue) ?>" data-bs-toggle="modal" data-bs-target="#modalObservaciones">
                                            <i class="fas fa-info-circle"></i> Información
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <h5 class="card-title"> <?= htmlspecialchars($docValue) ?></h5>
                                    <input type="file" class="form-control" name="file_<?= $docKey ?>" id="file_<?= $docKey ?>" data-doc="<?= htmlspecialchars($docValue) ?>" accept="application/pdf">
                                    <button class="btn btn-primary mt-2 btn-subir" data-doc="<?= htmlspecialchars($docValue) ?>">Subir</button>
                                    <button class="btn btn-info mt-2 btn-info-doc" data-doc="<?= htmlspecialchars($docValue) ?>" data-bs-toggle="modal" data-bs-target="#modalObservaciones">
                                        <i class="fas fa-info-circle"></i> Información
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Modal para observaciones -->
            <div class="modal fade" id="modalObservaciones" tabindex="-1" aria-labelledby="modalObservacionesLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalObservacionesLabel">Observaciones</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="modalObservacionesBody">
                            <!-- Contenido dinámico -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Almacenar datos relevantes en el localStorage
        localStorage.setItem('matricula', '<?= htmlspecialchars($matricula) ?>');
        localStorage.setItem('idSeguimiento', '<?= htmlspecialchars($segId) ?>');
        console.log("Matrícula almacenada:", localStorage.getItem('matricula'));
    </script>
    <script src="../assets/js/jquery.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#btn-crear-seguimiento').click(function() {
                $(this).prop('disabled', true);
                $.post('php/inicio_seguimiento.php', {
                        matricula: localStorage.getItem('matricula')
                    })
                    .done(function(res) {
                        let respuesta;
                        try {
                            respuesta = typeof res === 'object' ? res : JSON.parse(res);
                        } catch (e) {
                            mostrarMensaje("Error al interpretar la respuesta del servidor.", "danger");
                            return;
                        }
                        if (respuesta.ok) {
                            mostrarMensaje('Seguimiento iniciado correctamente.', "success");
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            mostrarMensaje('Error: ' + (respuesta.error || 'No se pudo iniciar el seguimiento.'), "danger");
                        }
                    })
                    .fail(function(err) {
                        console.error("Error en la petición:", err);
                        mostrarMensaje('Error al iniciar seguimiento.', "danger");
                    })
                    .always(function() {
                        $('#btn-crear-seguimiento').prop('disabled', false);
                    });
            });

            $('.btn-subir').click(function() {
                const docName = $(this).data('doc');
                const fileInput = $(this).siblings('input[type="file"]')[0];
                const file = fileInput.files[0];
                const $cardBody = $(this).closest('.card-body');

                if (!file) {
                    mostrarMensaje('Por favor, selecciona un archivo PDF para subir.', "danger");
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);
                formData.append('matricula', localStorage.getItem('matricula'));
                formData.append('idSeguimiento', localStorage.getItem('idSeguimiento'));
                formData.append('nombreDoc', docName);

                // Si se está actualizando (archivo rechazado), se envía el idDocumento
                const idDocumento = $(this).data('iddocumento');
                if (idDocumento) {
                    formData.append('idDocumento', idDocumento);
                }

                $.ajax({
                    url: 'php/subir_documento.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        let respuesta;
                        try {
                            respuesta = typeof res === 'object' ? res : JSON.parse(res);
                        } catch (e) {
                            mostrarMensaje("Error al interpretar la respuesta del servidor.", "danger");
                            return;
                        }
                        if (respuesta.ok) {
                            mostrarMensaje('Archivo subido correctamente.', "success");
                            // Se actualiza el contenedor; se espera que en la respuesta se indique el nuevo estado ("Por revisar")
                            $cardBody.html(`
                                <h5 class="card-title">${docName}</h5>
                                <p>Archivo subido</p>
                                <p><strong>Estado:</strong> <span class="${respuesta.estado === 'Rechazado' ? 'estado-rechazado' : (respuesta.estado === 'Aceptado' ? 'estado-aceptado' : '')}">${respuesta.estado || 'Por revisar'}</span></p>
                                <button class="btn btn-info btn-info-doc" data-doc="${docName}" data-bs-toggle="modal" data-bs-target="#modalObservaciones">
                                    <i class="fas fa-info-circle"></i> Información
                                </button>
                            `);
                        } else {
                            mostrarMensaje('Error: ' + (respuesta.error || 'No se pudo subir el archivo.'), "danger");
                        }
                    },
                    error: function(err) {
                        console.error("Error en la petición:", err);
                        mostrarMensaje('Error al subir el archivo.', "danger");
                    }
                });
            });

            $('.btn-info-doc').click(function() {
                const docName = $(this).data('doc');
                $.ajax({
                    url: 'php/obtener_observaciones.php',
                    type: 'POST',
                    data: JSON.stringify({
                        idSeguimiento: localStorage.getItem('idSeguimiento'),
                        nombreDoc: docName
                    }),
                    contentType: 'application/json',
                    success: function(res) {
                        let respuesta = typeof res === 'object' ? res : JSON.parse(res);
                        if (respuesta.ok) {
                            $('#modalObservacionesBody').text(respuesta.Observacion || 'No tienes observaciones');
                        } else {
                            $('#modalObservacionesBody').text('Error al cargar observaciones');
                        }
                    },
                    error: function() {
                        $('#modalObservacionesBody').text('Error al conectar con el servidor');
                    }
                });
            });

            function mostrarMensaje(mensaje, tipo) {
                $('#mensaje').removeClass('alert-success alert-danger').addClass('alert-' + tipo).text(mensaje).show();
                setTimeout(() => {
                    $('#mensaje').fadeOut();
                }, 2000);
            }
        });
    </script>
</body>
<?php include_once __DIR__ . '../../includes/footer.php'; ?>

</html>