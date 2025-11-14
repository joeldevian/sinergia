<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$accion = $_POST['accion'] ?? '';

if ($accion == 'cambiar_password') {
    cambiarPassword($conexion);
} else {
    // Redirect if the action is not recognized
    header("Location: ../vistas/estudiante/dashboard.php");
    exit();
}

function cambiarPassword($conexion) {
    $id_user = $_SESSION['user_id'];
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $password_confirmar = $_POST['password_confirmar'];

    // 1. Check if new passwords match
    if ($password_nueva !== $password_confirmar) {
        $_SESSION['mensaje_perfil'] = "La nueva contraseña y su confirmación no coinciden.";
        $_SESSION['mensaje_perfil_tipo'] = "danger";
        header("Location: ../vistas/estudiante/mi_perfil.php");
        exit();
    }

    // 2. Get current password hash from DB
    $stmt = $conexion->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $user = $resultado->fetch_assoc();
    $stmt->close();

    if (!$user) {
        // This should not happen
        session_destroy();
        header("Location: ../index.php");
        exit();
    }

    // 3. Verify current password
    if (!password_verify($password_actual, $user['password_hash'])) {
        $_SESSION['mensaje_perfil'] = "La contraseña actual es incorrecta.";
        $_SESSION['mensaje_perfil_tipo'] = "danger";
        header("Location: ../vistas/estudiante/mi_perfil.php");
        exit();
    }

    // 4. Hash and update new password
    $nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
    $stmt_update = $conexion->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt_update->bind_param("si", $nuevo_hash, $id_user);
    
    if ($stmt_update->execute()) {
        $_SESSION['mensaje_perfil'] = "Contraseña actualizada exitosamente.";
        $_SESSION['mensaje_perfil_tipo'] = "success";
    } else {
        $_SESSION['mensaje_perfil'] = "Error al actualizar la contraseña. Inténtalo de nuevo.";
        $_SESSION['mensaje_perfil_tipo'] = "danger";
    }
    $stmt_update->close();
    $conexion->close();

    header("Location: ../vistas/estudiante/mi_perfil.php");
    exit();
}
?>
