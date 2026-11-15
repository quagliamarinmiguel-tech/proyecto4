<?php
require_once "./controladores/publicoControlador.php";
$localCtrl = new publicoControlador();
$localRespuesta = $localCtrl->datos_local_publico_controlador(1);

$local = is_array($localRespuesta) && !empty($localRespuesta['ok']) && is_array($localRespuesta['data'])
    ? $localRespuesta['data']
    : [];

$textosLocal = is_array($local['textos_pagina'] ?? null) ? $local['textos_pagina'] : [];


// 1. Colores desde el mainModel (sin duplicar)
$paletasInicio  = mainModel::obtener_paletas();
$paletaInicioId = mainModel::extraer_paleta_activa($local['colores_pagina'] ?? null);
$coloresInicio  = $paletasInicio[$paletaInicioId] ?? $paletasInicio[0];


// 3. Profesores (mapeando desde $local['profesores'])
$profesores = array_map(function($prof) {
    return [
        'imagen_profesor' => $prof['img_route'] ?? '',
        'nombre_profesor' => $prof['name'] ?? '',
        'apellido_profesor' => $prof['last_name'] ?? '',
        'descripcion' => $prof['description'] ?? '',
        'telefono_profesor' => $prof['phone'] ?? '',
        'correo' => $prof['email'] ?? '',
    ];
}, $local['profesores'] ?? []);

// 4. Cursos (mapeando y calculando desde $local['cursos'])
$cursos = array_map(function($curso, $index) {
    $inscripcion = (float) ($curso['prices']['inscripcion'] ?? 0);
    $tipo = strtolower($curso['type'] ?? '');
    $modulo = (float) ($curso['prices']['modulo1'] ?? 0);
    if ($tipo === 'intensivo') {
        $modulo = (float) ($curso['prices']['encuentro'] ?? 0);
    }
    $pago_total = $inscripcion + ($modulo * 3);

    return [
        'id_materia' => $index + 1,
        'ruta_materia' => $curso['img_route'] ?? '',
        'nombre_curso' => $curso['name'] ?? '',
        'prefacio' => $curso['introduction'] ?? '',
        'marketing_id' => $curso['marketing_id'] ?? '',
        'pago_inscripcion' => $inscripcion,
        'pago_modulo' => $modulo,
        'oferta_vigente' => 0,
        'pago_total' => $pago_total,
    ];
}, $local['cursos'] ?? [], array_keys($local['cursos'] ?? []));

// Variables de contacto
$telefonoInicio = htmlspecialchars((string) ($local['telefono_contacto'] ?? ''), ENT_QUOTES, 'UTF-8');
$correoInicio = htmlspecialchars((string) ($local['correo_contacto'] ?? ''), ENT_QUOTES, 'UTF-8');
$facebookInicio = htmlspecialchars((string) ($local['link_facebook'] ?? '#'), ENT_QUOTES, 'UTF-8');
$instagramInicio = htmlspecialchars((string) ($local['link_instagram'] ?? '#'), ENT_QUOTES, 'UTF-8');
$ciudadInicio = htmlspecialchars((string) ($local['direccion_local_ciudad'] ?? ''), ENT_QUOTES, 'UTF-8');
$paisInicio = htmlspecialchars((string) ($local['direccion_local_pais'] ?? ''), ENT_QUOTES, 'UTF-8');
$rif = htmlspecialchars((string) ($local['rif'] ?? ''), ENT_QUOTES, 'UTF-8');


$imgn3 = [];
for ($i = 1; $i <= 6; $i++) {
    $ruta = mainModel::obtenerRutaImagenLocal("slider_vertical_$i");
    if (!empty($ruta)) {
        $imgn3[] = $ruta;
    }
}

// Sliders automáticos (slider_auto_1 .. slider_auto_8)
$imgn1 = [];
for ($i = 1; $i <= 8; $i++) {
    $ruta = mainModel::obtenerRutaImagenLocal("slider_auto_$i");
    if (!empty($ruta)) {
        $imgn1[] = $ruta;
    }
}

?>


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


    .div_header .contactos_header:hover { background-color: var(--inicio-secundario); }
    .nuevoslider { border-color: var(--inicio-quinario); }
    .hr_linea_color, .hr_encabezado { background-color: var(--inicio-terciario) !important; }
    .espacio_periodicos .encabezado1, .espacio_periodicos .encabezado2 { border-color: var(--inicio-quinario); }

    .footer_fondo { box-shadow: 0 6px var(--inicio-primario), 0 14px var(--inicio-secundario), 0 22px var(--inicio-terciario); }
    .creditos.footer { background-color: var(--inicio-secundario); }
    
        .inicio_header_imagen_ceproca {
        --sombra-base: <?php echo mainModel::hex2rgba($coloresInicio[0], 1); ?>;
        --sombra1:     <?php echo mainModel::hex2rgba($coloresInicio[0], 0.8); ?>;
        --sombra2:     <?php echo mainModel::hex2rgba($coloresInicio[0], 0.7); ?>;
        --sombra3:     <?php echo mainModel::hex2rgba($coloresInicio[0], 0.6); ?>;
        --sombra4:     <?php echo mainModel::hex2rgba($coloresInicio[0], 0.5); ?>;
        --sombra5:     <?php echo mainModel::hex2rgba($coloresInicio[0], 0.4); ?>;
    }
</style>






<div class="div_header">
	<div style="cursor:pointer;" class="logo_ceproca" onclick="window.location.href='<?php echo SERVERURL; ?>index.php', '_blank';">
        <h3><b id="not_ceproca"><?php echo htmlspecialchars($textosLocal['nombre_corto'], ENT_QUOTES, 'UTF-8'); ?></b></h3>
        <a href="index.php"><img src="<?= mainModel::obtenerRutaImagenLocal('logo') ?>" alt="porqueno"></a> <!--<c?php echo htmlspecialchars($imagenesLocal['logo'], ENT_QUOTES, 'UTF-8'); ?>-->				                  
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

<header class="inicio_header_imagen_ceproca" style="background-image: url('<?= mainModel::obtenerRutaImagenLocal('img_header') ?>');"><!--<c?php echo htmlspecialchars($imagenesLocal['img_header'], ENT_QUOTES, 'UTF-8'); ?>-->
    <p>
		<?php echo htmlspecialchars($textosLocal['txt_header'], ENT_QUOTES, 'UTF-8'); ?>
    </p>
</header>







<!--aqui empiezan los botones de cursos-->
<header class="texto_header_cursos_100_practicos">
    <div class="texto_header_cursos_100_practicos_div1"></div>
    	<h1><!--?php echo inicioLocalText('txt_titulo_post_header', 'VEN Y FORMA PARTE DE LA ACADEMIA MÁS GRANDE EL ESTADO GUÁRICO', $textosLocal); ?-->
    		<?php echo htmlspecialchars($textosLocal['txt_titulo_post_header'], ENT_QUOTES, 'UTF-8'); ?>
		</h1>
        <h1>
                <!--?php echo inicioLocalText('txt_subtitulo_post_header', 'CURSOS 100% PRÁCTICOS', $textosLocal); ?-->
	    		<?php echo htmlspecialchars($textosLocal['txt_subtitulo_post_header'], ENT_QUOTES, 'UTF-8'); ?>
	    </h1>
    <div class="texto_header_cursos_100_practicos_div2"></div>
</header>

<div class="div_img_verticales_y_tarjetas">

    <!--este es el slider de imagenes verticales-->

    <div class="multislider-container" id="slidio6">
        <div class="multislider-content height100" >
            <?php
            // Agrupar imágenes de a pares o individuales según la estructura original
            $total = count($imgn3);
            for ($i = 0; $i < $total; $i++):
                // Determinar si es un slide con 1 o 2 imágenes
                $slideImgs = [];
                if ($i % 2 == 0) {
                    // Slide con 1 imagen (índice par)
                    $slideImgs[] = $imgn3[$i];
                } else {
                    // Slide con 2 imágenes (índice impar)
                    if (isset($imgn3[$i])) $slideImgs[] = $imgn3[$i];
                    if (isset($imgn3[$i+1])) $slideImgs[] = $imgn3[$i+1];
                    $i++; // saltamos el siguiente índice porque ya lo usamos
                }
            ?>
                <div class="multislide <?php echo count($slideImgs) > 1 ? 'div_imagenes_verticales' : 'height100 div_imagenes_verticales'; ?>">
                    <?php foreach ($slideImgs as $img): ?>
                        <img class="imagenes_verticales" loading="lazy" src="<?php echo htmlspecialchars($img); ?>" alt="Imagen">
                    <?php endforeach; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    
    
    
    
    
	<!--aqui empiezan tarjetas-->
    <div class="tarjetas">
        <div class="tarjconteiners">
      
            <?php foreach ($cursos as $curso): ?>
                <div class="card" onclick="registrarYRedirigir('<?php echo $curso['marketing_id']; ?>')">
                    <figure>
                        <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($curso['ruta_materia']); ?>" alt="Imagen del curso">
                    </figure>
                    <div class="contenido">
                        <h4><?php echo htmlspecialchars($curso['nombre_curso']); ?></h4>
                        <p><?php echo htmlspecialchars($curso['prefacio']); ?></p>
                        <div class="tarjeta_div_precio_cupos">
                            <p>Precio: <?php echo htmlspecialchars($curso['pago_total']); ?>$</p>
                            <i class='bx bxs-badge-dollar' style='color:orange;'></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
</div>
<!--aqui terminan tarjetas-->

<br>
<a class="ver_mas" href="<?php echo SERVERURL; ?>index.php?views=cursos">Ver más</a>
<div id="ir_arriba"><i class='bx bxs-home-alt-2'></i></div>




<!--primer slider-->
<div class="nuevoslider" style="background-color: <?php echo htmlspecialchars($coloresInicio[2] ?? '#FF5F5D', ENT_QUOTES, 'UTF-8'); ?>;">
    <div class="primer_slider_texto" style="background-color:inherit;display:flex;justify-content:center;align-items:center;flex-direction:column;margin:20px;width:50%;">
        <h1 style="text-align:center;font-size:10rem;color: white;text-shadow: 4px 3px 0 #7A7A7A;"><?php echo htmlspecialchars($textosLocal['txt_titulo_info1'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <h1 style="text-shadow:0px 0px 10px rgba(15,7,4,0);text-align:center;"><?php echo htmlspecialchars($textosLocal['txt_subtitulo_info1'], ENT_QUOTES, 'UTF-8'); ?></h1>
    </div>    
    <div class="newslider width400" style="background-color:green;border-radius:12px;margin:30px;">
        <div class="newslides width400" style="border-radius:12px;">
                <img class="width400" style="border-radius:12px;" loading="lazy" src="<?= mainModel::obtenerRutaImagenLocal('img_desc1') ?>" alt="Imagen del Slider">
        </div><!--<c?php echo htmlspecialchars($imagenesLocal['img_desc1'], ENT_QUOTES, 'UTF-8'); ?>-->
    </div>
</div>

<div class="nuevoslider" style="background-color: <?php echo htmlspecialchars($coloresInicio[3] ?? '#FF5F5D', ENT_QUOTES, 'UTF-8'); ?>;">        
    <div class="newslider width400" style="background-color:green;border-radius:12px;margin:30px;">
        <div class="newslides width400" style="border-radius:12px;">
                <img class="width400" style="border-radius:12px;" loading="lazy" src="<?= mainModel::obtenerRutaImagenLocal('img_desc2') ?>" alt="Imagen del Slider">
        </div><!--<c?php echo htmlspecialchars($imagenesLocal['img_desc2'], ENT_QUOTES, 'UTF-8'); ?>-->
    </div>
    <div class="primer_slider_texto" style="background-color:inherit;display:flex;justify-content:center;align-items:center;flex-direction:column;margin:20px;width:50%;">
        <h1 style="text-align:center;font-size:10rem;color: white;text-shadow: 4px 3px 0 #7A7A7A;"><?php echo htmlspecialchars($textosLocal['txt_titulo_info2'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <h1 style="text-shadow:0px 0px 10px rgba(15,7,4,0);text-align:center;"> <?php echo htmlspecialchars($textosLocal['txt_subtitulo_info2'], ENT_QUOTES, 'UTF-8'); ?></h1>
    </div>
</div>

<div class="nuevoslider" style="background-color: <?php echo htmlspecialchars($coloresInicio[4] ?? '#FF5F5D', ENT_QUOTES, 'UTF-8'); ?>;">
    <div class="primer_slider_texto" style="background-color:inherit;display:flex;justify-content:center;align-items:center;flex-direction:column;margin:20px;width:50%;">
        <h1 style="text-align:center;font-size:10rem;color: white;text-shadow: 4px 3px 0 #7A7A7A;"><?php echo htmlspecialchars($textosLocal['txt_titulo_info3'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <h1 style="text-shadow:0px 0px 10px rgba(15,7,4,0);text-align:center;"><?php echo htmlspecialchars($textosLocal['txt_subtitulo_info3'], ENT_QUOTES, 'UTF-8'); ?></h1>
    </div>       
    <div class="newslider width400" style="background-color:green;border-radius:12px;margin:30px;">
        <div class="newslides width400" style="border-radius:12px;">
                <img class="width400" style="border-radius:12px;" loading="lazy" src="<?= mainModel::obtenerRutaImagenLocal('img_desc3') ?>" alt="Imagen del Slider">
        </div><!--<c?php echo htmlspecialchars($imagenesLocal['img_desc3'], ENT_QUOTES, 'UTF-8'); ?>-->
    </div>
</div>



<div class="space"></div>
    
    


<!--facebook y comentarios-->

<div class="div_facebook_y_comentarios flex_def">
    
    <div class="div_facebook">
    	<iframe class="pedro div_iframe_facebook" src="<?php echo htmlspecialchars($textosLocal['url_add'], ENT_QUOTES, 'UTF-8'); ?>" width="267" height="476" style="border:none;overflow:hidden;" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowFullScreen="true"></iframe>
    </div>

	<br><br><br>

   
    
    <!--comentarios-->
    <div class="multislider-container" id="multislider3" style="width: 500px;overflow:hidden;">
    	<div class="multislider-content">
        	<div class="multislide clase-extra">
            	<div class="bloque_cmts">
                	
                	<section class="cmt_doble cmt_doble_1_1">
                    	<section class="cmt cmt_1_1">
                        	<section class="datos_cmt">
                            	<p><b><?php echo htmlspecialchars($textosLocal['nombre_com1'], ENT_QUOTES, 'UTF-8'); ?></b></p>
                        	</section>
                        <p><?php echo htmlspecialchars($textosLocal['texto_com1'], ENT_QUOTES, 'UTF-8'); ?></p>
                    	</section>	

                		<section class="cmt cmt_1_2">
                        	<section class="datos_cmt">
                            	<p><b><?php echo htmlspecialchars($textosLocal['nombre_com2'], ENT_QUOTES, 'UTF-8'); ?></b></p>
                        	</section>
                        <p><?php echo htmlspecialchars($textosLocal['texto_com2'], ENT_QUOTES, 'UTF-8'); ?></p>
                   		</section>
                	</section>
                    
                    <section class="cmt_unitario cmt_unitario_1_1">
                        <section class="datos_cmt">
                            	<p><b><?php echo htmlspecialchars($textosLocal['nombre_com3'], ENT_QUOTES, 'UTF-8'); ?></b></p>
                        </section>
                        <p><?php echo htmlspecialchars($textosLocal['texto_com3'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </section>
                    
            	</div>
      		</div>



       


            <div class="multislide clase-extra">
                <div class="bloque_cmts" style="flex-wrap:nowrap;flex-direction:row;">
                    
                    <section class="cmt_doble cmt_doble_3_1">
                        <section class="cmt cmt_3_1">
                            <section class="datos_cmt">
                            	<p><b><?php echo htmlspecialchars($textosLocal['nombre_com4'], ENT_QUOTES, 'UTF-8'); ?></b></p>
                            </section>
                        <p><?php echo htmlspecialchars($textosLocal['texto_com4'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </section>	

                        <section class="cmt cmt_3_2" >
                            <section class="datos_cmt">
                            	<p><b><?php echo htmlspecialchars($textosLocal['nombre_com5'], ENT_QUOTES, 'UTF-8'); ?></b></p>
                            </section>
                        <p><?php echo htmlspecialchars($textosLocal['texto_com5'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </section>
                    </section>

                    <section class="cmt_unitario cmt_unitario_3_1">
                        <section class="datos_cmt">
                            	<p><b><?php echo htmlspecialchars($textosLocal['nombre_com6'], ENT_QUOTES, 'UTF-8'); ?></b></p>
                        </section>
                        <p><?php echo htmlspecialchars($textosLocal['texto_com6'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </section>
                    
                </div>
            </div>

            
            
            <div class="multislide otra-clase">
                <div class="bloque_cmts">
                    
                    <section class="cmt_unitario cmt_unitario_4_1">
                        <section class="datos_cmt">
                            	<p><b><?php echo htmlspecialchars($textosLocal['nombre_com7'], ENT_QUOTES, 'UTF-8'); ?></b></p>
                        </section>
                        <p><?php echo htmlspecialchars($textosLocal['texto_com7'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </section>

                    <section class="cmt_unitario cmt_unitario_4_2">
                        <section class="datos_cmt">
                            	<p><b><?php echo htmlspecialchars($textosLocal['nombre_com8'], ENT_QUOTES, 'UTF-8'); ?></b></p>
                        </section>
                                <p><?php echo htmlspecialchars($textosLocal['texto_com8'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </section>

                    <section class="cmt_unitario cmt_unitario_4_3">
                        <section class="datos_cmt">
                            	<p><b><?php echo htmlspecialchars($textosLocal['nombre_com9'], ENT_QUOTES, 'UTF-8'); ?></b></p>
                        </section>
                        <p><?php echo htmlspecialchars($textosLocal['texto_com9'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </section>
                    
                </div>
            </div>

        </div>
    </div>
</div><!--aqui terminan los comentarios y periodicos-->

<div class="space"></div>



<div class="flex_def div_slider_profes">
    <div class="multislider-container" id="sliderpr" style="width:80%;">
        <div class="multislider-content" style="height:100%;">
            <?php for ($i = 0; $i < count($profesores); $i += 2): ?>
                <div class="multislide">
                    <section class="div_tarjetas_profes">
                        <?php if (isset($profesores[$i])): ?>
                            <section class="tarjeta_profe">
                                <section class="datos_profe">
                                    <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($profesores[$i]['imagen_profesor']); ?>" alt="<?php echo htmlspecialchars($profesores[$i]['nombre_profesor']); ?>">
                                    <p><b>Prof. <?php echo htmlspecialchars($profesores[$i]['nombre_profesor'] . ' ' . $profesores[$i]['apellido_profesor']); ?></b></p>
                                </section>
                                <p><?php echo htmlspecialchars($profesores[$i]['descripcion']); ?></p>
                                <section class="contactos_profe">
                                    <section class="datos_contactos_profe">
                                        <i class='bx bx-phone'></i> <p><?php echo htmlspecialchars($profesores[$i]['telefono_profesor']); ?></p>
                                    </section>
                                    <section class="datos_contactos_profe">
                                        <i class='bx bx-envelope'></i> <p><?php echo htmlspecialchars($profesores[$i]['correo']); ?></p>
                                    </section>
                                </section>
                            </section>
                        <?php endif; ?>

                        <?php if (isset($profesores[$i + 1])): ?>
                            <section class="tarjeta_profe">
                                <section class="datos_profe">
                                    <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($profesores[$i + 1]['imagen_profesor']); ?>" alt="<?php echo htmlspecialchars($profesores[$i + 1]['nombre_profesor']); ?>">
                                    <p><b>Prof. <?php echo htmlspecialchars($profesores[$i + 1]['nombre_profesor'] . ' ' . $profesores[$i + 1]['apellido_profesor']); ?></b></p>
                                </section>
                                <p><?php echo htmlspecialchars($profesores[$i + 1]['descripcion']); ?></p>
                                <section class="contactos_profe">
                                    <section class="datos_contactos_profe">
                                        <i class='bx bx-phone'></i> <p><?php echo htmlspecialchars($profesores[$i + 1]['telefono_profesor']); ?></p>
                                    </section>
                                    <section class="datos_contactos_profe">
                                        <i class='bx bx-envelope'></i> <p><?php echo htmlspecialchars($profesores[$i + 1]['correo']); ?></p>
                                    </section>
                                </section>
                            </section>
                        <?php endif; ?>
                    </section>
                </div>
            <?php endfor; ?>
        </div>
        <button class="nav-button prev-button" onclick="navigateSlider('sliderpr', -1)">←</button>
        <button class="nav-button next-button" onclick="navigateSlider('sliderpr', 1)">→</button>
        <br><br>
    </div>
    <br>
</div>



<div class="space"></div>





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
        
        <div class="boton_contactos btn_contactos_animacion btn_contactos_clr3" onclick="toggleDiv(0,'oculto3',500)">
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

<div class="oculto" id="oculto3">
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
                    <section class="multislide flex_def" style="overflow:visible;">        
                        <a style="margin:auto;overflow:visible;" href="<?= mainModel::obtenerRutaImagenLocal('mapa_google') ?>" target="_blank">
                            <img loading="lazy" class="mapa_desplazante" width="91%" src="<?= mainModel::obtenerRutaImagenLocal('mapa_google') ?>">
                        </a>
                    </section>
         
                    <section class="multislide flex_def"><!--<?php echo htmlspecialchars($imagenesLocal['mapa_vista_satelital'], ENT_QUOTES, 'UTF-8'); ?>-->
                        <a style="margin:auto;" href="<?= mainModel::obtenerRutaImagenLocal('mapa_vista_satelital') ?>" target="_blank">
                            <img loading="lazy" class="mapa_desplazante" width="91%" src="<?= mainModel::obtenerRutaImagenLocal('mapa_vista_satelital') ?>">
                        </a>
                    </section>            
            	</section>
			</section>
        
        </section>
	</div>
</div>
<!--aqui terminan los mapas-->





<div class="slidiorr">
    <div class="slide-trackior">
        
        <?php foreach ($imgn1 as $image): ?>
            <?php if ($image): ?>
                <div class="slidio">
                    <img loading="lazy" src="<?php echo htmlspecialchars($image); ?>" alt="Slider Image">
                </div>
            <?php else: ?>
                <p>No se encontró la imagen <?php echo htmlspecialchars($image); ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php foreach ($imgn1 as $image): ?>
            <?php if ($image): ?>
                <div class="slidio">
                    <img loading="lazy" src="<?php echo htmlspecialchars($image); ?>" alt="Slider Image">
                </div>
            <?php else: ?>
                <p>No se encontró la imagen <?php echo htmlspecialchars($image); ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
        
    </div>  
</div>




<div class="footer_fondo" style="background-image: url('<?= mainModel::obtenerRutaImagenLocal('img_footer') ?>');">
    <!--p><c?php echo inicioLocalText('txt_titulo_footer', 'Hoy somos reconocidos como la academia más grande del Estado Guárico,
 contando con modalidades de estudio permanentes reconocidas nacional e internacionalmente. Tenemos convenios importantes con fundaciones y universidades nacionales como la UPEL,
 la UNERG, la Fundación Casa Arturo Uslar Prieti y la Fundación Banesco.
 Estamos registrados formalmente como academia formadora de emprendedores ante el INCES y pertenecemos al Sistema Nacional de Emprendedores e Innovadores.', $textosLocal); ?></p-->
    <p><?php echo htmlspecialchars($textosLocal['txt_titulo_footer'], ENT_QUOTES, 'UTF-8'); ?></p>
</div>

<div class="creditos footer">
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
            <img src="<?= mainModel::obtenerRutaImagenLocal('logo') ?>" alt="porqueno">
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

//  registra la interaccion y redirije  
function registrarYRedirigir(idEncriptado) {
    fetch('<?= SERVERURL ?>ajax/publicoAjax.php?accion=registrar_interaccion&id=' + encodeURIComponent(idEncriptado))
        .then(res => res.json())
        .then(data => {
            // Redirigir a la vista de detalle con el id encriptado
            window.location.href = '<?= SERVERURL ?>index.php?views=cursos-detail&id=' + encodeURIComponent(idEncriptado);
        })
        .catch(() => {
            // Si falla el registro, igual redirigimos
            window.location.href = '<?= SERVERURL ?>index.php?views=cursos-detail&id=' + encodeURIComponent(idEncriptado);
        });
}
    
    
//boton de ir arriba
var cajax=document.getElementById("ir_arriba");
cajax.addEventListener("click",function(){
	document.documentElement.scrollTop=0;
	})

window.addEventListener("scroll",function(){
	
	if(document.documentElement.scrollTop >3000){cajax.style.display="flex"}
	else{cajax.style.display="none"}
	})



//multislide
function navigateSlider(sliderId, direction) {
  const slider = document.getElementById(sliderId);
  const sliderContent = slider.querySelector('.multislider-content');
  const slides = slider.querySelectorAll('.multislide');
  const totalSlides = slides.length;
  let currentIndex = parseInt(slider.getAttribute('data-current-index') || 0);

  // Actualizar el índice actual
  currentIndex += direction;

  if (currentIndex < 0) {
    currentIndex = totalSlides - 1;
  } else if (currentIndex >= totalSlides) {
    currentIndex = 0;
  }

  slider.setAttribute('data-current-index', currentIndex);

  const offset = -currentIndex * 100;
  sliderContent.style.transform = `translateX(${offset}%)`;
}

const sliderContainers = document.querySelectorAll('.multislider-container');
sliderContainers.forEach((slider) => {
  setInterval(() => {
    navigateSlider(slider.id, 1);
  }, 5000); 
});




//funcion de ocultar definitiva
function toggleDiv(index, ocultoId, alturaVisible) {
  var oculto = document.getElementById(ocultoId);
  var divs = oculto.getElementsByTagName("div");

  if (divs[index].classList.contains("show")) {
    divs[index].classList.remove("show");
    oculto.classList.remove("show");
    setTimeout(() => {
      oculto.style.height = "0px"; 
      oculto.style.opacity = "0";
    }, 500);
  } else {
    for (var i = 0; i < divs.length; i++) {
      divs[i].classList.remove("show");
    }
    divs[index].classList.add("show");
    oculto.classList.add("show");
    oculto.style.height = alturaVisible + "px"; 
    oculto.style.opacity = "1"; 
  }
}
    //aqui termina la funcion de ocultar definitiva

    
    
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

//nuevoslider
    const newslides = document.querySelector('.newslides');
    const newimages = document.querySelectorAll('.newslides img');
    const newprevBtn = document.getElementById('previBtn');
    const newnextBtn = document.getElementById('nextiBtn');
    
    let currentIndexxx = 0;


const sliderWidth = document.querySelector('.newslider').clientWidth;

function updateSlider() {
    newslides.style.transform = `translateX(${-currentIndexxx * sliderWidth}px)`;
}


    newprevBtn.addEventListener('click', () => {
        currentIndexxx = (currentIndexxx > 0) ? currentIndexxx - 1 : newimages.length - 1;
        updateSlider();
    });

    newnextBtn.addEventListener('click', () => {
        currentIndexxx = (currentIndexxx < newimages.length - 1) ? currentIndexxx + 1 : 0;
        updateSlider();
    });

	//fin de nuevoslider

</script>