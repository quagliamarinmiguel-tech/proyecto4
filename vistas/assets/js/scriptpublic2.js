
//function redirigirACurso(idCurso, idBanner) {window.location.href = "aiframemateria3-copia.php?id=" + idCurso + "&banner=" + idBanner;}

function redirigirACurso() {
    var idMateria = document.getElementById("id_materia_seleccionado").value;

    if (idMateria) {
        window.location.href = "aiframemateria3-copia.php?id=" + idMateria + "&banner=" + idMateria;
    } else {
        alert("Por favor, selecciona un curso antes de enviar.");
    }
}

function seleccionarCurso(nombreCurso, idMateria) {
    document.getElementById("buscar").value = nombreCurso;
    document.getElementById("id_materia_seleccionado").value = idMateria;
}


//barra buscador
function consulta_buscador(busqueda){
    var dato="busca";
    var parametros= {"busqueda":busqueda,"dato":dato};
    $.ajax({
        data:parametros,
        url:'codigo_busqueda.php',
        type:'POST',
        beforeSend: function(){console.log('tamos en eso');},
        success: function(data){
            console.log('tamos ok');
            if(busqueda==""){
                document.getElementById("card_busqueda").style.opacity=0;
            }
            else{
                document.getElementById("card_busqueda").style.transition="all 1s";
                document.getElementById("card_busqueda").style.opacity=1;
            }
            document.getElementById("resultados_busqueda_nav").innerHTML=data;

        },
        error: function(data,error){console.log('tamos no ok');}
    });
}

//modo oscuro
const swith= document.querySelector(".switch");
swith.addEventListener("click", e => {swith.classList.toggle("active");
document.body.classList.toggle("active");})

//funcion de ocultar definitiva
function toggleDiv(index, ocultoId, alturaVisible) {
    console.log(index, ocultoId, alturaVisible);
  var oculto = document.getElementById(ocultoId);
  var divs = oculto.getElementsByTagName("div");
  var isAlreadyOpen = divs[0].classList.contains("show");

  closeAllOcultos(); // Cierra todos los divs ocultos visibles

  if (!isAlreadyOpen) {
    divs[0].classList.add("show");
    oculto.classList.add("show");
    oculto.style.height = alturaVisible + "vh";
    oculto.style.opacity = "1";
  }
}

// Cierra todos los divs ocultos visibles
function closeAllOcultos() {
  var ocultos = document.getElementsByClassName("oculto");
  for (var i = 0; i < ocultos.length; i++) {
    if (ocultos[i].classList.contains("show")) {
      ocultos[i].classList.remove("show");
      var divs = ocultos[i].getElementsByTagName("div");
      for (var j = 0; j < divs.length; j++) {
        divs[j].classList.remove("show");
      }
      ocultos[i].style.height = "0px";
      ocultos[i].style.opacity = "0";
    }
  }
}



//multislide
function navigateSlider(sliderId, direction) {
  const slider = document.getElementById(sliderId);
  const sliderContent = slider.querySelector('.multislider-content');
  const slides = slider.querySelectorAll('.multislide');
  const totalSlides = slides.length;
  let currentIndex = parseInt(slider.getAttribute('data-current-index') || 0);

  // Actualizar el índice actual
  currentIndex += direction;

  // Asegurarse de que el índice esté dentro de los límites
  if (currentIndex < 0) {
    currentIndex = totalSlides - 1;
  } else if (currentIndex >= totalSlides) {
    currentIndex = 0;
  }

  // Guardar el nuevo índice en el atributo de datos
  slider.setAttribute('data-current-index', currentIndex);

  // Calcular y aplicar el desplazamiento
  const offset = -currentIndex * 100;
  sliderContent.style.transform = `translateX(${offset}%)`;
}

// Iniciar el deslizamiento automático para todos los sliders
const sliderContainers = document.querySelectorAll('.multislider-container');
sliderContainers.forEach((slider) => {
  setInterval(() => {
    navigateSlider(slider.id, 1);
  }, 5000); // Cambia cada 5 segundos
});



document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const cursoSeleccionado = urlParams.get("cursoSeleccionado");

    if (cursoSeleccionado) {
        // Encuentra el div oculto correspondiente y activa toggleDiv
        const divOcultoId = "oculto" + cursoSeleccionado;
        const alturaVisible = 160; // Altura ajustable
        toggleDiv(0, divOcultoId, alturaVisible); // El índice ahora siempre es 0
    }
});

