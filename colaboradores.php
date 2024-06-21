<?php
$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "asistencia_evento";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$message = ''; // Variable para almacenar los mensajes (para mostrarlos en el box al final de container)

// Verificar si se ha enviado el formulario de correo
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_codigo'])) {
    $correo = isset($_POST['correo']) ? $_POST['correo'] : '';
    $bloque = isset($_POST['bloque']) ? $_POST['bloque'] : '';

    if (!empty($correo) && !empty($bloque)) {
        $sql = "SELECT id FROM usuarios WHERE correo='$correo'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $usuario_id = $row['id'];

            $sql_insert = "INSERT INTO asistencias (usuario_id, bloque) VALUES ('$usuario_id', '$bloque')";

            if ($conn->query($sql_insert) === TRUE) {
                $message = "Asistencia registrada correctamente. <a href='colaboradores.php'>Volver</a>";
            } else {
                $message = "Error al registrar la asistencia: " . $conn->error;
            }
        } else {
            $message = "Usuario no encontrado. <a href='colaboradores.php'>Volver</a>";
        }
    } else {
        $message = "Por favor, complete todos los campos.";
    }
}

// Consulta para obtener los horarios
$sql_horarios = "SELECT * FROM horarios";
$result_horarios = $conn->query($sql_horarios);
$horarios = [];

if ($result_horarios->num_rows > 0) {
    while ($row = $result_horarios->fetch_assoc()) {
        $horarios[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencia</title>
    <link rel="stylesheet" href="styles.css">
    <style>
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
        <h1>Registro de Asistencia</h1>

        <!-- Formulario para registrar asistencia por correo -->
        <form action="colaboradores.php" method="post" class="button-container">
            <label for="correo">Correo Electrónico:</label>
            <input type="email" id="correo" name="correo" required>
            <label for="bloque">Bloque:</label>
            <select id="bloque" name="bloque" required>
                <option value="">Seleccionar Bloque</option>
                <?php foreach ($horarios as $horario): ?>
                    <option value="<?php echo $horario['id']; ?>">
                        <?php echo $horario['inicio'] . ' - ' . $horario['fin']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="submit_codigo">Registrar Asistencia por Correo</button>
        </form>

        <!-- Formulario para habilitar la cámara -->
        <form action="camara.php" method="get" class="button-container">
            <button type="submit">Habilitar Cámara para Escanear QR</button>
        </form>

        <!-- Botón para ir a bloques.php -->
        <form action="bloques.php" method="get" class="button-container">
            <button type="submit">Administrar Horarios</button>
        </form>

        <!-- Botón para ir a index.html -->
        <form action="index.html" method="get" class="button-container">
            <button type="submit">Ir a Inicio</button>
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
