# SGRSI – Sistema de Gestión de Recursos y Soporte Informático

Proyecto de Bachillerato Tecnológico – Primera entrega (Junio 2026)

## ¿Qué es?

SGRSI es un sistema web para gestionar los recursos informáticos y el soporte técnico de una institución educativa. Permite registrar equipos, usuarios, préstamos y tickets de soporte, con autenticación por sesión, permisos según el rol del usuario e interfaz en español e inglés.

Es una aplicación PHP renderizada en el servidor: cada página `.php` procesa la petición, consulta MySQL y devuelve el HTML ya armado. No hay una API separada ni JavaScript que pida datos al servidor.

## Requisitos

- **XAMPP** con Apache, PHP 8.0 o superior y MySQL/MariaDB
- Navegador moderno (Chrome, Firefox, Edge)

## Instalación

1. Copiá la carpeta del proyecto dentro de `htdocs` de XAMPP (por ejemplo `C:\xampp\htdocs\proyectorevision\`).
2. Iniciá **Apache** y **MySQL** desde el panel de control de XAMPP.
3. Abrí `http://localhost/phpmyadmin`, entrá a la pestaña **Importar** y elegí `sql/BD_SGRSI.sql`. El script borra y vuelve a crear la base `BD_SGRSI`, sus tablas y los datos de prueba, así que no hace falta crearla antes.
4. Creá un usuario de MySQL propio para la aplicación (NO uses `root`). En phpMyAdmin, pestaña **SQL**, ejecutá (cambiá la clave por una tuya, larga):
   ```sql
   CREATE USER 'sgrsi_app'@'localhost' IDENTIFIED BY 'TU_CLAVE_LARGA';
   GRANT SELECT, INSERT, UPDATE, DELETE ON BD_SGRSI.* TO 'sgrsi_app'@'localhost';
   ```
   Luego copiá `config/secreto.ejemplo.php` como `config/secreto.php` y poné ahí el mismo usuario y clave (y `DB_PORT` si tu MySQL no usa el puerto 3306). Ese archivo no se sube a GitHub.
   Además, en `C:\xampp\mysql\bin\my.ini` agregá `bind-address=127.0.0.1` bajo `[mysqld]` y reiniciá MySQL, para que solo se pueda entrar desde esta computadora.
5. Configurá Apache para que el navegador solo pueda llegar a la carpeta `public/`. Abrí `C:\xampp\apache\conf\extra\httpd-vhosts.conf`, agregá al final este bloque (ajustá la ruta si tu carpeta tiene otro nombre) y reiniciá Apache desde el panel de XAMPP:
   ```apache
   <VirtualHost *:80>
       ServerName localhost
       DocumentRoot "C:/xampp/htdocs/proyectorevision/public"
       <Directory "C:/xampp/htdocs/proyectorevision/public">
           Require all granted
       </Directory>
   </VirtualHost>
   ```
   Así `config/`, `negocio/`, `datos/` y `sql/` quedan fuera del alcance del navegador (no hace falta `.htaccess`).
   Ojo: con esta configuración `localhost` apunta solo a SGRSI, así que otros proyectos de `htdocs` dejan de abrirse con `http://localhost/<carpeta>`. phpMyAdmin sigue en `http://localhost/phpmyadmin`, porque XAMPP lo define aparte.
6. Abrí `http://localhost/login.php` en el navegador. Las demás páginas también van directo después de `localhost/` (por ejemplo `http://localhost/prestamos.php`); la dirección vieja `http://localhost/<carpeta>/public/...` ahora da **Not Found**.

### Usuarios de prueba

| Rol | Email | Contraseña |
|-----|-------|------------|
| Administrador | juan.perez@utu.edu.uy | Perez123! |
| Técnico | ana.martinez@utu.edu.uy | Marti234! |
| Solicitante | maria.garcia@utu.edu.uy | Garcia456! |

Los usuarios creados desde la pantalla de usuarios reciben una clave inicial aleatoria que se muestra una sola vez en pantalla.

Si el login rechaza un usuario de prueba, importá `sql/reparar_passwords.sql` desde phpMyAdmin: regenera los hashes de los 10 usuarios de prueba.

## Módulos

| Página | Descripción | Tablas |
|--------|-------------|--------|
| `login.php` | Inicio de sesión (correo y contraseña) | `usuario` |
| `logout.php` | Cierra la sesión y vuelve al login | — |
| `index.php` | Inicio con acceso a los módulos según el rol | — |
| `recursos.php` | Inventario: lista y alta de equipos | `equipo` |
| `usuarios.php` | Alta y baja lógica de usuarios, con validación de la cédula | `usuario` |
| `migrar.php` | Se usa una sola vez en bases ya instaladas: agrega `usuario.activo` (baja lógica) y verifica las claves foráneas hacia `usuario`. Tiene su propio ingreso de Administrador | `usuario`, `prestamo`, `ticket`, `servicio` |
| `prestamos.php` | Registro y devolución de préstamos | `prestamo`, `equipo`, `usuario` |
| `tickets.php` | Mesa de ayuda: abrir y cerrar tickets | `ticket`, `usuario` |
| `reportes.php` | Contadores de equipos, tickets y préstamos por estado | `equipo`, `ticket`, `prestamo` |
| `historial.php` | Historial que une equipo, ticket y servicio | `historial`, `equipo`, `ticket`, `servicio` |

## Roles y permisos

`SessionAuth::normalizeRole()` convierte el rol guardado en la base a uno de tres perfiles. Cualquier rol que no sea administrador ni técnico (por ejemplo `Docente`) se trata como Solicitante.

| Perfil | Páginas | Edita inventario | Administra usuarios | Cierra tickets |
|--------|---------|------------------|---------------------|----------------|
| Administrador | Todas | Sí | Sí | Sí |
| Técnico | Todas menos usuarios | No | No | Sí |
| Solicitante | Inicio, préstamos y tickets | No | No | No |

## Stack tecnológico

- **Lenguaje:** PHP 8+ (tipado estricto, clases en `negocio/` y `datos/`)
- **Base de datos:** MySQL con MySQLi y consultas preparadas
- **Autenticación:** sesiones PHP y contraseñas con bcrypt (`password_hash` / `password_verify`)
- **Idioma:** español e inglés (clase `Translator` en `public/includes/Language.php`, funciones `t()` y `tv()`)
- **Estilos:** `public/CSS/styles.css` y `public/CSS/login.css`

## Arquitectura en tres capas

```
Navegador ──> public/*.php ──> negocio/ ──> datos/ ──> MySQL (BD_SGRSI)
              (presentación)   (reglas)     (SQL)
```

Cada capa solo le habla a la siguiente: las páginas de `public/` usan clases de `negocio/` y de `datos/`; `negocio/` usa `datos/`; `datos/` es la única que escribe SQL. Ni `negocio/` ni `datos/` conocen HTML, `$_GET` ni `$_POST` (salvo la sesión en `SessionAuth`).

| Capa | Carpeta | Qué hay |
|------|---------|---------|
| Presentación | `public/` | Páginas PHP (HTML + lectura de `$_POST`/`$_GET`), `CSS/`, `js/`, `images/`, y `includes/` con el traductor (`t()`, `tv()`) y `bootstrap.php` |
| Negocio | `negocio/` | `Usuario` (validación de cédula), `Equipo`, `Prestamo`, `Ticket`, `PrestamoServicio` (prestar/devolver cambia el estado del equipo), `SessionAuth` (login, roles y permisos) |
| Datos | `datos/` | `Conexion` (MySQLi) y un repositorio por tabla: `UsuarioRepository`, `EquipoRepository`, `PrestamoRepository`, `TicketRepository`, `HistorialRepository` |
| Config | `config/` | `config.php`: ajustes generales y autoload de las clases; `secreto.php`: usuario y clave de MySQL (no se sube a GitHub) |

Las páginas siguen el mismo patrón: cargan `includes/bootstrap.php` (que define la constante `ENTRYPOINT`), exigen sesión y permiso (`SessionAuth`), procesan el formulario creando un objeto de negocio y guardándolo con su repositorio, y dibujan el HTML.

## Estructura del proyecto

```
proyectorevision/
├── public/
│   ├── index.php, login.php, logout.php
│   ├── recursos.php, usuarios.php, prestamos.php
│   ├── tickets.php, reportes.php, historial.php
│   ├── generar_hash.php        # herramienta de desarrollo (borrar al entregar)
│   ├── includes/               # bootstrap.php, Language.php (t() y tv())
│   ├── CSS/                    # styles.css, login.css
│   ├── js/                     # Bootstrap 5 local (las páginas actuales no lo enlazan)
│   └── images/
├── negocio/
│   ├── Usuario.php, Equipo.php, Prestamo.php, Ticket.php
│   ├── PrestamoServicio.php
│   └── SessionAuth.php
├── datos/
│   ├── Conexion.php
│   └── UsuarioRepository.php, EquipoRepository.php, PrestamoRepository.php,
│       TicketRepository.php, HistorialRepository.php
├── config/
│   ├── config.php
│   ├── secreto.php             # credenciales (no se sube a GitHub)
│   └── secreto.ejemplo.php     # plantilla para crear secreto.php
├── sql/                        # BD_SGRSI.sql, reparar_passwords.sql
├── app/                        # versión anterior de algunas clases (las páginas no la usan)
└── jairo/                      # Proyecto SQL Server alternativo
```

## Base de datos

`BD_SGRSI` tiene seis tablas:

| Tabla | Clave primaria | Claves foráneas |
|-------|----------------|-----------------|
| `usuario` | `ci_usuario` (cédula, sin auto_increment); `activo` marca la baja lógica | — |
| `equipo` | `id_equipo` | — |
| `prestamo` | `id_prestamo` | `ci_solicitante`, `id_equipo` |
| `ticket` | `id_ticket` | `ci_solicitante` |
| `servicio` | `id_servicio` | `ci_usuario` |
| `historial` | `id_historial` | `id_equipo`, `id_ticket`, `id_servicio` |

## Restricciones de negocio

- La cédula se valida con su dígito verificador (8 dígitos) antes de dar de alta un usuario.
- No se pueden registrar dos usuarios con el mismo correo (`UNIQUE` en la base).
- La baja de usuarios es **lógica** (RF-06 / RN-10): "Dar de baja" pone `usuario.activo = 0`. El usuario no puede iniciar sesión, deja de aparecer en las listas y, si tenía la sesión abierta, se le cierra. Sus préstamos, tickets y servicios se conservan con su nombre (marcado "dado de baja"), y las claves foráneas mantienen la integridad referencial. Un administrador no puede darse de baja a sí mismo. No hay función de reactivación en esta versión; la cédula y el correo de un usuario dado de baja no se pueden reutilizar.
- Las contraseñas se guardan con hash bcrypt, nunca en texto plano.
- El formulario de préstamos solo ofrece equipos con estado `Disponible`; al prestar, el equipo pasa a `Prestado`, y al devolverlo vuelve a `Disponible`.
- Solo Administrador y Técnico pueden cerrar tickets y registrar devoluciones; solo Administrador gestiona usuarios.
- Un Solicitante solo ve sus propios préstamos y tickets, y los registra siempre a su nombre; el personal (Administrador y Técnico) ve todos y elige el solicitante.
- La fecha de devolución de un préstamo no puede ser anterior a hoy (hora de Uruguay).

## Seguridad

- **Solo `public/` es accesible desde el navegador**, gracias al `DocumentRoot` de Apache (paso 5 de la instalación).
- **Segunda barrera:** cada archivo de `config/`, `negocio/`, `datos/` e `includes/` empieza con `defined('ENTRYPOINT') || (http_response_code(404) && exit);`. Si alguien lo abre directo, responde 404; solo funciona cuando lo carga `bootstrap.php`.
- **Consultas preparadas** (`?` + `bind_param`) en `datos/Conexion.php`, contra inyección SQL.
- **Contraseñas con bcrypt** (`password_hash` / `password_verify`); en la sesión nunca se guarda el hash.
- **Login:** token CSRF, bloqueo de 10 minutos tras 5 intentos fallidos, espera de 0,4 s por intento fallido y `session_regenerate_id` al entrar. La cookie de sesión es `httponly` y `samesite=Lax`.
- **CSRF:** todos los formularios (incluido el login) llevan un token oculto (`csrf_campo()` / `csrf_valido()`).
- **F5 seguro:** después de cada formulario la página redirige (POST → redirección → GET) y muestra el mensaje una sola vez, así recargar no duplica tickets ni préstamos.
- **XSS:** todo lo que se muestra en pantalla pasa por `e()` (`htmlspecialchars`).
- **Errores:** no se muestran en pantalla (`display_errors = 0`). Si la base no responde, se muestra una página de error clara y el detalle queda en `C:\xampp\apache\logs\error.log`.

## Pendientes conocidos

- En una base instalada antes de la baja lógica, abrir una vez `migrar.php`, ingresar con un Administrador y apretar **Aplicar** (en la PC y en el servidor). Hasta hacerlo, el sistema muestra un aviso que lleva a esa página. El usuario de MySQL necesita permisos `ALTER` y `REFERENCES`. Después se puede borrar `public/migrar.php`.
- Borrar `public/generar_hash.php` y los usuarios de prueba antes de publicar.
- Usar en `config/secreto.php` un usuario propio de MySQL en lugar de `root` (paso 4 de la instalación).
- Si aparece la página "No se pudo trabajar con la base de datos", el motivo exacto está en `C:\xampp\apache\logs\error.log`. Si pasa después de cambiar el esquema, volvé a importar `sql/BD_SGRSI.sql`.
- La carpeta `app/` es una versión anterior que las páginas no usan; se puede borrar.

## Equipo de desarrollo

Completar con los nombres del equipo.
