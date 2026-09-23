<?php
	
	class vistasModelo{

		protected static function obtener_vistas_modelo($vistas): string {
    // Lista blanca de vistas válidas
            
            $listanegradedescarte=[
                "client-list", "client-new", "client-search", "client-update",
        		"company", "item-list", "item-new", "item-search", "item-update",
        		"reservation-list", "reservation-new", "reservation-pending", "reservation-search",
                "course-search","profesor-search","cursop-search",
                "course-vigente2-new", "course-vigente-int-new", "cv-int-list", "cv-int-update", "cv-int-list-archived",
                "anuncio-home","anuncio-new","anuncio-update","anuncio-list","anuncio-search","anuncio-image",
                "slider1-update","slider2-update","slider3-update",
                "user-search",
                "course-vigente-list", "course-vigente-update", "course-vigente-new", "course-vigente-archived",
        		"cohorte-new","cohorte-cursos", "cohorte-list","cohorte-update","curso-cohorte-update","cohorte-archived","cohorte-new2","cohorte2-update",
                "curso-vigente-new", "curso-vigente-list", "curso-vigente-update", "curso-vigente-list-archived", "curso-vigente-search","curso-terminado-list","curso-proximo-list", "curso-cohorte",
                "user2-list","user-personal-list","user-estudiante-list","user-upt-est","user-upt-per", "user2-new", "user4-new", "user3-new", "user2-search", "user2-update",
                "prueba1","prueba2","prueba3","prueba4", "prueba5", "prueba6", "prueba7", "prueba8", "prueba9"
            ];
            
    $listaBlanca = [
        "home", "inicio","cursos", "course-add","registro", "registrate", "crear-cuenta", "recuperar-cuenta", "recuperar-cuenta-validar", "cambiar-clave","solicitud-inscripcion", "cursos-detail",
        
        "user-list", "user-new", "user-update","user-activity-list",
        "course-update","course-new","course-list","course-list-archived",
        "seccion-new", "seccion-list","seccion-update","seccion-archived","seccion-archived2","seccion-list2","seccion-consult", "seccion-arc-consult", "seccion-consult-archived",
        "evaluacion-new","evaluacion-archived","evaluacion-update", "cal-excel",

        "solicitud-nuevo-ingreso-list", "solicitud-nuevo-ingreso-check",
        "period-new", "period-update", "period-list",  "period-list-archived", "period-course-new", "period-course-list", "period-course-list-archived",
        "profesor-new", "profesor-list","profesor-update","profesor-list-archived",
        "imagen-new","visitas-home","slider-home",
        
        "cursop-new","cursop-list","cursop-list-archived","cursop-update",
        "local","vistas-home","vistas-click-search","vistas-visit-search",
        
        "registroEstudiante", "registroEstudianteAdministracion", "inscribirse", "ListOwnerCourses", "ListCohorteAdministracion", "ListCursosCohorteAdministracion",
        "ListSectionCohorteAdministracion", "ListEstudiantesSeccionAdministracion", "ListEvaluacionSeccionAdministracion", 
        "calificacion_registro", "inscripciones_admin", "CrearEvaluacion", "ListEvaluacionesGlobal", "inscripciones-archivadas", "cohorteSearchAdministracion",
        "Lista-secciones-graduadas", "modificacion_estudiante", "nuevo_inicio"
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
    $carpeta = $carpetasPorCargo[$cargo] ?? "admin";

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


//($vista == "cursos" || "crear-cuenta" || "recuperar-cuenta" || "recuperar-cuenta-validar" || "cambiar-clave")
  //return "./vistas/contenidos/public/$vista-view.php";

if ($vista == "cursos" || $vista == "cursos-detail" || $vista == "crear-cuenta" || $vista == "recuperar-cuenta"
    || $vista == "recuperar-cuenta-validar" || $vista == "solicitud-inscripcion" || $vista == "cambiar-clave" || $vista == "nuevo_inicio")  {
  
    // $ruta = "./vistas/contenidos/public/cursos-view.php";
   // if (is_file($ruta)) {
   //     return $ruta;
 //   }
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