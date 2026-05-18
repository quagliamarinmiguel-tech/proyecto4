<?php
if($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

require_once "./controladores/localControlador.php";
$ctrl = new localControlador();
$datos = $ctrl->datos_local_controlador(); // ← Devuelve el registro único con imágenes

if($datos->rowCount() != 1){
    echo '<div class="alert alert-danger text-center">No se encontró la información institucional.</div>';
    exit();
}

$local = $datos->fetch();
?>
<style>
    #preview-local, #preview-mapa, #preview-satelite {
      display: inline-block;
      margin-right: 10px;
    }

/*ojo esto es probando*/    
 /*barra de inicio, la de arriba*/
		.header_barra{display:flex;align-items:center;justify-content:flex-end;width:75%;height:100%;margin-right:20px;}
		.header_barra a{text-decoration:none;margin:5px;text-align:center;color:white;width:auto;background-color:#00a9f1;}
    
		.header_barra ul{list-style:none;}
    
		.header_barra ul li{display:inline-block;position:relative;border-radius:6px;}
		.header_barra ul li a{display:block;padding:10px 5px;color:white;text-decoration:none;text-align:center;border-radius:6px;/*font-size:6rem;*/}
		.header_barra ul li a:hover{background-color:#002a6f;}
    

.dropdown{width:100%;background-color:#00a9f1;position:absolute; z-index:999;display:none;border-radius:6px;}
.dropdown li{display:block;}
    .header_barra ul li:hover ul.dropdown{display:block;}

.logo_ceproca{width:20%;display:flex;align-items:center;justify-content:center;}

.logo_ceproca a img{margin:8px;width:40px;height:auto}

.logo_ceproca a{text-decoration:none;/*font-size:5rem;*/}

.logo_ceproca h3 b{padding-top:10px;color:white;/*font-size:5rem;*/}

.logo_ceproca p{padding-top:10px;color:white;/*font-size:5rem;*/}


.div_header{
    height:10vh;
    z-index:99;
  width: 100%;
  display: flex;
  justify-content: space-between;
  background-color:#00a9f1;
  /*box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px;*/
    box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
    border-radius:3px;
}
  

.contactos_header{width:inherit;height:inherit;cursor: pointer;background-color:#00a9f1;border-radius:6px; }
	.contactos_header p{padding:15px 5px;color:white;text-decoration:none;text-align:center;font-size:6rem;border-radius:6px;}
		.contactos_header p:hover{background-color:#002a6f;}

/*aqui termina la barra de inicio de sesion*/

.footer{
    width:100%;
	height:215px;/*200px*/
	display:flex;
	justify-content:center;
	flex-wrap:wrap;
	align-items:center;
	background-color:#002a6f;
	}

	.footer p{text-align:center;width:100%;color:white;}


    .tlfno_y_correo_footer{width:98%;display:flex;margin:1%;justify-content:center;align-items:flex-start;flex-direction:row;background-color:inherit;height:auto;}
         .datos_tlfno_y_correo_footer{margin:0 1%;height:auto;display:flex;justify-content:flex-start;align-items:flex-start;flex-direction:row;padding:0;width:auto;}         
              .datos_tlfno_y_correo_footer p{padding:0;color:white;}
              .datos_tlfno_y_correo_footer i{color:white;padding:2% /*1 3 0 0px !important*/;margin:0;}


	.footer_creditos{height: 36%;width: 100%;display: flex;justify-content: center;align-items:flex-start;}

		.footer_creditos_imagen{width:33%;display:flex;flex-wrap:wrap;}
			.footer_creditos_imagen img{margin:3px;width:60px;height:60px;transition: all 0.3s ease;}
			.footer_creditos_imagen img:hover{background-color:#00a9f1;}

        .footer_creditos_siguenos{width:33%;display:flex;align-items:center;flex-wrap:wrap;}
        .footer_creditos_siguenos p{text-align:center;width:100%;color:white;}

            .footer_creditos_siguenos_enlaces{display:flex;align-items:center;justify-content:center;width:100%}
            .footer_creditos_siguenos_enlaces a{text-decoration:none;margin:5px;text-align:center;width:auto;}
            .footer_creditos_siguenos_enlaces a img{border-radius:50%;}

                    .footer_creditos_siguenos_enlaces1{border-radius:50%;transition:all 0.3s ease;padding:3px;}
                    .footer_creditos_siguenos_enlaces1:hover{background-color:#f0f;}
                    .footer_creditos_siguenos_enlaces2{border-radius:50%;transition:all 0.3s ease;padding:3px;}
                    .footer_creditos_siguenos_enlaces2:hover{background-color:#33f;}

        .footer_creditos_copi{width:33%;}
        .footer_creditos_copi p{}


/*aqui termina logo final y copiright*/

@media (max-width: 639px) {
        .nuevoslider{flex-wrap:wrap;}
        .tarjetas {width: 100%;}
        .primer_slider_texto{width:50%;text-align:center;margin-right:1%;}    
    }
    
    
    
    
</style>

<!--link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
<link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&amp;family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo SERVERURL; ?>vistas/css/style_public4.css"-->


<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-map-marked-alt fa-fw"></i> &nbsp; ACTUALIZAR INFORMACIÓN CEPROCA</h3>
    <p class="text-justify">
        Edita los datos institucionales y reemplaza las imágenes del local, mapa y vista satelital si es necesario.
    </p>
</div>

<div class="container-fluid">

    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/localAjax.php" method="POST" data-form="update" enctype="multipart/form-data" autocomplete="off">

                <input type="hidden" name="local_id_up" value="1"> <!-- único registro -->
        <input type="hidden" name="imagen_local_fk" value="<?php echo $local['imagen_local']; ?>">
        <input type="hidden" name="imagen_local_mapa_fk" value="<?php echo $local['imagen_local_mapa']; ?>">
        <input type="hidden" name="imagen_local_satelite_fk" value="<?php echo $local['imagen_local_satelite']; ?>">        
        
        
        
        
        <fieldset>
            <legend> ícono y logo del local</legend>
            <div class="row">
                <div class="col-md-6 mb-3" style="background-color:inherit;box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;">
                    <div class="row">
                        <div class="col-md-6 mb-3" style="background-color:inherit;">
                            <img src="vistas/cm/fond/icodefault.ico" class="img-thumbnail" style="max-width: 300px;">
                        </div>
                        <div class="col-md-6 mb-3" style="background-color:inherit;">
                            <label class="form-label mt-2">Icono de las pestañas</label>
                            <input type="file" name="imagen_local" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3" style="box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;">
                    <div class="row">
                        <div class="col-md-6 mb-3" style="background-color:inherit;">
                            <img src="vistas/cm/fond/mainicon.webp" class="img-thumbnail" style="max-width: 300px;">
                        </div>
                        <div class="col-md-6 mb-3" style="background-color:inherit;">
                            <label class="form-label mt-2">Logo del local</label>
                            <input type="file" name="imagen_local" class="form-control-file" accept=".jpg,.jpeg,.png,.webp,.ico">
                        </div>
                    </div>
                </div>
            </div>        
        </fieldset>        
        
        
        
        
        
        
        <div class="div_header">
            <div class="logo_ceproca">
                <a href="#"><img src="../vistas/assets/img/logoceprocahd1.webp" alt="porqueno"></a>
            </div>
            <div class="header_barra">
                <ul>
                    <!--li><a href="#">Cursos</a></li-->
                    <li><a href="#">Iniciar sesión</a></li>
                    <li><a href="#">Regístrate</a></li>       
                </ul>
            </div>
        </div>

        <div style="height:5vh;"></div>
                        
        <fieldset style="background-color:green;">
            <div class="row" style="background-color:white;">
                <div class="col-md-12 mb-3">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <img src="vistas/cm/fond/defont.webp" class="img-thumbnail" style="max-width: 300px;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label mt-2">Imagen del header</label>
                            <input type="file" name="imagen_local" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                            <hr>
                            <label class="form-label">Texto de la imagen del header</label>
                            <input type="url" class="form-control" name="texto1" maxlength="255">
                        </div>
                    </div>
                    <br>
                </div>
                
                <div class="col-md-12 mb-3" style="display:flex;justify-content:center;align-items:center;margin:10 0px;">
                    <div class="col-md-12 mb-3" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px, rgba(2, 165, 235, 0.8) 0px -6px,  rgba(1, 149, 212, 0.7) 0px -14px, rgba(4, 116, 164, 0.6) 0px -22px,  rgba(56, 116, 144, 0.5) 0px -30px, rgba(2, 53, 53, 0.4) 0px -38px;border-radius:6px;text-align:center; ">
                        <label class="form-label" style="font-weight:bold;">Subtítulo</label>
                        <input type="url" style="font-weight:bold;text-transform: uppercase;" class="form-control" name="subtitulo1" maxlength="100">
                        <input type="url" style="font-weight:bold;text-transform: uppercase;padding:10 0px;" class="form-control" name="subtitulo2" maxlength="100">
                        <br>
                    </div>
                </div>
            </div> 
            
            
            <div class="nuevoslider" style="background-color:#72F2EB;">

                <!--div class="primer_slider_texto" style="background-color:blue;display:flex;justify-content:center;align-items:center;flex-direction:column;margin:20px;width:50%;"-->
                <div class="col-md-6 mb-3" style="border-radius:6px;text-align:center; ">       
                    <label class="form-label mt-2">Imagen del header</label>
                        <input type="file" name="imagen_local" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                        <hr>
                    <label class="form-label">Titulo de la imagen del header</label>
                        <input type="url" class="form-control" name="texto1" maxlength="255">
                        <hr>
                    <label class="form-label">Subtitulo de la imagen del header</label>
                        <input type="url" class="form-control" name="texto1" maxlength="255">   
                </div>
                <div class="newslider width400" style="background-color:green;border-radius:12px;margin:30px;">
                    <div class="col-md-6 mb-3">
                        <img class="width400" style="border-radius:12px;" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn2[2]); ?>" alt="Imagen del Slider">
                    </div>
                </div>
            </div>

            <div class="nuevoslider" style="background-color:#FF5F5D;">        
                <div class="newslider width400" style="background-color:green;border-radius:12px;margin:30px;">
                    <div class="col-md-6 mb-3">
                            <img class="width400" style="border-radius:12px;" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn2[2]); ?>" alt="Imagen del Slider">
                    </div>
                </div>
                <!--div class="primer_slider_texto" style="background-color:blue;display:flex;justify-content:center;align-items:center;flex-direction:column;margin:20px;width:50%;"-->
                <div class="col-md-6 mb-3" style="border-radius:6px;text-align:center; ">       
                    <label class="form-label mt-2">Imagen del header</label>
                        <input type="file" name="imagen_local" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                        <hr>
                    <label class="form-label">Titulo de la imagen del header</label>
                        <input type="url" class="form-control" name="texto1" maxlength="255">
                        <hr>
                    <label class="form-label">Subtitulo de la imagen del header</label>
                        <input type="url" class="form-control" name="texto1" maxlength="255">   
                </div>
             
            </div>

            <div class="nuevoslider" style="background-color:#72F2EB;">
                <div class="col-md-6 mb-3" style="border-radius:6px;text-align:center; ">       
                    <label class="form-label mt-2">Imagen del header</label>
                        <input type="file" name="imagen_local" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                        <hr>
                    <label class="form-label">Titulo de la imagen del header</label>
                        <input type="url" class="form-control" name="texto1" maxlength="255">
                        <hr>
                    <label class="form-label">Subtitulo de la imagen del header</label>
                        <input type="url" class="form-control" name="texto1" maxlength="255">   
                </div>
                <div class="newslider width400" style="background-color:green;border-radius:12px;margin:30px;">
                    <div class="col-md-6 mb-3">
                        <img class="width400" style="border-radius:12px;" loading="lazy" src="../vistas/<?php echo htmlspecialchars($imgn2[2]); ?>" alt="Imagen del Slider">
                    </div>
                </div>
            </div>

        </fieldset>
        
        
<br><br>

   


        <fieldset>
            <br><br><br>
            <legend>Comentarios</legend>
            <div class="row" style="color:white;display: flex; justify-content: center; align-items: center;">
      
    
                <div class="col-md-12 mb-3" style="display:flex;justify-content:center;align-items:center;margin:10 0px;">
                    <div class="col-md-12 mb-3" style="border-radius:6px;text-align:center; ">
                        <label class="form-label" style="font-weight:bold;color:black;">URL de anuncio del local (Facebook o Youtube)</label>
                        <input type="url" style="font-weight:bold;text-transform: uppercase;" class="form-control" name="subtitulo1" maxlength="100">
                        <br>
                    </div>
                </div>


<!--coment 1  #F54927 border: 8px solid #FFB3A3-->
                <div class="col-md-5 mb-3" style="border-radius:6px;box-shadow:
                    rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
                    rgba(60, 64, 67, 0.15) 0px 1px 3px 1px,
                    rgba(245, 73, 39, 0.4) 5px 5px, 
                    rgba(245, 73, 39, 0.3) 10px 10px, 
                    rgba(245, 73, 39, 0.2) 15px 15px;margin:10px;">
                    <input type="url" class="form-control" name="texto1" placeholder="Nombre" maxlength="255">
                        <br>
                    <input type="url" class="form-control" name="texto1" placeholder="Comentario 1" maxlength="255"> 
                        <br>
                </div>
  
<!--comentario 2 #69F-->
                <div class="col-md-5 mb-3" style="border-radius:6px;box-shadow: 
                    rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
                    rgba(60, 64, 67, 0.15) 0px 1px 3px 1px,
                    rgba(102, 153, 255, 0.4) 5px 5px, 
                    rgba(102, 153, 255, 0.3) 10px 10px, 
                    rgba(102, 153, 255, 0.2) 15px 15px;margin:10px;">
                    <input type="url" class="form-control" name="texto1" placeholder="Nombre" maxlength="255">
                        <br>
                    <input type="url" class="form-control" name="texto1" placeholder="Comentario 2" maxlength="255"> 
                        <br>
                </div>


<!--comentario 3 #63F-->

                <div class="col-md-5 mb-3" style="border-radius:6px;box-shadow: 
                    rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
                    rgba(60, 64, 67, 0.15) 0px 1px 3px 1px,
                    rgba(102, 51, 255, 0.4) 5px 5px, 
                    rgba(102, 51, 255, 0.3) 10px 10px, 
                    rgba(102, 51, 255, 0.2) 15px 15px;;margin:10px;">
                    <input type="url" class="form-control" name="texto1" placeholder="Nombre" maxlength="255">
                        <br>
                    <input type="url" class="form-control" name="texto1" placeholder="Comentario 3" maxlength="255"> 
                        <br>
                </div>
<!--comentario 4 #63C C2ACFC-->

                <div class="col-md-5 mb-3" style="border-radius:6px;box-shadow: 
                    rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
                    rgba(60, 64, 67, 0.15) 0px 1px 3px 1px,
                    rgba(102, 51, 204, 0.4) 5px 5px, 
                    rgba(102, 51, 204, 0.3) 10px 10px, 
                    rgba(102, 51, 204, 0.2) 15px 15px;;margin:10px;">
                    <input type="url" class="form-control" name="texto1" placeholder="Nombre" maxlength="255">
                        <br>
                    <input type="url" class="form-control" name="texto1" placeholder="Comentario 4" maxlength="255"> 
                        <br>
                </div>
<!--comentario 5 #36F 9CA5FF-->

                <div class="col-md-5 mb-3" style="border-radius:6px;box-shadow: 
                    rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
                    rgba(60, 64, 67, 0.15) 0px 1px 3px 1px,
                    rgba(51, 102, 255, 0.4) 5px 5px, 
                    rgba(51, 102, 255, 0.3) 10px 10px, 
                    rgba(51, 102, 255, 0.2) 15px 15px;;margin:10px;">
                    <input type="url" class="form-control" name="texto1" placeholder="Nombre" maxlength="255">
                        <br>
                    <input type="url" class="form-control" name="texto1" placeholder="Comentario 5" maxlength="255"> 
                        <br>
                </div>
<!--comentario 6 #03F B0BDFF-->

                <div class="col-md-5 mb-3" style="border-radius:6px;box-shadow: 
                    rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
                    rgba(60, 64, 67, 0.15) 0px 1px 3px 1px,
                    rgba(0, 51, 255, 0.4) 5px 5px, 
                    rgba(0, 51, 255, 0.3) 10px 10px, 
                    rgba(0, 51, 255, 0.2) 15px 15px;;margin:10px;">
                    <input type="url" class="form-control" name="texto1" placeholder="Nombre" maxlength="255">
                        <br>
                    <input type="url" class="form-control" name="texto1" placeholder="Comentario 6" maxlength="255"> 
                        <br>
                </div>

<!--comentario 7 #3ca6a6-->

                <div class="col-md-4 mb-3" style="border-radius:6px;box-shadow: 
                    rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
                    rgba(60, 64, 67, 0.15) 0px 1px 3px 1px,
                    rgba(60, 166, 166, 0.4) 5px 5px, 
                    rgba(60, 166, 166, 0.3) 10px 10px, 
                    rgba(60, 166, 166, 0.2) 15px 15px;;margin:10px;">
                    <input type="url" class="form-control" name="texto1" placeholder="Nombre" maxlength="255">
                        <br>
                    <input type="url" class="form-control" name="texto1" placeholder="Comentario 7" maxlength="255"> 
                        <br>
                </div>
<!--comentario 8 #026773 5CE2FF-->

                <div class="col-md-3 mb-3" style="border-radius:6px;box-shadow: 
                    rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
                    rgba(60, 64, 67, 0.15) 0px 1px 3px 1px,
                    rgba(2, 103, 115, 0.4) 5px 5px, 
                    rgba(2, 103, 115, 0.3) 10px 10px, 
                    rgba(2, 103, 115, 0.2) 15px 15px;;margin:10px;">
                    <input type="url" class="form-control" name="texto1" placeholder="Nombre" maxlength="255">
                        <br>
                    <input type="url" class="form-control" name="texto1" placeholder="Comentario 8" maxlength="255"> 
                        <br>
                </div>
<!--comentario 9 #024959 61CFFA-->

                <div class="col-md-4 mb-3" style="border-radius:6px;box-shadow: 
                    rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
                    rgba(60, 64, 67, 0.15) 0px 1px 3px 1px,
                    rgba(2, 73, 89, 0.4) 5px 5px, 
                    rgba(2, 73, 89, 0.3) 10px 10px, 
                    rgba(2, 73, 89, 0.2) 15px 15px;;margin:10px;">
                    <input type="url" class="form-control" name="texto1" placeholder="Nombre" maxlength="255">
                        <br>
                    <input type="url" class="form-control" name="texto1" placeholder="Comentario 9" maxlength="255"> 
                        <br>
                </div>

            </div>
            <br>
        </fieldset>

        
        
        <fieldset>
            <legend><i class="fas fa-phone"></i> &nbsp; Datos de contacto</legend>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" name="telefono_contacto" maxlength="11" required pattern="[0-9]{11}"
 required  value="<?php echo htmlspecialchars($local['telefono_contacto']); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" class="form-control" name="correo_contacto" maxlength="60" required
                        value="<?php echo htmlspecialchars($local['correo_contacto']); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Facebook</label>
                    <input type="url" class="form-control" name="link_facebook" maxlength="100"
                        value="<?php echo htmlspecialchars($local['link_facebook']); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Instagram</label>
                    <input type="url" class="form-control" name="link_instagram" maxlength="100"
                        value="<?php echo htmlspecialchars($local['link_instagram']); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Ciudad</label>
                    <input type="text" class="form-control" name="direccion_local_ciudad" maxlength="60" required
                        value="<?php echo htmlspecialchars($local['direccion_local_ciudad']); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">País</label>
                    <input type="text" class="form-control" name="direccion_local_pais" maxlength="60" required
                        value="<?php echo htmlspecialchars($local['direccion_local_pais']); ?>">
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend><i class="far fa-image"></i> &nbsp; Imágenes institucionales</legend>
            <div class="row">

                <!-- Fachada -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Imagen actual del local</label><br>
                    <img src="vistas/cm/fond/perro.webp" class="img-thumbnail" style="max-width: 200px;">
                    <label class="form-label mt-2">Reemplazar imagen</label>
                    <input type="file" name="imagen_local" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                </div>

                <!-- Mapa -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mapa actual</label><br>
                    <img src="<?php echo SERVERURL . 'vistas/' . $local['ruta_mapa']; ?>" class="img-thumbnail" style="max-width: 200px;">
                    <label class="form-label mt-2">Reemplazar imagen</label>
                    <input type="file" name="imagen_local_mapa" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                </div>

                <!-- Satélite -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Vista satelital actual</label><br>
                    <img src="<?php echo SERVERURL . 'vistas/' . $local['ruta_satelite']; ?>" class="img-thumbnail" style="max-width: 200px;">
                    <label class="form-label mt-2">Reemplazar imagen</label>
                    <input type="file" name="imagen_local_satelite" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                </div>

            </div>
        </fieldset>



<br>
<hr>
        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <img src="vistas/cm/fond/defont.webp" class="img-thumbnail" style="max-width: 300px;">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label mt-2">Imagen del footer</label>
                        <input type="file" name="imagen_local" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                        <hr>
                        <label class="form-label">Texto de la imagen del footer</label>
                        <input type="url" class="form-control" name="texto1" maxlength="255">
                    </div>
                </div>
                <br>
            </div>
        </div>




<div class="creditos footer" style="height:auto;box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;border-radius:6px;">

    <div class="row" style="width:80%;margin-top:5px;">
        <div class="col-md-12 mb-3" style="background-color:white;border-radius:6px;">
            <label class="form-label">Texto final del footer 1</label>
            <input type="url" class="form-control" name="texto1" maxlength="255">
            <label class="form-label">Texto final del footer 2</label>
            <input type="url" class="form-control" name="texto1" maxlength="255">
            <br>
        </div>
    </div>
    
    <div class="footer_creditos">
        <div class="footer_creditos_imagen">
            <img src="../vistas/assets/img/logoceprocahd1.webp" alt="porqueno">
        </div>
    	
        <div class="footer_creditos_siguenos">
        	<p>Síguenos</p>
            <div class="footer_creditos_siguenos_enlaces">
            </div>
        </div>
    	
        <div class="footer_creditos_copi">
            <p>© Centro Educativo Profesional Cecilio Acosta.2024</p>
        </div>
    </div>

</div><!--creditos-->




<div id="preview-container" style="display: flex; gap: 15px; margin-top: 20px;">
        <div id="preview-local" style="display:none;"><img src="" style="max-width:200px;"></div>
        <div id="preview-mapa" style="display:none;"><img src="" style="max-width:200px;"></div>
        <div id="preview-satelite" style="display:none;"><img src="" style="max-width:200px;"></div>
</div>
        <br>
        <p class="text-center">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-sync-alt"></i> ACTUALIZAR DATOS
            </button>
        </p>
    </form>
</div>





<script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form[data-form='update']");
    const camposImagen = [
        { inputName: "imagen_local", previewId: "preview-local" },
        { inputName: "imagen_local_mapa", previewId: "preview-mapa" },
        { inputName: "imagen_local_satelite", previewId: "preview-satelite" }
    ];

    camposImagen.forEach(campo => {
        const input = document.querySelector(`input[name="${campo.inputName}"]`);
        const previewContainer = document.getElementById(campo.previewId);
        const previewImg = previewContainer?.querySelector("img");

        if (!input) return;

        input.addEventListener("change", () => {
            const file = input.files[0];
            if (!file) {
                previewContainer && (previewContainer.style.display = "none");
                return;
            }

            const ext = file.name.split(".").pop().toLowerCase();
            if (!["jpg", "jpeg", "png", "webp"].includes(ext)) {
                input.value = "";
                previewContainer && (previewContainer.style.display = "none");
                return;
            }

            // Añadir campo oculto solo una vez
            if (!form.querySelector('input[name="imagen_cambiada"]')) {
                const hidden = document.createElement("input");
                hidden.type = "hidden";
                hidden.name = "imagen_cambiada";
                hidden.value = "1";
                form.appendChild(hidden);
            }

            // Mostrar preview
            if (previewImg) {
                const reader = new FileReader();
                reader.onload = () => {
                    previewImg.src = reader.result;
                    previewContainer.style.display = "block";
                };
                reader.readAsDataURL(file);
            }
        });
    });
});
</script>






<!--script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form[data-form='update']");
    const camposImagen = [
        { inputName: "imagen_local", previewId: "preview-local" },
        { inputName: "imagen_local_mapa", previewId: "preview-mapa" },
        { inputName: "imagen_local_satelite", previewId: "preview-satelite" }
    ];

    let seCambioImagen = false;

    camposImagen.forEach(campo => {
        const input = document.querySelector(`input[name="${campo.inputName}"]`);
        const previewContainer = document.getElementById(campo.previewId);
        const previewImg = previewContainer?.querySelector("img");

        if (!input) return;

        input.addEventListener("change", () => {
            const file = input.files[0];
            if (!file) {
                previewContainer && (previewContainer.style.display = "none");
                return;
            }

            const ext = file.name.split(".").pop().toLowerCase();
            if (!["jpg", "jpeg", "png", "webp"].includes(ext)) {
                alert("Formato de imagen no permitido.");
                input.value = "";
                previewContainer && (previewContainer.style.display = "none");
                return;
            }

            seCambioImagen = true;

            // Añadir campo oculto solo una vez
            if (!form.querySelector('input[name="imagen_cambiada"]')) {
                const hidden = document.createElement("input");
                hidden.type = "hidden";
                hidden.name = "imagen_cambiada";
                hidden.value = "1";
                form.appendChild(hidden);
            }

            // Mostrar preview
            if (previewImg) {
                const reader = new FileReader();
                reader.onload = () => {
                    previewImg.src = reader.result;
                    previewContainer.style.display = "block";
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Mensaje visual si se detecta al menos una imagen nueva
    form.addEventListener("submit", () => {
        if (seCambioImagen) {
            alert("✅ Imagen actualizada. Cambios guardados.");
        }
    });
});
</script-->

