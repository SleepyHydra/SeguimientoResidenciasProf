<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../auth/php/functions.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

is_logged_in();

$db = conectar_db();
if (!$db) {
    die("Error de conexión a la base de datos.");
}

$idUsuario = $_SESSION['id_usuario'];

$sql_profesor = "SELECT P.IdProfesor, P.Nombre, P.ApellidoPaterno, P.ApellidoPaterno, P.Matricula FROM Profesores P 
                 INNER JOIN Usuarios U ON P.Matricula = U.Matricula 
                 WHERE U.IdUsuario = ? AND U.IdTipo = 1";

$stmt = $db->prepare($sql_profesor);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();
$profesor = $result->fetch_assoc();
$stmt->close();

if (!$profesor) {
    die("No se encontró información del profesor.");
}

$nombreProfesor = $profesor['Nombre'] . ' ' . $profesor['ApellidoPaterno'] . ' ' . $profesor['ApellidoPaterno'];
$matriculaProfesor = $profesor['Matricula'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cargar Alumnos | Seguimiento de Residencias</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script defer src="../js/carga_alumnos.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        header {
            background-color: white;
            color: #8a2036;
            padding: 1rem;
            border-bottom: 10px solid #8a2036;
            text-align: center;
        }

        header .logo {
            display: inline-flex;
            align-items: center;
            gap: 15px;
        }

        header img {
            height: 60px;
        }

        header h1 {
            font-size: 1.5rem;
            margin: 0;
            font-weight: bold;
        }

        main h2 {
            color: #8a2036;
            font-size: 1.5rem;
        }

        .btn-primary {
            background-color: #8a2036;
            border-color: #8a2036;
        }

        .btn-primary:hover {
            background-color: #7a1c30;
            border-color: #7a1c30;
        }

        .card {
            background-color: #ffffff;
            border: 1px solid #e3e3e3;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .card-title {
            color: #8a2036;
            font-size: 1.2rem;
        }

        .form-label {
            color: #8a2036;
        }
    </style>
</head>

<body>
    <?php include_once __DIR__ . '/../../includes/header.php'; ?>
    <?php include_once __DIR__ . '/../../includes/sidebar-profesor.php'; ?>

    <main class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 mb-4">
                <div class="card text-center mx-auto">
                    <h2 class="card-title">Cargar Alumnos</h2>
                    <p><strong>Coordinador:</strong> <?php echo htmlspecialchars($nombreProfesor); ?></p>
                    <p><strong>Matrícula:</strong> <?php echo htmlspecialchars($matriculaProfesor); ?></p>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-6">
                <div class="card">
                    <div id="mensaje"></div>

                    <div class="mb-3">
                        <label for="archivo" class="form-label">Seleccionar archivo (CSV o Excel)</label>
                        <input type="file" id="archivo" class="form-control" accept=".csv, .xlsx, .xls">
                    </div>

                    <button type="button" id="btnSubir" class="btn btn-primary btn-block">Subir archivo</button>
                    <button type="button" id="btnAgregarAlumno" class="btn btn-success btn-block mt-2" data-bs-toggle="modal" data-bs-target="#modalAgregarAlumno">
                        Agregar Alumno Individualmente
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para agregar un alumno -->
    <div class="modal fade" id="modalAgregarAlumno" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Agregar Nuevo Alumno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="mensaje-modal"></div>
                    <form id="formAgregarAlumno">
                        <div class="mb-3">
                            <label for="matricula" class="form-label">Matrícula</label>
                            <input type="text" id="matricula" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" id="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellidoPaterno" class="form-label">Apellido Paterno</label>
                            <input type="text" id="apellidoPaterno" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellidoMaterno" class="form-label">Apellido Materno</label>
                            <input type="text" id="apellidoMaterno" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" id="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Registrar Alumno</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>

<?php include_once __DIR__ . '/../../includes/footer.php'; ?>

</html>