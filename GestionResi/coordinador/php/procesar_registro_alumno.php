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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir datos del formulario
    $matricula = isset($_POST['matricula']) ? $_POST['matricula'] : '';
    $nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';
    $apellidoPaterno = isset($_POST['apellidoPaterno']) ? $_POST['apellidoPaterno'] : '';
    $apellidoMaterno = isset($_POST['apellidoMaterno']) ? $_POST['apellidoMaterno'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validar que los campos requeridos no estén vacíos
    if (empty($matricula) || empty($nombre) || empty($password) || empty($apellidoPaterno)) {
        echo json_encode(["error" => "Por favor, completa todos los campos obligatorios."]);
        exit;
    }

    // Insertar en la tabla Usuarios (esto disparará el trigger para crear el registro en Alumnos)
    $sql_insert = "INSERT INTO Usuarios (Matricula, Nombre, Password, IdTipo) VALUES (?, ?, ?, 2)";
    $stmt_insert = $db->prepare($sql_insert);
    if (!$stmt_insert) {
        echo json_encode(["error" => "Error en la preparación de la consulta de inserción en Usuarios."]);
        exit;
    }
    $stmt_insert->bind_param("sss", $matricula, $nombre, $password);
    if (!$stmt_insert->execute()) {
        echo json_encode(["error" => "Error al insertar en Usuarios: " . $stmt_insert->error]);
        exit;
    }
    $stmt_insert->close();

    // Obtener el IdProfesor del profesor logueado
    $idUsuario = $_SESSION['id_usuario'];
    $sql_profesor = "SELECT P.IdProfesor FROM Profesores P 
                     INNER JOIN Usuarios U ON P.Matricula = U.Matricula 
                     WHERE U.IdUsuario = ? AND U.IdTipo = 1";
    $stmt_prof = $db->prepare($sql_profesor);
    if (!$stmt_prof) {
        echo json_encode(["error" => "Error en la preparación de la consulta para obtener el profesor."]);
        exit;
    }
    $stmt_prof->bind_param("i", $idUsuario);
    $stmt_prof->execute();
    $result_prof = $stmt_prof->get_result();
    $profesor = $result_prof->fetch_assoc();
    $stmt_prof->close();

    if (!$profesor) {
        echo json_encode(["error" => "No se encontró información del profesor."]);
        exit;
    }

    $idProfesor = $profesor['IdProfesor'];

    // Actualizar la tabla Alumnos con ApellidoPaterno, ApellidoMaterno e IdProfesor
    $sql_update = "UPDATE Alumnos SET ApellidoPaterno = ?, ApellidoMaterno = ?, IdProfesor = ? WHERE Matricula = ?";
    $stmt_update = $db->prepare($sql_update);
    if (!$stmt_update) {
        echo json_encode(["error" => "Error en la preparación de la consulta de actualización en Alumnos."]);
        exit;
    }
    $stmt_update->bind_param("ssis", $apellidoPaterno, $apellidoMaterno, $idProfesor, $matricula);
    if (!$stmt_update->execute()) {
        echo json_encode(["error" => "Error al actualizar la tabla Alumnos: " . $stmt_update->error]);
        exit;
    }
    $stmt_update->close();

    echo json_encode(["success" => "Alumno registrado correctamente"]);
}
?>
