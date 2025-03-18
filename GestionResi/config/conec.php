<?php
$servername = "localhost"; // Nombre del servidor de la base de datos
$username = "root"; // Tu usuario de la base de datos
$password = "root"; // Tu contraseña de la base de datos
$dbname = "seguimientoresidencias"; // El nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
} else {
    echo "Conexión exitosa con la base de datos!<br>";

    // Consultar las tablas
    $sql = "SHOW TABLES";
    $result = $conn->query($sql);

    // Verificar si hay tablas y mostrarlas
    if ($result->num_rows > 0) {
        echo "Tablas en la base de datos:<br>";
        while($row = $result->fetch_assoc()) {
            echo $row['Tables_in_' . $dbname] . "<br>";
        }
    } else {
        echo "No se encontraron tablas en la base de datos.";
    }
}

// Cerrar la conexión
$conn->close();
?>
