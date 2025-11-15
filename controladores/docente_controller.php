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
        // 1. Crear el usuario
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $rol = 'docente';

        $sql_user = "INSERT INTO users (username, password_hash, rol) VALUES (?, ?, ?)";
        if (!execute_cud($sql_user, "sss", [$username, $password_hash, $rol])) {
            throw new Exception("No se pudo crear el usuario.");
        }
        $id_user = last_insert_id();

        // 2. Crear el docente
        $codigo_docente = $_POST['codigo_docente'];
        $dni = $_POST['dni'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $nombres = $_POST['nombres'];
        $especialidad = $_POST['especialidad'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];

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
        // Simplificado el manejo de errores. El original tenía comprobaciones más específicas.
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
    $id_docente = $_POST['id_docente'];
    $id_user = $_POST['id_user'];

    $conexion->begin_transaction();

    try {
        // 1. Actualizar datos del docente
        $codigo_docente = $_POST['codigo_docente'];
        $dni = $_POST['dni'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $nombres = $_POST['nombres'];
        $especialidad = $_POST['especialidad'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];
        $estado = $_POST['estado'];

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

        // 2. Actualizar contraseña si se proporcionó una nueva
        $password = $_POST['password'];
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