<?php
// developers.php

// Título de la página
$title = "Equipo de Desarrollo - Seguimiento de Residencias Profesionales";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }

        .container {
            max-width: 1000px;
        }

        .title {
            text-align: center;
            color: #8a2036;
            margin-bottom: 30px;
            font-size: 2em;
        }

        header .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        header .logo img {
            max-width: 150px;
        }

        .developer-card {
            background-color: #8a2036;
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        .developer-card h3 {
            color: white;
            font-size: 1.3em;
            margin-bottom: 10px;
        }

        .developer-card p {
            color: #f4f4f9;
            font-size: 1em;
        }

        .developer-card a {
            color: #f4f4f9;
            text-decoration: none;
            font-weight: bold;
            border-top: 2px solid #f4f4f9;
            padding-top: 10px;
            display: block;
            margin-top: 15px;
            transition: color 0.3s ease;
        }

        .developer-card a:hover {
            color: #7a1c30;
        }

        .back-btn {
            text-align: center;
            margin-top: 30px;
        }

        .back-btn a {
            background-color: #8a2036;
            color: white;
            padding: 10px 20px;
            font-size: 1em;
            border-radius: 5px;
            text-decoration: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease;
        }

        .back-btn a:hover {
            background-color: #7a1c30;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <!-- Header -->
        <header>
            <div class="logo">
                <img src="../assets/logo-header.png" alt="Logo">
                <h1>Seguimiento de Residencias Profesionales</h1>
            </div>
        </header>

        <!-- Page Title -->
        <h1 class="title"><?php echo $title; ?></h1>

        <!-- Developer Cards (Bootstrap Row and Col) -->
        <div class="row">
            <!-- Developer 1 -->
            <div class="col-md-6">
                <div class="developer-card">
                    <h3>Pablo Xiuhnel Pérez Amaya Arellano</h3>
                    <p><strong>Cargo:</strong> Desarrollador Frontend</p>
                    <p><strong>Descripción:</strong> Encargado del diseño y desarrollo de la interfaz de usuario para la plataforma de seguimiento de residencias profesionales. Responsable de garantizar una experiencia de usuario intuitiva y atractiva.</p>
                    <a href="mailto:pablo.xiuhnel@tesch.edu.mx">Contacto por correo</a>
                </div>
            </div>

            <!-- Developer 2 -->
            <div class="col-md-6">
                <div class="developer-card">
                    <h3>Monserrat Gallardo Bautista</h3>
                    <p><strong>Cargo:</strong> Desarrolladora Backend</p>
                    <p><strong>Descripción:</strong> Encargada del desarrollo y mantenimiento de la base de datos, así como la implementación de la lógica del servidor para asegurar la correcta gestión de las residencias profesionales en la plataforma.</p>
                    <a href="mailto:monserrat.gallardo@tesch.edu.mx">Contacto por correo</a>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="back-btn">
            <a href="../index.php">Volver al inicio</a>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

    <!-- Footer -->
    <?php include_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
