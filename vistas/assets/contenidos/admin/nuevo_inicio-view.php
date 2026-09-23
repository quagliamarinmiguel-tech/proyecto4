<?php

require_once "./controladores/publicoControlador.php";
$inicioCtrl = new publicoControlador();
//$datosInicio = $inicioCtrl->cargar_inicio_controlador();

require_once "./controladores/localControlador.php";
$localCtrl = new localControlador();
$localRespuesta = $localCtrl->datos_local_controlador();
$local = is_array($localRespuesta) && !empty($localRespuesta['ok']) && is_array($localRespuesta['data'])
    ? $localRespuesta['data']
    : [];
$textosLocal = [];
if (!empty($local['textos_pagina'])) {
    $textosLocal = is_array($local['textos_pagina'])
        ? $local['textos_pagina']
        : json_decode((string) $local['textos_pagina'], true);
    $textosLocal = is_array($textosLocal) ? $textosLocal : [];
}
$imagenesLocal = $localCtrl->obtener_imagenes_pagina_controlador();
$imagenesLocal = is_array($imagenesLocal) ? $imagenesLocal : [];

function inicioLocalText(string $key, string $fallback, array $textos): string {
    $value = $textos[$key] ?? '';
    return is_scalar($value) && trim((string) $value) !== ''
        ? htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
        : $fallback;
}

function inicioLocalImage(string $key, string $fallback, array $imagenes): string {
    $ruta = trim((string) ($imagenes[$key] ?? ''));
    if ($ruta === '') {
        return $fallback;
    }
    if (preg_match('/^https?:\/\//i', $ruta)) {
        return htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8');
    }
    return htmlspecialchars(SERVERURL . 'vistas/' . ltrim($ruta, '/'), ENT_QUOTES, 'UTF-8');
}

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
$coloresInicio = ['#00a9f1', '#002a6f', '#72F2EB', '#FF5F5D', '#3ca6a6'];
$coloresLocal = $local['colores_pagina'] ?? [];
$coloresLocal = is_array($coloresLocal) ? $coloresLocal : json_decode((string) $coloresLocal, true);
$paletaInicioId = is_array($coloresLocal) ? (int) ($coloresLocal['paleta_id'] ?? 0) : 0;
$coloresInicio = $paletasInicio[$paletaInicioId] ?? $coloresInicio;

//$cursos = $datosInicio["curso"];
$profesores = $datosInicio["profesores"];
$cursos = $datosInicio["curso"];
$imgn1 = $datosInicio["slider"]["slider_1"];
$imgn2 = $datosInicio["slider"]["slider_2"];
$imgn3 = $datosInicio["slider"]["slider_3"];
$info = $datosInicio["infoceproca"];
$telefonoInicio = htmlspecialchars((string) ($local['telefono_contacto'] ?? $info['telefono_contacto'] ?? ''), ENT_QUOTES, 'UTF-8');
$correoInicio = htmlspecialchars((string) ($local['correo_contacto'] ?? $info['correo_contacto'] ?? ''), ENT_QUOTES, 'UTF-8');
$facebookInicio = htmlspecialchars((string) ($local['link_facebook'] ?? $info['link_facebook'] ?? '#'), ENT_QUOTES, 'UTF-8');
$instagramInicio = htmlspecialchars((string) ($local['link_instagram'] ?? $info['link_instagram'] ?? '#'), ENT_QUOTES, 'UTF-8');
$ciudadInicio = htmlspecialchars((string) ($local['direccion_local_ciudad'] ?? $info['direccion_local_ciudad'] ?? ''), ENT_QUOTES, 'UTF-8');
$paisInicio = htmlspecialchars((string) ($local['direccion_local_pais'] ?? $info['direccion_local_pais'] ?? ''), ENT_QUOTES, 'UTF-8');







require_once "./vistas/inc/utilidadesPublicas.php"; // Ruta válida desde index.php

$pdo = conectarBase();
$ip = $_SERVER['REMOTE_ADDR'];
$idIP = registrar_ip_visitante($pdo, $ip);
registrar_visita($pdo, $idIP);



?>

<style>
    :root {
        --inicio-primario: <?php echo $coloresInicio[0]; ?>;
        --inicio-secundario: <?php echo $coloresInicio[1]; ?>;
        --inicio-terciario: <?php echo $coloresInicio[2]; ?>;
        --inicio-cuaternario: <?php echo $coloresInicio[3]; ?>;
        --inicio-quinario: <?php echo $coloresInicio[4]; ?>;
    }
    .div_header { background-color: var(--inicio-primario); }
    .div_header .header_barra ul li a,
    .div_header .contactos_header { background-color: var(--inicio-primario); }
    .div_header .header_barra ul li a:hover,
    .div_header .contactos_header:hover { background-color: var(--inicio-terciario); }
    .texto_header_cursos_100_practicos h1 { color: var(--inicio-secundario); }
    .nuevoslider { border-color: var(--inicio-quinario); }
    .hr_linea_color, .hr_encabezado { background-color: var(--inicio-terciario) !important; }
    .espacio_periodicos .encabezado1, .espacio_periodicos .encabezado2 { border-color: var(--inicio-quinario); }
    .nada3 h3, .flex_def h1 { color: var(--inicio-secundario); }
    .div_contactos .btn_contactos_clr1, .div_contactos .btn_contactos_clr4 { background-color: var(--inicio-primario); }
    .div_contactos .btn_contactos_clr2, .div_contactos .btn_contactos_clr5 { background-color: var(--inicio-terciario); }
    .div_contactos .btn_contactos_clr3 { background-color: var(--inicio-quinario); }
    .footer_fondo { box-shadow: 0 6px var(--inicio-primario), 0 14px var(--inicio-secundario), 0 22px var(--inicio-terciario); }
    .creditos.footer { background-color: var(--inicio-primario); }
    .tarjeta_div_precio_cupos i { color: var(--inicio-quinario) !important; }
</style>

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

<header class="inicio_header_imagen_ceproca" style="background-image: url('<?php echo inicioLocalImage('img_header', '../vistas/assets/img/talves4.webp', $imagenesLocal); ?>');">
    <p><?php echo inicioLocalText('txt_header', 'SOMOS UNA INSTITUCIÓN EDUCATIVA DEDICADA A LA FORMACIÓN DE CIUDADANOS ÍNTEGROS, OFRECIENDO CURSOS ADAPTADOS PARA ESTUDIANTES DE DIVERSAS EDADES, QUIENES, MEDIANTE LA EDUCACIÓN CON UN SENTIDO ÚTIL Y PRÁCTICO, BUSCAN REINVENTARSE PARA AVANZAR Y PRODUCIR.', $textosLocal); ?></p>
</header>







<!--aqui empiezan los botones de cursos-->
<header class="texto_header_cursos_100_practicos">
    <div class="texto_header_cursos_100_practicos_div1"></div>
    	<h1><?php echo inicioLocalText('txt_titulo_post_header', 'VEN Y FORMA PARTE DE LA ACADEMIA MÁS GRANDE EL ESTADO GUÁRICO', $textosLocal); ?></h1>
        	<h1><?php echo inicioLocalText('txt_subtitulo_post_header', 'CURSOS 100% PRÁCTICOS', $textosLocal); ?></h1>
    <div class="texto_header_cursos_100_practicos_div2"></div>
</header>

<!--section class="caracteristicas_cursos">
    <div class="buton_caracteristicas_cursos b1 bbb" onclick="toggleDiv(0,'oculto1',400)"><p>Sabatinos</p></div>
    <div class="buton_caracteristicas_cursos b2 bbb" onclick="toggleDiv(1,'oculto1',400)"><p>Dias de semana</p></div>
    <div class="buton_caracteristicas_cursos b3 bbb" onclick="toggleDiv(2,'oculto1',400)"><p>Intensivos</p></div>
    <div class="buton_caracteristicas_cursos b4 bbb" onclick="toggleDiv(3,'oculto1',400)"><p>Técnicos Profesionales</p></div>
    <div class="buton_caracteristicas_cursos b5 bbb" onclick="toggleDiv(4,'oculto1',400)"><p>Cursos para niños</p></div>
</section>

<div class="oculto" id="oculto1">
    <div>
  	    <section class="texto_caracteristicas_curso b1_static">
	        <i class='bx bxs-brightness'></i>    
	        <p>En solo unos cuantos fines de semana, verás un cambio significativo en tus habilidades y conocimientos. Ya sea que busques mejorar en tu carrera, emprender un nuevo proyecto o simplemente aprender algo nuevo.</p> 
 	        <br>
        </section> 
        <section class="imagen_caracteristicas_curso">
            <img loading="lazy" src="../vistas/assets/img/finde.webp"/>
        </section>
    </div>
  		 
    <div>	
  		<section class="texto_caracteristicas_curso b2_static">
            <i class='bx bx-calendar'></i>
	        <p>La regularidad en las clases puede ayudar a mantener alta la motivación. Al ver avances y recibir retroalimentación constante, es más fácil mantenerse enfocado en tus objetivos y seguir comprometido con el curso.</p> 
 	        <br>
 		</section>    
        <section class="imagen_caracteristicas_curso">
            <img loading="lazy" src="../vistas/assets/img/barbero5.webp"/>
        </section>
    </div>
  
    <div> 
  		<section class="texto_caracteristicas_curso b3_static">
            <i class='bx bxs-timer'></i>
            <p>los cursos intensivos están diseñados para cubrir una gran cantidad de contenido en un corto período de tiempo. Esto significa que puedes adquirir habilidades y conocimientos rápidamente, lo que es ideal si necesitas aprender algo nuevo para un trabajo o proyecto específico.</p>
 	        <br>
 		</section>    
  		<section class="imagen_caracteristicas_curso">
            <img loading="lazy" src="../vistas/assets/img/barbero4.webp"/>
        </section>
    </div>

    <div> 
		<section class="texto_caracteristicas_curso b4_static">
	        <i class='bx bxs-graduation'></i>
            <p>Los programas técnicos suelen actualizarse más rápidamente para adaptarse a las demandas cambiantes del mercado, asegurando que los estudiantes aprendan las habilidades más relevantes y actuales.</p>
 	        <br>
        </section>       
  		<section class="imagen_caracteristicas_curso">
            <img loading="lazy" src="../vistas/assets/img/tecnico2.webp"/>
        </section>  
    </div>

    <div> 
  	    <section class="texto_caracteristicas_curso b5_static">
            <i class='bx bx-child'></i>
	        <p>Los cursos proporcionan un entorno en el que los niños y jóvenes pueden hacer amigos y aprender a trabajar en equipo donde aprenden a comunicarse, compartir y colaborar con otros niños, lo cual es crucial para su desarrollo social.</p>
 	        <br>
        </section>  
  		<section class="imagen_caracteristicas_curso">
            <img loading="lazy" src="../vistas/assets/img/ingles4.webp"/>
        </section>   
    </div>

</div--><!--info oculto de caracteristicas de cursos-->















<div class="div_img_verticales_y_tarjetas">

    <!--este es el slider de imagenes verticales-->
	<div class="multislider-container" id="slidio6">
  		<div class="multislider-content height100" >
            <!--style="margin:auto;"-->
            <div class="multislide height100 div_imagenes_verticales">        
              <img class="imagenes_verticales" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn3[0]); ?>" alt="Imagen 1">
            </div>
    
            <div class="multislide div_imagenes_verticales" >
              <img class="imagenes_verticales" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn3[1]); ?>" alt="Imagen 2">
              <img class="imagenes_verticales" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn3[2]); ?>" alt="Imagen 3">
            </div>
                         
            <div class="multislide height100 div_imagenes_verticales">
              <img class="imagenes_verticales" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn3[3]); ?>" alt="Imagen 4">
            </div>
                
            <div class="multislide div_imagenes_verticales">
              <img class="imagenes_verticales" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn3[4]); ?>" alt="Imagen 5">
              <img class="imagenes_verticales" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn3[5]); ?>" alt="Imagen 6">
            </div> 
		</div>
	</div>

	<!--aqui empiezan tarjetas-->
    <div class="tarjetas">
        <div class="tarjconteiners">
            <?php foreach ($cursos as $curso): ?>
                <div class="card" onclick="redirigirACurso(<?php echo $curso['id_materia']; ?>, <?php echo $curso['id_materia']; ?>, 1)">
                    <figure>
                        <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($curso['ruta_materia']); ?>" alt="Imagen del curso">
                    </figure>
                    <div class="contenido">
                        <h4><?php echo htmlspecialchars($curso['nombre_curso']); ?></h4>
                        <p><?php echo htmlspecialchars($curso['prefacio']); ?></p>

                            <?php
                                $inscripcion = (float)$curso['pago_inscripcion'] ?? 0;
                                $modulo      = (float)$curso['pago_modulo'] ?? 0;
                                $oferta      = (int)$curso['oferta_vigente'] ?? 0;

                                $pago_total = $inscripcion + ($modulo * 3); // precio sin descuento
                                $pago_y_oferta = $pago_total;
                                $curso['pago_total'] = $pago_total;
                            ?>

                        <div class="tarjeta_div_precio_cupos">
                                <p>Precio: <?php echo htmlspecialchars($curso['pago_total']); ?>$</p><i class='bx bxs-badge-dollar' style='color:orange;'></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
</div>
<!--aqui terminan tarjetas-->

<br>
<a class="ver_mas" href="http://www.cecilioceproca.wuaze.com/index.php?views=cursos">Ver más</a>
<div id="ir_arriba"><i class='bx bxs-home-alt-2'></i></div>










<!--primer slider-->
<!--div class="nuevoslider">
    <div class="primer_slider_texto">
        <h1>No solo nos dedicamos a la formación profesional, sino que también jugamos un papel activo en la comunidad, promoviendo el desarrollo integral de nuestros estudiantes y fortaleciendo los lazos sociales en Valle de la Pascua.</h1>
    </div>
        
    <div class="newslider width400">
        <div class="newslides width400">
                
            <?php for ($i = 1; $i <= 8; $i++): ?><!--images_automatico3->
                <img class="width400" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn2[$i-1]); ?>" alt="Imagen del Slider">
            <?php endfor; ?>
                            
        </div>
        <div class="newnavigation">
            <button class="nav-btn" id="previBtn">⟨</button>
            <button class="nav-btn" id="nextiBtn">⟩</button>
            </div>
        </div>
    </div>
</div-->












<div class="space nada"></div>
<div class="space nada">
	<h1 class="flex_def">Más sobre Ceproca</h1><br><br style="height:5vh;">
</div>


<!--aqui empiezan los periodicos->
<div class="espacio_periodicos">
	<div class="encabezados_periodicos div_periodicos">
        
    	<div class="encabezado1 car" onclick="toggleDiv(0,'oculto2',650)">
        	<p><b class="subtitulo_encabezado1">¿Quiénes Somos?</b></p>
            <hr class="hr_linea_color" style="background-color:#039;">
            <img loading="lazy" src="../vistas/assets/img/viejaceproca.webp" style="margin-top:0px">
            <p>El Centro Educativo Profesional Cecilio Acosta (CEPROCA) es una institución educativa ubicada en Valle de la Pascua, estado Guárico, Venezuela Se caracteriza por su enfoque en la formación técnica y profesional...</p>
        </div>
        
        <div class="encabezado1 car" onclick="toggleDiv(1,'oculto2',650)">
        	<p><b class="subtitulo_encabezado2">¿Cuál es nuestro Objetivo?</b> Tenemos como misión principal proporcionar una educación técnica y profesional de alta calidad que responda a las necesidades del mercado laboral y contribuya al desarrollo integral de sus estudiantes.</p>
            <img loading="lazy" src="../vistas/assets/img/objetivo.webp">
            <hr class="hr_linea_color" style="background-color:#C33;">
        </div>
        
        <div class="encabezado2 car" onclick="toggleDiv(2,'oculto2',650)">
        	<p><b>¿Quién es Cecilio Acosta?</b> Destacado intelectual y escritor venezolano, nacido el 15 de diciembre de 1818 en La Guaira, Venezuela. Es conocido por su contribución a la literatura, la educación y la cultura en el país durante el siglo XIX.</p>
            <img loading="lazy" class="cecilio_acosta_foto" src="../vistas/assets/img/cecilio2.webp">
        </div>
        
	</div>
</div>


<div class="oculto" id="oculto2">
	<div>
    	<section class="espacio_periodicos">
    		<section class="encabezados_periodicos" style="margin-left:0%;">
                
    			<section class="encabezado1">
    				<p style="padding: 10px 10px;">ofreciendo una variedad de cursos y programas que buscan equipar a los estudiantes con habilidades prácticas y conocimientos relevantes para el mercado laboral. CEPROCA ofrece una amplia gama de cursos permanentes en diversas áreas, lo que permite a los estudiantes elegir formaciones que se alineen con sus intereses y necesidades profesionales. Estos cursos abarcan desde la formación en áreas técnicas hasta programas de desarrollo personal y profesional. </p>
                    <hr class="hr_encabezado">
                </section>
         
            	<section class="encabezado1">
					<p>La institución también se involucra en actividades comunitarias y eventos que promueven la educación y el desarrollo social en la región.</p><p>El centro no solo se enfoca en la educación formal, sino que también busca crear un ambiente de comunidad y colaboración. A menudo, organizan eventos y actividades que fomentan la interacción entre estudiantes, profesores y la comunidad local. Esto ayuda a construir una red de apoyo y a fortalecer los lazos sociales entre los participantes.</p>
                    <hr class="hr_encabezado">
                </section>
                
            	<section class="encabezado2">
    				<p>El equipo de CEPROCA está compuesto por profesionales capacitados y comprometidos con la educación. Los docentes son seleccionados por su experiencia en sus respectivas áreas y su capacidad para impartir conocimientos de manera efectiva. Esto asegura que los estudiantes reciban una educación de calidad que les prepare adecuadamente para sus futuros laborales.</p>
                    <br><hr class="hr_encabezado">
                </section>
                
            </section>    
        </section>
    </div>
    
	<div>
    	<section class="espacio_periodicos">
    		<section class="encabezados_periodicos" style="margin-left:0%;">
                
    			<section class="encabezado1">
    				<p>Tenemos como misión principal proporcionar una educación técnica y profesional de alta calidad que responda a las necesidades del mercado laboral y contribuya al desarrollo integral de sus estudiantes. Su enfoque está en formar profesionales competentes que no solo posean conocimientos teóricos, sino también habilidades prácticas que les permitan destacarse en sus respectivas áreas.</p>
					<p><b class="subtitulo_encabezado3">Entre nuestras metas principales tenemos...</b></p>
					<hr class="hr_encabezado">
        		</section>
                
                <section class="encabezado1">
    				<p>◦ Fomentar el desarrollo personal y profesional de sus estudiantes, ayudándoles a alcanzar sus metas y a mejorar su calidad de vida.</p>
                    <p>◦ Contribuir al desarrollo económico y social de la comunidad de Valle de la Pascua, formando profesionales que puedan satisfacer las demandas del mercado laboral local.</p>
                    <p>◦ Promover la inclusión y la equidad en la educación, asegurando que todos los interesados tengan acceso a oportunidades de formación, independientemente de su contexto socioeconómico.</p>
                 	<hr class="hr_encabezado" >
        		</section>
                
                <section class="encabezado2">
    				<p>◦ Establecer alianzas estratégicas con empresas y organizaciones locales para facilitar la inserción laboral de sus egresados y asegurar que los programas educativos estén alineados con las necesidades del sector productivo.</p>
                    <img class="imagen4_encabezado" loading="lazy" src="../vistas/assets/img/paisaje.webp"> 
                    <br> <hr class="hr_encabezado" style="margin-top:5px;">
        		</section>
                
            </section>
        </section>
    </div>
        
	<div>
    	<section class="espacio_periodicos">
    		<section class="encabezados_periodicos" style="margin-left:0%;">
                
    			<section class="encabezado1">
    				<p>Cecilio Acosta se caracterizaba por su profundo compromiso con la educación y la promoción del pensamiento crítico. Fue un ferviente defensor de la libertad de expresión y de la importancia de la educación como herramienta de progreso social. Su obra abarcó diversos géneros, incluyendo ensayos, artículos periodísticos y obras literarias, donde abordó temas como la moral, la política y la filosofía.</p>
                    <hr class="hr_encabezado">
        		</section>
                
                <section class="encabezado1">
    				<p>"El espíritu de la letra": Un ensayo que reflexiona sobre la importancia de la educación y la cultura.</p>
                    <p>"La educación pública": Un texto en el que expone sus ideas sobre la necesidad de una educación accesible y de calidad para todos.</p>
                    <p> "Cartas a un amigo": Una serie de cartas que abordan temas filosóficos y sociales, mostrando su pensamiento crítico y su visión sobre la sociedad venezolana.</p>
                    <hr class="hr_encabezado">
        		</section>
                
                <section class="encabezado2">
    				<p>Cecilio Acosta se destacó no solo como escritor, sino también como educador y periodista. Fue un pionero en la promoción de la educación laica en Venezuela y participó activamente en la creación de instituciones educativas. Su influencia se extendió a través de sus escritos y su labor en la prensa, donde defendió ideales de justicia social y equidad.</p>
                    <hr class="hr_encabezado" >
        		</section>
                
            </section>
        </section>
    </div>
</div>
<!--aqui terminan los periodicos-->

            <!-- SECCIÓN: Datos del local o la empresa -->
            <fieldset>
                <legend><i class="fas fa-sliders-h"></i> Información del local</legend>

                <div class="nuevoslider">
                    <div class="col-md-6 mb-3 primer-slider-texto bg-white-custom p-20">
                        <label class="form-label mt-2">Imagen 1</label>
                        <input type="file" name="img_desc1" class="form-control-file"
                            accept=".jpg,.jpeg,.png,.webp">
                        <hr>
                        <label class="form-label">Título 1</label>
                        <input type="text" class="form-control" name="txt_titulo_info1" maxlength="255" value="<?php echo inicioLocalText('txt_titulo_info1', '', $textosLocal); ?>">
                        <hr>
                        <label class="form-label">Subtítulo 1</label>
                        <input type="text" class="form-control" name="txt_subtitulo_info1" maxlength="255" value="<?php echo inicioLocalText('txt_subtitulo_info1', '', $textosLocal); ?>">
                    </div>
                    <div class="newslider width400">
                        <img class="width400" loading="lazy"
                            src="<?php echo inicioLocalImage('img_desc1', '../vistas/assets/img/default.webp', $imagenesLocal); ?>"
                            alt="Imagen del Slider 1">
                    </div>
                </div>

                <!-- datos 2 -->
                <div class="nuevoslider">
                    <div class="newslider width400">
                        <img class="width400" loading="lazy"
                            src="<?php echo inicioLocalImage('img_desc2', '../vistas/assets/img/default.webp', $imagenesLocal); ?>"
                            alt="Imagen del Slider 2">
                    </div>
                    <div class="col-md-6 mb-3 primer-slider-texto bg-white-custom p-20">
                        <label class="form-label mt-2">Imagen 2</label>
                        <input type="file" name="img_desc2" class="form-control-file"
                            accept=".jpg,.jpeg,.png,.webp">
                        <hr>
                        <label class="form-label">Título 2</label>
                        <input type="text" class="form-control" name="txt_titulo_info2" maxlength="255" value="<?php echo inicioLocalText('txt_titulo_info2', '', $textosLocal); ?>">
                        <hr>
                        <label class="form-label">Subtítulo 2</label>
                        <input type="text" class="form-control" name="txt_subtitulo_info2" maxlength="255" value="<?php echo inicioLocalText('txt_subtitulo_info2', '', $textosLocal); ?>">
                    </div>
                </div>

                <!-- datos 3-->
                <div class="nuevoslider">
                    <div class="col-md-6 mb-3 primer-slider-texto bg-white-custom p-20">
                        <label class="form-label mt-2">Imagen 3</label>
                        <input type="file" name="img_desc3" class="form-control-file"
                            accept=".jpg,.jpeg,.png,.webp">
                        <hr>
                        <label class="form-label">Título 3</label>
                        <input type="text" class="form-control" name="txt_titulo_info3" maxlength="255" value="<?php echo inicioLocalText('txt_titulo_info3', '', $textosLocal); ?>">
                        <hr>
                        <label class="form-label">Subtítulo 3</label>
                        <input type="text" class="form-control" name="txt_subtitulo_info3" maxlength="255" value="<?php echo inicioLocalText('txt_subtitulo_info3', '', $textosLocal); ?>">
                    </div>
                    <div class="newslider width400">
                        <img class="width400" loading="lazy"
                            src="<?php echo inicioLocalImage('img_desc3', '../vistas/assets/img/default.webp', $imagenesLocal); ?>"
                            alt="Imagen del Slider 3">
                    </div>
                </div>
            </fieldset>
<div class="space"></div>
    
    
    
    
    
    
    
    
    
    









<!--facebook y comentarios-->

<!-- style="width:100%;flex-wrap:wrap;"-->
<div class="div_facebook_y_comentarios flex_def">

    
    <!-- style="overflow:hidden;border-radius:8px;width:300px;height:535px;margin:4%;"-->
    <div class="div_facebook">
    <!--style="width:300px;height:550px;"-->    
    	<iframe class="pedro div_iframe_facebook" src="https://www.facebook.com/plugins/video.php?height=476&href=https%3A%2F%2Fwww.facebook.com%2Freel%2F2854427401381978%2F&show_text=false&width=267&t=0" width="267" height="476" style="border:none;overflow:hidden;" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowFullScreen="true"></iframe>
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
                            	<p><b>Juan Osorio</b></p>
                        	</section>
                        <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut"</p>
                    	</section>	

                		<section class="cmt cmt_1_2">
                        	<section class="datos_cmt">
                            	<p><b>Angela Arevalo</b></p>
                        	</section>
                        <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."</p>
                   		</section>
                	</section>
                    
                    <section class="cmt_unitario cmt_unitario_1_1">
                        <section class="datos_cmt">
                                <p><b>Luán Gomez</b></p>
                        </section>
                        <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation uLorem ipsum dolor sit amet, consectetur"</p>
                    </section>
                    
            	</div>
      		</div>



            <div class="multislide clase-extra">
                <div class="bloque_cmts">
                    
                    <section class="cmt_doble cmt_doble_2_1">
                        <section class="cmt cmt_2_1">
                            <section class="datos_cmt">
                                <p><b>Carla Carvallo</b></p>
                            </section>
                            <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation uLorem ipsum dolor sit amet, consectetur ad"</p>
                        </section>	
                        
                        <section class="cmt cmt_2_2">
                            <section class="datos_cmt">
                                <p><b>Lia Juarez</b></p>
                            </section>
                            <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore"</p>
                        </section>
                    </section>

                    <section class="cmt_doble cmt_doble_2_2">
                        <section class="cmt cmt_2_3">
                            <section class="datos_cmt">
                                <p><b>Prof. Pedro Parques</b></p>
                            </section>
                            <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed"</p>
                        </section>	

                        <section class="cmt cmt_2_4">
                            <section class="datos_cmt">
                                <p><b>Adrian Villegas</b></p>
                            </section>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation uLorem ipsum"</p>
                        </section>
                    </section>
                    
                </div>
            </div>



            <div class="multislide clase-extra">
                <div class="bloque_cmts" style="flex-wrap:nowrap;flex-direction:row;">
                    
                    <section class="cmt_doble cmt_doble_3_1">
                        <section class="cmt cmt_3_1">
                            <section class="datos_cmt">
                                <p><b>Silvia Suarez</b></p>
                            </section>
                            <p>"orem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna"</p>
                        </section>	

                        <section class="cmt cmt_3_2" >
                            <section class="datos_cmt">
                                <p><b>Armando Torrealba</b></p>
                            </section>
                            <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veni"</p>
                        </section>
                    </section>

                    <section class="cmt_unitario cmt_unitario_3_1">
                        <section class="datos_cmt">
                            <p><b>Eduard Padrón</b></p>
                        </section>
                        <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation u"</p>
                    </section>
                    
                </div>
            </div>

            
            
            <div class="multislide otra-clase">
                <div class="bloque_cmts">
                    
                    <section class="cmt_unitario cmt_unitario_4_1">
                        <section class="datos_cmt">
                            <p><b>Andresio herreira</b></p>
                        </section>
                        <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim "</p>
                    </section>

                    <section class="cmt_unitario cmt_unitario_4_2">
                        <section class="datos_cmt">
                            <p><b>Olivio del Soto</b></p>
                        </section>
                        <p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation uLorem ipsum dolor sit amet, co"</p>
                    </section>

                    <section class="cmt_unitario cmt_unitario_4_3">
                        <section class="datos_cmt">
                            <p><b>Prof. Ignacio Rosales</b></p>
                        </section>
                        <p>"Gracias al curso horneo un pan para mi abuela todos los fines de semana."</p>
                    </section>
                    
                </div>
            </div>

        </div>
    </div>
</div><!--aqui terminan los comentarios y periodicos-->

<div class="space"></div>







<!--los profesores-->
<div class="flex_def div_slider_profes">
    <div class="multislider-container" id="sliderpr" style="width:80%;">
        <div class="multislider-content" style="height:100%;">
            
            <?php for ($i = 0; $i < count($profesores); $i += 2): ?>

                <div class="multislide">     
                    <section class="div_tarjetas_profes">

                        <?php if (isset($profesores[$i])): ?>
                            <section class="tarjeta_profe">

                                <section class="datos_profe">
                                    <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($profesores[$i]['imagen_profesor']); ?>" alt="<?php echo htmlspecialchars($profesores[$i + 1]['nombre_profesor']); ?>">
                                    <p><b>Prof. <?php echo htmlspecialchars($profesores[$i]['nombre_profesor'] . ' ' . $profesores[$i]['apellido_profesor']); ?></b></p>
                                </section>

                                <p><?php echo htmlspecialchars($profesores[$i]['descripcion']); ?></p>

                                <section class="contactos_profe">
                                    <section class="datos_contactos_profe" >
                                        <i class='bx bx-phone'></i> <p><?php echo htmlspecialchars($profesores[$i]['telefono_profesor']); ?></p>
                                    </section>
                                    <section class="datos_contactos_profe" >
                                        <i class='bx bx-envelope'></i> <p><?php echo htmlspecialchars($profesores[$i]['correo']); ?></p>
                                    </section>
                                </section>

                            </section>
                        <?php endif; ?>

                        <?php if (isset($profesores[$i + 1])): ?>
                            <section class="tarjeta_profe" >

                                <section class="datos_profe">
                                    <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($profesores[$i + 1]['imagen_profesor']); ?>" alt="<?php echo htmlspecialchars($profesores[$i + 1]['nombre_profesor']); ?>">
                                    <p><b>Prof. <?php echo htmlspecialchars($profesores[$i + 1]['nombre_profesor'] . ' ' . $profesores[$i + 1]['apellido_profesor']); ?></b></p>
                                </section>

                                <p><?php echo htmlspecialchars($profesores[$i + 1]['descripcion']); ?></p>

                                <section class="contactos_profe">
                                    <section class="datos_contactos_profe" >
                                        <i class='bx bx-phone'></i> <p><?php echo htmlspecialchars($profesores[$i + 1]['telefono_profesor']); ?></p>
                                    </section>
                                    <section class="datos_contactos_profe" >
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
        <br>
        <br>
    </div>
    <br>
</div><!--aqui terminan los profes-->






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
                <img loading="lazy" src="<?php echo inicioLocalImage('loca', '../vistas/assets/img/default.webp', $imagenesLocal); ?>">
            </section>
                                                           
            <section class="multislider-container flex_def" id="slidiomapas">
  				<section class="multislider-content" style="overflow:visible;">
                    <!--display:flex;justify-content:center;align-items:center;-->
                    <section class="multislide flex_def" style="overflow:visible;">        
                        <a style="margin:auto;overflow:visible;" href="<?php echo inicioLocalImage('mapa_google', '../vistas/assets/img/default.webp', $imagenesLocal); ?>" target="_blank">
                            <img loading="lazy" class="mapa_desplazante" width="91%" src="<?php echo inicioLocalImage('mapa_google', '../vistas/assets/img/default.webp', $imagenesLocal); ?>">
                        </a>
                    </section>
                    <!-- style="display:flex;justify-content:center;align-items:center;"-->
                    <section class="multislide flex_def">
                        <a style="margin:auto;" href="<?php echo inicioLocalImage('mapa_vista_satelital', '../vistas/assets/img/default.webp', $imagenesLocal); ?>" target="_blank">
                            <img loading="lazy" class="mapa_desplazante" width="91%" src="<?php echo inicioLocalImage('mapa_vista_satelital', '../vistas/assets/img/default.webp', $imagenesLocal); ?>">
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
                    <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($image); ?>" alt="Slider Image">
                </div>
            <?php else: ?>
                <p>No se encontró la imagen <?php echo htmlspecialchars($image); ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php foreach ($imgn1 as $image): ?>
            <?php if ($image): ?>
                <div class="slidio">
                    <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($image); ?>" alt="Slider Image">
                </div>
            <?php else: ?>
                <p>No se encontró la imagen <?php echo htmlspecialchars($image); ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
        
    </div>  
</div>




<!--creditos-->
<div class="footer_fondo" style="background-image: url('<?php echo inicioLocalImage('img_footer', '../vistas/assets/img/graduacion.webp', $imagenesLocal); ?>');">
    <p><?php echo inicioLocalText('txt_titulo_footer', 'Hoy somos reconocidos como la academia más grande del Estado Guárico, contando con modalidades de estudio permanentes reconocidas nacional e internacionalmente. Tenemos convenios importantes con fundaciones y universidades nacionales como la UPEL, la UNERG, la Fundación Casa Arturo Uslar Prieti y la Fundación Banesco. Estamos registrados formalmente como academia formadora de emprendedores ante el INCES y pertenecemos al Sistema Nacional de Emprendedores e Innovadores.', $textosLocal); ?></p>
</div>

<div class="creditos footer">
    <p><?php echo inicioLocalText('txt_ult_titulo_footer', 'Somos el Centro Educativo Profesional Cecilio Acosta', $textosLocal); ?></p>
    <p><?php echo inicioLocalText('txt_ult_subtitulo_footer', '¡Sinónimo de Excelencia!', $textosLocal); ?></p>
    
    
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
            <img src="../vistas/assets/img/logoceprocahd1.webp" alt="porqueno">
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
            <p>© Centro Educativo Profesional Cecilio Acosta.CEPROCA.<?php echo date('Y'); ?></p>
            <p><b class="arroba tooltip" data-tooltip="quaglia.marin.miguel@gmail.com&#10;santiagosan2022@gmail.com">&#128231;</b>  Made by Oliver Martinez and Miguel Quaglia</p>
        </div>
        
    </div>
</div><!--creditos-->




<!--script src="./vistas/js/scriptpublic1.js"></script-->

<script>
function redirigirACurso(idCurso, idBanner,otroValor) {
   //window.location.href = "aiframemateria3-copia.php?id=" + idCurso + "&banner=" + idBanner + "&valor=" + otroValor;
   window.location.href = "vistas/contenidos/public/aiframemateria3-copia.php?id=" + idCurso + "&banner=" + idBanner + "&valor=" + otroValor;
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

    //function updateSlider() {
  //      newslides.style.transform = `translateX(${-currentIndexxx * 400}px)`;
//    }


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
