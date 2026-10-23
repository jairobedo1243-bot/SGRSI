<?php

declare(strict_types=1);

// ============================================================
// diagnostico_login.php  -  HERRAMIENTA TEMPORAL DE DIAGNOSTICO
// Compara una contraseña contra el hash guardado y muestra en qué
// paso exacto falla el inicio de sesión.
// Solo lo puede abrir un Administrador con sesión iniciada.
// BORRAR ESTE ARCHIVO DESPUES DE USARLO.
// ============================================================

require_once __DIR__ . '/includes/bootstrap.php';

$auth = new SessionAuth();
$usuario = $auth->requireLogin(true);
if (!$auth->permissionsForRole((string)($usuario['rol'] ?? ''))['usuariosAdministrar']) {
    http_response_code(403);
    exit('Solo un Administrador puede abrir esta página.');
}

$email = trim((string)($_POST['email'] ?? ''));
$clave = (string)($_POST['clave'] ?? '');
$r = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valido() && $email !== '') {
    $fila = Conexion::selectOne('SELECT * FROM usuario WHERE email = ?', [$email]);
    $r['Correo buscado'] = "«{$email}» (" . strlen($email) . ' caracteres)';
    $r['Largo de la contraseña escrita'] = strlen($clave) . ' caracteres';

    if ($fila === null) {
        $r['¿Existe el correo?'] = 'NO — ninguna fila con ese correo exacto';
        $par = Conexion::select("SELECT email FROM usuario WHERE email LIKE ?", ['%' . $email . '%']);
        $r['Correos parecidos'] = $par === [] ? 'ninguno' : implode(', ', array_column($par, 'email'));
    } else {
        $hash = (string)$fila['contrasena'];
        $r['¿Existe el correo?'] = 'SÍ';
        $r['Cédula'] = $fila['ci_usuario'];
        $r['Nombre'] = trim(($fila['nom'] ?? '') . ' ' . ($fila['ape'] ?? ''));
        $r['Rol'] = $fila['rol'];
        $r['¿Activo?'] = ((int)$fila['activo'] === 1 ? 'SÍ' : 'NO — está dado de baja, por eso no entra');
        $r['¿Debe cambiar la clave?'] = (isset($fila['debe_cambiar_clave']) && (int)$fila['debe_cambiar_clave'] === 1 ? 'SÍ' : 'no');
        $r['Largo del hash guardado'] = strlen($hash) . ' caracteres (un hash de bcrypt completo tiene 60)';
        $r['Comienzo del hash'] = substr($hash, 0, 7);
        $info = password_get_info($hash);
        $r['Algoritmo del hash'] = ($info['algoName'] ?? '?') . ' · costo ' . ($info['options']['cost'] ?? '?');
        $ok = password_verify($clave, $hash);
        $r['¿La contraseña coincide con el hash?'] = $ok ? 'SÍ — la contraseña es correcta' : 'NO — no coincide';
        if (!$ok) {
            $r['Prueba sin espacios al borde'] = password_verify(trim($clave), $hash) ? 'COINCIDE si se le quitan los espacios' : 'tampoco';
            $r['Prueba todo en minúscula'] = password_verify(mb_strtolower($clave), $hash) ? 'COINCIDE en minúscula' : 'tampoco';
        }
    }
    $r['¿Hay bloqueo por intentos en esta sesión?'] = $auth->estaBloqueado() ? 'SÍ' : 'no';
    $r['Versión de PHP'] = PHP_VERSION;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGRSI – Diagnóstico de inicio de sesión</title>
    <link rel="stylesheet" href="CSS/styles.css">
</head>
<body>
<header><h1>SGRSI</h1><p>Diagnóstico de inicio de sesión (herramienta temporal)</p></header>
<nav><a href="usuarios.php">Usuarios</a><a href="index.php">Inicio</a></nav>
<main>
    <section>
        <h2>Probar un correo y una contraseña</h2>
        <p class="mensaje info">Esta página es solo para encontrar el problema. Borrala del servidor y de XAMPP cuando termines.</p>
        <form method="POST" autocomplete="off">
            <?= csrf_campo() ?>
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
            <label for="clave">Contraseña a probar</label>
            <input type="text" id="clave" name="clave" value="<?= e($clave) ?>" required>
            <button type="submit">Probar</button>
        </form>

        <?php if ($r !== []): ?>
        <div class="tabla-wrapper">
            <table>
                <thead><tr><th>Dato</th><th>Resultado</th></tr></thead>
                <tbody>
                <?php foreach ($r as $k => $v): ?>
                    <tr><td><?= e($k) ?></td><td><strong><?= e((string)$v) ?></strong></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
</main>
<footer><p><?= t('footer') ?></p></footer>
</body>
</html>
