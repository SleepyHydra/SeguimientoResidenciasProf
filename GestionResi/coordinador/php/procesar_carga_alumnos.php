<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../auth/php/functions.php";
require_once __DIR__ . '/../../vendor/autoload.php'; // Cargar PhpSpreadsheet

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

is_logged_in();

$db = conectar_db();
if (!$db) {
    die("Error de conexión a la base de datos.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['archivo'])) {
    // Procesar archivo CSV o Excel
    $file = $_FILES['archivo']['tmp_name'];
    $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);

    $data = [];

    // Leer archivo según extensión
    if ($ext == 'csv') {
        // Leer archivo CSV
        $handle = fopen($file, "r");
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $data[] = $row;
        }
        fclose($handle);
    } elseif ($ext == 'xlsx' || $ext == 'xls') {
        // Leer archivo Excel
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
    }

    // Procesar los datos
    $result = [];

    foreach ($data as $index => $row) {
        if ($index === 0) {
            continue; // Omitir la primera fila (encabezados)
        }

        // Asumiendo que la estructura del archivo tiene la siguiente forma:
        // Matricula | Nombre | ApellidoPaterno | ApellidoMaterno | Password
        $matricula = $row[0]; // Asumiendo que la matrícula está en la primera columna
        $nombre = $row[1];     // Asumiendo que el nombre está en la segunda columna
        $apellidoPaterno = $row[2]; // Asumiendo que el apellido paterno está en la tercera columna
        $apellidoMaterno = $row[3]; // Asumiendo que el apellido materno está en la cuarta columna
        $password = $row[4];   // Asumiendo que la contraseña está en la quinta columna

        // Verificar si la matrícula ya existe en la tabla Usuarios
        $sql_check = "SELECT * FROM Usuarios WHERE Matricula = ?";
        $stmt_check = $db->prepare($sql_check);
        $stmt_check->bind_param("s", $matricula);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Si la matrícula ya existe, no se realiza la inserción en Usuarios
            continue; // Puedes agregar un mensaje de advertencia aquí si lo deseas
        }

        // Insertar en la tabla Usuarios (sin los apellidos aún, sin PrimerLogin)
        $sql_insert = "INSERT INTO Usuarios (Matricula, Nombre, Password, IdTipo) 
                       VALUES (?, ?, ?, 2)";
        $stmt_insert = $db->prepare($sql_insert);
        $stmt_insert->bind_param("sss", $matricula, $nombre, $password);
        $stmt_insert->execute();
        $stmt_insert->close();

        // Obtener el IdProfesor desde la matrícula del profesor logueado
        $idUsuario = $_SESSION['id_usuario']; // Obtenemos el id_usuario desde la sesión

        // Obtener información del profesor logueado
        $sql_profesor = "SELECT P.IdProfesor, P.Matricula FROM Profesores P 
                         INNER JOIN Usuarios U ON P.Matricula = U.Matricula 
                         WHERE U.IdUsuario = ? AND U.IdTipo = 1";
        $stmt_profesor = $db->prepare($sql_profesor);
        $stmt_profesor->bind_param("i", $idUsuario);
        $stmt_profesor->execute();
        $result_profesor = $stmt_profesor->get_result();
        $profesor = $result_profesor->fetch_assoc();

        // Verificar si el profesor se encontró
        if (!$profesor) {
            die("No se encontró el profesor con id_usuario: " . $idUsuario);
        }

        // Obtener la matrícula del profesor
        $matriculaProfesor = $profesor['Matricula'];
        $idProfesor = $profesor['IdProfesor'];
        $stmt_profesor->close();

        // Verificar los valores antes de la actualización
        echo "Matrícula alumno: " . $matricula . "<br>";
        echo "IdProfesor: " . $idProfesor . "<br>";
        echo "ApellidoPaterno: " . $apellidoPaterno . "<br>";
        echo "ApellidoMaterno: " . $apellidoMaterno . "<br>";

        // Insertar en la tabla Alumnos usando la información de la tabla Usuarios
        $sql_alumnos = "UPDATE Alumnos 
                        SET ApellidoPaterno = ?, ApellidoMaterno = ?, IdProfesor = ? 
                        WHERE Matricula = ?";
        $stmt_alumnos = $db->prepare($sql_alumnos);
        $stmt_alumnos->bind_param("ssis", $apellidoPaterno, $apellidoMaterno, $idProfesor, $matricula);
        if ($stmt_alumnos->execute()) {
            echo "Actualización exitosa<br>";
        } else {
            echo "Error en actualización: " . $stmt_alumnos->error . "<br>";
        }
        $stmt_alumnos->close();
    }

    $result['success'] = "Alumnos cargados correctamente.";
    echo json_encode($result);
} else {
    $result['error'] = "No se ha cargado ningún archivo.";
    echo json_encode($result);
}
?>
