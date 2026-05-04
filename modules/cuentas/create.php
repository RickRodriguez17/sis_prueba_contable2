<?php
require_once __DIR__ . '/../../includes/auth.php';

$pageTitle = 'Nueva cuenta contable';
$errors = [];
$old = ['codigo' => '', 'nombre' => '', 'tipo' => 'activo', 'activo' => 1];
$tiposValidos = ['activo','pasivo','patrimonio','ingreso','gasto'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['codigo'] = trim($_POST['codigo'] ?? '');
    $old['nombre'] = trim($_POST['nombre'] ?? '');
    $old['tipo']   = $_POST['tipo'] ?? '';
    $old['activo'] = isset($_POST['activo']) ? 1 : 0;

    // Validación BACKEND del código (numérico, exactamente 8 dígitos)
    if (!validarCodigoCuenta($old['codigo'])) {
        $errors[] = 'El código debe ser numérico y tener exactamente 8 dígitos (sin letras ni símbolos).';
    }
    if ($old['nombre'] === '') {
        $errors[] = 'El nombre de la cuenta es obligatorio.';
    }
    if (!in_array($old['tipo'], $tiposValidos, true)) {
        $errors[] = 'Tipo de cuenta inválido.';
    }

    // Verificar duplicados (UNIQUE en BD)
    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM cuentas WHERE codigo = ? LIMIT 1");
        $stmt->bind_param('s', $old['codigo']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Ya existe una cuenta con ese código. El código debe ser único.';
        }
        $stmt->close();
    }

    if (!$errors) {
        $stmt = $conn->prepare("INSERT INTO cuentas (codigo, nombre, tipo, activo) VALUES (?,?,?,?)");
        $stmt->bind_param('sssi', $old['codigo'], $old['nombre'], $old['tipo'], $old['activo']);
        if ($stmt->execute()) {
            flashSet('success', 'Cuenta contable creada correctamente.');
            header('Location: index.php');
            exit;
        }
        // Capturar duplicado por restricción UNIQUE
        if ($conn->errno === 1062) {
            $errors[] = 'Ya existe una cuenta con ese código.';
        } else {
            $errors[] = 'No se pudo guardar la cuenta: ' . $conn->error;
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><h1>Nueva cuenta contable</h1></div>

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
                       value="<?= e($old['codigo']) ?>"
                       data-validate="codigo-cuenta"
                       data-error-target="err-codigo"
                       maxlength="8"
                       inputmode="numeric"
                       pattern="[0-9]{8}"
                       required>
                <div class="error-text" id="err-codigo"></div>
                <div class="help-text">Solo números, exactamente 8 dígitos. Ejemplo: 11010001</div>
            </div>
            <div class="form-group">
                <label>Nombre de la cuenta</label>
                <input type="text" name="nombre" class="form-control" value="<?= e($old['nombre']) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group" style="max-width:260px">
                <label>Tipo</label>
                <select name="tipo" class="form-control" required>
                    <?php foreach (['activo','pasivo','patrimonio','ingreso','gasto'] as $t): ?>
                        <option value="<?= $t ?>" <?= $old['tipo']===$t?'selected':'' ?>>
                            <?= e(tipoCuentaLabel($t)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <label><input type="checkbox" name="activo" <?= $old['activo']?'checked':'' ?>> Cuenta activa</label>
            </div>
        </div>
        <button class="btn btn-success" type="submit">Guardar</button>
        <a class="btn btn-secondary" href="index.php">Cancelar</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
