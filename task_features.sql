-- task_features.sql

-- Tabla para almacenar las tareas asignadas a los cursos
CREATE TABLE `tareas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_asignacion` INT NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `descripcion` TEXT,
  `fecha_publicacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_entrega` DATETIME NOT NULL,
  `tipo_entrega` ENUM('archivo', 'texto') NOT NULL DEFAULT 'archivo', -- Si la entrega es un archivo o un texto
  `estado` ENUM('activa', 'inactiva', 'finalizada') NOT NULL DEFAULT 'activa',
  PRIMARY KEY (`id`),
  KEY `id_asignacion` (`id_asignacion`),
  CONSTRAINT `fk_tareas_asignacion` FOREIGN KEY (`id_asignacion`) REFERENCES `docente_curso` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla para almacenar las entregas de los estudiantes para cada tarea
CREATE TABLE `entregas_tarea` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_tarea` INT NOT NULL,
  `id_estudiante` INT NOT NULL,
  `ruta_archivo` VARCHAR(255), -- Ruta del archivo si tipo_entrega es 'archivo'
  `texto_entrega` TEXT,         -- Texto de la entrega si tipo_entrega es 'texto'
  `fecha_entrega` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `calificacion` DECIMAL(5,2) DEFAULT NULL,
  `comentarios_docente` TEXT,
  `estado` ENUM('pendiente', 'entregado', 'calificado', 'retrasado') NOT NULL DEFAULT 'entregado',
  PRIMARY KEY (`id`),
  KEY `id_tarea` (`id_tarea`),
  KEY `id_estudiante` (`id_estudiante`),
  CONSTRAINT `fk_entregas_tarea` FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_entregas_estudiante` FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
