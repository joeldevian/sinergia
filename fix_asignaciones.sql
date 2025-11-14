-- Script para corregir las asignaciones de prueba de docentes a cursos.

USE `sinergia`;

-- Primero, eliminar las asignaciones de prueba anteriores para evitar duplicados.
DELETE FROM `docente_curso`;
ALTER TABLE `docente_curso` AUTO_INCREMENT = 1;

-- Volver a insertar las asignaciones usando subconsultas para asegurar los IDs correctos.
INSERT INTO `docente_curso` (`id_docente`, `id_curso`, `periodo_academico`) VALUES
((SELECT id from `docentes` WHERE `codigo_docente` = 'DOC001'), (SELECT id from `cursos` WHERE `codigo_curso` = 'DS-101'), '2025-II'),
((SELECT id from `docentes` WHERE `codigo_docente` = 'DOC001'), (SELECT id from `cursos` WHERE `codigo_curso` = 'DS-202'), '2025-II'),
((SELECT id from `docentes` WHERE `codigo_docente` = 'DOC002'), (SELECT id from `cursos` WHERE `codigo_curso` = 'DG-101'), '2025-II'),
((SELECT id from `docentes` WHERE `codigo_docente` = 'DOC002'), (SELECT id from `cursos` WHERE `codigo_curso` = 'DG-205'), '2025-II'),
((SELECT id from `docentes` WHERE `codigo_docente` = 'DOC003'), (SELECT id from `cursos` WHERE `codigo_curso` = 'MD-101'), '2025-II'),
((SELECT id from `docentes` WHERE `codigo_docente` = 'DOC003'), (SELECT id from `cursos` WHERE `codigo_curso` = 'MD-201'), '2025-II');

SELECT 'Asignaciones de prueba corregidas exitosamente.' AS `estado`;
