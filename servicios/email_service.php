<?php
// Usar los namespaces de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Requerir el autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Envía un correo electrónico utilizando PHPMailer.
 *
 * @param string $to      La dirección de correo del destinatario.
 * @param string $subject El asunto del correo.
 * @param string $body    El cuerpo del correo en formato HTML.
 * @return bool|string    Devuelve true si el correo se envió con éxito, o un string con el mensaje de error si falló.
 */
function enviar_email($to, $subject, $body) {
    // Cargar la configuración de email
    $email_config = require __DIR__ . '/../config/email_config.php';

    // Verificar que la configuración no esté vacía
    if (empty($email_config['host']) || empty($email_config['username']) || empty($email_config['password'])) {
        return "Error: La configuración de SMTP no está completa. Por favor, revisa el archivo 'config/email_config.php'.";
    }

    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host       = $email_config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $email_config['username'];
        $mail->Password   = $email_config['password'];
        $mail->SMTPSecure = $email_config['encryption']; // PHPMailer::ENCRYPTION_STARTTLS o PHPMailer::ENCRYPTION_SMTPS
        $mail->Port       = $email_config['port'];
        $mail->CharSet    = 'UTF-8';

        // Opcional: Descomentar para depuración
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Muestra el log de la conexión SMTP

        // Remitente y destinatarios
        $mail->setFrom($email_config['from_email'], $email_config['from_name']);
        $mail->addAddress($to);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        // Opcional: Cuerpo alternativo para clientes de correo que no soportan HTML
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // En un entorno de producción, sería bueno loggear este error en lugar de solo devolverlo.
        return "El mensaje no pudo ser enviado. Error de PHPMailer: {$mail->ErrorInfo}";
    }
}
