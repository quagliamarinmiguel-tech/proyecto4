<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title><?php echo COMPANY; ?></title>
    <link rel="icon" href="<?php echo SERVERURL; ?>vistas/assets/img/logoceproca.ico" type="image/x-icon">





	<!--?php include "./vistas/inc/Link.php"; ?-->



    <!--php
        require_once "./controladores/vistasControlador.php";
        $IV = new vistasControlador();
        $vistaActual = basename($IV->obtener_vistas_controlador(), "-view.php");

        if (!in_array($vistaActual, ['inicio', 'cursos'])) {
            include "./vistas/inc/Link.php"; // CSS común para vistas internas
        } else {
            include "./vistas/inc/Linkpublico.php"; // tus estilos únicos para inicio y cursos
        }
    ?-->

    <?php
        require_once "./controladores/vistasControlador.php";
        $IV = new vistasControlador();
        $vistaActual = basename($IV->obtener_vistas_controlador(), "-view.php");

        if ($vistaActual === "inicio") {
            include "./vistas/inc/Linkpublico.php"; // CSS para página de inicio
        } elseif ($vistaActual === "cursos") {
            include "./vistas/inc/Linkpublico2.php"; // CSS exclusivo para cursos
        } elseif ($vistaActual === "nuevo_inicio") {
            include "./vistas/inc/Linkpublico.php"; // CSS exclusivo para cursos
        } else {
            include "./vistas/inc/Link.php"; // CSS común para vistas internas
        }
    ?>


</head>
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

		elseif($vistas=="inicio" || $vistas=="cursos" || $vistas=="registrate" || $vistas=="crear-cuenta" || $vistas=="recuperar-cuenta" || $vistas=="solicitud-inscripcion" || $vistas=="recuperar-cuenta-validar" || $vistas=="cambiar-clave" ||
        $vistas=="nuevo_inicio"){
                //    echo "hola";
			require_once "./vistas/contenidos/public/".$vistas."-view.php";
		}

        else{


/*
        $vistaRuta = $IV->obtener_vistas_controlador();
        $vistaNombre = basename($vistaRuta, '-view.php'); // esta línea es clave
        echo $vistaNombre;

        if ($vistaNombre == "inicio" || $vistaNombre == "cursos") {
            require_once $vistaRuta;
        } elseif ($vistaNombre == "login" || $vistaNombre == "404") {
            require_once $vistaRuta;
        } else {

*/




			session_start(['name'=>'SPM']);

			$pagina=explode("/", $_GET['views']);

			require_once "./controladores/loginControlador.php";
			$lc = new loginControlador();

			if(!isset($_SESSION['token_spm']) || !isset($_SESSION['usuario_spm']) || !isset($_SESSION['cargo_spm']) || !isset($_SESSION['id_spm'])){
				echo $lc->forzar_cierre_sesion_controlador();
				exit();
			}

			require_once "./modelos/mainModel.php";
			$pdo = mainModel::conectar();
			//mainModel::actualizarEstadoCohortes($pdo);
			//mainModel::actualizarEstadoPeriodosIntensivos($pdo);
            mainModel::limpiarVisitantesYInteracciones($pdo);
            //falta el de elimina viitas cada 90 dias
	?>
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
