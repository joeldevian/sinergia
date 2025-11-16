<?php
session_start();
require_once '../config/database.php'; // Use the new database functions

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

// The global $conexion is still needed for transactions
global $conexion;

switch ($accion) {
    case 'agregar':
        agregarEstudiante();
        break;
    case 'editar':
        editarEstudiante();
        break;
    case 'eliminar':
        eliminarEstudiante();
        break;
}

function agregarEstudiante() {
    global $conexion;
    $conexion->begin_transaction();

    try {
        // 1. Validate input data
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? ''; // Assuming a confirm password field
        $codigo_estudiante = trim($_POST['codigo_estudiante'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
        $apellido_materno = trim($_POST['apellido_materno'] ?? '');
        $nombres = trim($_POST['nombres'] ?? '');
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
        $sexo = $_POST['sexo'] ?? '';
        $direccion = trim($_POST['direccion'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $errors = [];

        if (empty($username)) $errors[] = "El nombre de usuario es requerido.";
        if (empty($password)) $errors[] = "La contraseña es requerida.";
        if ($password !== $password_confirm) $errors[] = "La contraseña y la confirmación no coinciden.";
        if (strlen($password) < 6) $errors[] = "La contraseña debe tener al menos 6 caracteres."; // Example complexity
        if (empty($codigo_estudiante)) $errors[] = "El código de estudiante es requerido.";
        if (empty($dni)) $errors[] = "El DNI es requerido.";
        if (!preg_match('/^[0-9]{8}$/', $dni)) $errors[] = "El DNI debe contener 8 dígitos numéricos.";
        if (empty($apellido_paterno)) $errors[] = "El apellido paterno es requerido.";
        if (empty($nombres)) $errors[] = "Los nombres son requeridos.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El formato del email no es válido.";
        if (!in_array($sexo, ['M', 'F'])) $errors[] = "El sexo debe ser 'M' o 'F'.";
        
        // Add more specific validations as needed (e.g., max lengths, date format)

        if (!empty($errors)) {
            $_SESSION['mensaje'] = "Errores de validación: " . implode("<br>", $errors);
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: ../vistas/admin/gestionar_estudiantes.php"); // Or redirect back to form with old data
            exit();
        }

        // 2. Create the user
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $rol = 'estudiante';

        $sql_user = "INSERT INTO users (username, password_hash, rol) VALUES (?, ?, ?)";
        if (!execute_cud($sql_user, "sss", [$username, $password_hash, $rol])) {
            throw new Exception("No se pudo crear el usuario.");
        }
        $id_user = last_insert_id();

        // 3. Create the student
        $sql_estudiante = "INSERT INTO estudiantes (codigo_estudiante, dni, apellido_paterno, apellido_materno, nombres, fecha_nacimiento, sexo, direccion, telefono, email, id_user) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params_estudiante = [
            $codigo_estudiante, $dni, $apellido_paterno, $apellido_materno, $nombres,
            $fecha_nacimiento, $sexo, $direccion, $telefono, $email, $id_user
        ];
        if (!execute_cud($sql_estudiante, "ssssssssssi", $params_estudiante)) {
            throw new Exception("No se pudo crear el registro del estudiante.");
        }

        $conexion->commit();
        $_SESSION['mensaje'] = "Estudiante agregado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $conexion->rollback();
        if (method_exists($e, 'getCode') && $e->getCode() == 1062) {
            $_SESSION['mensaje'] = "Error: Ya existe un registro con los datos proporcionados (usuario, DNI, código o email).";
        } else {
            $_SESSION['mensaje'] = "Error al agregar estudiante: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_estudiantes.php");
    exit();
}

function editarEstudiante() {
    global $conexion;
    $id_estudiante = $_POST['id_estudiante'] ?? 0;
    $id_user = $_POST['id_user'] ?? 0;

    $conexion->begin_transaction();

    try {
        // 1. Validate input data
        $codigo_estudiante = trim($_POST['codigo_estudiante'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
        $apellido_materno = trim($_POST['apellido_materno'] ?? '');
        $nombres = trim($_POST['nombres'] ?? '');
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
        $sexo = $_POST['sexo'] ?? '';
        $direccion = trim($_POST['direccion'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $estado = $_POST['estado'] ?? '';
        $password = $_POST['password'] ?? ''; // New password if provided
        $password_confirm = $_POST['password_confirm'] ?? ''; // Assuming a confirm password field

        $errors = [];

        if (empty($id_estudiante) || empty($id_user)) $errors[] = "IDs de estudiante o usuario no válidos.";
        if (empty($codigo_estudiante)) $errors[] = "El código de estudiante es requerido.";
        if (empty($dni)) $errors[] = "El DNI es requerido.";
        if (!preg_match('/^[0-9]{8}$/', $dni)) $errors[] = "El DNI debe contener 8 dígitos numéricos.";
        if (empty($apellido_paterno)) $errors[] = "El apellido paterno es requerido.";
        if (empty($nombres)) $errors[] = "Los nombres son requeridos.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El formato del email no es válido.";
        if (!in_array($sexo, ['M', 'F'])) $errors[] = "El sexo debe ser 'M' o 'F'.";
        if (!in_array($estado, ['activo', 'inactivo'])) $errors[] = "El estado debe ser 'activo' o 'inactivo'.";

        // Validate new password if provided
        if (!empty($password)) {
            if ($password !== $password_confirm) $errors[] = "La nueva contraseña y su confirmación no coinciden.";
            if (strlen($password) < 6) $errors[] = "La nueva contraseña debe tener al menos 6 caracteres."; // Example complexity
        }
        
        // Add more specific validations as needed (e.g., max lengths, date format)

        if (!empty($errors)) {
            $conexion->rollback(); // Rollback any potential changes if validation fails
            $_SESSION['mensaje'] = "Errores de validación: " . implode("<br>", $errors);
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: ../vistas/admin/gestionar_estudiantes.php"); // Or redirect back to form with old data
            exit();
        }

        // 2. Update student data
        $sql_estudiante = "UPDATE estudiantes SET 
                           codigo_estudiante = ?, dni = ?, apellido_paterno = ?, apellido_materno = ?, nombres = ?, 
                           fecha_nacimiento = ?, sexo = ?, direccion = ?, telefono = ?, email = ?, estado = ?
                           WHERE id = ?";
        $params_estudiante = [
            $codigo_estudiante, $dni, $apellido_paterno, $apellido_materno, $nombres,
            $fecha_nacimiento, $sexo, $direccion, $telefono, $email, $estado, $id_estudiante
        ];
        if (!execute_cud($sql_estudiante, "sssssssssssi", $params_estudiante)) {
            throw new Exception("No se pudo actualizar los datos del estudiante.");
        }

        // 3. Update password if provided
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql_user = "UPDATE users SET password_hash = ? WHERE id = ?";
            if (!execute_cud($sql_user, "si", [$password_hash, $id_user])) {
                throw new Exception("No se pudo actualizar la contraseña.");
            }
        }

        $conexion->commit();
        $_SESSION['mensaje'] = "Estudiante actualizado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $conexion->rollback();
        if (method_exists($e, 'getCode') && $e->getCode() == 1062) {
            $_SESSION['mensaje'] = "Error: Ya existe otro registro con los datos proporcionados (DNI, código o email).";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar estudiante: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_estudiantes.php");
    exit();
}

function eliminarEstudiante() {
    global $conexion;
    $id_estudiante = $_GET['id'] ?? 0;

    if ($id_estudiante == 0) {
        $_SESSION['mensaje'] = "ID de estudiante no válido.";
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: ../vistas/admin/gestionar_estudiantes.php");
        exit();
    }

    $conexion->begin_transaction();

    try {
        // 1. Get the id_user before deleting the student
        $estudiante = select_one("SELECT id_user FROM estudiantes WHERE id = ?", "i", [$id_estudiante]);
        if (!$estudiante || !isset($estudiante['id_user'])) {
            throw new Exception("No se pudo encontrar el usuario asociado al estudiante.");
        }
        $id_user = $estudiante['id_user'];

        // 2. Delete the student
        if (!execute_cud("DELETE FROM estudiantes WHERE id = ?", "i", [$id_estudiante])) {
            throw new Exception("No se pudo eliminar el registro del estudiante.");
        }

        // 3. Delete the associated user
        if (!execute_cud("DELETE FROM users WHERE id = ?", "i", [$id_user])) {
            throw new Exception("No se pudo eliminar el usuario asociado.");
        }

        $conexion->commit();
        $_SESSION['mensaje'] = "Estudiante eliminado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje'] = "Error al eliminar el estudiante: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_estudiantes.php");
    exit();
}
?>