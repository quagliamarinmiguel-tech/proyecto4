


<?php



$peticionAjax = true;

require_once "../config/APP.php";

// Activar errores en desarrollo (opcional)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../controladores/imagenControlador.php";
$img = new imagenControlador();


if(isset($_FILES['imagen']) && $_POST['tipo_seleccionado'] === "fond"){
    echo $img->subir_publicidad_img_controlador();
    exit();
}

if(isset($_FILES['imagen']) && isset($_POST['tipo_seleccionado']) && $_POST['tipo_seleccionado'] === "prof"){
    echo $img->subir_profesor_img_controlador();
    exit();
}

// Subida de imagen (al enviar el form normalmente)
if(isset($_FILES['imagen'])){
    echo $img->subir_slider_img_controlador();
    exit();
}

// Eliminación de imagen (al enviar un form con data-form="delete")
if(isset($_POST['id_imagen_del']) && isset($_POST['ruta_imagen_del'])){
    echo $img->eliminar_imagen_controlador($_POST['id_imagen_del'], $_POST['ruta_imagen_del']);
    exit();
}


if(isset($_GET['action']) && $_GET['action'] === "sugerencias" && isset($_GET['ruta'])){
    $ruta = $_GET['ruta'];
    $tipo = $_GET['tipo'] ?? 'sldr1';
    $excluir = (int)($_GET['excluir'] ?? 0);

    $ids_excluir = (new imagenControlador())->obtener_ids_imagenes_por_slider($tipo === 'sldr1' && $excluir === 1 ? 'sldr2' : 'sldr1');

    $imagenes = (new imagenControlador())->buscar_imagenes_por_ruta($tipo, $ruta, $ids_excluir);
    header("Content-Type: application/json");
    echo json_encode($imagenes);
    exit();
}


if(isset($_POST['slider_id']) && isset($_POST['ids']) && is_array($_POST['ids'])){
    echo $img->actualizar_slider_imagenes_controlador($_POST['slider_id'], $_POST['ids']);
    exit();
}

/*
if(isset($_FILES['imagen']) && isset($_POST['tipo_seleccionado']) && $_POST['tipo_seleccionado'] === "fond"){
    echo $img->subir_publicidad_img_controlador(); // Nueva función personalizada
    exit();
}*/

/*
if(isset($_GET['action']) && $_GET['action'] === "paginador-imagen-nuevo-ajax"){
    $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $tipo = $_GET['tipo'] ?? 'fond';
    $cantidad = isset($_GET['cantidad']) ? (int)$_GET['cantidad'] : 4;

    echo (new imagenControlador())->paginador_imagen_nuevo_ajax($tipo, $pagina, $cantidad);
    exit();
}*/

if(isset($_GET['action']) && $_GET['action'] === "paginador-imagen-nuevo-ajax"){
    $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $tipo = $_GET['tipo'] ?? 'fond';
    $cantidad = isset($_GET['cantidad']) ? (int)$_GET['cantidad'] : 4;
    $mostrarEliminar = ($_GET['eliminar'] ?? '1') === '1';

    echo (new imagenControlador())->paginador_imagen_nuevo_ajax($tipo, $pagina, $cantidad, $mostrarEliminar);
    exit();
}

if(isset($_GET['action']) && $_GET['action'] === "paginador-imagen-ajax"){
    $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $tipo = $_GET['tipo'] ?? 'sldr1';
    $excluir = isset($_GET['excluir']) ? (int)$_GET['excluir'] : 0; 

    echo (new imagenControlador())->paginador_imagen_ajax($tipo, $pagina, 8, $excluir);
    exit();
}

