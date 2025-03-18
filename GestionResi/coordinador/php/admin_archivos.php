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
$sqlProfesor = "SELECT IdProfesor, Nombre, ApellidoPaterno, ApellidoMaterno, IdCarrera FROM Profesores WHERE Matricula = ?";
$stmtProfesor = $db->prepare($sqlProfesor);
$stmtProfesor->bind_param("s", $matricula);
$stmtProfesor->execute();
$resultProfesor = $stmtProfesor->get_result();
$profesor = $resultProfesor->fetch_assoc();
$stmtProfesor->close();

if (!$profesor) {
    exit('Error: No se encontraron datos del profesor.');
}

$nombreCompleto = "{$profesor['Nombre']} {$profesor['ApellidoPaterno']} {$profesor['ApellidoMaterno']}";
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

// Documentos requeridos
$documentos = [
    "SOLICITUD_RP",
    "LIBERACION_SS",
    "ANTEPROYECTO_FIRMADO",
    "CARTA_PRESENTACIÓN",
    "CARTA_ACEPTACION",
    "LIBERACION_AC",
    "ASIGNACION_ASESOR",
    "ANEXO29-1",
    "ANEXO29-2",
    "ANEXO30",
    "INFORME_SEGUIMIENTO_RP",
    "INFORME_TECNICO",
    "CARTA_TERMINO",
    "PORTADA_INFORME_TECNICO",
    "ACTA_CALIFICACION"
];

// Consultar archivos ya subidos
$sqlArchivos = "SELECT Nombre, NombrePDF FROM DocsSubidos WHERE IdProfesor = ?";
$stmtArchivos = $db->prepare($sqlArchivos);
$stmtArchivos->bind_param("i", $idProfesor);
$stmtArchivos->execute();
$resultArchivos = $stmtArchivos->get_result();
$archivosSubidos = [];
while ($row = $resultArchivos->fetch_assoc()) {
    $archivosSubidos[$row['Nombre']] = $row['NombrePDF'];
}
$stmtArchivos->close();
?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Archivos - Profesor</title>
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
        <h2 class="fw-bold" style="font-size: 2rem;">Gestión de Archivos</h2>
    </div>

    <main class="container mt-4">
        <div class="container-card">
            <div class="card">
                <h5><strong>Profesor:</strong> <?= htmlspecialchars($nombreCompleto) ?></h5>
                <p><strong>Matrícula:</strong> <?= htmlspecialchars($matricula) ?></p>
                <p><strong>Carrera:</strong> <?= htmlspecialchars($nombreCarpeta) ?></p>
            </div>
        </div>

        <div class="row mt-4">
            <?php foreach ($documentos as $doc): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Documento: <?= htmlspecialchars($doc) ?></h5>
                            <?php if (isset($archivosSubidos[$doc])): ?>
                                <p class="text-success">Archivo subido: <br> <?= htmlspecialchars($archivosSubidos[$doc]) ?></p>
                                <a href="<?= htmlspecialchars("../../../GestionResi/subidas/coordinacion/{$nombreCarpeta}/" . $archivosSubidos[$doc]) ?>" target="_blank" class="btn btn-info">Ver archivo</a>
                                <button class="btn btn-danger" onclick="confirmarEliminar('<?= urlencode($doc) ?>')">Eliminar</button>
                            <?php else: ?>
                                <input type="file" class="form-control mb-2" id="file_<?= urlencode($doc) ?>" accept=".pdf,.doc,.docx">
                                <button class="btn btn-primary" onclick="subirArchivo('<?= urlencode($doc) ?>')">Subir</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-labelledby="modalConfirmacionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmacionLabel">Confirmación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ¿Seguro que deseas eliminar este archivo?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnEliminar">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    <div class="alert alert-success" id="alertSuccess"></div>
    <div class="alert alert-danger" id="alertError"></div>

    <script>
        let documentoAEliminar = '';

        function subirArchivo(doc) {
            const fileInput = document.getElementById(`file_${doc}`);
            const file = fileInput.files[0];
            if (!file) return mostrarAlerta('danger', "Selecciona un archivo");

            let formData = new FormData();
            formData.append("file", file);
            formData.append("document", doc);

            $.ajax({
                url: 'gestion_archivos.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
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
                },
                error: function() {
                    mostrarAlerta('danger', 'Error al subir el archivo');
                }
            });
        }

        function confirmarEliminar(doc) {
            documentoAEliminar = doc;
            $('#modalConfirmacion').modal('show');
        }

        document.getElementById('btnEliminar').addEventListener('click', function() {
            $.post('gestion_archivos.php', {
                document: documentoAEliminar,
                action: 'delete'
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
                mostrarAlerta('danger', "Error al eliminar el archivo");
            });
            $('#modalConfirmacion').modal('hide');
        });

        function mostrarAlerta(tipo, mensaje) {
            const alert = tipo === 'success' ? $('#alertSuccess') : $('#alertError');
            alert.text(mensaje).fadeIn().delay(3000).fadeOut();
        }
    </script>

    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html>