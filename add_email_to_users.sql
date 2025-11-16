-- add_email_to_users.sql

USE `sinergia`;

-- Añadir la columna 'email' a la tabla 'users'
ALTER TABLE `users`
ADD COLUMN `email` VARCHAR(150) UNIQUE NULL AFTER `username`;

-- Opcional: Actualizar los emails de los usuarios existentes si es necesario
-- UPDATE `users` SET `email` = 'admin@sinergia.edu' WHERE `username` = 'admin';
-- UPDATE `users` SET `email` = 'j.perez@sinergia.edu' WHERE `username` = 'j.perez';
-- etc.
