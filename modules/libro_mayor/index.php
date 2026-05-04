<?php
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Libro mayor';

$cuentaId = (int)($_GET['cuenta_id'] ?? 0);
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

$cuentas = $conn->query("SELECT id, codigo, nombre FROM cuentas ORDER BY codigo");

$movs = null;
$cuenta = null;
if ($cuentaId > 0) {
    $stmt = $conn->prepare("SELECT * FROM cuentas WHERE id = ?");
    $stmt->bind_param('i', $cuentaId);
    $stmt->execute();
    $cuenta = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $where = 'WHERE d.cuenta_id = ?';
    $params = [$cuentaId];
    $types = 'i';
    if ($desde !== '') { $where .= ' AND c.fecha >= ?'; $params[] = $desde; $types .= 's'; }
    if ($hasta !== '') { $where .= ' AND c.fecha <= ?'; $params[] = $hasta; $types .= 's'; }

    $stmt = $conn->prepare("
        SELECT c.fecha, c.numero, c.descripcion, d.debe, d.haber
        FROM comprobante_detalles d
        JOIN comprobantes c ON c.id = d.comprobante_id
        $where
        ORDER BY c.fecha ASC, c.id ASC, d.id ASC
    ");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $movs = $stmt->get_result();
}

include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <h1>Libro mayor</h1>
    <?php if ($cuenta): ?>
        <a class="btn btn-print" target="_blank"
           href="print.php?cuenta_id=<?= (int)$cuentaId ?>&desde=<?= urlencode($desde) ?>&hasta=<?= urlencode($hasta) ?>">Imprimir</a>
    <?php endif; ?>
</div>

<div class="card">
    <form method="get" class="form-row" style="align-items:flex-end">
        <div class="form-group">
            <label>Cuenta</label>
            <select name="cuenta_id" class="form-control" required>
                <option value="">-- Seleccionar cuenta --</option>
                <?php while ($c = $cuentas->fetch_assoc()): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= ($cuentaId === (int)$c['id'])?'selected':'' ?>>
                        <?= e($c['codigo'] . ' - ' . $c['nombre']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group" style="max-width:200px">
            <label>Desde</label>
            <input type="date" name="desde" class="form-control" value="<?= e($desde) ?>">
        </div>
        <div class="form-group" style="max-width:200px">
            <label>Hasta</label>
            <input type="date" name="hasta" class="form-control" value="<?= e($hasta) ?>">
        </div>
        <div class="form-group">
            <button class="btn" type="submit">Consultar</button>
        </div>
    </form>

    <?php if ($cuenta && $movs): ?>
        <h3><?= e($cuenta['codigo']) ?> - <?= e($cuenta['nombre']) ?> (<?= e(tipoCuentaLabel($cuenta['tipo'])) ?>)</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Comprobante</th>
                    <th>Glosa</th>
                    <th class="text-right">Debe</th>
                    <th class="text-right">Haber</th>
                    <th class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $saldo = 0;
            $totalDebe = 0;
            $totalHaber = 0;
            // Convención: activo y gasto -> saldo deudor (debe - haber); pasivo, patrimonio, ingreso -> saldo acreedor (haber - debe)
            $deudor = in_array($cuenta['tipo'], ['activo','gasto'], true);
            while ($m = $movs->fetch_assoc()):
                $totalDebe += $m['debe'];
                $totalHaber += $m['haber'];
                $saldo += $deudor ? ($m['debe'] - $m['haber']) : ($m['haber'] - $m['debe']);
            ?>
                <tr>
                    <td><?= e($m['fecha']) ?></td>
                    <td><?= e($m['numero']) ?></td>
                    <td><?= e($m['descripcion']) ?></td>
                    <td class="text-right"><?= $m['debe']>0?money($m['debe']):'' ?></td>
                    <td class="text-right"><?= $m['haber']>0?money($m['haber']):'' ?></td>
                    <td class="text-right"><?= money($saldo) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-right">Totales</th>
                    <th class="text-right"><?= money($totalDebe) ?></th>
                    <th class="text-right"><?= money($totalHaber) ?></th>
                    <th class="text-right"><?= money($saldo) ?></th>
                </tr>
            </tfoot>
        </table>
    <?php elseif ($cuentaId > 0): ?>
        <div class="alert alert-warning">No se encontraron movimientos para esa cuenta.</div>
    <?php else: ?>
        <p class="help-text">Seleccione una cuenta para ver sus movimientos.</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
