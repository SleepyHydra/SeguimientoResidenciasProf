<?php

require_once __DIR__ . "/../config/database.php";
require __DIR__ . "/../auth/php/functions.php";

// Definir base_url para las redirecciones usando la IP local o dominio
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/resi/GestionResi/';

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

$error = '';
$success = '';

// Verificar si el ID de usuario está configurado correctamente en la sesión
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
    exit('ID de usuario no encontrado en la sesión.');
}

// Obtener el ID del usuario actualmente autenticado
$idUsuario = $_SESSION['id_usuario'];

// Obtener el nombre del usuario logueado
$sql_usuario = "SELECT Nombre FROM Usuarios WHERE IdUsuario = ?";
$stmt_usuario = $db->prepare($sql_usuario);
$stmt_usuario->bind_param("i", $idUsuario);
$stmt_usuario->execute();
$resultado_usuario = $stmt_usuario->get_result();
$usuario = $resultado_usuario->fetch_assoc();
$stmt_usuario->close();

// Obtener el nombre si está disponible
$nombreUsuario = isset($usuario['Nombre']) ? $usuario['Nombre'] : 'Usuario no encontrado';

// Verificar si la matrícula está en la sesión, si no, obtenerla desde la base de datos
if (!isset($_SESSION['matricula']) || empty($_SESSION['matricula'])) {
    $sql_matricula = "SELECT Matricula FROM Usuarios WHERE IdUsuario = ? AND IdTipo = 2";
    $stmt_matricula = $db->prepare($sql_matricula);
    $stmt_matricula->bind_param("i", $idUsuario);
    $stmt_matricula->execute();
    $resultado_matricula = $stmt_matricula->get_result();
    $matricula = $resultado_matricula->fetch_assoc();
    $stmt_matricula->close();

    // Si la matrícula existe, asignarla a la sesión
    if ($matricula) {
        $_SESSION['matricula'] = $matricula['Matricula'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellidoPaterno = trim($_POST['apellido_paterno']);
    $apellidoMaterno = trim($_POST['apellido_materno']);
    $correoInstitucional = trim($_POST['correo_institucional']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Validaciones
    if (empty($nombre) || empty($apellidoPaterno) || empty($apellidoMaterno) || empty($correoInstitucional)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } else {
        // Actualizar los datos en la tabla Alumnos
        $query_alumnos = "UPDATE Alumnos 
                          SET Nombre = ?, ApellidoPaterno = ?, ApellidoMaterno = ?, CorreoInstitucional = ?, PrimerLogin = 'Y' 
                          WHERE Matricula = ?";
        $stmt_alumnos = $db->prepare($query_alumnos);

        if ($stmt_alumnos) {
            $stmt_alumnos->bind_param('sssss', $nombre, $apellidoPaterno, $apellidoMaterno, $correoInstitucional, $_SESSION['matricula']);

            if ($stmt_alumnos->execute()) {
                // Si la contraseña fue proporcionada, actualizamos la contraseña en la tabla Usuarios (sin encriptarla)
                if (!empty($password)) {
                    $query_usuarios = "UPDATE Usuarios 
                                       SET Password = ? 
                                       WHERE Matricula = ? AND IdTipo = 2";
                    $stmt_usuarios = $db->prepare($query_usuarios);
                    $stmt_usuarios->bind_param('ss', $password, $_SESSION['matricula']);
                    $stmt_usuarios->execute();
                    $stmt_usuarios->close();
                }

                $success = 'Los datos se han actualizado correctamente.';
                $stmt_alumnos->close();
                // Esperar 2 segundos antes de redirigir usando base_url
                echo "<script>
                        setTimeout(function() {
                            window.location.href = '" . $base_url . "alumnos/';
                        }, 2000);
                      </script>";
            } else {
                $error = 'Ocurrió un error al actualizar los datos. Intente nuevamente.';
                error_log("Error al actualizar los datos: " . $stmt_alumnos->error);
            }
        } else {
            $error = 'Error al preparar la consulta.';
            error_log("Error al preparar la consulta: " . $db->error);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Actualizar Datos | SEyBT TESCHA</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
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
        }

        .btn-primary {
            background-color: #8a2036;
            border-color: #8a2036;
        }

        .btn-primary:hover {
            background-color: #7a1c30;
            border-color: #7a1c30;
        }

        p a {
            color: #8a2036;
            text-decoration: none;
        }

        p a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body class="vh-100 d-flex flex-column">
    <header>
        <div class="logo">
            <img src="../assets/logo-header.png" alt="Logo">
            <h1>Seguimiento de Residencias Profesionales</h1>
        </div>
    </header>

    <main class="container flex-grow-1 mt-5">
        <h2 class="fw-bold mb-5 text-center">
            Actualizar Datos
        </h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form class="mx-auto" style="max-width: 400px;" action="actu_datos.php" method="POST">
            <div class="mb-3">
                <label for="matricula" class="form-label">
                    Matrícula
                    <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="matricula" name="matricula" value="<?php echo isset($_SESSION['matricula']) ? htmlspecialchars($_SESSION['matricula']) : ''; ?>" readonly>
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label">
                    Nombre
                    <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombreUsuario); ?>" required>
            </div>

            <div class="mb-3">
                <label for="apellido_paterno" class="form-label">
                    Apellido Paterno
                    <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" required>
            </div>

            <div class="mb-3">
                <label for="apellido_materno" class="form-label">
                    Apellido Materno
                    <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" required>
            </div>

            <div class="mb-3">
                <label for="correo_institucional" class="form-label">
                    Correo Institucional
                    <span class="text-danger">*</span>
                </label>
                <input type="email" class="form-control" id="correo_institucional" name="correo_institucional" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">
                    Contraseña
                    <span class="text-danger">*</span>
                </label>
                <input type="password" class="form-control" id="password" name="password" minlength="8" required>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">
                    Confirmar Contraseña
                    <span class="text-danger">*</span>
                </label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary fw-bold" onclick="window.location.href='<?php echo $base_url; ?>'">
                    Regresar
                </button>
                <button type="submit" class="btn btn-primary fw-bold">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </main>

    <script src="../assets/js/jquery.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

<?php
include_once __DIR__ . '/../includes/footer.php';
?>

</html>