<?php
if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

require_once "./controladores/localControlador.php";
$ctrl = new localControlador();
$datos = $ctrl->datos_local_controlador(); // ← Devuelve el registro único con imágenes

if ($datos->rowCount() != 1) {
    echo '<div class="alert alert-danger text-center">No se encontró la información institucional.</div>';
    exit();
}

$local = $datos->fetch();

// Extraer textos JSON
$textosJSON = [];
if (!empty($local['textos_pagina'])) {
    $decoded = json_decode($local['textos_pagina'], true);
    if (is_array($decoded)) {
        $textosJSON = $decoded;
    }
}

// Extraer imágenes mapeadas
$imagenesMap = [];
$resImg = $ctrl->obtener_imagenes_local_controlador();
while($img = $resImg->fetch(PDO::FETCH_ASSOC)) {
    $imagenesMap[$img['nom_esp']] = $img['ruta'];
}

// Función auxiliar para obtener textos del JSON
function getJsonText($key, $array) {
    return isset($array[$key]) ? htmlspecialchars($array[$key]) : '';
}

// Función auxiliar para obtener ruta de imagen mapeada
function getMappedImage($nom_esp, $array) {
    return isset($array[$nom_esp]) ? SERVERURL . "vistas/" . $array[$nom_esp] : SERVERURL . "vistas/assets/img/default.png";
}

// Función para convertir hex a rgb
function hex2rgb($hex)
{
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return "$r, $g, $b";
}

// Paletas de colores
$paletas = [
    0 => ['#00a9f1', '#002a6f', '#72F2EB', '#FF5F5D', '#3ca6a6'],
    1 => ['#9C2113', '#F9ECEC', '#014B43', '#FFD6D1', '#099078'],
    2 => ['#1C338C', '#1B1E59', '#8C1B1B', '#F2F2F2', '#262626'],
    3 => ['#BF0413', '#F27329', '#F24F13', '#F9ECEC', '#A60303'],
    4 => ['#518C81', '#667302', '#515916', '#D9C196', '#D92A1A'],
    5 => ['#FFEFC8', '#7A2B1F', '#471B00', '#522D00', '#E8A149'],
    6 => ['#F9D746', '#142C49', '#F3E3C6', '#4F7FE7', '#FFFFFF'],
    7 => ['#B3A5CC', '#E9EEFF', '#CCBAA6', '#9E82D9', '#C2ACF2'],
    8 => ['#27498C', '#1A4173', '#2E578C', '#B1BBC7', '#D9D9D7'],
    9 => ['#EB7428', '#4F342B', '#F1CDAB', '#D7EFE1', '#EFA438'],
    10 => ['#FBFFFF', '#EBC9C0', '#D48392', '#B73736', '#CFAA7D'],
    11 => ['#010326', '#024959', '#208C8C', '#89D9C2', '#0D0D0D'],
    12 => ['#F5F3F0', '#E8DFD1', '#5C1C3C', '#94215A', '#BAAC97'],
    13 => ['#193C40', '#2E5902', '#1E661B', '#D96941', '#A62B1F']
];

$paleta_activa = 0;
if (!empty($local['colores_pagina'])) {
    $decodedColores = json_decode($local['colores_pagina'], true);
    if (is_array($decodedColores) && isset($decodedColores['paleta_id'])) {
        $paleta_activa = (int) $decodedColores['paleta_id'];
    }
} else if (isset($_COOKIE['paleta_activa'])) {
    $paleta_activa = (int) $_COOKIE['paleta_activa'];
}
$colores = $paletas[$paleta_activa] ?? $paletas[0];
?>


<style>
    /* ============================================
           VARIABLES CSS DINÁMICAS
        ============================================ */
    :root {
        --color-primario:
            <?php echo $colores[0]; ?>
        ;
        --color-secundario:
            <?php echo $colores[1]; ?>
        ;
        --color-terciario:
            <?php echo $colores[2]; ?>
        ;
        --color-cuaternario:
            <?php echo $colores[3]; ?>
        ;
        --color-quinario:
            <?php echo $colores[4]; ?>
        ;
        --shadow-color1:
            <?php echo hex2rgb($colores[0]); ?>
        ;
        --shadow-color2:
            <?php echo hex2rgb($colores[1]); ?>
        ;
        --shadow-color3:
            <?php echo hex2rgb($colores[2]); ?>
        ;
        --shadow-color4:
            <?php echo hex2rgb($colores[3]); ?>
        ;
        --shadow-color5:
            <?php echo hex2rgb($colores[4]); ?>
        ;
    }

    /* ============================================
           ESTILOS GENERALES
        ============================================ */
    /*/ {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }*/

    /*
        body {
            font-family: 'Nunito', 'Poppins', sans-serif;
            background-color: #f5f5f5;
        }*/

    /* ============================================
           HEADER Y BARRA DE NAVEGACIÓN
        ============================================ */
    .div-header {
        height: 10vh;
        z-index: 99;
        width: 100%;
        display: flex;
        justify-content: space-between;
        background-color: var(--color-primario);
        box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
        border-radius: 3px;
    }

    .logo-ceproca {
        width: 20%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .logo-ceproca a {
        text-decoration: none;
    }

    .logo-ceproca a img {
        margin: 8px;
        width: 40px;
        height: auto;
    }

    .logo-ceproca h3 b,
    .logo-ceproca p {
        padding-top: 10px;
        color: white;
    }

    .header-barra {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        width: 75%;
        height: 100%;
        margin-right: 20px;
    }

    .header-barra ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .header-barra ul li {
        display: inline-block;
        position: relative;
        border-radius: 6px;
    }

    .header-barra ul li a {
        display: block;
        padding: 10px 5px;
        color: white;
        text-decoration: none;
        text-align: center;
        border-radius: 6px;
        background-color: var(--color-primario);
        transition: background-color 0.3s ease;
    }

    .header-barra ul li a:hover {
        background-color: var(--color-terciario);
    }

    .dropdown-menu-custom {
        width: 100%;
        background-color: var(--color-primario);
        position: absolute;
        z-index: 999;
        display: none;
        border-radius: 6px;
        padding: 0;
    }

    .dropdown-menu-custom li {
        display: block;
    }

    .header-barra ul li:hover .dropdown-menu-custom {
        display: block;
    }

    .contactos-header {
        width: inherit;
        height: inherit;
        cursor: pointer;
        background-color: var(--color-primario);
        border-radius: 6px;
    }

    .contactos-header p {
        padding: 15px 5px;
        color: white;
        text-decoration: none;
        text-align: center;
        font-size: 6rem;
        border-radius: 6px;
        margin: 0;
    }

    .contactos-header p:hover {
        background-color: var(--color-terciario);
    }

    /* ============================================
           SLIDERS
        ============================================ */
    .nuevoslider {
        display: flex;
        flex-wrap: wrap;
        padding: 20px;
        border-radius: 12px;
        margin: 20px 0;
    }

    .nuevoslider:nth-of-type(1) {
        background-color: var(--color-secundario);
    }

    .nuevoslider:nth-of-type(2) {
        background-color: var(--color-terciario);
    }

    .nuevoslider:nth-of-type(3) {
        background-color: var(--color-cuaternario);
    }

    .primer-slider-texto {
        border-radius: 6px;
        text-align: center;
    }

    .newslider {
        border-radius: 12px;
        margin: 30px;
    }

    .width400 {
        width: 400px;
        max-width: 100%;
    }

    .width400 img {
        border-radius: 12px;
        width: 100%;
        height: auto;
    }

    /* ============================================
           TARJETAS Y BOX SHADOWS DINÁMICOS
        ============================================ */
    .shadow-dinamico {
        box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px,
            rgba(var(--shadow-color1), 0.8) 0px -6px,
            rgba(var(--shadow-color2), 0.7) 0px -14px,
            rgba(var(--shadow-color3), 0.6) 0px -22px,
            rgba(var(--shadow-color4), 0.5) 0px -30px,
            rgba(var(--shadow-color5), 0.4) 0px -38px;
        border-radius: 6px;
        text-align: center;
        padding: 15px;
        background: white;
    }

    /* ============================================
           COMENTARIOS (COLORES FIJOS)
        ============================================ */
    .comentario-card {
        border-radius: 6px;
        margin: 10px;
        background: white;
        padding: 15px;
    }

    .comentario-1 {
        box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px, rgba(245, 73, 39, 0.4) 5px 5px, rgba(245, 73, 39, 0.3) 10px 10px, rgba(245, 73, 39, 0.2) 15px 15px;
    }

    .comentario-2 {
        box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px, rgba(102, 153, 255, 0.4) 5px 5px, rgba(102, 153, 255, 0.3) 10px 10px, rgba(102, 153, 255, 0.2) 15px 15px;
    }

    .comentario-3 {
        box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px, rgba(102, 51, 255, 0.4) 5px 5px, rgba(102, 51, 255, 0.3) 10px 10px, rgba(102, 51, 255, 0.2) 15px 15px;
    }

    .comentario-4 {
        box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px, rgba(102, 51, 204, 0.4) 5px 5px, rgba(102, 51, 204, 0.3) 10px 10px, rgba(102, 51, 204, 0.2) 15px 15px;
    }

    .comentario-5 {
        box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px, rgba(51, 102, 255, 0.4) 5px 5px, rgba(51, 102, 255, 0.3) 10px 10px, rgba(51, 102, 255, 0.2) 15px 15px;
    }

    .comentario-6 {
        box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px, rgba(0, 51, 255, 0.4) 5px 5px, rgba(0, 51, 255, 0.3) 10px 10px, rgba(0, 51, 255, 0.2) 15px 15px;
    }

    .comentario-7 {
        box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px, rgba(60, 166, 166, 0.4) 5px 5px, rgba(60, 166, 166, 0.3) 10px 10px, rgba(60, 166, 166, 0.2) 15px 15px;
    }

    .comentario-8 {
        box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px, rgba(2, 103, 115, 0.4) 5px 5px, rgba(2, 103, 115, 0.3) 10px 10px, rgba(2, 103, 115, 0.2) 15px 15px;
    }

    .comentario-9 {
        box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px, rgba(2, 73, 89, 0.4) 5px 5px, rgba(2, 73, 89, 0.3) 10px 10px, rgba(2, 73, 89, 0.2) 15px 15px;
    }

    /* ============================================
           FOOTER
        ============================================ */
    .footer {
        width: 100%;
        height: auto;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        align-items: center;
        background-color: var(--color-primario);
        margin-top: 30px;
        padding: 20px 0;
    }

    .footer p {
        text-align: center;
        width: 100%;
        color: white;
        margin: 0;
    }

    .tlfno-y-correo-footer {
        width: 98%;
        display: flex;
        margin: 1%;
        justify-content: center;
        align-items: flex-start;
        flex-direction: row;
        background-color: inherit;
        height: auto;
    }

    .datos-tlfno-y-correo-footer {
        margin: 0 1%;
        height: auto;
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        flex-direction: row;
        padding: 0;
        width: auto;
    }

    .datos-tlfno-y-correo-footer p {
        padding: 0;
        color: white;
    }

    .datos-tlfno-y-correo-footer i {
        color: white;
        padding: 2%;
        margin: 0;
    }

    .footer-creditos {
        height: 36%;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .footer-creditos-imagen {
        width: 33%;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }

    .footer-creditos-imagen img {
        margin: 3px;
        width: 60px;
        height: 60px;
        transition: all 0.3s ease;
    }

    .footer-creditos-imagen img:hover {
        background-color: var(--color-secundario);
    }

    .footer-creditos-siguenos {
        width: 33%;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        justify-content: center;
    }

    .footer-creditos-siguenos p {
        text-align: center;
        width: 100%;
        color: white;
    }

    .footer-creditos-siguenos-enlaces {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }

    .footer-creditos-siguenos-enlaces a {
        text-decoration: none;
        margin: 5px;
        text-align: center;
        width: auto;
    }

    .footer-creditos-siguenos-enlaces a img {
        border-radius: 50%;
    }

    .footer-creditos-copi {
        width: 33%;
        text-align: center;
    }

    /* ============================================
           SELECTOR DE PALETAS
        ============================================ */
    .selector-paletas {
        margin-bottom: 20px;
    }

    .paleta-opcion {
        cursor: pointer;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid transparent;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .paleta-opcion:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    .paleta-colores {
        display: flex;
        height: 40px;
    }

    .paleta-color {
        flex: 1;
    }

    .paleta-nombre {
        text-align: center;
        font-size: 12px;
        background-color: #f8f9fa;
        padding: 4px;
    }

    /* Grid de 7 columnas para el selector */
    .paletas-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
    }

    @media (max-width: 992px) {
        .paletas-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 576px) {
        .paletas-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    /* ============================================
           BOTONES Y FORMULARIOS
        ============================================ */
    .btn-actualizar {
        background-color: var(--color-primario) !important;
        border-color: var(--color-primario) !important;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-actualizar:hover {
        background-color: var(--color-terciario) !important;
        border-color: var(--color-terciario) !important;
        transform: translateY(-2px);
    }

    .form-control:focus {
        border-color: var(--color-primario);
        box-shadow: 0 0 0 0.2rem rgba(var(--shadow-color1), 0.25);
    }

    /* ============================================
           IMÁGENES Y PREVIEWS
        ============================================ */
    .img-thumbnail-custom {
        max-width: 300px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .preview-container {
        display: flex;
        gap: 15px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .preview-item {
        display: none;
    }

    .preview-item img {
        max-width: 200px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    /* ============================================
           RESPONSIVE
        ============================================ */
    @media (max-width: 639px) {
        .nuevoslider {
            flex-wrap: wrap;
        }

        .primer-slider-texto {
            width: 100%;
            text-align: center;
            margin-right: 0;
        }

        .newslider {
            margin: 15px;
        }

        .width400 {
            width: 100%;
        }

        .footer-creditos-imagen,
        .footer-creditos-siguenos,
        .footer-creditos-copi {
            width: 100%;
            margin: 10px 0;
        }
    }

    /* Utilidades */
    .bg-white-custom {
        background-color: white;
    }

    .rounded-custom {
        border-radius: 6px;
    }

    .p-20 {
        padding: 20px;
    }

    .m-10 {
        margin: 10px;
    }
</style>

<style id="tema-dinamico">
    /* Este estilo se actualizará vía JavaScript */
</style>
</head>

<body>

    <div class="container-fluid">

        <!-- SELECTOR DE PALETAS DE COLORES -->
        <div class="row mt-3 mb-3">
            <div class="col-12">
                <div class="card selector-paletas">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-palette"></i> Personalizar colores de la página
                    </div>
                    <div class="card-body">
                        <div class="paletas-grid" id="selector-paletas">
                            <?php foreach ($paletas as $idx => $paleta): ?>
                                <div class="paleta-opcion <?php echo ($paleta_activa == $idx) ? 'active' : ''; ?>"
                                    data-paleta="<?php echo $idx; ?>">
                                    <div class="paleta-colores">
                                        <?php foreach ($paleta as $color): ?>
                                            <div class="paleta-color" style="background-color: <?php echo $color; ?>;"></div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="paleta-nombre">Paleta <?php echo $idx; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HEADER -->
        <div class="div-header">
            <div class="logo-ceproca">
                <a href="#"><img src="../vistas/assets/img/logoceprocahd1.webp" alt="Logo CEPROCA"></a>
                <p><b>CEPROCA</b></p>
            </div>
            <div class="header-barra">
                <ul>
                    <li><a href="#">Iniciar sesión</a></li>
                    <li><a href="#">Regístrate</a></li>
                </ul>
            </div>
        </div>

        <div style="height: 5vh;"></div>

        <!-- TÍTULO DEL FORMULARIO -->
        <div class="full-box page-header">
            <h3 class="text-left"><i class="fas fa-map-marked-alt fa-fw"></i> &nbsp; ACTUALIZAR INFORMACIÓN CEPROCA</h3>
            <p class="text-justify">
                Edita los datos institucionales y reemplaza las imágenes del local, mapa y vista satelital si es
                necesario.
            </p>
        </div>

        <!-- FORMULARIO PRINCIPAL -->
        <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/localAjax.php" method="POST"
            data-form="update" enctype="multipart/form-data" autocomplete="off">

            <input type="hidden" name="local_id_up" value="1">
            <input type="hidden" name="paleta_activa" id="paleta_activa_input" value="<?php echo $paleta_activa; ?>">
            <input type="hidden" name="imagen_local_fk" value="<?php echo $local['imagen_local']; ?>">
            <input type="hidden" name="imagen_local_mapa_fk" value="<?php echo $local['imagen_local_mapa']; ?>">
            <input type="hidden" name="imagen_local_satelite_fk" value="<?php echo $local['imagen_local_satelite']; ?>">

            <!-- SECCIÓN: ÍCONO Y LOGO -->
            <fieldset>
                <legend><i class="fas fa-image"></i> Ícono y logo del local</legend>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <img src="<?php echo getMappedImage('icono', $imagenesMap); ?>" class="img-thumbnail-custom"
                                    alt="Icono actual">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label mt-2">Icono de las pestañas</label>
                                <input type="file" name="icono" class="form-control-file"
                                    accept=".ico">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <img src="<?php echo getMappedImage('logo', $imagenesMap); ?>" class="img-thumbnail-custom" alt="Logo actual">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label mt-2">Logo del local</label>
                                <input type="file" name="logo" class="form-control-file"
                                    accept=".jpg,.jpeg,.png,.webp">
                            </div>
                        </div>
                    </div>
                </div>



                <!--datos del header-->
                <div class="row" style="background-color:white;">
                    <div class="col-md-12 mb-3">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <img src="<?php echo getMappedImage('img_header', $imagenesMap); ?>" class="img-thumbnail" style="max-width: 300px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label mt-2">Imagen del header</label>
                                <input type="file" name="img_header" class="form-control-file"
                                    accept=".jpg,.jpeg,.png,.webp">
                                <hr>
                                <label class="form-label">Texto de la imagen del header</label>
                                <input type="text" class="form-control" name="txt_header" maxlength="255" value="<?php echo getJsonText('txt_header', $textosJSON); ?>">
                            </div>
                        </div>
                        <br>
                    </div>

                    <div class="col-md-12 mb-3"
                        style="display:flex;justify-content:center;align-items:center;margin:10 0px;">
                        <div class="col-md-12 mb-3 shadow-dinamico" style="border-radius:6px;text-align:center; ">
                            <label class="form-label" style="font-weight:bold;">Subtítulo</label>
                            <input type="text" style="font-weight:bold;text-transform: uppercase;" class="form-control"
                                name="txt_titulo_post_header" maxlength="100" value="<?php echo getJsonText('txt_titulo_post_header', $textosJSON); ?>">
                            <input type="text" style="font-weight:bold;text-transform: uppercase;padding:10 0px;"
                                class="form-control" name="txt_subtitulo_post_header" maxlength="100" value="<?php echo getJsonText('txt_subtitulo_post_header', $textosJSON); ?>">
                            <br>
                        </div>
                    </div>
                </div>





            </fieldset>

            <!-- SECCIÓN: SLIDERS -->
            <fieldset>
                <legend><i class="fas fa-sliders-h"></i> Información del local</legend>

                <!-- Slider 1 -->
                <div class="nuevoslider">
                    <div class="col-md-6 mb-3 primer-slider-texto bg-white-custom p-20">
                        <label class="form-label mt-2">Imagen 1</label>
                        <input type="file" name="img_desc1" class="form-control-file"
                            accept=".jpg,.jpeg,.png,.webp">
                        <hr>
                        <label class="form-label">Título 1</label>
                        <input type="text" class="form-control" name="txt_titulo_info1" maxlength="255" value="<?php echo getJsonText('txt_titulo_info1', $textosJSON); ?>">
                        <hr>
                        <label class="form-label">Subtítulo 1</label>
                        <input type="text" class="form-control" name="txt_subtitulo_info1" maxlength="255" value="<?php echo getJsonText('txt_subtitulo_info1', $textosJSON); ?>">
                    </div>
                    <div class="newslider width400">
                        <img class="width400" loading="lazy"
                            src="<?php echo getMappedImage('img_desc1', $imagenesMap); ?>"
                            alt="Imagen del Slider 1">
                    </div>
                </div>

                <!-- Slider 2 -->
                <div class="nuevoslider">
                    <div class="newslider width400">
                        <img class="width400" loading="lazy"
                            src="<?php echo getMappedImage('img_desc2', $imagenesMap); ?>"
                            alt="Imagen del Slider 2">
                    </div>
                    <div class="col-md-6 mb-3 primer-slider-texto bg-white-custom p-20">
                        <label class="form-label mt-2">Imagen 2</label>
                        <input type="file" name="img_desc2" class="form-control-file"
                            accept=".jpg,.jpeg,.png,.webp">
                        <hr>
                        <label class="form-label">Título 2</label>
                        <input type="text" class="form-control" name="txt_titulo_info2" maxlength="255" value="<?php echo getJsonText('txt_titulo_info2', $textosJSON); ?>">
                        <hr>
                        <label class="form-label">Subtítulo 2</label>
                        <input type="text" class="form-control" name="txt_subtitulo_info2" maxlength="255" value="<?php echo getJsonText('txt_subtitulo_info2', $textosJSON); ?>">
                    </div>
                </div>

                <!-- Slider 3 -->
                <div class="nuevoslider">
                    <div class="col-md-6 mb-3 primer-slider-texto bg-white-custom p-20">
                        <label class="form-label mt-2">Imagen 3</label>
                        <input type="file" name="img_desc3" class="form-control-file"
                            accept=".jpg,.jpeg,.png,.webp">
                        <hr>
                        <label class="form-label">Título 3</label>
                        <input type="text" class="form-control" name="txt_titulo_info3" maxlength="255" value="<?php echo getJsonText('txt_titulo_info3', $textosJSON); ?>">
                        <hr>
                        <label class="form-label">Subtítulo 3</label>
                        <input type="text" class="form-control" name="txt_subtitulo_info3" maxlength="255" value="<?php echo getJsonText('txt_subtitulo_info3', $textosJSON); ?>">
                    </div>
                    <div class="newslider width400">
                        <img class="width400" loading="lazy"
                            src="<?php echo getMappedImage('img_desc3', $imagenesMap); ?>"
                            alt="Imagen del Slider 3">
                    </div>
                </div>
            </fieldset>

            <!-- SECCIÓN: COMENTARIOS (Colores fijos) -->
            <fieldset>
                <legend><i class="fas fa-comments"></i> Comentarios destacados y add</legend>
                <div class="row justify-content-center">

                    <div class="col-md-12 mb-3"
                        style="display:flex;justify-content:center;align-items:center;margin:10 0px;">
                        <div class="col-md-12 mb-3" style="border-radius:6px;text-align:center; ">
                            <label class="form-label" style="font-weight:bold;color:black;">URL de anuncio del local
                                (Facebook o Youtube)</label>
                            <input type="url" class="form-control"
                                name="url_add" maxlength="100" value="<?php echo getJsonText('url_add', $textosJSON); ?>">
                            <br>
                        </div>
                    </div>


                    <div class="col-md-5 mb-3 comentario-card comentario-1">
                        <input type="text" class="form-control mb-2" name="nombre_com1" placeholder="Nombre"
                            maxlength="255" value="<?php echo getJsonText('nombre_com1', $textosJSON); ?>">
                        <textarea class="form-control" name="texto_com1" placeholder="Comentario 1"
                            rows="3"><?php echo getJsonText('texto_com1', $textosJSON); ?></textarea>
                    </div>

                    <div class="col-md-5 mb-3 comentario-card comentario-2">
                        <input type="text" class="form-control mb-2" name="nombre_com2" placeholder="Nombre"
                            maxlength="255" value="<?php echo getJsonText('nombre_com2', $textosJSON); ?>">
                        <textarea class="form-control" name="texto_com2" placeholder="Comentario 2"
                            rows="3"><?php echo getJsonText('texto_com2', $textosJSON); ?></textarea>
                    </div>

                    <div class="col-md-5 mb-3 comentario-card comentario-3">
                        <input type="text" class="form-control mb-2" name="nombre_com3" placeholder="Nombre"
                            maxlength="255" value="<?php echo getJsonText('nombre_com3', $textosJSON); ?>">
                        <textarea class="form-control" name="texto_com3" placeholder="Comentario 3"
                            rows="3"><?php echo getJsonText('texto_com3', $textosJSON); ?></textarea>
                    </div>

                    <div class="col-md-5 mb-3 comentario-card comentario-4">
                        <input type="text" class="form-control mb-2" name="nombre_com4" placeholder="Nombre"
                            maxlength="255" value="<?php echo getJsonText('nombre_com4', $textosJSON); ?>">
                        <textarea class="form-control" name="texto_com4" placeholder="Comentario 4"
                            rows="3"><?php echo getJsonText('texto_com4', $textosJSON); ?></textarea>
                    </div>

                    <div class="col-md-5 mb-3 comentario-card comentario-5">
                        <input type="text" class="form-control mb-2" name="nombre_com5" placeholder="Nombre"
                            maxlength="255" value="<?php echo getJsonText('nombre_com5', $textosJSON); ?>">
                        <textarea class="form-control" name="texto_com5" placeholder="Comentario 5"
                            rows="3"><?php echo getJsonText('texto_com5', $textosJSON); ?></textarea>
                    </div>

                    <div class="col-md-5 mb-3 comentario-card comentario-6">
                        <input type="text" class="form-control mb-2" name="nombre_com6" placeholder="Nombre"
                            maxlength="255" value="<?php echo getJsonText('nombre_com6', $textosJSON); ?>">
                        <textarea class="form-control" name="texto_com6" placeholder="Comentario 6"
                            rows="3"><?php echo getJsonText('texto_com6', $textosJSON); ?></textarea>
                    </div>

                    <div class="col-md-4 mb-3 comentario-card comentario-7">
                        <input type="text" class="form-control mb-2" name="nombre_com7" placeholder="Nombre"
                            maxlength="255" value="<?php echo getJsonText('nombre_com7', $textosJSON); ?>">
                        <textarea class="form-control" name="texto_com7" placeholder="Comentario 7"
                            rows="3"><?php echo getJsonText('texto_com7', $textosJSON); ?></textarea>
                    </div>

                    <div class="col-md-3 mb-3 comentario-card comentario-8">
                        <input type="text" class="form-control mb-2" name="nombre_com8" placeholder="Nombre"
                            maxlength="255" value="<?php echo getJsonText('nombre_com8', $textosJSON); ?>">
                        <textarea class="form-control" name="texto_com8" placeholder="Comentario 8"
                            rows="3"><?php echo getJsonText('texto_com8', $textosJSON); ?></textarea>
                    </div>

                    <div class="col-md-4 mb-3 comentario-card comentario-9">
                        <input type="text" class="form-control mb-2" name="nombre_com9" placeholder="Nombre"
                            maxlength="255" value="<?php echo getJsonText('nombre_com9', $textosJSON); ?>">
                        <textarea class="form-control" name="texto_com9" placeholder="Comentario 9"
                            rows="3"><?php echo getJsonText('texto_com9', $textosJSON); ?></textarea>
                    </div>
                </div>
            </fieldset>

            <!-- SECCIÓN: DATOS DE CONTACTO -->
            <fieldset>
                <legend><i class="fas fa-phone-alt"></i> Datos de contacto</legend>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre del local (completo)</label>
                        <input type="text" class="form-control" name="nombre_completo" id="local_nombre_completo" required value="<?php echo htmlspecialchars($local['nombre_local'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nombre del local (corto)</label>
                        <input type="text" class="form-control" name="nombre_corto" id="local_nombre_corto" required value="<?php echo htmlspecialchars($local['nombre_local_corto'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">RIF (Registro de Información Fiscal)</label>
                        <input type="text" class="form-control" name="rif_universal" id="rif_universal" required pattern="[VEJGCP]-[0-9]{7,8}-[0-9]{1}" title="Formato válido: Letra (V,E,J,G,C,P), guion, de 7 a 8 números, guion y dígito final." placeholder="J-12345678-9" value="<?php echo htmlspecialchars($local['rif'] ?? ''); ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" name="telefono_contacto" id="local_telefono"
                            maxlength="11" required pattern="[0-9]{11}"
                            value="<?php echo htmlspecialchars($local['telefono_contacto']); ?>">
                        <span class="text-danger small d-block" id="alerta_telefono_local" style="display:none;"></span>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" class="form-control" name="correo_contacto" id="local_correo" maxlength="60"
                            required value="<?php echo htmlspecialchars($local['correo_contacto']); ?>">
                        <span class="text-danger small d-block" id="alerta_correo_local" style="display:none;"></span>
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

            <!-- SECCIÓN: IMÁGENES INSTITUCIONALES -->
            <fieldset>
                <legend><i class="far fa-image"></i> Imágenes institucionales</legend>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Imagen actual del local</label><br>
                        <img src="<?php echo getMappedImage('loca', $imagenesMap); ?>" class="img-thumbnail-custom" style="max-width: 200px;">
                        <label class="form-label mt-2">Reemplazar imagen</label>
                        <input type="file" name="loca" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Mapa actual</label><br>
                        <img src="<?php echo getMappedImage('mapa_google', $imagenesMap); ?>"
                            class="img-thumbnail-custom" style="max-width: 200px;">
                        <label class="form-label mt-2">Reemplazar imagen</label>
                        <input type="file" name="mapa_google" class="form-control-file"
                            accept=".jpg,.jpeg,.png,.webp">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Vista satelital actual</label><br>
                        <img src="<?php echo getMappedImage('mapa_vista_satelital', $imagenesMap); ?>"
                            class="img-thumbnail-custom" style="max-width: 200px;">
                        <label class="form-label mt-2">Reemplazar imagen</label>
                        <input type="file" name="mapa_vista_satelital" class="form-control-file"
                            accept=".jpg,.jpeg,.png,.webp">
                    </div>
                </div>
            </fieldset>

            <!-- SECCIÓN: FOOTER -->
            <fieldset>
                <legend><i class="fas fa-copyright"></i> Configuración del Footer</legend>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <img src="<?php echo getMappedImage('img_footer', $imagenesMap); ?>" class="img-thumbnail-custom" style="max-width: 300px;">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label mt-2">Imagen del footer</label>
                        <input type="file" name="img_footer" class="form-control-file"
                            accept=".jpg,.jpeg,.png,.webp">
                        <hr>
                        <label class="form-label">Texto de la imagen del footer</label>
                        <input type="text" class="form-control" name="txt_titulo_footer" maxlength="255" value="<?php echo getJsonText('txt_titulo_footer', $textosJSON); ?>">
                    </div>
                </div>
            </fieldset>

            <!-- VISTA PREVIA DE FOOTER -->
            <div class="footer">
                <div class="footer-creditos">

                    <div class="row" style="width:80%;margin-top:5px;">
                        <div class="col-md-12 mb-3" style="background-color:white;border-radius:6px;">
                            <label class="form-label">Texto final del footer 1</label>
                            <input type="text" class="form-control" name="txt_ult_titulo_footer" maxlength="255" value="<?php echo getJsonText('txt_ult_titulo_footer', $textosJSON); ?>">
                            <label class="form-label">Texto final del footer 2</label>
                            <input type="text" class="form-control" name="txt_ult_subtitulo_footer" maxlength="255" value="<?php echo getJsonText('txt_ult_subtitulo_footer', $textosJSON); ?>">
                            <br>
                        </div>
                    </div>


                    <div class="footer-creditos-imagen">
                        <img src="../vistas/assets/img/logoceprocahd1.webp" alt="Logo CEPROCA">
                    </div>
                    <div class="footer-creditos-siguenos">
                        <p>Síguenos</p>
                        <div class="footer-creditos-siguenos-enlaces">
                            <a href="<?php echo htmlspecialchars($local['link_facebook'] ?? '#'); ?>" target="_blank">
                                <img src="vistas/assets/img/facebook-icon.png" alt="Facebook" width="30">
                            </a>
                            <a href="<?php echo htmlspecialchars($local['link_instagram'] ?? '#'); ?>" target="_blank">
                                <img src="vistas/assets/img/instagram-icon.png" alt="Instagram" width="30">
                            </a>
                        </div>
                    </div>
                    <div class="footer-creditos-copi">
                        <p>© Centro Educativo Profesional Cecilio Acosta <?php echo date('Y'); ?></p>
                    </div>
                </div>
            </div>

            <!-- PREVIEW DE IMÁGENES -->
            <div class="preview-container" id="preview-container">
                <div class="preview-item" id="preview-local">
                    <img src="" alt="Vista previa local">
                </div>
                <div class="preview-item" id="preview-mapa">
                    <img src="" alt="Vista previa mapa">
                </div>
                <div class="preview-item" id="preview-satelite">
                    <img src="" alt="Vista previa satélite">
                </div>
            </div>

            <br>
            <p class="text-center">
                <button type="submit" class="btn btn-actualizar btn-sm" id="btn_actualizar_local">
                    <i class="fas fa-sync-alt"></i> ACTUALIZAR DATOS
                </button>
            </p>

        </form>
    </div>

    <script>
        (function () {
            // ============================================
            // SISTEMA DE PALETAS DE COLORES
            // ============================================
            const paletas = <?php echo json_encode($paletas); ?>;
            const root = document.documentElement;

            // Función para convertir hex a rgb
            function hexToRgb(hex) {
                hex = hex.replace('#', '');
                if (hex.length === 3) {
                    hex = hex.split('').map(c => c + c).join('');
                }
                const r = parseInt(hex.substring(0, 2), 16);
                const g = parseInt(hex.substring(2, 4), 16);
                const b = parseInt(hex.substring(4, 6), 16);
                return `${r}, ${g}, ${b}`;
            }

            // Función para actualizar el tema
            function actualizarTema(paletaId) {
                const colores = paletas[paletaId];
                if (!colores) return;

                // Guardar en cookie por 30 días
                document.cookie = `paleta_activa=${paletaId}; path=/; max-age=${60 * 60 * 24 * 30}`;

                // Actualizar input oculto para enviarlo con el formulario
                const paletaActivaInput = document.getElementById('paleta_activa_input');
                if (paletaActivaInput) {
                    paletaActivaInput.value = paletaId;
                }

                // Actualizar variables CSS
                root.style.setProperty('--color-primario', colores[0]);
                root.style.setProperty('--color-secundario', colores[1]);
                root.style.setProperty('--color-terciario', colores[2]);
                root.style.setProperty('--color-cuaternario', colores[3]);
                root.style.setProperty('--color-quinario', colores[4]);
                root.style.setProperty('--shadow-color1', hexToRgb(colores[0]));
                root.style.setProperty('--shadow-color2', hexToRgb(colores[1]));
                root.style.setProperty('--shadow-color3', hexToRgb(colores[2]));
                root.style.setProperty('--shadow-color4', hexToRgb(colores[3]));
                root.style.setProperty('--shadow-color5', hexToRgb(colores[4]));

                // Marcar la paleta activa visualmente
                document.querySelectorAll('.paleta-opcion').forEach(el => {
                    el.classList.remove('active');
                    el.style.border = '2px solid transparent';
                    el.style.transform = 'scale(1)';
                });
                const activePaleta = document.querySelector(`.paleta-opcion[data-paleta="${paletaId}"]`);
                if (activePaleta) {
                    activePaleta.classList.add('active');
                    activePaleta.style.border = '2px solid #000';
                    activePaleta.style.transform = 'scale(1.02)';
                }
            }

            // Event listener para seleccionar paleta
            const selectorPaletas = document.getElementById('selector-paletas');
            if (selectorPaletas) {
                selectorPaletas.addEventListener('click', (e) => {
                    const paletaDiv = e.target.closest('.paleta-opcion');
                    if (paletaDiv) {
                        const paletaId = paletaDiv.dataset.paleta;
                        actualizarTema(paletaId);
                    }
                });
            }

            // ============================================
            // PREVIEW DINÁMICO EN LA IMAGEN ADYACENTE
            // ============================================
            document.querySelectorAll('input[type="file"]').forEach(input => {
                input.addEventListener('change', function () {
                    const file = this.files[0];
                    if (!file) return;

                    const ext = file.name.split('.').pop().toLowerCase();
                    if (!['jpg', 'jpeg', 'png', 'webp', 'ico'].includes(ext)) {
                        alert("Formato no permitido.");
                        this.value = '';
                        return;
                    }

                    // 1. Buscar primero en la misma columna (Caso: 3 imágenes juntas en col-md-4)
                    let img = null;
                    const parentCol = this.closest('[class*="col-"]');
                    if (parentCol) {
                        img = parentCol.querySelector('img.img-thumbnail-custom, img.img-thumbnail, img.width400');
                    }

                    // 2. Si no hay imagen en la misma columna, buscar en el contenedor general (Caso: icono/logo en col-md-6 separados)
                    if (!img) {
                        const parentRow = this.closest('.row, .nuevoslider');
                        if (parentRow) {
                            img = parentRow.querySelector('img.img-thumbnail-custom, img.img-thumbnail, img.width400');
                        }
                    }

                    if (img) {
                        const reader = new FileReader();
                        reader.onload = e => {
                            img.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            });

            // ============================================
            // VALIDACIÓN DE CAMPOS EN TIEMPO REAL
            // ============================================
            if (typeof Validador !== 'undefined') {
                Validador.init({ btnSubmitId: "btn_actualizar_local" });

                const inputTelefono = document.getElementById('local_telefono');
                const inputCorreo = document.getElementById('local_correo');

                if (inputTelefono) {
                    inputTelefono.addEventListener('input', function () {
                        Validador.validarCampoUnicoAsync({
                            idInput: 'local_telefono',
                            idAlerta: 'alerta_telefono_local',
                            tipo: 'telefono'
                        });

                        // Modificar el texto visible dinámicamente solo para esta vista
                        const valor = this.value.trim();
                        const alerta = document.getElementById('alerta_telefono_local');
                        if (valor === "") {
                            alerta.innerHTML = "";
                        } else if (typeof Utils !== 'undefined' && !Utils.validarTelefono(valor)) {
                            alerta.innerHTML = '<i class="fas fa-exclamation-circle"></i> Debe tener 11 dígitos y un código válido.';
                        } else {
                            alerta.innerHTML = '<i class="fas fa-check-circle"></i> Formato de teléfono válido';
                        }
                    });
                }

                if (inputCorreo) {
                    inputCorreo.addEventListener('input', function () {
                        Validador.validarCampoUnicoAsync({
                            idInput: 'local_correo',
                            idAlerta: 'alerta_correo_local',
                            tipo: 'correo'
                        });

                        // Modificar el texto visible dinámicamente solo para esta vista
                        const valor = this.value.trim();
                        const alerta = document.getElementById('alerta_correo_local');
                        if (valor === "") {
                            alerta.innerHTML = "";
                        } else if (typeof Utils !== 'undefined' && !Utils.validarCorreo(valor)) {
                            alerta.innerHTML = '<i class="fas fa-exclamation-circle"></i> Correo inválido.';
                        } else {
                            alerta.innerHTML = '<i class="fas fa-check-circle"></i> Formato de correo válido';
                        }
                    });
                }
            }
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>