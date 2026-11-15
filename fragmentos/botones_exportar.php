<div class="text-right mb-3">
    <?php if ($contexto_exportar === 'estudiantes'): ?>
        <a href="<?= SERVERURL ?>acciones/exportar_estudiantes_excel.php?id=<?= $id_seccion ?>" class="btn btn-success btn-sm">📥 Exportar a Excel</a>
        <a href="<?= SERVERURL ?>acciones/exportar_estudiantes_pdf.php?id=<?= $id_seccion ?>" class="btn btn-danger btn-sm ml-2">📄 Exportar a PDF</a>
    
    <?php elseif ($contexto_exportar === 'cohortes'): ?>
        <a href="<?= SERVERURL ?>acciones/exportar_cohortes_excel.php" class="btn btn-success btn-sm">📥 Exportar a Excel</a>
        <a href="<?= SERVERURL ?>acciones/exportar_cohortes_pdf.php" class="btn btn-danger btn-sm ml-2">📄 Exportar a PDF</a>
    <?php endif; ?>

    <?php if ($contexto_exportar === 'cursos_cohorte'): ?>
        <a href="<?= SERVERURL ?>acciones/exportar_cursos_cohorte_excel.php?id=<?= $id_cohorte ?>" class="btn btn-success btn-sm">📥 Exportar a Excel</a>
        <a href="<?= SERVERURL ?>acciones/exportar_cursos_cohorte_pdf.php?id=<?= $id_cohorte ?>" class="btn btn-danger btn-sm ml-2">📄 Exportar a PDF</a>
    <?php endif; ?>
    
    <?php if ($contexto_exportar === 'secciones_curso'): ?>
        <a href="<?= SERVERURL ?>acciones/exportar_secciones_excel.php?id=<?= $id_curso ?>" class="btn btn-success btn-sm">📥 Exportar Secciones a Excel</a>
        <a href="<?= SERVERURL ?>acciones/exportar_secciones_pdf.php?id=<?= $id_curso ?>" class="btn btn-danger btn-sm ml-2">📄 Exportar Secciones a PDF</a>
    <?php endif; ?>

    <?php if ($contexto_exportar === 'evaluaciones_seccion'): ?>
        <a href="<?= SERVERURL ?>acciones/exportar_evaluaciones_excel.php?id=<?= $id_seccion ?>" class="btn btn-success btn-sm">📥 Exportar Evaluaciones a Excel</a>
        <a href="<?= SERVERURL ?>acciones/exportar_evaluaciones_pdf.php?id=<?= $id_seccion ?>" class="btn btn-danger btn-sm ml-2">📄 Exportar Evaluaciones a PDF</a>
    <?php endif; ?>


</div>
