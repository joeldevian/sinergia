-- Crear base y usarla
CREATE DATABASE IF NOT EXISTS `sinergia` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sinergia`;

-- Tabla users
CREATE TABLE `users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `rol` ENUM('admin','docente','estudiante') NOT NULL DEFAULT 'estudiante',
  `estado` ENUM('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla carreras
CREATE TABLE `carreras` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre_carrera` VARCHAR(150) NOT NULL,
  `duracion_periodos` SMALLINT NOT NULL,
  `estado` ENUM('activa','inactiva') NOT NULL DEFAULT 'activa',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla cursos
CREATE TABLE `cursos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigo_curso` VARCHAR(50) NOT NULL,
  `nombre_curso` VARCHAR(200) NOT NULL,
  `creditos` TINYINT NOT NULL,
  `horas_semanales` TINYINT NOT NULL,
  `id_carrera` INT NOT NULL,
  `ciclo` VARCHAR(50) NULL,
  `tipo` ENUM('obligatorio','electivo') NOT NULL DEFAULT 'obligatorio',
  `estado` ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cursos_codigo` (`codigo_curso`),
  KEY `idx_cursos_id_carrera` (`id_carrera`),
  CONSTRAINT `fk_cursos_carreras` FOREIGN KEY (`id_carrera`) REFERENCES `carreras` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla docentes
CREATE TABLE `docentes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigo_docente` VARCHAR(50) NOT NULL,
  `dni` VARCHAR(20) NOT NULL,
  `apellido_paterno` VARCHAR(100) NOT NULL,
  `apellido_materno` VARCHAR(100),
  `nombres` VARCHAR(150) NOT NULL,
  `especialidad` VARCHAR(150),
  `telefono` VARCHAR(50),
  `email` VARCHAR(150),
  `id_user` INT DEFAULT NULL,
  `estado` ENUM('activo','inactivo','suspendido') NOT NULL DEFAULT 'activo',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_docentes_codigo` (`codigo_docente`),
  UNIQUE KEY `uq_docentes_dni` (`dni`),
  UNIQUE KEY `uq_docentes_email` (`email`),
  KEY `idx_docentes_id_user` (`id_user`),
  CONSTRAINT `fk_docentes_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla estudiantes
CREATE TABLE `estudiantes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `codigo_estudiante` VARCHAR(50) NOT NULL,
  `dni` VARCHAR(20) NOT NULL,
  `apellido_paterno` VARCHAR(100) NOT NULL,
  `apellido_materno` VARCHAR(100),
  `nombres` VARCHAR(150) NOT NULL,
  `fecha_nacimiento` DATE,
  `sexo` ENUM('M','F','O') DEFAULT 'O',
  `direccion` VARCHAR(255),
  `telefono` VARCHAR(50),
  `email` VARCHAR(150),
  `id_user` INT DEFAULT NULL,
  `estado` ENUM('activo','inactivo','egresado','desertor') NOT NULL DEFAULT 'activo',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_estudiantes_codigo` (`codigo_estudiante`),
  UNIQUE KEY `uq_estudiantes_dni` (`dni`),
  UNIQUE KEY `uq_estudiantes_email` (`email`),
  KEY `idx_estudiantes_id_user` (`id_user`),
  CONSTRAINT `fk_estudiantes_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla matriculas
CREATE TABLE `matriculas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_estudiante` INT NOT NULL,
  `id_curso` INT NOT NULL,
  `periodo_academico` VARCHAR(50) NOT NULL,
  `fecha_matricula` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` ENUM('matriculado','retirado','aprobado','aplazado') NOT NULL DEFAULT 'matriculado',
  PRIMARY KEY (`id`),
  KEY `idx_matriculas_estudiante` (`id_estudiante`),
  KEY `idx_matriculas_curso` (`id_curso`),
  CONSTRAINT `fk_matriculas_estudiante` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_matriculas_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla evaluaciones
CREATE TABLE `evaluaciones` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_curso` INT NOT NULL,
  `nombre_evaluacion` VARCHAR(150) NOT NULL,
  `porcentaje` DECIMAL(5,2) NOT NULL,
  `estado` ENUM('programada','realizada','anulada') NOT NULL DEFAULT 'programada',
  `fecha_programada` DATE DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_evaluaciones_curso` (`id_curso`),
  CONSTRAINT `fk_evaluaciones_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla notas
CREATE TABLE `notas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_estudiante` INT NOT NULL,
  `id_evaluacion` INT NOT NULL,
  `nota` DECIMAL(5,2) NOT NULL,
  `fecha_registro` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `registrado_por` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notas_estudiante` (`id_estudiante`),
  KEY `idx_notas_evaluacion` (`id_evaluacion`),
  CONSTRAINT `fk_notas_estudiante` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_notas_evaluacion` FOREIGN KEY (`id_evaluacion`) REFERENCES `evaluaciones` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_notas_registrado_por` FOREIGN KEY (`registrado_por`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla asistencia
CREATE TABLE `asistencia` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_estudiante` INT NOT NULL,
  `id_curso` INT NOT NULL,
  `fecha` DATE NOT NULL,
  `estado` ENUM('asistio','falto','tardanza') NOT NULL DEFAULT 'asistio',
  `registrado_por` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_asistencia_estudiante` (`id_estudiante`),
  KEY `idx_asistencia_curso` (`id_curso`),
  CONSTRAINT `fk_asistencia_estudiante` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_asistencia_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_asistencia_registrado_por` FOREIGN KEY (`registrado_por`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla pensiones
CREATE TABLE `pensiones` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_estudiante` INT NOT NULL,
  `periodo_academico` VARCHAR(50) NOT NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `fecha_vencimiento` DATE NOT NULL,
  `estado` ENUM('pendiente','pagado','vencido') NOT NULL DEFAULT 'pendiente',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pensiones_estudiante` (`id_estudiante`),
  CONSTRAINT `fk_pensiones_estudiante` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla pagos
CREATE TABLE `pagos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_pension` INT NOT NULL,
  `monto_pagado` DECIMAL(10,2) NOT NULL,
  `fecha_pago` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `metodo_pago` VARCHAR(100) NOT NULL,
  `referencia` VARCHAR(200),
  `registrado_por` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pagos_id_pension` (`id_pension`),
  CONSTRAINT `fk_pagos_pension` FOREIGN KEY (`id_pension`) REFERENCES `pensiones` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_pagos_registrado_por` FOREIGN KEY (`registrado_por`) REFERENCES `users` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
