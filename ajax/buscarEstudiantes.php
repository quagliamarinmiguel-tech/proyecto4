<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../modelos/mainModel.php';




/* --- Parámetros de entrada --- */
$busqueda  = trim($_POST['busqueda'] ?? '');
$filtro    = $_POST['filtro'] ?? 'todos';
$pagina    = max(1, (int)($_POST['pagina'] ?? 1));
$porPagina = 10;

/* --- Campos cifrados en la tabla 'student' (AJUSTA SEGÚN TU CASO) --- */
$camposEncriptados = [
    'ci', 'name', 'last_name', 'birthday',
    'phone', 'email', 'address', 'birthplace', 'whatsapp_phone'

];

/* --- Consultar Supabase --- */
try {
    $respuesta   = mainModel::supabaseGet('student', '*', ['order' => 'id.desc']);
    $estudiantes = $respuesta['data'] ?? [];
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error al consultar estudiantes: '
        . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}

/* --- Desencriptar (con fallback si el valor NO estaba cifrado) --- */
foreach ($estudiantes as &$est) {
    foreach ($camposEncriptados as $campo) {
        if (!empty($est[$campo])) {
            $dec = mainModel::decryption($est[$campo]);
            if ($dec !== false && $dec !== null && $dec !== '') {
                $est[$campo] = $dec;
            }
        }
    }
}
unset($est);

/* --- Filtro --- */
if ($busqueda !== '') {
    $q = mb_strtolower($busqueda);
    $estudiantes = array_values(array_filter($estudiantes, function ($est) use ($q, $filtro) {
        if ($filtro === 'estado') {
            $estado = ((int)$est['state'] === 1) ? 'activo' : 'inactivo';
            return mb_strpos($estado, $q) !== false;
        }
        $campos = $filtro === 'todos'
            ? ['ci', 'name', 'last_name', 'email']
            : [$filtro];
        foreach ($campos as $c) {
            if (!empty($est[$c]) && mb_strpos(mb_strtolower((string)$est[$c]), $q) !== false) {
                return true;
            }
        }
        return false;
    }));
}

/* --- Paginación --- */
$total        = count($estudiantes);
$totalPaginas = max(1, (int)ceil($total / $porPagina));
$pagina       = min($pagina, $totalPaginas);
$offset       = ($pagina - 1) * $porPagina;
$paginaDatos  = array_slice($estudiantes, $offset, $porPagina);

/* --- Render tabla --- */
if (empty($paginaDatos)) {
    echo '<div class="alert alert-info text-center">No se encontraron estudiantes.</div>';
} else {
    echo '<div class="table-responsive">';
    echo '<table class="table table-hover mb-0">';
    echo '<thead class="thead-light"><tr>'
        . '<th>CI</th>'
        . '<th>Nombre completo</th>'
        . '<th>F. Nac.</th>'
        . '<th>Teléfono</th>'
        . '<th>Email</th>'
        . '<th>Educación</th>'
        . '<th>Trabaja</th>'
        . '<th>Estado</th>'
        . '<th class="text-center">Datos completos</th>'
        . '</tr></thead><tbody>';

    foreach ($paginaDatos as $est) {
        $estadoBadge = ((int)$est['state'] === 1)
            ? '<span class="badge badge-success">Activo</span>'
            : '<span class="badge badge-secondary">Inactivo</span>';

        $trabajaBadge = !empty($est['work'])
            ? '<span class="badge badge-info">Sí</span>'
            : '<span class="badge badge-light">No</span>';

        $nombreCompleto = trim(($est['name'] ?? '') . ' ' . ($est['last_name'] ?? ''));
        $jsonEst = htmlspecialchars(json_encode($est, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');

        echo '<tr>';
        echo '<td>' . htmlspecialchars($est['ci'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($nombreCompleto) . '</td>';
        echo '<td>' . htmlspecialchars($est['birthday'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($est['phone'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($est['email'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($est['education_level'] ?? '') . '</td>';
        echo '<td>' . $trabajaBadge . '</td>';
        echo '<td>' . $estadoBadge . '</td>';
        echo '<td class="text-center">'
           . '<button class="btn btn-sm btn-info ver-estudiante" '
           . 'data-estudiante=\'' . $jsonEst . '\' title="Ver detalles">'
           . '<i class="fas fa-eye"></i></button>'
           . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table></div>';

    if ($totalPaginas > 1) {
        echo '<nav aria-label="Paginación estudiantes"><ul class="pagination justify-content-center mt-3">';
        $prev = $pagina <= 1 ? 'disabled' : '';
        echo '<li class="page-item ' . $prev . '"><a class="page-link pagina-link" href="#" data-pagina="' . ($pagina - 1) . '">&laquo;</a></li>';
        for ($i = 1; $i <= $totalPaginas; $i++) {
            $active = $i === $pagina ? 'active' : '';
            echo '<li class="page-item ' . $active . '"><a class="page-link pagina-link" href="#" data-pagina="' . $i . '">' . $i . '</a></li>';
        }
        $next = $pagina >= $totalPaginas ? 'disabled' : '';
        echo '<li class="page-item ' . $next . '"><a class="page-link pagina-link" href="#" data-pagina="' . ($pagina + 1) . '">&raquo;</a></li>';
        echo '</ul></nav>';
    }

    echo '<p class="text-muted text-center small mt-2">Mostrando '
       . ($offset + 1) . '–' . min($offset + $porPagina, $total)
       . ' de ' . $total . ' estudiantes.</p>';
}