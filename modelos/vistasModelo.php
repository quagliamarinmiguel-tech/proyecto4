<?php
	
	class vistasModelo{

		protected static function obtener_vistas_modelo($vistas): string {
    // Lista blanca de vistas válidas
            
    $listaBlanca = [
        "home", "inicio","cursos", "course-add","registro", "registrate", "crear-cuenta", "recuperar-cuenta", "recuperar-cuenta-validar", "cambiar-clave","solicitud-inscripcion", "cursos-detail",
        
        "user-list", "user-new", "user-update","user-activity-list","user-config",
        "course-update","course-new","course-list","course-list-archived",
        "seccion-new", "seccion-list","seccion-update","seccion-consult", "seccion-consult-archived",
        "evaluacion-new","evaluacion-archived","evaluacion-update", "cal-excel",

        "solicitud-nuevo-ingreso-list", "solicitud-nuevo-ingreso-check",
        "period-new", "period-update", "period-list",  "period-list-archived", "period-course-new", "period-course-list", "period-course-list-archived",
        "profesor-new", "profesor-list","profesor-update","profesor-list-archived",
        "imagen-new","visitas-home","slider-home",
        
        "cursop-new","cursop-list","cursop-list-archived","cursop-update",
        "local","vistas-home","local-config", "reportes",
        
        "registroEstudiante", "registroEstudianteAdministracion", "inscribirse", "ListOwnerCourses", "ListCohorteAdministracion", "ListCursosCohorteAdministracion",
        "ListSectionCohorteAdministracion", "ListEstudiantesSeccionAdministracion", "ListEvaluacionSeccionAdministracion", 
        "calificacion_registro", "inscripciones_admin", "CrearEvaluacion", "ListEvaluacionesGlobal", "inscripciones-archivadas", "cohorteSearchAdministracion",
        "Lista-secciones-graduadas", "modificacion_estudiante", "nuevo_inicio", "ListadeEstudiantesAdministracion", "ListaTransaccionesAdministracion", "inscripciones-pendientes", "transacciones-list"
    ];

    // Mapeo de cargos a nombres de carpeta
    $carpetasPorCargo = [
        1 => "admin",
        2 => "Supervisor de cursos",
        3 => "administrador de pagos",
        4 => "Control de estudio",
        5 => "Estudiante"
    ];

    // Obtener cargo del usuario desde la sesión
    $cargo = $_SESSION['cargo_spm'] ?? 1;
    $carpeta = "admin";

    // Sanitizar la entrada
    $vistas = trim($vistas);
    $vistas = str_replace("..", "", $vistas);

    // Separar carpeta/vista si viene explícita en la URL
    $partes = explode("/", $vistas);

    if (count($partes) == 2) {
        $carpeta = $partes[0];
        $vista = $partes[1];
    } else {
        $vista = $vistas;
    }


if ($vista == "cursos" || $vista == "cursos-detail" || $vista == "crear-cuenta" || $vista == "recuperar-cuenta"
    || $vista == "recuperar-cuenta-validar" || $vista == "solicitud-inscripcion" || $vista == "cambiar-clave" || $vista == "nuevo_inicio")  {
  
 return $vista;
 //  return "./vistas/contenidos/public/$vista-view.php";

}

    $ruta = "./vistas/contenidos/$carpeta/$vista-view.php";

    if (in_array($vista, $listaBlanca) && is_file($ruta)) {
        return $ruta;
    } elseif ($vista == "login" || $vista == "index") {
        return "login";
       //  return "./vistas/contenidos/admin/login-view.php";
    } else {
        return "404";
    }
}
	}