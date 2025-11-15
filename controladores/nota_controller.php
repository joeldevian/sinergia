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
    case 'guardar_notas':
        guardarNotas();
        break;
}

function guardarNotas() {
    global $conexion;
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
                $sql_check = "SELECT id FROM notas WHERE id_estudiante = ? AND id_evaluacion = ?";
                $existing_note = select_one($sql_check, "ii", [$id_estudiante, $id_evaluacion]);

                if ($existing_note) {
                    // Actualizar nota existente
                    $sql_update = "UPDATE notas SET nota = ?, fecha_registro = NOW(), registrado_por = ? WHERE id_estudiante = ? AND id_evaluacion = ?";
                    if (!execute_cud($sql_update, "diii", [$nota_float, $registrado_por, $id_estudiante, $id_evaluacion])) {
                        throw new Exception("Error al actualizar la nota para el estudiante {$id_estudiante} en la evaluación {$id_evaluacion}.");
                    }
                } else {
                    // Insertar nueva nota
                    $sql_insert = "INSERT INTO notas (id_estudiante, id_evaluacion, nota, registrado_por) VALUES (?, ?, ?, ?)";
                    if (!execute_cud($sql_insert, "iidi", [$id_estudiante, $id_evaluacion, $nota_float, $registrado_por])) {
                        throw new Exception("Error al insertar la nota para el estudiante {$id_estudiante} en la evaluación {$id_evaluacion}.");
                    }
                }
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

    header("Location: ../vistas/docente/gestionar_notas_curso.php?id_curso=" . $id_curso);
    exit();
}
?>