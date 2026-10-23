-- ============================================================
-- BD_SGRSI.sql  -  Base de datos del sistema SGRSI
-- Las tablas coinciden con lo que usa el codigo PHP (datos/*.php).
-- ============================================================
drop DATABASE IF EXISTS infosquad;
CREATE DATABASE infosquad CHARACTER SET utf8mb4;

use infosquad;

-- ci_usuario es la CEDULA (8 digitos, sin puntos ni guion).
-- activo: baja LOGICA (RF-06 / RN-10). Dar de baja a un usuario pone activo = 0:
-- no puede iniciar sesion y deja de aparecer en las listas, pero el registro se
-- conserva para no romper la integridad referencial con prestamos, tickets y servicios.
create table usuario(
    ci_usuario int primary key,
    nom varchar(20) not null,
    ape varchar(20) not null,
    email varchar(80) not null unique,
    contrasena varchar(255) not null,
    rol varchar(20) not null,
    activo tinyint(1) not null default 1,
    -- debe_cambiar_clave = 1: el usuario recien creado por el Administrador tiene que
    -- cambiar la clave inicial la primera vez que entra (no puede usar el sistema antes).
    debe_cambiar_clave tinyint(1) not null default 0
);

create table servicio(
    id_servicio int primary key auto_increment,
    fechaInServicio varchar(20) not null,
    descripcionServicio varchar(100) not null,
    fechaFinServicio varchar(20) not null,
    ci_usuario int not null,
    foreign key (ci_usuario) references usuario(ci_usuario)
);

create table equipo(
    id_equipo int primary key auto_increment,
    estado varchar(20) not null comment 'Disponible, Prestado, En reparacion',
    numeroSerie varchar(20) not null unique,
    modelo varchar(20) not null,
    marca varchar(20) not null,
    tipo varchar(20) not null
);

create table prestamo(
    id_prestamo int primary key auto_increment,
    fechaCreacion datetime not null default current_timestamp,
    fechaFinPrevista date not null,
    fechaDevolucion datetime null,
    observacion varchar(100) not null default '',
    estado varchar(20) not null default 'Entregado',
    ci_solicitante int not null,
    id_equipo int not null,
    foreign key (ci_solicitante) references usuario(ci_usuario),
    foreign key (id_equipo) references equipo(id_equipo)
);

create table ticket(
    id_ticket int primary key auto_increment,
    fechaCreacion datetime not null default current_timestamp,
    fechaFin datetime null,
    titulo varchar(60) not null,
    descripcion varchar(100) not null,
    prioridad varchar(20) not null,
    estado varchar(20) not null default 'Abierto',
    ci_solicitante int not null,
    foreign key (ci_solicitante) references usuario(ci_usuario)
);

create table historial(
    id_historial int primary key auto_increment,
    descripcion varchar(100) not null,
    accion varchar(100) not null,
    id_equipo int not null,
    id_ticket int not null,
    id_servicio int not null,
    foreign key (id_equipo)   references equipo(id_equipo),
    foreign key (id_ticket)   references ticket(id_ticket),
    foreign key (id_servicio) references servicio(id_servicio)
);

-- ============================================================
-- DATOS DE PRUEBA (las contrasenas de prueba estan en el Readme)
-- ============================================================
INSERT INTO usuario (ci_usuario, nom, ape, email, contrasena, rol) VALUES
(38147258, 'Juan', 'Perez', 'juan.perez@utu.edu.uy', '$2y$12$1c627uRL/EkCrJQVXMNof.KhuU5eLxIRWkjle7YWKvjO45AXPSI3m', 'Administrador'),
(41920637, 'Maria', 'Garcia', 'maria.garcia@utu.edu.uy', '$2y$12$mOobRFE.DyAThyc4q8ceqeHbLQUo.Teycd01ggs7koUjq5oHvrFgG', 'Solicitante'),
(45308174, 'Carlos', 'Rodriguez', 'carlos.rodriguez@utu.edu.uy', '$2y$12$UGiHHOGD5rS82D9ttPb2duVW.6lcJzoaF/QQTCvKmOJflFHWwzCcC', 'Solicitante'),
(39672418, 'Ana', 'Martinez', 'ana.martinez@utu.edu.uy', '$2y$12$w4xZNlslv.tDhuomvX9SUOZK8yeYAhCKIFDAk37sjozwx2vodTcrq', 'Tecnico'),
(50283947, 'Lucia', 'Fernandez', 'lucia.fernandez@utu.edu.uy', '$2y$12$Z1rmHvUoGUp1YBHoe/UjXObMGgbv0l3DmFXq6f/u48JzPNTxk46Sa', 'Solicitante'),
(46715083, 'Pedro', 'Silva', 'pedro.silva@utu.edu.uy', '$2y$12$KeHepYIaE2sK60U9xKRgKu11sMWdo6bCgd2FN./fTpfPyrKWQzw2S', 'Solicitante'),
(51839262, 'Sofia', 'Lopez', 'sofia.lopez@utu.edu.uy', '$2y$12$5OypuUcBFxoDkmgm1I2jPenNmuddVwvD3RZMRE/UFMNHHEPUPsxGO', 'Solicitante'),
(37456105, 'Diego', 'Torres', 'diego.torres@utu.edu.uy', '$2y$12$Cg1amuTm6HPuSm3uWp4ozO7.Ao.z48vq4PcApHQ2b2q.h5HWFuQyq', 'Administrador'),
(49082730, 'Martin', 'Gonzalez', 'martin.gonzalez@utu.edu.uy', '$2y$12$H6FY1olj3nb2JxNAVQOXr.2NKNTC9p6r0tg9o4zprUe3HRt//dfmO', 'Solicitante'),
(52610499, 'Valeria', 'Castro', 'valeria.castro@utu.edu.uy', '$2y$12$xkTT4M8saJGlcOHYI1e0MOdOJ9tohN6i1vDITtzSJ.5G.rnvilJI6', 'Tecnico');


INSERT INTO equipo (id_equipo, estado, numeroSerie, modelo, marca, tipo) VALUES
(1, 'Disponible', 'SN-001-ABC', 'ProDesk 400', 'HP', 'PC'),
(2, 'Prestado', 'SN-002-DEF', 'ThinkPad X1', 'Lenovo', 'Laptop'),
(3, 'Disponible', 'SN-003-GHI', 'LS24R350', 'Samsung', 'Monitor'),
(4, 'En reparacion', 'SN-004-JKL', 'OptiPlex 3080', 'Dell', 'PC'),
(5, 'Disponible', 'SN-005-MNO', 'Pavilion 14', 'HP', 'Laptop'),
(6, 'Prestado', 'SN-006-PQR', 'PowerLite 1785W', 'Epson', 'Proyector'),
(7, 'Disponible', 'SN-007-STU', 'K120', 'Logitech', 'Otro'),
(8, 'Disponible', 'SN-008-VWX', 'M720', 'Logitech', 'Otro');

INSERT INTO servicio (id_servicio, fechaInServicio, descripcionServicio, fechaFinServicio, ci_usuario) VALUES
(1, '2026-02-10', 'Mantenimiento preventivo de equipos informaticos', '2026-02-14', 39672418),
(2, '2026-03-01', 'Actualizacion de software en laboratorio 3', '2026-03-05', 52610499),
(3, '2026-03-15', 'Configuracion de red en aula virtual', '2026-03-18', 39672418),
(4, '2026-04-01', 'Instalacion de proyectores en salones', '2026-04-08', 52610499),
(5, '2026-04-20', 'Respaldo de datos de administracion', '2026-04-22', 39672418);

INSERT INTO ticket (id_ticket, fechaCreacion, fechaFin, titulo, descripcion, ci_solicitante, prioridad, estado) VALUES
(1, '2026-02-12', '2026-02-13', 'Pantalla azul', 'Pantalla azul al iniciar sesion en equipo del laboratorio', 45308174, 'Alta', 'Cerrado'),
(2, '2026-03-02', '2026-03-03', 'Mouse roto', 'No funciona el mouse del gabinete de informatica', 50283947, 'Media', 'Cerrado'),
(3, '2026-03-10', '2026-03-11', 'Proyector no enciende', 'Proyector no enciende en el salon 203', 41920637, 'Alta', 'Cerrado'),
(4, '2026-03-20', '2026-03-22', 'Sin WiFi', 'No se conecta a la red WiFi institucional', 51839262, 'Media', 'Cerrado'),
(5, '2026-04-05', NULL, 'Impresora sin respuesta', 'Impresora no responde en sala de docentes', 46715083, 'Baja', 'Abierto'),
(6, '2026-04-15', '2026-04-16', 'Teclado trabado', 'Teclado con teclas trabadas en equipo del estudiante', 49082730, 'Media', 'Cerrado'),
(7, '2026-05-01', NULL, 'Falla electrica', 'Fallo en el suministro electrico del laboratorio 1', 37456105, 'Alta', 'Abierto');

INSERT INTO prestamo (id_prestamo, fechaCreacion, fechaFinPrevista, fechaDevolucion, observacion, estado, ci_solicitante, id_equipo) VALUES
(1, '2026-02-20', '2026-02-27', '2026-02-27', 'Prestamo de laptop para trabajo en clase', 'Devuelto', 41920637, 2),
(2, '2026-03-05', '2026-03-12', NULL, 'Proyector para exposicion de proyectos', 'Entregado', 46715083, 6),
(3, '2026-03-25', '2026-03-28', '2026-03-28', 'Equipo para taller de programacion', 'Devuelto', 45308174, 5),
(4, '2026-04-10', '2026-04-17', '2026-04-17', 'Monitor para practica de diseno grafico', 'Devuelto', 51839262, 3),
(5, '2026-05-05', '2026-05-12', NULL, 'Laptop para curso de capacitacion docente', 'Entregado', 41920637, 2);

INSERT INTO historial (id_historial, descripcion, accion, id_equipo, id_ticket, id_servicio) VALUES
(1, 'Se reinstalo sistema operativo en equipo del laboratorio', 'Reparacion', 1, 1, 1),
(2, 'Se reemplazo mouse defectuoso en gabinete', 'Reemplazo', 8, 2, 2),
(3, 'Se reparo proyector del salon 203, lampara reemplazada', 'Reparacion', 6, 3, 4),
(4, 'Se configuro red WiFi en dispositivo del estudiante', 'Configuracion', 2, 4, 3),
(5, 'Se limpio y realizo mantenimiento preventivo a desktop', 'Mantenimiento', 4, 7, 5),
(6, 'Se reemplazo teclado con teclas trabadas', 'Reemplazo', 7, 6, 2);
