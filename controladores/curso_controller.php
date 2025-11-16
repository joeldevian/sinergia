<?php
session_start();
require_once '../config/database.php'; // Use the new database functions

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {
    case 'agregar':
        agregarCurso();
        break;
    case 'editar':
        editarCurso();
        break;
    case 'eliminar':
        eliminarCurso();
        break;
}

function agregarCurso() {
    try {
        // 1. Validate input data
        $codigo_curso = trim($_POST['codigo_curso'] ?? '');
        $nombre_curso = trim($_POST['nombre_curso'] ?? '');
        $creditos = filter_var($_POST['creditos'] ?? '', FILTER_VALIDATE_INT);
        $horas_semanales = filter_var($_POST['horas_semanales'] ?? '', FILTER_VALIDATE_INT);
        $id_carrera = filter_var($_POST['id_carrera'] ?? '', FILTER_VALIDATE_INT);
        $ciclo = trim($_POST['ciclo'] ?? '');
        $tipo = $_POST['tipo'] ?? '';

        $errors = [];

        if (empty($codigo_curso)) $errors[] = "El código del curso es requerido.";
        if (empty($nombre_curso)) $errors[] = "El nombre del curso es requerido.";
        if ($creditos === false || $creditos <= 0) $errors[] = "Los créditos deben ser un número entero positivo.";
        if ($horas_semanales === false || $horas_semanales <= 0) $errors[] = "Las horas semanales deben ser un número entero positivo.";
        if ($id_carrera === false || $id_carrera <= 0) $errors[] = "Debe seleccionar una carrera válida.";
        if (empty($ciclo)) $errors[] = "El ciclo es requerido.";
        if (!in_array($tipo, ['obligatorio', 'electivo'])) $errors[] = "El tipo de curso no es válido.";
        
        // Check if id_carrera exists
        if ($id_carrera !== false && $id_carrera > 0) {
            $carrera_exists = select_one("SELECT id FROM carreras WHERE id = ?", "i", [$id_carrera]);
            if (!$carrera_exists) {
                $errors[] = "La carrera seleccionada no existe.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['mensaje'] = "Errores de validación: " . implode("<br>", $errors);
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: ../vistas/admin/gestionar_cursos.php"); // Or redirect back to form with old data
            exit();
        }

        $sql = "INSERT INTO cursos (codigo_curso, nombre_curso, creditos, horas_semanales, id_carrera, ciclo, tipo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $codigo_curso, $nombre_curso, $creditos, $horas_semanales,
            $id_carrera, $ciclo, $tipo
        ];

        if (!execute_cud($sql, "ssiiiss", $params)) {
            throw new Exception("No se pudo agregar el curso.");
        }

        $_SESSION['mensaje'] = "Curso agregado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        if (method_exists($e, 'getCode') && $e->getCode() == 1062) {
            $_SESSION['mensaje'] = "Error: El código de curso '{$_POST['codigo_curso']}' ya existe.";
        } else {
            $_SESSION['mensaje'] = "Error al agregar curso: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_cursos.php");
    exit();
}

function editarCurso() {
    try {
        // 1. Validate input data
        $id_curso = filter_var($_POST['id_curso'] ?? '', FILTER_VALIDATE_INT);
        $codigo_curso = trim($_POST['codigo_curso'] ?? '');
        $nombre_curso = trim($_POST['nombre_curso'] ?? '');
        $creditos = filter_var($_POST['creditos'] ?? '', FILTER_VALIDATE_INT);
        $horas_semanales = filter_var($_POST['horas_semanales'] ?? '', FILTER_VALIDATE_INT);
        $id_carrera = filter_var($_POST['id_carrera'] ?? '', FILTER_VALIDATE_INT);
        $ciclo = trim($_POST['ciclo'] ?? '');
        $tipo = $_POST['tipo'] ?? '';
        $estado = $_POST['estado'] ?? '';

        $errors = [];

        if ($id_curso === false || $id_curso <= 0) $errors[] = "ID de curso no válido.";
        if (empty($codigo_curso)) $errors[] = "El código del curso es requerido.";
        if (empty($nombre_curso)) $errors[] = "El nombre del curso es requerido.";
        if ($creditos === false || $creditos <= 0) $errors[] = "Los créditos deben ser un número entero positivo.";
        if ($horas_semanales === false || $horas_semanales <= 0) $errors[] = "Las horas semanales deben ser un número entero positivo.";
        if ($id_carrera === false || $id_carrera <= 0) $errors[] = "Debe seleccionar una carrera válida.";
        if (empty($ciclo)) $errors[] = "El ciclo es requerido.";
        if (!in_array($tipo, ['obligatorio', 'electivo'])) $errors[] = "El tipo de curso no es válido.";
        if (!in_array($estado, ['activo', 'inactivo'])) $errors[] = "El estado del curso no es válido.";
        
        // Check if id_carrera exists
        if ($id_carrera !== false && $id_carrera > 0) {
            $carrera_exists = select_one("SELECT id FROM carreras WHERE id = ?", "i", [$id_carrera]);
            if (!$carrera_exists) {
                $errors[] = "La carrera seleccionada no existe.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['mensaje'] = "Errores de validación: " . implode("<br>", $errors);
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: ../vistas/admin/gestionar_cursos.php"); // Or redirect back to form with old data
            exit();
        }

        $sql = "UPDATE cursos SET 
                codigo_curso = ?, nombre_curso = ?, creditos = ?, horas_semanales = ?, 
                id_carrera = ?, ciclo = ?, tipo = ?, estado = ?
                WHERE id = ?";
        
        $params = [
            $codigo_curso, $nombre_curso, $creditos, $horas_semanales,
            $id_carrera, $ciclo, $tipo, $estado, $id_curso
        ];

        if (!execute_cud($sql, "ssiiisssi", $params)) {
            throw new Exception("No se pudo actualizar el curso.");
        }

        $_SESSION['mensaje'] = "Curso actualizado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        if (method_exists($e, 'getCode') && $e->getCode() == 1062) {
            $_SESSION['mensaje'] = "Error: El código de curso '{$_POST['codigo_curso']}' ya existe.";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar curso: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_cursos.php");
    exit();
}

function eliminarCurso() {
    $id_curso = $_GET['id'] ?? 0;

    if ($id_curso == 0) {
        $_SESSION['mensaje'] = "ID de curso no válido.";
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: ../vistas/admin/gestionar_cursos.php");
        exit();
    }

    try {
        $sql = "DELETE FROM cursos WHERE id = ?";
        if (!execute_cud($sql, "i", [$id_curso])) {
            throw new Exception("No se pudo eliminar el curso.");
        }

        $_SESSION['mensaje'] = "Curso eliminado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al eliminar el curso: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_cursos.php");
    exit();
}
?>