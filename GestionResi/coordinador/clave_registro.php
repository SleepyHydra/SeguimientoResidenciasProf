<?php
require_once __DIR__ . "/../config/database.php";
session_start();

$base_url = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/resi/GestionResi/coordinador/nuevo_coordinador.php";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clave = trim($_POST['clave']);
    //AQUI SE PUEDE CAMBIAR LA CLAVE PARA EL REGISTRO
    if ($clave === '780967') {
        header("Location: " . $base_url);
        exit;
    } else {
        $error = 'Clave incorrecta. Solicítala a la coordinadora de la carrera de Sistemas.';
    }
}
?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registro de Coordinador | Gestion De Residencias Profesionales TESCHA</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
</head>

<body class="vh-100 d-flex flex-column">
    <header class="bg-white text-center py-3 border-bottom border-4 border-danger">
        <div class="logo d-inline-flex align-items-center gap-3">
            <img src="../assets/logo-header.png" alt="Logo" height="60">
            <h1 class="h5 fw-bold text-danger">Seguimiento de Residencias Profesionales</h1>
        </div>
    </header>

    <main class="container flex-grow-1 mt-5">
        <h2 class="text-center fw-bold text-danger mb-4">Registro de Coordinador</h2>

        <p class="text-center mb-4">Introduce la clave de registro del coordinador. Si no cuentas con ella, solicítala a la coordinadora de la carrera de Sistemas.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form class="mx-auto" style="max-width: 400px;" method="POST">
            <div class="mb-3">
                <label for="clave" class="form-label">Clave de Registro</label>
                <input type="password" class="form-control" id="clave" name="clave" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold mb-3">Registrar</button>
            <a href="<?php echo htmlspecialchars($base_url); ?>" class="btn btn-secondary w-100 fw-bold">Regresar</a>
        </form>
    </main>
</body>

<?php include_once __DIR__ . '../../includes/footer.php'; ?>

</html>