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
        agregarAsignacion();
        break;
    case 'eliminar':
        eliminarAsignacion();
        break;
}

function agregarAsignacion() {
    try {
        // 1. Validate input data
        $id_docente = filter_var($_POST['id_docente'] ?? '', FILTER_VALIDATE_INT);
        $id_curso = filter_var($_POST['id_curso'] ?? '', FILTER_VALIDATE_INT);
        $periodo_academico = trim($_POST['periodo_academico'] ?? '');

        $errors = [];

        if ($id_docente === false || $id_docente <= 0) $errors[] = "Debe seleccionar un docente válido.";
        if ($id_curso === false || $id_curso <= 0) $errors[] = "Debe seleccionar un curso válido.";
        if (empty($periodo_academico)) $errors[] = "El periodo académico es requerido.";
        // Example: Validate academic period format (e.g., 2025-I, 2025-II)
        if (!preg_match('/^\d{4}-[I|II]$/', $periodo_academico)) $errors[] = "El formato del periodo académico no es válido (ej. 2025-I).";

        // Check if docente exists
        if ($id_docente !== false && $id_docente > 0) {
            $docente_exists = select_one("SELECT id FROM docentes WHERE id = ?", "i", [$id_docente]);
            if (!$docente_exists) {
                $errors[] = "El docente seleccionado no existe.";
            }
        }

        // Check if curso exists
        if ($id_curso !== false && $id_curso > 0) {
            $curso_exists = select_one("SELECT id FROM cursos WHERE id = ?", "i", [$id_curso]);
            if (!$curso_exists) {
                $errors[] = "El curso seleccionado no existe.";
            }
        }

        if (!empty($errors)) {
            $_SESSION['mensaje'] = "Errores de validación: " . implode("<br>", $errors);
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: ../vistas/admin/gestionar_asignaciones.php");
            exit();
        }

        $sql = "INSERT INTO docente_curso (id_docente, id_curso, periodo_academico) VALUES (?, ?, ?)";
        
        if (!execute_cud($sql, "iis", [$id_docente, $id_curso, $periodo_academico])) {
            throw new Exception("No se pudo crear la asignación.");
        }

        $_SESSION['mensaje'] = "Asignación creada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        if (method_exists($e, 'getCode') && $e->getCode() == 1062) { // Error for duplicate entry
            $_SESSION['mensaje'] = "Error: Esta asignación (docente, curso, periodo) ya existe.";
        } else {
            $_SESSION['mensaje'] = "Error al crear la asignación: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_asignaciones.php");
    exit();
}

function eliminarAsignacion() {
    $id_asignacion = $_GET['id'] ?? 0;

    if ($id_asignacion == 0) {
        $_SESSION['mensaje'] = "ID de asignación no válido.";
        $_SESSION['mensaje_tipo'] = "danger";
    } else {
        try {
            $sql = "DELETE FROM docente_curso WHERE id = ?";
            if (!execute_cud($sql, "i", [$id_asignacion])) {
                throw new Exception("No se pudo eliminar la asignación.");
            }

            $_SESSION['mensaje'] = "Asignación eliminada exitosamente.";
            $_SESSION['mensaje_tipo'] = "success";

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al eliminar la asignación: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
        }
    }

    header("Location: ../vistas/admin/gestionar_asignaciones.php");
    exit();
}
?>