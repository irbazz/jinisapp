<?php
require 'phpqrcode/qrlib.php';

$dir = 'qrcodes/'; // Directorio donde se almacenarán los códigos QR

if (!file_exists($dir)) {
    mkdir($dir);
}

$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "asistencia_evento";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$message = ''; // Variable para almacenar los mensajes

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si las variables están definidas en $_POST antes de usarlas
    $nombres = isset($_POST['nombres']) ? $_POST['nombres'] : '';
    $apellidos = isset($_POST['apellidos']) ? $_POST['apellidos'] : '';
    $dni = isset($_POST['dni']) ? $_POST['dni'] : '';
    $correo = isset($_POST['correo']) ? $_POST['correo'] : '';

    // Generar nombre único para el archivo QR
    $archivo_qr = $dir . $correo . '.png';

    // Configuración del QR
    $tamaño = 10; // Tamaño de píxel
    $level = 'H'; // Nivel de corrección de errores: L, M, Q, H
    $frameSize = 3; // Tamaño del marco blanco alrededor del QR en celdas

    // Generar QR
    QRcode::png($correo, $archivo_qr, $level, $tamaño, $frameSize);

    // Insertar datos en la base de datos
    $sql = "INSERT INTO usuarios (nombres, apellidos, dni, correo, qr_code)
            VALUES ('$nombres', '$apellidos', '$dni', '$correo', '$archivo_qr')";

    if ($conn->query($sql) === TRUE) {
        $message = "Registro exitoso. <a href='index.html'>Volver</a>";
    } else {
        $message = "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-height: 80vh;
            /* Establece la altura máxima del contenedor */
            overflow-y: auto;
            /* Permite el desplazamiento vertical cuando sea necesario */
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }
        .button-container {
            margin-bottom: 10px;
        }
        .image-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .image-container img {
            margin: 0 20px;
            max-width: 170px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Contenedor de las imágenes -->
        <div class="image-container">
            <img src="imagen1.png" alt="Imagen 1">
            <img src="imagen2.png" alt="Imagen 2">
        </div>
        <h1>Registro de Usuario</h1>
        <form action="register.php" method="post" class="button-container">
            <label for="nombres">Nombres:</label>
            <input type="text" id="nombres" name="nombres" required>
            <label for="apellidos">Apellidos:</label>
            <input type="text" id="apellidos" name="apellidos" required>
            <label for="dni">DNI:</label>
            <input type="text" id="dni" name="dni" required>
            <label for="correo">Correo Electrónico:</label>
            <input type="email" id="correo" name="correo" required>
            <button type="submit">Registrarse</button>
        </form>

        <!-- Botón para redireccionar a index.html -->
        <form action="index.html" class="button-container">
            <button type="submit">Volver a Inicio</button>
        </form>

        <!-- Mostrar mensajes -->
        <?php if (!empty($message)): ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
