<?php
    
error_reporting(E_ALL);
ini_set('display_errors', 1);
    
    
//require_once "./controladores/localControlador.php";
//$localCtrl = new localControlador();
//$localRespuesta = $localCtrl->datos_local_publico_controlador(0);
require_once "./controladores/publicoControlador.php";
$localCtrl = new publicoControlador();
$localRespuesta = $localCtrl->datos_local_publico_controlador(0);
//var_dump($localRespuesta);die();


$local = is_array($localRespuesta) && !empty($localRespuesta['ok']) && is_array($localRespuesta['data'])
    ? $localRespuesta['data']
    : [];

$textosLocal = is_array($local['textos_pagina'] ?? null) ? $local['textos_pagina'] : [];

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

    .footer_fondo { box-shadow: 0 6px var(--inicio-secundario), 0 14px var(--inicio-secundario), 0 22px var(--inicio-terciario); }
    .creditos.footer { background-color: var(--inicio-secundario); }
    
</style>
































































<div class="div_header">
	<div style="cursor:pointer;" class="logo_ceproca" onclick="window.location.href='<?php echo SERVERURL; ?>index.php', '_blank';">
        <!--h3><b id="not_ceproca">ceprociando</b></h3-->
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
																		<!--<c?php echo htmlspecialchars($imagenesLocal['img_header_cursos'], ENT_QUOTES, 'UTF-8'); ?>-->
<div class="inicio_header_imagen_ceproca" style="background-image: url('<?= mainModel::obtenerRutaImagenLocal('img_header_cursos') ?>');flex-direction:column;"><!--nueva2--> 
    <h1 class="header_imagen_ceproca_cursos_h1"><?php echo htmlspecialchars($textosLocal['txt_imagen_header_cursos'], ENT_QUOTES, 'UTF-8'); ?></h1>
	<p class="header_imagen_ceproca_cursos_p"><?php echo htmlspecialchars($textosLocal['txt2_imagen_header_cursos'], ENT_QUOTES, 'UTF-8'); ?></p>
</div>





<header class="texto_header_cursos_100_practicos">
    <div class="texto_header_cursos_100_practicos_div1"></div><!--"Aprovecha cada oportunidad para aprender algo nuevo y especialízate en lo que amas."-->
    	<h1><?php echo htmlspecialchars($textosLocal['txt2_imagen_header_cursos'], ENT_QUOTES, 'UTF-8'); ?></h1>
    <div class="texto_header_cursos_100_practicos_div2"></div>
</header>


<div class="space"></div>


<!-- 
para form                   .form_filtro_cursos{display:flex;justify-content:center;align-items:center;box-shadow: none;padding:0;max-width:90%; max-height: 15vh;margin: 30px auto;background-color: #f2f2f2;border-radius: 8px;box-shadow: 0 0 10px rgba(0,0,0,0.1);}
para el div que los guarda  .div_filtro_cursos{display:flex;justify-content:flex-start;align-items:center;background-color:inherit;box-shadow: none;width:100%;padding:0;margin:0;}
el div que guarda el select .div_select_filtro_cursos{background-color:inherit;display:flex;justify-content:flex-start;align-items:center;padding:3px;margin:0;max-width:30vw;flex: 1 1 45%; flex-direction: column;}
el div q guarda el input    .div_input_filtro_cursos{background-color:inherit;display:flex;justify-content:flex-start;align-items:center;padding:3px;margin:0;max-width:30vw;flex: 1 1 45%;flex-direction: column;}
							.div_input_filtro_cursos input{font-size:5rem;border: 1px solid #ccc;border-radius: 4px;background-color: #fff;transition: border-color 0.3s;width:100%;background-color:inherit;margin:0;padding:0;}
buton de filtrar            .boton_filtro_cursos{font-size:5rem;margin:0;padding:3px;max-width:20vw;margin-right:5px;  margin-top: 20px;background-color: #007bff;color: #fff;font-weight: bold;border: none;border-radius: 4px;cursor: pointer;display: block;width: 100%;transition: background-color 0.3s;}

.pagination {
    display: flex;
    justify-content: center;
    list-style: none;
    padding: 0;
    margin: 20px 0;
    gap: 5px;
}

.page-link{font-size:5rem;}
.page-item .page-link {
    display: block;
    padding: 8px 14px;
    background: #f8f9fa;
    color: #333;
    border-radius: 4px;
    text-decoration: none;
    border: 1px solid #ddd;
    transition: 0.2s;
}

.page-item .page-link:hover {
    background: #e9ecef;
    border-color: #ccc;
}

.page-item.active .page-link {
    background: #007bff;
    color: #fff;
    border-color: #007bff;
}

.page-item .page-link:active {
    transform: scale(0.95);
}



-->

    	<p style="font-size:6rem;margin-left:5%;">Filtros de Búsqueda</p>


<form method="GET" class="filtro-formulario form_filtro_cursos" >
    <input type="hidden" name="views" value="cursos">
    <div class="div_filtro_cursos"> 
        <div class="div_select_filtro_cursos filtro-campo">
            <select name="tipo" class="form-control" onchange="this.form.submit()" style="width:100%;background-color:inherit;margin:0;padding:0;">
                <option value="">Todos los tipos</option>
                <option value="Intensivo" <?= isset($_GET['tipo']) && $_GET['tipo'] == 'Intensivo' ? 'selected' : '' ?>>Intensivo</option>
                <option value="Extendido" <?= isset($_GET['tipo']) && $_GET['tipo'] == 'Extendido' ? 'selected' : '' ?>>Extendido</option>
                <option value="Técnico Profesional" <?= isset($_GET['tipo']) && $_GET['tipo'] == 'Técnico Profesional' ? 'selected' : '' ?>>Técnico Profesional</option>
            </select>
        </div>
        
        <div class="div_input_filtro_cursos filtro-campo">
            <input type="text" name="busqueda" class="form-control div_input_filtro_cursos_input" placeholder="Buscar curso..." value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>"> <!--style="font-size: 5rem;border: 1px solid #ccc;border-radius: 4px;background-color: #fff;transition: border-color 0.3s;width:100%;background-color:inherit;margin:0;padding:0;"-->
        </div>
        
        <button type="submit" class="boton_filtro_cursos" style="margin-top:0;">Filtrar</button>
    </div>
</form>



<!--form method="GET" class="filtro-formulario" style="display:flex;justify-content:center;align-items:center;box-shadow: none;padding:0;max-width:90%;background-color: #f2f2f2;border-radius: 8px;box-shadow: 0 0 10px rgba(0,0,0,0.1);">
    <input type="hidden" name="views" value="inicio">
    <div style="display:flex;justify-content:flex-start;align-items:center;background-color:inherit;box-shadow: none;width:100%;padding:0;margin:0;"> 
        
        <div class="filtro-campo" style="background-color:inherit;display:flex;justify-content:flex-start;align-items:center;padding:3px;margin:0;max-width:30vw;">
            <select name="tipo" class="form-control" onchange="this.form.submit()" style="width:100%;background-color:inherit;margin:0;padding:0;">
                <option value="">Todos los tipos</option>
                <option value="Intensivo" <?= isset($_GET['tipo']) && $_GET['tipo'] == 'Intensivo' ? 'selected' : '' ?>>Intensivo</option>
                <option value="Extendido" <?= isset($_GET['tipo']) && $_GET['tipo'] == 'Extendido' ? 'selected' : '' ?>>Extendido</option>
                <option value="Técnico Profesional" <?= isset($_GET['tipo']) && $_GET['tipo'] == 'Técnico Profesional' ? 'selected' : '' ?>>Técnico Profesional</option>
            </select>
        </div>
        
        <div class="filtro-campo" style="background-color:inherit;display:flex;justify-content:flex-start;align-items:center;padding:3px;margin:0;max-width:30vw;">
            <input type="text" style="font-size: 5rem;border: 1px solid #ccc;border-radius: 4px;background-color: #fff;transition: border-color 0.3s;width:100%;background-color:inherit;margin:0;padding:0;"
            name="busqueda" class="form-control" placeholder="Buscar curso..." value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>"> 
        </div>
        
        <button type="submit" class="btn-filtrar" style="font-size:5rem;margin:0;padding:3px;max-width:20vw;margin-right:5px">Filtrar</button>
    </div>
</form-->





<div style="display:flex;justify-content:center;align-items:center;width:100%;"><!--en teoria va a cerrarse desde el controaldor-->
    <?php
    //esta parte se encarga de paginar los datos
    $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    //$filtroTipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
    //$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : null;
    $filtroTipo = !empty($_GET['tipo']) ? $_GET['tipo'] : null;
    $busqueda = !empty($_GET['busqueda']) ? $_GET['busqueda'] : null;
    echo $localCtrl->listar_cursos_publico_controlador($pagina, $filtroTipo, $busqueda);
    ?>



<!--div class="paginacion">
    <c?php
    $totalPaginas = ceil($totalCursos / $cursosPorPagina);
    for ($i = 1; $i <= $totalPaginas; $i++):
        $link = $urlBase . "&pagina=" . $i;
        $activo = ($i == $paginaActual) ? 'class="activo"' : '';
    ?>
        <a href="<c?= $link ?>#c" <c?= $activo ?> style="color:white;">
            <c?= $i ?>
        </a>
    <c?php endfor; ?>
</div-->


<!--br><br><br><br><br><br-->
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





    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    







<!--iframe src="aiframeceprocaprofes.php" style="width:100%; height:105vh;border:0;"></iframe-->
<!--br><br><br><br><br><br-->
<div class="space"></div>

<!--div class="div_footer_cursos_texto" >
	<div class="footer_cursos_texto">
    	<h1>El aprendizaje nunca se detiene</h1>
        <p>En un mundo que cambia rápidamente, adquirir nuevas habilidades y conocimientos es esencial para mantenerte relevante. Un curso te ofrece la oportunidad de aprender de expertos en el campo, acceder a recursos actualizados y participar en un entorno estructurado que fomenta el aprendizaje efectivo. Al inscribirte en un curso, no solo aprendes, sino que también te rodeas de personas que comparten tus intereses. Estas conexiones pueden abrir puertas a nuevas oportunidades laborales, colaboraciones y amistades duraderas. Nunca sabes quién podría ser tu próximo socio comercial o mentor.</p>
    </div>
	<div class="footer_cursos_texto_img">
        <img src="../vistas/assets/img/persona1.webp">
    </div>
</div-->






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


    
    
<!--script>
    //redirige mediante ajax a cursos detail
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
</script-->
    <script>
function registrarYRedirigir(idEncriptado) {
    const url = '<?= SERVERURL ?>ajax/publicoAjax.php?accion=registrar_interaccion&id=' + encodeURIComponent(idEncriptado);
    console.log('URL a llamar:', url);
    fetch(url)
        .then(res => {
            console.log('Status HTTP:', res.status);
            return res.text(); // usar text() primero para ver errores PHP
        })
        .then(text => {
            console.log('Respuesta cruda:', text);
            try {
                const data = JSON.parse(text);
                console.log('Respuesta JSON:', data);
            } catch (e) {
                console.error('No es JSON válido:', e);
            }
            window.location.href = '<?= SERVERURL ?>index.php?views=cursos-detail&id=' + encodeURIComponent(idEncriptado);
        })
        .catch(err => {
            console.error('Error en fetch:', err);
            window.location.href = '<?= SERVERURL ?>index.php?views=cursos-detail&id=' + encodeURIComponent(idEncriptado);
        });
    }
    </script>
    