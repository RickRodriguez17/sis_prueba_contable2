<?php
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Plan de cuentas';
$tipoFiltro = $_GET['tipo'] ?? '';
$tiposValidos = ['activo','pasivo','patrimonio','ingreso','gasto'];

if ($tipoFiltro !== '' && in_array($tipoFiltro, $tiposValidos, true)) {
    $stmt = $conn->prepare("SELECT * FROM cuentas WHERE tipo = ? ORDER BY codigo");
    $stmt->bind_param('s', $tipoFiltro);
    $stmt->execute();
    $cuentas = $stmt->get_result();
} else {
    $cuentas = $conn->query("SELECT * FROM cuentas ORDER BY codigo");
}

include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <h1>Plan de cuentas</h1>
    <div>
        <a class="btn btn-success" href="create.php">+ Nueva cuenta</a>
        <a class="btn btn-print" href="print.php<?= $tipoFiltro ? '?tipo='.urlencode($tipoFiltro) : '' ?>" target="_blank">Imprimir</a>
    </div>
</div>

<div class="card">
    <form method="get" class="form-row" style="margin-bottom:14px">
        <div class="form-group" style="max-width:260px">
            <label>Filtrar por tipo</label>
            <select name="tipo" class="form-control" onchange="this.form.submit()">
                <option value="">Todas</option>
                <?php foreach ($tiposValidos as $t): ?>
                    <option value="<?= $t ?>" <?= $tipoFiltro===$t?'selected':'' ?>>
                        <?= e(tipoCuentaLabel($t)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th style="width:140px">Código</th>
                <th>Nombre</th>
                <th style="width:130px">Tipo</th>
                <th class="text-center" style="width:90px">Activa</th>
                <th class="text-center" style="width:160px">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($c = $cuentas->fetch_assoc()): ?>
            <tr>
                <td><?= e($c['codigo']) ?></td>
                <td><?= e($c['nombre']) ?></td>
                <td><?= e(tipoCuentaLabel($c['tipo'])) ?></td>
                <td class="text-center"><?= ((int)$c['activo']===1) ? 'Sí' : 'No' ?></td>
                <td class="text-center">
                    <a class="btn btn-sm" href="edit.php?id=<?= (int)$c['id'] ?>">Editar</a>
                    <a class="btn btn-sm btn-danger"
                       href="delete.php?id=<?= (int)$c['id'] ?>"
                       onclick="return confirm('¿Eliminar esta cuenta?');">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
