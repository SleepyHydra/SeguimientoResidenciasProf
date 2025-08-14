<?php
// Iniciar la sesión si no está iniciada
session_start();

// Destruir la sesión si está activa
if (isset($_SESSION)) {
    session_unset();  // Eliminar todas las variables de sesión
    session_destroy(); // Destruir la sesión
}
?>

<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iniciar sesión | Seguimiento de Residencias Profesionales</title>
    <link rel="stylesheet" href="./assets/css/bootstrap.min.css" />
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
            <img src="./assets/logo-header.png" alt="Logo">
            <h1>Seguimiento de Residencias Profesionales</h1>
        </div>
    </header>

    <main class="container flex-grow-1 mt-5">
        <h2 class="fw-bold mb-5 text-center">
            Inicia sesión para comenzar
        </h2>
        <form class="mx-auto" id="formLogin" style="max-width: 400px;">
            <div class="mb-3">
                <label for="user" class="form-label">
                    Matrícula
                    <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="user" name="user" required />
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">
                    Contraseña
                    <span class="text-danger">*</span>
                </label>
                <input type="password" class="form-control" id="password" name="password" required />
            </div>
            <button type="submit" class="btn btn-primary fw-bold w-100">
                Iniciar sesión
            </button>
            <p class="mt-4">¿Eres coordinador? <a href="./coordinador/clave_registro.php">Regístrate</a></p>
        </form>
    </main>

    <script src="./assets/js/jquery.js"></script>
    <script src="./assets/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="./auth/js/login.js"></script>
</body>

<?php
include_once __DIR__ . '/includes/footer.php';
?>

</html>