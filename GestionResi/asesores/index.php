<?php

require_once __DIR__ . "../../config/database.php";
require __DIR__ . "../../auth/php/functions.php";

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
is_logged_in();

$db = conectar_db();
if (!$db) {
    exit('Error de conexión a la base de datos');
}

// Verificar si el ID de usuario está configurado correctamente en la sesión
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    exit('ID de usuario no encontrado en la sesión.');
}

// Obtener el ID del usuario actualmente autenticado
$idUsuario = $_SESSION['id_usuario'];

// Obtener la matrícula del asesor logueado desde la tabla Usuarios
$sql_matricula = "SELECT Matricula FROM Usuarios WHERE IdUsuario = ? AND IdTipo = 3";  // 3 para Asesor
$stmt_matricula = $db->prepare($sql_matricula);
$stmt_matricula->bind_param("i", $idUsuario);
$stmt_matricula->execute();
$resultado_matricula = $stmt_matricula->get_result();
$matricula = $resultado_matricula->fetch_assoc();
$stmt_matricula->close();

if (!$matricula) {
    echo "<div class='alert alert-danger'>No se encontró la matrícula para este usuario.</div>";
    exit;
}

// Obtener el ID del asesor logueado
$sql_asesor_id = "SELECT IdAsesor FROM Asesores WHERE Matricula = ?";
$stmt_asesor_id = $db->prepare($sql_asesor_id);
$stmt_asesor_id->bind_param("s", $matricula['Matricula']);
$stmt_asesor_id->execute();
$resultado_asesor_id = $stmt_asesor_id->get_result();
$asesor = $resultado_asesor_id->fetch_assoc();
$stmt_asesor_id->close();

if (!$asesor) {
    echo "<div class='alert alert-danger'>No se encontró el ID del asesor.</div>";
    exit;
}

$idAsesor = $asesor['IdAsesor'];

// Obtener los datos del asesor
$sql_usuario = "SELECT CONCAT(a.Nombre, ' ', a.ApellidoPaterno, ' ', a.ApellidoMaterno) AS NombreCompleto, c.Nombre AS Carrera 
                FROM Asesores a
                JOIN Carreras c ON a.IdCarrera = c.IdCarrera
                WHERE a.Matricula = ?";
$stmt_usuario = $db->prepare($sql_usuario);
$stmt_usuario->bind_param("s", $matricula['Matricula']);
$stmt_usuario->execute();
$resultado_usuario = $stmt_usuario->get_result();
$usuario = $resultado_usuario->fetch_assoc();
$stmt_usuario->close();

$nombreUsuario = $usuario['NombreCompleto'] ?? 'Usuario no encontrado';
$carreraUsuario = $usuario['Carrera'] ?? 'Carrera no encontrada';

// Obtener los alumnos a cargo del asesor
$sql_alumnos = "SELECT CONCAT(a.Nombre, ' ', a.ApellidoPaterno, ' ', a.ApellidoMaterno) AS NombreCompleto, a.Matricula 
                FROM Alumnos a
                JOIN Asesores ase ON a.IdAsesor = ase.IdAsesor
                WHERE ase.Matricula = ?";
$stmt_alumnos = $db->prepare($sql_alumnos);
$stmt_alumnos->bind_param("s", $matricula['Matricula']);
$stmt_alumnos->execute();
$resultado_alumnos = $stmt_alumnos->get_result();
$alumnos = [];
while ($alumno = $resultado_alumnos->fetch_assoc()) {
    $alumnos[] = $alumno;
}
$stmt_alumnos->close();
?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Seguimiento de Residencias Profesionales</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
</head>

<body>
    <?php
    include_once __DIR__ . '/../includes/header-asesor.php';
    ?>

    <main class="container flex-grow-1 mt-5">
        <h2 class="fw-bold text-center" style="color: #8a2036; font-size: 2rem;">Seguimientos de Alumnos</h2>
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 mb-4">
                <div class="card text-center mx-auto">
                    <h2 class="card-title">Carrera: <?php echo htmlspecialchars($carreraUsuario); ?></h2>
                    <p><strong>Asesor:</strong> <?php echo htmlspecialchars($nombreUsuario); ?></p>
                    <p><strong>Matrícula:</strong> <?php echo htmlspecialchars($matricula['Matricula']); ?></p>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mb-4">
            <div class="col-12 col-md-6">
                <input type="text" id="searchBox" class="form-control" placeholder="Buscar por nombre o matrícula">
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h6 class="text-center" style="color: #8a2036; font-size: 1.25rem; margin-bottom: 1.5rem;">Tus alumnos a cargo:</h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre Completo</th>
                                    <th>Matrícula</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="alumnosTable">
                                <?php if (empty($alumnos)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No tienes alumnos a tu cargo.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($alumnos as $index => $alumno): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($alumno['NombreCompleto']); ?></td>
                                            <td><?php echo htmlspecialchars($alumno['Matricula']); ?></td>
                                            <td class="text-center">
                                                <a href="php/ver_seguimiento.php?matricula=<?php echo urlencode($alumno['Matricula']); ?>"
                                                    class="btn btn-primary"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Ver seguimiento">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/jquery.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
</body>

<?php
include_once __DIR__ . '/../includes/footer.php';
?>

</html>