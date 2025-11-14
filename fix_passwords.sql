-- Script para actualizar los hashes de contraseña de los usuarios de prueba.

USE `sinergia`;

-- Nuevo hash generado por el sistema de Joel para 'password123'
SET @new_password_hash = '$2y$10$YPWppbf5NDW1QASq8NFsmeXFzMfNguxH0rxqmvjHN75U4a9LvGeoe';

-- Actualizar contraseñas para docentes
UPDATE `users`
SET `password_hash` = @new_password_hash
WHERE `rol` = 'docente';

-- Actualizar contraseñas para estudiantes
UPDATE `users`
SET `password_hash` = @new_password_hash
WHERE `rol` = 'estudiante';

SELECT 'Contraseñas de usuarios de prueba actualizadas exitosamente.' AS `estado`;
