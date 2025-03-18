<?php

use FFI\Exception;

require_once "../../config/database.php";
$db = conectar_db();

/**
 * Sanitiza los datos de un arreglo.
 *
 * @param  array $arr   Arreglo a sanitizar.
 * @return array        Arreglo con los datos sanitizados.
 */
function sanitize_array($arr) {
    global $db;
    $sanitized_array = [];

    foreach($arr as $key => $value) {
        if( is_array($value) ) {
            $sanitized_array[$key] = sanitize_array($value);
        } else {
            $sanitized_array[$key] = $db->escape_string($value);
        }
    }

    return $sanitized_array;
}

/**
 * Envía una respuesta en JSON con el código de status correspondiente.
 *
 * @param array $arr_response   Arreglo con la respuesta.
 * @param int   $status_code    (Opcional) Código de estado HTTP que se enviará
 *                              en la respuesta. Por defecto, es 200 (OK).
 */
function send_response($arr_response, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($arr_response);
}



?>