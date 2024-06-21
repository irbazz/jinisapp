<?php
$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "asistencia_evento";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Variables para almacenar nombre y código QR
$nombres = "";
$apellidos = "";
$qr_code = "";

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $dni = $_POST['dni'];

    // Verifica credenciales de admin
    if ($correo === "admin@correo.com" && $dni === "admin") {
        header("Location: colaboradores.php");
        exit;
    }

    // Consultar en la base de datos
    $sql = "SELECT * FROM usuarios WHERE correo='$correo' AND dni='$dni'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Usuario encontrado, obtener datos
        $row = $result->fetch_assoc();
        $nombres = $row['nombres'];
        $apellidos = $row['apellidos'];
        $qr_code = $row['qr_code'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso de Usuario</title>
    <link rel="stylesheet" href="styles.css">
    <style>
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
        <h1>Ingreso de Usuario</h1>
        <?php if ($nombres): ?>
            <p>Nombre: <?php echo $nombres . ' ' . $apellidos; ?></p>
            <?php if ($qr_code): ?>
                <p>Código QR: <br><img src='<?php echo $qr_code; ?>' alt='Código QR'></p>
            <?php endif; ?>
            <br><a href='index.html'>Volver</a>
        <?php else: ?>
            <p>Usuario no encontrado. <a href='index.html'>Volver</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
