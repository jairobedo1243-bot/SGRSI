<?php

declare(strict_types=1);

// ============================================================
// migrar.php  -  SE USA UNA SOLA VEZ en cada base ya instalada.
//
// Prepara la base para la baja LÓGICA de usuarios (RF-06 / RN-10):
// agrega la columna usuario.activo y se asegura de que préstamos,
// tickets y servicios tengan su clave foránea hacia usuario.
// No borra datos.
//
// Cómo se usa: entrar como Administrador, abrir /migrar.php y apretar
// "Aplicar". Después de usarlo en la PC y en el servidor, se puede borrar.
// Una base nueva importada con sql/BD_SGRSI.sql ya viene así y no lo necesita.
// ============================================================

require_once __DIR__ . '/includes/bootstrap.php';

$auth = new SessionAuth();
$migracion = new MigracionRepository();

// Antes de migrar, la columna activo todavía no existe y el login normal (que filtra
// por activo) no funciona. Por eso esta página tiene su propio ingreso de Administrador,
// que no consulta esa columna. Vale una sesión de Administrador ya abierta o este ingreso.
$usuario = $auth->getUser();
$esAdmin = ($usuario !== null && $auth->permissionsForRole((string)($usuario['rol'] ?? ''))['usuariosAdministrar'])
    || !empty($_SESSION['migrar_admin']);
$errorIngreso = '';

if (!$esAdmin && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $candidato = $migracion->buscarUsuarioPorEmail(trim((string)$_POST['email']));
    $claveOk = $candidato !== null
        && password_verify((string)($_POST['contrasena'] ?? ''), (string)$candidato['contrasena']);

    if (!csrf_valido()) {
        $errorIngreso = 'La página expiró, volvé a intentar';
    } elseif (!$claveOk || !$auth->permissionsForRole((string)$candidato['rol'])['usuariosAdministrar']) {
        usleep(400000);
        $errorIngreso = 'Credenciales inválidas o el usuario no es Administrador';
    } else {
        session_regenerate_id(true);
        $_SESSION['migrar_admin'] = true;
        header('Location: migrar.php');
        exit;
    }
}

if (!$esAdmin) {
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGRSI – Actualizar base de datos</title>
    <link rel="stylesheet" href="CSS/styles.css">
</head>
<body>
<header><h1>SGRSI</h1><p>Actualizar base de datos</p></header>
<main>
    <section>
        <h2>Ingreso de Administrador</h2>
        <?php if ($errorIngreso !== ''): ?><p class="mensaje error"><?= e($errorIngreso) ?></p><?php endif; ?>
        <form method="POST">
            <?= csrf_campo() ?>
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" required>
            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" required>
            <button type="submit">Ingresar</button>
        </form>
    </section>
</main>
</body>
</html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        flash_y_volver('Solicitud no válida, recargá la página', false);
    }

    $fallas = $migracion->aplicar();
    if ($fallas === []) {
        flash_y_volver('Listo: la base quedó actualizada para la baja lógica de usuarios.', true);
    }
    flash_y_volver('No se pudo completar: ' . implode(', ', $fallas) . '. El usuario de MySQL de config/secreto.php necesita permisos ALTER y REFERENCES (el detalle está en el log de Apache).', false);
}

$flash = flash_tomar();
$pendientes = $migracion->pendientes();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGRSI – Actualizar base de datos</title>
    <link rel="stylesheet" href="CSS/styles.css">
</head>
<body>

<header>
    <h1>SGRSI</h1>
    <p>Actualizar base de datos</p>
</header>

<nav>
    <a href="index.php">Inicio</a>
    <a href="usuarios.php">Usuarios</a>
</nav>

<main>
    <section>
        <h2>Baja lógica de usuarios (RF-06 / RN-10)</h2>

        <?= flash_html($flash) ?>

        <?php if ($pendientes === []): ?>
            <p class="mensaje exito">La base ya está actualizada: los usuarios se dan de baja sin borrar sus préstamos, tickets ni servicios. Esta página ya se puede borrar.</p>
        <?php else: ?>
            <p class="mensaje info">Faltan estos pasos. Al aplicarlos no se borra ningún dato.</p>
            <div class="tabla-wrapper">
                <table>
                    <thead><tr><th>#</th><th>Paso pendiente</th></tr></thead>
                    <tbody>
                    <?php foreach ($pendientes as $i => $paso): ?>
                        <tr><td><?= $i + 1 ?></td><td><?= e($paso) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <form method="POST">
                <?= csrf_campo() ?>
                <button type="submit">Aplicar</button>
            </form>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>© SGRSI – Proyecto Bachillerato Tecnológico</p>
</footer>

</body>
</html>
