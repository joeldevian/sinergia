<?php
session_start();
require_once '../config/conexion.php';

// Definir una función para redirigir con un mensaje
function redirect_with_message($message, $type) {
    $_SESSION['status_message'] = $message;
    $_SESSION['status_type'] = $type;
    header("Location: ../vistas/forgot_password.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect_with_message("Por favor, ingresa una dirección de correo electrónico válida.", "warning");
    }

    // Buscar el usuario por email
    $sql = "SELECT id FROM users WHERE email = ? LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // El usuario existe, proceder a generar el token
        
        // 1. Generar un token seguro
        $token = bin2hex(random_bytes(32));

        // 2. Hashear el token para guardarlo en la BD
        $token_hash = hash("sha256", $token);

        // 3. Establecer fecha de expiración (ej. 1 hora)
        $expires_at = new DateTime();
        $expires_at->add(new DateInterval("PT1H"));
        $expires_at_string = $expires_at->format("Y-m-d H:i:s");

        // 4. Actualizar el registro del usuario en la BD
        $update_sql = "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?";
        $update_stmt = $conexion->prepare($update_sql);
        $update_stmt->bind_param("ssi", $token_hash, $expires_at_string, $user['id']);
        
        require_once '../servicios/email_service.php';
        
        if ($update_stmt->execute()) {
            // 5. Construir el enlace de reseteo y enviar el correo
            
            // Detectar el host dinámicamente
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            // Obtener la ruta base del proyecto de forma más robusta
            $base_path = dirname($_SERVER['PHP_SELF'], 2); // Sube dos niveles desde /controladores/
            $reset_link = "{$protocol}://{$host}{$base_path}/vistas/reset_password.php?token={$token}";

            // Contenido del correo
            $subject = "Restablecimiento de Contraseña - Instituto Sinergia";
            $body = "
                <h1>Solicitud de Restablecimiento de Contraseña</h1>
                <p>Hola,</p>
                <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en el sistema del Instituto Sinergia.</p>
                <p>Por favor, haz clic en el siguiente enlace para continuar:</p>
                <p><a href='{$reset_link}'>Restablecer mi Contraseña</a></p>
                <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
                <p>Este enlace es válido por 1 hora.</p>
                <br>
                <p>Saludos,</p>
                <p><strong>Equipo del Instituto Sinergia</strong></p>
            ";

            $enviado = enviar_email($email, $subject, $body);

            if ($enviado === true) {
                // Redirigir a la página de confirmación de envío
                header("Location: ../vistas/mensaje_enviado.php");
                exit();
            } else {
                // $enviado contiene el mensaje de error del servicio de email
                // Es importante loggear este error para depuración, pero mostramos un mensaje genérico al usuario.
                error_log("Error al enviar email de recuperación: " . $enviado);
                redirect_with_message("No se pudo enviar el correo de recuperación. Por favor, contacta al administrador.", "danger");
            }

        } else {
            redirect_with_message("Ocurrió un error al generar el enlace. Por favor, intenta de nuevo.", "danger");
        }

    } else {
        // Para no revelar si un email existe o no, mostramos el mismo mensaje de éxito.
        // Redirigir a la página de confirmación de envío para no dar pistas.
        header("Location: ../vistas/mensaje_enviado.php");
        exit();
    }

} else {
    // Si no es POST, redirigir
    header("Location: ../vistas/forgot_password.php");
    exit();
}
?>
