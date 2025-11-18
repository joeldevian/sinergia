-- Joel, por favor ejecuta este script en tu base de datos para añadir la funcionalidad de recuperación de contraseña.

-- Asumimos que tu tabla de usuarios se llama 'users'. Si tiene otro nombre, por favor ajústalo.
-- Si no estás seguro, puedes verificarlo en tu archivo `controladores/login_controller.php`.

ALTER TABLE `users`
ADD COLUMN `reset_token_hash` VARCHAR(64) NULL DEFAULT NULL,
ADD COLUMN `reset_token_expires_at` DATETIME NULL DEFAULT NULL,
ADD UNIQUE INDEX `reset_token_hash` (`reset_token_hash`);
