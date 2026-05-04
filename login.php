<?php
require_once __DIR__ . '/config/config.php';

// Si ya está logueado, ir al dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Ingrese usuario y contraseña.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, nombre, rol, activo FROM usuarios WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if ($user && (int)$user['activo'] === 1 && password_verify($password, $user['password'])) {
            $_SESSION['user_id']     = (int)$user['id'];
            $_SESSION['user_nombre'] = $user['nombre'];
            $_SESSION['user_rol']    = $user['rol'];
            $_SESSION['user_username'] = $user['username'];
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="login-wrap">
    <form class="login-card" method="post" action="">
        <h1><?= e(APP_NAME) ?></h1>
        <div class="subtitle">Inicio de sesión</div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label for="username">Usuario</label>
            <input type="text" name="username" id="username" class="form-control" required autofocus>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn">Ingresar</button>

        <p class="help-text" style="margin-top:14px;text-align:center">
            Usuario por defecto: <strong>admin / admin123</strong>
        </p>
    </form>
</div>
</body>
</html>
