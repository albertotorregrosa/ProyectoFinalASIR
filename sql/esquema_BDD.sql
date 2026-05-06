-- =====================================================
-- Base de datos: gira_db
-- Script de creación de estructura (Esquema Limpio)
-- =====================================================

-- =====================================================
-- 1) TABLA: usuarios
-- =====================================================
CREATE TABLE `usuarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ldap_uid` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellidos` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `estado` enum('ACTIVO','BLOQUEADO') NOT NULL DEFAULT 'ACTIVO',
  `rol` enum('ADMIN','PROFESOR','ALUMNO') NOT NULL DEFAULT 'ALUMNO',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuarios_ldap_uid` (`ldap_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 2) TABLA: aulas
-- =====================================================
CREATE TABLE `aulas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('AULA','LAB','TALLER','OTRA') NOT NULL DEFAULT 'AULA',
  `capacidad` smallint(5) unsigned DEFAULT NULL,
  `ubicacion` varchar(120) DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_aulas_codigo` (`codigo`),
  KEY `idx_aulas_activa` (`activa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 3) TABLA: dispositivos
-- =====================================================
CREATE TABLE `dispositivos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('PORTATIL','TABLET','PROYECTOR','CAMARA','OTRO') NOT NULL DEFAULT 'OTRO',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_dispositivos_codigo` (`codigo`),
  KEY `idx_dispositivos_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 4) TABLA: sesiones
-- =====================================================
CREATE TABLE `sesiones` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `token` char(64) NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `expira_en` datetime NOT NULL,
  `revocada_en` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sesiones_token` (`token`),
  KEY `idx_sesiones_usuario_id` (`usuario_id`),
  CONSTRAINT `fk_sesiones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 5) TABLA: recuperacion_claves
-- =====================================================
CREATE TABLE `recuperacion_claves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `token` varchar(64) NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `recuperacion_claves_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 6) TABLA: reservas
-- =====================================================
CREATE TABLE `reservas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `aula_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `inicio` datetime NOT NULL,
  `fin` datetime NOT NULL,
  `estado` enum('CONFIRMADA','CANCELADA') NOT NULL DEFAULT 'CONFIRMADA',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reservas_aula_inicio_fin` (`aula_id`,`inicio`,`fin`),
  KEY `idx_reservas_usuario` (`usuario_id`),
  CONSTRAINT `fk_reservas_aula` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`),
  CONSTRAINT `fk_reservas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 7) TABLA: reservas_dispositivos
-- =====================================================
CREATE TABLE `reservas_dispositivos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dispositivo_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `inicio` datetime NOT NULL,
  `fin` datetime NOT NULL,
  `estado` enum('CONFIRMADA','CANCELADA') NOT NULL DEFAULT 'CONFIRMADA',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rd_disp_inicio_fin` (`dispositivo_id`,`inicio`,`fin`),
  KEY `idx_rd_usuario` (`usuario_id`),
  CONSTRAINT `fk_rd_dispositivo` FOREIGN KEY (`dispositivo_id`) REFERENCES `dispositivos` (`id`),
  CONSTRAINT `fk_rd_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 8) TABLA: incidencias_aula
-- =====================================================
CREATE TABLE `incidencias_aula` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `aula_id` int(10) unsigned NOT NULL,
  `reportada_por` int(10) unsigned NOT NULL,
  `titulo` varchar(120) NOT NULL,
  `descripcion` text NOT NULL,
  `prioridad` enum('BAJA','MEDIA','ALTA','URGENTE') NOT NULL DEFAULT 'MEDIA',
  `estado` enum('ABIERTA','EN_PROCESO','RESUELTA','CERRADA') NOT NULL DEFAULT 'ABIERTA',
  `asignada_a` int(10) unsigned DEFAULT NULL,
  `resuelta_en` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inc_aula_estado` (`aula_id`,`estado`),
  KEY `idx_inc_reportada_por` (`reportada_por`),
  KEY `idx_inc_asignada_a` (`asignada_a`),
  CONSTRAINT `fk_inc_asignada_a` FOREIGN KEY (`asignada_a`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_inc_aula` FOREIGN KEY (`aula_id`) REFERENCES `aulas` (`id`),
  CONSTRAINT `fk_inc_reportada_por` FOREIGN KEY (`reportada_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
