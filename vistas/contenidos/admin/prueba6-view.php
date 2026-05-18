<!-- Incluye SheetJS desde CDN -->
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<button onclick="generarExcel()">Exportar Excel</button>
    
    
    <a href="exportadores/exportar-excel-phpspreadsheet1.php?curso_vigente=216&seccion=A" target="_blank">
    	<button class="btn btn-success">Descargar Excel (.xlsx)</button>
    </a>

    <a href="exportadores/exportar-excel-phpspreadsheet1.php?curso_vigente=207&seccion=A" target="_blank">
    	<button class="btn btn-success">Descargar Excel 2(.xlsx)</button>
    </a>    
    
        <a href="exportadores/practica1php.php" target="_blank">
    	<button class="btn btn-success">practica1php</button>
    </a>

<script>
async function generarExcel() {
  // 1. Obtener los datos desde PHP (AJAX o fetch)
  const res = await fetch('<?php echo SERVERURL; ?>exportadores/api_seccion_json.php?curso_vigente=113&seccion=A');
  const datos = await res.json(); // Asegúrate de que PHP devuelva JSON

  // 2. Construir la hoja de cálculo
  const hoja = [];

  // Encabezado institucional
  hoja.push([
    `CENTRO EDUCATIVO PROFESIONAL "CECILIO ACOSTA"`,
    '',
    '',
    ...datos.evaluaciones.map((_, i) => [`Estrategia ${i + 1}`, '']).flat(),
    'Final', '', ''
  ]);

  hoja.push([
    `PROFESOR: ${datos.seccion.nombre_profesor} ${datos.seccion.apellido_profesor}`,
    '',
    '',
    ...datos.evaluaciones.map(ev => [ev.tipo_evaluacion, '']).flat(),
    '', '', ''
  ]);

  hoja.push([
    `CURSO: ${datos.seccion.nombre_curso}`,
    '',
    '',
    ...datos.evaluaciones.map(ev => [`${ev.porcentaje}%`, '']).flat(),
    '', '', ''
  ]);

  hoja.push([
    `COHORTE: ${datos.seccion.tipo_cohorte} ${datos.seccion.numero_cohorte}/${datos.seccion.anio_cohorte} | SECCIÓN: ${datos.seccion.nombre_seccion}`,
    '',
    '',
    ...datos.evaluaciones.map(ev => [`OBJ ${ev.obj}`, '']).flat(),
    '', '', ''
  ]);

  // Encabezados de columnas
  const encabezado = ['Nº', 'C.I', 'Apellidos y Nombres'];
  datos.evaluaciones.forEach(() => encabezado.push('T', '%'));
  encabezado.push('Acumulado', 'Calificación', 'Redondeada');
  hoja.push(encabezado);

  // Filas de estudiantes
  datos.estudiantes.forEach((est, i) => {
    const fila = [
      i + 1,
      est.ci,
      `${est.apellido} ${est.nombre}`
    ];

    est.evaluaciones.forEach(ev => {
      fila.push(ev.puntaje, `${ev.porcentaje_obtenido}%`);
    });

    fila.push(`${est.suma_porcentajes}%`, est.nota_final, est.nota_redondeada);
    hoja.push(fila);
  });

  // Resumen
  hoja.push(['Resumen', 'N°', '%']);
  hoja.push(['Cursantes', datos.estudiantes.length, '100']);
  const aprobados = datos.estudiantes.filter(e => e.nota_redondeada >= 50).length;
  const reprobados = datos.estudiantes.length - aprobados;
  hoja.push(['Aprobados', aprobados, ((aprobados / datos.estudiantes.length) * 100).toFixed(2)]);
  hoja.push(['Reprobados', reprobados, ((reprobados / datos.estudiantes.length) * 100).toFixed(2)]);

  // 3. Crear workbook y exportar
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(hoja);
  XLSX.utils.book_append_sheet(wb, ws, "Notas");

  XLSX.writeFile(wb, `Notas_Seccion_${datos.seccion.nombre_seccion}_${datos.seccion.anio_cohorte}.xlsx`);
}
</script>
