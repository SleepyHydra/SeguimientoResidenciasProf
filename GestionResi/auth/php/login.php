<?php

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/php/functions.php";

$db = conectar_db();
if (!$db) exit;

$jsonData = [];
$content_type = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : "";
if ($content_type === "application/json") {
    $rawData = file_get_contents("php://input");
    $jsonData = json_decode($rawData, true);
}

$datos_post = count($jsonData) > 0 ? sanitize_array($jsonData) : sanitize_array($_POST);
$datos_get = sanitize_array($_GET);

$opcion = $_SERVER["REQUEST_METHOD"] === "POST" && isset($datos_post["case"]) ? $datos_post["case"] : ($datos_get["case"] ?? "");

switch ($opcion) {
    case "login":
        login($datos_post);
        break;

    default:
        send_response(["ok" => false, "mensaje" => "Operación no soportada"], 400);
        break;
}

function login($post)
{
    global $db;

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        send_response(["ok" => false, "mensaje" => "Método no permitido"], 405);
        exit;
    }

    if (empty($post["data"]["user"]) || empty($post["data"]["password"])) {
        send_response(["ok" => false, "mensaje" => "Ingresa tu usuario y contraseña"], 400);
        exit;
    }

    $usuario = $post["data"]["user"];
    $password = $post["data"]["password"];

    try {
        // Buscar usuario en la tabla Usuarios
        $sql = "SELECT IdUsuario, Matricula, Password, IdTipo FROM Usuarios WHERE Matricula = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            send_response(["ok" => false, "mensaje" => "Este usuario no existe"], 401);
            exit;
        }

        $usuario_bd = $resultado->fetch_assoc();
        $stmt->close();

        // Verificar la contraseña
        if (!isset($usuario_bd["Password"]) || $usuario_bd["Password"] !== $password) {
            send_response(["ok" => false, "mensaje" => "Contraseña incorrecta"], 401);
            exit;
        }

        // Determinar tipo de usuario
        $tipo_usuario = "";
        $redirect_url = "";

        // Obtener la URL base
        $base_url = "http://" . ($_SERVER['SERVER_ADDR'] === "::1" ? "localhost" : $_SERVER['SERVER_ADDR']) . "/resi/GestionResi/";

        switch ($usuario_bd["IdTipo"]) {
            case 1:
                $tipo_usuario = "Profesor";
                $redirect_url = $base_url . "coordinador/index.php";
                break;

            case 2:
                $tipo_usuario = "Alumno";

                // Obtener `PrimerLogin` del Alumno
                $sql = "SELECT PrimerLogin FROM Alumnos WHERE Matricula = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("s", $usuario);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $stmt->close();

                if ($resultado->num_rows > 0) {
                    $alumno_data = $resultado->fetch_assoc();
                    $redirect_url = ($alumno_data["PrimerLogin"] === "N") ?
                        $base_url . "alumnos/actu_datos.php" :
                        $base_url . "alumnos/index.php";
                }
                break;

            case 3:
                $tipo_usuario = "Asesor";
                $redirect_url = $base_url . "asesores/index.php";
                break;

            default:
                send_response(["ok" => false, "mensaje" => "Tipo de usuario no reconocido"], 401);
                exit;
        }

        // Iniciar sesión de forma segura
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION["logged_in"] = true;
        $_SESSION["tipo_usuario"] = $tipo_usuario;
        $_SESSION["id_usuario"] = $usuario_bd["IdUsuario"];

        // Respuesta JSON con la información del usuario y redirección
        send_response([
            "ok" => true,
            "mensaje" => "Inicio de sesión exitoso",
            "data" => [
                "logged_in" => true,
                "tipo_usuario" => $tipo_usuario,
                "redirect" => $redirect_url
            ]
        ]);
    } catch (Exception $e) {
        send_response(["ok" => false, "mensaje" => "Error interno: " . $e->getMessage()], 500);
    }
}
