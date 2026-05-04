<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT id, username, nombre, rol, activo FROM usuarios WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    flashSet('error', 'Usuario no encontrado.');
    header('Location: index.php');
    exit;
}

$pageTitle = 'Editar usuario';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user['username'] = trim($_POST['username'] ?? '');
    $user['nombre']   = trim($_POST['nombre'] ?? '');
    $user['rol']      = $_POST['rol'] ?? 'contador';
    $user['activo']   = isset($_POST['activo']) ? 1 : 0;
    $password         = $_POST['password'] ?? '';
    $password2        = $_POST['password2'] ?? '';

    if ($user['username'] === '') $errors[] = 'El nombre de usuario es obligatorio.';
    if ($user['nombre'] === '')   $errors[] = 'El nombre es obligatorio.';
    if (!in_array($user['rol'], ['administrador','contador'], true)) $errors[] = 'Rol inválido.';

    if ($password !== '' || $password2 !== '') {
        if (strlen($password) < 4) $errors[] = 'La contraseña debe tener al menos 4 caracteres.';
        if ($password !== $password2) $errors[] = 'Las contraseñas no coinciden.';
    }

    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ? AND id <> ? LIMIT 1");
        $stmt->bind_param('si', $user['username'], $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = 'Ese nombre de usuario ya está en uso.';
        $stmt->close();
    }

    if (!$errors) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET username=?, nombre=?, rol=?, activo=?, password=? WHERE id=?");
            $stmt->bind_param('sssisi', $user['username'], $user['nombre'], $user['rol'], $user['activo'], $hash, $id);
        } else {
            $stmt = $conn->prepare("UPDATE usuarios SET username=?, nombre=?, rol=?, activo=? WHERE id=?");
            $stmt->bind_param('sssii', $user['username'], $user['nombre'], $user['rol'], $user['activo'], $id);
        }
        if ($stmt->execute()) {
            flashSet('success', 'Usuario actualizado correctamente.');
            header('Location: index.php');
            exit;
        }
        $errors[] = 'No se pudo actualizar: ' . $conn->error;
    }
}

include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><h1>Editar usuario</h1></div>

<div class="card">
    <?php if ($errors): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="form-row">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username" class="form-control" value="<?= e($user['username']) ?>" required>
            </div>
            <div class="form-group">
                <label>Nombre completo</label>
                <input type="text" name="nombre" class="form-control" value="<?= e($user['nombre']) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Rol</label>
                <select name="rol" class="form-control">
                    <option value="contador" <?= $user['rol']==='contador'?'selected':'' ?>>Contador (usuario)</option>
                    <option value="administrador" <?= $user['rol']==='administrador'?'selected':'' ?>>Administrador</option>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <label><input type="checkbox" name="activo" <?= ((int)$user['activo']===1)?'checked':'' ?>> Usuario activo</label>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Nueva contraseña <span class="help-text">(opcional)</span></label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-group">
                <label>Repetir nueva contraseña</label>
                <input type="password" name="password2" class="form-control">
            </div>
        </div>
        <button class="btn btn-success" type="submit">Actualizar</button>
        <a class="btn btn-secondary" href="index.php">Cancelar</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
