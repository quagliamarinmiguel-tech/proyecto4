function iniciarBuscadorFK({ inputTextoID, listaResultadosID, inputHiddenID, validacionID, endpointURL }) {
    const input = document.getElementById(inputTextoID);
    const lista = document.getElementById(listaResultadosID);
    const hidden = document.getElementById(inputHiddenID);
    const validacion = document.getElementById(validacionID);

    input.addEventListener("input", () => {
        const valor = input.value.trim();
        if (valor.length < 2) {
            lista.innerHTML = "";
            lista.style.display = "none";
            hidden.value = "";
            validacion.innerText = "";
            validacion.classList.remove("text-success");
            return;
        }

        fetch(endpointURL, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "busqueda=" + encodeURIComponent(valor) + "&dato=busca"
        })
        .then(res => res.text())
        .then(data => {
            lista.innerHTML = data;
            lista.style.display = "block";
        });
    });

    lista.addEventListener("click", e => {
        if (e.target.classList.contains("item-fk")) {
            const nombre = e.target.getAttribute("data-nombre");
            const id = e.target.getAttribute("data-id");

            input.value = nombre;
            hidden.value = id;
            lista.innerHTML = "";
            lista.style.display = "none";

            validacion.innerText = "Curso seleccionado correctamente.";
            validacion.classList.add("text-success");
        }
    });
}