<?php
// ============================================================
// generar_hash.php  -  HERRAMIENTA DE APOYO (no es parte del sistema)
//
// Sirve para crear el hash de una contraseña cuando querés dar de
// alta un usuario a mano desde phpMyAdmin.
//
// Cómo se usa:
//   1. Abrir en el navegador:
//        http://localhost/generar_hash.php  (o http://localhost/SGRSI/public/generar_hash.php)
//   2. Escribir la contraseña que querés y apretar "Generar".
//   3. Copiar el hash y pegarlo en la columna "contrasena".
//
// IMPORTANTE: este archivo es sólo para desarrollo. Antes de
// entregar o publicar el proyecto, borralo.
// ============================================================

// Solo se puede usar desde esta misma computadora.
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit;
}

$hash = "";
$pass = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pass = $_POST["pass"] ?? "";
    if ($pass !== "") {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>SGRSI – Generar hash de contraseña</title>
    <link rel="stylesheet" href="CSS/styles.css">
</head>
<body>

<header>
    <h1>SGRSI</h1>
    <p>Herramienta de desarrollo – Generar hash de contraseña</p>
</header>

<main>
    <section>
        <h2>Generar hash</h2>

        <form method="POST">
            <label for="pass">Contraseña en texto plano</label>
            <input type="text" id="pass" name="pass" value="<?= htmlspecialchars($pass) ?>"
                   placeholder="Ej: Lopez123!" required>
            <button type="submit">Generar</button>
        </form>

        <?php if ($hash !== ""): ?>
            <p class="mensaje exito">Hash generado. Copialo y pegalo en la columna
               <strong>contrasena</strong> de la tabla <strong>usuario</strong>.</p>

            <div class="tabla-wrapper">
                <table>
                    <thead>
                        <tr><th>Contraseña</th><th>Hash para guardar en la base</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= htmlspecialchars($pass) ?></td>
                            <td style="font-family:monospace;font-size:0.8rem;word-break:break-all;">
                                <?= htmlspecialchars($hash) ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p style="font-size:0.85rem;color:var(--text-muted);margin-top:10px;">
                Ejemplo de INSERT completo:
            </p>
            <div class="tabla-wrapper">
                <table>
                    <tbody>
                        <tr><td style="font-family:monospace;font-size:0.78rem;word-break:break-all;">
INSERT INTO usuario (ci_usuario, nom, ape, email, contrasena, rol) VALUES
(12345672, 'Nombre', 'Apellido', 'correo@utu.edu.uy', '<?= htmlspecialchars($hash) ?>', 'Solicitante');
                        </td></tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<footer>
    <p>© SGRSI – Proyecto Bachillerato Tecnológico</p>
</footer>

</body>
</html>
