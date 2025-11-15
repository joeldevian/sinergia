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
        // 1. Create the user
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $rol = 'estudiante';

        $sql_user = "INSERT INTO users (username, password_hash, rol) VALUES (?, ?, ?)";
        if (!execute_cud($sql_user, "sss", [$username, $password_hash, $rol])) {
            throw new Exception("No se pudo crear el usuario.");
        }
        $id_user = last_insert_id();

        // 2. Create the student
        $codigo_estudiante = $_POST['codigo_estudiante'];
        $dni = $_POST['dni'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $nombres = $_POST['nombres'];
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?: null;
        $sexo = $_POST['sexo'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];

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
    $id_estudiante = $_POST['id_estudiante'];
    $id_user = $_POST['id_user'];

    $conexion->begin_transaction();

    try {
        // 1. Update student data
        $codigo_estudiante = $_POST['codigo_estudiante'];
        $dni = $_POST['dni'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $nombres = $_POST['nombres'];
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?: null;
        $sexo = $_POST['sexo'];
        $direccion = $_POST['direccion'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];
        $estado = $_POST['estado'];

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

        // 2. Update password if provided
        $password = $_POST['password'];
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