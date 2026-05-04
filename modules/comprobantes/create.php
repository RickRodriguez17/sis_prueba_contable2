<?php
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Nuevo comprobante';
$errors = [];

$cuentas = [];
$resCuentas = $conn->query("SELECT id, codigo, nombre FROM cuentas WHERE activo = 1 ORDER BY codigo");
while ($row = $resCuentas->fetch_assoc()) {
    $cuentas[] = $row;
}

$old = [
    'fecha' => date('Y-m-d'),
    'descripcion' => '',
];
$detallesViejos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['fecha']       = $_POST['fecha'] ?? '';
    $old['descripcion'] = trim($_POST['descripcion'] ?? '');
    $cuentasIn = $_POST['cuenta_id'] ?? [];
    $debes     = $_POST['debe']      ?? [];
    $haberes   = $_POST['haber']     ?? [];

    // Reconstruir detalles para repintar el form en caso de error
    $detalles = [];
    $totalDebe = 0;
    $totalHaber = 0;
    for ($i = 0; $i < count($cuentasIn); $i++) {
        $cuentaId = (int)($cuentasIn[$i] ?? 0);
        $debe = (float)str_replace(',', '.', $debes[$i] ?? 0);
        $haber = (float)str_replace(',', '.', $haberes[$i] ?? 0);
        if ($cuentaId === 0 && $debe == 0 && $haber == 0) continue;
        $detalles[] = ['cuenta_id'=>$cuentaId, 'debe'=>$debe, 'haber'=>$haber];
        $totalDebe += $debe;
        $totalHaber += $haber;
    }
    $detallesViejos = $detalles;

    // Validaciones
    if ($old['fecha'] === '' || !DateTime::createFromFormat('Y-m-d', $old['fecha'])) {
        $errors[] = 'Fecha inválida.';
    }
    if ($old['descripcion'] === '') {
        $errors[] = 'La descripción es obligatoria.';
    }
    if (count($detalles) < 2) {
        $errors[] = 'Debe registrar al menos 2 líneas de detalle.';
    }
    foreach ($detalles as $i => $d) {
        if ($d['cuenta_id'] <= 0) {
            $errors[] = 'Línea ' . ($i+1) . ': cuenta inválida.';
        }
        if ($d['debe'] < 0 || $d['haber'] < 0) {
            $errors[] = 'Línea ' . ($i+1) . ': los montos no pueden ser negativos.';
        }
        if ($d['debe'] > 0 && $d['haber'] > 0) {
            $errors[] = 'Línea ' . ($i+1) . ': una línea no puede tener debe y haber al mismo tiempo.';
        }
        if ($d['debe'] == 0 && $d['haber'] == 0) {
            $errors[] = 'Línea ' . ($i+1) . ': debe ingresar un monto en debe o haber.';
        }
    }
    if (round($totalDebe, 2) !== round($totalHaber, 2) || $totalDebe == 0) {
        $errors[] = 'El total del DEBE (' . money($totalDebe) . ') debe ser igual al total del HABER (' . money($totalHaber) . ') y mayor a cero.';
    }

    if (!$errors) {
        $numero = siguienteNumeroComprobante($conn);
        $userId = (int)$_SESSION['user_id'];

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO comprobantes (numero, fecha, descripcion, usuario_id, total_debe, total_haber) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('sssidd', $numero, $old['fecha'], $old['descripcion'], $userId, $totalDebe, $totalHaber);
            $stmt->execute();
            $compId = $conn->insert_id;
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO comprobante_detalles (comprobante_id, cuenta_id, debe, haber) VALUES (?,?,?,?)");
            foreach ($detalles as $d) {
                $stmt->bind_param('iidd', $compId, $d['cuenta_id'], $d['debe'], $d['haber']);
                $stmt->execute();
            }
            $stmt->close();
            $conn->commit();

            flashSet('success', 'Comprobante ' . $numero . ' registrado correctamente.');
            header('Location: view.php?id=' . $compId);
            exit;
        } catch (Throwable $ex) {
            $conn->rollback();
            $errors[] = 'No se pudo guardar: ' . $ex->getMessage();
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><h1>Nuevo comprobante contable</h1></div>

<div class="card">
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form id="form-comprobante" method="post" action="">
        <div class="form-row">
            <div class="form-group" style="max-width:220px">
                <label>Fecha</label>
                <input type="date" name="fecha" class="form-control" value="<?= e($old['fecha']) ?>" required>
            </div>
            <div class="form-group">
                <label>Descripción / glosa</label>
                <input type="text" name="descripcion" class="form-control" value="<?= e($old['descripcion']) ?>" required>
            </div>
        </div>

        <h3>Detalle del asiento</h3>
        <table class="table" id="tabla-detalle">
            <thead>
                <tr>
                    <th style="width:55%">Cuenta</th>
                    <th class="text-right">Debe</th>
                    <th class="text-right">Haber</th>
                    <th class="text-center" style="width:60px">-</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $filasIniciales = $detallesViejos ?: [['cuenta_id'=>0,'debe'=>0,'haber'=>0],['cuenta_id'=>0,'debe'=>0,'haber'=>0]];
            foreach ($filasIniciales as $det):
            ?>
                <tr class="detalle-row">
                    <td>
                        <select name="cuenta_id[]" class="form-control cuenta-select" required>
                            <option value="">-- Seleccionar cuenta --</option>
                            <?php foreach ($cuentas as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"
                                    <?= ((int)$det['cuenta_id'] === (int)$c['id'])?'selected':'' ?>>
                                    <?= e($c['codigo'] . ' - ' . $c['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="number" step="0.01" min="0" name="debe[]" value="<?= e($det['debe']) ?>" class="form-control text-right"></td>
                    <td><input type="number" step="0.01" min="0" name="haber[]" value="<?= e($det['haber']) ?>" class="form-control text-right"></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="eliminarFila(this)">X</button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-right">Totales</th>
                    <th class="text-right" id="total-debe">0.00</th>
                    <th class="text-right" id="total-haber">0.00</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>

        <template id="plantilla-fila">
            <tr class="detalle-row">
                <td>
                    <select name="cuenta_id[]" class="form-control cuenta-select" required>
                        <option value="">-- Seleccionar cuenta --</option>
                        <?php foreach ($cuentas as $c): ?>
                            <option value="<?= (int)$c['id'] ?>"><?= e($c['codigo'] . ' - ' . $c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="number" step="0.01" min="0" name="debe[]" value="0" class="form-control text-right"></td>
                <td><input type="number" step="0.01" min="0" name="haber[]" value="0" class="form-control text-right"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="eliminarFila(this)">X</button></td>
            </tr>
        </template>

        <p>
            <button type="button" class="btn btn-secondary" onclick="agregarFilaDetalle()">+ Agregar línea</button>
        </p>

        <div id="aviso-balance"></div>

        <button class="btn btn-success" type="submit">Guardar comprobante</button>
        <a class="btn btn-secondary" href="index.php">Cancelar</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
