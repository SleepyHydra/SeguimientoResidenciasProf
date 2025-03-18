<?php
require_once __DIR__ . "/../config/database.php";
require __DIR__ . "/../auth/php/functions.php";

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

// Obtener la matrícula del profesor logueado desde la tabla Usuarios
$sql_matricula = "SELECT Matricula FROM Usuarios WHERE IdUsuario = ? AND IdTipo = 1"; // 1 para Profesor
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

// Obtener el ID del profesor logueado
$sql_profesor_id = "SELECT IdProfesor FROM Profesores WHERE Matricula = ?";
$stmt_profesor_id = $db->prepare($sql_profesor_id);
$stmt_profesor_id->bind_param("s", $matricula['Matricula']);
$stmt_profesor_id->execute();
$resultado_profesor_id = $stmt_profesor_id->get_result();
$profesor = $resultado_profesor_id->fetch_assoc();
$stmt_profesor_id->close();

if (!$profesor) {
    echo "<div class='alert alert-danger'>No se encontró el ID del profesor.</div>";
    exit;
}

$idProfesor = $profesor['IdProfesor'];

// Obtener los datos del profesor
$sql_usuario = "SELECT CONCAT(p.Nombre, ' ', p.ApellidoPaterno, ' ', p.ApellidoMaterno) AS NombreCompleto, c.Nombre AS Carrera 
                FROM Profesores p
                JOIN Carreras c ON p.IdCarrera = c.IdCarrera
                WHERE p.Matricula = ?";
$stmt_usuario = $db->prepare($sql_usuario);
$stmt_usuario->bind_param("s", $matricula['Matricula']);
$stmt_usuario->execute();
$resultado_usuario = $stmt_usuario->get_result();
$usuario = $resultado_usuario->fetch_assoc();
$stmt_usuario->close();

$nombreUsuario = $usuario['NombreCompleto'] ?? 'Usuario no encontrado';
$carreraUsuario = $usuario['Carrera'] ?? 'Carrera no encontrada';

// Obtener los alumnos a cargo del profesor
$sql_alumnos = "SELECT a.IdAlumno, CONCAT(a.Nombre, ' ', a.ApellidoPaterno, ' ', a.ApellidoMaterno) AS NombreCompleto, a.Matricula, a.IdAsesor 
                FROM Alumnos a
                JOIN Profesores p ON a.IdProfesor = p.IdProfesor
                WHERE p.Matricula = ?";
$stmt_alumnos = $db->prepare($sql_alumnos);
$stmt_alumnos->bind_param("s", $matricula['Matricula']);
$stmt_alumnos->execute();
$resultado_alumnos = $stmt_alumnos->get_result();
$alumnos = [];
while ($alumno = $resultado_alumnos->fetch_assoc()) {
    $alumnos[] = $alumno;
}
$stmt_alumnos->close();

// Actualizar IdAsesor a NULL si el asesor está inactivo
$sql_update_alumnos = "UPDATE Alumnos SET IdAsesor = NULL WHERE IdAsesor IN (SELECT IdAsesor FROM Asesores WHERE Estado = 0)";
$db->query($sql_update_alumnos);

// Obtener asesores de la misma carrera, excluyendo los que tienen Estado = 0
$sql_asesores = "SELECT IdAsesor, CONCAT(Nombre, ' ', ApellidoPaterno, ' ', ApellidoMaterno) AS NombreCompleto 
                 FROM Asesores 
                 WHERE IdCarrera = (SELECT IdCarrera FROM Profesores WHERE Matricula = ?)
                 AND Estado != 0"; // Se excluyen asesores inactivos
$stmt_asesores = $db->prepare($sql_asesores);
$stmt_asesores->bind_param("s", $matricula['Matricula']);
$stmt_asesores->execute();
$resultado_asesores = $stmt_asesores->get_result();
$asesores = [];
while ($asesor = $resultado_asesores->fetch_assoc()) {
    $asesores[] = $asesor;
}
$stmt_asesores->close();

// Obtener los alumnos a cargo del profesor con el nombre del asesor
$sql_alumnos = "SELECT a.IdAlumno, CONCAT(a.Nombre, ' ', a.ApellidoPaterno, ' ', a.ApellidoMaterno) AS NombreCompleto, 
                       a.Matricula, a.IdAsesor, 
                       COALESCE(CONCAT(ases.Nombre, ' ', ases.ApellidoPaterno, ' ', ases.ApellidoMaterno), 'Sin asignar') AS NombreAsesor
                FROM Alumnos a
                JOIN Profesores p ON a.IdProfesor = p.IdProfesor
                LEFT JOIN Asesores ases ON a.IdAsesor = ases.IdAsesor
                WHERE p.Matricula = ?";
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
    <style>
        #searchBox {
            margin-bottom: 1rem;
            width: 100%;
        }

        .message {
            display: none;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <?php
    include_once __DIR__ . '/../includes/header.php';
    include_once __DIR__ . '/../includes/sidebar-profesor.php';
    ?>

    <main class="container flex-grow-1 mt-5">
        <h2 class="fw-bold text-center" style="color: #8a2036; font-size: 2rem;">Seguimientos de Alumnos</h2>

        <!-- Recuadro de mensaje -->
        <div id="message" class="alert message" role="alert"></div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-6 mb-4">
                <div class="card text-center mx-auto">
                    <h2 class="card-title">Carrera: <?php echo htmlspecialchars($carreraUsuario); ?></h2>
                    <p><strong>Coordinador:</strong> <?php echo htmlspecialchars($nombreUsuario); ?></p>
                    <p><strong>Matrícula:</strong> <?php echo htmlspecialchars($matricula['Matricula']); ?></p>
                </div>
            </div>
        </div>

        <!-- Barra de búsqueda -->
        <div class="row justify-content-center mb-4">
            <div class="col-12 col-md-6">
                <input type="text" id="searchBox" class="form-control" placeholder="Buscar por nombre, asesor o matrícula">
            </div>
        </div>

        <!-- Listado de alumnos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h6 class="text-center" style="color: #8a2036; font-size: 1.25rem; margin-bottom: 1.5rem;">Tus alumnos a cargo:</h6>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Nombre Completo</th>
                                    <th scope="col">Matrícula</th>
                                    <th scope="col">Asesor</th>
                                    <th scope="col">Acciones</th>
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
                                            <td><?php echo htmlspecialchars($alumno['NombreAsesor']); ?></td>
                                            <td class="text-center">
                                                <a href="php/ver_seguimiento.php?matricula=<?php echo urlencode($alumno['Matricula']); ?>"
                                                    class="btn btn-primary btn-sm"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Ver Seguimiento">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#asignarAsesorModal" data-matricula="<?php echo htmlspecialchars($alumno['Matricula']); ?>">
                                                    Asignar asesor
                                                </button>
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

    <!-- Modal Asignar Asesor -->
    <div class="modal fade" id="asignarAsesorModal" tabindex="-1" aria-labelledby="asignarAsesorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="asignarAsesorModalLabel">Asignar Asesor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="asignarAsesorForm">
                        <div class="mb-3">
                            <label for="asesorSelect" class="form-label">Selecciona un asesor</label>
                            <select class="form-select" id="asesorSelect" required>
                                <option value="" selected disabled>Seleccionar asesor</option>
                                <?php foreach ($asesores as $asesor): ?>
                                    <option value="<?php echo htmlspecialchars($asesor['IdAsesor']); ?>"><?php echo htmlspecialchars($asesor['NombreCompleto']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary" id="btnAsignar" disabled>Asignar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();

            $("#searchBox").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                value = value.normalize("NFD").replace(/[\u0300-\u036f]/g, ""); // Normaliza y quita acentos

                $("#alumnosTable tr").filter(function() {
                    var nombre = $(this).find("td:eq(1)").text().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, ""); // Normaliza y quita acentos
                    var apellidoPaterno = $(this).find("td:eq(2)").text().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, ""); // Normaliza y quita acentos
                    var apellidoMaterno = $(this).find("td:eq(3)").text().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, ""); // Normaliza y quita acentos

                    $(this).toggle(
                        nombre.indexOf(value) > -1 ||
                        apellidoPaterno.indexOf(value) > -1 ||
                        apellidoMaterno.indexOf(value) > -1
                    );
                });
            });


            // Habilitar o deshabilitar el botón de asignar
            $('#asesorSelect').on('change', function() {
                $('#btnAsignar').prop('disabled', !$(this).val());
            });

            // Manejar el envío del formulario
            $('#asignarAsesorForm').on('submit', function(e) {
                e.preventDefault();
                var idAsesor = $('#asesorSelect').val();
                var matriculaAlumno = $('#asignarAsesorModal').data('matricula');

                $.ajax({
                    url: 'php/asignar_asesor.php', // Cambiar la ruta según sea necesario
                    type: 'POST',
                    data: {
                        idAsesor: idAsesor,
                        matricula: matriculaAlumno
                    },
                    success: function(response) {
                        const res = JSON.parse(response);
                        // Mostrar el mensaje en el div
                        $('#message').removeClass('alert-success alert-danger').addClass(res.status === 'success' ? 'alert-success' : 'alert-danger').text(res.message).show();
                        if (res.status === 'success') {
                            $('#asignarAsesorModal').modal('hide'); // Cerrar el modal al asignar
                            setTimeout(function() {
                                location.reload(); // Recargar la página
                            }, 1000);
                        }
                    },
                    error: function() {
                        $('#message').removeClass('alert-success').addClass('alert-danger').text('Ocurrió un error al asignar el asesor.').show();
                    }
                });
            });

            // Pasar la matrícula del alumno al modal
            $('#asignarAsesorModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Botón que activó el modal
                var matricula = button.data('matricula'); // Extraer la matrícula del atributo data-* 
                $(this).data('matricula', matricula); // Almacenar la matrícula en el modal
            });
        });
    </script>
</body>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>

</html>