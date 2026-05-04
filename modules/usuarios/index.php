<?php
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();

$pageTitle = 'Gestión de usuarios';
$usuarios = $conn->query("SELECT id, username, nombre, rol, activo, created_at FROM usuarios ORDER BY id");
include __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <h1>Usuarios</h1>
    <a class="btn btn-success" href="create.php">+ Nuevo usuario</a>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Rol</th>
                <th class="text-center">Activo</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($u = $usuarios->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$u['id'] ?></td>
                <td><?= e($u['username']) ?></td>
                <td><?= e($u['nombre']) ?></td>
                <td><?= e(ucfirst($u['rol'])) ?></td>
                <td class="text-center"><?= ((int)$u['activo'] === 1) ? 'Sí' : 'No' ?></td>
                <td class="text-center">
                    <a class="btn btn-sm" href="edit.php?id=<?= (int)$u['id'] ?>">Editar</a>
                    <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                        <a class="btn btn-sm btn-danger"
                           href="delete.php?id=<?= (int)$u['id'] ?>"
                           onclick="return confirm('¿Eliminar este usuario?');">Eliminar</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
