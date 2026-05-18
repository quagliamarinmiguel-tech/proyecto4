<!--?php
require_once "../config/APP.php";
require_once "autoloadcurso.php";

$termino = trim($_POST['busqueda'] ?? '');
$termino = mainModel::limpiar_cadena($termino);

$conexion = mainModel::conectar();
$query = $conexion->prepare("
    SELECT 
        m.id_materia,
        c.nombre_curso,
        c.tipo_curso
    FROM curso_publicidad m
    LEFT JOIN curso c ON m.id_curso = c.id_curso
    WHERE c.nombre_curso LIKE :term and estado_curso= 1
    ORDER BY c.nombre_curso ASC
    LIMIT 10
");


//SELECT m.id_materia, c.id_curso, c.nombre_curso, c.tipo_curso FROM curso_publicidad m LEFT JOIN curso c ON m.id_curso = c.id_curso LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte WHERE co.estado_cohorte = 'Vigente'

$query->execute([":term" => "%$termino%"]);

while ($row = $query->fetch()) {
    $nombre = htmlspecialchars($row["nombre_curso"])." (".htmlspecialchars($row["tipo_curso"]).")";
    echo '<div class="resultados_busqueda_nav" data-id="'.$row["id_materia"].'" data-nombre="'.$nombre.'">'.$nombre.'</div>';

}




if ($query->rowCount() === 0) {
    echo '<div class="list-group-item text-muted disabled">Sin coincidencias</div>';
}
?-->

<!--
        SELECT 
        m.id_materia,
        c.id_curso,
        c.nombre_curso,
        c.tipo_curso
    FROM curso_publicidad m
    LEFT JOIN curso c ON m.id_curso = c.id_curso
    LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
    WHERE co.estado_cohorte = 'Vigente'
    AND c.nombre_curso LIKE :term AND c.estado_curso = 1 AND co.archivar_cohorte = 1
    ORDER BY c.nombre_curso ASC
    LIMIT 10
    
    -->

    
    
    
    

<?php
require_once "../config/APP.php";
require_once "autoloadcurso.php";

$termino = trim($_POST['busqueda'] ?? '');
$termino = mainModel::limpiar_cadena($termino);

$conexion = mainModel::conectar();
$query = $conexion->prepare("    
        
SELECT 
    m.id_materia,
    c.id_curso,
    c.nombre_curso,
    c.tipo_curso,
    
    -- Para saber si es Vigente o Próximo
    CASE 
        WHEN vig_co.id_cohorte IS NOT NULL OR vig_pi.id_periodo_intensivo IS NOT NULL THEN 'Vigente'
        WHEN prox_co.id_cohorte IS NOT NULL OR prox_pi.id_periodo_intensivo IS NOT NULL THEN 'Próximo'
        ELSE 'N/A'
    END AS estado_actual

FROM curso_publicidad m
LEFT JOIN curso c ON m.id_curso = c.id_curso
LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk

-- ========== VIGENTES ==========
-- Cohortes VIGENTES
LEFT JOIN cohorte vig_co ON cv.id_cohorte_fk = vig_co.id_cohorte 
    AND vig_co.estado_cohorte = 'Vigente' 
    AND vig_co.archivar_cohorte = 1

-- Periodos Intensivos VIGENTES  
LEFT JOIN periodo_intensivo vig_pi ON cv.id_curso_vigente = vig_pi.id_curso_vigente_fk 
    AND vig_pi.estado_periodo = 'Vigente'

-- ========== PRÓXIMOS ==========
-- Cohortes PRÓXIMOS (más cercano)
LEFT JOIN (
    SELECT 
        cvp.id_curso_fk,
        cop.id_cohorte,
        cop.fecha_inicio,
        ABS(DATEDIFF(CURDATE(), cop.fecha_inicio)) as dias_diferencia
    FROM curso_vigente cvp
    INNER JOIN cohorte cop ON cvp.id_cohorte_fk = cop.id_cohorte
    WHERE cop.estado_cohorte = 'Próximo' 
    ORDER BY dias_diferencia ASC
) prox_co ON prox_co.id_curso_fk = c.id_curso

-- Periodos Intensivos PRÓXIMOS (más cercano)
LEFT JOIN (
    SELECT 
        pcv.id_curso_fk,
        pcp.id_periodo_intensivo,
        pcp.fecha_inicio_int,
        ABS(DATEDIFF(CURDATE(), pcp.fecha_inicio_int)) as dias_diferencia
    FROM periodo_intensivo pcp
    INNER JOIN curso_vigente pcv ON pcp.id_curso_vigente_fk = pcv.id_curso_vigente
    WHERE pcp.estado_periodo = 'Próximo'
    ORDER BY dias_diferencia ASC
) prox_pi ON prox_pi.id_curso_fk = c.id_curso

WHERE c.nombre_curso LIKE :term
    AND c.estado_curso = 1
    AND (
        vig_co.id_cohorte IS NOT NULL 
        OR vig_pi.id_periodo_intensivo IS NOT NULL
        OR prox_co.id_cohorte IS NOT NULL
        OR prox_pi.id_periodo_intensivo IS NOT NULL
    )

GROUP BY c.id_curso
ORDER BY 
    -- Priorizar Vigentes primero
    CASE 
        WHEN vig_co.id_cohorte IS NOT NULL OR vig_pi.id_periodo_intensivo IS NOT NULL THEN 1
        ELSE 2
    END ASC,
    -- Luego por proximidad de fecha (solo Próximos)
    CASE 
        WHEN vig_co.id_cohorte IS NOT NULL OR vig_pi.id_periodo_intensivo IS NOT NULL THEN 0
        ELSE COALESCE(prox_co.dias_diferencia, prox_pi.dias_diferencia, 9999)
    END ASC,
    c.nombre_curso ASC
LIMIT 10    
    
    
    
    


");


/*
    SELECT 
        m.id_materia,
        c.id_curso,
        c.nombre_curso,
        c.tipo_curso
    FROM curso_publicidad m
    LEFT JOIN curso c ON m.id_curso = c.id_curso
    LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
    LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk

    WHERE (
        (co.estado_cohorte = 'Vigente' AND co.archivar_cohorte = 1)
        OR
        (cv.id_cohorte_fk IS NULL AND pi.estado_periodo = 'Vigente')
    )
    AND c.nombre_curso LIKE :term
    AND c.estado_curso = 1

    ORDER BY c.nombre_curso ASC
    LIMIT 10
    
*/



$query->execute([":term" => "%$termino%"]);

while ($row = $query->fetch()) {
    $nombre = htmlspecialchars($row["nombre_curso"])." (".htmlspecialchars($row["tipo_curso"]).")";
    echo '<div class=" resultados_busqueda_nav item-fk" style="font-size: 3rem;color: black;background-color: #fff;padding: 8px;cursor: pointer;opacity:1;" data-id="'.$row["id_materia"].'" data-nombre="'.$nombre.'">'.$nombre.'</div>';

//ecgho '<div class="resultados_busqueda_nav item-fk" 
//           style="font-size:6rem; color:#000; background:#fff; padding:8px;" 
  //         data-id="'.$row["id_materia"].'" 
    //       data-nombre="'.htmlspecialchars($row["nombre_curso"]).'">';

//echo '<p style="margin:0;color:black;font-size:5rem;">' . htmlspecialchars($row["nombre_curso"]) . ' - ' . htmlspecialchars($row["tipo_curso"]) . '</p>';
//echo '</div>';
}
//list-group-item item-fk
if ($query->rowCount() === 0) {
    echo '<div class="list-group-item text-muted disabled">Sin coincidencias</div>';
}
?>


