<?php
session_start();
require_once '../config/database.php'; // Use the new database functions

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../index.php");
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

// The global $conexion is still needed for transactions
global $conexion;

switch ($accion) {
    case 'guardar_asistencia':
        guardarAsistencia();
        break;
}

function guardarAsistencia() {
    global $conexion;
    $id_curso = filter_var($_POST['id_curso'] ?? '', FILTER_VALIDATE_INT);
    $fecha = $_POST['fecha'] ?? '';
    $asistencia_enviada = $_POST['asistencia'] ?? [];
    $registrado_por = $_SESSION['user_id'];

    $errors = [];

    if ($id_curso === false || $id_curso <= 0) $errors[] = "ID de curso no válido.";
    if (empty($fecha)) $errors[] = "La fecha es requerida.";
    // Basic date format validation (YYYY-MM-DD)
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha)) $errors[] = "Formato de fecha no válido.";

    if (!is_array($asistencia_enviada) || empty($asistencia_enviada)) {
        $errors[] = "No se recibieron datos de asistencia.";
    } else {
        $allowed_estados = ['presente', 'ausente', 'tardanza']; // Define allowed attendance states
        foreach ($asistencia_enviada as $id_estudiante_str => $estado) {
            $id_estudiante = filter_var($id_estudiante_str, FILTER_VALIDATE_INT);
            if ($id_estudiante === false || $id_estudiante <= 0) {
                $errors[] = "ID de estudiante no válido en los datos de asistencia.";
            }
            if (!in_array($estado, $allowed_estados)) {
                $errors[] = "Estado de asistencia no válido para el estudiante {$id_estudiante_str}.";
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['mensaje'] = "Errores de validación: " . implode("<br>", $errors);
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: ../vistas/docente/mis_cursos.php"); // Redirect to a general page or specific error page
        exit();
    }

    $conexion->begin_transaction();

    try {
        foreach ($asistencia_enviada as $id_estudiante_str => $estado) {
            $id_estudiante = (int)$id_estudiante_str; // Already validated as int

            // Verificar si el registro de asistencia ya existe
            $sql_check = "SELECT id FROM asistencia WHERE id_estudiante = ? AND id_curso = ? AND fecha = ?";
            $existing_attendance = select_one($sql_check, "iis", [$id_estudiante, $id_curso, $fecha]);

            if ($existing_attendance) {
                // Actualizar asistencia existente
                $sql_update = "UPDATE asistencia SET estado = ?, registrado_por = ? WHERE id_estudiante = ? AND id_curso = ? AND fecha = ?";
                if (!execute_cud($sql_update, "siiss", [$estado, $registrado_por, $id_estudiante, $id_curso, $fecha])) {
                    throw new Exception("Error al actualizar la asistencia para el estudiante {$id_estudiante}.");
                }
            } else {
                // Insertar nuevo registro de asistencia
                $sql_insert = "INSERT INTO asistencia (id_estudiante, id_curso, fecha, estado, registrado_por) VALUES (?, ?, ?, ?, ?)";
                if (!execute_cud($sql_insert, "iissi", [$id_estudiante, $id_curso, $fecha, $estado, $registrado_por])) {
                    throw new Exception("Error al insertar la asistencia para el estudiante {$id_estudiante}.");
                }
            }
        }

        $conexion->commit();
        $_SESSION['mensaje'] = "Asistencia guardada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje'] = "Error al guardar asistencia: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/docente/gestionar_asistencia_curso.php?id_curso=" . $id_curso . "&fecha=" . $fecha);
    exit();
}
?>