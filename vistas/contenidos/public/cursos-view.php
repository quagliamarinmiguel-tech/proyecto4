<?php
    
    error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/../../../modelos/mainModel.php";
$cursosPorPagina = 20;



require_once "./controladores/publicoControlador.php";
$inicioCtrl = new publicoControlador();
$datosInicio = $inicioCtrl->cargar_inicio_controlador();
$info = $datosInicio["infoceproca"];

// Página actual
$paginaActual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$tipoFiltro = isset($_GET['filtro_tipo']) ? trim($_GET['filtro_tipo']) : null;
$idFiltro = isset($_GET['filtro_nombre']) ? intval($_GET['filtro_nombre']) : null;
$offset = ($paginaActual - 1) * $cursosPorPagina;

$urlBase = "index.php?views=cursos";
if (!empty($_GET['filtro_nombre'])) {
    $urlBase .= "&filtro_nombre=" . urlencode($_GET['filtro_nombre']);
}
if (!empty($_GET['filtro_tipo'])) {
    $urlBase .= "&filtro_tipo=" . urlencode($_GET['filtro_tipo']);
}

// Condiciones simplificadas
$condiciones = "
    cv.estado_curso_vigente = 1
    AND p.estado_profesor = 1
    AND c.estado_curso = 1
    AND s.archivar_seccion = 1
    AND m.estado_curso_publicidad = 1
    AND s.nombre_seccion = 'A'
    AND (
        vig_co.id_cohorte IS NOT NULL 
        OR vig_pc.id_periodo_intensivo IS NOT NULL
        OR prox_co.id_cohorte IS NOT NULL
        OR prox_pc.id_periodo_intensivo IS NOT NULL
    )
";

$condiciones_principal = "
    cv.estado_curso_vigente = 1
    AND p.estado_profesor = 1
    AND c.estado_curso = 1
    AND s.archivar_seccion = 1
    AND m.estado_curso_publicidad = 1
    AND s.nombre_seccion = 'A'
    AND (
        vig_co.id_cohorte IS NOT NULL 
        OR vig_pc.id_periodo_intensivo IS NOT NULL
        OR prox_co.id_cohorte IS NOT NULL
        OR prox_pc.id_periodo_intensivo IS NOT NULL
    )
";

// Condiciones base para el COUNT
$condiciones_count_base = "
    cv.estado_curso_vigente = 1
    AND p.estado_profesor = 1
    AND c.estado_curso = 1
    AND s.archivar_seccion = 1
    AND m.estado_curso_publicidad = 1
    AND s.nombre_seccion = 'A'
    AND (
        cv.id_cohorte_fk IN (SELECT id_cohorte FROM cohorte WHERE estado_cohorte IN ('Vigente', 'Próximo'))
        OR cv.id_curso_vigente IN (SELECT id_curso_vigente_fk FROM periodo_intensivo WHERE estado_periodo IN ('Vigente', 'Próximo'))
        OR c.id_curso IN (
            SELECT cvp.id_curso_fk 
            FROM curso_vigente cvp 
            INNER JOIN cohorte cop ON cvp.id_cohorte_fk = cop.id_cohorte 
            WHERE cop.estado_cohorte = 'Próximo'
        )
        OR c.id_curso IN (
            SELECT pcv.id_curso_fk 
            FROM periodo_intensivo pcp 
            INNER JOIN curso_vigente pcv ON pcp.id_curso_vigente_fk = pcv.id_curso_vigente 
            WHERE pcp.estado_periodo = 'Próximo'
        )
    )
";








$params = [];
$params_count = []; // Parámetros separados para el COUNT

if ($tipoFiltro) {
    $condiciones_principal .= " AND c.tipo_curso = :tipo";
    $condiciones_count_base .= " AND c.tipo_curso = :tipo_count";
    $params[':tipo'] = $tipoFiltro;
    $params_count[':tipo_count'] = $tipoFiltro;
}

if ($idFiltro) {
    $condiciones_principal .= " AND c.id_curso = :id";
    $condiciones_count_base .= " AND c.id_curso = :id_count";
    $params[':id'] = $idFiltro;
    $params_count[':id_count'] = $idFiltro;
}


try {
    $pdo = mainModel::conectar();

    $sql = "
SELECT  
    m.id_materia,
    m.prefacio,
    m.materia_descripcion,
    m.cont1, m.cont2, m.cont3, m.cont4, m.cont5,
    m.cont6, m.cont7, m.cont8, m.cont9, m.cont10,
    m.requisito1, m.requisito2, m.requisito3, m.requisito4,
    m.icono1, m.icono2, m.icono3,
    m.clase,

    im_m.ruta AS ruta_materia,

    c.id_curso,
    c.nombre_curso,
    c.tipo_curso,
    c.cupos,

    -- CV principal
    cv.id_curso_vigente,
    
    -- ========== DATOS VIGENTES (ACTUAL) ==========
    -- Cohortes VIGENTES
    vig_co.pago_inscripcion AS pago_inscripcion_vigente,
    vig_co.pago_modulo AS pago_modulo_vigente,
    vig_co.fecha_inicio AS fecha_inicio_vigente,
    vig_co.fecha_fin AS fecha_fin_vigente,
    vig_co.numero_cohorte AS numero_cohorte_vigente,
    vig_co.anio_cohorte AS anio_cohorte_vigente,
    vig_co.id_cohorte AS id_cohorte_vigente,
    
    -- Periodos Intensivos VIGENTES
    vig_pc.pago_inscripcion_int AS pago_inscripcion_int_vigente,
    vig_pc.pago_encuentro_int AS pago_encuentro_int_vigente,
    vig_pc.pago_titulo_int AS pago_titulo_int_vigente,
    vig_pc.fecha_inicio_int AS fecha_inicio_int_vigente,
    vig_pc.fecha_fin_int AS fecha_fin_int_vigente,
    vig_pc.id_periodo_intensivo AS id_periodo_vigente,
    
    -- ========== DATOS PRÓXIMOS (SIGUIENTE) ==========
    -- Cohortes PRÓXIMOS
    prox_co.pago_inscripcion AS pago_inscripcion_proximo,
    prox_co.pago_modulo AS pago_modulo_proximo,
    prox_co.fecha_inicio AS fecha_inicio_proximo,
    prox_co.fecha_fin AS fecha_fin_proximo,
    prox_co.numero_cohorte AS numero_cohorte_proximo,
    prox_co.anio_cohorte AS anio_cohorte_proximo,
    prox_co.id_cohorte AS id_cohorte_proximo,
    
    -- Periodos Intensivos PRÓXIMOS
    prox_pc.pago_inscripcion_int AS pago_inscripcion_int_proximo,
    prox_pc.pago_encuentro_int AS pago_encuentro_int_proximo,
    prox_pc.pago_titulo_int AS pago_titulo_int_proximo,
    prox_pc.fecha_inicio_int AS fecha_inicio_int_proximo,
    prox_pc.fecha_fin_int AS fecha_fin_int_proximo,
    prox_pc.id_periodo_intensivo AS id_periodo_proximo,
    
    -- ========== DATOS PARA MOSTRAR (COALESCE - VIGENTE TIENE PRIORIDAD) ==========
    -- Datos actuales a mostrar (Vigente si existe, si no Próximo)
    COALESCE(
        vig_co.pago_inscripcion, 
        vig_pc.pago_inscripcion_int,
        prox_co.pago_inscripcion, 
        prox_pc.pago_inscripcion_int,
        0
    ) AS pago_inscripcion_actual,
    
    COALESCE(
        vig_co.pago_modulo, 
        vig_pc.pago_encuentro_int,
        prox_co.pago_modulo, 
        prox_pc.pago_encuentro_int,
        0
    ) AS pago_modulo_actual,
    
    COALESCE(
        vig_co.fecha_inicio, 
        vig_pc.fecha_inicio_int,
        prox_co.fecha_inicio,
        prox_pc.fecha_inicio_int
    ) AS fecha_inicio_actual,
    
    COALESCE(
        vig_co.fecha_fin, 
        vig_pc.fecha_fin_int,
        prox_co.fecha_fin,
        prox_pc.fecha_fin_int
    ) AS fecha_fin_actual,
    
    COALESCE(vig_co.numero_cohorte, prox_co.numero_cohorte) AS numero_cohorte_actual,
    COALESCE(vig_co.anio_cohorte, prox_co.anio_cohorte) AS anio_cohorte_actual,

    -- Determinar si hay Vigente y/o Próximo
    CASE 
        WHEN vig_co.id_cohorte IS NOT NULL OR vig_pc.id_periodo_intensivo IS NOT NULL THEN 'Vigente'
        WHEN prox_co.id_cohorte IS NOT NULL OR prox_pc.id_periodo_intensivo IS NOT NULL THEN 'Próximo'
        ELSE 'N/A'
    END AS estado_actual,

    CASE 
        WHEN prox_co.id_cohorte IS NOT NULL OR prox_pc.id_periodo_intensivo IS NOT NULL THEN 'Sí'
        ELSE 'No'
    END AS tiene_proximo,

    s.ci_profesor,
    p.nombre_profesor,
    p.apellido_profesor,
    p.descripcion,
    p.estado_profesor,
    p.profe_mostrar,
    im_p.ruta AS ruta_profesor

FROM curso_publicidad m
LEFT JOIN imagen im_m ON m.id_imagen_materia_fk = im_m.id_imagen
LEFT JOIN curso c ON m.id_curso = c.id_curso
LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk

-- Sección y Profesor
LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk AND s.nombre_seccion = 'A' AND s.archivar_seccion = 1
LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor AND p.estado_profesor = 1
LEFT JOIN imagen im_p ON p.imagen_profesor_fk = im_p.id_imagen

-- ========== VIGENTES ==========
-- Cohortes VIGENTES (del mismo CV)
LEFT JOIN cohorte vig_co ON cv.id_cohorte_fk = vig_co.id_cohorte AND vig_co.estado_cohorte = 'Vigente'

-- Periodos Intensivos VIGENTES (del mismo CV)
LEFT JOIN periodo_intensivo vig_pc ON cv.id_curso_vigente = vig_pc.id_curso_vigente_fk AND vig_pc.estado_periodo = 'Vigente'

-- ========== PRÓXIMOS ==========
-- Cohortes PRÓXIMOS (de cualquier CV del mismo curso)
LEFT JOIN (
    SELECT 
        cvp.id_curso_fk,
        cop.id_cohorte,
        cop.pago_inscripcion,
        cop.pago_modulo,
        cop.fecha_inicio,
        cop.fecha_fin,
        cop.numero_cohorte,
        cop.anio_cohorte
    FROM curso_vigente cvp
    INNER JOIN cohorte cop ON cvp.id_cohorte_fk = cop.id_cohorte
    WHERE cop.estado_cohorte = 'Próximo'
    ORDER BY ABS(DATEDIFF(CURDATE(), cop.fecha_inicio)) ASC
) prox_co ON prox_co.id_curso_fk = c.id_curso

-- Periodos Intensivos PRÓXIMOS (de cualquier CV del mismo curso)
LEFT JOIN (
    SELECT 
        pcv.id_curso_fk,
        pcp.id_periodo_intensivo,
        pcp.pago_inscripcion_int,
        pcp.pago_encuentro_int,
        pcp.pago_titulo_int,
        pcp.fecha_inicio_int,
        pcp.fecha_fin_int
    FROM periodo_intensivo pcp
    INNER JOIN curso_vigente pcv ON pcp.id_curso_vigente_fk = pcv.id_curso_vigente
    WHERE pcp.estado_periodo = 'Próximo'
    ORDER BY ABS(DATEDIFF(CURDATE(), pcp.fecha_inicio_int)) ASC
) prox_pc ON prox_pc.id_curso_fk = c.id_curso

WHERE $condiciones_principal
GROUP BY c.id_curso
ORDER BY 
    -- Priorizar cursos con estado Vigente primero
    CASE 
        WHEN vig_co.id_cohorte IS NOT NULL OR vig_pc.id_periodo_intensivo IS NOT NULL THEN 1
        WHEN prox_co.id_cohorte IS NOT NULL OR prox_pc.id_periodo_intensivo IS NOT NULL THEN 2
        ELSE 3
    END ASC,
    m.id_materia ASC
LIMIT :limite OFFSET :offset
";


    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limite', $cursosPorPagina, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // COUNT corregido con filtros
    $sqlTotal = "
SELECT COUNT(DISTINCT c.id_curso) AS total
FROM curso_publicidad m
LEFT JOIN curso c ON m.id_curso = c.id_curso
LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk 
LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor 
WHERE $condiciones_count_base
";

    $stmtTotal = $pdo->prepare($sqlTotal);
    // Usar los parámetros específicos para el COUNT
    foreach ($params_count as $key => $value) {
        $stmtTotal->bindValue($key, $value);
    }
    $stmtTotal->execute();
    $totalCursos = intval($stmtTotal->fetchColumn());

} catch (PDOException $e) {
    error_log("Error al obtener cursos paginados: " . $e->getMessage());
    $cursos = [];
    $totalCursos = 0;
}

// Debug mejorado para ver qué está pasando
echo "<script>console.log('Debug información:', " . json_encode([
    'pagina_actual' => $paginaActual,
    'tipo_filtro' => $tipoFiltro,
    'id_filtro' => $idFiltro,
    'cursos_encontrados' => count($cursos),
    'total_cursos' => $totalCursos,
    'condiciones_principal' => $condiciones_principal,
    'condiciones_count' => $condiciones_count_base
]) . ");</script>";

// Debug de los cursos
echo "<script>console.log('Cursos encontrados:', " . json_encode(array_map(function($curso) {
    return [
        'id_curso' => $curso['id_curso'],
        'nombre' => $curso['nombre_curso'],
        'tipo' => $curso['tipo_curso'],
        'estado_actual' => $curso['estado_actual'],
        'tiene_proximo' => $curso['tiene_proximo'],
        'vigente_cohorte' => $curso['id_cohorte_vigente'] ? 'Sí' : 'No',
        'vigente_periodo' => $curso['id_periodo_vigente'] ? 'Sí' : 'No',
        'proximo_cohorte' => $curso['id_cohorte_proximo'] ? 'Sí' : 'No',
        'proximo_periodo' => $curso['id_periodo_proximo'] ? 'Sí' : 'No'
    ];
}, $cursos)) . ");</script>";
?>



<div class="div_header">
	<div style="cursor:pointer;" class="logo_ceproca" onclick="window.location.href='<?php echo SERVERURL; ?>index.php', '_blank';">
        <h3><b id="not_ceproca">ceprociando</b></h3>
        <a href="index.php"><img src="../vistas/assets/img/logoceprocahd1.webp" alt="porqueno"></a>
    </div>

    <div class="header_barra">
        <ul>
            <li><a href="<?php echo SERVERURL; ?>index.php?views=cursos">Cursos</a></li>
            <li id="not_dropdown">
                <a style="cursor:context-menu;"  >Contáctanos</a>
                <ul class="dropdown">
                    <li><a href="<?php echo htmlspecialchars($info['link_facebook']); ?>">Facebook</a></li>
                    <li><a href="<?php echo htmlspecialchars($info['link_instagram']); ?>">Instagram</a></li>
                    <li> 
                        <div class="contactos_header" >
                             <a href="https://wa.me/<?php echo htmlspecialchars($info['telefono_contacto']); ?>">Teléfono</a>
                        </div>
                    </li>
                    <li>
                        <div class="contactos_header" >
                            <a href="mailto:<?php echo htmlspecialchars($info['correo_contacto']); ?>?subject=Asunto%20predefinido&body=Hola,%20quiero%20contactarte">Correo</a>
                        </div>
                    </li>
                </ul>
            </li>
            <li><a href="<?php echo SERVERURL; ?>?views=login">Iniciar sesión</a></li>
            <li><a href="<?php echo SERVERURL; ?>solicitud-inscripcion">Regístrate</a></li>       
        </ul>
    </div>
</div>




<div class="space"></div>

<div class="inicio_header_imagen_ceproca" style="background-image: url('../vistas/assets/img/graduacion3.webp');flex-direction:column;"><!--nueva2--> 
    <h1 class="header_imagen_ceproca_cursos_h1">Nuestros Cursos</h1>
	<p class="header_imagen_ceproca_cursos_p">¿Estás buscando mejorar tus habilidades y abrir nuevas puertas en tu carrera? CEPROCA es el lugar perfecto para ti. Nuestros cursos de alta calidad te brindarán las herramientas y conocimientos necesarios para alcanzar tus metas.</p>
</div>





<header class="texto_header_cursos_100_practicos">
    <div class="texto_header_cursos_100_practicos_div1"></div>
    	<h1>"Aprovecha cada oportunidad para aprender algo nuevo y especialízate en lo que amas."</h1>
    <div class="texto_header_cursos_100_practicos_div2"></div>
</header>


<div class="space"></div>

<div class="divbarracurso1" style="width:100%;">
 <div class="divbarracurso2" >
    <form style="width:100%;" class="barra_busqueda_cursos" onsubmit="event.preventDefault(); redirigirACurso();" >
      <input type="text" id="buscarPublicidad" class="form-control" placeholder="Buscar curso ...">
      <input type="hidden" id="id_materia_seleccionado" name="id_materia">
        <small style="width:1%;" id="val_publicidad" class="form-text mt-1"></small>

      <button type="button" class="button_barra" onclick="redirigirACurso()">→</button>
    </form>

    <div class="card_busqueda" id="card_busqueda" style="opacity:1;">
        <!--resultados_busqueda_nav'--> 
        <div class="list-group shadow-sm" id="resultadosPublicidad" style="font-size:5rem;"> 
        </div>
        <!-- style="opacity:1;background-color:#efeaea;position: absolute; z-index: 10; width: 100%; max-height: 230px; overflow-y: auto;color:black;">-->
    </div>
  </div>
  <!--p class="frase_barra">¿Buscas algún curso en específico?</p-->
 
 <!--action="<?php echo SERVERURL; ?>index.php?views=cursos"-->
<form method="GET"   onsubmit="return redirigirDesdeFormulario()" class="filtro-formulario" style="display:flex;justify-content:center;align-items:center;background-color:inherit;box-shadow: none;"> 
    <div class="filtro-campo">
    <!--label for="filtro_tipo">Tipo</label-->
      <select name="filtro_tipo" id="filtro_tipo">
        <option value="">Buscar cursos por tipo</option>
        <option value="Intensivo">Intensivo</option>
        <option value="Extendido">Extendido</option>
        <option value="Técnico Profesional">Técnico Profesional</option>
      </select>
    </div>
    <button type="submit" class="btn-filtrar" style="font-size:3rem;width:50%;margin:5%;">Buscar cursos</button>
</form>
</div>


<div class="cursos">
    <?php for ($index = 0; $index < count($cursos); $index += 2): ?>
        <!-- Bucle para las 2 tarjetas de cursos -->
        <?php for ($offset = 0; $offset <= 1; $offset++): ?>
            <?php if (isset($cursos[$index + $offset])): ?>
                <?php 
                $curso = $cursos[$index + $offset];
                $currentIndex = $index + $offset;
                ?>
                
                <!-- TARJETA DEL CURSO (solo este código, se repite 2 veces por ciclo) -->
                <div class="tarjetacurso3 tarjeta_curso car" id="<?php echo htmlspecialchars($curso['id_materia']); ?>" onclick="toggleDiv(<?php echo $currentIndex ?>, 'oculto<?php echo htmlspecialchars($curso['id_materia']); ?>', 130)">
                    <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($curso['ruta_materia']); ?>" />
                    <div class="subtarjetacurso3 tarjeta_curso_texto">
                        <p><?php echo htmlspecialchars($curso['nombre_curso']); ?></p>
                        <!-- style="font-size:4rem;"-->
                        <p class="tarjeta_curso_texto_prefacio"><?php echo htmlspecialchars($curso['prefacio']); ?></p>
                        
                        <?php if ($curso['profe_mostrar'] == 1): ?>
                        	<!--style="margin-left:3%;width:95%;height:30%;"-->
                            <section class="datosprofe_curso tarjeta_datosprofe_curso">
                                <!-- style="width:40%;height:65%;border-radius:50%;"-->
                                <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($curso['ruta_profesor']); ?>" />
                                <p><b>Prof. <?php echo htmlspecialchars($curso['nombre_profesor'] . ' ' . $curso['apellido_profesor']); ?></b></p>
                            </section>   
                        <?php endif; ?>
                        
                        <div class="info_precio_tarjeta3 div_precio_tarjeta">        
                            <div class="precio_n_tarjeta3 precio_tarjeta">
                                <?php
                                // Determinar precio (misma lógica para ambos cursos)
                                $esCohorteVigente = !empty($curso['id_cohorte_vigente']);
                                $esIntensivoVigente = !empty($curso['id_periodo_vigente']);
                                $esCohorteProximo = !empty($curso['id_cohorte_proximo']);
                                $esIntensivoProximo = !empty($curso['id_periodo_proximo']);

                                if ($esCohorteVigente) {
                                    $total = $curso['pago_inscripcion_vigente'];
                                } elseif ($esIntensivoVigente) {
                                    $total = $curso['pago_inscripcion_int_vigente'];
                                } elseif ($esCohorteProximo) {
                                    $total = $curso['pago_inscripcion_proximo'];
                                } elseif ($esIntensivoProximo) {
                                    $total = $curso['pago_inscripcion_int_proximo'];
                                } else {
                                    $total = 0;
                                }
                                ?>
                                
                                <!-- style="font-size:5rem;"-->
                                <p>Insc: <?php echo number_format($total, 2, ',', '.'); ?>$</p>
                                <!-- style="font-size:5rem;"-->
                                <p>- <?php echo htmlspecialchars($curso['tipo_curso']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php endif; ?>
        <?php endfor; ?>
        

    <?php for ($offset = 0; $offset <= 1; $offset++): ?>
            <?php if (isset($cursos[$index + $offset])): ?>
                <?php 
                $curso = $cursos[$index + $offset];
                $currentIndex = $index + $offset;
                ?>
                
                <div class="oculto" id="oculto<?php echo htmlspecialchars($curso['id_materia']); ?>">
                    <div style="background-color:white; height: auto;">
                        <section class="datos datos_cursos">
                            <section class="lado1 div_conjunto_datos_curso_1">
                                <section class="general curso_descripcion_y_profesor">
                                    <!-- style="font-size:7rem;"-->
                                    <p class="pref_curso"><?php echo htmlspecialchars($curso['materia_descripcion']); ?></p>
                                    
                                    <?php if ($curso['profe_mostrar'] == 1): ?>        
                                        <section class="datosprofe_curso">
                                            <img src="../vistas/<?php echo htmlspecialchars($curso['ruta_profesor']); ?>" />
                                            <p><b>Prof. <?php echo htmlspecialchars($curso['nombre_profesor'] . ' ' . $curso['apellido_profesor']); ?></b></p>
                                            <p><?php echo htmlspecialchars($curso['descripcion']); ?></p>
                                        </section>   
                                    <?php endif; ?>
                                </section><!--general-->

                                <!-- style="width:100%;flex-direction:column;"-->
                                <section class="checklist contenido_list">
                                    <h1>Aquí aprenderás:</h1>
                                    <!-- style="align-items:center;justify-content:center;width:100%;"-->
                                    <section class="parachecklisst div_contenido_list">  
                                        <!-- style="width:100%;flex-wrap:wrap;flex-direction:row;justify-content:center;"-->
                                        <section class="subchecklisst sub_div_contenido_list">
                                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                                <?php if (!is_null($curso['cont' . $i]) && $curso['cont' . $i] !== ''): ?>
                                            		<!-- style="width:40%;"-->
                                                    <section class="checking contenido_curso"><i class='bx bx-check-square'></i><p><?php echo htmlspecialchars($curso['cont' . $i]); ?></p></section>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </section>
                                    </section>
                                </section><!--checklisst-->
                                
                                <section class="inclusion div_beneficios_curso">
                                    <h1>Aquí obtendrás:</h1>
                                    <section class="inclusionforzada beneficios_curso">
                                        <section class="logros beneficio">
                                            <section class="logrospng clr_rojo beneficio_icon"><i class='bx bx-bookmark-alt-minus'></i></section><!-- b4-->
                                            <p>Obtén un certificado</p>
                                        </section>

                                        <?php if ($curso['icono1'] == 1): ?>
                                            <section class="logros beneficio">
                                                <section class="logrospng clr_rosa beneficio_icon"><i class='bx bx-book-open'></i></section><!-- b1 Técnico Profesional-->
                                                <p>Curso para mayores de 9 años en adelante</p>
                                            </section>
                                        <?php endif; ?>
                                        <?php if ($curso['icono2'] == 1): ?>
                                            <section class="logros beneficio">
                                                <section class="logrospng clr_verde beneficio_icon"><i class='bx bx-buildings'></i></section><!-- b2-->
                                                <p>Colaboraciones con negocios y empresas</p>
                                            </section>
                                        <?php endif; ?>
                                        <?php if ($curso['icono3'] == 1): ?>
                                            <section class="logros beneficio">
                                                <section class="logrospng clr_morado beneficio_icon"><i class='bx bxs-wrench'></i></section><!--b3-->
                                                <p>Prácticas y evaluaciones dentro y fuera de la institución</p>
                                            </section>
                                        <?php endif; ?>
                                    </section>
                                </section>
                            </section><!--lado1-->

                            <section class="lado2 div_conjunto_datos_curso_2">
                                <section class="otrascosas div_req_precio_duracion">
                                    <?php if ($curso['tipo_curso'] === 'Intensivo'): ?> 
                                        <h1 class="duracion_curso_h">Curso de 6 encuentros</h1> 
                                    <?php elseif ($curso['tipo_curso'] === 'Extendido'): ?> 
                                        <h1 class="duracion_curso_h">Curso de 3 meses de duración</h1> 
                                    <?php elseif ($curso['tipo_curso'] === 'Técnico Profesional'): ?> 
                                        <h1 class="duracion_curso_h">Curso Técnico de 3 meses de duración</h1> 
                                    <?php endif; ?>
                                    <br>
                                    <h1 class="nombre_curso_h"><?php echo htmlspecialchars($curso['nombre_curso']); ?></h1><br>
                                    
                                    
                                    
                                    
                                    <!--section class="enlace_inscripcion_cursos_view_tlfno flex_def">
                                        <php if ($curso['tipo_curso'] === 'Intensivo'): ?> 
                                            <h1 class="duracion_curso_h">Curso de 6 encuentros</h1> 
                                        <php elseif ($curso['tipo_curso'] === 'Extendido'): ?> 
                                            <h1 class="duracion_curso_h">Curso de 3 meses de duración</h1> 
                                        <php elseif ($curso['tipo_curso'] === 'Técnico Profesional'): ?> 
                                            <h1 class="duracion_curso_h">Curso Técnico de 3 meses de duración</h1> 
                                        <php endif; ?>

                                        <section class="cohorte_y_solicitud_curso">
                                            <a href="#"
                                                class="btn btn-sm car"
                                                onclick="window.location.href='<= SERVERURL ?>index.php?views=solicitud-inscripcion&id=<= htmlspecialchars($curso['id_curso']) ?>'; return false;">
                                                    Solicitar inscripción
                                            </a>
                                        </section>
                                    </section-->
                                    
                                    
                                    
                                    
                                    
                                  
                                    <hr>
                                    
                                    <section class="otrascosas1 requisitos_curso">
                                        <h1>Requisitos:</h1>
                                        <?php if (!empty($curso['requisito1'])): ?>
                                            <p>◦ <?php echo htmlspecialchars($curso['requisito1']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($curso['requisito2'])): ?>
                                            <p>◦ <?php echo htmlspecialchars($curso['requisito2']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($curso['requisito3'])): ?>
                                            <p>◦ <?php echo htmlspecialchars($curso['requisito3']); ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($curso['requisito4'])): ?>
                                            <p>◦ <?php echo htmlspecialchars($curso['requisito4']); ?></p>
                                        <?php endif; ?>
                                        <br><br>
                                    </section>
										       
                                    
                                    <section class="div_precio_y_horario_curso">
                                        <!-- style="align-items:flex-start;width:95%;"-->
                                        <section class="otrascosas21p precio_y_horario_curso">
                                            <section class="tengoclase precio_y_horario_titulo">
                                                <!-- style="color:#6C6;font-size:6rem;"-->
                                                <i class='bx bx-money' style="color:#6C6;"></i>
                                                <!--style="font-size:6rem;"-->
                                                <h1>Costos</h1>
                                            </section><!--tengoclase-->

                                            <?php 
                                            // Lógica de costos (CORREGIDA - había un error con las variables $curso[$index])
                                            if (!empty($curso['pago_inscripcion_vigente']) || !empty($curso['pago_modulo_vigente'])) {
                                                $inscripcion = $curso['pago_inscripcion_vigente'];
                                                $modulo = $curso['pago_modulo_vigente'];
                                                $total = $inscripcion + ($modulo * 3);
                                                $mod = $modulo;
                                                $textoCuota = 'Pago por módulo: ';
                                            } elseif (!empty($curso['pago_inscripcion_proximo']) || !empty($curso['pago_modulo_proximo'])) {
                                                $inscripcion = $curso['pago_inscripcion_proximo'];
                                                $modulo = $curso['pago_modulo_proximo'];
                                                $total = $inscripcion + ($modulo * 3);
                                                $mod = $modulo;
                                                $textoCuota = 'Pago por módulo: ';
                                            } elseif (!empty($curso['pago_inscripcion_int_vigente']) || !empty($curso['pago_encuentro_int_vigente'])) {
                                                $inscripcion = $curso['pago_inscripcion_int_vigente'];
                                                $encuentro = $curso['pago_encuentro_int_vigente'];
                                                $total = $inscripcion + ($encuentro * 6);
                                                $mod = $encuentro;
                                                $textoCuota = 'Pago por encuentro: ';
                                            } elseif (!empty($curso['pago_inscripcion_int_proximo']) || !empty($curso['pago_encuentro_int_proximo'])) {
                                                $inscripcion = $curso['pago_inscripcion_int_proximo'];
                                                $encuentro = $curso['pago_encuentro_int_proximo'];
                                                $total = $inscripcion + ($encuentro * 6);
                                                $mod = $encuentro;
                                                $textoCuota = 'Pago por encuentro: ';
                                            } else {
                                                $inscripcion = 0;
                                                $mod = 0;
                                                $total = 0;
                                                $textoCuota = '';
                                            }
                                            ?>

                                            <!--style="font-size:5rem;"-->
                                            <p>Inscripción: <?php echo number_format($inscripcion, 2, ',', '.'); ?> $</p>
                                            <!--style="font-size:5rem;"-->
                                            <p><?php echo $textoCuota; ?>: <?php echo number_format($mod, 2, ',', '.'); ?> $</p>
                                            <!--style="font-size:5rem;"-->
                                            <p>Total: <?php echo number_format($total, 2, ',', '.'); ?> $</p>
                                        </section>

                                        <!-- style="align-items:flex-start;width:95%;"-->
                                        <section class="otrascosas21h precio_y_horario_curso">
                                            <section class="tengoclase precio_y_horario_titulo">
                                                <!-- style="font-size:6rem;"-->
                                                <i class='bx bx-time-five' style="color:#f63;"></i>
                                                <!-- style="font-size:6rem;"-->
                                                <h1>Horarios</h1>
                                            </section>
                                            <!-- style="font-size:5rem;"-->
                                            <p><?php echo htmlspecialchars($curso['clase']); ?></p>
                                        </section>
                                    </section>
                       
                                    <hr/><br>
                                    <!-- style="height:15%;"-->
                                    <section class="llamanos cohorte_y_solicitud_curso">
                                        <?php if (!empty($curso['numero_cohorte_vigente']) && !empty($curso['anio_cohorte_vigente'])): ?>
                                        	<!-- style="font-size:5rem;"-->
                                            <p>
                                                Cohorte <?php echo htmlspecialchars($curso['numero_cohorte_vigente']); ?> - <?php echo htmlspecialchars($curso['anio_cohorte_vigente']); ?>
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($curso['numero_cohorte_proximo']) && !empty($curso['anio_cohorte_proximo'])): ?>
                                        	<!-- style="font-size:5rem;"-->
                                            <p>
                                                Próximo cohorte <?php echo htmlspecialchars($curso['numero_cohorte_proximo']); ?> - <?php echo htmlspecialchars($curso['anio_cohorte_proximo']); ?>
                                            </p>
                                        	<!-- style="font-size:5rem;"-->
                                            <p>
                                                Inscripción: <?php echo htmlspecialchars($curso['pago_inscripcion_proximo']); ?>$ - Módulo: <?php echo htmlspecialchars($curso['pago_modulo_proximo']); ?>$
                                            </p>
                                        <?php endif; ?>
                                         
                                        <?php if (!empty($curso['fecha_inicio_int_vigente']) && !empty($curso['fecha_fin_int_vigente'])): ?>
                                        	<!-- style="font-size:5rem;"-->
                                            <p>
                                                Intensivo <?php echo htmlspecialchars($curso['fecha_inicio_int_vigente']); ?> - <?php echo htmlspecialchars($curso['fecha_fin_int_vigente']); ?>
                                            </p>
                                        	<!-- style="font-size:5rem;"-->
                                            <p>
                                                Inscripción: <?php echo htmlspecialchars($curso['pago_inscripcion_int_vigente']); ?>$ - Encuentro: <?php echo htmlspecialchars($curso['pago_encuentro_int_vigente']); ?>$
                                            </p>
                                        <?php endif; ?>

                                        <?php if (!empty($curso['fecha_inicio_int_proximo']) && !empty($curso['fecha_fin_int_proximo'])): ?>
                                        	<!-- style="font-size:5rem;"-->
                                            <p>
                                                Próximo intensivo <?php echo htmlspecialchars($curso['fecha_inicio_int_proximo']); ?> - <?php echo htmlspecialchars($curso['fecha_fin_int_proximo']); ?>
                                            </p>
                                        	<!-- style="font-size:5rem;"-->
                                            <p>
                                                Inscripción: <?php echo htmlspecialchars($curso['pago_inscripcion_int_proximo']); ?>$ - Encuentro: <?php echo htmlspecialchars($curso['pago_encuentro_int_proximo']); ?>$
                                            </p>
                                        <?php endif; ?>
                                        
                                        <!-- style="margin:10px auto;width:90%; background-color:#3CA6A6; color:white; padding:8px; border-radius:8px; 
                                               text-decoration:none;font-size:4rem;box-shadow:rgba(0, 0, 0, 0.16) 0px 3px 6px, rgba(0, 0, 0, 0.23) 0px 3px 6px;"-->
                                        <a href="#"
                                        class="btn btn-sm car"
                                        onclick="window.location.href='<?= SERVERURL ?>index.php?views=solicitud-inscripcion&id=<?= htmlspecialchars($curso['id_curso']) ?>'; return false;">
                                            Solicitar inscripción
                                        </a>
                                        <br>
                                    </section>
                                    
                                </section>
                            </section>
                            
                        </section>
                    </div>
                </div><!--oculto-->
                
            <?php endif; ?>
        <?php endfor; ?>
        
    <?php endfor; ?>
</div>


<!--div class="paginacion"> la buena, q si servia 22925
    <?php
    $totalPaginas = ceil($totalCursos / $cursosPorPagina);
    for ($i = 1; $i <= $totalPaginas; $i++): ?>
    
        <a 
        echo '<a href="' . $urlBase . '&pagina=' . $i . '">Página ' . $i . '</a>';
            href="index.php?views=cursos&pagina=<= $i ?>#c"
            <= ($i == $paginaActual) ? 'class="activo"' : '' ?>
            style="color:white;"
        >
            <= $i ?>
        </a>

    <?php endfor; ?>
</div-->

<div class="paginacion">
    <?php
    $totalPaginas = ceil($totalCursos / $cursosPorPagina);
    for ($i = 1; $i <= $totalPaginas; $i++):
        $link = $urlBase . "&pagina=" . $i;
        $activo = ($i == $paginaActual) ? 'class="activo"' : '';
    ?>
        <a href="<?= $link ?>#c" <?= $activo ?> style="color:white;">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>

<!--div class="paginacion">
    <?php
    $totalPaginas = ceil($totalCursos / $cursosPorPagina);
    for ($i = 1; $i <= $totalPaginas; $i++): ?>
        
        <a style="color:white;" href="?pagina=<?php echo $i; ?>#c" <?php if ($i == $paginaActual) echo 'class="activo"'; ?>>
        	<?php echo $i; ?> 
        </a>
        
    <?php endfor; ?>
</div-->


<!--br><br><br><br><br><br-->
<div class="space"></div>






















<!--aqui empiezan los contactos-->
<div class="space"></div>
<div class="nada3">
    <h3>CONTÁCTANOS</h3><br><br>
</div>
<div class="div_contactos">
    <div class="enlaces_div_circulos">

        <div class="boton_contactos btn_contactos_animacion btn_contactos_clr1" onclick="window.open('<?php echo htmlspecialchars($info['link_facebook']); ?>', '_blank');">
            <img src="../vistas/assets/img/feici.webp">
        </div>
        
        <div class="boton_contactos btn_contactos_animacion btn_contactos_clr2" onclick="window.open('<?php echo htmlspecialchars($info['link_instagram']); ?>', '_blank');">
            <img src="../vistas/assets/img/insta.webp">
        </div>
        
        <div class="boton_contactos btn_contactos_animacion btn_contactos_clr3" onclick="toggleContactos(500)">
            <img src="../vistas/assets/img/maps.webp">
        </div>
        
        <div class="div_tlfno_correo">
            <div class="boton_contactos_animacion2 btn_contactos_clr4" onclick="window.open('https://wa.me/<?php echo htmlspecialchars($info['telefono_contacto']); ?>', '_blank')" id="botonTelefono">
                <img src="../vistas/assets/img/phoni1.webp">
        	</div>
            <article id="mensajeTelefono" class="hiddenz"><?php echo htmlspecialchars($info['telefono_contacto']); ?></article>
        </div>
       
        <div class="div_tlfno_correo">    
            <div class="boton_contactos_animacion2 btn_contactos_animacion btn_contactos_clr5" onclick="window.open('mailto:<?php echo htmlspecialchars($info['correo_contacto']); ?>?subject=Asunto%20predefinido&body=Hola,%20quiero%20contactarte')" id="botonEmail">
                <img src="../vistas/assets/img/carta1.webp">
            </div>
            <article id="mensajeEmail" class="hiddenz"><?php echo htmlspecialchars($info['correo_contacto']); ?></article>
        </div>   
    
    </div>
</div>

<div class="ocultocontactos" id="ocultocontactos">
    <div>
    	<section class="mapas">
            
            <section class="mapasfotos div_mapas_texto">
                <section class="mapastexto mapas_texto">
                    <h3>Encuéntranos</h3>
                    <p ><?php echo htmlspecialchars($info['direccion_local_ciudad']); ?></p>
                    <p ><?php echo htmlspecialchars($info['direccion_local_pais']); ?></p>
                </section>
                <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($info['ruta_local']); ?>">
            </section>
                                                           
            <section class="multislider-container flex_def" id="slidiomapas">
  				<section class="multislider-content" style="overflow:visible;">
                    <!--display:flex;justify-content:center;align-items:center;-->
                    <section class="multislide flex_def" style="overflow:visible;">        
                        <a style="margin:auto;overflow:visible;" href="../vistas/<?php echo htmlspecialchars($info['ruta_mapa']); ?>" target="_blank">
                            <img loading="lazy" class="mapa_desplazante" width="91%" src="../vistas/<?php echo htmlspecialchars($info['ruta_mapa']); ?>">
                        </a>
                    </section>
                    <section class="multislide flex_def">
                        <a style="margin:auto;" href="../vistas/<?php echo htmlspecialchars($info['ruta_satelite']); ?>" target="_blank">
                            <img loading="lazy" class="mapa_desplazante" width="91%" src="../vistas/<?php echo htmlspecialchars($info['ruta_satelite']); ?>">
                        </a>
                    </section>            
            	</section>
			</section>
        
        </section>
	</div>
</div>
<!--aqui terminan los mapas-->






























<!--iframe src="aiframeceprocaprofes.php" style="width:100%; height:105vh;border:0;"></iframe-->
<!--br><br><br><br><br><br-->
<div class="space"></div>

<div class="div_footer_cursos_texto" ><!--otrascosas2add-->
	<div class="footer_cursos_texto"><!-- style="border:0;"--><!--generaladd-->
        <!-- style="font-size:8rem;"-->
    	<h1>El aprendizaje nunca se detiene</h1>
        <!-- style="font-size:6rem;"-->
        <p>En un mundo que cambia rápidamente, adquirir nuevas habilidades y conocimientos es esencial para mantenerte relevante. Un curso te ofrece la oportunidad de aprender de expertos en el campo, acceder a recursos actualizados y participar en un entorno estructurado que fomenta el aprendizaje efectivo. Al inscribirte en un curso, no solo aprendes, sino que también te rodeas de personas que comparten tus intereses. Estas conexiones pueden abrir puertas a nuevas oportunidades laborales, colaboraciones y amistades duraderas. Nunca sabes quién podría ser tu próximo socio comercial o mentor.</p>
    </div>
    <!--style="position:relative;width:30%;height:100%;bottom:0;"-->
	<div class="footer_cursos_texto_img">
        <!-- style="position:absolute;width:90%;height:90%;bottom:0;left:0;"-->
        <img src="../vistas/assets/img/persona1.webp">
    </div>
</div>








<div class="creditos footer">
	<p>Somos el Centro Educativo Profesional Cecilio Acosta</p>
    <p>¡Sinónimo de Excelencia!</p>
    
    
    
    <section class="tlfno_y_correo_footer flex_def">
        <section class="datos_tlfno_y_correo_footer flex_def">
            <i class='bx bx-phone'></i>    <p><?php echo htmlspecialchars($info['telefono_contacto']); ?></p>
        </section>
        <section class="datos_tlfno_y_correo_footer flex_def">
            <i class='bx bx-envelope'></i> <p><?php echo htmlspecialchars($info['correo_contacto']); ?></p>
        </section>
    </section>
    
    
    
    
    <div class="footer_creditos">
    	
        <div class="footer_creditos_imagen">
            <img src="../vistas/assets/img/logoceprocahd1.webp" alt="porqueno">
        </div>
    	
        <div class="footer_creditos_siguenos">
        	<p>Síguenos</p>
            <div class="footer_creditos_siguenos_enlaces">
                <a class="footer_creditos_siguenos_enlaces1" href="<?php echo htmlspecialchars($info['link_instagram']); ?>" target="_blank">
                    <img src="../vistas/assets/img/insta.webp" alt="porqueno" width="20" height="20">
                </a>
                <a class="footer_creditos_siguenos_enlaces2"  href="<?php echo htmlspecialchars($info['link_facebook']); ?>" target="_blank">
                    <img src="../vistas/assets/img/feici.webp" alt="porqueno" width="20" height="20">
                </a>
            </div>
        </div>
    	
        <div class="footer_creditos_copi">
            <p>© Centro Educativo Profesional Cecilio Acosta.CEPROCA.2024</p>
            <p><b class="arroba tooltip" data-tooltip="quaglia.marin.miguel@gmail.com&#10;santiagosan2022@gmail.com">&#128231;</b>  Made by Oliver Martinez and Miguel Quaglia</p>
        </div>
        
    </div>
</div><!--creditos-->





<!--script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function() {
    $('#filtro_nombre').select2({
      placeholder: "Seleccione un curso",
      allowClear: true
    });
  });
</script-->





<script>
function redirigirDesdeFormulario() {
  const tipo = document.getElementById("filtro_tipo").value;
  const base = "<?php echo SERVERURL; ?>index.php?views=cursos";
  const url = tipo ? `${base}&filtro_tipo=${encodeURIComponent(tipo)}` : base;
  window.location.href = url;
  return false; // evita el envío tradicional
}
</script>

<script src="<?php echo SERVERURL; ?>vistas/js/buscadorFK.js"></script>



<script>
// Mostrar teléfono al pasar el mouse
const botonTelefono = document.getElementById('botonTelefono');
const mensajeTelefono = document.getElementById('mensajeTelefono');

botonTelefono.addEventListener('mouseenter', () => {
    mensajeTelefono.classList.add('visiblez');
    mensajeTelefono.classList.remove('hiddenz');
});

botonTelefono.addEventListener('mouseleave', () => {
    if (!mensajeTelefono.classList.contains('clicked')) {
        mensajeTelefono.classList.remove('visiblez');
        mensajeTelefono.classList.add('hiddenz');
    }
});

// Mostrar correo al pasar el mouse
const botonEmail = document.getElementById('botonEmail');
const mensajeEmail = document.getElementById('mensajeEmail');

botonEmail.addEventListener('mouseenter', () => {
    mensajeEmail.classList.add('visiblez');
    mensajeEmail.classList.remove('hiddenz');
});

botonEmail.addEventListener('mouseleave', () => {
    if (!mensajeEmail.classList.contains('clicked')) {
        mensajeEmail.classList.remove('visiblez');
        mensajeEmail.classList.add('hiddenz');
    }
});

function toggleContactos(alturaVisible) {
    const oculto = document.getElementById("ocultocontactos");
    const div = oculto.querySelector("div");

    if (div.classList.contains("show")) {
        div.classList.remove("show");
        oculto.classList.remove("show");
        setTimeout(() => {
            oculto.style.height = "0px";
            oculto.style.opacity = "0";
        }, 500);
    } else {
        div.classList.add("show");
        oculto.classList.add("show");
        oculto.style.height = alturaVisible + "px";
        oculto.style.opacity = "1";
    }
}
</script>






<script>

document.addEventListener("DOMContentLoaded", () => {
  iniciarBuscadorFK({
    inputTextoID: "buscarPublicidad",
    listaResultadosID: "resultadosPublicidad",
    inputHiddenID: "id_materia_seleccionado",
    validacionID: "val_publicidad", // opcional si querés mostrar mensajes
    endpointURL: "<?php echo SERVERURL; ?>ajax/codigo_busqueda_publicidad.php"
  });
});




function redirigirACurso() {
  const idMateria = document.getElementById("id_materia_seleccionado").value;
  const tipo = 2; // Informativo

  if (idMateria) {
    window.location.href = "<?php echo SERVERURL; ?>vistas/contenidos/public/aiframemateria3-copia.php?id=" + idMateria + "&valor=" + tipo;
  } else {
    alert("Por favor, selecciona un curso antes de continuar.");
  }
}


function seleccionarCurso(nombreCurso, idMateria) {
    document.getElementById("buscar").value = nombreCurso;
    document.getElementById("id_materia_seleccionado").value = idMateria;
}




//modo oscuro
//const swith= document.querySelector(".switch");
//swith.addEventListener("click", e => {swith.classList.toggle("active");
//document.body.classList.toggle("active");})

//funcion de ocultar definitiva
function toggleDiv(index, ocultoId, alturaVisible) {
    console.log(index, ocultoId, alturaVisible);
  var oculto = document.getElementById(ocultoId);
  var divs = oculto.getElementsByTagName("div");
  var isAlreadyOpen = divs[0].classList.contains("show");

  closeAllOcultos(); // Cierra todos los divs ocultos visibles

  if (!isAlreadyOpen) {
    divs[0].classList.add("show");
    oculto.classList.add("show");
    oculto.style.height = alturaVisible + "vh";
    oculto.style.opacity = "1";
  }
}

// Cierra todos los divs ocultos visibles
function closeAllOcultos() {
  var ocultos = document.getElementsByClassName("oculto");
  for (var i = 0; i < ocultos.length; i++) {
    if (ocultos[i].classList.contains("show")) {
      ocultos[i].classList.remove("show");
      var divs = ocultos[i].getElementsByTagName("div");
      for (var j = 0; j < divs.length; j++) {
        divs[j].classList.remove("show");
      }
      ocultos[i].style.height = "0px";
      ocultos[i].style.opacity = "0";
    }
  }
}



//multislide
function navigateSlider(sliderId, direction) {
  const slider = document.getElementById(sliderId);
  const sliderContent = slider.querySelector('.multislider-content');
  const slides = slider.querySelectorAll('.multislide');
  const totalSlides = slides.length;
  let currentIndex = parseInt(slider.getAttribute('data-current-index') || 0);

  // Actualizar el índice actual
  currentIndex += direction;

  // Asegurarse de que el índice esté dentro de los límites
  if (currentIndex < 0) {
    currentIndex = totalSlides - 1;
  } else if (currentIndex >= totalSlides) {
    currentIndex = 0;
  }

  // Guardar el nuevo índice en el atributo de datos
  slider.setAttribute('data-current-index', currentIndex);

  // Calcular y aplicar el desplazamiento
  const offset = -currentIndex * 100;
  sliderContent.style.transform = `translateX(${offset}%)`;
}

// Iniciar el deslizamiento automático para todos los sliders
const sliderContainers = document.querySelectorAll('.multislider-container');
sliderContainers.forEach((slider) => {
  setInterval(() => {
    navigateSlider(slider.id, 1);
  }, 5000); // Cambia cada 5 segundos
});



document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const cursoSeleccionado = urlParams.get("cursoSeleccionado");

    if (cursoSeleccionado) {
        // Encuentra el div oculto correspondiente y activa toggleDiv
        const divOcultoId = "oculto" + cursoSeleccionado;
        const alturaVisible = 160; // Altura ajustable
        toggleDiv(0, divOcultoId, alturaVisible); // El índice ahora siempre es 0
    }
});


</script>