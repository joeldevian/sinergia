<?php
session_start();
require_once '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Por favor, ingrese usuario y contraseña.";
        header("Location: ../index.php");
        exit();
    }

    // Preparar la consulta para evitar inyecciones SQL
    $stmt = $conexion->prepare("SELECT id, username, password_hash, rol FROM users WHERE username = ? AND estado = 'activo'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verificar la contraseña
        if (password_verify($password, $user['password_hash'])) {
            // Contraseña correcta, iniciar sesión
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['rol'] = $user['rol'];

            // Redirigir según el rol
            switch ($user['rol']) {
                case 'admin':
                    header("Location: ../vistas/admin/dashboard.php");
                    break;
                case 'docente':
                    header("Location: ../vistas/docente/dashboard.php");
                    break;
                case 'estudiante':
                    header("Location: ../vistas/estudiante/dashboard.php");
                    break;
                default:
                    // Rol desconocido, redirigir a login con error
                    $_SESSION['login_error'] = "Rol de usuario desconocido.";
                    header("Location: ../index.php");
                    break;
            }
            exit();
        } else {
            // Contraseña incorrecta
            $_SESSION['login_error'] = "DEBUG: La contraseña no coincide.";
            header("Location: ../index.php");
            exit();
        }
    } else {
        // Usuario no encontrado o inactivo
        $_SESSION['login_error'] = "DEBUG: Usuario no encontrado o está inactivo.";
        header("Location: ../index.php");
        exit();
    }

    $stmt->close();
    $conexion->close();
} else {
    // Si se intenta acceder directamente a este controlador sin POST
    header("Location: ../index.php");
    exit();
}
?>