<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
  exit;
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 99){
    header("location: login");
    exit;
}
if($_SESSION['numdoc'] !== "1014273279" && $_SESSION['numdoc'] !== "1000693019"){
  header("location: login");
  exit;
}

if($_SESSION['permiso_gastos'] !== 1 && $_SESSION['numdoc'] !== "1014273279" && $_SESSION['numdoc'] !== "1000693019"){
    header("location: login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Pagos</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">

  <!-- CSS Libraries -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">

  <!-- Select2 CSS (para select buscable) -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <link rel="icon" href="../../assets/images/icolet.png" type="image/x-icon">

<!-- Start GA -->
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-94034622-3');
</script>
<!-- /END GA -->
</head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      
      <?php include "nav.php"; ?>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>Administración</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="#">Administración</a></div>
              <div class="breadcrumb-item"><a href="#">Pagos</a></div>
              <div class="breadcrumb-item">Ver</div>
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">Pagos</h2>
            <br></br>

            <!-- FILTROS -->
            <div class="card">
              <div class="card-body">
                <?php
                // Preparar listas de pacientes y psicólogos desde tabla usuarios
                $pacientes_arr = [];
                $psicologos_arr = [];

                // Ajusta los nombres de columna si tu tabla usa id_usu u otro nombre
                $sqlPac = "SELECT id, pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE permiso = 9 ORDER BY pn_usu, pa_usu";
                if ($resPac = $conn->query($sqlPac)) {
                    while ($r = $resPac->fetch_assoc()) {
                        $nombre = trim(preg_replace('/\s+/', ' ', ($r['pn_usu'].' '.$r['sn_usu'].' '.$r['pa_usu'].' '.$r['sa_usu'])));
                        $pacientes_arr[] = ['id' => $r['id'], 'nombre' => $nombre];
                    }
                }

                $sqlPsi = "SELECT id, pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE permiso = 3 ORDER BY pn_usu, pa_usu";
                if ($resPsi = $conn->query($sqlPsi)) {
                    while ($r = $resPsi->fetch_assoc()) {
                        $nombre = trim(preg_replace('/\s+/', ' ', ($r['pn_usu'].' '.$r['sn_usu'].' '.$r['pa_usu'].' '.$r['sa_usu'])));
                        $psicologos_arr[] = ['id' => $r['id'], 'nombre' => $nombre];
                    }
                }

                // Valores actuales de filtros (para mantener selección)
                $filter_fecha = $_GET['fecha'] ?? '';
                $filter_fecpareal = $_GET['fecpareal'] ?? '';
                $filter_paciente = $_GET['paciente'] ?? '';
                $filter_psi = $_GET['psi'] ?? '';
                $filter_ref = $_GET['referencia'] ?? '';
                ?>
                <form method="get" class="mb-3">
                  <div class="form-row">
                
                    <div class="form-group col-md-2">
                      <label for="fecha">Fecha cargue</label>
                      <input type="date" name="fecha" id="fecha" class="form-control" value="<?= htmlspecialchars($filter_fecha) ?>">
                    </div>
                
                    <div class="form-group col-md-2">
                      <label for="fecpareal">Fecha pago</label>
                      <input type="date" name="fecpareal" id="fecpareal" class="form-control" value="<?= htmlspecialchars($filter_fecpareal) ?>">
                    </div>
                
                    <div class="form-group col-md-2">
                      <label for="paciente">Paciente</label>
                      <select name="paciente" id="paciente" class="form-control" style="width:100%;">
                        <option value="">Todos</option>
                        <?php foreach ($pacientes_arr as $p): ?>
                          <option value="<?= htmlspecialchars($p['id']) ?>" <?= ($filter_paciente !== '' && $filter_paciente == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nombre']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                
                    <div class="form-group col-md-2">
                      <label for="psi">Psicólogo</label>
                      <select name="psi" id="psi" class="form-control">
                        <option value="">Todos</option>
                        <?php foreach ($psicologos_arr as $p): ?>
                          <option value="<?= htmlspecialchars($p['id']) ?>" <?= ($filter_psi !== '' && $filter_psi == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nombre']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                
                    <div class="form-group col-md-2">
                      <label for="referencia">Referencia</label>
                      <input type="text" name="referencia" id="referencia" class="form-control" value="<?= htmlspecialchars($filter_ref) ?>" placeholder="Referencia">
                    </div>
                
                    <div class="form-group col-md-2 d-flex align-items-end">
                      <button type="submit" class="btn btn-primary mr-2 col-md-12">Filtrar</button>
                    </div>
                
                  </div>
                </form>
              </div>
            </div>
            <!-- FIN FILTROS -->

            <div class="row">
              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-bordered table-md">
                        <thead>
                        <tr>
                          <th scope="col" style="text-align: center;">#</th>
                          <th scope="col" style="text-align: center;">Orden de pago</th>
                          <th scope="col" style="text-align: center;">Fecha de pago</th>
                          <th scope="col" style="text-align: center;">Fecha de cargue</th>
                          <th scope="col" style="text-align: center;">Referencia</th>
                          <th scope="col" style="text-align: center;">Valor</th>
                          <th scope="col" style="text-align: center;">Modalidad</th>
                          <th scope="col" style="text-align: center;">Paciente</th>
                          <th scope="col" style="text-align: center;">Psicólogo</th>
                          <th scope="col" style="text-align: center;">Editar</th>
                        </tr>
                        </thead>
                        <tbody>
                          <?php
                            include "../../conexionsm.php";

                            // ---------- PAGINACIÓN ----------
                            $registrosPorPagina = 10;
                            $paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            if ($paginaActual < 1) $paginaActual = 1;
                            $offset = ($paginaActual - 1) * $registrosPorPagina;
                            $offset = (int)$offset;
                            $registrosPorPagina = (int)$registrosPorPagina;

                            // ---------- CONSTRUIR WHERE DINÁMICO ----------
                            $whereClauses = [];
                            $bindParams = []; // valores
                            $bindTypes = '';  // tipos para bind_param

                            if (!empty($_GET['fecha'])) {
                                $whereClauses[] = "p.`fecha` >= ? AND p.`fecha` < DATE_ADD(?, INTERVAL 1 DAY)";
                                $bindParams[] = $_GET['fecha'];
                                $bindParams[] = $_GET['fecha'];
                                $bindTypes .= 'ss';
                            }

                            if (!empty($_GET['fecpareal'])) {
                                $whereClauses[] = "p.`fecpareal` >= ? AND p.`fecpareal` < DATE_ADD(?, INTERVAL 1 DAY)";
                                $bindParams[] = $_GET['fecpareal'];
                                $bindParams[] = $_GET['fecpareal'];
                                $bindTypes .= 'ss';
                            }

                            if (!empty($_GET['paciente'])) {
                                $whereClauses[] = "s.userID = ?";
                                $bindParams[] = (int)$_GET['paciente'];
                                $bindTypes .= 'i';
                            }

                            if (!empty($_GET['psi'])) {
                                $whereClauses[] = "s.psi = ?";
                                $bindParams[] = (int)$_GET['psi'];
                                $bindTypes .= 'i';
                            }

                            if (!empty($_GET['referencia'])) {
                                $whereClauses[] = "p.`ref` LIKE ?";
                                $bindParams[] = '%' . $_GET['referencia'] . '%';
                                $bindTypes .= 's';
                            }

                            $whereSql = $whereClauses ? ' WHERE ' . implode(' AND ', $whereClauses) : '';

                            // ---------- CONSULTA PRINCIPAL + CONTEO ----------
                            $sql = "
                            SELECT SQL_CALC_FOUND_ROWS
                                p.*,
                                s.userID AS session_userID,
                                s.psi AS session_psi,
                                s.site AS session_tipo,
                                CONCAT_WS(' ', u1.pn_usu, u1.sn_usu, u1.pa_usu, u1.sa_usu) AS paciente_nombre,
                                CONCAT_WS(' ', u2.pn_usu, u2.sn_usu, u2.pa_usu, u2.sa_usu) AS psicologo_nombre
                            FROM pays p
                            LEFT JOIN sesiones_min_id s ON p.`order` = s.`order`
                            LEFT JOIN usuarios u1 ON s.userID = u1.id
                            LEFT JOIN usuarios u2 ON s.psi = u2.id
                            {$whereSql}
                            ORDER BY p.fecpareal DESC
                            LIMIT ?, ?
                            ";

                            if ($stmt = $conn->prepare($sql)) {
                                $bindParamsSelect = $bindParams;
                                $bindTypesSelect = $bindTypes . 'ii';
                                $bindParamsSelect[] = $offset;
                                $bindParamsSelect[] = $registrosPorPagina;

                                $a_params = [];
                                $a_params[] = & $bindTypesSelect;
                                foreach ($bindParamsSelect as $k => $v) {
                                    $a_params[] = & $bindParamsSelect[$k];
                                }
                                if ($bindTypesSelect) {
                                    call_user_func_array([$stmt, 'bind_param'], $a_params);
                                }

                                $stmt->execute();
                                $result = $stmt->get_result();

                                // total registros
                                $resTotal = $conn->query("SELECT FOUND_ROWS()");
                                $totalRegistros = (int)$resTotal->fetch_row()[0];
                                $totalPaginas = ($totalRegistros > 0) ? ceil($totalRegistros / $registrosPorPagina) : 1;

                                // mostrar filas
                                $count = $offset + 1;
                                while ($row = $result->fetch_assoc()) {
                                    echo '<tr>';
                                    echo '<td style="text-align: center;">' . $count . '</td>';
                                    echo '<td style="text-align: center;">' . htmlspecialchars($row['order']) . '</td>';
                                    echo '<td style="text-align: center;">' . htmlspecialchars($row['fecpareal']) . '</td>';
                                    echo '<td style="text-align: center;">' . htmlspecialchars($row['fecha']) . '</td>';
                                    echo '<td style="text-align: center;">' . htmlspecialchars($row['ref']) . '</td>';
                                    echo '<td style="text-align: center;">$ ' . number_format($row['valor'], 0, '.', ',') . '</td>';

                                    $pacienteNombre = trim($row['paciente_nombre'] ?? '');
                                    $psicologoNombre = trim($row['psicologo_nombre'] ?? '');
                                    if ($pacienteNombre === '') {
                                        $pacienteNombre = ($row['session_userID']) ? 'ID: ' . htmlspecialchars($row['session_userID']) : '-';
                                    }
                                    if ($psicologoNombre === '') {
                                        $psicologoNombre = ($row['session_psi']) ? 'ID: ' . htmlspecialchars($row['session_psi']) : '-';
                                    }
                                    echo '<td style="text-align: center;">' . ($row['session_tipo'] == 1 ? 'Presencial' : ($row['session_tipo'] == 2 ? 'Virtual' : '')) . '</td>';
                                    echo '<td style="text-align: center;">' . htmlspecialchars($pacienteNombre) . '</td>';
                                    echo '<td style="text-align: center;">' . htmlspecialchars($psicologoNombre) . '</td>';
                                    echo '<td style="text-align: center;">
                                            <button 
                                                class="btn btn-secondary btn-edit" 
                                                data-id="' . htmlspecialchars($row['ID']) . '" 
                                                data-userid="' . htmlspecialchars($row['session_userID']) . '" 
                                                data-valor="' . htmlspecialchars($row['valor']) . '" 
                                                data-tipo="' . htmlspecialchars($row['session_tipo'] ?? '') . '" 
                                                data-toggle="modal" 
                                                data-target="#exampleModal">
                                                <strong>Editar</strong>
                                            </button>
                                          </td>';
                                    echo '</tr>';
                                    $count++;
                                }

                                $stmt->close();
                            } else {
                                echo '<tr><td colspan="9">Error en la consulta.</td></tr>';
                            }
                          ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                    <div class="card-footer text-right">
                      <nav class="d-inline-block">
                        <ul class="pagination mb-0">
                          <!-- Botón de página anterior -->
                          <?php
                          // Función rápida para construir query string conservando filtros
                          function build_page_link($page) {
                              $qs = $_GET;
                              $qs['page'] = $page;
                              return '?' . http_build_query($qs);
                          }
                          ?>
                          <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= ($paginaActual <= 1) ? '#' : build_page_link(max(1, $paginaActual - 1)) ?>">
                              <i class="fas fa-chevron-left"></i>
                            </a>
                          </li>
                    
                          <?php
                          $rango = 3;
                          $inicio = max(1, $paginaActual - $rango);
                          $fin = min($totalPaginas, $paginaActual + $rango);

                          if ($inicio > 1) {
                              echo '<li class="page-item"><a class="page-link" href="' . build_page_link(1) . '">1</a></li>';
                              if ($inicio > 2) {
                                  echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                              }
                          }

                          for ($i = $inicio; $i <= $fin; $i++) {
                              $active = ($i == $paginaActual) ? 'active' : '';
                              echo "<li class='page-item $active'><a class='page-link' href='" . build_page_link($i) . "'>$i</a></li>";
                          }

                          if ($fin < $totalPaginas) {
                              if ($fin < $totalPaginas - 1) {
                                  echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                              }
                              echo '<li class="page-item"><a class="page-link" href="' . build_page_link($totalPaginas) . '">' . $totalPaginas . '</a></li>';
                          }
                          ?>
                    
                          <!-- Botón de página siguiente -->
                          <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= ($paginaActual >= $totalPaginas) ? '#' : build_page_link(min($totalPaginas, $paginaActual + 1)) ?>">
                              <i class="fas fa-chevron-right"></i>
                            </a>
                          </li>
                        </ul>
                      </nav>
                    </div>

                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <?php include "footer.php"; ?>
    </div>
  </div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
    
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Editar Pago</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
              <span aria-hidden="true">×</span>
            </button>
          </div>
    
          <form action="pagos_editar.php" method="POST">
            <div class="modal-body">
              <!-- Input oculto para el ID -->
              <input type="hidden" name="id" id="modalPagoId">
              <div class="form-row">
                <!-- Valor -->
                <div class="form-group col-md-12">
                    <label for="valor">Valor</label>
                    <input type="number" class="form-control" id="valor" name="valor" placeholder="Ingrese el valor" required>
                </div>

                <!-- Paciente con Select2 (modal) -->
                <div class="form-group col-md-12">
                    <label for="pacienteed">Paciente</label>
                    <select id="pacienteed" name="paciente" class="form-control select2" style="width: 100%;" required>
                        <option value="">Seleccione un paciente</option>
                        <?php foreach ($pacientes_arr as $p): ?>
                          <option value="<?= htmlspecialchars($p['id']) ?>" <?= ($filter_paciente !== '' && $filter_paciente == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nombre']) ?>
                          </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Presencial o Virtual -->
                <div class="form-group col-md-12">
                    <label for="tipo_consulta">Tipo de Consulta</label>
                    <select id="tipo_consulta" name="tipo_consulta" class="form-control" required>
                        <option value="">Seleccione un tipo de terapia</option>
                        <option value="1">Presencial</option>
                        <option value="2">Virtual</option>
                    </select>
                </div>
                
              </div>
    
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
              </div>
            </div>
          </form>
    
        </div>
      </div>
    </div>


  <!-- General JS Scripts -->
  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/tooltip.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>
  
  <!-- JS Libraies -->
  <script src="assets/modules/prism/prism.js"></script>

  <!-- Page Specific JS File -->
  <script src="assets/js/page/bootstrap-modal.js"></script>
  <script src="assets/modules/jquery-ui/jquery-ui.min.js"></script>

  <!-- Page Specific JS File -->
  <script src="assets/js/page/components-table.js"></script>
  
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <!-- Toastr JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>

  <!-- Select2 JS (después de jQuery) -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    // Función que convierte el texto de todos los inputs de la página a mayúsculas
    function convertirMayusculas() {
        const formularios = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], textarea');
        formularios.forEach(input => {
            input.addEventListener('input', function() {
                input.value = input.value.toUpperCase();
            });
        });
    }
    document.addEventListener('DOMContentLoaded', convertirMayusculas);

    $(document).ready(function() {

        // ---------- Select2: inicialización para ambos selects ----------
        if ($.fn.select2) {

            // 1) Select que está fuera del modal (filtros) -> inicializar al cargar
            if ($('#paciente').length && !$('#paciente').data('select2')) {
                $('#paciente').select2({
                    placeholder: "Seleccione un paciente",
                    allowClear: true,
                    width: '100%' // más fiable dentro de layouts / modales
                });
            }

            // 2) Select que está dentro del modal -> inicializar AL MOSTRAR el modal
            // (evita problemas con dropdown y cálculo de anchos)
            $('#exampleModal').on('shown.bs.modal', function () {
                var $pModal = $('#pacienteed');
                if ($pModal.length && !$pModal.data('select2')) {
                    $pModal.select2({
                        placeholder: "Seleccione un paciente",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $(this) // esencial para que no se corte el dropdown dentro del modal
                    });
                }
            });

            // Si el modal ya viniera abierto por alguna razón al cargar, forzamos la inicialización
            if ($('#exampleModal').hasClass('show')) {
                $('#exampleModal').trigger('shown.bs.modal');
            }

        } else {
            console.warn('Select2 no está disponible. Verifica la carga del script de Select2.');
        }
        // ----------------------------------------------------------------

        // Función de descifrado XOR con clave (tu original)
        function simpleDecrypt(text, key) {
            const decodedText = atob(text);
            let output = '';
            for (let i = 0; i < decodedText.length; i++) {
                output += String.fromCharCode(decodedText.charCodeAt(i) ^ key.charCodeAt(i % key.length));
            }
            return output;
        }

        const urlParams = new URLSearchParams(window.location.search);
        const encryptedStatus = urlParams.get('sta');

        if (encryptedStatus) {
            const status = simpleDecrypt(encryptedStatus, '2020');

            toastr.options = {
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            if (status === 'editpagook') {
                toastr.success('El Pago se editó correctamente');
            } else if (status === 'errorsql') {
                toastr.error('Hubo un error al editar el registro, verifique si quedó alterado.');
            } else if (status === 'nosepuedeporestado') {
                toastr.warning('No es posible editar este pago porque la sesión está cancelada o ya se dio.');
            } else if (status === 'nosepuedefacturado') {
                toastr.error('No se puede editar este pago porque ya está facturado.');
            } else if (status === 'no_update') {
                toastr.error('No se realizó ningún cambio.');
            }
        }

        // Click en editar: asigna el ID oculto antes de abrir modal (tu botón ya abre el modal vía data-* attrs)
        $(document).on('click', '.btn-edit', function () {
            var pagoId = $(this).data('id');
            var valor = $(this).data('valor');
            var paciente = $(this).data('userid');
            var tipo = $(this).data('tipo');

            // Asignar valores al modal
            $('#modalPagoId').val(pagoId);
            $('#valor').val(valor);
            $('#pacienteed').val(paciente).trigger('change'); // si usas Select2
            $('#tipo_consulta').val(tipo);
        });

    });
  </script>


</body>
</html>
