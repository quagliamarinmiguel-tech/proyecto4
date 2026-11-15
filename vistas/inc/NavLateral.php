<section class="full-box nav-lateral">
	<div class="full-box nav-lateral-bg show-nav-lateral"></div>
	<div class="full-box nav-lateral-content">
		<figure class="full-box nav-lateral-avatar"style="padding: 5%; margin-bottom: 0;">
			<i class="far fa-times-circle show-nav-lateral"></i>
			<!--img src="<c?php echo SERVERURL; ?>vistas/assets/img/logoceprocahd1.webp" class="img-fluid w-25" alt="Avatar"-->
			<img src="<?= mainModel::obtenerRutaImagenLocal('logo') ?>" class="img-fluid w-25" alt="Avatar">
    
			<figcaption class="roboto-medium text-center">
				<?php $nombre = $_SESSION['nombre_spm'] ?? 'Usuario';
								$apellido = $_SESSION['apellido_spm'] ?? '';
								$cargo = $_SESSION['cargo_spm'] ?? '';

                        $cargo = $_SESSION['cargo_spm'] ?? 1;
                        $carpetasPorCargo = [
                            1 => "admin",
                            3 => "Control de estudio",
                            2 => "administrador de pagos",
                            4 => "Supervisor de cursos",
                            5 => "Estudiante"
                        ];

                        $rolesPorCargo = [
                            1 => "Administrador",
                            2 => "Administrador de Pagos",
                            3 => "Control de Estudio",
                            4 => "Supervisor de Cursos",
                            5 => "Estudiante"
                        ];

                        $carpeta = "admin";        		
                        $rolNombre = $rolesPorCargo[(int) $cargo] ?? "Desconocido";


				echo $nombre . " " . $apellido; ?> <br><small class="roboto-condensed-light"><?php echo $_SESSION['usuario_spm']; ?></small>
                    <br><small  class="roboto-condensed-light">
                        <?= htmlspecialchars($rolNombre, ENT_QUOTES, 'UTF-8') ?>
                    </small>
			</figcaption>

        

		</figure>

        <style>
            :root {--color-primario:   <?= $primario ?>;}
        </style>

		<div class="full-box nav-lateral-bar" style="background-color: <?= $primario ?>;"></div>
		<nav class="full-box nav-lateral-menu">
			<ul>
				<li>
					<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/home"><i class="fab fa-dashcube fa-fw"></i> &nbsp; Dashboard</a>
				</li>

				
                <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4 || $_SESSION['cargo_spm'] == 3 || $_SESSION['cargo_spm'] == 2): ?>
                  		<li><a href="#" class="nav-btn-submenu"> &nbsp; Cursos <i class="fas fa-chevron-down"></i></a>

                        <ul>
                            <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/course-new">&nbsp; Nuevo curso</a>
                            </li>
                            <?php endif; ?>

                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/course-list"> &nbsp; Lista de cursos</a>
                            </li>

                          <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
                            <!--li>
                                <a href="<c?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/course-list-archived"> &nbsp; Lista de cursos archivados</a>
                            </li-->
                            <?php endif; ?>


                        </ul>
                    </li>
				<?php endif; ?>

				<?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4 || $_SESSION['cargo_spm'] == 3 || $_SESSION['cargo_spm'] == 2): ?>
                    <li>
                        <a href="#" class="nav-btn-submenu">&nbsp; Periodos <i class="fas fa-chevron-down"></i></a>
                        <ul>

                            <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
                                <li>
                                    <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/period-new"> &nbsp; Nuevo periodo</a>
                                </li>
                            <?php endif; ?>

                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/period-list"> &nbsp; Lista de periodos</a>
                            </li>

                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/seccion-list"> &nbsp; Secciones</a>
                            </li>
                        </ul>
                    </li>
				<?php endif; ?>
			
				
                <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4 || $_SESSION['cargo_spm'] == 3 || $_SESSION['cargo_spm'] == 2): ?>
                    <li>
                        <a href="#" class="nav-btn-submenu"> &nbsp; Profesores <i class="fas fa-chevron-down"></i></a>
                        <ul>

                           <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/profesor-new"> &nbsp; Nuevo Profesor</a>
                            </li>
                        <?php endif; ?>


                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/profesor-list"> &nbsp; Lista de profesores</a>
                            </li>						


                           <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
                            <!--li>
                                <a href="<c?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/profesor-list-archived"> &nbsp; Lista de profesores archivados</a>
                            </li-->
                        <?php endif; ?>

                        </ul>
                    </li>
				<?php endif; ?>

              <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
				<li>
					<a href="#" class="nav-btn-submenu">&nbsp; Portal publicitario <i class="fas fa-chevron-down"></i></a>
					<ul>
                        
                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/vistas-home"> &nbsp; control de visitas</a>
						</li>
                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/cursop-new"> &nbsp; Nueva publicidad de cursos</a>
						</li>

						<li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/cursop-list"> &nbsp;Lista de cursos publicitados</a>
						</li>						

						<li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/imagen-new">&nbsp; Sliders</a>
						</li>         	
                    
                    </ul>
				</li>
                <?php endif; ?>

                
                <!-- //INSCRIPCIONES PARA PORTAL ESTUDIANTE-->
                
                <?php if($_SESSION['cargo_spm'] == 5): ?>
                    <li>
                        <a href="#" class="nav-btn-submenu"> &nbsp; inscripciones portal estudiante <i class="fas fa-chevron-down"></i></a>
                        <ul>
                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/inscribirse"> &nbsp; inscribirse</a>
                            </li>

                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/ListOwnerCourses">&nbsp; Lista personal</a>
                            </li>						

                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/seccion-list"> &nbsp; Lista de cursos</a>
                            </li>

                        </ul>

                        <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/modificacion_estudiante"> &nbsp; Modificar datos del estudiante</a>
                    </li>
                <?php endif; ?>

				
                <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 2): ?>
                    <li>
                        <a href="#" class="nav-btn-submenu">&nbsp; Estudiantes <i class="fas fa-chevron-down"></i></a>
                        <ul>

                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/registroEstudianteAdministracion"> &nbsp; Registrar estudiante</a>
                            </li>

                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/ListadeEstudiantesAdministracion"> &nbsp; Lista de estudiantes</a>
                            </li>

                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/inscripciones_admin"> &nbsp; Inscripciones</a>
                            </li>

                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/inscripciones-archivadas"> &nbsp; Inscripciones archivadas</a>
                            </li>				

                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/ListaTransaccionesAdministracion"> &nbsp; Transacciones</a>
                            </li>
                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/inscripciones-archivadas"> &nbsp; Crear transc. (personal)</a>
                            </li>		
                        </ul>
                    </li>
                <?php endif; ?>                
                
                
                <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 2): ?>
                    <li>
                        <a href="#" class="nav-btn-submenu">&nbsp; Solicitudes <i class="fas fa-chevron-down"></i></a>
                        <ul>

                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/solicitud-nuevo-ingreso-list"> &nbsp; Lista de s. de nuevo ingreso</a>
                            </li>

                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/inscripciones-pendientes"> &nbsp; Lista de s. de inscripción</a>
                            </li>

                            <li>
    							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/transacciones-list">&nbsp; Lista de s. de transacciones</a>
							</li>

                        </ul>
                    </li>
                <?php endif; ?>   
 

                <?php if($_SESSION['cargo_spm'] == 1): ?>
                    <li>
                        <a href="#" class="nav-btn-submenu">&nbsp; Sistema <i class="fas fa-chevron-down"></i></a>
                        <ul>
                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/user-new"> &nbsp;Crear usuario-personal</a>
                            </li>                        
                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/user-list"> &nbsp;Lista de usuario-personal</a>
                            </li>

                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/user-activity-list"> &nbsp;Actividad de usuarios</a>
                            </li>

                            <li>
                                    <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/local-config"> &nbsp; Local</a>
                            </li>
                            
                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/reportes"> &nbsp;Reportes</a>
                            </li>

                        </ul>
                    </li>
                <?php endif; ?>

            <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 3): ?>
            	<li>
					<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/Lista-secciones-graduadas"> &nbsp; Lista graduados</a>
				</li>
            <?php endif; ?>

                
		   <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 2 || $_SESSION['cargo_spm'] == 3 || $_SESSION['cargo_spm'] == 4): ?>
                <li>
					<!--a href="<?php echo SERVERURL; ?>documentos/Manual%20de%20Usuario%20para%20ceproca%20Stage%20Alfa.pdf" -->
					<a href="<?php echo SERVERURL; ?>documentos/Manual_de_Usuario.pdf" 
                     class="elegant-download-btn" 
              		 download="Manual_de_Usuario_CEPROCA.pdf"
               		 target="_blank">
                         <i class="fas fa-book"></i>
                         &nbsp; Manual de usuario
                    </a>
				</li>
                                
            	    <li>
						<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/user-config"> &nbsp; Ajustes de cuenta</a>
					</li>
               
           <?php endif; ?>
                        
                
				<!--?php } ?-->
			</ul>
		</nav>
	</div>
</section>

<!--script>
console.log('[CEPROCA][NavLateral] renderizado', {
	cargo: <php echo json_encode($cargo); ?>,
	carpeta: <php echo json_encode($carpeta); ?>,
	submenus: document.querySelectorAll('.nav-btn-submenu').length,
	botonesMostrar: document.querySelectorAll('.show-nav-lateral').length
});
</script-->