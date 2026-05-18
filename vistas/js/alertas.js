const formularios_ajax = document.querySelectorAll(".FormularioAjax");

function enviar_formulario_ajax(e){
	e.preventDefault();

	let data = new FormData(this);
	let method = this.getAttribute("method");
	let action = this.getAttribute("action");
	let tipo = this.getAttribute("data-form");

	let encabezados = new Headers();

	let config = {
		method: method,
		headers: encabezados,
		mode: 'cors',
		cache: 'no-cache',
		body: data
	}

	let texto_alerta;

	if(tipo==="save"){
		texto_alerta="Los datos quedaran guardados en el sistema";



	}  else if(tipo==="delete"){
			
			const entidad = e.target.getAttribute("data-entity") || "registro";

			let texto_alerta = "¿Estás seguro? Se archivará este " + entidad;

			if(entidad === "curso"){
				texto_alerta = "¿Estás seguro? Se archivará este curso, su publicidad, sus registros de vigencia en cohortes, sus secciones, evaluaciones y calificaciones de estudiantes.";
			} else if(entidad === "cohorte"){
				texto_alerta = "¿Estás seguro? Se archivará esta cohorte, junto con sus cursos vigentes, secciones, evaluaciones y calificaciones.";
			} else if(entidad === "curso_vigente"){
				texto_alerta = "¿Estás seguro? Se archivará este curso vigente, sus secciones, evaluaciones y calificaciones.";
			} else if(entidad === "seccion"){
				texto_alerta = "¿Estás seguro? Se archivará esta sección, sus evaluaciones y calificaciones.";
			} else if(entidad === "evaluacion"){
				texto_alerta = "¿Estás seguro? Se archivará esta evaluación y sus calificaciones de estudiantes.";
			} else if(entidad === "profesor"){
				texto_alerta = "¿Estás seguro? Se archivará este profesor y las secciones, evaluaciones y calificaciones que impartió";
			} 

			Swal.fire({
				title: 'Confirmar archivado',
				text: texto_alerta,
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Archivar',
				cancelButtonText: 'Cancelar'
			}).then((result) => {
				if(result.value){
					fetch(e.target.getAttribute("action"), {
						method: e.target.getAttribute("method"),
						body: new FormData(e.target)
					})
					.then(res => res.json())
					.then(res => alertas_ajax(res));
					//return; // ⛔️ Esto evita que se ejecute el Swal genérico

				}
			});
			return;
	}

    
    else if(tipo === "delete2") {

    const entidad = this.getAttribute("data-entity") || "registro";

    let titulo_alerta = "Confirmar eliminación";
    let texto_alerta = "¿Estás seguro? Se eliminará este " + entidad + " permanentemente. Esta acción no se puede deshacer.";

    // Mensajes personalizados por entidad
    if (entidad === "solicitud_inscripcion") {
        texto_alerta = "¿Estás seguro? Se eliminará esta solicitud y sus datos de forma permanente.";
    } else if (entidad === "evaluacion") {
        texto_alerta = "¿Estás seguro? Se eliminará esta evaluación de forma permanente junto con sus calificaciones.";
    }

    Swal.fire({
        title: titulo_alerta,
        text: texto_alerta,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.value){
            fetch(this.getAttribute("action"), {
                method: this.getAttribute("method"),
                body: new FormData(this)
            })
            .then(res => res.json())
            .then(res => alertas_ajax(res));
        }
    });

    return;
}
    
    
    
    
    
    
	
	
	else if(tipo==="update"){
		texto_alerta="Los datos del sistema serán actualizados";
	}else if(tipo==="search"){
		texto_alerta="Se eliminará el término de búsqueda y tendrás que escribir uno nuevo";
	}else if(tipo==="loans"){
		texto_alerta="Desea remover los datos seleccionados para préstamos o reservaciones";
	}else{
		texto_alerta="Quieres realizar la operación solicitada";
	}

	Swal.fire({
		title: '¿Estás seguro?',
		text: texto_alerta,
		type: 'question',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Aceptar',
		cancelButtonText: 'Cancelar'
	}).then((result) => {
		if(result.value){
			fetch(action,config)
			.then(respuesta => respuesta.json())
			.then(respuesta => {
				return alertas_ajax(respuesta);
			});
		}
	});

}

formularios_ajax.forEach(formularios => {
	formularios.addEventListener("submit", enviar_formulario_ajax);
});

function alertas_ajax(alerta){
	if(alerta.Alerta==="simple"){
		Swal.fire({
			title: alerta.Titulo,
			text: alerta.Texto,
			type: alerta.Tipo,
			confirmButtonText: 'Aceptar'
		});
	}else if(alerta.Alerta==="recargar"){
		Swal.fire({
			title: alerta.Titulo,
			text: alerta.Texto,
			type: alerta.Tipo,
			confirmButtonText: 'Aceptar'
		}).then((result) => {
			if(result.value){
				location.reload();
			}
		});
	}else if(alerta.Alerta==="limpiar"){
		Swal.fire({
			title: alerta.Titulo,
			text: alerta.Texto,
			type: alerta.Tipo,
			confirmButtonText: 'Aceptar'
		}).then((result) => {
			if(result.value){
				document.querySelector(".FormularioAjax").reset();
			}
		});
	}
	
//	else if(alerta.Alerta==="redireccionar"){
//		window.location.href=alerta.URL;
//	}


	else if(alerta.Alerta==="redireccionar"){
		Swal.fire({
			title: alerta.Titulo,
			text: alerta.Texto,
			type: alerta.Tipo,
			confirmButtonText: 'Ir ahora'
		}).then((result) => {
			if(result.value){
				window.location.href = alerta.URL;
			}
		});
	}


}