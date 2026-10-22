<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$auth = new SessionAuth();
$usuario = $auth->requireLogin();
$permiso = $auth->permissionsForRole((string)($usuario['rol'] ?? 'Solicitante'));
$idioma = Translator::currentLanguage();
?>
<!DOCTYPE html>
<html lang="<?= $idioma ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('tituloInicio') ?></title>
    <link rel="stylesheet" href="CSS/styles.css">
</head>
<body>

<header>
    <h1>SGRSI</h1>
    <p><?= t('appSubtitulo') ?></p>
    <div style="margin-top:8px;font-size:0.85rem;opacity:0.9;">
        <?= e($usuario['nom']) ?> <?= e($usuario['ape']) ?>
        <span class="badge <?= $permiso['clase'] ?>" style="vertical-align:middle;margin-left:4px;"><?= tv($permiso['etiqueta']) ?></span>
    </div>
    <div style="margin-top:6px;font-size:0.8rem;">
        <a href="?idioma=es" style="color:<?= $idioma === 'es' ? '#fff' : '#94a3b8' ?>;text-decoration:none;font-weight:<?= $idioma === 'es' ? '700' : '400' ?>;">ES</a>
        <span style="opacity:0.5;">|</span>
        <a href="?idioma=en" style="color:<?= $idioma === 'en' ? '#fff' : '#94a3b8' ?>;text-decoration:none;font-weight:<?= $idioma === 'en' ? '700' : '400' ?>;">EN</a>
    </div>
</header>

<nav>
    <a href="index.php" class="activo"><?= t('navInicio') ?></a>
    <?php if (in_array('recursos.php', $permiso['paginas'], true)): ?><a href="recursos.php"><?= t('navInventario') ?></a><?php endif; ?>
    <?php if (in_array('usuarios.php', $permiso['paginas'], true)): ?><a href="usuarios.php"><?= t('navUsuarios') ?></a><?php endif; ?>
    <?php if (in_array('prestamos.php', $permiso['paginas'], true)): ?><a href="prestamos.php"><?= t('navPrestamos') ?></a><?php endif; ?>
    <?php if (in_array('tickets.php', $permiso['paginas'], true)): ?><a href="tickets.php"><?= t('navTickets') ?></a><?php endif; ?>
    <?php if (in_array('reportes.php', $permiso['paginas'], true)): ?><a href="reportes.php"><?= t('navReportes') ?></a><?php endif; ?>
    <?php if (in_array('historial.php', $permiso['paginas'], true)): ?><a href="historial.php"><?= t('navHistorial') ?></a><?php endif; ?>

    <a href="logout.php" style="margin-left:auto;color:#fca5a5;"><?= t('navCerrarSesion') ?></a>
</nav>

<main>
    <div class="bienvenida-hero">
        <h2><?= t('bienvenidaTitulo') ?></h2>
        <p><?= t('bienvenidaTexto') ?></p>
    </div>

    <div class="modulos-grid">
        <?php
        // Solo se muestran los módulos a los que el rol puede entrar.
        $modulos = [
            'recursos.php' => ['navInventario', 'moduloInventarioDesc'],
            'usuarios.php' => ['navUsuarios', 'moduloUsuariosDesc'],
            'prestamos.php' => ['navPrestamos', 'moduloPrestamosDesc'],
            'tickets.php' => ['navTickets', 'moduloTicketsDesc'],
            'reportes.php' => ['navReportes', 'moduloReportesDesc'],
            'historial.php' => ['navHistorial', 'moduloHistorialDesc'],
        ];
        foreach ($modulos as $pagina => [$titulo, $descripcion]):
            if (!in_array($pagina, $permiso['paginas'], true)) {
                continue;
            }
        ?>
        <a href="<?= $pagina ?>" class="modulo-card">
            <h3><?= t($titulo) ?></h3>
            <p><?= t($descripcion) ?></p>
        </a>
        <?php endforeach; ?>
    </div>
</main>

<footer>
    <p><?= t('footer') ?></p>
</footer>

</body>
</html>
