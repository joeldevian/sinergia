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
        agregarEvaluacion();
        break;
    case 'editar':
        editarEvaluacion();
        break;
    case 'eliminar':
        eliminarEvaluacion();
        break;
}

function agregarEvaluacion() {
    try {
        $id_curso = $_POST['id_curso'];
        $nombre_evaluacion = $_POST['nombre_evaluacion'];
        $porcentaje = $_POST['porcentaje'];

        // Check if total percentage for the course exceeds 100%
        $sql_check = "SELECT SUM(porcentaje) AS total_porcentaje FROM evaluaciones WHERE id_curso = ?";
        $row_percentage = select_one($sql_check, "i", [$id_curso]);
        $current_total_percentage = $row_percentage['total_porcentaje'] ?? 0;

        if (($current_total_percentage + $porcentaje) > 100) {
            $_SESSION['mensaje'] = "Error: El porcentaje total de las evaluaciones para este curso excedería el 100%.";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: ../vistas/admin/gestionar_evaluaciones.php");
            exit();
        }

        $sql_insert = "INSERT INTO evaluaciones (id_curso, nombre_evaluacion, porcentaje) VALUES (?, ?, ?)";
        if (!execute_cud($sql_insert, "isd", [$id_curso, $nombre_evaluacion, $porcentaje])) {
            throw new Exception("No se pudo agregar la evaluación.");
        }

        $_SESSION['mensaje'] = "Evaluación agregada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al agregar evaluación: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_evaluaciones.php");
    exit();
}

function editarEvaluacion() {
    try {
        $id_evaluacion = $_POST['id_evaluacion'];
        $id_curso = $_POST['id_curso']; // Needed for percentage check
        $nombre_evaluacion = $_POST['nombre_evaluacion'];
        $porcentaje = $_POST['porcentaje'];

        // Check if total percentage for the course exceeds 100% (excluding current evaluation's percentage)
        $sql_check = "SELECT SUM(porcentaje) AS total_porcentaje FROM evaluaciones WHERE id_curso = ? AND id != ?";
        $row_percentage = select_one($sql_check, "ii", [$id_curso, $id_evaluacion]);
        $current_total_percentage = $row_percentage['total_porcentaje'] ?? 0;

        if (($current_total_percentage + $porcentaje) > 100) {
            $_SESSION['mensaje'] = "Error: El porcentaje total de las evaluaciones para este curso excedería el 100%.";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: ../vistas/admin/gestionar_evaluaciones.php");
            exit();
        }

        $sql_update = "UPDATE evaluaciones SET id_curso = ?, nombre_evaluacion = ?, porcentaje = ? WHERE id = ?";
        if (!execute_cud($sql_update, "isdi", [$id_curso, $nombre_evaluacion, $porcentaje, $id_evaluacion])) {
            throw new Exception("No se pudo actualizar la evaluación.");
        }

        $_SESSION['mensaje'] = "Evaluación actualizada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al actualizar evaluación: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_evaluaciones.php");
    exit();
}

function eliminarEvaluacion() {
    $id_evaluacion = $_GET['id'] ?? 0;

    if ($id_evaluacion == 0) {
        $_SESSION['mensaje'] = "ID de evaluación no válido.";
        $_SESSION['mensaje_tipo'] = "danger";
    } else {
        try {
            $sql_delete = "DELETE FROM evaluaciones WHERE id = ?";
            if (!execute_cud($sql_delete, "i", [$id_evaluacion])) {
                throw new Exception("No se pudo eliminar la evaluación.");
            }

            $_SESSION['mensaje'] = "Evaluación eliminada exitosamente.";
            $_SESSION['mensaje_tipo'] = "success";

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al eliminar la evaluación: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
        }
    }

    header("Location: ../vistas/admin/gestionar_evaluaciones.php");
    exit();
}
?>