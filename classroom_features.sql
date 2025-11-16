-- classroom_features.sql

-- Tabla para almacenar recursos del curso (archivos, enlaces, etc.)
CREATE TABLE `recursos_curso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_asignacion` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_recurso` enum('archivo','enlace') NOT NULL,
  `ruta` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_asignacion` (`id_asignacion`),
  CONSTRAINT `recursos_curso_ibfk_1` FOREIGN KEY (`id_asignacion`) REFERENCES `docente_curso` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla para almacenar comunicaciones o anuncios del curso
CREATE TABLE `comunicaciones_curso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_asignacion` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_asignacion` (`id_asignacion`),
  CONSTRAINT `comunicaciones_curso_ibfk_1` FOREIGN KEY (`id_asignacion`) REFERENCES `docente_curso` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
