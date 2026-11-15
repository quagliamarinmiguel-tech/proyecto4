<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

$id_inscripcion = (int)($_POST['id_inscripcion'] ?? 0);

if (!$id_inscripcion) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

// 1. Obtener el tipo de periodo
$selectReg = 'period_course(period(id,type))';
$resultReg = mainModel::supabaseQuery('registration', $selectReg, ['id' => 'eq.' . $id_inscripcion]);

$periodData = $resultReg['data'][0]['period_course']['period'] ?? [];
$period_id = $periodData['id'] ?? 0;
$tipoPeriodo = strtolower(trim($periodData['type'] ?? 'extendido'));

// 2. Obtener costos base del periodo
$resultCosts = mainModel::supabaseQuery('period_costs', 'cost_description,price', ['period_id' => 'eq.' . $period_id]);
$costs = $resultCosts['data'] ?? [];

$precios = ['modulo'=>0, 'arancel'=>0, 'art13'=>0, 'inscripcion'=>0, 'encuentro'=>0, 'titulo'=>0];
foreach ($costs as $c) {
    $desc = strtolower(trim($c['cost_description']));
    if (strpos($desc, 'modulo') !== false) $precios['modulo'] = $c['price'];
    elseif (strpos($desc, 'arancel') !== false) $precios['arancel'] = $c['price'];
    elseif (strpos($desc, 'art') !== false || strpos($desc, '13') !== false) $precios['art13'] = $c['price'];
    elseif (strpos($desc, 'inscripcion') !== false) $precios['inscripcion'] = $c['price'];
    elseif (strpos($desc, 'encuentro') !== false) $precios['encuentro'] = $c['price'];
    elseif (strpos($desc, 'titulo') !== false || strpos($desc, 'titulacion') !== false) $precios['titulo'] = $c['price'];
}

// 3. OBTENER PAGOS PREVIOS
$resultDetalles = mainModel::supabaseQuery('transaction_detail', 'motive_payment, amount', ['registration_id' => 'eq.' . $id_inscripcion]);
$detalles = $resultDetalles['data'] ?? [];

$pagadoPorMotivo = [];
foreach ($detalles as $det) {
    $motivo = strtolower(trim($det['motive_payment'] ?? ''));
    $monto = ($det['amount'] ?? 0) / 100;
    if ($motivo) {
        if (!isset($pagadoPorMotivo[$motivo])) $pagadoPorMotivo[$motivo] = 0;
        $pagadoPorMotivo[$motivo] += $monto;
    }
}

// 4. Funciones para verificar si ya está pagado
function yaPagado($motivoBuscar, $precioTarifa, $pagadoPorMotivo) {
    $pagado = 0;
    foreach ($pagadoPorMotivo as $motivo => $monto) {
        if (strpos($motivo, $motivoBuscar) !== false) {
            $pagado += $monto;
        }
    }
    return ($pagado >= ($precioTarifa - 0.01));
}

// 5. Construir Tarjetas (SIN LA TARJETA DE INSCRIPCIÓN)
$htmlTarjetas = '<div class="row mb-2" id="tarjetasPrecios">';

if ($tipoPeriodo === 'intensivo') {
    $pEnc = number_format($precios['encuentro'], 2);
    $pTit = number_format($precios['titulo'], 2);

    // Tarjeta Encuentros
    $htmlTarjetas .= "
      <div class='col-md-6 mb-2'>
        <div class='card border-0 shadow-sm h-100 border-left-success' style='border-left: 4px solid #28a745;'>
          <div class='card-body text-center p-2'>
            <i class='fas fa-users fa-2x text-success mb-1'></i>
            <h6 class='text-muted mb-1'>ENCUENTROS (4)</h6>
            <div class='h5 mb-0 text-success' data-usd='{$precios['encuentro']}'>\$$pEnc</div>
            <small class='text-muted d-block'>Por cada encuentro</small>
            <small class='text-success font-weight-bold precio-ves'></small>
          </div>
        </div>
      </div>";

    // Tarjeta Titulación
    $htmlTarjetas .= "
      <div class='col-md-6 mb-2'>
        <div class='card border-0 shadow-sm h-100'>
          <div class='card-body text-center p-2'>
            <i class='fas fa-graduation-cap fa-2x text-warning mb-1'></i>
            <h6 class='text-muted mb-1'>TITULACIÓN</h6>
            <div class='h5 mb-0 text-warning' data-usd='{$precios['titulo']}'>\$$pTit</div>
            <small class='text-success font-weight-bold precio-ves'></small>
          </div>
        </div>
      </div>";

} else {
    $pMod = number_format($precios['modulo'], 2);
    $pAra = number_format($precios['arancel'], 2);
    $pArt = number_format($precios['art13'], 2);

    // Tarjeta Módulos
    $htmlTarjetas .= "
      <div class='col-md-4 mb-2'>
        <div class='card border-0 shadow-sm h-100 border-left-primary' style='border-left: 4px solid #007bff;'>
          <div class='card-body text-center p-2'>
            <i class='fas fa-cubes fa-2x text-primary mb-1'></i>
            <h6 class='text-muted mb-1'>MÓDULOS (3)</h6>
            <div class='h5 mb-0 text-primary' data-usd='{$precios['modulo']}'>\$$pMod</div>
            <small class='text-muted d-block'>Por cada módulo</small>
            <small class='text-success font-weight-bold precio-ves'></small>
          </div>
        </div>
      </div>";

    // Tarjeta Aranceles
    $htmlTarjetas .= "
      <div class='col-md-4 mb-2'>
        <div class='card border-0 shadow-sm h-100'>
          <div class='card-body text-center p-2'>
            <i class='fas fa-file-invoice-dollar fa-2x text-success mb-1'></i>
            <h6 class='text-muted mb-1'>ARANCELES</h6>
            <div class='h5 mb-0 text-success' data-usd='{$precios['arancel']}'>\$$pAra</div>
            <small class='text-success font-weight-bold precio-ves'></small>
          </div>
        </div>
      </div>";

    // Tarjeta Artículo 13
    $htmlTarjetas .= "
      <div class='col-md-4 mb-2'>
        <div class='card border-0 shadow-sm h-100'>
          <div class='card-body text-center p-2'>
            <i class='fas fa-gavel fa-2x text-warning mb-1'></i>
            <h6 class='text-muted mb-1'>ART. 13</h6>
            <div class='h5 mb-0 text-warning' data-usd='{$precios['art13']}'>\$$pArt</div>
            <small class='text-success font-weight-bold precio-ves'></small>
          </div>
        </div>
      </div>";
}
$htmlTarjetas .= '</div>';

// 6. Construir Opciones del Select (SIN LA OPCIÓN DE INSCRIPCIÓN)
$optionsSelect = '<option value="">Seleccione</option>';

if ($tipoPeriodo === 'intensivo') {
    for ($i = 1; $i <= 4; $i++) {
        $pagado = yaPagado("encuentro $i", $precios['encuentro'], $pagadoPorMotivo);
        $disabled = $pagado ? 'disabled' : '';
        $texto = $pagado ? "Encuentro $i (Pagado)" : "Encuentro $i";
        $optionsSelect .= "<option value='Encuentro $i' data-price='{$precios['encuentro']}' $disabled>$texto</option>";
    }
    
    $pagadoTit = yaPagado("titulacion", $precios['titulo'], $pagadoPorMotivo) || yaPagado("titulo", $precios['titulo'], $pagadoPorMotivo);
    $disabledTit = $pagadoTit ? 'disabled' : '';
    $textoTit = $pagadoTit ? "Titulación (Pagado)" : "Titulación";
    $optionsSelect .= "<option value='Titulación' data-price='{$precios['titulo']}' $disabledTit>$textoTit</option>";
} else {
    for ($i = 1; $i <= 3; $i++) {
        $pagado = yaPagado("modulo $i", $precios['modulo'], $pagadoPorMotivo);
        $disabled = $pagado ? 'disabled' : '';
        $texto = $pagado ? "Módulo $i (Pagado)" : "Módulo $i";
        $optionsSelect .= "<option value='Módulo $i' data-price='{$precios['modulo']}' $disabled>$texto</option>";
    }
    
    $pagadoAra = yaPagado("arancel", $precios['arancel'], $pagadoPorMotivo);
    $disabledAra = $pagadoAra ? 'disabled' : '';
    $textoAra = $pagadoAra ? "Aranceles (Pagado)" : "Aranceles";
    $optionsSelect .= "<option value='Aranceles' data-price='{$precios['arancel']}' $disabledAra>$textoAra</option>";
    
    $pagadoArt = yaPagado("art13", $precios['art13'], $pagadoPorMotivo) || yaPagado("art 13", $precios['art13'], $pagadoPorMotivo);
    $disabledArt = $pagadoArt ? 'disabled' : '';
    $textoArt = $pagadoArt ? "Artículo 13 (Pagado)" : "Artículo 13";
    $optionsSelect .= "<option value='art13' data-price='{$precios['art13']}' $disabledArt>$textoArt</option>";
}

echo json_encode(['tarjetas' => $htmlTarjetas, 'options' => $optionsSelect]);