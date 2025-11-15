<?php
session_start();
if (empty($_SESSION["id"])) {
    header("location: login");
}
include "session_update.php";
include "../../conexionsm.php"; // Conexión a la base de datos

actualizarSesion($conn);
if ($_SESSION['permiso'] !== 3 && $_SESSION['permiso'] !== 99 && $_SESSION['numdoc'] !== '1000693019') {
    header("location: login");
}
$vistaDefault = (
    $_SESSION['numdoc'] == 1032407665 ||
    $_SESSION['numdoc'] == 1033731398
) ? 'month' : 'agendaWeek';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM &rsaquo; Agenda</title>
    <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="../../assets/images/icolet.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <script src="assets/modules/jquery.min.js"></script>

    <script src="assets/js/scripts.js"></script>
    <?php
$fechasRestringidas = [];
$idUsuario = $_SESSION["id"];
$query = "SELECT date FROM days_exception WHERE status = 0 AND id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $fechasRestringidas[] = $row["date"];
}
$stmt->close();
?>
<script>
    var fechasRestringidas = <?php echo json_encode($fechasRestringidas); ?>;
</script>

</head>
<body>
<div class="main-wrapper main-wrapper-1">
    <div class="navbar-bg"></div>
    <?php include "nav.php"; ?>
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Agenda</h1>
            </div>
            <div class="section-body">
                <div class="card">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#AgendarCitaModal">Agendar Nueva Cita</button>
                    <div class="card-body">
                        <div id='calendar'></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php include "footer.php"; ?>
</div>

<!-- Modal para ver detalles de la sesión -->
<div class="modal fade" id="sessionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sessionTitle"></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p><strong>Tipo:</strong> <span id="sessionType"></span></p>
                <p><strong>Fecha:</strong> <span id="sessionDate"></span></p>
                <p><strong>Hora:</strong> <span id="sessionTime"></span></p>
                <p><strong>Sitio:</strong> <span id="sessionSite"></span></p>
                <p><strong>Link de meet:</strong> <a id="sessionLink"></a></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="editSession">Editar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar sesión -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Sesión</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="editForm" action="calendar_update_event.php" method="POST">
                    <input type="hidden" id="editId" name="editId">
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" id="editDate" name="editDate" class="form-control" required readonly style="appearance: none; -webkit-appearance: none; -moz-appearance: none;">
                        <input type="hidden" name="id_profesional" id="id_profesional" value="<?php echo $_SESSION['id']?>" required>
                        </style>
                    </div>
                    <div class="form-group">
                        <label>Hora</label>
                        <select type="time" id="editTime" name="editTime" class="form-control" required>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Observación</label>
                        <input type="text" id="observa" name="observa" class="form-control" required>
                        </style>
                    </div>
                    <button type="submit" class="btn btn-success">Guardar Cambios</button>
                    <button type="button" class="btn btn-danger" id="cancelEdit">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/es.js"></script>
    <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.2/css/pikaday.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.2/pikaday.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment-with-locales.min.js"></script>


  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
<script>
$(document).ready(function() {
    let isEditing = false; // Bandera para controlar si se debe reabrir el modal

    $('#calendar').fullCalendar({
        locale: 'es',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        defaultView: '<?php echo $vistaDefault; ?>',
        editable: false,
        events: 'calendar_get_events.php',
        eventRender: function(event, element) {
            element.css("color", "white"); // Letra blanca en eventos

            // Establecer la duración del evento como 1 hora si no está configurada
            if (!event.end) {
                event.end = moment(event.start).add(1, 'hour'); // Añadir 1 hora al evento
            }
        },
        eventClick: function(event) {
            $('#sessionTitle').text(event.title);
            const tipoTerapiaMap = {
                1: "Individual",
                2: "Pareja",
                5: "Familia",
                6: "Psiquiátrica",
                7: "Valoración",
                8: "Nutrición",
                9: "Infantil"
            };
            $('#sessionType').text(tipoTerapiaMap[event.tipo] || "Desconocido");
            $('#sessionDate').text(event.start.format('YYYY-MM-DD'));
            $('#sessionTime').text(event.start.format('HH:mm'));
            const siteMap = {
                1: "Presencial",
                2: "Virtual"
            };
            $('#sessionSite').text(siteMap[event.site] || "Desconocido");
            $('#sessionLink').attr('href', event.link_ingreso).attr('target', '_blank').text(event.link_ingreso);
            $('#editSession').attr('data-id', event.id);
            
            $('#sessionModal').modal('show');
        }
    });


    $('#editSession').on('click', function() {
        var sessionId = $(this).attr('data-id');
        $('#editId').val(sessionId);
        $('#editDate').val($('#sessionDate').text());
        $('#editTime').val($('#sessionTime').text());
        $('#sessionModal').modal('hide');
        $('#editModal').modal('show');
    });

    // Si se cierra el modal de edición, solo vuelve al de información si no se editó nada
    $('#editModal').on('hidden.bs.modal', function() {
        if (!isEditing) {
            $('#sessionModal').modal('show');
        }
        isEditing = false; // Reiniciamos la bandera
    });

    // Botón de cancelar en el modal de edición
    $('#cancelEdit').on('click', function() {
        $('#editModal').modal('hide');
    });
});














$(document).ready(function () {
    var fechasRestringidas = window.fechasRestringidas || []; // Asegurar que no sea undefined
    
    var picker = new Pikaday({
        field: document.getElementById('editDate'),
        format: 'YYYY-MM-DD',
        minDate: moment().toDate(), // Ahora permite hoy, pero no días anteriores
        disableDayFn: function (date) {
            var formattedDate = moment(date).format('YYYY-MM-DD');
            // Deshabilitar fechas de la BD
            var isRestrictedDate = fechasRestringidas.includes(formattedDate);
            // Deshabilitar domingos
            var isSunday = date.getDay() === 0; // 0 es domingo
            // Deshabilitar días anteriores y el día actual
            var isBeforeToday = moment(date).isBefore(moment(), 'day'); // Compara solo el día

            return isRestrictedDate || isSunday || isBeforeToday; // Deshabilitar si es fecha restringida, domingo o día anterior
        },
        i18n: {
            previousMonth: 'Mes anterior',
            nextMonth: 'Mes siguiente',
            months: [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ],
            weekdays: [
                'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'
            ],
            weekdaysShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
        }
    });

    // Deshabilitar manualmente los días restringidos en el input
    $('#editDate').on('change', function () {
        var selectedDate = $(this).val();
        if (fechasRestringidas.includes(selectedDate)) {
            toastr.warning('Esta fecha no está disponible', 'Advertencia');
            $(this).val(''); // Borra la selección
        }
    });
});











document.getElementById('editDate').addEventListener('change', async function() {
    const id_profesional = document.getElementById('id_profesional').value;
    const fechaSeleccionada = this.value;

    try {
        const response = await fetch(`agendar_citas_get_hours.php?id_profesional=${id_profesional}&fecha=${fechaSeleccionada}`);
        const horasDisponibles = await response.json();

        const selectHora = document.getElementById('editTime');
        selectHora.innerHTML = ''; // Limpiar el select antes de llenarlo

        horasDisponibles.forEach(hora => {
            // Convertir la hora a formato HHMMSS
            const horaValue = String(hora).padStart(2, '0') + '0000'; // Agregar '0000' para los minutos y segundos

            // Formatear la hora para mostrar en el select
            const horaDisplay = moment().startOf('day').add(hora, 'hours').format('hh:mm A'); // Formato 09:00 AM

            const option = document.createElement('option');
            option.value = horaValue; // Por ejemplo, 050000
            option.textContent = horaDisplay; // Por ejemplo, 05:00 AM
            selectHora.appendChild(option);
        });

        // Realizamos la consulta AJAX para verificar la disponibilidad
        $.ajax({
            url: 'agendar_citas_validate_date.php',
            method: 'POST',
            data: { 
                fecha: fechaSeleccionada, 
                id_profesional: id_profesional 
            },
            success: function(response) {
                // Limpiar el select de tipo de atención
                var tipoAtencionSelect = document.getElementById('tipo_atencion');
                tipoAtencionSelect.innerHTML = '<option value="">Seleccione tipo de atención</option><option value="2">Virtual</option>';

                // Verificar si la fecha es válida para tipo presencial
                if (response == 'presencial') {
                    var optionPresencial = document.createElement("option");
                    optionPresencial.value = "1";
                    optionPresencial.text = "Presencial";
                    tipoAtencionSelect.appendChild(optionPresencial);
                }
            }
        });
    } catch (error) {
        console.error('Error al obtener las horas disponibles:', error);
    }
});
</script>



<!-- Modal para Agendar Nueva Cita -->
<div class="modal fade" id="AgendarCitaModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agendar Nueva Cita</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="agendarForm" method="post" action="calendar_new_session">
                    <input type="hidden" id="id_profesional" name="id_profesional" value="<?php echo $_SESSION['id']?>">
                    <div class="form-group">
                        <label for="fechaCita">Fecha</label>
                        <input type="date" id="fechaCita" name="fechaCita" class="form-control" required readonly>
                    </div>

                    <div class="form-group" id="habilitartodoGroup">
                        <label>
                            <input type="checkbox" id="habilitartodo" name="habilitartodo"> Habilitar todo
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="horaCita">Hora</label>
                        <select id="horaCita" name="horaCita" class="form-control" required></select>
                    </div>

                    <div class="form-group" id="agendarDosHorasGroup">
                        <label>
                            <input type="checkbox" id="agendarDosHoras" name="agendarDosHoras" onchange="toggleAgendarDosHoras()"> Agendar dos horas
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="tipoAtencion">Tipo de Atención</label>
                        <select id="tipoAtencion" name="tipoAtencion" class="form-control" required>
                            <option value="">Seleccione tipo de atención</option>
                            <option value="1">Presencial</option>
                            <option value="2">Virtual</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipoTerapia">Tipo de Terapia</label>
                        <select id="tipoTerapia" name="tipoTerapia" class="form-control" required>
                            <option value="">Seleccione tipo de terapia</option>
                            <option value="1">Individual</option>
                            <option value="2">Pareja</option>
                            <option value="5">Familia</option>
                            <option value="6">Psiquiatrica</option>
                            <option value="8">Nutrición</option>
                            <option value="9">Individual Infantil</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="paciente">Paciente</label>
                        <select id="paciente" name="paciente" class="form-control" required>
                        <?php
                        // Obtiene el ID del psicólogo desde la sesión
                        $psicologo = $_SESSION['id'];
                        
                        // Prepara la consulta SQL incluyendo también el campo 'estado'
                        $query = "SELECT ID, CONCAT_WS(' ', pn_usu, sn_usu, pa_usu, sa_usu) AS nombre_completo, proceso FROM usuarios WHERE profesional_asignado = ?";
                        $stmt = $conn->prepare($query);
                        
                        // Asigna el valor al parámetro y ejecuta la consulta
                        $stmt->bind_param("i", $psicologo);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        // Mapeo de estado numérico a texto
                        $estados = [
                            1 => 'INDIVIDUAL',
                            2 => 'PAREJA',
                            5 => 'FAMILIA',
                            6 => 'PSIQUIATRIA',
                            8 => 'NUTRICIÓN',
                            9 => 'INDIVIDUAL INFANTIL'
                        ];
                        
                        // Verifica si hay resultados y genera las opciones
                        if ($result->num_rows > 0) {
                            echo '<option value="">Seleccione paciente</option>';
                            while ($row = $result->fetch_assoc()) {
                                $estadoTexto = isset($estados[$row['proceso']]) ? $estados[$row['proceso']] : 'Desconocido';
                                echo '<option value="' . $row['ID'] . '">' . htmlspecialchars($row['nombre_completo']) . ' - ' . htmlspecialchars($estadoTexto) . '</option>';
                            }
                        }
                        
                        // Cierra la consulta preparada
                        $stmt->close();
                        ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Agendar Cita</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>

<script>
$(document).ready(function() {
    // Inicializa Select2 normalmente
    $('#paciente').select2({
        dropdownParent: $('#AgendarCitaModal'), // Hace que el dropdown se renderice dentro del modal
        placeholder: 'Seleccione un paciente',
        allowClear: true,
        width: '100%'
    });
});





$(document).ready(function () {
    var fechasRestringidas = window.fechasRestringidas || []; // Evitar valores indefinidos

    var picker = new Pikaday({
        field: document.getElementById('fechaCita'),
        format: 'YYYY-MM-DD',
        minDate: moment().toDate(), // Permite hoy, bloquea días anteriores
        disableDayFn: function (date) {
            var formattedDate = moment(date).format('YYYY-MM-DD');
            var isRestrictedDate = fechasRestringidas.includes(formattedDate);
            var isSunday = date.getDay() === 0; // 0 es domingo

            return isRestrictedDate || isSunday;
        },
        i18n: {
            previousMonth: 'Mes anterior',
            nextMonth: 'Mes siguiente',
            months: [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ],
            weekdays: [
                'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'
            ],
            weekdaysShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
        }
    });
});

function cargarDatosDeCita() {
    var selectedDate = $('#fechaCita').val();
    var id_profesional = '<?php echo $_SESSION['id']; ?>';

    // Cargar horas disponibles según la fecha seleccionada
    $.ajax({
        url: 'agendar_citas_get_hours.php',
        method: 'GET',
        data: { id_profesional: id_profesional, fecha: selectedDate },
        success: function(response) {
            var horasDisponibles = JSON.parse(response);
            var selectHora = $('#horaCita');
            selectHora.empty();

            horasDisponibles.forEach(hora => {
                var horaValue = moment().startOf('day').add(hora, 'hours').format('HH:mm'); // 24 horas para el value
                var horaText = moment().startOf('day').add(hora, 'hours').format('h:mm A'); // 12 horas para mostrar
                selectHora.append(`<option value="${horaValue}">${horaText}</option>`);
            });
        }
    });

    // Validar si la fecha permite atención presencial
    $.ajax({
        url: 'agendar_citas_validate_date.php',
        method: 'POST',
        data: { fecha: selectedDate, id_profesional: id_profesional },
        success: function(response) {
            var tipoAtencionSelect = $('#tipoAtencion');
            tipoAtencionSelect.empty();
            tipoAtencionSelect.append('<option value="">Seleccione tipo de atención</option>');
            tipoAtencionSelect.append('<option value="2">Virtual</option>');

            //if (response == 'presencial') {
                tipoAtencionSelect.append('<option value="1">Presencial</option>');
            //}
        }
    });
}

// Y aquí llamas a la función desde el evento:
$('#fechaCita').on('change', cargarDatosDeCita);



function toggleAgendarDosHoras() {
    const agendarDosHoras = document.getElementById('agendarDosHoras');
    const selectHora = document.getElementById('horaCita');

    if (agendarDosHoras.checked) {
        const horaSeleccionada = selectHora.value;
        
        // Convertir de "HH:MM" a "HHMM00"
        const horaNumerica = parseInt(horaSeleccionada.replace(':', '') + '00');
        
        // Buscar si existe la siguiente hora (una hora más)
        const opciones = Array.from(selectHora.options);
        const indexActual = opciones.findIndex(opt => {
            // Convertir cada opción al mismo formato para comparar
            const optHoraNumerica = parseInt(opt.value.replace(':', '') + '00');
            return optHoraNumerica === horaNumerica;
        });

        const siguienteOpcion = opciones[indexActual + 1];

        // Validar que exista
        if (!siguienteOpcion) {
            agendarDosHoras.checked = false;
            toastr.error('No es posible agendar dos horas porque no hay una hora continua disponible.');
            return;
        }

        // Convertir la siguiente opción al formato numérico
        const siguienteHoraNumerica = parseInt(siguienteOpcion.value.replace(':', '') + '00');

        // Validar que sea una hora después exacta (diferencia de 10000)
        const diferencia = siguienteHoraNumerica - horaNumerica;
        if (diferencia !== 10000) {
            agendarDosHoras.checked = false;
            toastr.error('No es posible agendar dos horas porque la hora siguiente no está disponible.');
            return;
        }
    }
}


$('#habilitartodo').on('change', toggleHabilitarTodo);

function toggleHabilitarTodo() {
    const habilitarTodo = $('#habilitartodo').is(':checked');
    const selectHora = $('#horaCita');
    const fechaCita = $('#fechaCita').val();

    if (habilitarTodo) {
        if (!fechaCita) {
            // Si no hay fecha seleccionada, mostrar toastr y detener la ejecución
            toastr.warning('Por favor, seleccione una fecha primero.');
            $('#habilitartodo').prop('checked', false); // Desmarcar el checkbox
            return;
        }

        // Llenar el select con todas las horas del día (ej: 1 AM a 11 PM)
        selectHora.empty();
        for (let hora = 1; hora <= 23; hora++) {
            const horaValue = moment().startOf('day').add(hora, 'hours').format('HH:mm');
            const horaText = moment().startOf('day').add(hora, 'hours').format('h:mm A');
            selectHora.append(`<option value="${horaValue}">${horaText}</option>`);
        }

        // Advertencia con toastr
        toastr.warning('Este agendamiento corre bajo su responsabilidad. El sistema no está validando disponibilidad y podría haber cruces. Por favor valide antes de continuar.');
    } else {
        if (fechaCita) {
            cargarDatosDeCita();
        } else {
            // Si no hay fecha, limpiar el select
            selectHora.empty();
        }
    }
}




</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
<script>
      $(document).ready(function() {
      // Función de descifrado XOR con clave
      function simpleDecrypt(text, key) {
          const decodedText = atob(text);  // Decodificar base64
          let output = '';
          for (let i = 0; i < decodedText.length; i++) {
              output += String.fromCharCode(decodedText.charCodeAt(i) ^ key.charCodeAt(i % key.length));
          }
          return output;
      }

      // Obtiene el valor cifrado de 'sta' en la URL
      const urlParams = new URLSearchParams(window.location.search);
      const encryptedStatus = urlParams.get('sta');

      if (encryptedStatus) {
          const status = simpleDecrypt(encryptedStatus, '2020'); // Descifra usando la clave

          if (status === 'success_insert') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('El paciente se agendó correctamente.');
          } else if (status === 'error_insert') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Error en la base de datos');
          } else if (status === 'success_update') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('El paciente se agendó correctamente.');
          } else if (status === 'cancorr') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Cita cancelada exitosamente.');
          } else if (status === 'error_update') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Error en la base de datos');
          } else if (status === 'error_exists') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('El paciente ya tiene una cita agendada para la misma fecha y hora');
          } else if (status === 'error_exists2') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('El paciente ya tiene una cita agendada para la misma fecha y hora para la segunda franja');
          } else if (status === 'carpagext') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Pago cargado correctamente.');
          } else if (status === 'atsok') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Actualización de tokens de Google efectuada exitosamente, por favor intente agendar de nuevo la cita.');
          } else if (status === 'reagcorr') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Cita reagendada de manera exitosa.');
          }
      }
    });
</script>






<!-- Modal de confirmación -->
<div class="modal fade" id="confirmarCitaModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmar agendamiento</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p><strong>Fecha:</strong> <span id="conf-fecha"></span></p>
        <p><strong>Hora:</strong> <span id="conf-hora"></span></p>
        <p><strong>Tipo de atención:</strong> <span id="conf-tipo-atencion"></span></p>
        <p><strong>Tipo de terapia:</strong> <span id="conf-tipo-terapia"></span></p>
        <p><strong>Paciente:</strong> <span id="conf-paciente"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="confirmarAgendar">Confirmar</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>
<script>
$(document).ready(function() {
    let formAgendar = $('#agendarForm');
    let modalAgendar = $('#AgendarCitaModal');
    let modalConfirm = $('#confirmarCitaModal');

    formAgendar.on('submit', function(e) {
        e.preventDefault(); // Evita envío inmediato

        // Obtiene los textos visibles de los selects
        const fecha = $('#fechaCita').val();
        const hora = $('#horaCita option:selected').text();
        const tipoAtencion = $('#tipoAtencion option:selected').text();
        const tipoTerapia = $('#tipoTerapia option:selected').text();
        const paciente = $('#paciente option:selected').text();

        // Llena los textos del modal de confirmación
        $('#conf-fecha').text(fecha || '—');
        $('#conf-hora').text(hora || '—');
        $('#conf-tipo-atencion').text(tipoAtencion || '—');
        $('#conf-tipo-terapia').text(tipoTerapia || '—');
        $('#conf-paciente').text(paciente || '—');

        // Cierra el modal de agendar y muestra el de confirmación
        modalAgendar.modal('hide');
        modalConfirm.modal('show');
    });

    // Confirmar envío
    $('#confirmarAgendar').on('click', function() {
        modalConfirm.modal('hide');
        formAgendar.off('submit').submit(); // Envía el form normalmente
    });

    // Si cancela la confirmación, reabre el modal original sin perder datos
    modalConfirm.on('hidden.bs.modal', function() {
        if (!$('#confirmarCitaModal').data('confirmed')) {
            modalAgendar.modal('show');
        }
        $('#confirmarCitaModal').removeData('confirmed');
    });

    // Marca cuando se confirma
    $('#confirmarAgendar').on('click', function() {
        $('#confirmarCitaModal').data('confirmed', true);
    });
});
</script>


</body>
</html>
