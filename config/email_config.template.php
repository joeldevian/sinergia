<?php
// Plantilla de configuración de Email
// Por favor, renombra este archivo a 'email_config.php' y rellena tus datos.

// ** NO subas el archivo 'email_config.php' con tus credenciales a repositorios públicos. **

return [
    // Configuración del servidor SMTP
    'host' => 'smtp.example.com', // Por ej. 'smtp.gmail.com' para Gmail
    'username' => 'tu_usuario@example.com',
    'password' => 'tu_contraseña',
    'port' => 587, // Puerto SMTP. 587 para TLS (recomendado), 465 para SSL.
    'encryption' => 'tls', // 'tls' o 'ssl'

    // Información del remitente
    'from_email' => 'no-reply@example.com',
    'from_name' => 'Instituto Sinergia'
];
