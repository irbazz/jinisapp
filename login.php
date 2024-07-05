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
$apto_certificado = false;

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $dni = $_POST['dni'];

    // Verifica credenciales de admin
    if ($correo === "admin@correo.com" && $dni === "admin") {
        header("Location: bloques.php");
        exit;
    } elseif ($correo === "colaborador@correo.com" && $dni === "colaborador") {
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
        $apto_certificado = $row['apto_certificado'];
    }
}

// Consulta para obtener el cronograma de ponentes
$sql = "SELECT p.nombre, p.tema, h.dia, h.bloque, h.inicio, h.fin
        FROM ponentes p
        JOIN horarios h ON p.bloque_id = h.id
        ORDER BY h.dia, h.inicio";
$result = $conn->query($sql);
$cronograma = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cronograma[] = $row;
    }
}

// Consulta para obtener la cantidad de asistencias por bloque del usuario
$asistencias = [];
if ($nombres) {
    $sql = "SELECT bloque, COUNT(*) as cantidad
            FROM asistencias
            WHERE usuario_id = (SELECT id FROM usuarios WHERE correo='$correo' AND dni='$dni')
            GROUP BY bloque";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $asistencias[$row['bloque']] = $row['cantidad'];
        }
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

        .cronograma-container,
        .asistencias-container,
        .certificado-container {
            margin-top: 20px;
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

            <!-- Contenedor del cronograma -->
            <div class="cronograma-container">
                <h2>Cronograma de Ponentes</h2>
                <?php if (!empty($cronograma)): ?>
                    <ul>
                        <?php foreach ($cronograma as $evento): ?>
                            <li>
                                <strong><?php echo $evento['nombre']; ?></strong> - <?php echo $evento['tema']; ?> <br>
                                <em><?php echo $evento['dia']; ?> - <?php echo $evento['bloque']; ?>
                                    (<?php echo $evento['inicio']; ?> - <?php echo $evento['fin']; ?>)</em>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No hay ponentes programados.</p>
                <?php endif; ?>
            </div>

            <!-- Contenedor de asistencias -->
            <div class="asistencias-container">
                <h2>Asistencias por Bloque</h2>
                <?php if (!empty($asistencias)): ?>
                    <ul>
                        <?php foreach ($asistencias as $bloque => $cantidad): ?>
                            <li><?php echo $bloque; ?>: <?php echo $cantidad; ?> asistencias</li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No hay asistencias registradas.</p>
                <?php endif; ?>
            </div>

            <!-- Contenedor de certificado -->
            <div class="certificado-container">
                <h2>Certificado</h2>
                <p><?php echo $apto_certificado ? 'Apto para recibir certificado.' : 'No apto para recibir certificado.'; ?>
                </p>
            </div>

            <br><a href='index.html'>Volver</a>
        <?php else: ?>
            <p>Usuario no encontrado. <a href='index.html'>Volver</a></p>
        <?php endif; ?>
    </div>
</body>

</html>