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

if (!$usuario || $usuario['IdTipo'] != 2) { // Solo acceso a alumnos
    exit('Error: No autorizado.');
}

$matricula = $usuario['Matricula'];

// Obtener datos del alumno incluyendo IdProfesor
$sqlAlumno = "SELECT Nombre, ApellidoPaterno, ApellidoMaterno, IdProfesor FROM Alumnos WHERE Matricula = ?";
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
$idProfesor = $alumno['IdProfesor'];

// Obtener IdCarrera del profesor
$sqlProfesor = "SELECT IdCarrera FROM Profesores WHERE IdProfesor = ?";
$stmtProfesor = $db->prepare($sqlProfesor);
$stmtProfesor->bind_param("i", $idProfesor);
$stmtProfesor->execute();
$resultProfesor = $stmtProfesor->get_result();
$profesor = $resultProfesor->fetch_assoc();
$stmtProfesor->close();

if (!$profesor) {
    exit('Error: No se encontró información del profesor asignado.');
}

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

// Consultar archivos ya subidos asociados al profesor
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

$basePath = "../../../GestionResi/subidas/coordinacion/{$nombreCarpeta}/";
?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formatos - Alumno</title>
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
    <?php include_once __DIR__ . '/../../includes/sidebar-estudiante.php'; ?>

    <div class="hero">
        <h2 class="fw-bold" style="font-size: 2rem;">Formatos</h2>
    </div>

    <main class="container mt-4">
        <div class="container-card">
            <div class="card">
                <h5><strong>Alumno:</strong> <?= htmlspecialchars($nombreCompleto) ?></h5>
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
                                <a href="<?= htmlspecialchars($basePath . $archivosSubidos[$doc]) ?>" target="_blank" class="btn btn-info">Descargar formato</a>
                            <?php else: ?>
                                <p class="text-danger">No hay archivo subido.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Alertas -->
    <div class="alert alert-success" id="alertSuccess"></div>
    <div class="alert alert-danger" id="alertError"></div>

    <script>
        function mostrarAlerta(tipo, mensaje) {
            const alert = tipo === 'success' ? $('#alertSuccess') : $('#alertError');
            alert.text(mensaje).fadeIn().delay(3000).fadeOut();
        }
    </script>

    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html>