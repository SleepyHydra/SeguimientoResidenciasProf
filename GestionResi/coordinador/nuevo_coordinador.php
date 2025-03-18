<?php
require_once __DIR__ . "/../config/database.php";
session_start(); // Iniciar la sesión

// Definir base_url de forma más robusta
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST']; // Esto incluye localhost o la IP/dominio del servidor
$base_url = $protocol . $host . "/resi/GestionResi/";

$db = conectar_db();
if (!$db) {
    exit('Error de conexión a la base de datos');
}

$error = '';
$success = '';

// Obtener la lista de carreras
$query_carreras = "SELECT IdCarrera, Nombre FROM Carreras";
$result_carreras = $db->query($query_carreras);
$carreras = $result_carreras->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = trim($_POST['matricula']);
    $nombre = trim($_POST['nombre']);
    $apellidoPaterno = trim($_POST['apellido_paterno']);
    $apellidoMaterno = trim($_POST['apellido_materno']);
    $idCarrera = $_POST['carrera'];
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Validaciones
    if (empty($matricula) || empty($nombre) || empty($apellidoPaterno) || empty($apellidoMaterno) || empty($idCarrera) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($password) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } else {
        // Insertar en la tabla Profesores
        $query_profesor = "INSERT INTO Profesores (IdTipoUsuario, Matricula, Nombre, ApellidoPaterno, ApellidoMaterno, IdCarrera) 
                           VALUES (1, ?, ?, ?, ?, ?)";
        $stmt_profesor = $db->prepare($query_profesor);

        if ($stmt_profesor) {
            $stmt_profesor->bind_param('ssssi', $matricula, $nombre, $apellidoPaterno, $apellidoMaterno, $idCarrera);

            if ($stmt_profesor->execute()) {
                // Insertar la contraseña en Usuarios
                $query_password = "UPDATE Usuarios SET Password = ?, IdTipo = 1 WHERE Matricula = ?";
                $stmt_password = $db->prepare($query_password);
                $stmt_password->bind_param('ss', $password, $matricula);
                $stmt_password->execute();
                $stmt_password->close();

                // Crear la sesión para el profesor
                $_SESSION['matricula'] = $matricula;
                $_SESSION['tipo_usuario'] = 1; // Tipo de usuario es Profesor

                // Mostrar mensaje de éxito y redirigir después de 3 segundos
                $success = 'Los datos se han registrado correctamente.';
            } else {
                $error = 'Ocurrió un error al registrar al profesor.';
            }
            $stmt_profesor->close();
        } else {
            $error = 'Error al preparar la consulta.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro de Profesor | SEyBT TESCHA</title>
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

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }

        p a {
            color: #8a2036;
            text-decoration: none;
        }

        p a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        // Función para redirigir después de 3 segundos
        function redirectAfterDelay() {
            setTimeout(function() {
                window.location.href = "<?php echo htmlspecialchars($base_url . 'coordinador/'); ?>";
            }, 2000); // Delay
        }
    </script>
</head>

<body class="vh-100 d-flex flex-column">
    <header>
        <div class="logo">
            <img src="../assets/logo-header.png" alt="Logo">
            <h1>Seguimiento de Residencias Profesionales</h1>
        </div>
    </header>

    <main class="container flex-grow-1 mt-5">
        <h2 class="fw-bold mb-5 text-center">Registro de Profesor</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <script>
                // Llamamos a la función para redirigir después de mostrar el mensaje de éxito
                redirectAfterDelay();
            </script>
        <?php endif; ?>

        <form class="mx-auto" style="max-width: 400px;" action="nuevo_coordinador.php" method="POST">
            <div class="mb-3">
                <label for="matricula" class="form-label">Matrícula <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="matricula" name="matricula" required>
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>

            <div class="mb-3">
                <label for="apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" required>
            </div>

            <div class="mb-3">
                <label for="apellido_materno" class="form-label">Apellido Materno <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" required>
            </div>

            <div class="mb-3">
                <label for="carrera" class="form-label">Carrera <span class="text-danger">*</span></label>
                <select class="form-control" id="carrera" name="carrera" required>
                    <option value="">Seleccione una carrera</option>
                    <?php foreach ($carreras as $carrera): ?>
                        <option value="<?php echo $carrera['IdCarrera']; ?>">
                            <?php echo htmlspecialchars($carrera['Nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password" name="password" required minlength="8">
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
            </div>

            <button type="submit" class="btn btn-primary fw-bold w-100 mb-3">Registrar</button>
            <a href="<?php echo htmlspecialchars($base_url); ?>" class="btn btn-secondary fw-bold w-100">Regresar</a>
        </form>
    </main>
</body>

<?php
include_once __DIR__ . '../../includes/footer.php';
?>

</html>