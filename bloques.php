<?php
$servername = "localhost";
$username = "root";
$password = "admin";
$dbname = "asistencia_evento";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$message = ''; // Variable para almacenar los mensajes

// Verificar si se ha enviado el formulario de agregar horario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_agregar_horario'])) {
    $dia = $_POST['dia'];
    $bloque = $_POST['bloque'];
    $inicio = $_POST['inicio'];
    $fin = $_POST['fin'];

    $sql = "INSERT INTO horarios (dia, bloque, inicio, fin) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $dia, $bloque, $inicio, $fin);

    if ($stmt->execute()) {
        $message = "Nuevo horario agregado correctamente. <a href='bloques.php'>Volver</a>";
    } else {
        $message = "Error al agregar el horario: " . $conn->error;
    }
}

// Verificar si se ha enviado el formulario de agregar ponente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_agregar_ponente'])) {
    $nombre = $_POST['nombre'];
    $tema = $_POST['tema'];
    $dia = $_POST['dia_ponente'];
    $bloque_id = $_POST['bloque_id'];

    $sql = "INSERT INTO ponentes (nombre, tema, dia, bloque_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $nombre, $tema, $dia, $bloque_id);

    if ($stmt->execute()) {
        $message = "Ponente agregado correctamente. <a href='bloques.php'>Volver</a>";
    } else {
        $message = "Error al agregar el ponente: " . $conn->error;
    }
}

// Verificar si se ha enviado el formulario de eliminar horario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_eliminar_horario'])) {
    $id_horario = $_POST['id_horario'];

    $sql = "DELETE FROM horarios WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_horario);

    if ($stmt->execute()) {
        $message = "Horario eliminado correctamente. <a href='bloques.php'>Volver</a>";
    } else {
        $message = "Error al eliminar el horario: " . $conn->error;
    }
}

// Verificar si se ha enviado el formulario de eliminar ponente
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_eliminar_ponente'])) {
    $id_ponente = $_POST['id_ponente'];

    $sql = "DELETE FROM ponentes WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_ponente);

    if ($stmt->execute()) {
        $message = "Ponente eliminado correctamente. <a href='bloques.php'>Volver</a>";
    } else {
        $message = "Error al eliminar el ponente: " . $conn->error;
    }
}

// Verificar si se ha enviado el formulario para establecer el mínimo de asistencias
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_min_asistencias'])) {
    $min_asistencias = intval($_POST['min_asistencias']);

    // Actualizar la aptitud de los usuarios en función del mínimo de asistencias
    $sql = "UPDATE usuarios u
            SET apto_certificado = (
                SELECT IF(COUNT(a.id) >= ?, 1, 0)
                FROM asistencias a
                WHERE a.usuario_id = u.id
            )";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $min_asistencias);

    if ($stmt->execute()) {
        $message = "Aptitud para certificado actualizada correctamente para todos los usuarios. <a href='bloques.php'>Volver</a>";
    } else {
        $message = "Error al actualizar la aptitud para certificado: " . $conn->error;
    }
}

// Consultas para obtener los horarios, ponentes y usuarios
$horarios = [];
$ponentes = [];
$usuarios = [];

$result = $conn->query("SELECT * FROM horarios");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $horarios[] = $row;
    }
}

$result = $conn->query("SELECT p.*, h.dia, h.bloque FROM ponentes p JOIN horarios h ON p.bloque_id = h.id");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $ponentes[] = $row;
    }
}

$result = $conn->query("SELECT * FROM usuarios");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Bloques y Ponentes</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

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

        form {
            margin-bottom: 30px;
        }

        form label {
            display: block;
            margin: 10px 0 5px;
        }

        form input,
        form select,
        form button {
            width: 100%;
            padding: 10px;
            margin: 5px 0 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form button {
            background: #5cb85c;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        form button:hover {
            background: #4cae4c;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        ul li {
            padding: 10px;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            background: #f9f9f9;
        }

        ul li form {
            display: inline;
        }

        ul li button {
            background: #d9534f;
            border: none;
            color: white;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        ul li button:hover {
            background: #c9302c;
        }

        h1,
        h2 {
            text-align: center;
            color: #333;
        }

        h1 {
            margin-bottom: 20px;
        }

        h2 {
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .container {
                max-width: 95%;
            }
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
        <h1>Administrar Bloques y Ponentes</h1>

        <!-- Formulario para agregar horario -->
        <h2>Agregar Horario</h2>
        <form action="bloques.php" method="post">
            <label for="dia">Día:</label>
            <input type="text" id="dia" name="dia" required>
            <label for="bloque">Bloque:</label>
            <input type="text" id="bloque" name="bloque" required>
            <label for="inicio">Inicio:</label>
            <input type="time" id="inicio" name="inicio" required>
            <label for="fin">Fin:</label>
            <input type="time" id="fin" name="fin" required>
            <button type="submit" name="submit_agregar_horario">Agregar Horario</button>
        </form>

        <!-- Formulario para agregar ponente -->
        <h2>Agregar Ponente</h2>
        <form action="bloques.php" method="post">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
            <label for="tema">Tema:</label>
            <input type="text" id="tema" name="tema" required>
            <label for="dia_ponente">Día:</label>
            <select id="dia_ponente" name="dia_ponente" required>
                <option value="Lunes">Lunes</option>
                <option value="Martes">Martes</option>
                <option value="Miércoles">Miércoles</option>
                <option value="Jueves">Jueves</option>
                <option value="Viernes">Viernes</option>
            </select>
            <label for="bloque_id">Bloque:</label>
            <select id="bloque_id" name="bloque_id" required>
                <?php foreach ($horarios as $horario): ?>
                    <option value="<?php echo $horario['id']; ?>">
                        <?php echo $horario['dia'] . ', Bloque ' . $horario['bloque']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="submit_agregar_ponente">Agregar Ponente</button>
        </form>

        <!-- Formulario para establecer el mínimo de asistencias -->
        <h2>Establecer Mínimo de Asistencias para Certificado</h2>
        <form action="bloques.php" method="post">
            <label for="min_asistencias">Mínimo de Asistencias:</label>
            <input type="number" id="min_asistencias" name="min_asistencias" required>
            <button type="submit" name="submit_min_asistencias">Establecer Mínimo</button>
        </form>

        <!-- Botón para ir a index.html -->
        <form action="index.html" method="get" class="button-container">
            <button type="submit">Volver al Inicio</button>
        </form>
        
        <!-- Mensaje de confirmación o error -->
        <?php if (!empty($message)): ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Lista de horarios -->
        <h2>Horarios</h2>
        <ul>
            <?php foreach ($horarios as $horario): ?>
                <li>
                    <?php echo $horario['dia'] . ', Bloque ' . $horario['bloque']; ?>
                    <form action="bloques.php" method="post" style="display: inline;">
                        <input type="hidden" name="id_horario" value="<?php echo $horario['id']; ?>">
                        <button type="submit" name="submit_eliminar_horario">Eliminar</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Lista de ponentes -->
        <h2>Ponentes</h2>
        <ul>
            <?php foreach ($ponentes as $ponente): ?>
                <li>
                    <?php echo $ponente['nombre'] . ' - ' . $ponente['tema'] . ' - ' . $ponente['dia'] . ', Bloque ' . $ponente['bloque']; ?>
                    <form action="bloques.php" method="post" style="display: inline;">
                        <input type="hidden" name="id_ponente" value="<?php echo $ponente['id']; ?>">
                        <button type="submit" name="submit_eliminar_ponente">Eliminar</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>

</html>