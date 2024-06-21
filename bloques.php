<?php
$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "asistencia_evento";

// conexion con la db
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$message = ''; // variable que almacena el mensaje

// funcion que muestra los horarios de la db
function obtenerHorariosRegistrados($conn) {
    $sql = "SELECT * FROM horarios";
    $result = $conn->query($sql);
    $horarios = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $horarios[] = $row;
        }
    }

    return $horarios;
}

// para agregar nuevos horarios
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_agregar'])) {
    $inicio = $_POST['inicio'];
    $fin = $_POST['fin'];

    if (!empty($inicio) && !empty($fin)) {
        $sql_insert = "INSERT INTO horarios (inicio, fin) VALUES ('$inicio', '$fin')";

        if ($conn->query($sql_insert) === TRUE) {
            $message = "Horario agregado correctamente.";
        } else {
            $message = "Error al agregar horario: " . $conn->error;
        }
    } else {
        $message = "Por favor, complete todos los campos.";
    }
}

// para eliminar horarios
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['eliminar_id'])) {
    $horario_id = $_GET['eliminar_id'];

    if (!empty($horario_id)) {
        $sql_delete = "DELETE FROM horarios WHERE id='$horario_id'";

        if ($conn->query($sql_delete) === TRUE) {
            $message = "Horario eliminado correctamente.";
            // Redireccionar para evitar reenvío del formulario al actualizar la página (importante)
            header("Location: bloques.php");
            exit;
        } else {
            $message = "Error al eliminar horario: " . $conn->error;
        }
    } else {
        $message = "ID de horario no válido.";
    }
}

// Obtener horarios después de las operaciones POST
$horarios = obtenerHorariosRegistrados($conn);

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Bloques</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .eliminar-btn {
            background-color: red;
            color: white;
            padding: 6px 12px;
            border: none;
            cursor: pointer;
            width: 100px;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
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
    <script>
        function confirmarEliminar(id) {
            if (confirm("¿Estás seguro de eliminar este horario?")) {
                window.location.href = "bloques.php?eliminar_id=" + id; // redirige con el id a eliminar (admin)
            }
        }
    </script>
</head>
<body>
    <div class="container">
         <!-- contenedor de las imagenes -->
         <div class="image-container">
            <img src="imagen1.png" alt="Imagen 1">
            <img src="imagen2.png" alt="Imagen 2">
        </div>
        <h1>Administrar Bloques</h1>

        <!-- formulario para agregar nuevo horario -->
        <form action="bloques.php" method="post">
            <label for="inicio">Inicio:</label>
            <input type="time" id="inicio" name="inicio" required>
            <label for="fin">Fin:</label>
            <input type="time" id="fin" name="fin" required>
            <button type="submit" name="submit_agregar">Agregar Horario</button>
        </form>

        <br>

        <!-- lista de horarios registrados -->
        <?php
        if (!empty($horarios)) {
            echo "<h2>Horarios Registrados:</h2>";
            echo "<ul>";
            foreach ($horarios as $horario) {
                echo "<li>{$horario['inicio']} - {$horario['fin']} <button class='eliminar-btn' onclick='confirmarEliminar({$horario['id']})'>Eliminar</button></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No hay horarios registrados.</p>";
        }
        ?>

        <!-- redirecciona a colaboradores.php -->
        <form action="colaboradores.php" method="get">
            <button type="submit">Registro de Asistencia</button>
        </form>

        <!-- muestra los mensajes en un box-->
        <?php if (!empty($message)): ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
