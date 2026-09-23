

function redirigirACurso(idCurso, idBanner,otroValor) {
   window.location.href = "aiframemateria3-copia.php?id=" + idCurso + "&banner=" + idBanner + "&valor=" + otroValor;
}
//falando

//modo oscuro
const swith= document.querySelector(".switch");
swith.addEventListener("click", e => {swith.classList.toggle("active");
document.body.classList.toggle("active");})




//boton de ir arriba
var cajax=document.getElementById("ir_arriba");
cajax.addEventListener("click",function(){
	document.documentElement.scrollTop=0;
	})

window.addEventListener("scroll",function(){
	
	if(document.documentElement.scrollTop >3000){cajax.style.display="flex"}
	else{cajax.style.display="none"}
	})



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




// //telefono y correo de barra de arriba
// function showText(type) {
//     const textElement = document.getElementById(type === 'teléfono' ? 'telefonoText' : 'correoText');
//     textElement.textContent = type === 'teléfono' ? '0414-344-5154' : 'ceprocaguarico@gmail.com';
// }

// function resetText(type) {
//     const textElement = document.getElementById(type === 'teléfono' ? 'telefonoText' : 'correoText');
//     textElement.textContent = type === 'teléfono' ? 'Teléfono' : 'Correo';
// }

// function copyToClipboard(text, elementId) {
//     navigator.clipboard.writeText(text).then(() => {
//         const textElement = document.getElementById(elementId);
//         textElement.textContent = 'Copiado';
//         setTimeout(() => resetText(elementId), 3000); 
//     }).catch(err => {
//         console.error('Error al copiar el texto: ', err);
//     });
// }//aqui termina tlfno y correo de barra de arriba






//funcion de ocultar definitiva
function toggleDiv(index, ocultoId, alturaVisible) {
  var oculto = document.getElementById(ocultoId);
  var divs = oculto.getElementsByTagName("div");

  if (divs[index].classList.contains("show")) {
    divs[index].classList.remove("show");
    oculto.classList.remove("show");
    setTimeout(() => {
      oculto.style.height = "0px"; 
      oculto.style.opacity = "0";
    }, 500);
  } else {
    for (var i = 0; i < divs.length; i++) {
      divs[i].classList.remove("show");
    }
    divs[index].classList.add("show");
    oculto.classList.add("show");
    oculto.style.height = alturaVisible + "px"; 
    oculto.style.opacity = "1"; 
  }
}
//aqui termina la funcion de ocultar definitiva



//codigo de copiar correo y tlfno de contactos
// const botonTelefono = document.getElementById('botonTelefono');
// const mensajeTelefono = document.getElementById('mensajeTelefono');
// const botonEmail = document.getElementById('botonEmail');
// const mensajeEmail = document.getElementById('mensajeEmail');

 botonTelefono.addEventListener('mouseenter', () => {
     mensajeTelefono.classList.add('visiblez');
     mensajeTelefono.classList.remove('hiddenz');
 });

 botonTelefono.addEventListener('mouseleave', () => {
     if (!mensajeTelefono.classList.contains('clicked')) {
         mensajeTelefono.classList.remove('visiblez');
         mensajeTelefono.classList.add('hiddenz');
     }
 });

// botonTelefono.addEventListener('click', () => {
//     const telefono = '0414-344-5154'; 
//     navigator.clipboard.writeText(telefono).then(() => {
//         mensajeTelefono.textContent = 'Número copiado';
//         mensajeTelefono.classList.add('clicked');
//         mensajeTelefono.classList.add('visiblez');
//         mensajeTelefono.classList.remove('hiddenz');

//         setTimeout(() => {
//             mensajeTelefono.classList.remove('visiblez');
//             mensajeTelefono.classList.add('hiddenz');
//             mensajeTelefono.classList.remove('clicked');
//         }, 3000);
//     });
// });

		
 botonEmail.addEventListener('mouseenter', () => {
     mensajeEmail.classList.add('visiblez');
     mensajeEmail.classList.remove('hiddenz');
 });

 botonEmail.addEventListener('mouseleave', () => {
     if (!mensajeEmail.classList.contains('clicked')) {
         mensajeEmail.classList.remove('visiblez');
         mensajeEmail.classList.add('hiddenz');
     }
 });

// botonEmail.addEventListener('click', () => {
//     const email = 'ceprocaguarico@gmail.com'; 
//     navigator.clipboard.writeText(email).then(() => {
//         mensajeEmail.textContent = 'Correo copiado';
//         mensajeEmail.classList.add('clicked');
//         mensajeEmail.classList.add('visiblez');
//         mensajeEmail.classList.remove('hiddenz');

//         setTimeout(() => {
//             mensajeEmail.classList.remove('visiblez');
//             mensajeEmail.classList.add('hiddenz');
//             mensajeEmail.classList.remove('clicked');
//         }, 3000);
//     });
// });
//aqui termina el codigo de copiar correo y tlfno


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

//     // Agregar después del script existente
// document.addEventListener('DOMContentLoaded', () => {
//     const modalbuscar = document.querySelector('.Busqueda'); // Cambiado a querySelector para seleccionar por clase
//     const btnbuscar = document.getElementById('button_califi');
//     const btncerrar_modal = document.getElementById('closeModal');

//     btnbuscar.addEventListener('click', () => {
//         modalbuscar.classList.add('show');
//     });
//     btncerrar_modal.addEventListener('click', () => {
//         modalbuscar.classList.remove('show');
//         document.getElementById('searchInput').value = ''; // Limpia el campo de búsqueda
//         document.getElementById('searchResultsBody').innerHTML = ''; // Limpia los resultados de la búsqueda
//     });
// });

// // Modificar el evento click del botón de búsqueda
// document.getElementById('searchBtn').addEventListener('click', function() {
//     const cedula = document.getElementById('searchInput').value.trim();
//     if(!cedula) {
//         alert('Por favor ingrese una cédula');
//         return;
//     }
    
//     // Mostrar mensaje de carga
//     const tbody = document.getElementById('searchResultsBody');
//     tbody.innerHTML = '<tr><td colspan="7">Buscando...</td></tr>';
    
//     fetch('buscar_calificaciones.php', {
//         method: 'POST',
//         headers: {
//             'Content-Type': 'application/x-www-form-urlencoded',
//         },
//         body: 'cedula=' + encodeURIComponent(cedula)
//     })
//     .then(response => {
//         if (!response.ok) {
//             throw new Error('Error en la respuesta del servidor');
//         }
//         return response.json();
//     })
//     .then(data => {
//         tbody.innerHTML = '';
        
//         if(!data || data.length === 0) {
//             tbody.innerHTML = '<tr><td colspan="7">No se encontraron calificaciones para esta cédula</td></tr>';
//             return;
//         }
        
//         data.forEach(row => {
//             if(row.error) {
//                 // Si hay error por pago pendiente
//                 tbody.innerHTML += `
//                     <tr>
//                         <td>${row.ci || ''}</td>
//                         <td>${row.nombre || ''}</td>
//                         <td>${row.apellido || ''}</td>
//                         <td>${row.curso || ''}</td>
//                         <td colspan="3" style="color: red;">${row.error} - Monto pendiente: ${row.pago_pendiente}</td>
//                     </tr>
//                 `;
//             } else {
//                 // Si no hay error, mostrar calificaciones normalmente
//                 tbody.innerHTML += `
//                     <tr>
//                         <td>${row.ci || ''}</td>
//                         <td>${row.nombre || ''}</td>
//                         <td>${row.apellido || ''}</td>
//                         <td>${row.curso || ''}</td>
//                         <td>${row.objetivos || ''}</td>
//                         <td>${row.nota_final || ''}</td>
//                         <td>${row.estado || ''}</td>
//                     </tr>
//                 `;
//             }
//         });
//     })
//     .catch(error => {
//         console.error('Error:', error);
//         tbody.innerHTML = '<tr><td colspan="7">Error al buscar las calificaciones: ' + error.message + '</td></tr>';
//     });
// });

// // Asegúrate de que los botones de exportación estén correctamente configurados
// document.getElementById('exportExcel').addEventListener('click', function() {
//     const data = getTableData();
//     if (data.length === 0) {
//         alert('No hay datos para exportar');
//         return;
//     }
//     exportToExcel(data);
// });

// document.getElementById('exportPDF').addEventListener('click', function() {
//     const data = getTableData();
//     if (data.length === 0) {
//         alert('No hay datos para exportar');
//         return;
//     }

//     const { jsPDF } = window.jspdf; // Asegúrate de que jsPDF esté disponible
//     const doc = new jsPDF();

//     doc.setFontSize(18);
//     doc.text('Reporte de Calificaciones CEPROCA', 14, 22);
//     doc.setFontSize(11);
//     doc.text(`Fecha: ${new Date().toLocaleDateString()}`, 14, 30);

//     const headers = [['CI', 'NOMBRE', 'APELLIDO', 'CURSO', 'OBJETIVOS', 'NOTA FINAL', 'ESTADO']];
//     const body = data.slice(1).map(row => row);

//     doc.autoTable({
//         head: headers,
//         body: body,
//         startY: 35,
//         theme: 'grid',
//         styles: { fontSize: 8 },
//         headStyles: { fillColor: [41, 128, 185] }
//     });

//     doc.save('calificaciones_ceproca.pdf');
// });

// // Función para obtener los datos de la tabla
// function getTableData() {
//     const table = document.getElementById('searchResults');
//     const rows = table.getElementsByTagName('tr');
//     const data = [];
    
//     // Obtener encabezados
//     const headers = [];
//     const headerCells = rows[0].getElementsByTagName('th');
//     for (let i = 0; i < headerCells.length; i++) {
//         headers.push(headerCells[i].textContent);
//     }
//     data.push(headers);
    
//     // Obtener datos
//     for (let i = 1; i < rows.length; i++) {
//         const row = [];
//         const cells = rows[i].getElementsByTagName('td');
//         for (let j = 0; j < cells.length; j++) {
//             row.push(cells[j].textContent);
//         }
//         data.push(row);
//     }
    
//     return data;
// }

// // Función para exportar a Excel
// function exportToExcel(data) {
//     const ws = XLSX.utils.aoa_to_sheet(data);
//     const wb = XLSX.utils.book_new();
//     XLSX.utils.book_append_sheet(wb, ws, "Calificaciones");
//     XLSX.writeFile(wb, "calificaciones_ceproca.xlsx");
// }

// // Función para exportar a PDF
// function exportToPDF(data) {
//     const doc = new jsPDF();
    
//     // Configurar el título
//     doc.setFontSize(18);
//     doc.text("Reporte de Calificaciones CEPROCA", 14, 20);
    
//     // Configurar la tabla
//     doc.autoTable({
//         head: [data[0]], // Encabezados
//         body: data.slice(1), // Datos
//         startY: 30,
//         theme: 'grid',
//         styles: {
//             fontSize: 8,
//             cellPadding: 2
//         },
//         headStyles: {
//             fillColor: [40, 40, 40]
//         }
//     });
    
//     // Guardar el PDF
//     doc.save("calificaciones_ceproca.pdf");
// }
	//fin de nuevoslider


//felindo

