<?php
require_once __DIR__ . '/../../includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM cuentas WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$cuenta = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cuenta) {
    flashSet('error', 'Cuenta no encontrada.');
    header('Location: index.php');
    exit;
}

$pageTitle = 'Editar cuenta';
$errors = [];
$tiposValidos = ['activo','pasivo','patrimonio','ingreso','gasto'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cuenta['codigo'] = trim($_POST['codigo'] ?? '');
    $cuenta['nombre'] = trim($_POST['nombre'] ?? '');
    $cuenta['tipo']   = $_POST['tipo'] ?? '';
    $cuenta['activo'] = isset($_POST['activo']) ? 1 : 0;

    if (!validarCodigoCuenta($cuenta['codigo'])) {
        $errors[] = 'El código debe ser numérico y tener exactamente 8 dígitos.';
    }
    if ($cuenta['nombre'] === '') $errors[] = 'El nombre es obligatorio.';
    if (!in_array($cuenta['tipo'], $tiposValidos, true)) $errors[] = 'Tipo inválido.';

    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM cuentas WHERE codigo = ? AND id <> ? LIMIT 1");
        $stmt->bind_param('si', $cuenta['codigo'], $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = 'Ya existe otra cuenta con ese código.';
        $stmt->close();
    }

    if (!$errors) {
        $stmt = $conn->prepare("UPDATE cuentas SET codigo=?, nombre=?, tipo=?, activo=? WHERE id=?");
        $stmt->bind_param('sssii', $cuenta['codigo'], $cuenta['nombre'], $cuenta['tipo'], $cuenta['activo'], $id);
        if ($stmt->execute()) {
            flashSet('success', 'Cuenta actualizada correctamente.');
            header('Location: index.php');
            exit;
        }
        if ($conn->errno === 1062) {
            $errors[] = 'Ya existe una cuenta con ese código.';
        } else {
            $errors[] = 'No se pudo actualizar: ' . $conn->error;
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><h1>Editar cuenta contable</h1></div>

<div class="card">
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form id="form-cuenta" method="post" action="" novalidate>
        <div class="form-row">
            <div class="form-group" style="max-width:240px">
                <label>Código (8 dígitos)</label>
                <input type="text"
                       name="codigo"
                       class="form-control"
                       value="<?= e($cuenta['codigo']) ?>"
                       data-validate="codigo-cuenta"
                       data-error-target="err-codigo"
                       maxlength="8"
                       inputmode="numeric"
                       pattern="[0-9]{8}"
                       required>
                <div class="error-text" id="err-codigo"></div>
                <div class="help-text">Solo números, exactamente 8 dígitos.</div>
            </div>
            <div class="form-group">
                <label>Nombre de la cuenta</label>
                <input type="text" name="nombre" class="form-control" value="<?= e($cuenta['nombre']) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group" style="max-width:260px">
                <label>Tipo</label>
                <select name="tipo" class="form-control" required>
                    <?php foreach ($tiposValidos as $t): ?>
                        <option value="<?= $t ?>" <?= $cuenta['tipo']===$t?'selected':'' ?>>
                            <?= e(tipoCuentaLabel($t)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <label><input type="checkbox" name="activo" <?= ((int)$cuenta['activo']===1)?'checked':'' ?>> Cuenta activa</label>
            </div>
        </div>
        <button class="btn btn-success" type="submit">Actualizar</button>
        <a class="btn btn-secondary" href="index.php">Cancelar</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
