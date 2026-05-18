<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm'] != 4){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }


    //require_once "../../../controladores/imagenControlador.php";

    require_once "./controladores/imagenControlador.php";
    $imgCtrl = new imagenControlador();
    $images_automatico = $imgCtrl->obtener_imagenes_por_id_slider(1);
    $images_automatico2 =$imgCtrl->obtener_imagenes_por_id_slider(2);
    $images_automatico3 =$imgCtrl->obtener_imagenes_por_id_slider(3);
?>


<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-calendar-alt fa-fw"></i> &nbsp; SLIDERS
    </h3>
    <p class="text-justify">
        Visualiza todos los sliders de la página publicitaria
    </p>
</div>


<!--div class="full-box page-header">
	<h3 class="text-left">
		<i class="fas fa-clipboard-list fa-fw"></i> - Primer slider principal
	</h3>
	<p class="text-justify">
		Editar slider
	</p>
</div-->


<div class="slidiorr">
    <div class="slide-trackior">
        <?php foreach ($images_automatico as $image): ?>
            <?php if ($image): ?>
                <div class="slidio">
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Slider Image">
                </div>
            <?php else: ?>
                <p>No se encontró la imagen <?php echo htmlspecialchars($image); ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php foreach ($images_automatico as $image): ?>
            <?php if ($image): ?>
                <div class="slidio">
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="Slider Image">
                </div>
            <?php else: ?>
                <p>No se encontró la imagen <?php echo htmlspecialchars($image); ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div><!--slide-trackior-->  
</div><!--slidiorr-->



<!--div class="text-center mt-3">
    <a href="<?php echo SERVERURL; ?>index.php?views=slider1-update" class="btn btn-primary btn-sm">
        <i class="fas fa-search fa-fw"></i> &nbsp; Editar slider automático
    </a>
</div-->


<div class="text-center mt-4">
  <a href="<?php echo SERVERURL; ?>index.php?views=slider1-update" class="btn btn-primary btn-lg px-4 py-2">
    <i class="fas fa-search fa-fw"></i> &nbsp; Editar slider automático
  </a>
</div>




<div style="width:100%;height:700px;display:flex;justify-content:center;align-items:center;flex-wrap: wrap;">
	<div class="multislider-container" id="slidio6" style="height:100%;width:25%;">
        <div class="multislider-content" style="height:100%;">
  
            <div class="multislide llorar" style="height:100%;margin:auto;">        
                <img style="height:40%;" loading="lazy" src="<?php echo htmlspecialchars($images_automatico3[0]); ?>" alt="Imagen 1">
            </div>
    
            <div class="multislide llorar" >
                <img style="height:40%;" loading="lazy" src="<?php echo htmlspecialchars($images_automatico3[1]); ?>" alt="Imagen 2">
                <img style="height:40%;" loading="lazy" src="<?php echo htmlspecialchars($images_automatico3[2]); ?>" alt="Imagen 3">
            </div>
                         
            <div class="multislide llorar" style="height:100%;">
                <img style="height:40%;" loading="lazy" src="<?php echo htmlspecialchars($images_automatico3[3]); ?>" alt="Imagen 4">
            </div>
                
            <div class="multislide llorar">
                <img style="height:40%;" loading="lazy" src="<?php echo htmlspecialchars($images_automatico3[4]); ?>" alt="Imagen 5">
                <img style="height:40%;" loading="lazy" src="<?php echo htmlspecialchars($images_automatico3[5]); ?>" alt="Imagen 6">
            </div>
        </div>
    </div>


    <div class="nuevoslider " style="width:50%;background-color:inherit;">
            <div class="newslider" style="width:400px;">
                <div class="newslides" style="width:400px;">
                    
                <?php for ($i = 1; $i <= 6; $i++): ?><!--images_automatico3-->
                    <img  style="width:400px;" loading="lazy" src="<?php echo htmlspecialchars($images_automatico2[$i-1]); ?>" alt="Imagen del Slider">
                <?php endfor; ?>
                                
                </div>
                <div class="newnavigation">
                    <button class="nav-btn" id="prevBtn">⟨</button>
                    <button class="nav-btn" id="nextBtn">⟩</button>
                </div>
            </div>
        </div><!--nuevoslider-->

</div>




<div style="width: 100%;">
  <div class="mt-4 d-flex justify-content-center align-items-center" style="gap: 60px;">
    <a href="<?php echo SERVERURL; ?>index.php?views=slider3-update" class="btn btn-success btn-lg px-4 py-2">
      <i class="fas fa-search fa-fw"></i> &nbsp; Editar slider vertical
    </a>

    <a href="<?php echo SERVERURL; ?>index.php?views=slider2-update" class="btn btn-info btn-lg px-4 py-2">
      <i class="fas fa-search fa-fw"></i> &nbsp; Editar tercer slider
    </a>
  </div>
</div>


<!--div style="width: 100%;">
    <div class="mt-4" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 20px;">
        <a href="<?php echo SERVERURL; ?>index.php?views=slider3-update" class="btn btn-success btn-sm">
            <i class="fas fa-search fa-fw"></i> &nbsp; Editar slider vertical
        </a>

        <a href="<?php echo SERVERURL; ?>index.php?views=slider2-update" class="btn btn-info btn-sm">
            <i class="fas fa-search fa-fw"></i> &nbsp; Editar tercer slider
        </a>
    </div>
</div-->


<script>

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



//nuevoslider
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
	//fin de nuevoslider



//reducir el tamano del slider automatico<br>
document.addEventListener('DOMContentLoaded', function () {
    const slideTrack = document.querySelector('.slide-trackior');
    const slides = document.querySelectorAll('.slidio');

    // Ajustar el ancho dinámicamente
    slideTrack.style.width = slides.length * 400 + 'px';
});

</script>
<!--div class="full-box page-header">
	<h3 class="text-left">
		<i class="fas fa-clipboard-list fa-fw"></i> - Segundo slider automático
	</h3>
	<p class="text-justify">
		Editar slider
	</p>
</div-->

