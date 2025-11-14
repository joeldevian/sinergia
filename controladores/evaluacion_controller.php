<?php
session_start();
require_once '../config/conexion.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

switch ($accion) {
    case 'agregar':
        agregarEvaluacion($conexion);
        break;
    case 'editar':
        editarEvaluacion($conexion);
        break;
    case 'eliminar':
        eliminarEvaluacion($conexion);
        break;
}

function agregarEvaluacion($conexion) {
    try {
        $id_curso = $_POST['id_curso'];
        $nombre_evaluacion = $_POST['nombre_evaluacion'];
        $porcentaje = $_POST['porcentaje'];

        // Check if total percentage for the course exceeds 100%
        $stmt_check_percentage = $conexion->prepare("SELECT SUM(porcentaje) AS total_porcentaje FROM evaluaciones WHERE id_curso = ?");
        $stmt_check_percentage->bind_param("i", $id_curso);
        $stmt_check_percentage->execute();
        $resultado_percentage = $stmt_check_percentage->get_result();
        $row_percentage = $resultado_percentage->fetch_assoc();
        $current_total_percentage = $row_percentage['total_porcentaje'] ?? 0;
        $stmt_check_percentage->close();

        if (($current_total_percentage + $porcentaje) > 100) {
            $_SESSION['mensaje'] = "Error: El porcentaje total de las evaluaciones para este curso excedería el 100%.";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: ../vistas/admin/gestionar_evaluaciones.php");
            exit();
        }

        $stmt = $conexion->prepare(
            "INSERT INTO evaluaciones (id_curso, nombre_evaluacion, porcentaje) 
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param(
            "isd", // i for int, s for string, d for double/decimal
            $id_curso,
            $nombre_evaluacion,
            $porcentaje
        );
        $stmt->execute();
        $stmt->close();

        $_SESSION['mensaje'] = "Evaluación agregada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        $_SESSION['mensaje'] = "Error al agregar evaluación: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_evaluaciones.php");
    exit();
}

function editarEvaluacion($conexion) {
    $id_evaluacion = $_POST['id_evaluacion'];
    $id_curso = $_POST['id_curso']; // Needed for percentage check
    $nombre_evaluacion = $_POST['nombre_evaluacion'];
    $porcentaje = $_POST['porcentaje'];

    try {
        // Check if total percentage for the course exceeds 100% (excluding current evaluation's percentage)
        $stmt_check_percentage = $conexion->prepare("SELECT SUM(porcentaje) AS total_porcentaje FROM evaluaciones WHERE id_curso = ? AND id != ?");
        $stmt_check_percentage->bind_param("ii", $id_curso, $id_evaluacion);
        $stmt_check_percentage->execute();
        $resultado_percentage = $stmt_check_percentage->get_result();
        $row_percentage = $resultado_percentage->fetch_assoc();
        $current_total_percentage = $row_percentage['total_porcentaje'] ?? 0;
        $stmt_check_percentage->close();

        if (($current_total_percentage + $porcentaje) > 100) {
            $_SESSION['mensaje'] = "Error: El porcentaje total de las evaluaciones para este curso excedería el 100%.";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: ../vistas/admin/gestionar_evaluaciones.php");
            exit();
        }

        $stmt = $conexion->prepare(
            "UPDATE evaluaciones SET 
             id_curso = ?, nombre_evaluacion = ?, porcentaje = ?
             WHERE id = ?"
        );
        $stmt->bind_param(
            "isdi", // i for int, s for string, d for double/decimal, i for int
            $id_curso,
            $nombre_evaluacion,
            $porcentaje,
            $id_evaluacion
        );
        $stmt->execute();
        $stmt->close();

        $_SESSION['mensaje'] = "Evaluación actualizada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        $_SESSION['mensaje'] = "Error al actualizar evaluación: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_evaluaciones.php");
    exit();
}

function eliminarEvaluacion($conexion) {
    $id_evaluacion = $_GET['id'] ?? 0;

    if ($id_evaluacion == 0) {
        $_SESSION['mensaje'] = "ID de evaluación no válido.";
        $_SESSION['mensaje_tipo'] = "danger";
    } else {
        try {
            $stmt = $conexion->prepare("DELETE FROM evaluaciones WHERE id = ?");
            $stmt->bind_param("i", $id_evaluacion);
            $stmt->execute();
            $stmt->close();

            $_SESSION['mensaje'] = "Evaluación eliminada exitosamente.";
            $_SESSION['mensaje_tipo'] = "success";

        } catch (mysqli_sql_exception $e) {
            $_SESSION['mensaje'] = "Error al eliminar la evaluación: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
        }
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_evaluaciones.php");
    exit();
}
?>
