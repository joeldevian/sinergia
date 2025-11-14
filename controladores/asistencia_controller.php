<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../index.php");
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {
    case 'guardar_asistencia':
        guardarAsistencia($conexion);
        break;
}

function guardarAsistencia($conexion) {
    $id_curso = $_POST['id_curso'] ?? 0;
    $fecha = $_POST['fecha'] ?? '';
    $asistencia_enviada = $_POST['asistencia'] ?? [];
    $registrado_por = $_SESSION['user_id'];

    if ($id_curso == 0 || empty($fecha)) {
        $_SESSION['mensaje'] = "Datos incompletos para guardar asistencia.";
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: ../vistas/docente/mis_cursos.php");
        exit();
    }

    $conexion->begin_transaction();

    try {
        foreach ($asistencia_enviada as $id_estudiante => $estado) {
            // Verificar si el registro de asistencia ya existe
            $stmt_check = $conexion->prepare("SELECT id FROM asistencia WHERE id_estudiante = ? AND id_curso = ? AND fecha = ?");
            $stmt_check->bind_param("iis", $id_estudiante, $id_curso, $fecha);
            $stmt_check->execute();
            $resultado_check = $stmt_check->get_result();

            if ($resultado_check->num_rows > 0) {
                // Actualizar asistencia existente
                $stmt_update = $conexion->prepare("UPDATE asistencia SET estado = ?, registrado_por = ? WHERE id_estudiante = ? AND id_curso = ? AND fecha = ?");
                $stmt_update->bind_param("siiss", $estado, $registrado_por, $id_estudiante, $id_curso, $fecha);
                $stmt_update->execute();
                $stmt_update->close();
            } else {
                // Insertar nuevo registro de asistencia
                $stmt_insert = $conexion->prepare("INSERT INTO asistencia (id_estudiante, id_curso, fecha, estado, registrado_por) VALUES (?, ?, ?, ?, ?)");
                $stmt_insert->bind_param("iissi", $id_estudiante, $id_curso, $fecha, $estado, $registrado_por);
                $stmt_insert->execute();
                $stmt_insert->close();
            }
            $stmt_check->close();
        }

        $conexion->commit();
        $_SESSION['mensaje'] = "Asistencia guardada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje'] = "Error al guardar asistencia: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/docente/gestionar_asistencia_curso.php?id_curso=" . $id_curso . "&fecha=" . $fecha);
    exit();
}
?>
