<?php
session_start();
// Incluir las nuevas funciones de base de datos. Esto también incluye 'conexion.php'
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

// La conexión global sigue siendo necesaria para manejar las transacciones
global $conexion;

switch ($accion) {
    case 'agregar':
        agregarDocente();
        break;
    case 'editar':
        editarDocente();
        break;
    case 'eliminar':
        eliminarDocente();
        break;
}

function agregarDocente() {
    global $conexion;
    $conexion->begin_transaction();

    try {
        // 1. Validate input data
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? ''; // Assuming a confirm password field
        $codigo_docente = trim($_POST['codigo_docente'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
        $apellido_materno = trim($_POST['apellido_materno'] ?? '');
        $nombres = trim($_POST['nombres'] ?? '');
        $especialidad = trim($_POST['especialidad'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $errors = [];

        if (empty($username)) $errors[] = "El nombre de usuario es requerido.";
        if (empty($password)) $errors[] = "La contraseña es requerida.";
        if ($password !== $password_confirm) $errors[] = "La contraseña y la confirmación no coinciden.";
        if (strlen($password) < 6) $errors[] = "La contraseña debe tener al menos 6 caracteres."; // Example complexity
        if (empty($codigo_docente)) $errors[] = "El código de docente es requerido.";
        if (empty($dni)) $errors[] = "El DNI es requerido.";
        if (!preg_match('/^[0-9]{8}$/', $dni)) $errors[] = "El DNI debe contener 8 dígitos numéricos.";
        if (empty($apellido_paterno)) $errors[] = "El apellido paterno es requerido.";
        if (empty($nombres)) $errors[] = "Los nombres son requeridos.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El formato del email no es válido.";
        if (empty($especialidad)) $errors[] = "La especialidad es requerida.";
        
        // Add more specific validations as needed (e.g., max lengths)

        if (!empty($errors)) {
            $_SESSION['mensaje'] = "Errores de validación: " . implode("<br>", $errors);
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: ../vistas/admin/gestionar_docentes.php"); // Or redirect back to form with old data
            exit();
        }

        // 2. Crear el usuario
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $rol = 'docente';

        $sql_user = "INSERT INTO users (username, password_hash, rol) VALUES (?, ?, ?)";
        if (!execute_cud($sql_user, "sss", [$username, $password_hash, $rol])) {
            throw new Exception("No se pudo crear el usuario.");
        }
        $id_user = last_insert_id();

        // 3. Crear el docente
        $sql_docente = "INSERT INTO docentes (codigo_docente, dni, apellido_paterno, apellido_materno, nombres, especialidad, telefono, email, id_user) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params_docente = [
            $codigo_docente, $dni, $apellido_paterno, $apellido_materno, $nombres,
            $especialidad, $telefono, $email, $id_user
        ];
        if (!execute_cud($sql_docente, "ssssssssi", $params_docente)) {
            throw new Exception("No se pudo crear el registro del docente.");
        }

        $conexion->commit();
        $_SESSION['mensaje'] = "Docente agregado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $conexion->rollback();
        if (method_exists($e, 'getCode') && $e->getCode() == 1062) {
             $_SESSION['mensaje'] = "Error: Ya existe un registro con los datos proporcionados (usuario, DNI, código o email).";
        } else {
            $_SESSION['mensaje'] = "Error al agregar docente: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_docentes.php");
    exit();
}

function editarDocente() {
    global $conexion;
    $id_docente = $_POST['id_docente'] ?? 0;
    $id_user = $_POST['id_user'] ?? 0;

    $conexion->begin_transaction();

    try {
        // 1. Validate input data
        $codigo_docente = trim($_POST['codigo_docente'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
        $apellido_materno = trim($_POST['apellido_materno'] ?? '');
        $nombres = trim($_POST['nombres'] ?? '');
        $especialidad = trim($_POST['especialidad'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $estado = $_POST['estado'] ?? '';
        $password = $_POST['password'] ?? ''; // New password if provided
        $password_confirm = $_POST['password_confirm'] ?? ''; // Assuming a confirm password field

        $errors = [];

        if (empty($id_docente) || empty($id_user)) $errors[] = "IDs de docente o usuario no válidos.";
        if (empty($codigo_docente)) $errors[] = "El código de docente es requerido.";
        if (empty($dni)) $errors[] = "El DNI es requerido.";
        if (!preg_match('/^[0-9]{8}$/', $dni)) $errors[] = "El DNI debe contener 8 dígitos numéricos.";
        if (empty($apellido_paterno)) $errors[] = "El apellido paterno es requerido.";
        if (empty($nombres)) $errors[] = "Los nombres son requeridos.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El formato del email no es válido.";
        if (empty($especialidad)) $errors[] = "La especialidad es requerida.";
        if (!in_array($estado, ['activo', 'inactivo'])) $errors[] = "El estado debe ser 'activo' o 'inactivo'.";

        // Validate new password if provided
        if (!empty($password)) {
            if ($password !== $password_confirm) $errors[] = "La nueva contraseña y su confirmación no coinciden.";
            if (strlen($password) < 6) $errors[] = "La nueva contraseña debe tener al menos 6 caracteres."; // Example complexity
        }
        
        // Add more specific validations as needed (e.g., max lengths)

        if (!empty($errors)) {
            $conexion->rollback(); // Rollback any potential changes if validation fails
            $_SESSION['mensaje'] = "Errores de validación: " . implode("<br>", $errors);
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: ../vistas/admin/gestionar_docentes.php"); // Or redirect back to form with old data
            exit();
        }

        // 2. Actualizar datos del docente
        $sql_docente = "UPDATE docentes SET 
                        codigo_docente = ?, dni = ?, apellido_paterno = ?, apellido_materno = ?, nombres = ?, 
                        especialidad = ?, telefono = ?, email = ?, estado = ?
                        WHERE id = ?";
        $params_docente = [
            $codigo_docente, $dni, $apellido_paterno, $apellido_materno, $nombres,
            $especialidad, $telefono, $email, $estado, $id_docente
        ];
        if (!execute_cud($sql_docente, "sssssssssi", $params_docente)) {
            throw new Exception("No se pudo actualizar los datos del docente.");
        }

        // 3. Actualizar contraseña si se proporcionó una nueva
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql_user = "UPDATE users SET password_hash = ? WHERE id = ?";
            if (!execute_cud($sql_user, "si", [$password_hash, $id_user])) {
                throw new Exception("No se pudo actualizar la contraseña.");
            }
        }

        $conexion->commit();
        $_SESSION['mensaje'] = "Docente actualizado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $conexion->rollback();
        if (method_exists($e, 'getCode') && $e->getCode() == 1062) {
            $_SESSION['mensaje'] = "Error: Ya existe otro registro con los datos proporcionados (DNI, código o email).";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar docente: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_docentes.php");
    exit();
}

function eliminarDocente() {
    global $conexion;
    $id_docente = $_GET['id'] ?? 0;

    if ($id_docente == 0) {
        $_SESSION['mensaje'] = "ID de docente no válido.";
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: ../vistas/admin/gestionar_docentes.php");
        exit();
    }

    $conexion->begin_transaction();

    try {
        // 1. Obtener el id_user antes de eliminar el docente
        $docente = select_one("SELECT id_user FROM docentes WHERE id = ?", "i", [$id_docente]);
        if (!$docente || !isset($docente['id_user'])) {
            throw new Exception("No se pudo encontrar el usuario asociado al docente.");
        }
        $id_user = $docente['id_user'];

        // 2. Eliminar al docente.
        if (!execute_cud("DELETE FROM docentes WHERE id = ?", "i", [$id_docente])) {
            throw new Exception("No se pudo eliminar el registro del docente.");
        }

        // 3. Eliminar el usuario asociado
        if (!execute_cud("DELETE FROM users WHERE id = ?", "i", [$id_user])) {
            throw new Exception("No se pudo eliminar el usuario asociado.");
        }

        $conexion->commit();
        $_SESSION['mensaje'] = "Docente eliminado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje'] = "Error al eliminar el docente: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_docentes.php");
    exit();
}
?>