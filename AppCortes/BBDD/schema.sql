-- =============================================
-- AppCortes - Base de Datos MySQL
-- Importar en phpMyAdmin
-- =============================================

CREATE DATABASE IF NOT EXISTS `appcortes`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `appcortes`;

-- =============================================
-- TABLAS PRINCIPALES (sin dependencias)
-- =============================================

-- ---------------------------------------------
-- Tabla: roles (escalable - añade los que necesites)
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `id`          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`      VARCHAR(50)      NOT NULL UNIQUE,
  `descripcion` VARCHAR(255)              DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- Roles iniciales
INSERT INTO `roles` (`nombre`, `descripcion`) VALUES
  ('admin',     'Administrador con acceso total'),
  ('peluquero', 'Peluquero activo del salón');

-- ---------------------------------------------
-- Tabla: usuarios
-- FK: rol_id → roles(id)
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id`            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `nombre`        VARCHAR(100)     NOT NULL,
  `email`         VARCHAR(150)     NOT NULL UNIQUE,
  `password_hash` VARCHAR(255)     NOT NULL,
  `rol_id`        TINYINT UNSIGNED NOT NULL DEFAULT 2 COMMENT '1=admin, 2=peluquero',
  `activo`        TINYINT(1)       NOT NULL DEFAULT 1,
  `creado_en`     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_usuarios_rol`
    FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------
-- Tabla: clientes
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `clientes` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`        VARCHAR(100) NOT NULL,
  `email`         VARCHAR(150)          DEFAULT NULL,
  `telefono`      VARCHAR(20)           DEFAULT NULL,
  `ultima_visita` DATE                  DEFAULT NULL,
  `creado_en`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- ---------------------------------------------
-- Tabla: cortes (galería de estilos)
-- FK: peluquero_id → usuarios(id)
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `cortes` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`       VARCHAR(100) NOT NULL,
  `tags`         VARCHAR(255)          DEFAULT NULL,
  `imagen_path`  VARCHAR(500)          DEFAULT NULL,
  `peluquero_id` INT UNSIGNED          DEFAULT NULL COMMENT 'Peluquero que añadió el corte',
  `creado_en`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_cortes_peluquero`
    FOREIGN KEY (`peluquero_id`) REFERENCES `usuarios` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- TABLAS CON DEPENDENCIAS MÚLTIPLES
-- =============================================

-- ---------------------------------------------
-- Tabla: citas (agenda de reservas)
-- FK: cliente_id   → clientes(id)
-- FK: peluquero_id → usuarios(id)
-- FK: corte_id     → cortes(id)   (opcional)
-- ---------------------------------------------
CREATE TABLE IF NOT EXISTS `citas` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cliente_id`   INT UNSIGNED NOT NULL,
  `peluquero_id` INT UNSIGNED NOT NULL,
  `corte_id`     INT UNSIGNED          DEFAULT NULL COMMENT 'Estilo solicitado (opcional)',
  `fecha`        DATE         NOT NULL,
  `hora`         TIME         NOT NULL,
  `estado`       ENUM('pendiente','confirmada','en_curso','completada','cancelada')
                              NOT NULL DEFAULT 'pendiente',
  `notas`        TEXT                  DEFAULT NULL,
  `creado_en`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_citas_cliente`
    FOREIGN KEY (`cliente_id`)   REFERENCES `clientes` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_citas_peluquero`
    FOREIGN KEY (`peluquero_id`) REFERENCES `usuarios` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_citas_corte`
    FOREIGN KEY (`corte_id`)     REFERENCES `cortes` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- ÍNDICES de rendimiento
-- =============================================
CREATE INDEX `idx_citas_fecha`      ON `citas` (`fecha`);
CREATE INDEX `idx_citas_peluquero`  ON `citas` (`peluquero_id`);
CREATE INDEX `idx_citas_cliente`    ON `citas` (`cliente_id`);
CREATE INDEX `idx_citas_estado`     ON `citas` (`estado`);
CREATE INDEX `idx_clientes_nombre`  ON `clientes` (`nombre`);
CREATE INDEX `idx_usuarios_rol`     ON `usuarios` (`rol_id`);

-- =============================================
-- DATOS INICIALES
-- =============================================

-- Admin:  admin@appcortes.com / admin123
INSERT INTO `usuarios` (`nombre`, `email`, `password_hash`, `rol_id`, `activo`) VALUES
('Administrador', 'admin@appcortes.com',
 '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
 1, 1);

-- Peluqueros de ejemplo  (password: admin123)
INSERT INTO `usuarios` (`nombre`, `email`, `password_hash`, `rol_id`, `activo`) VALUES
('Carlos López',  'carlos@appcortes.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 1),
('María Sánchez', 'maria@appcortes.com',  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 1);

-- Clientes de ejemplo
INSERT INTO `clientes` (`nombre`, `email`, `telefono`, `ultima_visita`) VALUES
('Juan Pérez',  'juan@gmail.com',  '612345678', '2025-02-10'),
('Ana Gómez',   'ana@gmail.com',   '623456789', '2025-02-03'),
('Carlos Ruiz', 'carlos@gmail.com','634567890', '2025-01-20');

-- Cortes de ejemplo
INSERT INTO `cortes` (`nombre`, `tags`, `peluquero_id`) VALUES
('Corte Moderno',  '#degradado #hombres #estilo', 2),
('Corte Bob',      '#bob #mujer #elegante',        3),
('Color Fantasía', '#colorido #atrevido #fantasia',2);

-- Citas de ejemplo (Actualizadas para que salgan en la semana actual)
INSERT INTO `citas` (`cliente_id`, `peluquero_id`, `corte_id`, `fecha`, `hora`, `estado`, `notas`) VALUES
(1, 2, 1, '2026-05-18', '10:00:00', 'pendiente', 'Corte Clásico'),
(2, 3, 2, '2026-05-18', '11:30:00', 'confirmada', 'Corte + Barba'),
(3, 2, 3, '2026-05-19', '09:00:00', 'pendiente', 'Tinte / Color');
