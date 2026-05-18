<?php

require_once "./controladores/publicoControlador.php";
$inicioCtrl = new publicoControlador();
$datosInicio = $inicioCtrl->cargar_inicio_controlador();
//$cursos = $datosInicio["curso"];
$profesores = $datosInicio["profesores"];
$cursos = $datosInicio["curso"];
$imgn1 = $datosInicio["slider"]["slider_1"];
$imgn2 = $datosInicio["slider"]["slider_2"];
$imgn3 = $datosInicio["slider"]["slider_3"];
$info = $datosInicio["infoceproca"];







require_once "./vistas/inc/utilidadesPublicas.php"; // Ruta válida desde index.php

$pdo = conectarBase();
$ip = $_SERVER['REMOTE_ADDR'];
$idIP = registrar_ip_visitante($pdo, $ip);
registrar_visita($pdo, $idIP);



?>

<div class="absoluto ">
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
            <li><a href="<?php echo SERVERURL; ?>?views=login">Iniciar sesión</a>
            </li>
            <li><a href="<?php echo SERVERURL; ?>solicitud-inscripcion">Regístrate</a>

            <li class="switch">
                    <i class="bx bxs-sun" ></i>
                	<i class="bx bxs-moon"></i>
            </li>           
            </ul>
    </div><!--contactosesion-->
</div><!--absoluto-->


<header class="inicio_header_imagen_ceproca" style="background-image: url('../vistas/assets/img/talves4.webp');">
    <p>SOMOS UNA INSTITUCIÓN EDUCATIVA DEDICADA A LA FORMACIÓN DE CIUDADANOS ÍNTEGROS, OFRECIENDO CURSOS ADAPTADOS PARA ESTUDIANTES DE DIVERSAS EDADES, QUIENES, MEDIANTE LA EDUCACIÓN CON UN SENTIDO ÚTIL Y PRÁCTICO, BUSCAN REINVENTARSE PARA AVANZAR Y PRODUCIR.</p>
</header><!--nueva2-->


<!--aqui empiezan los botones de cursos-->
<header class="texto_header_cursos_100_practicos">
    <div class="texto_header_cursos_100_practicos_div1"></div>
    <h1>VEN Y FORMA PARTE DE LA ACADEMIA MAS GRANDE EL ESTADO GUÁRICO</h4>
    <h1>CURSOS 100% PRÁCTICOS</h1>
    <div class="texto_header_cursos_100_practicos_div2"></div>
</header>



<section class="caracteristicas_cursos">
    <div class="buton_caracteristicas_cursos b1 bbb" onclick="toggleDiv(0,'oculto1',400)"><p>Sabatinos</p></div>
    <div class="buton_caracteristicas_cursos b2 bbb" onclick="toggleDiv(1,'oculto1',400)"><p>Dias de semana</p></div>
    <div class="buton_caracteristicas_cursos b3 bbb" onclick="toggleDiv(2,'oculto1',400)"><p>Intensivos</p></div>
    <div class="buton_caracteristicas_cursos b4 bbb" onclick="toggleDiv(3,'oculto1',400)"><p>Técnicos Profesionales</p></div>
    <div class="buton_caracteristicas_cursos b5 bbb" onclick="toggleDiv(4,'oculto1',400)"><p>Cursos para niños</p></div>
</section><!--mentiras-->

<div class="oculto" id="oculto1">
    <div>
  	    <section class="texto_caracteristicas_curso b1_static">
	        <i class='bx bxs-brightness'></i>    
	        <p>En solo unos cuantos fines de semana, verás un cambio significativo en tus habilidades y conocimientos. Ya sea que busques mejorar en tu carrera, emprender un nuevo proyecto o simplemente aprender algo nuevo.</p> 
 	        <br>
        </section> 
        <section class="imagen_caracteristicas_curso"><img loading="lazy" src="../vistas/assets/img/finde.webp"/></section>
    </div>
  		 

    <div>	
  		<section class="texto_caracteristicas_curso b2_static">
            <i class='bx bx-calendar'></i>
	        <p>La regularidad en las clases puede ayudar a mantener alta la motivación. Al ver avances y recibir retroalimentación constante, es más fácil mantenerse enfocado en tus objetivos y seguir comprometido con el curso.</p> 
 	        <br>
 		</section>    
        <section class="imagen_caracteristicas_curso"><img loading="lazy" src="../vistas/assets/img/barbero5.webp"/></section>
    </div>
  
  
    <div> 
  		 <section class="texto_caracteristicas_curso b3_static">
            <i class='bx bxs-timer'></i>
            <p>los cursos intensivos están diseñados para cubrir una gran cantidad de contenido en un corto período de tiempo. Esto significa que puedes adquirir habilidades y conocimientos rápidamente, lo que es ideal si necesitas aprender algo nuevo para un trabajo o proyecto específico.</p>
 	        <br>
 		</section>    
  		<section class="imagen_caracteristicas_curso"><img loading="lazy" src="../vistas/assets/img/barbero4.webp"/></section>
    </div>


    <div> 
		<section class="texto_caracteristicas_curso b4_static">
	        <i class='bx bxs-graduation'></i>
            <p>Los programas técnicos suelen actualizarse más rápidamente para adaptarse a las demandas cambiantes del mercado, asegurando que los estudiantes aprendan las habilidades más relevantes y actuales.</p>
 	        <br>
        </section>       
  		<section class="imagen_caracteristicas_curso"><img loading="lazy" src="../vistas/assets/img/tecnico2.webp"/></section>  
    </div>


    <div> 
  	    <section class="texto_caracteristicas_curso b5_static">
            <i class='bx bx-child'></i>
	        <p>Los cursos proporcionan un entorno en el que los niños y jóvenes pueden hacer amigos y aprender a trabajar en equipo donde aprenden a comunicarse, compartir y colaborar con otros niños, lo cual es crucial para su desarrollo social.</p>
 	        <br>
        </section>  
  		<section class="imagen_caracteristicas_curso"><img loading="lazy" src="../vistas/assets/img/ingles4.webp"/></section>   
    </div>

</div><!--info oculto de caracteristicas de cursos-->
<!--br><br-->




<div class="publicitando2">

	<div class="multislider-container" id="slidio6" style="height:1200px;width:20%;">
  <div class="multislider-content" style="height:100%;">
  
    <div class="multislide llorar" style="height:100%;margin:auto;">        
      <img style="height:35%;margin-top:5px;" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn3[0]); ?>" alt="Imagen 1">
    </div>
    
    <div class="multislide llorar" >
      <img style="height:35%;margin-top:5px;" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn3[1]); ?>" alt="Imagen 2">
      <img style="height:35%;margin-top:5px;" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn3[2]); ?>" alt="Imagen 3">
    </div>
                         
    <div class="multislide llorar" style="height:100%;">
      <img style="height:35%;margin-top:5px;" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn3[3]); ?>" alt="Imagen 4">
    </div>
                
    <div class="multislide llorar">
      <img style="height:35%;margin-top:5px;" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn3[4]); ?>" alt="Imagen 5">
      <img style="height:35%;margin-top:5px;" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn3[5]); ?>" alt="Imagen 6">
    </div>
    
  </div>
</div>



<!--aqui empiezan tarjetas-->





<div class="tarjetas">
    <div class="tarjconteiners">

        <?php foreach ($cursos as $curso): ?>
            <div class="card obsw" onclick="redirigirACurso(<?php echo $curso['id_materia']; ?>, <?php echo $curso['id_materia']; ?>, 1)">
                <figure>
                    <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($curso['ruta']); ?>" alt="Imagen del curso">
                </figure>
                <div class="contenido obsw">
                    <h4><?php echo htmlspecialchars($curso['nombre_curso']); ?></h4>
                    <p><?php echo htmlspecialchars($curso['prefacio']); ?></p>




                    <?php
                        $inscripcion = (float)$curso['pago_inscripcion'] ?? 0;
                        $modulo      = (float)$curso['pago_modulo'] ?? 0;
                        $oferta      = (int)$curso['oferta_vigente'] ?? 0;

                        $pago_total = $inscripcion + ($modulo * 3); // precio sin descuento


						  $pago_y_oferta = $pago_total;
                        // Puedes inyectar estas variables al array si preferís
                        $curso['pago_total'] = $pago_total;
                       // $curso['pago_y_oferta'] = $pago_y_oferta;
                    ?>

                    <div class="tarjeta_div_precio_cupos">
                        <?php if ((int)$curso['cupos'] === 0): ?>
                            <div class="tarjeta_div_cupo">
                                <i class='bx bxs-user-minus' style="color:red;"></i>
                                <p>NO HAY CUPOS DISPONIBLES</p>
                            </div>
                        <?php elseif (!empty($curso['oferta']) && (int)$curso['oferta'] !== 0): ?>
                            <div class="tarjeta_div_precio">
                                <p>Precio: <?php echo htmlspecialchars($curso['pago_y_oferta']); ?>$</p>
                                <p><s style="font-size:inherit;"><?php echo htmlspecialchars($curso['pago_total']); ?>$</s></p>
                            </div>
                            <div class="tarjeta_div_oferta_icono">
                                <i class='bx bxs-badge-dollar' style='color:orange;'></i>
                                <p>OFERTA DISPONIBLE</p>
                            </div>
                        <?php else: ?>
                            <p>Precio: <?php echo htmlspecialchars($curso['pago_total']); ?>$</p><i class='bx bxs-badge-dollar' style='color:orange;'></i>

                        <?php endif; ?>

                    </div><!--tarjeta_div_precio_cupos-->
                </div><!--contenido-->
            </div><!--card-->
        <?php endforeach; ?>

    </div><!--tarjconteiners-->
</div><!--tarjetas-->






<!--aqui terminan tarjetas-->
</div><!--publicitando2-->
<br>
<a class="ver_mas" href="http://www.cecilioceproca.wuaze.com/index.php?views=cursos">Ver más</a>



<div id="ir_arriba"><i class='bx bxs-home-alt-2'></i></div>


  <div class="nuevoslider obsb">
        <div class="thyplosion obsb">
            <h1>No solo nos dedicamos a la formación profesional, sino que también jugamos un papel activo en la comunidad, promoviendo el desarrollo integral de nuestros estudiantes y fortaleciendo los lazos sociales en Valle de la Pascua.</h1>
        </div><!--thyplosion-->
        
        <div class="newslider" style="width:400px;">
            <div class="newslides" style="width:400px;">
                
            <?php for ($i = 1; $i <= 8; $i++): ?><!--images_automatico3-->
                <img  style="width:400px;" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn2[$i-1]); ?>" alt="Imagen del Slider">
            <?php endfor; ?>
                            
            </div>
            <div class="newnavigation">
                <button class="nav-btn" id="previBtn">⟨</button>
                <button class="nav-btn" id="nextiBtn">⟩</button>
            </div>
        </div>
    </div><!--nuevoslider-->



    <!--div class="nuevoslider " style="width:50%;background-color:inherit;">

        <div class="thyplosion obsb">
            <h1>No solo nos dedicamos a la formación profesional, sino que también jugamos un papel activo en la comunidad, promoviendo el desarrollo integral de nuestros estudiantes y fortaleciendo los lazos sociales en Valle de la Pascua.</h1>
        </div><!--thyplosion->

            <div class="newslider" style="width:400px;">
                <div class="newslides" style="width:400px;">
                    
                <?php for ($i = 1; $i <= 6; $i++): ?><!--images_automatico3->
                    <img  style="width:400px;" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn1[$i-1]); ?>" alt="Imagen del Slider">
                <?php endfor; ?>
                                
                </div>
                <div class="newnavigation">
                    <button class="nav-btn" id="prevBtn">⟨</button>
                    <button class="nav-btn" id="nextBtn">⟩</button>
                </div>
            </div>
        </div><!--nuevoslider-->












<div style="height:5vh;"></div>
<div class="nada">

<h1 style="display:flex;align-items:center;Justify-content:center;">Más sobre Ceproca</h1><br><br style="height:5vh;">
</div>
<!--aqui empiezan los periodicos-->
<div class="espacio obsb">
	<div class="periodicou obsw">
    	<div class="quien car" onclick="toggleDiv(0,'oculto2',650)">
        	<p><b class="pebee1" style="color:#36C;">¿Quienes Somos?</b></p><hr style="width:100%;height:20px;margin-left:9%;margin-right:9%;background-color:#039;"><img loading="lazy" src="../vistas/assets/img/viejaceproca.webp" style="margin-top:0px"><p>El Centro Educativo Profesional Cecilio Acosta (CEPROCA) es una institución educativa ubicada en Valle de la Pascua, estado Guárico, Venezuela Se caracteriza por su enfoque en la formación técnica y profesional...</p>
        </div><!--quien-->
        <div class="quien car obsw" onclick="toggleDiv(1,'oculto2',650)">
        	<p><b class="pebee2" style="color:#C33;">¿Cual es nuestro Objetivo?</b> Tenemos como misión principal proporcionar una educación técnica y profesional de alta calidad que responda a las necesidades del mercado laboral y contribuya al desarrollo integral de sus estudiantes.</p><img loading="lazy" src="../vistas/assets/img/objetivo.webp"><hr style="width:100%;height:20px;margin-left:9%;margin-right:9%;background-color:#C33;">
        </div><!--porque-->
        <div class="quien2 car obsw" onclick="toggleDiv(2,'oculto2',650)">
        	<p><b>¿Quién es Cecilio Acosta?</b> Destacado intelectual y escritor venezolano, nacido el 15 de diciembre de 1818 en La Guaira, Venezuela. Es conocido por su contribución a la literatura, la educación y la cultura en el país durante el siglo XIX.
</p><img loading="lazy" src="../vistas/assets/img/cecilio2.webp" style="width:50%;height:30%;margin-top:10px;border-radius:50%;">
        </div><!--cecilio-->
	</div><!--periodicou-->
</div><!--espacio-->


<div class="oculto" id="oculto2">
	<div>
    	<section class="espacio">
    		<section class="periodicou" style="margin-left:0%;">
    			<section class="quien">
    				<p style="padding: 10px 10px;">ofreciendo una variedad de cursos y programas que buscan equipar a los estudiantes con habilidades prácticas y conocimientos relevantes para el mercado laboral. CEPROCA ofrece una amplia gama de cursos permanentes en diversas áreas, lo que permite a los estudiantes elegir formaciones que se alineen con sus intereses y necesidades profesionales. Estos cursos abarcan desde la formación en áreas técnicas hasta programas de desarrollo personal y profesional. </p><hr style="width:80%;margin-left:9%;margin-right:9%;">
                </section><!--quien-->
            
            <section class="quien">
					<p>La institución también se involucra en actividades comunitarias y eventos que promueven la educación y el desarrollo social en la región.</p><p>El centro no solo se enfoca en la educación formal, sino que también busca crear un ambiente de comunidad y colaboración. A menudo, organizan eventos y actividades que fomentan la interacción entre estudiantes, profesores y la comunidad local. Esto ayuda a construir una red de apoyo y a fortalecer los lazos sociales entre los participantes.
</p><hr style="width:80%;margin-left:9%;margin-right:9%;">
    			
                </section><!--quien-->
            <section class="quien2">
    				<p>El equipo de CEPROCA está compuesto por profesionales capacitados y comprometidos con la educación. Los docentes son seleccionados por su experiencia en sus respectivas áreas y su capacidad para impartir conocimientos de manera efectiva. Esto asegura que los estudiantes reciban una educación de calidad que les prepare adecuadamente para sus futuros laborales.
</p><br><hr style="width:80%;margin-left:9%;margin-right:9%;">
                </section><!--quien2-->
            </section><!--periodicou-->    
        </section><!--espacio-->
    </div>
    
    
	<div>
    	<section class="espacio">
    		<section class="periodicou" style="margin-left:0%;">
    			<section class="quien">
    				<p>Tenemos como misión principal proporcionar una educación técnica y profesional de alta calidad que responda a las necesidades del mercado laboral y contribuya al desarrollo integral de sus estudiantes. Su enfoque está en formar profesionales competentes que no solo posean conocimientos teóricos, sino también habilidades prácticas que les permitan destacarse en sus respectivas áreas.</p>
	<p><b class="pebee3">Entre nuestras metas principales tenemos...</b></p>
<hr style="width:80%;margin-left:9%;margin-right:9%;">
        		</section><!--quien-->
                <section class="quien">
    				<p>◦ Fomentar el desarrollo personal y profesional de sus estudiantes, ayudándoles a alcanzar sus metas y a mejorar su calidad de vida.</p>
                    <p>◦ Contribuir al desarrollo económico y social de la comunidad de Valle de la Pascua, formando profesionales que puedan satisfacer las demandas del mercado laboral local.</p>
                    <p>◦ Promover la inclusión y la equidad en la educación, asegurando que todos los interesados tengan acceso a oportunidades de formación, independientemente de su contexto socioeconómico.</p>
                 <hr style="width:80%;margin-left:9%;margin-right:9%;">
        		</section><!--quien-->
                <section class="quien2">
    				<p>◦ Establecer alianzas estratégicas con empresas y organizaciones locales para facilitar la inserción laboral de sus egresados y asegurar que los programas educativos estén alineados con las necesidades del sector productivo.</p><img loading="lazy" src="../vistas/assets/img/paisaje.webp" style="width:100%;height:40%;margin-top:5px;margin-bottom:3px;box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 50px;"> <br> <hr style="width:80%;margin-left:9%;margin-right:9%;margin-top:5px;">
        		</section><!--quien-->
            </section><!--periodicou-->
        </section><!--espacio-->
    </div>
    
        
	<div>
    	<section class="espacio">
    		<section class="periodicou" style="margin-left:0%;">
    			<section class="quien">
    				<p>Cecilio Acosta se caracterizaba por su profundo compromiso con la educación y la promoción del pensamiento crítico. Fue un ferviente defensor de la libertad de expresión y de la importancia de la educación como herramienta de progreso social. Su obra abarcó diversos géneros, incluyendo ensayos, artículos periodísticos y obras literarias, donde abordó temas como la moral, la política y la filosofía.
</p><hr style="width:80%;margin-left:9%;margin-right:9%;">
        		</section><!--quien-->
                <section class="quien">
    				<p>"El espíritu de la letra": Un ensayo que reflexiona sobre la importancia de la educación y la cultura.
</p>
                    <p>"La educación pública": Un texto en el que expone sus ideas sobre la necesidad de una educación accesible y de calidad para todos.
</p>
                    <p> "Cartas a un amigo": Una serie de cartas que abordan temas filosóficos y sociales, mostrando su pensamiento crítico y su visión sobre la sociedad venezolana.

</p><hr style="width:80%;margin-left:9%;margin-right:9%;">
        		</section><!--quien-->
                <section class="quien2">
    				<p>Cecilio Acosta se destacó no solo como escritor, sino también como educador y periodista. Fue un pionero en la promoción de la educación laica en Venezuela y participó activamente en la creación de instituciones educativas. Su influencia se extendió a través de sus escritos y su labor en la prensa, donde defendió ideales de justicia social y equidad.</p><hr style="width:80%;margin-left:9%;margin-right:9%;">
        		</section><!--quien-->
            </section><!--periodicou-->
        </section><!--espacio-->
    </div>
</div><!--oculto-->
<!--aqui terminan los periodicos-->


<br><br>
<div style="width:100%;display:flex;justify-content:center;align-items:center;flex-wrap:wrap;">

<div style="overflow:hidden;border-radius:8px;width:300px;height:535px;margin:4%;">
<iframe class="pedro" style="width:300px;height:550px;"  src="https://www.facebook.com/plugins/video.php?height=476&href=https%3A%2F%2Fwww.facebook.com%2Freel%2F2854427401381978%2F&show_text=false&width=267&t=0" width="267" height="476" style="border:none;overflow:hidden;" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" allowFullScreen="true"></iframe>
</div>

<br><br><br>
<div class="multislider-container" id="multislider3" style="width: 500px;overflow:hidden;">
  <div class="multislider-content">
    <div class="multislide clase-extra">
        <div class="bloque1">
        	<section class="cuadrodoble" style="width:90%;margin:5%;height:40%;">
        		<section class="subcuadro" style="width:40%;margin-bottom:5%;height:85%;background-color:#6C9;">
                	<section class="datosprofe2">
                		<p><b>Juan Osorio</b></p>
                	</section><!--datosprofe-->
            	
                	<p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut"</p>
            	</section><!--subcuadro-->	
            
        	<section class="subcuadro" style="width:85%;margin-top:5%;height:89%;margin-left:5%;background-color:#6C3;">
                	<section class="datosprofe2">
                		<p><b>Angela Arevalo</b></p>
                	</section><!--datosprofe2-->
            	
                	<p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."</p>
            	</section><!--subcuadro-->
            </section><!--cuadrodoble-->
            
            <section class="cuadro1" style="width:80%;background-color:#69F;">
            	<section class="datosprofe2">
                		<p><b>Luán Gomez</b></p>
                	</section><!--datosprofe-->
            	
                	<p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation uLorem ipsum dolor sit amet, consectetur"</p>
            </section><!--cuadro1-->
        </div><!--bloque1-->
  </div>
  
  <!------------------------------------------------------------------------>
  
  
    <div class="multislide clase-extra">
    	<div class="bloque3">
        	<section class="cuadrodoble" style="flex-direction:column;width:50%;height:95%;">
        		<section class="subcuadro" style="height:46%;width:88%;margin: 20% 2%;background-color:#63F;">
                	<section class="datosprofe2">
                		<p><b>Carla Carvallo</b></p>
                	</section><!--datosprofe-->
            	
                	<p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation uLorem ipsum dolor sit amet, consectetur ad"</p>
            	</section><!--otrascosas21-->	
            
        	<section class="subcuadro" style="height:30%;width:94%;background-color:#63C;">
                	<section class="datosprofe2">
                		<p><b>Lia Juarez</b></p>
                	</section><!--datosprofe-->
            	
                	<p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore"</p>
            	</section><!--otrascosas21-->
            </section><!--otrascosas2-->
            
            <section class="cuadrodoble" style="flex-direction:column;justify-content:center;width:50%;height:95%;">
        		<section class="subcuadro" style="height:28%;width:91%;background-color:#36F;">
                	<section class="datosprofe2">
                		<p><b>Prof. Pedro Parques</b></p>
                	</section><!--datosprofe-->
            	
                	<p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed"</p>
            	</section><!--otrascosas21-->	
            
        	<section class="subcuadro" style="height:50%;width:86%;margin:13% 2%;background-color:#03F;">
                	<section class="datosprofe2">
                		<p><b>Adrian Villegas</b></p>
                	</section><!--datosprofe-->
            	
                	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation uLorem ipsum"</p>
            	</section><!--otrascosas21-->
            </section><!--otrascosas2-->
        </div><!--bloque2-->
    
    </div>
    
    
    <!------------------------------------------------------------------>
    <div class="multislide clase-extra">
    	<div class="bloque1" style="flex-wrap:nowrap;flex-direction:row;">
        	<section class="cuadrodoble" style="flex-direction:column;width:50%;height:95%;">
        		<section class="subcuadro" style="height:38%;width:86%;margin-right:4%;background-color:#F90;">
                			<section class="datosprofe2">
                				<p><b>Silvia Suarez</b></p>
                			</section><!--datosprofe-->
            	
                			<p>"orem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna"</p>
            	</section><!--otrascosas21-->	
            
        	<section class="subcuadro" style="height:45%;width:90%;margin-top:5%;margin-left:3%;background-color:#F36;">
                	<section class="datosprofe2">
                		<p><b>Armando Torrealba</b></p>
                	</section><!--datosprofe-->
            	
                	<p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veni"</p>
            	</section><!--otrascosas21-->
            </section><!--otrascosas2-->
            
            <section class="cuadro1" style="width:30%;margin:1%;height:50%;background-color:#C00;">
            	<section class="datosprofe2">
                		<p><b>Eduard Padrón</b></p>
                	</section><!--datosprofe-->
            	
                	<p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation u"</p>
            </section><!--general-->
        </div><!--bloque3-->
    </div>
    <!---------------------------------------------------------------->
    <div class="multislide otra-clase">
    	<div class="bloque1">
        	<section class="cuadro1" style="width:80%;height:25%;margin-right:5%;margin-bottom:5%;">
            	<section class="datosprofe2">
                		<p><b>Andresio herreira</b></p>
                	</section><!--datosprofe-->
            	
                	<p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim "</p>
            </section><!--general-->
            
            
            
            <section class="cuadro1" style="width:80%;margin-left:5%;background-color:#026773;">
            	<section class="datosprofe2">
                		<p><b>Olivio del Soto</b></p>
                	</section><!--datosprofe-->
            	
                	<p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation uLorem ipsum dolor sit amet, co"</p>
            </section><!--general-->
            
            
            
            <section class="cuadro1" style="height:20%;margin:5%;width:80%;background-color:#024959;">
            	<section class="datosprofe2">
                		<p><b>Prof. Ignacio Rosales</b></p>
                	</section><!--datosprofe-->
            	
                	<p>"Gracias al curso horneo un pan para mi abuela todos los fines de semana."</p>
            </section><!--general-->
        </div><!--bloque1-->
    </div><!--4cosa-->
    	
  </div><!--el div q encierra todos los multislide-->
</div>
</div><!--chamo-->

<br><br><br><br><br><br><br><br><br><br>

</div>











<div class="cebo" style="width:100%;display:flex;justify-content:center;align-items:center;">
    <div class="multislider-container" id="sliderpr" style="width:80%;">
        <div class="multislider-content" style="height:100%;">
            <?php for ($i = 0; $i < count($profesores); $i += 2): ?>
                <div class="multislide">
                    <section style="width:100%;display:flex;justify-content:space-between;align-items:center;">
                        <?php if (isset($profesores[$i])): ?>
                            <section class="generalprofe" style="width:45%;height:100%;">
                                <section class="datosprofenew">
                                    <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($profesores[$i]['imagen_profesor']); ?>" alt="<?php echo htmlspecialchars($profesores[$i + 1]['nombre_profesor']); ?>">
                                     <p><b>Prof. <?php echo htmlspecialchars($profesores[$i]['nombre_profesor'] . ' ' . $profesores[$i]['apellido_profesor']); ?></b></p>
                                </section><!--datosprofenew-->
                                <p><?php echo htmlspecialchars($profesores[$i]['descripcion']); ?></p>
                                <section class="generalchecking">
                                    <section class="checkingprofe" ><i class='bx bx-phone'></i> <p><?php echo htmlspecialchars($profesores[$i]['telefono_profesor']); ?></p></section><!--checkingprofe-->
                                    <section class="checkingprofe" ><i class='bx bx-envelope'></i> <p><?php echo htmlspecialchars($profesores[$i]['correo']); ?></p></section><!--checkingprofe-->
                                </section><!--generalchecking-->
                            </section><!--generalprofe-->
                        <?php endif; ?>

                        <?php if (isset($profesores[$i + 1])): ?>
                            <section class="generalprofe" style="width:45%;height:100%;">
                                <section class="datosprofenew">
                                    <img loading="lazy" src="../vistas/<?php echo htmlspecialchars($profesores[$i + 1]['imagen_profesor']); ?>" alt="<?php echo htmlspecialchars($profesores[$i + 1]['nombre_profesor']); ?>">
                                    <p><b>Prof. <?php echo htmlspecialchars($profesores[$i + 1]['nombre_profesor'] . ' ' . $profesores[$i + 1]['apellido_profesor']); ?></b></p>
                                </section><!--datosprofenew-->
                                <p><?php echo htmlspecialchars($profesores[$i + 1]['descripcion']); ?></p>
                                <section class="generalchecking">
                                    <section class="checkingprofe" ><i class='bx bx-phone'></i> <p><?php echo htmlspecialchars($profesores[$i + 1]['telefono_profesor']); ?></p></section><!--checkingprofe-->
                                    <section class="checkingprofe" ><i class='bx bx-envelope'></i> <p><?php echo htmlspecialchars($profesores[$i + 1]['correo']); ?></p></section><!--checkingprofe-->
                                </section><!--generalchecking-->
                            </section><!--generalprofe-->
                        <?php endif; ?>
                    </section><!--ordenador-->
                </div><!--slide-->
            <?php endfor; ?>
        </div>
        <button class="nav-button prev-button" onclick="navigateSlider('sliderpr', -1)">←</button>
        <button class="nav-button next-button" onclick="navigateSlider('sliderpr', 1)">→</button>
        <br>
        <br>
    </div>
    <br>
</div><!--elqguardatodo-->






<div style="height:10vh;"></div>





<!--aqui terminan los contactos-->
<div class="nada3"><h3>CONTÁCTANOS</h3><br><br></div>

<!--estos son los mapas-->

<aqui empiezan los contactos->
<div style="width:100%;height:30vh;" class="enlaces">
    <div style="width:50%;margin:auto;" class="enlaces">
        <div class="anima aa1" onclick="window.open('<?php echo htmlspecialchars($info['link_facebook']); ?>', '_blank');"><img src="../vistas/assets/img/feici.webp"></div>
        <div class="anima aa2" onclick="window.open('<?php echo htmlspecialchars($info['link_instagram']); ?>', '_blank');"><img src="../vistas/assets/img/insta.webp"></div>

        <div class="anima aa3" onclick="toggleDiv(0,'oculto3',500)"><img src="../vistas/assets/img/maps.webp"></div>
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
<div class="oculto" id="oculto3" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;background-color:#e64a19;height:0px;">
    	<div>
    		<section class="mapas">
            	<section class="mapasfotos" style="width:30%;height:95%;"><section class="mapastexto" style="height:50%;"><h3>Encuéntranos</h3><p style="text-align:center;"><?php echo htmlspecialchars($info['direccion_local_ciudad']); ?></p><p style="text-align:center;"><?php echo htmlspecialchars($info['direccion_local_pais']); ?></p></section><img loading="lazy" src="../vistas/<?php echo htmlspecialchars($info['ruta_local']); ?>"></section>
                
               
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


<div class="slidiorr">
    <div class="slide-trackior"><!--images_automatico-->
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
    </div><!--slide-trackior-->  
</div><!--slidiorr-->




<!--creditos-->
<div class="nueva" style="background-image: url('../vistas/assets/img/graduacion.webp');">
<p>Hoy somos reconocidos como la academia más grande del Estado Guárico, contando con modalidades de estudio permanentes reconocidas nacional e internacionalmente. Tenemos convenios importantes con fundaciones y universidades nacionales como la UPEL, la UNERG, la Fundación Casa Arturo Uslar Prieti y la Fundación Banesco. Estamos registrados formalmente como academia formadora de emprendedores ante el INCES y pertenecemos al Sistema Nacional de Emprendedores e Innovadores.</p>
</div><!--nueva-->

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
<!--script src="./vistas/js/scriptpublic1.js"></script-->



<script>
function redirigirACurso(idCurso, idBanner,otroValor) {
   //window.location.href = "aiframemateria3-copia.php?id=" + idCurso + "&banner=" + idBanner + "&valor=" + otroValor;
   window.location.href = "vistas/contenidos/public/aiframemateria3-copia.php?id=" + idCurso + "&banner=" + idBanner + "&valor=" + otroValor;
}
//falando

//modo oscuro
const swith= document.querySelector(".switch");
swith.addEventListener("click", e => {swith.classList.toggle("active");
document.body.classList.toggle("active");})




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
/*
    const newslides = document.querySelector('.newslides');
    const newimages = document.querySelectorAll('.newslides img');
    const newprevBtn = document.getElementById('prevBtn');
    const newnextBtn = document.getElementById('nextBtn');
    
    let currentIndexxx = 0;

    function updateSlider() {
        newslides.style.transform = `translateX(${-currentIndexxx * 400}px)`;
    }

    newprevBtn.addEventListener('click', () => {
        currentIndexxx = (currentIndexxx > 0) ? currentIndexxx - 1 : newimages.length - 1;
        updateSlider();
    });

    newnextBtn.addEventListener('click', () => {
        currentIndexxx = (currentIndexxx < newimages.length - 1) ? currentIndexxx + 1 : 0;
        updateSlider();
    });

*/

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

