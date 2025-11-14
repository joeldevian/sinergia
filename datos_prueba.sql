-- SCRIPT DE DATOS DE PRUEBA v4 - Definitivo

SET FOREIGN_KEY_CHECKS=0;
USE `sinergia`;

-- PASO 1: Eliminar datos existentes.
DELETE FROM `pagos`;
DELETE FROM `pensiones`;
DELETE FROM `asistencia`;
DELETE FROM `notas`;
DELETE FROM `evaluaciones`;
DELETE FROM `matriculas`;
DELETE FROM `cursos`;
DELETE FROM `carreras`;
DELETE FROM `docentes`;
DELETE FROM `estudiantes`;
DELETE FROM `users` WHERE `rol` != 'admin';

-- PASO 2: Resetear contadores de autoincremento para un estado limpio.
ALTER TABLE `pagos` AUTO_INCREMENT = 1;
ALTER TABLE `pensiones` AUTO_INCREMENT = 1;
ALTER TABLE `asistencia` AUTO_INCREMENT = 1;
ALTER TABLE `notas` AUTO_INCREMENT = 1;
ALTER TABLE `evaluaciones` AUTO_INCREMENT = 1;
ALTER TABLE `matriculas` AUTO_INCREMENT = 1;
ALTER TABLE `cursos` AUTO_INCREMENT = 1;
ALTER TABLE `carreras` AUTO_INCREMENT = 1;
ALTER TABLE `docentes` AUTO_INCREMENT = 1;
ALTER TABLE `estudiantes` AUTO_INCREMENT = 1;
-- No reseteamos la tabla 'users' para no afectar el ID del admin.

-- PASO 3: Insertar datos nuevos.

-- 3.1. Insertar Carreras
INSERT INTO `carreras` (`nombre_carrera`, `duracion_periodos`, `estado`) VALUES
('Desarrollo de Software', 6, 'activa'),
('Diseño Gráfico Digital', 6, 'activa'),
('Marketing Digital', 4, 'activa'),
('Contabilidad', 6, 'inactiva');

-- 3.2. Insertar Usuarios (Docentes y Estudiantes)
-- Contraseña para todos los usuarios de prueba: 'password123'
-- Hash: '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.'
INSERT INTO `users` (`username`, `password_hash`, `rol`) VALUES
('j.perez', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'docente'),
('m.gonzales', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'docente'),
('l.torres', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'docente'),
('c.rodriguez', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('a.martinez', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('f.gomez', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('s.diaz', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('v.sanchez', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('p.lopez', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('d.fernandez', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('m.romero', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('e.alvarez', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('r.torres', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('g.ruiz', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('l.ramirez', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('j.flores', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('b.acosta', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('n.benitez', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('i.sosa', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('h.rivera', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('o.medina', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('k.herrera', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('u.aguilar', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('y.morales', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('z.castillo', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('w.ortega', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('x.gimenez', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante'),
('d.vazquez', '$2y$10$9.G/MIg3a0A/15a.Bv2a.OpuJg0RzB.2.u.fS3gY8wz.LzF.j/Lz.', 'estudiante');

-- 3.3. Insertar Docentes
INSERT INTO `docentes` (`codigo_docente`, `dni`, `apellido_paterno`, `apellido_materno`, `nombres`, `especialidad`, `email`, `id_user`) VALUES
('DOC001', '10101010', 'Perez', 'Garcia', 'Juan', 'Bases de Datos', 'j.perez@sinergia.edu', (SELECT id FROM users WHERE username='j.perez')),
('DOC002', '20202020', 'Gonzales', 'Rojas', 'Maria', 'Diseño UI/UX', 'm.gonzales@sinergia.edu', (SELECT id FROM users WHERE username='m.gonzales')),
('DOC003', '30303030', 'Torres', 'Vega', 'Luis', 'Marketing y SEO', 'l.torres@sinergia.edu', (SELECT id FROM users WHERE username='l.torres'));

-- 3.4. Insertar Estudiantes
INSERT INTO `estudiantes` (`codigo_estudiante`, `dni`, `apellido_paterno`, `apellido_materno`, `nombres`, `email`, `id_user`) VALUES
('EST001', '70000001', 'Rodriguez', 'Solis', 'Carlos', 'c.rodriguez@sinergia.edu', (SELECT id FROM users WHERE username='c.rodriguez')),
('EST002', '70000002', 'Martinez', 'Luna', 'Ana', 'a.martinez@sinergia.edu', (SELECT id FROM users WHERE username='a.martinez')),
('EST003', '70000003', 'Gomez', 'Paredes', 'Fernando', 'f.gomez@sinergia.edu', (SELECT id FROM users WHERE username='f.gomez')),
('EST004', '70000004', 'Diaz', 'Campos', 'Sofia', 's.diaz@sinergia.edu', (SELECT id FROM users WHERE username='s.diaz')),
('EST005', '70000005', 'Sanchez', 'Rios', 'Valeria', 'v.sanchez@sinergia.edu', (SELECT id FROM users WHERE username='v.sanchez')),
('EST006', '70000006', 'Lopez', 'Mendoza', 'Pablo', 'p.lopez@sinergia.edu', (SELECT id FROM users WHERE username='p.lopez')),
('EST007', '70000007', 'Fernandez', 'Cruz', 'Daniela', 'd.fernandez@sinergia.edu', (SELECT id FROM users WHERE username='d.fernandez')),
('EST008', '70000008', 'Romero', 'Silva', 'Matias', 'm.romero@sinergia.edu', (SELECT id FROM users WHERE username='m.romero')),
('EST009', '70000009', 'Alvarez', 'Soto', 'Elena', 'e.alvarez@sinergia.edu', (SELECT id FROM users WHERE username='e.alvarez')),
('EST010', '70000010', 'Torres', 'Vargas', 'Ricardo', 'r.torres@sinergia.edu', (SELECT id FROM users WHERE username='r.torres')),
('EST011', '70000011', 'Ruiz', 'Castro', 'Gabriela', 'g.ruiz@sinergia.edu', (SELECT id FROM users WHERE username='g.ruiz')),
('EST012', '70000012', 'Ramirez', 'Nuñez', 'Lucia', 'l.ramirez@sinergia.edu', (SELECT id FROM users WHERE username='l.ramirez')),
('EST013', '70000013', 'Flores', 'Reyes', 'Javier', 'j.flores@sinergia.edu', (SELECT id FROM users WHERE username='j.flores')),
('EST014', '70000014', 'Acosta', 'Morales', 'Beatriz', 'b.acosta@sinergia.edu', (SELECT id FROM users WHERE username='b.acosta')),
('EST015', '70000015', 'Benitez', 'Ortega', 'Nicolas', 'n.benitez@sinergia.edu', (SELECT id FROM users WHERE username='n.benitez')),
('EST016', '70000016', 'Sosa', 'Jimenez', 'Isabella', 'i.sosa@sinergia.edu', (SELECT id FROM users WHERE username='i.sosa')),
('EST017', '70000017', 'Rivera', 'Delgado', 'Hugo', 'h.rivera@sinergia.edu', (SELECT id FROM users WHERE username='h.rivera')),
('EST018', '70000018', 'Medina', 'Pascual', 'Oscar', 'o.medina@sinergia.edu', (SELECT id FROM users WHERE username='o.medina')),
('EST019', '70000019', 'Herrera', 'Santos', 'Kiara', 'k.herrera@sinergia.edu', (SELECT id FROM users WHERE username='k.herrera')),
('EST020', '70000020', 'Aguilar', 'Iglesias', 'Ulises', 'u.aguilar@sinergia.edu', (SELECT id FROM users WHERE username='u.aguilar')),
('EST021', '70000021', 'Morales', 'Blanco', 'Yara', 'y.morales@sinergia.edu', (SELECT id FROM users WHERE username='y.morales')),
('EST022', '70000022', 'Castillo', 'Guerrero', 'Zoe', 'z.castillo@sinergia.edu', (SELECT id FROM users WHERE username='z.castillo')),
('EST023', '70000023', 'Ortega', 'Cano', 'Walter', 'w.ortega@sinergia.edu', (SELECT id FROM users WHERE username='w.ortega')),
('EST024', '70000024', 'Gimenez', 'Prieto', 'Ximena', 'x.gimenez@sinergia.edu', (SELECT id FROM users WHERE username='x.gimenez')),
('EST025', '70000025', 'Vazquez', 'Molina', 'David', 'd.vazquez@sinergia.edu', (SELECT id FROM users WHERE username='d.vazquez'));

-- 3.5. Insertar Cursos
INSERT INTO `cursos` (`codigo_curso`, `nombre_curso`, `creditos`, `horas_semanales`, `id_carrera`, `ciclo`, `tipo`) VALUES
('DS-101', 'Programación Orientada a Objetos', 5, 6, 1, 'II', 'obligatorio'),
('DS-202', 'Bases de Datos Relacionales', 5, 6, 1, 'III', 'obligatorio'),
('DG-101', 'Fundamentos del Diseño Gráfico', 4, 5, 2, 'I', 'obligatorio'),
('DG-205', 'Diseño de Interfaces (UI)', 4, 5, 2, 'IV', 'electivo'),
('MD-101', 'Introducción al Marketing Digital', 3, 4, 3, 'I', 'obligatorio'),
('MD-201', 'SEO y SEM', 4, 5, 3, 'II', 'obligatorio');

-- 3.6. Insertar Matrículas
INSERT INTO `matriculas` (`id_estudiante`, `id_curso`, `periodo_academico`)
SELECT id, 1, '2025-II' FROM estudiantes WHERE id BETWEEN 1 AND 15;
INSERT INTO `matriculas` (`id_estudiante`, `id_curso`, `periodo_academico`)
SELECT id, 2, '2025-II' FROM estudiantes WHERE id BETWEEN 1 AND 15;
INSERT INTO `matriculas` (`id_estudiante`, `id_curso`, `periodo_academico`)
SELECT id, 3, '2025-II' FROM estudiantes WHERE id BETWEEN 16 AND 25;
INSERT INTO `matriculas` (`id_estudiante`, `id_curso`, `periodo_academico`)
SELECT id, 4, '2025-II' FROM estudiantes WHERE id BETWEEN 16 AND 25;

-- 3.7. Insertar Evaluaciones
INSERT INTO `evaluaciones` (`id_curso`, `nombre_evaluacion`, `porcentaje`) VALUES
(1, 'Examen Parcial', 30.00),
(1, 'Examen Final', 40.00),
(1, 'Proyecto Final', 30.00),
(2, 'Práctica Calificada 1', 20.00),
(2, 'Práctica Calificada 2', 20.00),
(2, 'Examen Final', 60.00),
(3, 'Entrega 1', 50.00),
(3, 'Entrega 2', 50.00);

-- 3.8. Insertar Notas
INSERT INTO `notas` (`id_estudiante`, `id_evaluacion`, `nota`, `registrado_por`) VALUES
(1, 1, 15.50, (SELECT id FROM users WHERE username='j.perez')),
(1, 2, 12.00, (SELECT id FROM users WHERE username='j.perez')),
(1, 3, 18.00, (SELECT id FROM users WHERE username='j.perez')),
(2, 1, 11.00, (SELECT id FROM users WHERE username='j.perez')),
(2, 2, 14.50, (SELECT id FROM users WHERE username='j.perez')),
(3, 4, 19.00, (SELECT id FROM users WHERE username='j.perez')),
(3, 5, 16.00, (SELECT id FROM users WHERE username='j.perez')),
(3, 6, 17.50, (SELECT id FROM users WHERE username='j.perez'));

-- 3.9. Insertar Asistencia
INSERT INTO `asistencia` (`id_estudiante`, `id_curso`, `fecha`, `estado`, `registrado_por`) VALUES
(1, 1, '2025-10-01', 'asistio', (SELECT id FROM users WHERE username='j.perez')),
(1, 1, '2025-10-03', 'tardanza', (SELECT id FROM users WHERE username='j.perez')),
(1, 1, '2025-10-08', 'asistio', (SELECT id FROM users WHERE username='j.perez')),
(2, 1, '2025-10-01', 'falto', (SELECT id FROM users WHERE username='j.perez')),
(2, 1, '2025-10-03', 'asistio', (SELECT id FROM users WHERE username='j.perez'));

-- 3.10. Insertar Pensiones y Pagos
INSERT INTO `pensiones` (`id_estudiante`, `periodo_academico`, `monto`, `fecha_vencimiento`, `estado`) VALUES
(1, '2025-II', 500.00, '2025-09-30', 'pagado'),
(1, '2025-II', 500.00, '2025-10-30', 'pendiente');
INSERT INTO `pagos` (`id_pension`, `monto_pagado`, `metodo_pago`, `registrado_por`) VALUES
((SELECT id FROM pensiones WHERE id_estudiante=1 AND fecha_vencimiento='2025-09-30'), 500.00, 'Transferencia Bancaria', (SELECT id FROM users WHERE username='admin'));
INSERT INTO `pensiones` (`id_estudiante`, `periodo_academico`, `monto`, `fecha_vencimiento`, `estado`) VALUES
(2, '2025-II', 550.00, '2025-09-30', 'vencido');

SELECT 'Datos de prueba cargados exitosamente.' AS `estado`;

SET FOREIGN_KEY_CHECKS=1;