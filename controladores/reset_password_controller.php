<?php
session_start();
require_once '../config/conexion.php';

// Función para redirigir con mensaje de estado
function redirect_with_status($location, $message, $type) {
    $_SESSION['status_message'] = $message;
    $_SESSION['status_type'] = $type;
    header("Location: {$location}");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // 1. Validaciones básicas
    if (empty($token) || empty($password) || empty($password_confirm)) {
        redirect_with_status("../vistas/reset_password.php?token={$token}", "Por favor, completa todos los campos.", "warning");
    }

    if ($password !== $password_confirm) {
        redirect_with_status("../vistas/reset_password.php?token={$token}", "Las contraseñas no coinciden.", "warning");
    }

    // 2. Hashear el token recibido para buscarlo en la BD
    $token_hash = hash("sha256", $token);

    // 3. Buscar el usuario por el hash del token
    $sql = "SELECT id, reset_token_expires_at FROM users WHERE reset_token_hash = ? LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        redirect_with_status("../vistas/forgot_password.php", "El enlace de recuperación es inválido.", "danger");
    }

    // 4. Verificar si el token ha expirado
    $expires_at = new DateTime($user['reset_token_expires_at']);
    $now = new DateTime();

    if ($now > $expires_at) {
        redirect_with_status("../vistas/forgot_password.php", "El enlace de recuperación ha expirado. Por favor, solicita uno nuevo.", "danger");
    }

    // 5. El token es válido, proceder a cambiar la contraseña
    $new_password_hash = password_hash($password, PASSWORD_DEFAULT);

    // 6. Actualizar la contraseña y limpiar los campos del token
    $update_sql = "UPDATE users SET password_hash = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?";
    $update_stmt = $conexion->prepare($update_sql);
    $update_stmt->bind_param("si", $new_password_hash, $user['id']);

    if ($update_stmt->execute()) {
        // Redirigir al login con mensaje de éxito
        $_SESSION['login_success_message'] = "Tu contraseña ha sido actualizada correctamente. Ya puedes iniciar sesión.";
        header("Location: ../index.php");
        exit();
    } else {
        redirect_with_status("../vistas/reset_password.php?token={$token}", "Ocurrió un error al actualizar tu contraseña. Intenta de nuevo.", "danger");
    }

} else {
    // Redirigir si el acceso no es por POST
    header("Location: ../index.php");
    exit();
}
?>
