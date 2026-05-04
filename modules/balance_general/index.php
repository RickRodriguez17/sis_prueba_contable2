<?php
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Balance general';
$hasta = $_GET['hasta'] ?? '';

/**
 * Devuelve un array de cuentas con su saldo agrupadas por tipo.
 * Para activos/gastos, saldo = debe - haber (deudor).
 * Para pasivos/patrimonio/ingresos, saldo = haber - debe (acreedor).
 */
function obtenerSaldosPorTipo($conn, $hasta) {
    $where = '';
    $params = []; $types = '';
    if ($hasta !== '') { $where = 'WHERE c.fecha <= ?'; $params = [$hasta]; $types = 's'; }
    $sql = "
        SELECT ct.codigo, ct.nombre, ct.tipo,
               COALESCE(SUM(d.debe),0)  AS total_debe,
               COALESCE(SUM(d.haber),0) AS total_haber
        FROM cuentas ct
        LEFT JOIN comprobante_detalles d ON d.cuenta_id = ct.id
        LEFT JOIN comprobantes c ON c.id = d.comprobante_id
        $where
        GROUP BY ct.id, ct.codigo, ct.nombre, ct.tipo
        ORDER BY ct.codigo
    ";
    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $r = $stmt->get_result();
    $grupos = ['activo'=>[], 'pasivo'=>[], 'patrimonio'=>[], 'ingreso'=>[], 'gasto'=>[]];
    while ($row = $r->fetch_assoc()) {
        $debe = (float)$row['total_debe'];
        $haber = (float)$row['total_haber'];
        $deudor = in_array($row['tipo'], ['activo','gasto'], true);
        $saldo = $deudor ? ($debe - $haber) : ($haber - $debe);
        if (abs($saldo) < 0.005) continue;
        $row['saldo'] = $saldo;
        $grupos[$row['tipo']][] = $row;
    }
    return $grupos;
}

$grupos = obtenerSaldosPorTipo($conn, $hasta);

$totalIngresos = array_sum(array_column($grupos['ingreso'], 'saldo'));
$totalGastos   = array_sum(array_column($grupos['gasto'], 'saldo'));
$resultadoEjercicio = $totalIngresos - $totalGastos;

$totalActivo = array_sum(array_column($grupos['activo'], 'saldo'));
$totalPasivo = array_sum(array_column($grupos['pasivo'], 'saldo'));
$totalPatrimonio = array_sum(array_column($grupos['patrimonio'], 'saldo')) + $resultadoEjercicio;

include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <h1>Balance general</h1>
    <a class="btn btn-print" target="_blank" href="print.php?hasta=<?= urlencode($hasta) ?>">Imprimir</a>
</div>

<div class="card">
    <form method="get" class="form-row" style="align-items:flex-end">
        <div class="form-group" style="max-width:220px">
            <label>Cortado al</label>
            <input type="date" name="hasta" class="form-control" value="<?= e($hasta) ?>">
        </div>
        <div class="form-group">
            <button class="btn">Calcular</button>
            <a class="btn btn-secondary" href="index.php">Limpiar</a>
        </div>
    </form>

    <table class="table">
        <thead>
            <tr><th colspan="3" style="background:#1f3a5f;color:#fff">ACTIVO</th></tr>
        </thead>
        <tbody>
        <?php foreach ($grupos['activo'] as $c): ?>
            <tr>
                <td><?= e($c['codigo']) ?></td>
                <td><?= e($c['nombre']) ?></td>
                <td class="text-right"><?= money($c['saldo']) ?></td>
            </tr>
        <?php endforeach; ?>
            <tr>
                <th colspan="2" class="text-right">TOTAL ACTIVO</th>
                <th class="text-right"><?= money($totalActivo) ?></th>
            </tr>
        </tbody>
    </table>

    <table class="table">
        <thead>
            <tr><th colspan="3" style="background:#1f3a5f;color:#fff">PASIVO</th></tr>
        </thead>
        <tbody>
        <?php foreach ($grupos['pasivo'] as $c): ?>
            <tr>
                <td><?= e($c['codigo']) ?></td>
                <td><?= e($c['nombre']) ?></td>
                <td class="text-right"><?= money($c['saldo']) ?></td>
            </tr>
        <?php endforeach; ?>
            <tr>
                <th colspan="2" class="text-right">TOTAL PASIVO</th>
                <th class="text-right"><?= money($totalPasivo) ?></th>
            </tr>
        </tbody>
    </table>

    <table class="table">
        <thead>
            <tr><th colspan="3" style="background:#1f3a5f;color:#fff">PATRIMONIO</th></tr>
        </thead>
        <tbody>
        <?php foreach ($grupos['patrimonio'] as $c): ?>
            <tr>
                <td><?= e($c['codigo']) ?></td>
                <td><?= e($c['nombre']) ?></td>
                <td class="text-right"><?= money($c['saldo']) ?></td>
            </tr>
        <?php endforeach; ?>
            <tr>
                <td>—</td>
                <td>Resultado del ejercicio (Ingresos - Gastos)</td>
                <td class="text-right"><?= money($resultadoEjercicio) ?></td>
            </tr>
            <tr>
                <th colspan="2" class="text-right">TOTAL PATRIMONIO</th>
                <th class="text-right"><?= money($totalPatrimonio) ?></th>
            </tr>
            <tr>
                <th colspan="2" class="text-right">TOTAL PASIVO + PATRIMONIO</th>
                <th class="text-right"><?= money($totalPasivo + $totalPatrimonio) ?></th>
            </tr>
        </tbody>
    </table>

    <?php $diff = abs(($totalPasivo + $totalPatrimonio) - $totalActivo); ?>
    <?php if ($diff < 0.01): ?>
        <div class="alert alert-success">El balance cuadra: ACTIVO = PASIVO + PATRIMONIO.</div>
    <?php else: ?>
        <div class="alert alert-warning">
            El balance no cuadra: diferencia de <?= money($diff) ?>. Revise los comprobantes.
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
