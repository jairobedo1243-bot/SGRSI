<?php

declare(strict_types=1);
defined('ENTRYPOINT') || (http_response_code(404) && exit);

// Configuración global.
// Las credenciales NO van acá: están en config/secreto.php (no se sube a GitHub).
// Copiá config/secreto.ejemplo.php como config/secreto.php y poné tus datos.

// No mostrar errores en pantalla (pueden revelar rutas, usuarios y claves).
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Hora de Uruguay. XAMPP trae Europe/Berlin por defecto en php.ini, y con eso
// "hoy" cambia de día a las 19/20 h y la validación de fechas falla.
date_default_timezone_set('America/Montevideo');

define('DB_HOST', 'localhost');
define('DB_NAME', 'infosquad');

$secreto = __DIR__ . '/secreto.php';
if (!is_file($secreto)) {
    http_response_code(500);
    exit('Falta el archivo config/secreto.php (copiá config/secreto.ejemplo.php y poné tus datos)');
}
require_once $secreto;   // define DB_USER y DB_PASS 

// Puerto de MySQL/MariaDB: 3306 salvo que secreto.php diga otro.
defined('DB_PORT') || define('DB_PORT', 3306);

define('RAIZ', dirname(__DIR__));

// Autoload: las clases se buscan solo en negocio/ y datos/.
spl_autoload_register(function (string $clase): void {
    foreach (['negocio', 'datos'] as $capa) {
        $ruta = RAIZ . "/$capa/$clase.php";
        if (is_file($ruta)) {
            require_once $ruta;
            return;
        }
    }
});
