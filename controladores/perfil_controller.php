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
} elseif ($accion == 'actualizar_perfil') {
    actualizarPerfil();
} else {
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
                $redirect_page = '../vistas/admin/mi_perfil.php'; // Admin now has mi_perfil.php
                break;
            case 'docente':
                $redirect_page = '../vistas/docente/mi_perfil.php'; // Docente now has mi_perfil.php
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

function actualizarPerfil() {
    $id_user = $_SESSION['user_id'];
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';

    // Determine redirect URL based on user role
    $redirect_page = '../vistas/estudiante/mi_perfil.php'; // Default
    if (isset($_SESSION['rol'])) {
        switch ($_SESSION['rol']) {
            case 'admin':
                $redirect_page = '../vistas/admin/mi_perfil.php';
                break;
            case 'docente':
                $redirect_page = '../vistas/docente/mi_perfil.php';
                break;
            case 'estudiante':
                $redirect_page = '../vistas/estudiante/mi_perfil.php';
                break;
        }
    }

    // Basic validations
    if (empty($username)) {
        $_SESSION['mensaje_perfil'] = "El nombre de usuario no puede estar vacío.";
        $_SESSION['mensaje_perfil_tipo'] = "danger";
        header("Location: " . $redirect_page);
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        $_SESSION['mensaje_perfil'] = "El formato del correo electrónico no es válido.";
        $_SESSION['mensaje_perfil_tipo'] = "danger";
        header("Location: " . $redirect_page);
        exit();
    }

    // Check if username is already taken by another user
    $existing_user_by_username = select_one("SELECT id FROM users WHERE username = ? AND id != ?", "si", [$username, $id_user]);
    if ($existing_user_by_username) {
        $_SESSION['mensaje_perfil'] = "El nombre de usuario ya está en uso.";
        $_SESSION['mensaje_perfil_tipo'] = "danger";
        header("Location: " . $redirect_page);
        exit();
    }

    // Check if email is already taken by another user (if email is provided)
    if (!empty($email)) {
        $existing_user_by_email = select_one("SELECT id FROM users WHERE email = ? AND id != ?", "si", [$email, $id_user]);
        if ($existing_user_by_email) {
            $_SESSION['mensaje_perfil'] = "El correo electrónico ya está en uso.";
            $_SESSION['mensaje_perfil_tipo'] = "danger";
            header("Location: " . $redirect_page);
            exit();
        }
    }

    // Update user data
    $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
    if (execute_cud($sql, "ssi", [$username, $email, $id_user])) {
        $_SESSION['mensaje_perfil'] = "Información de perfil actualizada exitosamente.";
        $_SESSION['mensaje_perfil_tipo'] = "success";
        // Update session username if it changed
        $_SESSION['username'] = $username;
    } else {
        $_SESSION['mensaje_perfil'] = "Error al actualizar la información del perfil. Inténtalo de nuevo.";
        $_SESSION['mensaje_perfil_tipo'] = "danger";
    }

    header("Location: " . $redirect_page);
    exit();
}
?>
