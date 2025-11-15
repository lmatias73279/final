<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);

$user_id = $_SESSION['id'];

// Consulta si existe un consentimiento con estado 0 y tipo_consentimiento 1
$sql = "SELECT ID FROM consentimientos WHERE id_user = ? AND tipo_consentimiento = 2 AND estado = 0 LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Si no hay registros que cumplan la condición, redirecciona a index.php
    header("Location: index.php");
    exit();
}

// Obtener el ID del consentimiento
$row = $result->fetch_assoc();
$consentimiento_id = $row['ID']; // Guardamos el ID en una variable

include "../../conexionsm.php";

// Definir zona horaria para Colombia
date_default_timezone_set('America/Bogota');

// Comprobar si el formulario ha sido enviado
if (isset($_POST['submit'])) {
    $fecha_actual = date('Y-m-d H:i:s');
    $useridpolities = $_SESSION['id'];

    // Obtener el contenido de la firma (base64)
    $firma_data = isset($_POST['firma_data']) ? $_POST['firma_data'] : '';

    if (!empty($firma_data)) {
        // Generar un nombre único para la imagen
        $nombre_imagen = uniqid('firma_', true) . '.png';

        // Guardar la imagen de la firma
        $firma_path = 'firmas/' . $nombre_imagen;

        // Convertir la firma de base64 a imagen
        $image_data = base64_decode($firma_data);
        file_put_contents($firma_path, $image_data);

        // Actualizar la información en la base de datos
        $sql = "UPDATE consentimientos SET firma = ?, fecha_sign = ?, estado = 1 WHERE ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nombre_imagen, $fecha_actual, $consentimiento_id);

        // Función de encriptado XOR con clave
        function simpleEncrypt($text, $key) {
            $output = '';
            for ($i = 0; $i < strlen($text); $i++) {
                $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
            }
            return base64_encode($output); // Convertir a base64 para URL seguro
        }

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Encriptar el mensaje 'ok' con XOR y redirigir
            $encryptedStatus = simpleEncrypt('ok_psiquiatria', '2020');
            header('Location: index?f=' . urlencode($encryptedStatus));
            exit();
        } else {
            echo "Error al actualizar el consentimiento: " . $conn->error;
        }
    } else {
        echo "Por favor, firme antes de enviar.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM &rsaquo; Políticas</title>
    <link rel="icon" href="../../assets/images/icolet.png" type="image/x-icon">

    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
        }
        .signature-pad {
            border: 2px solid #ccc;
            width: 100%;
            height: 200px;
            position: relative;
        }
        .signature-pad canvas {
            width: 100%;
            height: 100%;
        }
        .signature-buttons {
            text-align: center;
            margin-top: 10px;
        }
        .signature-buttons button {
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            background: linear-gradient(45deg, #800080, #66c2ff); /* Morado a azul aguamarina */
        }
        .signature-buttons button:hover {
            background: linear-gradient(45deg, #6a0dad, #00bfff); /* Efecto hover */
        }
        .terms {
            margin-top: 20px;
            font-size: 16px;
        }
        .terms a {
            color: #007bff;
            text-decoration: none;
        }

        /* Estilos para el modal */
        .modal {
            display: none; /* Inicialmente no visible */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4); /* Fondo semitransparente */
            display: flex;  /* Usa flexbox para centrar el contenido */
            justify-content: center;
            align-items: center;
        }

        /* Estilo del contenido del modal */
        .modal-content {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-height: 80%; /* Limita la altura del modal */
            overflow-y: auto; /* Permite desplazamiento vertical */
            border-radius: 8px;
        }

        /* Estilo del botón de cierre */
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }



        /* Hacer la página responsive */
        @media (max-width: 600px) {
            .signature-pad {
                height: 150px;
            }
        }
        
    </style>
</head>
<body>

<div class="container">
    <h1>Aceptación de Políticas</h1>

    <!-- Formulario -->
    <form id="signature-form" method="POST" action="" enctype="multipart/form-data">
        <!-- Área para firmar -->
        <div class="signature-pad" id="signature-pad">
            <canvas id="signature-canvas"></canvas>
        </div>

        <!-- Campo oculto para almacenar la firma en base64 -->
        <input type="hidden" name="firma_data" id="firma_data">

        <!-- Botones de firma -->
        <div class="signature-buttons">
            <button type="button" id="clear-btn">Limpiar Firma</button>
        </div>

        <!-- Texto de condiciones -->
        <div class="terms">
            <p>He leído y acepto las <a href="#" id="terms-link">Condiciones de Prestación del Servicio Terapéutico</a>.</p>
        </div>

        <!-- Botón para enviar -->
        <div class="signature-buttons">
            <button type="submit" name="submit" id="submit-btn">Aceptar</button>
        </div>
    </form>
</div>

<!-- Modal de condiciones -->
<div id="terms-modal" class="modal">
    <div class="modal-content">
        <span class="close" id="close-modal">&times;</span>
        <h2>Condiciones de Prestación del Servicio Terapéutico</h2>
        <span>Apreciado Usuario ,</span><br><br>
        <span>El proceso de atención psicológica contempla los siguientes aspectos:</span><br><br>
        <span>Generalidades de la atención:</span><br><br>
        <ul>
            <li>Se realizará la sesión dispuesta a la fecha establecida por mutuo acuerdo entre el consultante y el profesional.</li>
            <li>Si el consultante desea realizar cambios de la fecha y hora previamente establecida deberá hacerlo con un tiempo de 5 horas. De lo contrario, se realizará la facturación de la sesión.</li>
            <li>El pago se realizará con 12 horas de anticipación a la cita programada; de no hacerlo, se dará por cancelada la sesión.</li>
            <li>El consultante puede solicitar un cambio del terapeuta tratante con previo aviso y con 2 posibilidades de argumentación del motivo.</li>
            <li>En caso de presentar alguna petición, queja, reclamo, sugerencia o felicitación, el usuario puede reportarla al correo electrónico psicologiasana.mente.co@gmail.com.</li>
            <li>En dado caso de solicitar devolución de dinero consignado, podrá hacerlo con máximo 7 días calendario después de la fecha y hora del pago. Pasado el tiempo no se llevará a cabo la devolución por fi nes contables.</li>
        </ul>
        <span>La información del proceso terapéutico se recopilará en la historia clínica que será registrada por el profesional asignado en la plataforma de "Sana Mente SAS" sin ninguna divulgación, tal y como lo establece la normativa nacional Ley 1090 de 2006 (Artículo 10: Deberes y obligaciones del psicólogo).</span><br><br>
        <span>Estos procedimientos son fundamentales para garantizar un proceso terapéutico efectivo y cumplir con las normativas vigentes. Agradecemos su confi anza en nuestros servicios y estamos disponibles para cualquier consulta adicional que pueda surgir.</span><br><br><br><br>
        
        <h2>Responsabilidades del usuario</h2>
        <ul>
            <li>Las sesiones se realizarán en modalidad virtual o presencial de acuerdo a lo establecida previamente.</li>
            <li>Los horarios de atención se desarrollarán de lunes a viernes de 8:00 a.m. a 9:00 p.m. y sábado de 8:00 a.m. a 1.00 p.m.</li>
            <li>El usuario debe garantizar un espacio reservado (sin interrupciones, sin otras personas que estén de manera presencial o conectadas por medio virtual) que garantice la confi dencialidad de la información.</li>
            <li>Para el desarrollo de la sesión se sugiere que el usuario tenga una presentación personal que favorezca el desarrollo de la misma, sentado en un espacio iluminado, evitando estar a contraluz.</li>
            <li>El desarrollo del proceso terapéutico implica contar con dispositivo electrónico que permita llevar a cabo sesiones de teleconferencia con acceso a vídeo y voz.</li>
            <li>El usuario debe contar con servicio de Internet, ya sea vía Wifi o por otra modalidad, que garantice la estabilidad de la conexión durante la videoconferencia.</li>
            <li>El usuario conoce que las sesiones en línea dependen de la conectividad, intervienen tanto el equipo desde el cual se conecta, así como la compañía que provee el servicio de internet. Si en las sesiones se dan interferencias o cortes de manera continua que impidan el desarrollo adecuado de la sesión, se deberá posponer la cita.</li>
            <li>Cumplir con las actividades o acuerdos que se establezcan durante el proceso terapéutico.</li>
        </ul>
        
        <h2>Responsabilidades del profesional</h2>
        <ul>
            <li>Asistir puntualmente a las sesiones programadas. En caso de inasistencia, comunicar al usuario a tiempo.</li>
            <li>Desarrollar las fases propuestas durante el proceso terapéutico, teniendo en cuenta el cumplimiento del usuario.</li>
            <li>Garantizar un espacio reservado (sin interrupciones, sin otras personas que estén de manera presencial o conectadas por medio virtual) que garantice la confi dencialidad de la información.</li>
            <li>Presentarse con condiciones que favorezcan el desarrollo de la misma (sentado en un espacio iluminado, evitando estar a contraluz, con presentación personal acorde al desarrollo de una sesión psicológica, entre otros).</li>
            <li>Tener un dispositivo electrónico que permita llevar a cabo sesiones de teleconferencia con acceso a vídeo y voz.</li>
            <li>Contar con servicio de Internet ya sea vía Wifi o por otra modalidad, que garantice la estabilidad de la conexión durante la videoconferencia.</li>
            <li>Disponer de audífonos que permitan la privacidad de la información que se está manejando.</li>
            <li>Disponer de un lugar físico idóneo para la atención presencial.</li>
        </ul>
    </div>
</div>

<script>
    // Inicializar el canvas para la firma
    const canvas = document.getElementById("signature-canvas");
    const context = canvas.getContext("2d");
    canvas.width = document.getElementById("signature-pad").offsetWidth;
    canvas.height = document.getElementById("signature-pad").offsetHeight;

    let drawing = false;

    canvas.addEventListener("mousedown", startDrawing);
    canvas.addEventListener("mousemove", draw);
    canvas.addEventListener("mouseup", stopDrawing);
    canvas.addEventListener("mouseout", stopDrawing);

    // Funciones para dibujar la firma
    function startDrawing(e) {
        drawing = true;
        context.beginPath();
        context.moveTo(e.offsetX, e.offsetY);
    }

    function draw(e) {
        if (drawing) {
            context.lineTo(e.offsetX, e.offsetY);
            context.stroke();
        }
    }

    function stopDrawing() {
        drawing = false;
    }

    // Limpiar la firma
    document.getElementById("clear-btn").addEventListener("click", function() {
        context.clearRect(0, 0, canvas.width, canvas.height);
    });

    // Modal de condiciones
    const termsModal = document.getElementById("terms-modal");
    const termsLink = document.getElementById("terms-link");
    const closeModal = document.getElementById("close-modal");

    termsLink.addEventListener("click", function(event) {
        event.preventDefault();
        termsModal.style.display = "flex";  // Cambié 'block' a 'flex' para mantener el centrado
    });

    closeModal.addEventListener("click", function() {
        termsModal.style.display = "none";  // Cierra el modal
    });

    // Cerrar el modal cuando se haga clic fuera del contenido
    window.addEventListener("click", function(event) {
        if (event.target === termsModal) {
            termsModal.style.display = "none";  // Cierra el modal si se hace clic fuera
        }
    });

    // Validar el canvas antes de enviar el formulario
    document.querySelector("#signature-form").addEventListener("submit", function(event) {
        // Verificar si el canvas está vacío (sin trazos)
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        let isCanvasEmpty = true;

        for (let i = 0; i < imageData.data.length; i += 4) {
            if (imageData.data[i + 3] !== 0) { // Comprueba si hay algún píxel no transparente
                isCanvasEmpty = false;
                break;
            }
        }

        if (isCanvasEmpty) {
            event.preventDefault(); // Prevenir el envío del formulario
            alert("Por favor, firme antes de aceptar las políticas.");
        } else {
            // Si no está vacío, almacenar la firma en base64
            const firmaData = canvas.toDataURL().split(',')[1];
            document.getElementById("firma_data").value = firmaData;
        }
    });
</script>
