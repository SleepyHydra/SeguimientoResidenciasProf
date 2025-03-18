<?php

require_once __DIR__ . "/../../config/database.php";
require __DIR__ . "/../../auth/php/functions.php";

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

if ($resultado_matricula->num_rows === 0) {
    echo "<div class='alert alert-danger'>No se encontró la matrícula para este usuario.</div>";
    exit;
}

$matricula = $resultado_matricula->fetch_assoc()['Matricula'];
$stmt_matricula->close();

// Obtener los datos del profesor logueado, incluyendo su IdCarrera
$sql_profesor = "SELECT p.Nombre, p.ApellidoPaterno, p.ApellidoMaterno, p.IdCarrera 
                 FROM Profesores p WHERE p.Matricula = ?";
$stmt_profesor = $db->prepare($sql_profesor);
$stmt_profesor->bind_param("s", $matricula);
$stmt_profesor->execute();
$resultado_profesor = $stmt_profesor->get_result();

if ($resultado_profesor->num_rows === 0) {
    echo "<div class='alert alert-danger'>No se encontraron datos del profesor.</div>";
    exit;
}
$profesor = $resultado_profesor->fetch_assoc();
$stmt_profesor->close();

$nombreUsuario = $profesor['Nombre'] . ' ' . $profesor['ApellidoPaterno'] . ' ' . $profesor['ApellidoMaterno'];
$idCarrera = $profesor['IdCarrera'];

$error = '';
$success = '';

// Registrar un nuevo asesor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar'])) {
    $matricula = trim($_POST['matricula']);
    $nombre = trim($_POST['nombre']);
    $apellidoPaterno = trim($_POST['apellido_paterno']);
    $apellidoMaterno = trim($_POST['apellido_materno']);
    $password = trim($_POST['password']); // Obtener la contraseña
    $idTipoUsuario = 3; // IdTipoUsuario para asesores es 3

    if (empty($matricula) || empty($nombre) || empty($apellidoPaterno) || empty($apellidoMaterno) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        // Insertar en la tabla Asesores
        $query_insert = "INSERT INTO Asesores (IdTipoUsuario, Matricula, Nombre, ApellidoPaterno, ApellidoMaterno, IdCarrera) 
                         VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $db->prepare($query_insert);

        if ($stmt_insert) {
            $stmt_insert->bind_param('issssi', $idTipoUsuario, $matricula, $nombre, $apellidoPaterno, $apellidoMaterno, $idCarrera);

            if ($stmt_insert->execute()) {
                // Insertar contraseña en Usuarios
                $query_password = "UPDATE Usuarios SET Password = ? WHERE Matricula = ?";
                $stmt_password = $db->prepare($query_password);
                $stmt_password->bind_param('ss', $password, $matricula);
                if ($stmt_password->execute()) {
                    $success = 'Asesor registrado correctamente.';
                    // Limpiar el formulario después de 3 segundos
                    echo "<script>
                            setTimeout(function() {
                                window.location.href = window.location.href; // Recargar la página para limpiar el formulario
                            }, 3000);
                          </script>";
                } else {
                    $error = 'Error al actualizar la contraseña en Usuarios.';
                }
                $stmt_password->close();
            } else {
                $error = 'Error al registrar el asesor.';
            }
            $stmt_insert->close();
        } else {
            $error = 'Error al preparar la consulta.';
        }
    }
}

// Obtener la lista de asesores que pertenecen al mismo IdCarrera que el profesor logueado
$sql_asesores = "SELECT IdAsesor, Matricula, Nombre, ApellidoPaterno, ApellidoMaterno, Estado 
                 FROM Asesores 
                 WHERE IdCarrera = ?";
$stmt_asesores = $db->prepare($sql_asesores);
$stmt_asesores->bind_param("i", $idCarrera);
$stmt_asesores->execute();
$resultado_asesores = $stmt_asesores->get_result();
$asesores = $resultado_asesores->fetch_all(MYSQLI_ASSOC);
$stmt_asesores->close();

// Cambiar estado del asesor (usando AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $idAsesor = $_POST['id'];
    $nuevoEstado = $_POST['estado'];

    $query_update = "UPDATE Asesores SET Estado = ? WHERE IdAsesor = ?";
    $stmt_update = $db->prepare($query_update);
    $stmt_update->bind_param("ii", $nuevoEstado, $idAsesor);

    if ($stmt_update->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error"]);
    }
    $stmt_update->close();
    exit;
}

// Eliminar asesor (usando AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar'])) {
    $idAsesor = $_POST['id'];

    // Actualizar alumnos para eliminar la referencia al asesor y colocar IdAsesor en 0
    $query_update_alumnos = "UPDATE Alumnos SET IdAsesor = 0 WHERE IdAsesor = ?";
    $stmt_update_alumnos = $db->prepare($query_update_alumnos);
    $stmt_update_alumnos->bind_param("i", $idAsesor);
    $stmt_update_alumnos->execute();
    $stmt_update_alumnos->close();

    // Eliminar el asesor
    $query_delete_asesor = "DELETE FROM Asesores WHERE IdAsesor = ?";
    $stmt_delete_asesor = $db->prepare($query_delete_asesor);
    $stmt_delete_asesor->bind_param("i", $idAsesor);
    $stmt_delete_asesor->execute();
    $stmt_delete_asesor->close();

    echo json_encode(["status" => "success"]);
    exit;
}

?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Administración de Asesores</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="../../assets/css/style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <style>
        #searchBox {
            margin-bottom: 1rem;
            width: 100%;
        }

        .inactivo {
            background-color: #f8d7da;
            /* Color de fondo para inactivo */
        }

        .estado-activo {
            color: green;
            /* Color para estado activo */
        }

        .estado-inactivo {
            color: orange;
            /* Color para estado inactivo */
        }
    </style>
</head>

<body>
    <?php
    include_once __DIR__ . '/../../includes/header.php';
    include_once __DIR__ . '/../../includes/sidebar-profesor.php';
    ?>

    <main class="container flex-grow-1 mt-5">
        <h2 class="fw-bold text-center" style="color: #8a2036; font-size: 2rem;">Administración de Asesores</h2>
        <div class="text-center mb-4">
            <p><strong>Coordinador:</strong> <?php echo htmlspecialchars($nombreUsuario); ?> (<?php echo htmlspecialchars($matricula); ?>)</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST" class="text-center mb-4" id="registroForm">
            <input type="text" name="matricula" class="form-control mb-2" placeholder="Matrícula" required>
            <input type="text" name="nombre" class="form-control mb-2" placeholder="Nombre" required>
            <input type="text" name="apellido_paterno" class="form-control mb-2" placeholder="Apellido Paterno" required>
            <input type="text" name="apellido_materno" class="form-control mb-2" placeholder="Apellido Materno" required>
            <input type="password" name="password" class="form-control mb-2" placeholder="Contraseña" required>
            <button type="submit" name="registrar" class="btn btn-primary mb-3" id="registrar-btn">
                <i class="fa fa-user-plus"></i> Registrar Asesor
            </button>
        </form>

        <div class="row justify-content-center mb-4">
            <div class="col-12 col-md-6">
                <input type="text" id="searchBox" class="form-control" placeholder="Buscar por nombre o matrícula">
            </div>
        </div>

        <!-- Listado de asesores -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h6 class="text-center" style="color: #8a2036; font-size: 1.25rem; margin-bottom: 1.5rem;">Asesores disponibles:</h6>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Matrícula</th>
                                    <th>Nombre</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="asesoresTableBody">
                                <?php foreach ($asesores as $asesor): ?>
                                    <tr data-id="<?php echo htmlspecialchars($asesor['IdAsesor']); ?>" class="<?php echo ($asesor['Estado'] == 0) ? 'inactivo' : ''; ?>">
                                        <td><?php echo htmlspecialchars($asesor['IdAsesor']); ?></td>
                                        <td><?php echo htmlspecialchars($asesor['Matricula']); ?></td>
                                        <td><?php echo htmlspecialchars($asesor['Nombre'] . ' ' . $asesor['ApellidoPaterno'] . ' ' . $asesor['ApellidoMaterno']); ?></td>
                                        <td>
                                            <button class="btn btn-outline-warning btn-sm cambiarEstado" data-id="<?php echo htmlspecialchars($asesor['IdAsesor']); ?>" data-estado="<?php echo htmlspecialchars($asesor['Estado'] == 1 ? 0 : 1); ?>">
                                                <i class="fas fa-toggle-<?php echo ($asesor['Estado'] == 1) ? 'on estado-activo' : 'off estado-inactivo'; ?>"></i> Cambiar Estado
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm eliminar" data-id="<?php echo htmlspecialchars($asesor['IdAsesor']); ?>">
                                                <i class="fas fa-trash-alt"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/jquery.js"></script>
    <script>
        $(document).ready(function() {
            // Filtrar asesores
            $('#searchBox').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#asesoresTableBody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            // Cambiar estado
            $('.cambiarEstado').on('click', function() {
                var id = $(this).data('id');
                var estado = $(this).data('estado');

                $.ajax({
                    url: '<?= $_SERVER['PHP_SELF'] ?>',
                    method: 'POST',
                    data: {
                        cambiar_estado: true,
                        id: id,
                        estado: estado
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.status === 'success') {
                            location.reload(); // Recargar la página para ver el cambio
                        } else {
                            alert('Error al cambiar el estado.');
                        }
                    }
                });
            });

            // Eliminar asesor
            $('.eliminar').on('click', function() {
                var id = $(this).data('id');
                if (confirm('¿Estás seguro de que quieres eliminar este asesor?')) {
                    $.ajax({
                        url: '<?= $_SERVER['PHP_SELF'] ?>',
                        method: 'POST',
                        data: {
                            eliminar: true,
                            id: id
                        },
                        success: function(response) {
                            var data = JSON.parse(response);
                            if (data.status === 'success') {
                                location.reload(); // Recargar la página para ver el cambio
                            } else {
                                alert('Error al eliminar el asesor.');
                            }
                        }
                    });
                }
            });
        });
    </script>
</body>

<?php
include_once __DIR__ . '/../../includes/footer.php';
?>

</html>