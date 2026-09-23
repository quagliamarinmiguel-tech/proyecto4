<section class="full-box nav-lateral">
	<div class="full-box nav-lateral-bg show-nav-lateral"></div>
	<div class="full-box nav-lateral-content">
		<figure class="full-box nav-lateral-avatar"style="padding: 5%; margin-bottom: 0;">
			<i class="far fa-times-circle show-nav-lateral"></i>
			<img src="<?php echo SERVERURL; ?>vistas/assets/img/logoceprocahd1.webp" class="img-fluid w-25" alt="Avatar">
			<figcaption class="roboto-medium text-center">
				<?php $nombre = $_SESSION['nombre_spm'] ?? 'Usuario';
								$apellido = $_SESSION['apellido_spm'] ?? '';
								$cargo = $_SESSION['cargo_spm'] ?? '';

                        $cargo = $_SESSION['cargo_spm'] ?? 1;
                        $carpetasPorCargo = [
                            1 => "admin",
                            2 => "Control de estudio",
                            3 => "administrador de pagos",
                            4 => "Supervisor de cursos",
                            5 => "Estudiante"
                        ];

                        $carpeta = $carpetasPorCargo[$cargo] ?? "admin";        

				echo $nombre . " " . $apellido; ?> <br><small class="roboto-condensed-light"><?php echo $_SESSION['usuario_spm']; ?></small>
			</figcaption>
		</figure>
		<div class="full-box nav-lateral-bar"></div>
		<nav class="full-box nav-lateral-menu">
			<ul>
				<li>
					<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/home"><i class="fab fa-dashcube fa-fw"></i> &nbsp; Dashboard</a>
				</li>

				

				<!--li>
					<a href="#" class="nav-btn-submenu"> &nbsp; homes <i class="fas fa-chevron-down"></i></a>

					<ul>
						<li>
							<a href="<?php echo SERVERURL; ?>index.php?views=Supervisor de cursos/home">&nbsp;home supervisor</a><!--agregar validacion de cargo->
						</li>

						<li>
							<a href="<?php echo SERVERURL; ?>index.php?views=Estudiante/home">home estudiante</a>
						</li>

						<li>
							<a href="<?php echo SERVERURL; ?>index.php?views=Control de estudio/home"> &nbsp; administracion</a>
						</li>

						<li>
							<a href="<?php echo SERVERURL; ?>index.php?views=administrador de pagos/home"> &nbsp; control pagos</a>
						</li>

					</ul>
				</li-->
				
                <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4 || $_SESSION['cargo_spm'] == 3 || $_SESSION['cargo_spm'] == 2): ?>
				<li>
					<a href="#" class="nav-btn-submenu"> &nbsp; Cursos <i class="fas fa-chevron-down"></i></a>

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
						<li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/course-list-archived"> &nbsp; Lista de cursos archivados</a>
						</li>
                        <?php endif; ?>


					</ul>
				</li>

				<?php endif; ?>

				<?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4 || $_SESSION['cargo_spm'] == 3 || $_SESSION['cargo_spm'] == 2): ?>

				<li>
					<a href="#" class="nav-btn-submenu">&nbsp; Cohortes <i class="fas fa-chevron-down"></i></a>
					<ul>
                        
                        <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
                            <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/cohorte-new"> &nbsp; Nuevo cohorte</a>
                            </li>
                        <?php endif; ?>
					
						<li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/cohorte-list"> &nbsp; Lista de cohortes</a>
						</li>

					<?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
						<li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/cohorte-archived"> &nbsp; Lista de cohortes archivados</a>
						</li>
                    <?php endif; ?>


                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/seccion-archived"> &nbsp; Periodos / cohortes archivados</a>
						</li>
                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/seccion-list"> &nbsp; Periodos de cursos / cohortes</a>
						</li>		
                       


					</ul>
				</li>

				<?php endif; ?>
			
				<?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4 || $_SESSION['cargo_spm'] == 3 || $_SESSION['cargo_spm'] == 2): ?>

				<li>
					<a href="#" class="nav-btn-submenu"> &nbsp; Periodos de cursos int <i class="fas fa-chevron-down"></i></a>
					<ul>
                        
                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/cv-int-new"> &nbsp; Nuevo periodo de curso Int</a>
						</li>	
                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/cv-int-list"> &nbsp; Periodos de cursos Int</a>
						</li>	
                       <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
                             <li>
                                <a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/cv-int-list-archived"> &nbsp; Periodos intensivos archivados</a>
                            </li>
                        <?php endif; ?>

                        
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
                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/profesor-list-archived"> &nbsp; Lista de profesores archivados</a>
						</li>
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
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/cursop-list-archived"> &nbsp;Cursos publicitados archivados</a>
						</li>
						<li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/imagen-new">&nbsp; Nueva imágen de slider</a>
						</li>

						<!--li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/slider-home"> &nbsp;sliders</a>
						</li-->	

						<!--li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/anuncio-image"> &nbsp;imagen sin fondo</a>
						</li-->	                	
                    
                    </ul>
				</li>
                    <?php endif; ?>

                

				<!--li>
					<a href="#" class="nav-btn-submenu"> &nbsp; Publicidad de cursos <i class="fas fa-chevron-down"></i></a>
					<ul>


					</ul>
				</li-->




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

                <!-- ## LISTA DE COHORTE PARA PORTAL ADMINISTRACION PAGOS ## -->
				
                <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 3): ?>
                <li>
					<a href="#" class="nav-btn-submenu">&nbsp; Estudiantes <i class="fas fa-chevron-down"></i></a>
					<ul>

                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/registroEstudianteAdministracion"> &nbsp; Registrar estudiante</a>
						</li>
                        
                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/registroEstudianteAdministracion"> &nbsp; Lista de estudiantes(falta link)</a>
						</li>

                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/inscripciones_admin"> &nbsp; Inscripciones</a>
						</li>

                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/inscripciones-archivadas"> &nbsp; Inscripciones archivadas</a>
						</li>				

                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/inscripciones-archivadas"> &nbsp; Transacciones(falta link)</a>
						</li>
                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/inscripciones-archivadas"> &nbsp; Crear transc. (personal)</a>
						</li>		
					</ul>
				</li>
                <?php endif; ?>                
                
                
                <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 3): ?>
                <li>
					<a href="#" class="nav-btn-submenu">&nbsp; Solicitudes <i class="fas fa-chevron-down"></i></a>
					<ul>

                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/solicitud-list"> &nbsp; Lista de s. de nuevo ingreso</a>
						</li>
                        
                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/registroEstudianteAdministracion"> &nbsp; Lista de s. de inscripción</a>
						</li>

                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/inscripciones_admin"> &nbsp; Lista de s. de transacción</a>
						</li>

					</ul>
				</li>
                <?php endif; ?>   
                
                
                
                
                
                
                
                <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 3): ?>
                <li>
					<a href="#" class="nav-btn-submenu" style="color:red;">&nbsp; Administracion pagos <i class="fas fa-chevron-down"></i></a>
					<ul>
						<li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/ListCohorteAdministracion">&nbsp; Listas</a>
						</li>

                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/registroEstudianteAdministracion"> &nbsp; Registro de estudiante</a>
						</li>

                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/inscripciones_admin"> &nbsp; Inscripciones</a>
						</li>

                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/inscripciones-archivadas"> &nbsp; Inscripciones archivadas</a>
						</li>				

                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/solicitud-list"> &nbsp; Lista de solicitudes de inscripcion</a>
						</li>
					</ul>
				</li>
                <?php endif; ?>
                
                 <!-- ## LISTA DE COHORTE PARA PORTAL CONTROL DE ESTUDIO ## -->
				<?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 2): ?>
                <li>
					<a href="#" class="nav-btn-submenu"  style="color:red;"> &nbsp; Control de Estudio <i class="fas fa-chevron-down"></i></a>
					<ul>
						<li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/calificacion_registro"> &nbsp; Crear calificacion</a>
						</li>

                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/CrearEvaluacion"> &nbsp; Crear evaluaciones</a>
						</li>

                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/ListEvaluacionesGlobal"> &nbsp; Lista Evaluaciones</a>
						</li>
                        
                        <!--li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/Lista-secciones-graduadas"> &nbsp; Lista graduados</a>
						</li-->
					</ul>
				</li>
				<?php endif; ?>



                       <?php if($_SESSION['cargo_spm'] == 1): ?>
				<li>
					<a href="#" class="nav-btn-submenu">&nbsp; Usuarios <i class="fas fa-chevron-down"></i></a>
					<ul>
                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/user4-new"> &nbsp;Crear usuario-personal</a>
						</li>                        
                        <!--li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/user3-new"> &nbsp;Crear usuario-estudiante</a>
						</li-->
                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/user-estudiante-list"> &nbsp;Lista de usuario-estudiantes</a>
						</li>
                        <li>
							<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/user-personal-list"> &nbsp;Lista de usuario-personal</a>
						</li>
					</ul>
				</li>
                    <?php endif; ?>


                
            <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 3): ?>
            	<li>
					<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/Lista-secciones-graduadas"> &nbsp; Lista graduados</a>
				</li>
            <?php endif; ?>

           <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
				<li>
					<a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $carpeta; ?>/local"> &nbsp; Local</a>
				</li>
           <?php endif; ?>
                
		   <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 2 || $_SESSION['cargo_spm'] == 3 || $_SESSION['cargo_spm'] == 4): ?>
                <li>
					<a href="<?php echo SERVERURL; ?>documentos/Manual%20de%20Usuario%20para%20ceproca%20Stage%20Alfa.pdf" 
                     class="elegant-download-btn" 
              		 download="Manual_de_Usuario_CEPROCA.pdf"
               		 target="_blank">
                         <i class="fas fa-book"></i>
                         &nbsp; Manual de usuario
                    </a>
				</li>
           <?php endif; ?>
                        
                
				<!--?php } ?-->
			</ul>
		</nav>
	</div>
</section>
