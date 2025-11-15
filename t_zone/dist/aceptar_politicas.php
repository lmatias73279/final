<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);

date_default_timezone_set('America/Bogota');
$fecha_actual = date('Y-m-d');
$fecha_dos_anos_atras = date('Y-m-d', strtotime('-2 years', strtotime($fecha_actual)));
$userpoliticas = $_SESSION['id'];
$sql = "SELECT * FROM politicas WHERE fecha >= '$fecha_dos_anos_atras' AND userID = '$userpoliticas'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    header("Location: index");
    exit();
}

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

        // Insertar la información en la base de datos
        $sql = "INSERT INTO politicas (sign, fecha, userID) VALUES ('$nombre_imagen', '$fecha_actual', '$useridpolities')";
        

        // Función de encriptado XOR con clave
        function simpleEncrypt($text, $key) {
            $output = '';
            for ($i = 0; $i < strlen($text); $i++) {
                $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
            }
            return base64_encode($output); // Convertir a base64 para URL seguro
        }

        // El código que ejecuta la consulta
        if ($conn->query($sql) === TRUE) {
            // Encriptar el mensaje 'ok' con XOR y redirigir
            $encryptedStatus = simpleEncrypt('ok', '2020');
            header('Location: index?f=' . urlencode($encryptedStatus));
            exit();
        } else {
            echo "Error al registrar la firma: " . $conn->error;
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
    <h1>Aceptación de Tratamiento de datos personales.</h1>

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
            <p>He leído y acepto las <a href="#" id="terms-link">Tratamiento de Datos Personales</a>.</p>
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
        <h2>Tratamiento de Datos Personales</h2>
        
        <h2>Ley de Protección de Datos Personales o Ley 1581 de 2012</h2>
        <span>Dando cumplimiento a lo dispuesto en la Ley 1581 de 2012, "Por el cual se dictandisposiciones generales para la protección de datos personales" y de conformidadcon lo señalado en el Decreto 1377 de 2013, con la fi rma de este documentomanifi esto que he sido informado por SALUD MENTAL Y BIENESTAR INTEGRALSANA MENTE</span><br><br>
        <ul>
            <li>Sana Mente actuará como Responsable del Tratamiento de datos personales delos cuales soy titular y que, conjunta o separadamente podrá recolectar, usar ytratar mis datos personales conforme la Política de Tratamiento de DatosPersonales.</li>
            <li>Mis derechos como titular de los datos son los previstos en la Constitución y laley, especialmente el derecho a conocer, actualizar, rectifi car y suprimir miinformación personal, así como el derecho a revocar el consentimiento otorgadopara el tratamiento de datos personales.</li>
        </ul>
        <span><strong>Teniendo en cuenta lo anterior, autorizo de manera voluntaria, previa, explícita,informada e inequívoca a SANA MENTE para tratar mis datos personales y tomarmi huella y fotografía de acuerdo con su Política de Tratamiento de DatosPersonales para los fi nes relacionados con su objeto y en especial para fi neslegales, contractuales, terapéuticos y misionales descritos en la Política deTratamiento de Datos Personales.</strong></span>
    </div>
</div>

<script>
    // Inicializar el canvas para la firma
    const canvas = document.getElementById("signature-canvas");
    const context = canvas.getContext("2d");
    canvas.width = document.getElementById("signature-pad").offsetWidth;
    canvas.height = document.getElementById("signature-pad").offsetHeight;
    
    let drawing = false;
    
    // Eventos para mouse
    canvas.addEventListener("mousedown", startDrawing);
    canvas.addEventListener("mousemove", draw);
    canvas.addEventListener("mouseup", stopDrawing);
    canvas.addEventListener("mouseout", stopDrawing);
    
    // Eventos para touch (móviles)
    canvas.addEventListener("touchstart", startTouch, { passive: false });
    canvas.addEventListener("touchmove", moveTouch, { passive: false });
    canvas.addEventListener("touchend", stopDrawing);
    
    function startDrawing(e) {
        drawing = true;
        context.beginPath();
        context.moveTo(e.offsetX, e.offsetY);
    }
    
    function draw(e) {
        if (!drawing) return;
        context.lineTo(e.offsetX, e.offsetY);
        context.stroke();
    }
    
    function stopDrawing() {
        drawing = false;
    }
    
    // Funciones para touch
    function getTouchPos(touchEvent) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: touchEvent.touches[0].clientX - rect.left,
            y: touchEvent.touches[0].clientY - rect.top
        };
    }
    
    function startTouch(e) {
        e.preventDefault(); // evita que la página se desplace
        const pos = getTouchPos(e);
        drawing = true;
        context.beginPath();
        context.moveTo(pos.x, pos.y);
    }
    
    function moveTouch(e) {
        e.preventDefault(); // evita scroll
        if (!drawing) return;
        const pos = getTouchPos(e);
        context.lineTo(pos.x, pos.y);
        context.stroke();
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
