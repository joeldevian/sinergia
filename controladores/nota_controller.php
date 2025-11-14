<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../index.php");
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {
    case 'guardar_notas':
        guardarNotas($conexion);
        break;
}

function guardarNotas($conexion) {
    $id_curso = $_POST['id_curso'] ?? 0;
    $notas_enviadas = $_POST['notas'] ?? [];
    $registrado_por = $_SESSION['user_id'];

    if ($id_curso == 0) {
        $_SESSION['mensaje'] = "ID de curso no válido para guardar notas.";
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: ../vistas/docente/mis_cursos.php");
        exit();
    }

    $conexion->begin_transaction();

    try {
        foreach ($notas_enviadas as $id_estudiante => $evaluaciones) {
            foreach ($evaluaciones as $id_evaluacion => $nota) {
                // Convertir la nota a float y validar
                $nota_float = floatval($nota);
                if ($nota_float < 0 || $nota_float > 20) {
                    throw new Exception("La nota para el estudiante {$id_estudiante} en la evaluación {$id_evaluacion} debe estar entre 0 y 20.");
                }

                // Verificar si la nota ya existe
                $stmt_check = $conexion->prepare("SELECT id FROM notas WHERE id_estudiante = ? AND id_evaluacion = ?");
                $stmt_check->bind_param("ii", $id_estudiante, $id_evaluacion);
                $stmt_check->execute();
                $resultado_check = $stmt_check->get_result();

                if ($resultado_check->num_rows > 0) {
                    // Actualizar nota existente
                    $stmt_update = $conexion->prepare("UPDATE notas SET nota = ?, fecha_registro = NOW(), registrado_por = ? WHERE id_estudiante = ? AND id_evaluacion = ?");
                    $stmt_update->bind_param("diii", $nota_float, $registrado_por, $id_estudiante, $id_evaluacion);
                    $stmt_update->execute();
                    $stmt_update->close();
                } else {
                    // Insertar nueva nota
                    $stmt_insert = $conexion->prepare("INSERT INTO notas (id_estudiante, id_evaluacion, nota, registrado_por) VALUES (?, ?, ?, ?)");
                    $stmt_insert->bind_param("iidi", $id_estudiante, $id_evaluacion, $nota_float, $registrado_por);
                    $stmt_insert->execute();
                    $stmt_insert->close();
                }
                $stmt_check->close();
            }
        }

        $conexion->commit();
        $_SESSION['mensaje'] = "Notas guardadas exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensaje'] = "Error al guardar notas: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/docente/gestionar_notas_curso.php?id_curso=" . $id_curso);
    exit();
}
?>
