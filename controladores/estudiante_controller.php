<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    // Redirect to login if not authenticated as admin
    header("Location: ../index.php");
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {
    case 'agregar':
        agregarEstudiante($conexion);
        break;
    case 'editar':
        editarEstudiante($conexion);
        break;
    case 'eliminar':
        eliminarEstudiante($conexion);
        break;
    // Add cases for 'editar' and 'eliminar' later
}

function eliminarEstudiante($conexion) {
    $id_estudiante = $_GET['id'] ?? 0;

    if ($id_estudiante == 0) {
        $_SESSION['mensaje'] = "ID de estudiante no válido.";
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: ../vistas/admin/gestionar_estudiantes.php");
        exit();
    }

    $conexion->begin_transaction();

    try {
        // 1. Obtener el id_user antes de eliminar el estudiante
        $stmt_get_user = $conexion->prepare("SELECT id_user FROM estudiantes WHERE id = ?");
        $stmt_get_user->bind_param("i", $id_estudiante);
        $stmt_get_user->execute();
        $resultado = $stmt_get_user->get_result();
        $estudiante = $resultado->fetch_assoc();
        $id_user = $estudiante['id_user'];
        $stmt_get_user->close();

        if (!$id_user) {
            throw new Exception("No se pudo encontrar el usuario asociado al estudiante.");
        }

        // 2. Eliminar al estudiante. Las tablas relacionadas (matriculas, notas, etc.) se eliminarán en cascada.
        $stmt_estudiante = $conexion->prepare("DELETE FROM estudiantes WHERE id = ?");
        $stmt_estudiante->bind_param("i", $id_estudiante);
        $stmt_estudiante->execute();
        $stmt_estudiante->close();

        // 3. Eliminar el usuario asociado
        $stmt_user = $conexion->prepare("DELETE FROM users WHERE id = ?");
        $stmt_user->bind_param("i", $id_user);
        $stmt_user->execute();
        $stmt_user->close();

        $conexion->commit();
        $_SESSION['mensaje'] = "Estudiante eliminado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje'] = "Error al eliminar el estudiante: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_estudiantes.php");
    exit();
}

function editarEstudiante($conexion) {
    $id_estudiante = $_POST['id_estudiante'];
    $id_user = $_POST['id_user'];

    $conexion->begin_transaction();

    try {
        // 1. Actualizar datos del estudiante
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

        $stmt_estudiante = $conexion->prepare(
            "UPDATE estudiantes SET 
             codigo_estudiante = ?, dni = ?, apellido_paterno = ?, apellido_materno = ?, nombres = ?, 
             fecha_nacimiento = ?, sexo = ?, direccion = ?, telefono = ?, email = ?, estado = ?
             WHERE id = ?"
        );
        $stmt_estudiante->bind_param(
            "sssssssssssi",
            $codigo_estudiante, $dni, $apellido_paterno, $apellido_materno, $nombres,
            $fecha_nacimiento, $sexo, $direccion, $telefono, $email, $estado,
            $id_estudiante
        );
        $stmt_estudiante->execute();
        $stmt_estudiante->close();

        // 2. Actualizar contraseña si se proporcionó una nueva
        $password = $_POST['password'];
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt_user = $conexion->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt_user->bind_param("si", $password_hash, $id_user);
            $stmt_user->execute();
            $stmt_user->close();
        }

        $conexion->commit();
        $_SESSION['mensaje'] = "Estudiante actualizado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        $conexion->rollback();
        if ($e->getCode() == 1062) {
             if (strpos($e->getMessage(), 'uq_estudiantes_codigo') !== false) {
                $_SESSION['mensaje'] = "Error: El código de estudiante '{$codigo_estudiante}' ya existe.";
            } elseif (strpos($e->getMessage(), 'uq_estudiantes_dni') !== false) {
                $_SESSION['mensaje'] = "Error: El DNI '{$dni}' ya está registrado.";
            } elseif (strpos($e->getMessage(), 'uq_estudiantes_email') !== false) {
                $_SESSION['mensaje'] = "Error: El email '{$email}' ya está registrado.";
            } else {
                $_SESSION['mensaje'] = "Error al actualizar estudiante: Entrada duplicada no especificada.";
            }
        } else {
            $_SESSION['mensaje'] = "Error al actualizar estudiante: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_estudiantes.php");
    exit();
}

function agregarEstudiante($conexion) {
    // Iniciar transacción
    $conexion->begin_transaction();

    try {
        // 1. Crear el usuario
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $rol = 'estudiante';

        $stmt_user = $conexion->prepare("INSERT INTO users (username, password_hash, rol) VALUES (?, ?, ?)");
        $stmt_user->bind_param("sss", $username, $password_hash, $rol);
        $stmt_user->execute();
        $id_user = $conexion->insert_id;
        $stmt_user->close();

        // 2. Crear el estudiante
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

        $stmt_estudiante = $conexion->prepare(
            "INSERT INTO estudiantes (codigo_estudiante, dni, apellido_paterno, apellido_materno, nombres, fecha_nacimiento, sexo, direccion, telefono, email, id_user) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt_estudiante->bind_param(
            "ssssssssssi",
            $codigo_estudiante,
            $dni,
            $apellido_paterno,
            $apellido_materno,
            $nombres,
            $fecha_nacimiento,
            $sexo,
            $direccion,
            $telefono,
            $email,
            $id_user
        );
        $stmt_estudiante->execute();
        $stmt_estudiante->close();

        // Si todo fue bien, confirmar la transacción
        $conexion->commit();
        
        $_SESSION['mensaje'] = "Estudiante agregado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        // Si algo falla, revertir la transacción
        $conexion->rollback();

        // Manejar errores de duplicados
        if ($e->getCode() == 1062) { // Código de error para entrada duplicada
            if (strpos($e->getMessage(), 'uq_users_username') !== false) {
                $_SESSION['mensaje'] = "Error: El nombre de usuario '{$username}' ya existe.";
            } elseif (strpos($e->getMessage(), 'uq_estudiantes_codigo') !== false) {
                $_SESSION['mensaje'] = "Error: El código de estudiante '{$codigo_estudiante}' ya existe.";
            } elseif (strpos($e->getMessage(), 'uq_estudiantes_dni') !== false) {
                $_SESSION['mensaje'] = "Error: El DNI '{$dni}' ya está registrado.";
            } elseif (strpos($e->getMessage(), 'uq_estudiantes_email') !== false) {
                $_SESSION['mensaje'] = "Error: El email '{$email}' ya está registrado.";
            } else {
                $_SESSION['mensaje'] = "Error al agregar estudiante: Entrada duplicada no especificada.";
            }
        } else {
            $_SESSION['mensaje'] = "Error al agregar estudiante: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_estudiantes.php");
    exit();
}
?>
