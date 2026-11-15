<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<!--title><c?php cecho COMPANY; c?></title-->

    <!--link rel="icon" href="<c?php echo SERVERURL; ?>vistas/assets/img/logoceproca.ico" type="image/x-icon"-->      
    <link rel="icon" href="<?php echo SERVERURL; ?>vistas/cm/ifpg/icono.ico" type="image/x-icon">      



    <?php
        require_once "./controladores/vistasControlador.php";
        $IV = new vistasControlador();
        $vistaActual = basename($IV->obtener_vistas_controlador(), "-view.php");
		//$bloqueado = in_array($vistaActual, $GLOBALS['vistas_sin_dark_mode'] ?? []);


        if ($vistaActual === "inicio" || $vistaActual === "cursos" || $vistaActual === "cursos-detail") { 
            include "./vistas/inc/Linkpublico.php"; // CSS para página de inicio
        }
    
    //     elseif ($vistaActual === "local") {
  //          include "./vistas/inc/Link_local.php"; // CSS exclusivo para local view
//        } 
		else {
            include "./vistas/inc/Link.php"; // CSS común para vistas internas
        }


		// trae el nombre y color del local
        require_once "./modelos/mainModel.php"; 

        $jsonLocal = mainModel::leer_json_local(['textos', 'paleta_id']);
        $textosJSON     = $jsonLocal['textos']    ?? [];

        $paletaInicioId = (int) ($jsonLocal['paleta_id'] ?? 0);
        $nombreCorto = $textosJSON['nombre_corto'] ?? '';
		if ($nombreCorto === '') {$nombreCorto = COMPANY;}

        $paletas       = mainModel::obtener_paletas();
        $coloresInicio = $paletas[$paletaInicioId] ?? $paletas[0];
        $primario   = $coloresInicio[0];

    ?>
	
	<title><?php echo $nombreCorto; ?></title>

</head>
<script>
(function() {
    const hash = window.location.hash;
    if (!hash || hash.length < 2) return;

    const params   = new URLSearchParams(hash.substring(1));
    const type     = params.get('type');
    const error    = params.get('error');
    const hasToken = params.has('access_token');

    const currentUrl     = window.location.href;
    const enCambiarClave = currentUrl.includes('views=cambiar-clave');
    const enLogin        = currentUrl.includes('views=login');

    let destino = null;

    if (error === 'access_denied') {
        if (!enLogin) destino = '<?= SERVERURL ?>index.php?views=login' + hash;
    } else if (type === 'recovery') {
        if (!enCambiarClave) destino = '<?= SERVERURL ?>index.php?views=cambiar-clave' + hash;
    } else if (hasToken || type === 'signup' || type === 'magiclink' || type === 'invite') {
        if (!enLogin) destino = '<?= SERVERURL ?>index.php' + hash;
    }

    if (destino !== null) {
        window.location.replace(destino);
    }
})();</script>

<body>
    
    
    
	<?php
		$peticionAjax=false;
		require_once "./controladores/vistasControlador.php";
		$IV = new vistasControlador();

		$vistas=$IV->obtener_vistas_controlador();
    
    
        //echo $vistas;
 
        sleep(1); // pausa de 1 segundo
		if($vistas=="login" || $vistas=="404"){
			require_once "./vistas/contenidos/admin/".$vistas."-view.php";
		}

		elseif($vistas=="inicio" || $vistas=="cursos" || $vistas=="cursos-detail" || $vistas=="registrate" || $vistas=="crear-cuenta" ||
               $vistas=="recuperar-cuenta" || $vistas=="solicitud-inscripcion" || $vistas=="recuperar-cuenta-validar" || $vistas=="cambiar-clave" || $vistas=="nuevo_inicio"){
                //    echo "hola";
			require_once "./vistas/contenidos/public/".$vistas."-view.php";
		}

        else{

                        
			session_start(['name'=>'SPM']);

			if(!isset($_SESSION['expira_en']) || time() >= (int) $_SESSION['expira_en']){
				session_unset();
				session_destroy();
				header("Location: ".SERVERURL."index.php?views=login");
				exit();
			}
			$_SESSION['ultima_actividad'] = time();

			$pagina=explode("/", $_GET['views']);

			require_once "./controladores/loginControlador.php";
			$lc = new loginControlador();

			if(!isset($_SESSION['token_spm']) || !isset($_SESSION['usuario_spm']) || !isset($_SESSION['cargo_spm']) || !isset($_SESSION['id_spm'])){
				echo $lc->forzar_cierre_sesion_controlador();
				exit();
			}

			require_once "./modelos/mainModel.php";
			//$pdo = mainModel::conectar();
			//mainModel::actualizarEstadoCohortes($pdo);
			//mainModel::actualizarEstadoPeriodosIntensivos($pdo);
            //mainModel::limpiarVisitantesYInteracciones($pdo);
            //falta el de elimina viitas cada 90 dias
	?>
    
    <!--bloquea el modo oscuro en las vistas publicas-->
    <!--script>window.DARK_MODE_BLOQUEADO = <c?= $bloqueado ? 'true' : 'false' ?>;</script-->
	
    <!-- Main container -->
	<main class="full-box main-container">
        
        <!-- Nav lateral -->
		<?php include "./vistas/inc/NavLateral.php"; ?>

		<!-- Page content -->
		<section class="full-box page-content">
			<?php 
				include "./vistas/inc/NavBar.php";

				include  $vistas;
			?>
		</section>
	</main>
	<?php
			include "./vistas/inc/LogOut.php";
		}
		include "./vistas/inc/Script.php"; 
	?>
</body>
</html>