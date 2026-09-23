<?php
if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 3) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}
require_once "./config/APP.php";
require_once "./modelos/mainModel.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

$idSolicitud = $_GET['solicitud_id'] ?? "";
$idSolicitud = mainModel::decryption($idSolicitud);
$idSolicitud = mainModel::limpiar_cadena($idSolicitud);
$pdo = mainModel::conectar();

// Obtener datos de la solicitud
$stmt = $pdo->prepare("
    SELECT si.*, c.nombre_curso, c.tipo_curso
    FROM solicitud_inscripcion si
    LEFT JOIN curso c ON si.id_curso_seleccionado = c.id_curso
    WHERE si.id_solicitud = :id
");
$stmt->execute([':id' => $idSolicitud]);
$solicitud = $stmt->fetch();

if (!$solicitud) {
    echo '<p class="text-center">Solicitud no encontrada</p>';
    exit();
}

// Validación 1: ¿El estudiante ya existe?
$stmtEst = $pdo->prepare("
    SELECT COUNT(*) FROM estudiante
    WHERE ci_estudiante = :ci
      AND nombre = :nombre
      AND apellido = :apellido
      AND fecha_nacimiento = :nac
      AND telefono_estudiante = :tel
      AND correo_estudiante = :correo
");
$stmtEst->execute([
    ':ci' => $solicitud['cedula_estudiante'],
    ':nombre' => $solicitud['nombre_estudiante'],
    ':apellido' => $solicitud['apellido_estudiante'],
    ':nac' => $solicitud['fecha_nacimiento'],
    ':tel' => $solicitud['telefono_estudiante'],
    ':correo' => $solicitud['correo_Estudiante']
]);
$estudianteExiste = $stmtEst->fetchColumn() > 0;

// Validación 2: ¿El curso ya está activo y vigente?
$stmtCurso = $pdo->prepare("
    SELECT COUNT(*) FROM curso_vigente cv
    INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
    WHERE cv.id_curso_fk = :id
      AND cv.estado_curso_vigente = 1
      AND ch.estado_cohorte = 'Vigente'
      AND ch.archivar_cohorte = 1
");
$stmtCurso->execute([':id' => $solicitud['id_curso_seleccionado']]);
$cursoActivo = $stmtCurso->fetchColumn() > 0;

// Obtener secciones disponibles
$stmtSecciones = $pdo->prepare("
    SELECT 
        s.id_seccion, 
        s.nombre_seccion,
        COUNT(i.id_inscripcion) AS inscritos
    FROM seccion s
    INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
    INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
    LEFT JOIN inscripcion i ON i.id_seccion_fk = s.id_seccion AND i.archivada = 1
    WHERE cv.id_curso_fk = :id
      AND cv.estado_curso_vigente = 1
      AND ch.estado_cohorte = 'Vigente'
      AND ch.archivar_cohorte = 1
      AND s.archivar_seccion = 1
    GROUP BY s.id_seccion
    ORDER BY s.nombre_seccion ASC
");
$stmtSecciones->execute([':id' => $solicitud['id_curso_seleccionado']]);
$secciones = $stmtSecciones->fetchAll();

// Obtener profesores activos
$stmtProfe = $pdo->query("
    SELECT id_profesor, nombre_profesor, apellido_profesor
    FROM profesor
    WHERE estado_profesor = 1
    ORDER BY nombre_profesor ASC
");
$profesores = $stmtProfe->fetchAll();
$bloquearBoton = $estudianteExiste || !$cursoActivo;
?>

<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-check-circle fa-fw"></i> &nbsp; VERIFICAR SOLICITUD</h3>
    <p class="text-justify">Revisa los datos de la solicitud antes de procesarla. Si el estudiante ya existe o el curso ya está activo, no se puede aceptar.</p>
</div>
<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>solicitud-check"><i class="fas fa-plus fa-fw"></i> &nbsp; Verificar solicitud</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>solicitud-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de solicitudes</a>
        </li>
    </ul>   
</div>


<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <p><strong>Nombre:</strong> <?= htmlspecialchars($solicitud['nombre_estudiante'] . ' ' . $solicitud['apellido_estudiante']) ?></p>
            <p><strong>Cédula:</strong> <?= htmlspecialchars($solicitud['cedula_estudiante']) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($solicitud['telefono_estudiante']) ?></p>
            <p><strong>Correo:</strong> <?= htmlspecialchars($solicitud['correo_Estudiante']) ?></p>
            <p><strong>Fecha de nacimiento:</strong> <?= htmlspecialchars($solicitud['fecha_nacimiento']) ?></p>
            <p><strong>Curso seleccionado:</strong> ID: <?= $solicitud['id_curso_seleccionado'] ?> / <?= htmlspecialchars($solicitud['nombre_curso']) ?> (<?= htmlspecialchars($solicitud['tipo_curso']) ?>)</p>
            <p><strong>Fecha de solicitud:</strong> <?= htmlspecialchars($solicitud['fecha_solicitud']) ?></p>
            <p><strong>Estado:</strong> <?= htmlspecialchars($solicitud['estado_solicitud']) ?></p>
        </div>
        <div class="col-md-6 text-center">
            <p><strong>Captura de pago:</strong></p>
            <img src="<?= SERVERURL . 'vistas/' . $solicitud['captura_pago'] ?>" class="img-fluid" style="max-height: 500px; border: 2px solid #ccc;">
        </div>
    </div>

    <hr>

    <form class="FormularioAjax" action="<?= SERVERURL ?>ajax/recuperarAjax.php" method="POST" data-form="save" autocomplete="off">
       
<input type="hidden" name="nombre_estudiante" value="<?= htmlspecialchars($solicitud['nombre_estudiante']) ?>">
<input type="hidden" name="apellido_estudiante" value="<?= htmlspecialchars($solicitud['apellido_estudiante']) ?>">
<input type="hidden" name="cedula_estudiante" value="<?= htmlspecialchars($solicitud['cedula_estudiante']) ?>">
<input type="hidden" name="telefono_estudiante" value="<?= htmlspecialchars($solicitud['telefono_estudiante']) ?>">
<input type="hidden" name="correo_Estudiante" value="<?= htmlspecialchars($solicitud['correo_Estudiante']) ?>">
<input type="hidden" name="fecha_nacimiento" value="<?= htmlspecialchars($solicitud['fecha_nacimiento']) ?>">
<input type="hidden" name="monto" value="<?= htmlspecialchars($solicitud['monto']) ?>">
<input type="hidden" name="fecha_pago" value="<?= htmlspecialchars($solicitud['fecha_pago']) ?>">
<input type="hidden" name="metodo_pago" value="<?= htmlspecialchars($solicitud['metodo_pago']) ?>">
<input type="hidden" name="captura_pago" value="<?= htmlspecialchars($solicitud['captura_pago']) ?>">
<input type="hidden" name="solicitud_id_check" value="<?= $solicitud['id_solicitud'] ?>">
<?php
// Obtener el curso vigente activo y vigente
$stmtCursoVigente = $pdo->prepare("
    SELECT cv.id_curso_vigente
    FROM curso_vigente cv
    INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
    WHERE cv.id_curso_fk = :id
      AND cv.estado_curso_vigente = 1
      AND ch.estado_cohorte = 'Vigente'
      AND ch.archivar_cohorte = 1
    LIMIT 1
");
$stmtCursoVigente->execute([':id' => $solicitud['id_curso_seleccionado']]);
$idCursoVigente = $stmtCursoVigente->fetchColumn();
?>

<input type="hidden" name="id_curso_vigente_fk" value="<?= $idCursoVigente ?>">        
        
        
        <div class="form-group">
            <label for="seccion" class="form-label fs-5">Seleccionar sección disponible</label>
<select name="seccion_id" id="seccion" class="form-control" >
    <option value="">-- Seleccione una sección --</option>
    <?php foreach ($secciones as $sec): ?>
        <option value="<?= $sec['id_seccion'] ?>">
            <?= htmlspecialchars($sec['nombre_seccion']) ?> (Inscritos: <?= $sec['inscritos'] ?>)
        </option>
    <?php endforeach; ?>
</select>
        </div>

        <div class="form-group">

              <label for="profesor_select" class="form-label fs-8">Elegir profesor para crear una sección nueva si no hay cupos para las otras</label>
              <select name="id_profesor_fk_reg" id="profesor_select" class="form-select col-md-8" >
                <option value="" selected disabled>Buscar profesor...</option>
              </select>
        </div>

        <div class="form-group text-center">
            <?php if ($estudianteExiste): ?>
                <p class="text-danger">⚠️ Esta solicitud pertenece a un estudiante que ya está registrado.</p>
            <?php endif; ?>
            <?php if ($cursoActivo): ?>
                <p class="text-danger">⚠️ El curso seleccionado ya está activo y vigente.</p>
            <?php endif; ?>
            <?php if ($solicitud['estado_solicitud'] === 'Aprobada'): ?>
              <p class="text-center text-muted mt-2">⚠️ Esta solicitud ya fue aprobada y no puede ser rechazada.</p>
            <?php endif; ?>

            
            <!--            <button type="submit" class="btn btn-success" <= ($estudianteExiste || $cursoActivo) ? 'disabled' : '' ?>>-->
            <button type="submit" class="btn btn-success" <?= (!$cursoActivo || $estudianteExiste) ? 'disabled' : '' ?>>            
                <i class="fas fa-check"></i> Aceptar solicitud
            </button>
<!--button type="submit" class="btn btn-success" id="btnAceptar" <?= $bloquearBoton ? 'disabled data-bloqueado="1"' : '' ?>>
<i class="fas fa-check"></i> Aceptar solicitud</button>
            <!--button type="submit" class="btn btn-success" id="btnAceptar" <?= $bloquearBoton ? 'disabled' : '' ?>--> 
        </div>
    </form>
    
<div class="d-flex justify-content-center mt-3">
  <form action="<?php echo SERVERURL; ?>ajax/recuperarAjax.php" method="POST" class="FormularioAjax" data-form="update" data-entity="solicitud">
    <input type="hidden" name="id_solicitud_reprobar" value="<?php echo $_GET['solicitud_id']; ?>">
    <button type="submit" class="btn btn-danger" <?= ($solicitud['estado_solicitud'] === 'Aceptada') ? 'disabled' : '' ?>>
      Rechazar solicitud
    </button>
  </form>
</div>
    
</div>


<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
  $('#profesor_select').select2({
    placeholder: "Buscar profesor...",
    allowClear: true,
    minimumInputLength: 2,
    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_profesor",
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({
        results: data.map(p => ({
          id: p.id_profesor,
          text: `${p.nombre_profesor} ${p.apellido_profesor}`
        }))
      }),
      cache: true
    }
  });
});
</script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Inicializar Select2 para profesores
    $('#profesor_select').select2({
        placeholder: "Buscar profesor...",
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_profesor",
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: data.map(p => ({
                    id: p.id_profesor,
                    text: `${p.nombre_profesor} ${p.apellido_profesor}`
                }))
            }),
            cache: true
        }
    });

    // Validación en tiempo real para habilitar/deshabilitar el botón de aceptar
    $('#seccion').change(function() {
        validarBotonAceptar();
    });

    $('#profesor_select').change(function() {
        validarBotonAceptar();
    });

    function validarBotonAceptar() {
        const seccionSeleccionada = $('#seccion').val();
        const profesorSeleccionado = $('#profesor_select').val();
        const botonAceptar = $('button[type="submit"]');
        
        // Habilitar si hay sección seleccionada O profesor seleccionado
        if ((seccionSeleccionada && seccionSeleccionada !== '') || (profesorSeleccionado && profesorSeleccionado !== '')) {
            botonAceptar.prop('disabled', false);
        } else {
            botonAceptar.prop('disabled', true);
        }
    }

    // Validación inicial
    validarBotonAceptar();

    // Manejar el envío del formulario
    $('.FormularioAjax').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = new FormData(this);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const resultado = JSON.parse(response);
                    
                    if (resultado.Alerta === "recargar") {
                        Swal.fire({
                            icon: 'success',
                            title: resultado.Titulo,
                            text: resultado.Texto,
                            confirmButtonText: 'Aceptar'
                        }).then(() => {
                            window.location.href = '<?= SERVERURL ?>solicitud-list';
                        });
                    } else {
                        Swal.fire({
                            icon: resultado.Tipo === 'success' ? 'success' : 'error',
                            title: resultado.Titulo,
                            text: resultado.Texto,
                            confirmButtonText: 'Aceptar'
                        });
                    }
                } catch (e) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al procesar la respuesta del servidor',
                        confirmButtonText: 'Aceptar'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    });
});
</script>
