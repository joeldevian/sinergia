-- Script para añadir la tabla de asignación de docentes a cursos.
-- Este script es seguro de ejecutar, no borra datos existentes.

USE `sinergia`;

CREATE TABLE `docente_curso` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_docente` INT NOT NULL,
  `id_curso` INT NOT NULL,
  `periodo_academico` VARCHAR(50) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_docente_curso_id_docente` (`id_docente`),
  KEY `idx_docente_curso_id_curso` (`id_curso`),
  CONSTRAINT `fk_docente_curso_docente` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_docente_curso_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `uq_asignacion` (`id_docente`, `id_curso`, `periodo_academico`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Opcional: Insertar algunas asignaciones de prueba
INSERT INTO `docente_curso` (`id_docente`, `id_curso`, `periodo_academico`) VALUES
(1, 1, '2025-II'), -- Juan Perez -> Programación Orientada a Objetos
(1, 2, '2025-II'), -- Juan Perez -> Bases de Datos Relacionales
(2, 3, '2025-II'), -- Maria Gonzales -> Fundamentos del Diseño Gráfico
(2, 4, '2025-II'), -- Maria Gonzales -> Diseño de Interfaces (UI)
(3, 5, '2025-II'), -- Luis Torres -> Introducción al Marketing Digital
(3, 6, '2025-II'); -- Luis Torres -> SEO y SEM

SELECT 'Tabla docente_curso creada y datos de prueba insertados exitosamente.' AS `estado`;
