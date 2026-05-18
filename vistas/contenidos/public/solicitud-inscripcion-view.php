<?php
require_once __DIR__ . "/../../../modelos/mainModel.php";

// ID recibido por GET (si existe) - este es el id_curso
$idSeleccionado = isset($_GET['id']) ? intval($_GET['id']) : null;

// Si hay un id_curso específico, buscar solo sus curso_vigente
if ($idSeleccionado) {
    $sql = "
    SELECT 
        cv.id_curso_vigente,
        c.id_curso,
        c.nombre_curso,
        c.tipo_curso,
        
        -- Datos de COHORTE (si existe)
        co.id_cohorte,
        co.numero_cohorte,
        co.anio_cohorte,
        co.pago_inscripcion AS pago_cohorte,
        co.pago_modulo,
        co.fecha_inicio AS fecha_inicio_cohorte,
        co.fecha_fin AS fecha_fin_cohorte,
        co.estado_cohorte,
        
        -- Datos de PERIODO INTENSIVO (si existe)
        pi.id_periodo_intensivo,
        pi.pago_inscripcion_int,
        pi.pago_encuentro_int,
        pi.fecha_inicio_int,
        pi.fecha_fin_int,
        pi.estado_periodo,
        
        -- Determinar tipo de CV
        CASE 
            WHEN co.id_cohorte IS NOT NULL THEN 'cohorte'
            WHEN pi.id_periodo_intensivo IS NOT NULL THEN 'intensivo'
            ELSE 'desconocido'
        END AS tipo_cv,
        
        -- Estado actual
        CASE 
            WHEN co.estado_cohorte IN ('Vigente', 'Próximo') THEN co.estado_cohorte
            WHEN pi.estado_periodo IN ('Vigente', 'Próximo') THEN pi.estado_periodo
            ELSE 'Inactivo'
        END AS estado_actual

    FROM curso_vigente cv
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte AND co.estado_cohorte IN ('Vigente', 'Próximo')
    LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk AND pi.estado_periodo IN ('Vigente', 'Próximo')

    -- Verificar que tenga sección activa
    INNER JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk 
        AND s.nombre_seccion = 'A' 
        AND s.archivar_seccion = 1
    INNER JOIN profesor p ON s.ci_profesor = p.id_profesor AND p.estado_profesor = 1
 --   INNER JOIN curso_publicidad m ON c.id_curso = m.id_curso AND m.estado_curso_publicidad = 1

    WHERE 
        cv.estado_curso_vigente = 1 
        AND c.estado_curso = 1
        AND c.id_curso = :id_curso
        AND (co.id_cohorte IS NOT NULL OR pi.id_periodo_intensivo IS NOT NULL)

    ORDER BY 
        -- Priorizar vigentes primero
        CASE 
            WHEN co.estado_cohorte = 'Vigente' OR pi.estado_periodo = 'Vigente' THEN 1
            WHEN co.estado_cohorte = 'Próximo' OR pi.estado_periodo = 'Próximo' THEN 2
            ELSE 3
        END ASC,
        co.numero_cohorte DESC,
        pi.fecha_inicio_int DESC
    ";

    $stmt = mainModel::conectar()->prepare($sql);
    $stmt->bindValue(':id_curso', $idSeleccionado, PDO::PARAM_INT);
    $stmt->execute();
    $cursosVigentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} else {
    // Si no hay id específico, traer todos los cursos vigentes (tu consulta original)
    $sql = "
    SELECT 
        cv.id_curso_vigente,
        c.id_curso,
        c.nombre_curso,
        c.tipo_curso,
        
        -- Datos de COHORTE (si existe)
        co.id_cohorte,
        co.numero_cohorte,
        co.anio_cohorte,
        co.pago_inscripcion AS pago_cohorte,
        co.pago_modulo,
        co.fecha_inicio AS fecha_inicio_cohorte,
        co.fecha_fin AS fecha_fin_cohorte,
        co.estado_cohorte,
        
        -- Datos de PERIODO INTENSIVO (si existe)
        pi.id_periodo_intensivo,
        pi.pago_inscripcion_int,
        pi.pago_encuentro_int,
        pi.fecha_inicio_int,
        pi.fecha_fin_int,
        pi.estado_periodo,
        
        -- Determinar tipo de CV
        CASE 
            WHEN co.id_cohorte IS NOT NULL THEN 'cohorte'
            WHEN pi.id_periodo_intensivo IS NOT NULL THEN 'intensivo'
            ELSE 'desconocido'
        END AS tipo_cv,
        
        -- Estado actual
        CASE 
            WHEN co.estado_cohorte IN ('Vigente', 'Próximo') THEN co.estado_cohorte
            WHEN pi.estado_periodo IN ('Vigente', 'Próximo') THEN pi.estado_periodo
            ELSE 'Inactivo'
        END AS estado_actual

    FROM curso_vigente cv
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte AND co.estado_cohorte IN ('Vigente', 'Próximo')
    LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk AND pi.estado_periodo IN ('Vigente', 'Próximo')

    -- Verificar que tenga sección activa
    INNER JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk 
        AND s.nombre_seccion = 'A' 
        AND s.archivar_seccion = 1
    INNER JOIN profesor p ON s.ci_profesor = p.id_profesor AND p.estado_profesor = 1
  --  INNER JOIN curso_publicidad m ON c.id_curso = m.id_curso AND m.estado_curso_publicidad = 1

    WHERE 
        cv.estado_curso_vigente = 1 
        AND c.estado_curso = 1
        AND (co.id_cohorte IS NOT NULL OR pi.id_periodo_intensivo IS NOT NULL)

    ORDER BY 
        -- Priorizar vigentes primero
        CASE 
            WHEN co.estado_cohorte = 'Vigente' OR pi.estado_periodo = 'Vigente' THEN 1
            WHEN co.estado_cohorte = 'Próximo' OR pi.estado_periodo = 'Próximo' THEN 2
            ELSE 3
        END ASC,
        c.nombre_curso ASC,
        co.numero_cohorte DESC,
        pi.fecha_inicio_int DESC
    ";

    $stmt = mainModel::conectar()->prepare($sql);
    $stmt->execute();
    $cursosVigentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$cursos = $cursosVigentes;

// Debug
echo "<script>console.log('Cursos encontrados para ID $idSeleccionado:', " . json_encode(array_map(function($cv) {
    return [
        'id_curso_vigente' => $cv['id_curso_vigente'],
        'nombre_curso' => $cv['nombre_curso'],
        'tipo_cv' => $cv['tipo_cv'],
        'estado_actual' => $cv['estado_actual']
    ];
}, $cursosVigentes)) . ");</script>";
?>

<!-- Select2 para elegir curso_vigente ->
<select name="id_curso_vigente" id="curso_vigente_select" class="form-control select2" required>
    <option value="">Seleccione un curso disponible</option>
    <?php foreach($cursosVigentes as $cv): ?>
        <option value="<?= $cv['id_curso_vigente'] ?>" 
                <?= ($cv['id_curso_vigente'] == $idSeleccionado) ? 'selected' : '' ?> 
                data-pago="<?= $cv['pago_inscripcion'] ?>"
                data-tipo="<?= $cv['tipo_cv'] ?>"
                data-estado="<?= $cv['estado_actual'] ?>">
            <?= htmlspecialchars($cv['texto_mostrar']) ?>
        </option>
    <?php endforeach; ?>
</select>-->






<style>
body {
//  background-color: #e3f2fd; /* Azul claro */
 // min-height: 100vh;
  padding: 30px 0;
background: #0440cc;
background: linear-gradient(90deg, rgba(4, 64, 204, 1) 0%, rgba(0, 141, 241, 1) 50%, rgba(16, 78, 179, 1) 99%);
}
</style>

<div class="container fondo-azul mt-2">
			<a href="<?php echo SERVERURL; ?>index.php" style="color:white;"><i class="fas fa-arrow-left"></i> Volver al inicio</a>
  <h4 class="text-center mb-2" style="color:white;"><i class="fas fa-user-plus"></i> Registro de pago y datos personales
      <button type="button" class="btn btn-sm ver-ayuda-texto"
            data-text="Por favor, complete el formulario para iniciar el proceso de inscripción. Se solicitarán datos personales del estudiante, así como la realización de una transacción para el pago de la inscripción. Una vez que el personal reciba esta información y verifique su validez, se procederá a registrar al estudiante en el sistema y a crearle una cuenta de acceso con la clave registrada."
            title="Ver explicación"
            style="background-color: #FF4221; color: #fff; border: none; padding: 4px 8px; border-radius: 4px;">
      <i class="fas fa-question"></i>
      </button>
  </h4>

  <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/recuperarAjax.php" method="POST" enctype="multipart/form-data" autocomplete="off">

    <fieldset>
      <!--legend><i class="fas fa-id-card"></i> &nbsp; Datos personales</legend-->
      <legend style="font-size: 1rem;"><i class="fas fa-id-card"></i> &nbsp; Datos personales</legend>
    <div class="row">
        <div class="col-md-3 form-group">
          <label for="nombre">Nombre</label>
          <input type="text" class="form-control" name="nombre" id="nombre" maxlength="30" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}"  required>
        </div>
        <div class="col-md-3 form-group">
          <label for="apellido">Apellido</label>
          <input type="text" class="form-control" name="apellido" id="apellido" maxlength="30" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}"  required>
        </div>



        <div class="col-md-6 form-group">
          <label for="id_curso">Curso</label>
         
            

<!-- Select2 para elegir curso_vigente  -- curso_vigente_select ->
<select name="id_curso_vigente" id="curso_up" class="form-control select2" required>
    <option value="">Seleccione un curso disponible</option>
    <php foreach($cursosVigentes as $cv): ?>
        <php
        // Armar el texto según el tipo de CV
        if ($cv['tipo_cv'] == 'cohorte') {
            $textoMostrar = sprintf(
                '%s (co. %s.%s %s) F.inicio: %s / F.fin: %s',
                $cv['nombre_curso'],
                $cv['numero_cohorte'],
                $cv['anio_cohorte'],
                $cv['tipo_curso'],
                date('d-m-y', strtotime($cv['fecha_inicio_cohorte'])),
                date('d-m-y', strtotime($cv['fecha_fin_cohorte']))
            );
            $pago = $cv['pago_cohorte'];
        } elseif ($cv['tipo_cv'] == 'intensivo') {
            $textoMostrar = sprintf(
                '%s (%s) F.inicio: %s / F.fin: %s',
                $cv['nombre_curso'],
                $cv['tipo_curso'],
                date('d-m-y', strtotime($cv['fecha_inicio_int'])),
                date('d-m-y', strtotime($cv['fecha_fin_int']))
            );
            $pago = $cv['pago_inscripcion_int'];
        } else {
            $textoMostrar = $cv['nombre_curso'] . ' (' . $cv['tipo_curso'] . ')';
            $pago = 0;
        }
        ?>
        <option value="<?= $cv['id_curso_vigente'] ?>" 
                <?= ($cv['id_curso_vigente'] == $idSeleccionado) ? 'selected' : '' ?> 
                data-pago="<?= $pago ?> ?? '0'">
            <?= htmlspecialchars($textoMostrar) ?>
        </option>
    <php endforeach; ?>
</select-->            
     
            
            
            
            
            
            
            
            <select name="id_curso" id="curso_up" class="form-control select2" required>
    <option value="">Seleccione un curso disponible</option>
    <?php foreach($cursosVigentes as $cv): ?>
        <?php
        // 🔹 Abreviaciones de tipo_curso
        $abreviaciones = [
            'Intensivo' => 'INT',
            'Extendido' => 'EXT', 
            'Técnico Profesional' => 'Téc'
        ];
        $tipoCursoAbrev = $abreviaciones[$cv['tipo_curso']] ?? $cv['tipo_curso'];

        // 🔹 Armar el texto según el tipo de CV
        if ($cv['tipo_cv'] == 'cohorte') {
            $textoMostrar = sprintf(
                '%s (co. %s.%s %s) F.inicio: %s / F.fin: %s',
                $cv['nombre_curso'],
                $cv['numero_cohorte'],
                $cv['anio_cohorte'],
                $tipoCursoAbrev,
                date('d-m-y', strtotime($cv['fecha_inicio_cohorte'])),
                date('d-m-y', strtotime($cv['fecha_fin_cohorte']))
            );
            $pago = $cv['pago_cohorte'] ?? 0;
        } elseif ($cv['tipo_cv'] == 'intensivo') {
            $textoMostrar = sprintf(
                '%s (%s) F.inicio: %s / F.fin: %s',
                $cv['nombre_curso'],
                $tipoCursoAbrev,
                date('d-m-y', strtotime($cv['fecha_inicio_int'])),
                date('d-m-y', strtotime($cv['fecha_fin_int']))
            );
            $pago = $cv['pago_inscripcion_int'] ?? 0;
        } else {
            $textoMostrar = $cv['nombre_curso'] . ' (' . $tipoCursoAbrev . ')';
            $pago = 0;
        }
        ?>
        <option value="<?= $cv['id_curso_vigente'] ?>"
                <?= ($cv['id_curso_vigente'] == $idSeleccionado) ? 'selected' : '' ?>
                data-pago="<?= $pago ?>">
            <?= htmlspecialchars($textoMostrar) ?>
        </option>
    <?php endforeach; ?>
</select>
            
            
            
            
            
            
            
            <!--select name="id_curso" id="curso_up" class="form-control select2" required>
            <option value="">Seleccione un curso</option>
            <php foreach($cursos as $curso): ?>
          <option value="<?= $curso['id_curso'] ?>" 
          <?= ($curso['id_curso'] == $idSeleccionado) ? 'selected' : '' ?> 
          data-pago="<?= $curso['pago_inscripcion'] ?? '0' ?>">
    <?= htmlspecialchars($curso['nombre_curso']) ?> (<?= htmlspecialchars($curso['tipo_curso']) ?>)
  </option>
            <php endforeach; ?>
          </select-->
       
        
        
        
        </div>
    </div>


        
        
    <div class="row">
        <div class="col-md-2 form-group">
          <label for="fecha_nacimiento">Fecha de nacimiento</label>
          <!--input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento" min="1900-01-01" max="<?= date('Y-m-d') ?>"  required-->
          <input type="date" class="form-control" name="fecha_nacimiento" min="1920-01-01" max="<?= date('Y-m-d') ?>" id="fecha_nac" required>
        </div>
        <div class="col-md-2 form-group">
          <label for="cedula">Cédula</label>
            
          <input type="text" class="form-control" name="cedula" id="cedula" maxlength="10" pattern="[VE]{1}[1-9]{1}[0-9]{6,7}" placeholder="ej:V12345678"  required>
        </div>
        <div class="col-md-2 form-group">
          <label for="telefono">Teléfono</label>
          <input type="text" class="form-control" name="telefono" id="telefono" pattern="[0-9]{10,13}" maxlength="11" required>
        </div>
        <div class="col-md-3 form-group" style="display:flex;justify-content:center;align-items:center;padding-top:10px;">
        	<p>Solo se registran dígitos de números telefónicos, no caracteres como " _ ", "()", " - ". Ej: "04123334444".</p>
        </div>
        <div class="col-md-3 form-group">
          <label for="correo">Correo electrónico</label>
          <input type="text" class="form-control" name="correo" id="correo" maxlength="60" required>
        </div>

        <!-- Escriba el número de teléfono común (0412-123-1234) de esta manera: 04123334444. En el caso de que sea un número de Whatsapp (+58 412-123...) escríbalo así: 584123334444.-->
        
        <!--div class="col-md-4 form-group">
          <label for="direccion">Dirección</label>
          <input type="text" class="form-control" name="direccion" id="direccion" maxlength="150" required>
        </div-->
      </div>
    </fieldset>

    <br>

      
      <div id="alerta-general" class="alert alert-danger" style="display:none;"></div>
<div class="row">
  <div class="col-md-8">
    <fieldset>
      <legend style="font-size: 1rem;"><i class="fas fa-money-check-alt"></i> &nbsp; Información de pago</legend>
      <div class="row">
        <!--div class="col-md-2 form-group">
          <label for="monto">Monto</label>
          <input type="number" class="form-control" name="monto" id="monto" min="0" step="0.01" required>
        </div-->

                  <div class="col-md-2 form-group">
          <!--label>Pago de inscripción</label-->
          <p id="pagoInscripcionTexto" style="font-weight:bold; margin-top:8px;">Pago de inscripción: —</p>
        </div>
          
          <div class="col-md-2 form-group">
              <label for="monto">Monto</label>
              <input type="number" class="form-control" name="monto" id="monto" min="0" step="0.01" required>
              <small id="montoError" class="text-danger" style="display:none;">⚠️ El monto no puede exceder el pago de inscripción.</small>
            </div>
          
          
          
        <div class="col-md-3 form-group">
          <label for="fecha_pago">Fecha de pago</label>
          <input type="date" class="form-control" name="fecha_pago" min="2025-01-01" max="<?= date('Y-m-d') ?>" id="fecha_pago" required>
        </div>
        <div class="col-md-2 form-group" >
          <label for="metodo_pago">Método de pago</label>
          <select class="form-control" name="metodo_pago" id="metodo_pago" required>
            <option value="" disabled selected>Seleccione</option>
            <option value="Transferencia">Transferencia</option>
            <option value="Pago móvil">Pago móvil</option>
            <option value="Efectivo">Efectivo</option>
            <option value="Otro">Otro</option>
          </select>
        </div>
        <div class="col-md-3 form-group">
          <label for="captura_pago" style="font-size: 0.85rem;">Captura o imagen del pago</label>
          <input type="file" style="font-size: 0.85rem;" class="form-control-file" name="captura_pago" id="captura_pago" accept="image/*" required>
          <small id="nombre-archivo" class="form-text text-muted"></small>
        </div>
      </div>
    </fieldset>
  </div><!--row1-->
    
  <div class="col-md-4">
    <fieldset>
      <legend style="font-size: 1rem;"><i class="fas fa-lock"></i> &nbsp; Seguridad de acceso (mínimo 7 caracteres)</legend>
      <div class="text-danger small mt-1" id="alerta-clave" style="display:none;"></div>
      <div class="row">
        <div class="col-md-6 form-group">
          <label for="clave">Clave de acceso</label>
          <input type="password" class="form-control" name="clave" id="clave"required pattern="[^ ].*" title="El nombre no debe comenzar con espacio." minlength="7" maxlength="100" required>
        </div>
        <div class="col-md-6 form-group">
          <label for="confirmar_clave">Confirmar clave</label>
          <input type="password" class="form-control" name="confirmar_clave" required pattern="[^ ].*" title="El nombre no debe comenzar con espacio." id="confirmar_clave" minlength="7" maxlength="100" required>
        </div>
      </div>
      <div class="text-danger small mt-1" id="alerta-clave" style="display:none;"></div>
    </fieldset>
  </div><!--row2-->
    

    
    
    
    
    
    
    
    
    
    
</div> <!--padre-->

    <br>
      
    <p class="text-center">
      <button type="submit" class="btn btn-info btn-sm" id="btn-guardar" style="width:100%;"><i class="far fa-save"></i> &nbsp; GUARDAR</button>
    </p>
  </form>
</div>

<div id="modalTextoAyuda" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75);">
  <div style="position:relative; margin:auto; top:10%; max-width:60%; background:#000; border:3px solid #fff; padding:20px; border-radius:8px; color:white;">
    <p id="textoAyuda" style="font-size:1.2rem; line-height:1.6; text-align:center;">Por favor, complete el formulario para iniciar el proceso de inscripción. Se solicitarán datos personales del estudiante, así como la realización de una transacción para el pago de la inscripción. Una vez que el personal reciba esta información y verifique su validez, se procederá a registrar al estudiante en el sistema y a crearle una cuenta de acceso con la clave registrada.</p>
    <button onclick="document.getElementById('modalTextoAyuda').style.display='none'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
      <i class="fas fa-times"></i>
    </button>
  </div>
</div>

<script>
//modal
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".ver-ayuda-texto").forEach(btn => {
    btn.addEventListener("click", () => {
      const modal = document.getElementById("modalTextoAyuda");
      const texto = btn.dataset.text || "No hay ayuda disponible.";
      modal.querySelector("#textoAyuda").textContent = texto;
      modal.style.display = "block";
    });
  });
});
</script>

<script>
//imagen
document.addEventListener("DOMContentLoaded", () => {
  const input = document.getElementById("captura_pago");
  const nombreArchivo = document.getElementById("nombre-archivo");

  input.addEventListener("change", () => {
    const archivo = input.files[0];
    if (archivo) {
      nombreArchivo.textContent = `Archivo seleccionado: ${archivo.name}`;
    } else {
      nombreArchivo.textContent = "";
    }
  });
});
</script>


<script>
document.addEventListener("DOMContentLoaded", () => {
  const selectCurso = document.getElementById("curso_up");
  const pagoTexto = document.getElementById("pagoInscripcionTexto");
  const montoInput = document.getElementById("monto");
  const montoError = document.getElementById("montoError");
  const claveInput = document.getElementById("clave");
  const confirmarInput = document.getElementById("confirmar_clave");
  const alertaClave = document.getElementById("alerta-clave");
  const btnGuardar = document.getElementById("btn-guardar");
  const correoInput = document.getElementById("correo");
  const telefonoInput = document.getElementById("telefono");
  const fechaInput = document.getElementById("fecha_nac");

  const alertaGeneral = document.getElementById("alerta-general");

  let pagoActual = null;

  // Estados individuales
  let estadoMontoValido = false;
  let estadoClaveValida = false;
  let estadoCorreoValido = false;
  let estadoTelefonoValido = false;
  let estadoFechaValida = false;

  function actualizarEstadoBoton() {
    const todoValido = estadoMontoValido && estadoClaveValida && estadoCorreoValido && estadoTelefonoValido && estadoFechaValida;
    btnGuardar.disabled = !todoValido;
    btnGuardar.style.opacity = todoValido ? "1" : "0.6";
    btnGuardar.style.cursor = todoValido ? "pointer" : "not-allowed";
  }

  function mostrarError(mensaje) {
    alertaGeneral.textContent = mensaje;
    alertaGeneral.style.display = "block";
  }

  function limpiarError() {
    alertaGeneral.textContent = "";
    alertaGeneral.style.display = "none";
  }

  function validarFechaNacimiento() {
    const fechaIngresada = new Date(fechaInput.value);
    const hoy = new Date();
    const fechaLimite = new Date(hoy.getFullYear() - 9, hoy.getMonth(), hoy.getDate());

    if (fechaIngresada > fechaLimite) {
      mostrarError("⚠️ Debe tener al menos 9 años cumplidos.");
      estadoFechaValida = false;
    } else {
      limpiarError();
      estadoFechaValida = true;
    }
    actualizarEstadoBoton();
  }

  function actualizarPago() {
    const selected = selectCurso.options[selectCurso.selectedIndex];
    const pagoStr = selected.dataset.pago;
    const pago = parseFloat(pagoStr);

    if (!isNaN(pago) && pago > 0) {
      pagoTexto.textContent = `Pago de inscripción: ${pago.toFixed(2)}$`;
      pagoActual = pago;
    } else {
      pagoTexto.textContent = "Pago de inscripción: No definido";
      pagoActual = 0;
    }

    validarMonto();
  }

  function validarMonto() {
    const monto = parseFloat(montoInput.value);

    if (!isNaN(monto) && monto > 0) {
      if (pagoActual > 0 && monto > pagoActual) {
        montoError.textContent = `⚠️ El monto no puede exceder ${pagoActual.toFixed(2)}$`;
        montoError.style.display = "block";
        estadoMontoValido = false;
      } else {
        montoError.style.display = "none";
        estadoMontoValido = true;
      }
    } else {
      montoError.style.display = "none";
      estadoMontoValido = false;
    }

    actualizarEstadoBoton();
  }

  function validarClaves() {
    const clave = claveInput.value.trim();
    const confirmar = confirmarInput.value.trim();

    if (clave.length >= 7 && confirmar.length >= 7) {
      if (clave !== confirmar) {
        alertaClave.textContent = "⚠️ Las claves no coinciden.";
        alertaClave.style.display = "block";
        estadoClaveValida = false;
      } else {
        alertaClave.style.display = "none";
        estadoClaveValida = true;
      }
    } else {
      alertaClave.style.display = "none";
      estadoClaveValida = false;
    }

    actualizarEstadoBoton();
  }

  function validarCorreo() {
    const correo = correoInput.value.trim().toLowerCase();
    const correoValido = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-z]{2,}$/.test(correo);
    const dominiosPermitidos = [
      "@gmail.com", "@yahoo.com", "@hotmail.com", "@outlook.com",
      "@live.com", "@icloud.com", "@aol.com", "@protonmail.com",
      "@gmx.com", "@zoho.com"
    ];
    const dominioValido = dominiosPermitidos.some(d => correo.endsWith(d));

    if (!correoValido || !dominioValido) {
      mostrarError("⚠️ Correo inválido o no permitido.");
      estadoCorreoValido = false;
    } else {
      limpiarError();
      estadoCorreoValido = true;
    }

    actualizarEstadoBoton();
  }

  function validarTelefono() {
    const telefono = telefonoInput.value.trim();
    const prefijosValidos = [
      "0212","0412","0414","0415","0416","0417","0418",
      "0424","0426","0246","0247","0235","0238","0239"
    ];
    const prefijo = telefono.substring(0, 4);
    const sufijo = telefono.substring(4);

    const telefonoValido = /^[0-9]{11}$/.test(telefono);
    const sufijoValido = !/^0+$/.test(sufijo);

    if (!telefonoValido || !prefijosValidos.includes(prefijo) || !sufijoValido) {
      mostrarError("⚠️ Teléfono inválido o no permitido.");
      estadoTelefonoValido = false;
    } else {
      limpiarError();
      estadoTelefonoValido = true;
    }

    actualizarEstadoBoton();
  }

  // Eventos
  if (window.jQuery) {
    $('#curso_up').on('select2:select', actualizarPago);
  } else {
    selectCurso.addEventListener("change", actualizarPago);
  }

  montoInput.addEventListener("input", validarMonto);
  claveInput.addEventListener("input", validarClaves);
  confirmarInput.addEventListener("input", validarClaves);
  correoInput.addEventListener("input", validarCorreo);
  telefonoInput.addEventListener("input", validarTelefono);
  fechaInput.addEventListener("change", validarFechaNacimiento);

  // Inicializar al cargar
  actualizarPago();
  validarClaves();
  validarCorreo();
  validarTelefono();
  validarFechaNacimiento();
});
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Inicialización -->
<script>
  $(document).ready(function() {
    $('.select2').select2({
      placeholder: "Seleccione un curso",
      allowClear: true,
      width: '100%'
    });
  });
</script>








