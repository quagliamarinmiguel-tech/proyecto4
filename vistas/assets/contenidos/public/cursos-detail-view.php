<?php
    
error_reporting(E_ALL);
ini_set('display_errors', 1);
    
//    echo "hola"; die();
//require_once "./controladores/localControlador.php";
//$localCtrl = new localControlador();
require_once "./controladores/publicoControlador.php";
$localCtrl = new publicoControlador();
$localRespuesta = $localCtrl->datos_local_publico_controlador(0);
//var_dump($localRespuesta);die();


$idEnc = $_GET['id'] ?? '';
$marketingId = (int) mainModel::decryption($idEnc);

if ($marketingId <= 0) {
    echo '<div class="alert alert-danger">Curso no encontrado.</div>';
    exit;
}

$respuesta = $localCtrl->detalle_curso_publico_controlador($marketingId);
if (!$respuesta['ok']) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($respuesta['message']) . '</div>';
    exit;
}

$detalle = $respuesta['data'];
$marketing = $detalle['marketing'];
$course = $detalle['course'];
$teacher = $detalle['teacher'];
$period = $detalle['period'];
$precios = $detalle['precios'];

//var_dump($detalle,$marketing,$course,$period,$precios);die();


$local = is_array($localRespuesta) && !empty($localRespuesta['ok']) && is_array($localRespuesta['data'])
    ? $localRespuesta['data']
    : [];

$textosLocal = is_array($local['textos_pagina'] ?? null) ? $local['textos_pagina'] : [];
/*$imagenesLocal = $localCtrl->obtener_imagenes_pagina_publico_controlador();
$imagenesLocal = is_array($imagenesLocal) ? $imagenesLocal : [];


// 1. Paletas de colores
$paletasInicio = [
    0 => ['#00a9f1', '#002a6f', '#72F2EB', '#FF5F5D', '#3ca6a6'],
    1 => ['#9C2113', '#F9ECEC', '#014B43', '#FFD6D1', '#099078'],
    2 => ['#1C338C', '#1B1E59', '#8C1B1B', '#F2F2F2', '#262626'],
    3 => ['#BF0413', '#F27329', '#F24F13', '#F9ECEC', '#A60303'],
    4 => ['#518C81', '#667302', '#515916', '#D9C196', '#D92A1A'],
    5 => ['#FFEFC8', '#7A2B1F', '#471B00', '#522D00', '#E8A149'],
    6 => ['#F9D746', '#142C49', '#F3E3C6', '#4F7FE7', '#FFFFFF'],
    7 => ['#B3A5CC', '#E9EEFF', '#CCBAA6', '#9E82D9', '#C2ACF2'],
    8 => ['#27498C', '#1A4173', '#2E578C', '#B1BBC7', '#D9D9D7'],
    9 => ['#EB7428', '#4F342B', '#F1CDAB', '#D7EFE1', '#EFA438'],
    10 => ['#FBFFFF', '#EBC9C0', '#D48392', '#B73736', '#CFAA7D'],
    11 => ['#010326', '#024959', '#208C8C', '#89D9C2', '#0D0D0D'],
    12 => ['#F5F3F0', '#E8DFD1', '#5C1C3C', '#94215A', '#BAAC97'],
    13 => ['#193C40', '#2E5902', '#1E661B', '#D96941', '#A62B1F']
];


$coloresLocal = $local['colores_pagina'] ?? [];
$paletaInicioId = is_array($coloresLocal) ? (int) ($coloresLocal['paleta_id'] ?? 0) : 0;
$coloresInicio = $paletasInicio[$paletaInicioId] ?? $paletasInicio[0];*/

// Colores desde el mainModel (sin duplicar)
$paletasInicio  = mainModel::obtener_paletas();
$paletaInicioId = mainModel::extraer_paleta_activa($local['colores_pagina'] ?? null);
$coloresInicio  = $paletasInicio[$paletaInicioId] ?? $paletasInicio[0];





// Variables de contacto
$telefonoInicio = htmlspecialchars((string) ($local['telefono_contacto'] ?? ''), ENT_QUOTES, 'UTF-8');
$correoInicio = htmlspecialchars((string) ($local['correo_contacto'] ?? ''), ENT_QUOTES, 'UTF-8');
$facebookInicio = htmlspecialchars((string) ($local['link_facebook'] ?? '#'), ENT_QUOTES, 'UTF-8');
$instagramInicio = htmlspecialchars((string) ($local['link_instagram'] ?? '#'), ENT_QUOTES, 'UTF-8');
$ciudadInicio = htmlspecialchars((string) ($local['direccion_local_ciudad'] ?? ''), ENT_QUOTES, 'UTF-8');
$paisInicio = htmlspecialchars((string) ($local['direccion_local_pais'] ?? ''), ENT_QUOTES, 'UTF-8');
$rif = htmlspecialchars((string) ($local['rif'] ?? ''), ENT_QUOTES, 'UTF-8');

//var_dump($telefonoInicio,$correoInicio,$facebookInicio,$instagramInicio,$ciudadInicio,$paisInicio);die();
?>
<script>window.DARK_MODE_BLOQUEADO = true;</script>






<!--?php
//require_once __DIR__ . "/../../../modelos/mainModel.php";
//$publicoCtrl = new publicoControlador();
?-->

<!--style>
    :root {
        --inicio-primario: <c?php echo $coloresInicio[0]; ?>;
        --inicio-secundario: <c?php echo $coloresInicio[1]; ?>;
        --inicio-terciario: <c?php echo $coloresInicio[2]; ?>;
        --inicio-cuaternario: <c?php echo $coloresInicio[3]; ?>;
        --inicio-quinario: <c?php echo $coloresInicio[4]; ?>;
    }
    .div_header, .header_barra { background-color: var(--inicio-primario); }
    .div_header .contactos_header,.div_header .header_barra ul li a, .div_header { background-color: var(--inicio-primario); }
    .dropdown { background-color: var(--inicio-primario); }
    .header_barra ul li a:hover{ background-color: var(--inicio-secundario); }
    .ver_mas  { background-color: var(--inicio-primario); }
    .ver_mas:hover  { background-color: var(--inicio-secundario); } 


    .div_header .contactos_header:hover { background-color: var(--inicio-terciario); }
    .nuevoslider { border-color: var(--inicio-quinario); }
    .hr_linea_color, .hr_encabezado { background-color: var(--inicio-terciario) !important; }
    .espacio_periodicos .encabezado1, .espacio_periodicos .encabezado2 { border-color: var(--inicio-quinario); }

    .footer_fondo { box-shadow: 0 6px var(--inicio-secundario), 0 14px var(--inicio-secundario), 0 22px var(--inicio-terciario); }
    .creditos.footer { background-color: var(--inicio-secundario); }
    
</style-->



<style>

        :root {
        --inicio-primario: <?php echo $coloresInicio[0]; ?>;
        --inicio-secundario: <?php echo $coloresInicio[1]; ?>;
        --inicio-terciario: <?php echo $coloresInicio[2]; ?>;
        --inicio-cuaternario: <?php echo $coloresInicio[3]; ?>;
        --inicio-quinario: <?php echo $coloresInicio[4]; ?>;
    }
    
    .div_header, .header_barra { background-color: var(--inicio-primario); }
    .div_header .contactos_header,.div_header .header_barra ul li a, .div_header { background-color: var(--inicio-primario); }
    .dropdown { background-color: var(--inicio-primario); }
    .header_barra ul li a:hover{ background-color: var(--inicio-secundario); }
    .ver_mas  { background-color: var(--inicio-primario); }
    .ver_mas:hover  { background-color: var(--inicio-secundario); } 
    .div_header .contactos_header:hover { background-color: var(--inicio-terciario); }
    .nuevoslider { border-color: var(--inicio-quinario); }
    .hr_linea_color, .hr_encabezado { background-color: var(--inicio-terciario) !important; }
    .espacio_periodicos .encabezado1, .espacio_periodicos .encabezado2 { border-color: var(--inicio-quinario); }
    .footer_fondo { box-shadow: 0 6px var(--inicio-primario), 0 14px var(--inicio-secundario), 0 22px var(--inicio-terciario); }
    .creditos.footer { background-color: var(--inicio-primario); }
    
    /*info de los cursos*/
    .checklist{border-color: var(--inicio-quinario);}
    .contenido_curso{border-color:var(--inicio-secundario);}
    .datosprofe_curso img{border-color:var(--inicio-primario);}
    .duracion_curso_h {background-color:var(--inicio-secundario);border-color:var(--inicio-secundario);}
    .contenido_curso i{color:var(--inicio-secundario);}    
</style>


<div class="div_header">
	<div style="cursor:pointer;" class="logo_ceproca" onclick="window.location.href='<?php echo SERVERURL; ?>index.php', '_blank';">
        <h3><b id="not_ceproca"><?php echo htmlspecialchars($textosLocal['nombre_corto'], ENT_QUOTES, 'UTF-8'); ?></b></h3>
        <a href="index.php"><img src="<?= mainModel::obtenerRutaImagenLocal('logo') ?>" alt="porqueno"></a> 				          
    </div>

    <div class="header_barra">
        <ul>
            <li><a href="<?php echo SERVERURL; ?>index.php?views=cursos">Cursos</a></li>
            <li id="not_dropdown">
                <a style="cursor:context-menu;"  >Contáctanos</a>
                <ul class="dropdown">
                    <li><a href="<?php echo $facebookInicio; ?>">Facebook</a></li>
                    <li><a href="<?php echo $instagramInicio; ?>">Instagram</a></li>
                    <li> 
                        <div class="contactos_header" >
                             <a href="https://wa.me/<?php echo $telefonoInicio; ?>">Teléfono</a>
                        </div>
                    </li>
                    <li>
                        <div class="contactos_header" >
                            <a href="mailto:<?php echo $correoInicio; ?>?subject=Asunto%20predefinido&body=Hola,%20quiero%20contactarte">Correo</a>
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

<div class="inicio_header_imagen_ceproca" style="background-image: url('<?= mainModel::obtenerRutaImagenLocal('img_header_cursos') ?>');flex-direction:column;"><!--nueva2--> 
    <h1 class="header_imagen_ceproca_cursos_h1"><?php echo htmlspecialchars($textosLocal['txt_imagen_header_cursos'], ENT_QUOTES, 'UTF-8'); ?></h1>
	<p class="header_imagen_ceproca_cursos_p"><?php echo htmlspecialchars($textosLocal['txt2_imagen_header_cursos'], ENT_QUOTES, 'UTF-8'); ?></p>
</div>



<header class="texto_header_cursos_100_practicos">
    <div class="texto_header_cursos_100_practicos_div1"></div>
    	<h1>"Aprovecha cada oportunidad para aprender algo nuevo y especialízate en lo que amas."</h1>
    <div class="texto_header_cursos_100_practicos_div2"></div>
</header>



<div style="display:flex;justify-content:center;align-items:center;width:100%;height:auto;">

    <div style="display:flex;justify-content:center;align-items:center;width:100%;height:auto;">
        <div style="background-color:white; height: auto;width:90%;">
            <section class="datos datos_cursos">
                <section class="lado1 div_conjunto_datos_curso_1">
                    <section class="nombre_del_curso">
                        <h1><?= htmlspecialchars($course['name']) ?></h1>
                    </section>
                    <br>
                    <section class="general curso_descripcion_y_profesor">
                        <p class="pref_curso"><?= htmlspecialchars($marketing['description']) ?></p>

                        <?php if ($teacher !== null): ?>
                            <section class="datosprofe_curso">
                                <img src="../vistas/<?= htmlspecialchars($teacher['img_route']) ?>" />
                                <p><b>Prof. <?= htmlspecialchars($teacher['name'] . ' ' . $teacher['last_name']) ?></b></p>
                                <p><?= htmlspecialchars($teacher['description']) ?></p>
                            </section>
                        <?php endif; ?>
                    </section><!--general-->

                    <section class="checklist contenido_list">
                        <h1>Aquí aprenderás:</h1>
                        <section class="parachecklisst div_contenido_list">
                            <section class="subchecklisst sub_div_contenido_list">
                                <?php foreach ($marketing['contenidos'] as $contenido): ?>
                                    <section class="checking contenido_curso">
                                        <i class='bx bx-check-square'></i>
                                        <p><?= htmlspecialchars($contenido) ?></p>
                                    </section>
                                <?php endforeach; ?>
                            </section>
                        </section>
                    </section><!--checklist-->

                    <section class="inclusion div_beneficios_curso">
                        <h1>Aquí obtendrás:</h1>
                        <section class="inclusionforzada beneficios_curso">
                            <section class="logros beneficio">
                                <section class="logrospng clr_rojo beneficio_icon"><i class='bx bx-bookmark-alt-minus'></i></section>
                                <p>Obtén un certificado</p>
                            </section>

                            <?php if ($marketing['icon_1']): ?>
                                <section class="logros beneficio">
                                    <section class="logrospng clr_rosa beneficio_icon"><i class='bx bx-book-open'></i></section>
                                    <p>Curso para mayores de 9 años en adelante</p>
                                </section>
                            <?php endif; ?>

                            <?php if ($marketing['icon_2']): ?>
                                <section class="logros beneficio">
                                    <section class="logrospng clr_verde beneficio_icon"><i class='bx bx-buildings'></i></section>
                                    <p>Colaboraciones con negocios y empresas</p>
                                </section>
                            <?php endif; ?>

                            <?php if ($marketing['icon_3']): ?>
                                <section class="logros beneficio">
                                    <section class="logrospng clr_morado beneficio_icon"><i class='bx bxs-wrench'></i></section>
                                    <p>Prácticas y evaluaciones dentro y fuera de la institución</p>
                                </section>
                            <?php endif; ?>
                        </section>
                    </section>
                </section><!--lado1-->

                <section class="lado2 div_conjunto_datos_curso_2">
                    
                    <section class="lado2png">
                        <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($marketing['img_route']); ?>" />                        
                    </section>
                    
                    <section class="otrascosas div_req_precio_duracion">
                        <?php if ($course['type'] === 'Intensivo'): ?>
                            <h1 class="duracion_curso_h">Curso de 6 encuentros</h1>
                        <?php elseif ($course['type'] === 'Extendido'): ?>
                            <h1 class="duracion_curso_h">Curso de 3 meses de duración</h1>
                        <?php elseif ($course['type'] === 'Técnico Profesional'): ?>
                            <h1 class="duracion_curso_h">Curso Técnico de 3 meses de duración</h1>
                        <?php endif; ?>
                        <br>
                        <!--h1 class="nombre_curso_h"><c?= htmlspecialchars($course['name']) ?></h1-->
                        <br>

                        <hr>

                        <section class="otrascosas1 requisitos_curso">
                            <h1>Requisitos:</h1>
                            <?php foreach ($marketing['requisitos'] as $req): ?>
                                <p>◦ <?= htmlspecialchars($req) ?></p>
                            <?php endforeach; ?>
                            <br><br>
                        </section>

                        <section class="div_precio_y_horario_curso">
                            <section class="otrascosas21p precio_y_horario_curso">
                                <section class="tengoclase precio_y_horario_titulo">
                                    <i class='bx bx-money' style="color:#6C6;"></i>
                                    <h1>Costos</h1>
                                </section>

                                <?php if ($period !== null): ?>
                                    <p>Inscripción: <?= number_format($precios['inscripcion'], 2, ',', '.') ?> $</p>
                                    <p><?= htmlspecialchars($precios['texto_cuota']) ?>: <?= number_format($precios['modulo'], 2, ',', '.') ?> $</p>
                                    <p>Total: <?= number_format($precios['total'], 2, ',', '.') ?> $</p>
                                <?php else: ?>
                                    <p>No hay costos disponibles actualmente.</p>
                                <?php endif; ?>
                            </section>

                            <section class="otrascosas21h precio_y_horario_curso">
                                <section class="tengoclase precio_y_horario_titulo">
                                    <i class='bx bx-time-five' style="color:#f63;"></i>
                                    <h1>Horarios</h1>
                                </section>
                                <p><?= htmlspecialchars($marketing['class']) ?></p>
                            </section>
                        </section>

                        <hr/><br/>

                        <!--section class="llamanos cohorte_y_solicitud_curso">
                            <?php if ($period !== null): ?>
                                <p>
                                    <?= htmlspecialchars($period['name']) ?>
                                    <?php if (!empty($period['start_date'])): ?>
                                        (<?= htmlspecialchars($period['start_date']) ?>
                                        <?php if (!empty($period['end_date'])): ?>
                                            - <?= htmlspecialchars($period['end_date']) ?>
                                        <?php endif; ?>)
                                    <?php endif; ?>
                                </p>
                                <p>
                                    <b>Estado:</b> <?= htmlspecialchars($period['current_state']) ?>
                                </p>
                            <?php endif; ?>

                            <a href="#"
                                class="btn btn-sm car"
                                onclick="window.location.href='<?= SERVERURL ?>index.php?views=solicitud-inscripcion&id=<?= htmlspecialchars($course['id']) ?>'; return false;">
                                Solicitar inscripción
                            </a>
                            <br>
                            <section style="height:10vh;"</section>
                        </section-->
                        <section class="llamanos cohorte_y_solicitud_curso">
                            <?php if ($period !== null): ?>
                                <p>
                                    <?= htmlspecialchars($period['name']) ?>
                                    <?php if (!empty($period['start_date'])): ?>
                                        (<?= htmlspecialchars($period['start_date']) ?>
                                        <?php if (!empty($period['end_date'])): ?>
                                            - <?= htmlspecialchars($period['end_date']) ?>
                                        <?php endif; ?>)
                                    <?php endif; ?>
                                </p>
                                <p>
                                    <b>Estado:</b> <?= htmlspecialchars($period['current_state']) ?>
                                </p>

                                <a href="#"
                                    class="btn btn-sm car"
                                    onclick="window.location.href='<?= SERVERURL ?>index.php?views=solicitud-inscripcion&id=<?= htmlspecialchars($course['id']) ?>'; return false;">
                                    Solicitar inscripción
                                </a>
                            <?php else: ?>
                                <div style="display:flex;justify-content:center;align-items:center;width:100%;">
                                    <p class="text-muted">No disponible actualmente</p>
                                </div>
                            <?php endif; ?>

                            <br>
                            <section style="height:5vh;"></section>
                        </section>
                    </section>
                </section>

            </section>
        </div>
    </div>
    

</div>



<section style="height:10vh;"></section>


<!--aqui empiezan los contactos-->
<div class="nada3">
    <h3>CONTÁCTANOS</h3><br><br>
</div>


<div class="div_contactos">
    <div class="enlaces_div_circulos">
        
        <div class="boton_contactos btn_contactos_animacion btn_contactos_clr1" onclick="window.open('<?php echo $facebookInicio; ?>', '_blank');">
            <img src="../vistas/assets/img/feici.webp">
        </div>
        
        <div class="boton_contactos btn_contactos_animacion btn_contactos_clr2" onclick="window.open('<?php echo $instagramInicio; ?>', '_blank');">
            <img src="../vistas/assets/img/insta.webp">
        </div>
        
        <div class="boton_contactos btn_contactos_animacion btn_contactos_clr3" onclick="toggleContactos(500)">
            <img src="../vistas/assets/img/maps.webp">
        </div>
        
        <div class="div_tlfno_correo">
            <div class="boton_contactos_animacion2 btn_contactos_clr4" onclick="window.open('https://wa.me/<?php echo $telefonoInicio; ?>', '_blank')" id="botonTelefono">
                <img src="../vistas/assets/img/phoni1.webp">
        	</div>
            <article id="mensajeTelefono" class="hiddenz"><?php echo $telefonoInicio; ?></article>
        </div>
       
        <div class="div_tlfno_correo">    
            <div class="boton_contactos_animacion2 btn_contactos_animacion btn_contactos_clr5" onclick="window.open('mailto:<?php echo $correoInicio; ?>?subject=Asunto%20predefinido&body=Hola,%20quiero%20contactarte')" id="botonEmail">
                <img src="../vistas/assets/img/carta1.webp">
            </div>
            <article id="mensajeEmail" class="hiddenz"><?php echo $correoInicio; ?></article>
        </div>   
    
    </div>
</div>

<div class="oculto" id="ocultocontactos"><!--id="oculto3"-->
    <div>
    	<section class="mapas">
            
            <section class="mapasfotos div_mapas_texto">
                <section class="mapastexto mapas_texto">
                    <h3>Encuéntranos</h3>
                    <p ><?php echo $ciudadInicio; ?></p>
                    <p ><?php echo $paisInicio; ?></p>
                </section>
                <img loading="lazy" src="<?= mainModel::obtenerRutaImagenLocal('local') ?>">
            </section>
                                                           
            <section class="multislider-container flex_def" id="slidiomapas">
  				<section class="multislider-content" style="overflow:visible;">
                    <!--display:flex;justify-content:center;align-items:center;-->
                    <section class="multislide flex_def" style="overflow:visible;">        
                        <a style="margin:auto;overflow:visible;" href="<?php echo htmlspecialchars($imagenesLocal['mapa_google'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                            <img loading="lazy" class="mapa_desplazante" width="91%" src="<?= mainModel::obtenerRutaImagenLocal('mapa_google') ?>">
                        </a>
                    </section>
                    <!--
<c?php echo inicioLocalImage('mapa_google', '../vistas/assets/img/default.webp', $imagenesLocal); ?><c?php echo inicioLocalImage('mapa_vista_satelital', '../vistas/assets/img/default.webp', $imagenesLocal); ?>
-->
                    <!-- style="display:flex;justify-content:center;align-items:center;"-->
                    <section class="multislide flex_def">
                        <a style="margin:auto;" href="<?php echo htmlspecialchars($imagenesLocal['mapa_vista_satelital'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                            <img loading="lazy" class="mapa_desplazante" width="91%" src="<?= mainModel::obtenerRutaImagenLocal('mapa_vista_satelital') ?>">
                        </a>
                    </section>            
            	</section>
			</section>
        
        </section>
	</div>
</div>
<!--aqui terminan los mapas-->











<div class="space"></div>

<div class="div_footer_cursos_texto" >
	<div class="footer_cursos_texto">
    	<h1>El aprendizaje nunca se detiene</h1>
        <p>En un mundo que cambia rápidamente, adquirir nuevas habilidades y conocimientos es esencial para mantenerte relevante. Un curso te ofrece la oportunidad de aprender de expertos en el campo, acceder a recursos actualizados y participar en un entorno estructurado que fomenta el aprendizaje efectivo. Al inscribirte en un curso, no solo aprendes, sino que también te rodeas de personas que comparten tus intereses. Estas conexiones pueden abrir puertas a nuevas oportunidades laborales, colaboraciones y amistades duraderas. Nunca sabes quién podría ser tu próximo socio comercial o mentor.</p>
    </div>
	<div class="footer_cursos_texto_img">
        <img src="../vistas/assets/img/persona1.webp">
    </div>
</div>



<div class="creditos footer">
    <!--?php echo htmlspecialchars($textosLocal['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?-->
    <p>RIF: <?php echo $rif; ?></p>
    <p><?php echo htmlspecialchars($textosLocal['txt_ult_subtitulo_footer'], ENT_QUOTES, 'UTF-8'); ?></p>
        
    <section class="tlfno_y_correo_footer flex_def">
        <section class="datos_tlfno_y_correo_footer flex_def">
            <i class='bx bx-phone'></i>    <p><?php echo $telefonoInicio; ?></p>
        </section>
        <section class="datos_tlfno_y_correo_footer flex_def">
            <i class='bx bx-envelope'></i> <p><?php echo $correoInicio; ?></p>
        </section>
    </section>
    
    <div class="footer_creditos">
        <div class="footer_creditos_imagen">
            <img src="<?= mainModel::obtenerRutaImagenLocal('logo') ?>" alt="porqueno"><!--<c?php echo htmlspecialchars($imagenesLocal['logo'], ENT_QUOTES, 'UTF-8'); ?>-->
        </div>
    	
        <div class="footer_creditos_siguenos">
        	<p>Síguenos</p>
            <div class="footer_creditos_siguenos_enlaces">
                <a class="footer_creditos_siguenos_enlaces1" href="<?php echo $instagramInicio; ?>" target="_blank">
                    <img src="../vistas/assets/img/insta.webp" alt="porqueno" width="20" height="20">
                </a>
                <a class="footer_creditos_siguenos_enlaces2"  href="<?php echo $facebookInicio; ?>" target="_blank">
                    <img src="../vistas/assets/img/feici.webp" alt="porqueno" width="20" height="20">
                </a>
            </div>
        </div>
    	
        <div class="footer_creditos_copi">
            <p>© <?php echo htmlspecialchars($textosLocal['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?>.<?php echo date('Y'); ?></p>
            <p><b class="arroba tooltip" data-tooltip="quaglia.marin.miguel@gmail.com&#10;santiagosan2022@gmail.com">&#128231;</b>  Made by Oliver Martinez and Miguel Quaglia</p>
        </div>
    </div>
</div><!--creditos-->






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

</script>
