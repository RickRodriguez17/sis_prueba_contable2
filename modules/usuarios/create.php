<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$pageTitle = 'Nuevo usuario';
$errors = [];
$old = ['username' => '', 'nombre' => '', 'rol' => 'contador', 'activo' => 1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['username'] = trim($_POST['username'] ?? '');
    $old['nombre']   = trim($_POST['nombre'] ?? '');
    $old['rol']      = $_POST['rol'] ?? 'contador';
    $old['activo']   = isset($_POST['activo']) ? 1 : 0;
    $password        = $_POST['password'] ?? '';
    $password2       = $_POST['password2'] ?? '';

    if ($old['username'] === '')         $errors[] = 'El nombre de usuario es obligatorio.';
    if ($old['nombre'] === '')           $errors[] = 'El nombre es obligatorio.';
    if (!in_array($old['rol'], ['administrador','contador'], true))
                                          $errors[] = 'Rol inválido.';
    if (strlen($password) < 4)           $errors[] = 'La contraseña debe tener al menos 4 caracteres.';
    if ($password !== $password2)        $errors[] = 'Las contraseñas no coinciden.';

    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $old['username']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Ese nombre de usuario ya existe.';
        }
        $stmt->close();
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (username, password, nombre, rol, activo) VALUES (?,?,?,?,?)");
        $stmt->bind_param('ssssi', $old['username'], $hash, $old['nombre'], $old['rol'], $old['activo']);
        if ($stmt->execute()) {
            flashSet('success', 'Usuario creado correctamente.');
            header('Location: index.php');
            exit;
        }
        $errors[] = 'No se pudo guardar el usuario: ' . $conn->error;
    }
}

include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header"><h1>Nuevo usuario</h1></div>

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
                <input type="text" name="username" class="form-control" value="<?= e($old['username']) ?>" required>
            </div>
            <div class="form-group">
                <label>Nombre completo</label>
                <input type="text" name="nombre" class="form-control" value="<?= e($old['nombre']) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Rol</label>
                <select name="rol" class="form-control">
                    <option value="contador" <?= $old['rol']==='contador'?'selected':'' ?>>Contador (usuario)</option>
                    <option value="administrador" <?= $old['rol']==='administrador'?'selected':'' ?>>Administrador</option>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <label><input type="checkbox" name="activo" <?= $old['activo']?'checked':'' ?>> Usuario activo</label>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Repetir contraseña</label>
                <input type="password" name="password2" class="form-control" required>
            </div>
        </div>
        <button class="btn btn-success" type="submit">Guardar</button>
        <a class="btn btn-secondary" href="index.php">Cancelar</a>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
