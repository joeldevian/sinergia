<?php
session_start();
require_once '../config/database.php'; // Use the new database functions

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$accion = $_POST['accion'] ?? '';

if ($accion == 'cambiar_password') {
    cambiarPassword();
} else {
    // Redirect if the action is not recognized
    // Determine redirect based on user role if possible, otherwise default to student dashboard
    $redirect_url = '../vistas/estudiante/dashboard.php'; // Default
    if (isset($_SESSION['rol'])) {
        switch ($_SESSION['rol']) {
            case 'admin':
                $redirect_url = '../vistas/admin/dashboard.php';
                break;
            case 'docente':
                $redirect_url = '../vistas/docente/dashboard.php';
                break;
            case 'estudiante':
                $redirect_url = '../vistas/estudiante/dashboard.php';
                break;
        }
    }
    header("Location: " . $redirect_url);
    exit();
}

function cambiarPassword() {
    $id_user = $_SESSION['user_id'];
    $password_actual = $_POST['password_actual'];
    $password_nueva = $_POST['password_nueva'];
    $password_confirmar = $_POST['password_confirmar'];

    // Determine redirect URL based on user role
    $redirect_page = '../vistas/estudiante/mi_perfil.php'; // Default
    if (isset($_SESSION['rol'])) {
        switch ($_SESSION['rol']) {
            case 'admin':
                $redirect_page = '../vistas/admin/dashboard.php'; // Admin doesn't have mi_perfil.php
                break;
            case 'docente':
                $redirect_page = '../vistas/docente/dashboard.php'; // Docente doesn't have mi_perfil.php
                break;
            case 'estudiante':
                $redirect_page = '../vistas/estudiante/mi_perfil.php';
                break;
        }
    }


    // 1. Check if new passwords match
    if ($password_nueva !== $password_confirmar) {
        $_SESSION['mensaje_perfil'] = "La nueva contraseña y su confirmación no coinciden.";
        $_SESSION['mensaje_perfil_tipo'] = "danger";
        header("Location: " . $redirect_page);
        exit();
    }

    // 2. Get current password hash from DB
    $user = select_one("SELECT password_hash FROM users WHERE id = ?", "i", [$id_user]);

    if (!$user) {
        // This should not happen, user not found
        session_destroy();
        header("Location: ../index.php");
        exit();
    }

    // 3. Verify current password
    if (!password_verify($password_actual, $user['password_hash'])) {
        $_SESSION['mensaje_perfil'] = "La contraseña actual es incorrecta.";
        $_SESSION['mensaje_perfil_tipo'] = "danger";
        header("Location: " . $redirect_page);
        exit();
    }

    // 4. Hash and update new password
    $nuevo_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
    
    if (execute_cud("UPDATE users SET password_hash = ? WHERE id = ?", "si", [$nuevo_hash, $id_user])) {
        $_SESSION['mensaje_perfil'] = "Contraseña actualizada exitosamente.";
        $_SESSION['mensaje_perfil_tipo'] = "success";
    } else {
        $_SESSION['mensaje_perfil'] = "Error al actualizar la contraseña. Inténtalo de nuevo.";
        $_SESSION['mensaje_perfil_tipo'] = "danger";
    }

    header("Location: " . $redirect_page);
    exit();
}
?>