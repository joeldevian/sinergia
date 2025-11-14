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
        agregarDocente($conexion);
        break;
    case 'editar':
        editarDocente($conexion);
        break;
    case 'eliminar':
        eliminarDocente($conexion);
        break;
}

function agregarDocente($conexion) {
    // Iniciar transacción
    $conexion->begin_transaction();

    try {
        // 1. Crear el usuario
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $rol = 'docente';

        $stmt_user = $conexion->prepare("INSERT INTO users (username, password_hash, rol) VALUES (?, ?, ?)");
        $stmt_user->bind_param("sss", $username, $password_hash, $rol);
        $stmt_user->execute();
        $id_user = $conexion->insert_id;
        $stmt_user->close();

        // 2. Crear el docente
        $codigo_docente = $_POST['codigo_docente'];
        $dni = $_POST['dni'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $nombres = $_POST['nombres'];
        $especialidad = $_POST['especialidad'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];

        $stmt_docente = $conexion->prepare(
            "INSERT INTO docentes (codigo_docente, dni, apellido_paterno, apellido_materno, nombres, especialidad, telefono, email, id_user) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt_docente->bind_param(
            "ssssssssi",
            $codigo_docente,
            $dni,
            $apellido_paterno,
            $apellido_materno,
            $nombres,
            $especialidad,
            $telefono,
            $email,
            $id_user
        );
        $stmt_docente->execute();
        $stmt_docente->close();

        // Si todo fue bien, confirmar la transacción
        $conexion->commit();
        
        $_SESSION['mensaje'] = "Docente agregado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        // Si algo falla, revertir la transacción
        $conexion->rollback();

        // Manejar errores de duplicados
        if ($e->getCode() == 1062) { // Código de error para entrada duplicada
            if (strpos($e->getMessage(), 'uq_users_username') !== false) {
                $_SESSION['mensaje'] = "Error: El nombre de usuario '{$username}' ya existe.";
            } elseif (strpos($e->getMessage(), 'uq_docentes_codigo') !== false) {
                $_SESSION['mensaje'] = "Error: El código de docente '{$codigo_docente}' ya existe.";
            } elseif (strpos($e->getMessage(), 'uq_docentes_dni') !== false) {
                $_SESSION['mensaje'] = "Error: El DNI '{$dni}' ya está registrado.";
            } elseif (strpos($e->getMessage(), 'uq_docentes_email') !== false) {
                $_SESSION['mensaje'] = "Error: El email '{$email}' ya está registrado.";
            } else {
                $_SESSION['mensaje'] = "Error al agregar docente: Entrada duplicada no especificada.";
            }
        } else {
            $_SESSION['mensaje'] = "Error al agregar docente: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_docentes.php");
    exit();
}

function editarDocente($conexion) {
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

        $stmt_docente = $conexion->prepare(
            "UPDATE docentes SET 
             codigo_docente = ?, dni = ?, apellido_paterno = ?, apellido_materno = ?, nombres = ?, 
             especialidad = ?, telefono = ?, email = ?, estado = ?
             WHERE id = ?"
        );
        $stmt_docente->bind_param(
            "sssssssssi",
            $codigo_docente, $dni, $apellido_paterno, $apellido_materno, $nombres,
            $especialidad, $telefono, $email, $estado,
            $id_docente
        );
        $stmt_docente->execute();
        $stmt_docente->close();

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
        $_SESSION['mensaje'] = "Docente actualizado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        $conexion->rollback();
        if ($e->getCode() == 1062) {
             if (strpos($e->getMessage(), 'uq_docentes_codigo') !== false) {
                $_SESSION['mensaje'] = "Error: El código de docente '{$codigo_docente}' ya existe.";
            } elseif (strpos($e->getMessage(), 'uq_docentes_dni') !== false) {
                $_SESSION['mensaje'] = "Error: El DNI '{$dni}' ya está registrado.";
            } elseif (strpos($e->getMessage(), 'uq_docentes_email') !== false) {
                $_SESSION['mensaje'] = "Error: El email '{$email}' ya está registrado.";
            } else {
                $_SESSION['mensaje'] = "Error al actualizar docente: Entrada duplicada no especificada.";
            }
        } else {
            $_SESSION['mensaje'] = "Error al actualizar docente: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_docentes.php");
    exit();
}

function eliminarDocente($conexion) {
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
        $stmt_get_user = $conexion->prepare("SELECT id_user FROM docentes WHERE id = ?");
        $stmt_get_user->bind_param("i", $id_docente);
        $stmt_get_user->execute();
        $resultado = $stmt_get_user->get_result();
        $docente = $resultado->fetch_assoc();
        $id_user = $docente['id_user'];
        $stmt_get_user->close();

        if (!$id_user) {
            throw new Exception("No se pudo encontrar el usuario asociado al docente.");
        }

        // 2. Eliminar al docente.
        $stmt_docente = $conexion->prepare("DELETE FROM docentes WHERE id = ?");
        $stmt_docente->bind_param("i", $id_docente);
        $stmt_docente->execute();
        $stmt_docente->close();

        // 3. Eliminar el usuario asociado
        $stmt_user = $conexion->prepare("DELETE FROM users WHERE id = ?");
        $stmt_user->bind_param("i", $id_user);
        $stmt_user->execute();
        $stmt_user->close();

        $conexion->commit();
        $_SESSION['mensaje'] = "Docente eliminado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje'] = "Error al eliminar el docente: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_docentes.php");
    exit();
}

function agregarDocente($conexion) {
    // Iniciar transacción
    $conexion->begin_transaction();

    try {
        // 1. Crear el usuario
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $rol = 'docente';

        $stmt_user = $conexion->prepare("INSERT INTO users (username, password_hash, rol) VALUES (?, ?, ?)");
        $stmt_user->bind_param("sss", $username, $password_hash, $rol);
        $stmt_user->execute();
        $id_user = $conexion->insert_id;
        $stmt_user->close();

        // 2. Crear el docente
        $codigo_docente = $_POST['codigo_docente'];
        $dni = $_POST['dni'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $nombres = $_POST['nombres'];
        $especialidad = $_POST['especialidad'];
        $telefono = $_POST['telefono'];
        $email = $_POST['email'];

        $stmt_docente = $conexion->prepare(
            "INSERT INTO docentes (codigo_docente, dni, apellido_paterno, apellido_materno, nombres, especialidad, telefono, email, id_user) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt_docente->bind_param(
            "ssssssssi",
            $codigo_docente,
            $dni,
            $apellido_paterno,
            $apellido_materno,
            $nombres,
            $especialidad,
            $telefono,
            $email,
            $id_user
        );
        $stmt_docente->execute();
        $stmt_docente->close();

        // Si todo fue bien, confirmar la transacción
        $conexion->commit();
        
        $_SESSION['mensaje'] = "Docente agregado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        // Si algo falla, revertir la transacción
        $conexion->rollback();

        // Manejar errores de duplicados
        if ($e->getCode() == 1062) { // Código de error para entrada duplicada
            if (strpos($e->getMessage(), 'uq_users_username') !== false) {
                $_SESSION['mensaje'] = "Error: El nombre de usuario '{$username}' ya existe.";
            } elseif (strpos($e->getMessage(), 'uq_docentes_codigo') !== false) {
                $_SESSION['mensaje'] = "Error: El código de docente '{$codigo_docente}' ya existe.";
            } elseif (strpos($e->getMessage(), 'uq_docentes_dni') !== false) {
                $_SESSION['mensaje'] = "Error: El DNI '{$dni}' ya está registrado.";
            } elseif (strpos($e->getMessage(), 'uq_docentes_email') !== false) {
                $_SESSION['mensaje'] = "Error: El email '{$email}' ya está registrado.";
            } else {
                $_SESSION['mensaje'] = "Error al agregar docente: Entrada duplicada no especificada.";
            }
        } else {
            $_SESSION['mensaje'] = "Error al agregar docente: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_docentes.php");
    exit();
}
?>
