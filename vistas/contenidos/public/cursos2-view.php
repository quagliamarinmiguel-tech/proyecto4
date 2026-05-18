

<?php
//require_once "../../modelos/mainModel.php"; // Ruta ajustada desde vistas/contenidos/public
require_once __DIR__ . "/../../../modelos/mainModel.php";
$cursosPorPagina = 20;



require_once "./controladores/publicoControlador.php";
$inicioCtrl = new publicoControlador();
$datosInicio = $inicioCtrl->cargar_inicio_controlador();
//$cursos = $datosInicio["curso"];
//$profesores = $datosInicio["profesores"];
//$cursos = $datosInicio["curso"];
//$imgn1 = $datosInicio["slider"]["slider_1"];
//$imgn2 = $datosInicio["slider"]["slider_2"];
//$imgn3 = $datosInicio["slider"]["slider_3"];
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


$condiciones = "
    co.estado_cohorte = 'Vigente'
    AND cv.estado_curso_vigente = 1
    AND p.estado_profesor = 1
    AND c.estado_curso = 1
    AND s.archivar_seccion = 1
    AND m.estado_curso_publicidad = 1
    AND s.nombre_seccion = 'A'
";

$params = [];

if ($tipoFiltro) {
    $condiciones .= " AND c.tipo_curso = :tipo";
    $params[':tipo'] = $tipoFiltro;
}

if ($idFiltro) {
    $condiciones .= " AND c.id_curso = :id";
    $params[':id'] = $idFiltro;
}






try {
    $pdo = mainModel::conectar();

    


    $sql="

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

        cv.id_curso_vigente,
        co.pago_inscripcion,
        co.pago_modulo,
        co.fecha_inicio,
        co.fecha_fin,
        co.numero_cohorte,
        co.anio_cohorte,

        co.id_cohorte,
        co.tipo_cohorte,

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
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
    LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk AND s.nombre_seccion = 'A' AND s.archivar_seccion = 1
    LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor AND p.estado_profesor = 1
    LEFT JOIN imagen im_p ON p.imagen_profesor_fk = im_p.id_imagen

    WHERE $condiciones
    ORDER BY m.id_materia ASC
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

//stmt viejos pero 100% funcionales    $stmt = $pdo->prepare($sql);
  //  $stmt->bindValue(':limite', $cursosPorPagina, PDO::PARAM_INT);
    //$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    //$stmt->execute();
    //$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //$sqlTotal = "SELECT COUNT(*) as total FROM curso_publicidad";
    $sqlTotal = "

    SELECT COUNT(DISTINCT m.id_materia) AS total
    FROM curso_publicidad m
    LEFT JOIN curso c ON m.id_curso = c.id_curso
    LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
    LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk 
    LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor 
    WHERE $condiciones


";

  //  $resultado = $pdo->query($sqlTotal)->fetch(PDO::FETCH_ASSOC);
//    $totalCursos = intval($resultado['total'] ?? 0);

    $stmtTotal = $pdo->prepare($sqlTotal);
    foreach ($params as $key => $value) {
        $stmtTotal->bindValue($key, $value);
    }
    $stmtTotal->execute();
    $totalCursos = intval($stmtTotal->fetchColumn());



} catch (PDOException $e) {
    error_log("Error al obtener cursos paginados: " . $e->getMessage());
    $cursos = [];
    $totalCursos = 0;
}
?>











<style>
/* Contenedor del formulario */
.filtro-formulario {
  max-height: 15vh;
  max-width: 80%;
  margin: 30px auto;
  padding: 20px;
  background-color: #f2f2f2;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
option{font-size:4rem;}
/* Título opcional */
.filtro-formulario h3 {
  text-align: center;
  margin-bottom: 20px;
  font-size: 3rem;
  color: #333;
}

/* Distribución horizontal */
.filtro-grupo {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: space-between;
}

/* Estilo de cada campo */
.filtro-campo {
  flex: 1 1 45%;
  display: flex;
  flex-direction: column;
}

/* Etiquetas */
.filtro-campo label {
  margin-bottom: 6px;
  font-weight: bold;
  color: #444;
}

/* Selects */
.filtro-campo select {
  padding: 8px 10px;
  font-size: 5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: #fff;
  transition: border-color 0.3s;
}

.filtro-campo select:focus {
  border-color: #007bff;
  outline: none;
}

/* Botón de búsqueda */
.filtro-formulario .btn-filtrar {
  margin-top: 20px;
  padding: 10px 20px;
  background-color: #007bff;
  color: #fff;
  font-weight: bold;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: block;
  width: 100%;
  transition: background-color 0.3s;
}

.filtro-formulario .btn-filtrar:hover {
  background-color: #0056b3;
}







/*aqui empieza periodico y enlaces*/
.espacio{width:100%;display:flex;align-items:center;justify-content:center;}
.periodicou{width:80%;height:65vh;display:flex;align-items:center;justify-content:center;background-color:white;}
.quien{width:28%;height:65vh;margin-left:10px;margin-right:10px;display:flex;justify-content:center;align-items:center;flex-direction:column;box-shadow: rgba(0, 0, 0, 0.45) 0px 25px 20px -20px;background-color:#f2f3f4;border-top: 1px solid #0CF;}
.quien p{font-size:4.3rem;color:black;padding:5px 10px;}
.quien p > b{color:#6FF;font-size:6rem;}
.quien img{width:100%;height:40%;margin-top:5px;margin-bottom:3px;}
.quien2{width:25%;display:flex;height:65vh;justify-content:center;margin-left:10px;margin-right:10px;align-items:center;flex-direction:column;box-shadow: rgba(0, 0, 0, 0.45) 0px 25px 20px -20px;background-color:#f2f3f4;}
.quien2 p{font-size:4.5rem;color:black;padding:5px 10px;}
.quien2 p > b{font-size:6rem;}

.pebee1{font-size:10rem;text-align:center;}
.pebee2{font-size:6rem;text-align:center;}
.pebee3{font-size:4rem;text-align:center;}

.car{transition:all 400ms ease;}
.car:hover{box-shadow:5px 5px 20px rgb(0,0,0,0.4);transform:translateY(-3%);cursor:pointer;}

article {
    position: absolute;
    margin-top: 10%;
    opacity: 0;
    transition: opacity 0.5s ease;
}
body.active .visiblez{color:white;}
.visiblez {opacity: 1;margin-top:12%;}
.visiblez {font-size:6rem;}
.hiddenz {display: none;}

.enlaces{width:100%;height:30vh;display:flex;justify-content:center;align-items:center;/*background-color:#f2f4f4fad5d1*/}

.anima{display:flex;justify-content:center; align-items:center;width:14%;margin-left:1%;margin-right:1%;border-radius:60px;height:30%/*90px*/;box-shadow: rgba(14, 30, 37, 0.12) 0px 2px 4px 0px, rgba(14, 30, 37, 0.32) 0px 2px 16px 0px;}
.anima img{width:50%;height:50%;object-fit:cover;}

.medio{display:flex;justify-content:center; align-items:center;width:14%;margin-left:1%;margin-right:1%;flex-direction:column;height:30%;transition:width 0.5s,height 0.5s;}
.medio:hover{width:15%;height:31%;}

.aa1{background-color:#36F;transition: background-color 0.5s,width 0.5s,height 0.5s;}
.aa1:hover{background-color:#33F;cursor:pointer;width:15%;height:31%;}
.aa2{background-color:#F9F;transition: background-color 0.5s,width 0.5s,height 0.5s;}
.aa2:hover{background-color:#F0F;cursor:pointer;width:15%;height:31%;}
.aa3{background-color:#C00;transition: background-color 0.5s,width 0.5s,height 0.5s;}
.aa3:hover{background-color:#F03;cursor:pointer;width:15%;height:31%;}
.aa4{background-color:#0C0;transition: background-color 0.5s,width 0.5s,height 0.5s;}
.aa4:hover{background-color:#02e002;cursor:pointer;width:100%;height:31%;}
.aa5{background-color:#66C;transition: background-color 0.5s,width 0.5s,height 0.5s;}
.aa5:hover{background-color:#6CF;cursor:pointer;width:100%;height:31%;}


.mapas{width:60%;margin-left:4%;margin-right:4%;height:100%/*400px*/;display:flex;justify-content:center;align-items:center;flex-wrap:nowrap;}
.mapasgoogle{width:35%;/*height:auto;*/height:80%;}
.mapasgoogle > a{width:100%;height:100%;background-color:yellow;}
.revoluciondelainformatica{border-radius:20px;cursor:pointer;transition:all 0.3s ease-in-out;object-fit:contain;}
.revoluciondelainformatica:hover{box-shadow: rgba(244, 67, 54, 0.3) 0px 19px 38px, rgba(244, 67, 54, 0.22) 0px 15px 12px;}

.mapasfotos{width:30%;height:90%;margin-right:1%;margin-left:1%;display:flex;flex-direction:column;justify-content:center;align-items:center;}
.mapasfotos img{width:98%;height:40%;border-radius:8px;box-shadow: rgba(0, 0, 0, 0.25) 0px 14px 28px, rgba(0, 0, 0, 0.22) 0px 10px 10px;}
.mapastexto{display:flex;align-items:center;justify-content:center;flex-direction:column;margin-bottom:5%;box-shadow: rgba(0, 0, 0, 0.25) 0px 14px 28px, rgba(0, 0, 0, 0.22) 0px 10px 10px;padding:12px;  background-color:#e74c3c;border-radius:6px;width:95%;}
.mapastexto p{display:flex;align-items:center;color:white;font-size:5rem;justify-content:center;padding:5px;}
.mapastexto h3{display:flex;align-items:center;color:white;font-size:7rem;}

.ocultocontactos {
  height: 0px;
  width: 100%;
  display: flex;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.5s ease, height 0.5s ease;
  flex-direction: row;
}
.ocultocontactos div {
  display: none;
}
.ocultocontactos.show {
  height:155vh;
  opacity: 1;
}
.ocultocontactos div.show {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
}
</style>














<div class="absoluto seg2">

<!--
    SELECT COUNT(*) as total
    FROM curso_publicidad m
    LEFT JOIN curso c ON m.id_curso = c.id_curso
    LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
    WHERE co.estado_cohorte = 'Vigente'
consulta count que no contaba todo lo desarchivado



no wxcluyw sduplicados
    SELECT COUNT(*) AS total
    FROM curso_publicidad m
    LEFT JOIN curso c ON m.id_curso = c.id_curso
    LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
    LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk
    LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
    WHERE $condiciones

    WHERE
     co.estado_cohorte = 'Vigente'
    AND cv.estado_curso_vigente = 1
    AND p.estado_profesor = 1
    AND c.estado_curso = 1
    AND s.archivar_seccion = 1
    AND m.estado_curso_publicidad = 1








    consulta con duplicados

    
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

        cv.id_curso_vigente,
        co.pago_inscripcion,
        co.pago_modulo,
        cv.oferta AS oferta_vigente,

        co.id_cohorte,
        co.tipo_cohorte,

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
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
    LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk
    LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
    LEFT JOIN imagen im_p ON p.imagen_profesor_fk = im_p.id_imagen

    WHERE $condiciones

    ORDER BY m.id_materia ASC
    LIMIT :limite OFFSET :offset

-->


	<div style="cursor:pointer;" class="loguito " onclick="window.location.href='<?php echo SERVERURL; ?>index.php', '_blank';"><h3><b>Ceproca</b></h3><a href="index.php"><img src="../vistas/assets/img/logoceprocahd1.webp" alt="porqueno"></a></div><!--loguito-->
   
    <div class="contactosesion">
        	<ul>
            <!--/li-->
            <li><a href="<?php echo SERVERURL; ?>index.php?views=cursos">Cursos</a>
            </li>
            <li>
                     
            	<a style="cursor:context-menu;"  >Contáctanos</a>
                
            	<ul class="dropdown">
            		<li><a href="<?php echo htmlspecialchars($info['link_facebook']); ?>">Facebook</a></li>
            		<li><a href="<?php echo htmlspecialchars($info['link_instagram']); ?>">Instagram</a></li>
            		<li> <div class="precaucion " >
                            <a href="https://wa.me/<?php echo htmlspecialchars($info['telefono_contacto']); ?>">Teléfono</a><!--p id="telefonoText">Teléfono</p-->
                        </div></li>


                    <li><div class="precaucion " >
                            <a href="mailto:<?php echo htmlspecialchars($info['correo_contacto']); ?>?subject=Asunto%20predefinido&body=Hola,%20quiero%20contactarte">Correo</a><!--p id="correoText">Correo</p-->
                            <!--a href="mailto:<php echo htmlspecialchars($infoceproca[1]); ?>">Correo</a-->
                        </div></li>
                </ul>
            </li>
            <li><a href="<?php echo SERVERURL; ?>index.php?views=login">Iniciar sesión</a>
            </li>
            <li><a href="<?php echo SERVERURL; ?>solicitud-inscripcion">Regístrate</a>

            <li class="switch">
                    <i class="bx bxs-sun" ></i>
                	<i class="bx bxs-moon"></i>
            </li>           
            </ul>
    </div><!--contactosesion-->
</div><!--absoluto-->




<div style="height:5vh;"></div>

<div class="nueva2" 	style="background-image: url('../vistas/assets/img/graduacion3.webp');">
    <h1 >Nuestros Cursos</h1>
<p>¿Estás buscando mejorar tus habilidades y abrir nuevas puertas en tu carrera? CEPROCA es el lugar perfecto para ti. Nuestros cursos de alta calidad te brindarán las herramientas y conocimientos necesarios para alcanzar tus metas.</p>
</div><!--nueva2-->










<div class="nada">
<div style="height:10vh;"></div>
<h4 style="text-align:center;font-size:12rem;width:90%;margin:auto;">"Aprovecha cada oportunidad para aprender algo nuevo y especialízate en lo que amas."</h4>
<br><br><br><br><br>
</div><!--colorgris-->

<div style="height:5vh;"></div>

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






<!--form method="GET" action="index.php" class="filtro-formulario">
  <h3>Filtrar cursos</h3>
  <div class="filtro-grupo">
    <div class="filtro-campo">
      <!--label for="filtro_nombre">Curso</label->
      <select name="filtro_nombre" id="filtro_nombre" class="select2" style="font-size:4rem;">
        <option style="border-radius:0px;border:1px black; font-size: 4rem;" value="">Todos los cursos</option>
        <!-- Opciones dinámicas aquí ->
      </select>
    </div->
    <div class="filtro-campo">
      <!--label for="filtro_tipo">Tipo</label->
      <select name="filtro_tipo" id="filtro_tipo">
        <option value="">Todos los tipos</option>
        <option value="Intensivo">Intensivo</option>
        <option value="Extendido">Extendido</option>
        <option value="Técnico Profesional">Técnico Profesional</option>
      </select>
    </div>
  </div>
  <button type="submit" class="btn-filtrar">Buscar cursos</button>
</form-->







<div class="kursos">
    <?php for ($index = 0; $index < count($cursos); $index += 2): ?>
        <!-- Primer curso del par -->
    <div class="tarjetacurso3 car" id="<?php echo htmlspecialchars($cursos[$index]['id_materia']); ?>" onclick="toggleDiv(<?php echo $index ?>, 'oculto<?php echo htmlspecialchars($cursos[$index]['id_materia']); ?>', 160)">
        <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($cursos[$index]['ruta_materia']); ?>" />
        <div class="subtarjetacurso3">
            <p><?php echo htmlspecialchars($cursos[$index]['prefacio']); ?></p>
               <?php if (($cursos[$index]['profe_mostrar']) == 1): ?>        
                <section class="datosprofe" style="margin-left:3%;width:95%;height:30%;">
                    <img loading="lazy" style="width:40%;height:65%;border-radius:50%;" src="../vistas/<?php echo htmlspecialchars($cursos[$index]['ruta_profesor']); ?>" />
                    <p><b>Prof. <?php echo htmlspecialchars($cursos[$index]['nombre_profesor'] . ' ' . $cursos[$index]['apellido_profesor']); ?></b></p>
                </section>   
			   <?php endif; ?>
            <div class="info_precio_tarjeta3">        
                <div class="precio_n_tarjeta3">

                    <?php
                        $inscripcion = $cursos[$index]['pago_inscripcion'];
                        $modulo      = $cursos[$index]['pago_modulo'];



                        $total = $inscripcion + ($modulo * 3);
                    ?>
                    <p style="font-size:5rem;">Total: <?php echo number_format($total, 2, ',', '.'); ?>$</p>

                    <!--h1><php echo htmlspecialchars($cursos[$index]['pago_inscripcion']); ?>$</h1-->
                    <i class='bx bxs-badge-dollar'></i>
                </div>

            </div>
        </div>
   </div><!--tarhetacurso3-->


        <?php if ($index + 1 < count($cursos)): ?>
            <!-- Segundo curso del par -->
    <div class="tarjetacurso3 car" id="<?php echo htmlspecialchars($cursos[$index + 1]['id_materia']); ?>" onclick="toggleDiv(<?php echo $index + 1 ?>, 'oculto<?php echo htmlspecialchars($cursos[$index + 1]['id_materia']); ?>', 160)">
        <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($cursos[$index + 1]['ruta_materia']); ?>" />
        <div class="subtarjetacurso3">
            <p><?php echo htmlspecialchars($cursos[$index + 1]['prefacio']); ?></p>
               <?php if (($cursos[$index + 1]['profe_mostrar']) == 1): ?>        
                <section class="datosprofe" style="margin-left:3%;width:95%;height:30%;">
                    <img loading="lazy" style="width:40%;height:65%;border-radius:50%;" src="../vistas/<?php echo htmlspecialchars($cursos[$index + 1]['ruta_profesor']); ?>" />
                    <p><b>Prof. <?php echo htmlspecialchars($cursos[$index + 1]['nombre_profesor'] . ' ' . $cursos[$index + 1]['apellido_profesor']); ?></b></p>
                </section>   
			   <?php endif; ?>
            <div class="info_precio_tarjeta3">        
                <div class="precio_n_tarjeta3">
                    <?php
                        $inscripcion = $cursos[$index + 1]['pago_inscripcion'];
                        $modulo      = $cursos[$index + 1]['pago_modulo'];


                        $total = $inscripcion + ($modulo * 3);
                    ?>
                    <p style="font-size:5rem;">Total: <?php echo number_format($total, 2, ',', '.'); ?>$</p>

                    <!--h1><php echo htmlspecialchars($cursos[$index]['pago_inscripcion']); ?>$</h1-->
                    <i class='bx bxs-badge-dollar'></i>
                </div>

            </div>
        </div>
   </div><!--tarhetacurso3-->
        <?php endif; ?>




            <div class="oculto" id="oculto<?php echo htmlspecialchars($cursos[$index]['id_materia']); ?>">
            <!--div class="registroCurso" id="registro<?php echo htmlspecialchars($cursos[$index]['id_materia']); ?>" data-estado="0" style="display:none;"></div-->
            <div style="background-color:white; height: auto;">
                <section class="datos">
                <section class="lado1">
                    <section class="general">
                        <p style="font-size:7rem;"><?php echo htmlspecialchars($cursos[$index]['materia_descripcion']); ?></p>
                        
               <?php if (($cursos[$index]['profe_mostrar']) == 1): ?>        
                <section class="datosprofe">
                    <img src="../vistas/<?php echo htmlspecialchars($cursos[$index]['ruta_profesor']); ?>" />
                    <p><b>Prof. <?php echo htmlspecialchars($cursos[$index]['nombre_profesor'] . ' ' . $cursos[$index]['apellido_profesor']); ?></b></p>
                    <p><?php echo htmlspecialchars($cursos[$index]['descripcion']); ?></p>
                </section>   
			   <?php endif; ?>
                
                    </section><!--general-->

                
<section class="checklisst">
    <h1>Aquí aprenderás:</h1>

    <section class="parachecklisst" style="align-items:center;justify-content:center;">  
        

        <section class="subchecklisst" style="width:100%;flex-wrap:wrap;flex-direction:row;justify-content:center;">
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <?php if (!is_null($cursos[$index]['cont' . $i]) && $cursos[$index]['cont' . $i] !== ''): ?>
                    <section class="checking" style="width:40%;"><i class='bx bx-check-square'></i><p><?php echo htmlspecialchars($cursos[$index]['cont' . $i]); ?></p></section><!--checking-->
                <?php endif; ?>
            <?php endfor; ?>
        </section><!--subchecklisst-->


    </section><!--parachecklisst-->
</section><!--checklisst-->
            
                
                <section class="inclusion">
    <h1>Aquí obtendrás:</h1>
    <section class="inclusionforzada">



            <section class="logros">
                <section class="logrospng b4"><i class='bx bx-bookmark-alt-minus'></i></section><!--logrospng-->
                <p>Obtén un certificado</p>
            </section><!--logros-->

        <?php if ($cursos[$index]['icono1'] == 1): ?>
            <section class="logros">
                <section class="logrospng b1"><i class='bx bx-book-open'></i></section><!--logrospng-->
                <p>Técnico Profesional</p>
            </section><!--logros-->
        <?php endif; ?>
        <?php if ($cursos[$index]['icono2'] == 1): ?>
            <section class="logros">
                <section class="logrospng b2"><i class='bx bx-buildings'></i></section><!--logrospng-->
                <p>Colaboraciones con negocios y empresas</p>
            </section><!--logros-->
        <?php endif; ?>
        <?php if ($cursos[$index]['icono3'] == 1): ?>
            <section class="logros">
                <section class="logrospng b3"><i class='bx bxs-wrench'></i></section><!--logrospng-->
                <p>Prácticas y evaluaciones dentro y fuera de la institución</p>
            </section><!--logros-->
        <?php endif; ?>
    </section><!--inclusionforzada-->
</section><!--inclusion-->
                </section><!--lado1-->

                <section class="lado2">
    <!--section class="lado2png"><img loading="lazy" src="<php echo htmlspecialchars($cursos[$index]['imagen_materia']); ?>" /></section><!--lado2png-->

    <section class="otrascosas">
    
		<?php if ($cursos[$index]['tipo_curso'] === 'Intensivo'): ?> 
        	<h1 class="duracion_curso_h">Curso de 6 encuentros</h1> 
		<?php elseif ($cursos[$index]['tipo_curso'] === 'Extendido'): ?> 
        	<h1 class="duracion_curso_h">Curso de 3 meses de duración</h1> 
		<?php endif; ?>
        <!--ea49ff-->
        <br>

            <h1 class="nombre_curso_h"><?php echo htmlspecialchars($cursos[$index]['nombre_curso']); ?></h1><br><br> 

        <hr>
        
                    <section class="otrascosas1">
                        <h1>Requisitos:</h1>
                        <?php if (!empty($cursos[$index]['requisito1'])): ?>
                            <p>◦ <?php echo htmlspecialchars($cursos[$index]['requisito1']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($cursos[$index]['requisito2'])): ?>
                            <p>◦ <?php echo htmlspecialchars($cursos[$index]['requisito2']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($cursos[$index]['requisito3'])): ?>
                            <p>◦ <?php echo htmlspecialchars($cursos[$index]['requisito3']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($cursos[$index]['requisito4'])): ?>
                            <p>◦ <?php echo htmlspecialchars($cursos[$index]['requisito4']); ?></p>
                        <?php endif; ?>
                        <br><br><hr>
                    </section><!--otrascosas1-->

        <!--section class="otrascosas2"-->
            <section class="otrascosas21p" style="align-items:flex-start;width:95%;">
                <section class="tengoclase"><i class='bx bx-money' style="color:#6C6;font-size:6rem;"></i><h1 style="font-size:6rem;">Costos</h1></section><!--tengoclase-->
                <p style="font-size:5rem;">Inscripción: <?php echo htmlspecialchars($cursos[$index]['pago_inscripcion']); ?>$</p>
                <p style="font-size:5rem;">Cuotas: <?php echo htmlspecialchars($cursos[$index]['pago_modulo']); ?>$</p>
                
                
                
                
                <!--php
                    $total = $cursos[$index]['pago_inscripcion'] + ($cursos[$index]['pago_modulo'] * 3);
                >
                <p style="font-size:5rem;">Total: <?php echo number_format($total, 2, ',', '.'); ?>$</p-->

                    <?php
                        $inscripcion = $cursos[$index]['pago_inscripcion'];
                        $modulo      = $cursos[$index]['pago_modulo'];
                        $oferta      = $cursos[$index]['oferta_vigente'];

                        if(!is_null($oferta) && $oferta > 0){
                            $modulo = $modulo * ((100 - $oferta) / 100); 
                        }

                        $total = $inscripcion + ($modulo * 3);
                    ?>
                    <p style="font-size:5rem;">Total: <?php echo number_format($total, 2, ',', '.'); ?>$</p>

                <?php if(!is_null($oferta) && $oferta > 0): ?>
                        <p style="font-size:5rem;"><s><?php echo htmlspecialchars($cursos[$index]['oferta_vigente']); ?>%</s></p>
			   <?php endif; ?>


            </section><!--otrascosas21p-->

            <section class="otrascosas21h" style="align-items:flex-start;width:95%;">
                <section class="tengoclase"><i class='bx bx-time-five' style="color:#f63;font-size:6rem;"></i><h1 style="font-size:6rem;">Horarios</h1></section><!--tengoclase-->
                <p style="font-size:5rem;"><?php echo htmlspecialchars($cursos[$index]['clase']); ?></p>
            </section><!--otrascosas21h-->
        <!--/section><--otrascosas2-->      

        <hr/><br>
             <section class="llamanos" style="height:15%;">
            	<?php if ($cursos[$index]['cupos'] == 1): ?> 
                		<p class='llamanos111'>Cupos disponibles</p>
				<?php else: ?> 
                	<p class='llamanos111' style="background-color:red;">No hay cupos disponibles</p> 
				<?php endif; ?> 


                <!--a href="<?php echo SERVERURL; ?>solicitud-inscripcion?id=<?php echo htmlspecialchars($cursos[$index]['id_curso']); ?>"
                class="btn btn-sm"
                style="margin:10px auto; width:90%; background-color:#3CA6A6; color:white; padding:8px; border-radius:8px; text-decoration:none;font-size:4rem;box-shadow:rgba(0, 0, 0, 0.16) 0px 3px 6px, rgba(0, 0, 0, 0.23) 0px 3px 6px;">
                Solicitar inscripción
                </a-->

                <!--a href="#" onclick="window.location.href='<?= SERVERURL ?>solicitud-inscripcion?id=<?= htmlspecialchars($cursos[$index]['id_curso']) ?>'; return false;"
                class="btn btn-sm"
                style="margin:10px auto; width:90%; background-color:#3CA6A6; color:white; padding:8px; border-radius:8px; text-decoration:none;font-size:4rem;box-shadow:rgba(0, 0, 0, 0.16) 0px 3px 6px, rgba(0, 0, 0, 0.23) 0px 3px 6px;">
                Solicitar inscripción
                </a-->

                <a href="#"
                class="btn btn-sm"
                style="margin:10px auto; width:90%; background-color:#3CA6A6; color:white; padding:8px; border-radius:8px; text-decoration:none;font-size:4rem;box-shadow:rgba(0, 0, 0, 0.16) 0px 3px 6px, rgba(0, 0, 0, 0.23) 0px 3px 6px;"
                onclick="window.location.href='<?= SERVERURL ?>index.php?views=solicitud-inscripcion&id=<?= htmlspecialchars($cursos[$index]['id_curso']) ?>'; return false;">
                Solicitar inscripción
                </a>
                


				<!--php if ($cursos[$index]['oferta'] !== ''): ?> 
                	<p class='llamanos222'><php echo htmlspecialchars($cursos[$index]['oferta']); ?></p> 
				<php endif; ?-->
                <br>
        </section><!--llamanos-->
    </section><!--otrascosas-->
</section><!--lado2-->
</section><!--datos-->
            </div>
        </div>








        <?php if ($index + 1 < count($cursos)): ?>
            <div class="oculto" id="oculto<?php echo htmlspecialchars($cursos[$index+1]['id_materia']); ?>">
            <!--div class="registroCurso" id="registro<?php echo htmlspecialchars($cursos[$index + 1]['id_materia']); ?>" data-estado="0" style="display:none;"></div-->
                <div style="background-color:white; height: auto;">
                                    <section class="datos">
                <section class="lado1">
                    <section class="general">
                        <p style="font-size:7rem;"><?php echo htmlspecialchars($cursos[$index + 1]['materia_descripcion']); ?></p>
                        
               <?php if (($cursos[$index + 1]['profe_mostrar']) == 1): ?>        
                <section class="datosprofe">
                    <img src="../vistas/<?php echo htmlspecialchars($cursos[$index + 1]['ruta_profesor']); ?>" />
                    <p><b>Prof. <?php echo htmlspecialchars($cursos[$index + 1]['nombre_profesor'] . ' ' . $cursos[$index + 1]['apellido_profesor']); ?></b></p>
                    <p><?php echo htmlspecialchars($cursos[$index + 1]['descripcion']); ?></p>
                </section>   
			   <?php endif; ?>
                
                    </section><!--general-->

                
<section class="checklisst">
    <h1>Aquí aprenderás:</h1>

    <section class="parachecklisst" style="align-items:center;justify-content:center;">  
        
        <section class="subchecklisst" style="width:100%;flex-wrap:wrap;flex-direction:row;justify-content:center;">
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <?php if (!is_null($cursos[$index + 1]['cont' . $i]) && $cursos[$index + 1]['cont' . $i] !== ''): ?>
                    <section class="checking" style="width:40%;"><i class='bx bx-check-square'></i><p><?php echo htmlspecialchars($cursos[$index + 1]['cont' . $i]); ?></p></section><!--checking-->
                <?php endif; ?>
            <?php endfor; ?>
        </section><!--subchecklisst-->

    </section><!--parachecklisst-->
</section><!--checklisst-->
            
                
                <section class="inclusion">
    <h1>Aquí obtendrás:</h1>

    <section class="inclusionforzada">

            <section class="logros">
                <section class="logrospng b4"><i class='bx bx-bookmark-alt-minus'></i></section><!--logrospng-->
                <p>Obtén un certificado</p>
            </section><!--logros-->

        <?php if ($cursos[$index + 1]['icono1'] == 1): ?>
            <section class="logros">
                <section class="logrospng b1"><i class='bx bx-book-open'></i></section><!--logrospng-->
                <p>Técnico profesional</p>
            </section><!--logros-->
        <?php endif; ?>
        <?php if ($cursos[$index + 1]['icono2'] == 1): ?>
            <section class="logros">
                <section class="logrospng b2"><i class='bx bx-buildings'></i></section><!--logrospng-->
                <p>Colaboraciones con negocios y empresas</p>
            </section><!--logros-->
        <?php endif; ?>
        <?php if ($cursos[$index + 1]['icono3'] == 1): ?>
            <section class="logros">
                <section class="logrospng b3"><i class='bx bxs-wrench'></i></section><!--logrospng-->
                <p>Prácticas y evaluaciones dentro y fuera de la institución</p>
            </section><!--logros-->
        <?php endif; ?>

    </section><!--inclusionforzada-->
</section><!--inclusion-->
                </section><!--lado1-->


                <section class="lado2">

    <section class="otrascosas">
    
		<?php if ($cursos[$index + 1]['tipo_curso'] === 'Intensivo'): ?> 
        	<h1 class="duracion_curso_h">Curso de 6 encuentros</h1> 
		<?php elseif ($cursos[$index + 1]['tipo_curso'] === 'Extendido'): ?> 
        	<h1 class="duracion_curso_h">Curso de 3 meses de duración</h1> 
		<?php endif; ?>
        <!--ea49ff-->
        <br>

            <h1 class="nombre_curso_h"><?php echo htmlspecialchars($cursos[$index + 1]['nombre_curso']); ?></h1><br><br><br>

        <hr>
        
                    <section class="otrascosas1">
                        <h1>Requisitos:</h1>
                        <?php if (!empty($cursos[$index + 1]['requisito1'])): ?>
                            <p>◦ <?php echo htmlspecialchars($cursos[$index]['requisito1']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($cursos[$index + 1]['requisito2'])): ?>
                            <p>◦ <?php echo htmlspecialchars($cursos[$index]['requisito2']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($cursos[$index + 1]['requisito3'])): ?>
                            <p>◦ <?php echo htmlspecialchars($cursos[$index]['requisito3']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($cursos[$index + 1]['requisito4'])): ?>
                            <p>◦ <?php echo htmlspecialchars($cursos[$index]['requisito4']); ?></p>
                        <?php endif; ?>
                        <br><br><hr>
                    </section><!--otrascosas1-->

        <!--section class="otrascosas2"-->
            <section class="otrascosas21p" style="align-items:flex-start;width:95%;">
                <section class="tengoclase"><i class='bx bx-money' style="color:#6C6;font-size:6rem;"></i><h1 style="font-size:6rem;">Costos</h1></section><!--tengoclase-->
                <p style="font-size:5rem;">Inscripción: <?php echo htmlspecialchars($cursos[$index + 1]['pago_inscripcion']); ?>$</p>
                <p style="font-size:5rem;">Cuotas: <?php echo htmlspecialchars($cursos[$index + 1]['pago_modulo']); ?>$</p>
                <!--php
                    $total = $cursos[$index]['pago_inscripcion'] + ($cursos[$index + 1]['pago_modulo'] * 3);
                >
                <p style="font-size:5rem;">Total: <?php echo number_format($total, 2, ',', '.'); ?>$</p-->



                    <?php
                        $inscripcion = $cursos[$index + 1]['pago_inscripcion'];
                        $modulo      = $cursos[$index + 1]['pago_modulo'];
                        $oferta      = $cursos[$index + 1]['oferta_vigente'];

                        if(!is_null($oferta) && $oferta > 0){
                            $modulo = $modulo * ((100 - $oferta) / 100); 
                        }

                        $total = $inscripcion + ($modulo * 3);
                    ?>
                    <p style="font-size:5rem;">Total: <?php echo number_format($total, 2, ',', '.'); ?>$</p>

                <?php if(!is_null($oferta) && $oferta > 0): ?>
                        <p style="font-size:5rem;"><s><?php echo htmlspecialchars($cursos[$index + 1]['oferta_vigente']); ?>%</s></p>
			   <?php endif; ?>


            </section><!--otrascosas21p-->



            <section class="otrascosas21h" style="align-items:flex-start;width:95%;">
                <section class="tengoclase"><i class='bx bx-time-five' style="color:#f63;font-size:6rem;"></i><h1 style="font-size:6rem;">Horarios</h1></section><!--tengoclase-->
                <p style="font-size:5rem;"><?php echo htmlspecialchars($cursos[$index + 1]['clase']); ?></p>
            </section><!--otrascosas21h-->
        <!--/section><--otrascosas2-->      

        <hr/><br>
             <section class="llamanos" style="height:15%;">
            	<?php if ($cursos[$index + 1]['cupos'] == 1): ?> 
                		<p class='llamanos111'>Cupos disponibles</p>
				<?php else: ?> 
                	<p class='llamanos111' style="background-color:red;">No hay cupos disponibles</p> 
				<?php endif; ?> 

                <!--a href="<?php echo SERVERURL; ?>solicitud-inscripcion?id=<?php echo htmlspecialchars($cursos[$index + 1]['id_curso']); ?>"
                class="btn btn-sm"
                style="margin:10px auto; width:90%; background-color:#3CA6A6; color:white; padding:8px; border-radius:8px; text-decoration:none;font-size:4rem;box-shadow:rgba(0, 0, 0, 0.16) 0px 3px 6px, rgba(0, 0, 0, 0.23) 0px 3px 6px;">
                Solicitar inscripción
                </a-->

                <a href="#"
                class="btn btn-sm"
                style="margin:10px auto; width:90%; background-color:#3CA6A6; color:white; padding:8px; border-radius:8px; text-decoration:none;font-size:4rem;box-shadow:rgba(0, 0, 0, 0.16) 0px 3px 6px, rgba(0, 0, 0, 0.23) 0px 3px 6px;"
                onclick="window.location.href='<?= SERVERURL ?>index.php?views=solicitud-inscripcion&id=<?= htmlspecialchars($cursos[$index + 1]['id_curso']) ?>'; return false;">
                Solicitar inscripción
                </a>


				<!--php if ($cursos[$index]['oferta'] !== ''): ?> 
                	<p class='llamanos222'><php echo htmlspecialchars($cursos[$index]['oferta']); ?></p> 
				<php endif; ?-->
                <br>
        </section><!--llamanos-->
    </section><!--otrascosas-->
</section><!--lado2-->
</section><!--datos-->
                </div>        
            </div>
        <?php endif; ?>
    <?php endfor; ?>
</div>

<br>


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


<br><br><br><br><br><br>



<!--aqui terminan los contactos-->
<div class="nada3" ><h3 style="font-size:10rem;text-align:center;color:#F03">CONTÁCTANOS</h3><br><br></div>

<!--estos son los mapas-->

<!--aqui empiezan los contactos-->
<div style="width:100%;height:30vh;" class="enlaces">
    <div style="width:50%;margin:auto;" class="enlaces">
        <div class="anima aa1" onclick="window.open('<?php echo htmlspecialchars($info['link_facebook']); ?>', '_blank');"><img src="../vistas/assets/img/feici.webp"></div>
        <div class="anima aa2" onclick="window.open('<?php echo htmlspecialchars($info['link_instagram']); ?>', '_blank');"><img src="../vistas/assets/img/insta.webp"></div>

        <div class="anima aa3"  onclick="toggleContactos(500)"><img src="../vistas/assets/img/maps.webp"></div>
        <div class="medio">
            <div class="anima aa4" onclick="window.open('https://wa.me/<?php echo htmlspecialchars($info['telefono_contacto']); ?>', '_blank')" id="botonTelefono" style="flex-direction:column;cursor:pointer;width:99%;border-radius:60px;height:99%;box-shadow: rgba(14, 30, 37, 0.12) 0px 2px 4px 0px, rgba(14, 30, 37, 0.32) 0px 2px 16px 0px;"><img src="../vistas/assets/img/phoni1.webp">
            </div>
        
            <article id="mensajeTelefono" class="hiddenz"><?php echo htmlspecialchars($info['telefono_contacto']); ?></article>
        </div>
        <div class="medio">    
            <div class="anima aa5" onclick="window.open('mailto:<?php echo htmlspecialchars($info['correo_contacto']); ?>?subject=Asunto%20predefinido&body=Hola,%20quiero%20contactarte')" id="botonEmail" style="cursor:pointer;flex-direction:column;width:99%;border-radius:60px;height:99%;box-shadow: rgba(14, 30, 37, 0.12) 0px 2px 4px 0px, rgba(14, 30, 37, 0.32) 0px 2px 16px 0px;"><img src="../vistas/assets/img/carta1.webp">
            </div>
        
            <article id="mensajeEmail" class="hiddenz"><?php echo htmlspecialchars($info['correo_contacto']); ?></article>
        </div>   
    
    
    </div><!--enlaces->
</div><!--encierra todo lo de contactos en rosa-->




</div>
<!--class="oculto" id="oculto3" y de anima3 tenemos onclick="toggleDiv(0,'oculto3',500)"-->
<div class="ocultocontactos" id="ocultocontactos" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;background-color:#e64a19;height:0px;">
    	<div>
    		<section class="mapas">
            <section class="mapasfotos" style="width:30%;height:95%;"><section class="mapastexto" style="height:50%;"><h3>Encuéntranos</h3><p style="text-align:center;"><?php echo htmlspecialchars($info['direccion_local_ciudad']); ?>  </p><p style="text-align:center;"><?php echo htmlspecialchars($info['direccion_local_pais']); ?></p></section><img loading="lazy" src="../vistas/<?php echo htmlspecialchars($info['ruta_local']); ?>"></section>
                
               
               	<section class="multislider-container" id="slidiomapas" style="width:70%;height:80%;display:flex;justify-content:center;align-items:center;overflow-x:hidden;" >
  					<section class="multislider-content" style="overflow:visible;">
    					<section class="multislide " style="display:flex;justify-content:center;align-items:center;overflow:visible;">        
    						<a style="margin:auto;overflow:visible;" href="../vistas/<?php echo htmlspecialchars($info['ruta_mapa']); ?>" target="_blank"><img loading="lazy" class="revoluciondelainformatica" width="91%" src="../vistas/<?php echo htmlspecialchars($info['ruta_mapa']); ?>"></a>
    					</section>
    
    					<section class="multislide " style="display:flex;justify-content:center;align-items:center;">
    						<a style="margin:auto;" href="../vistas/<?php echo htmlspecialchars($info['ruta_satelite']); ?>" target="_blank"><img loading="lazy" class="revoluciondelainformatica" width="91%" src="../vistas/<?php echo htmlspecialchars($info['ruta_satelite']); ?>"></a>
    					</section>
                         
  					</section>
				</section>
                

            </section><!--mapas-->
    	</section></div>
    </div><!--oculto3-->
<!--aqui terminan los mapas-->



<!--iframe src="aiframeceprocaprofes.php" style="width:100%; height:105vh;border:0;"></iframe-->

<br><br><br><br><br><br>
<div class="otrascosas2add" >
	<div class="generaladd" style="border:0;">
    	<h1 style="font-size:8rem;">El aprendizaje nunca se detiene</h1>
        <p style="font-size:6rem;">En un mundo que cambia rápidamente, adquirir nuevas habilidades y conocimientos es esencial para mantenerte relevante. Un curso te ofrece la oportunidad de aprender de expertos en el campo, acceder a recursos actualizados y participar en un entorno estructurado que fomenta el aprendizaje efectivo. Al inscribirte en un curso, no solo aprendes, sino que también te rodeas de personas que comparten tus intereses. Estas conexiones pueden abrir puertas a nuevas oportunidades laborales, colaboraciones y amistades duraderas. Nunca sabes quién podría ser tu próximo socio comercial o mentor.
</p>
    </div><!--general-->
<div style="position:relative;width:30%;height:100%;bottom:0;"><img style="position:absolute;width:90%;height:90%;bottom:0;left:0;" src="../vistas/assets/img/persona1.webp"></div>
</div><!--otrascosas2-->




<!--creditos-->

<div class="creditos">
	<p>Somos el Centro Educativo Profesional Cecilio Acosta</p>
    <p>¡Sinónimo de Excelencia!</p>
    <div class="division">
    	<div class="divimagen"><img src="../vistas/assets/img/logoceprocahd1.webp" alt="porqueno"></div>
    	<div class="siguenos" >
        	<p>Síguenos</p>
            <div class="quieromorirme">
        	
               
            	<a class="xocas1" style="border-radius:50%;" href="<?php echo htmlspecialchars($info['link_instagram']); ?>" target="_blank"><img src="../vistas/assets/img/insta.webp" alt="porqueno" width="20" height="20"></a>
            
            	<a class="xocas2" style="border-radius:50%;" href="<?php echo htmlspecialchars($info['link_facebook']); ?>" target="_blank"><img src="../vistas/assets/img/feici.webp" alt="porqueno" width="20" height="20"></a>
            
            </div><!--quieromorirme-->
         </div><!--siguenos-->
    		<div class="copi"><p>© Centro Educativo Profesional Cecilio Acosta.CEPROCA.2024</p><p><b class="arroba tooltip" data-tooltip="quaglia.marin.miguel@gmail.com&#10;santiagosan2022@gmail.com">&#128231;</b>  Made by Oliver Martinez and Miguel Quaglia</p>

            </div>
    </div><!--division-->
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

//function redirigirACurso(idCurso, idBanner) {window.location.href = "aiframemateria3-copia.php?id=" + idCurso + "&banner=" + idBanner;}

//function redirigirACurso(idCurso, idBanner,otroValor) {
   //window.location.href = "aiframemateria3-copia.php?id=" + idCurso + "&banner=" + idBanner + "&valor=" + otroValor;
//   window.location.href = "vistas/contenidos/public/aiframemateria3-copia.php?id=" + idCurso + "&banner=" + idBanner + "&valor=" + otroValor;
//}


//function redirigirACurso() {
//  const idCurso = document.getElementById("id_materia_seleccionado").value;
//  const idBanner = 0; // si querés dejar este campo fijo o dinámico luego
//  const valor = 2; // para distinguir que es tipo "Informativo" desde la barra

//  if (idCurso !== "") {
  //  window.location.href = "vistas/contenidos/public/aiframemateria3-copia.php?id=" + idCurso + "&banner=" + idBanner + "&valor=" + valor;
//  } else {
 //   alert("Selecciona un curso válido antes de continuar.");
//  }
//}


//document.addEventListener("DOMContentLoaded", () => {
//  iniciarBuscadorFK({
//    inputTextoID: "buscar",
//    listaResultadosID: "resultadosPublicidad",
//    inputHiddenID: "id_materia_seleccionado",
//    validacionID: "val_publicidad",
//    endpointURL: "<php echo SERVERURL; ?>ajax/codigo_busqueda_publicidad.php"
//  });
//});
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

//function redirigirACurso() { esta era la q menos toque
  //  var idMateria = document.getElementById("id_materia_seleccionado").value;

    //if (idMateria) {
      //  window.location.href = "aiframemateria3-copia.php?id=" + idMateria + "&banner=" + idMateria;
   // } else {
     //   alert("Por favor, selecciona un curso antes de enviar.");
    //}
//}

function seleccionarCurso(nombreCurso, idMateria) {
    document.getElementById("buscar").value = nombreCurso;
    document.getElementById("id_materia_seleccionado").value = idMateria;
}




//modo oscuro
const swith= document.querySelector(".switch");
swith.addEventListener("click", e => {swith.classList.toggle("active");
document.body.classList.toggle("active");})

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
