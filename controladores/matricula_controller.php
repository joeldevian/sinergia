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
        agregarMatricula($conexion);
        break;
    case 'editar':
        editarMatricula($conexion);
        break;
    case 'eliminar':
        eliminarMatricula($conexion);
        break;
}

function agregarMatricula($conexion) {
    try {
        $id_estudiante = $_POST['id_estudiante'];
        $id_curso = $_POST['id_curso'];
        $periodo_academico = $_POST['periodo_academico'];
        $fecha_matricula = $_POST['fecha_matricula'];

        // Check for duplicate enrollment
        $stmt_check = $conexion->prepare("SELECT COUNT(*) FROM matriculas WHERE id_estudiante = ? AND id_curso = ? AND periodo_academico = ?");
        $stmt_check->bind_param("iis", $id_estudiante, $id_curso, $periodo_academico);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            $_SESSION['mensaje'] = "Error: El estudiante ya está matriculado en este curso para el periodo académico seleccionado.";
            $_SESSION['mensaje_tipo'] = "danger";
            $conexion->close();
            header("Location: ../vistas/admin/gestionar_matriculas.php");
            exit();
        }

        $stmt = $conexion->prepare(
            "INSERT INTO matriculas (id_estudiante, id_curso, periodo_academico, fecha_matricula) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "iiss",
            $id_estudiante,
            $id_curso,
            $periodo_academico,
            $fecha_matricula
        );
        $stmt->execute();
        $stmt->close();

        $_SESSION['mensaje'] = "Matrícula agregada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        $_SESSION['mensaje'] = "Error al agregar matrícula: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_matriculas.php");
    exit();
}

function editarMatricula($conexion) {
    $id_matricula = $_POST['id_matricula'];

    try {
        $id_estudiante = $_POST['id_estudiante'];
        $id_curso = $_POST['id_curso'];
        $periodo_academico = $_POST['periodo_academico'];
        $fecha_matricula = $_POST['fecha_matricula'];
        $estado = $_POST['estado'];

        // Check for duplicate enrollment (excluding the current one being edited)
        $stmt_check = $conexion->prepare("SELECT COUNT(*) FROM matriculas WHERE id_estudiante = ? AND id_curso = ? AND periodo_academico = ? AND id != ?");
        $stmt_check->bind_param("iisi", $id_estudiante, $id_curso, $periodo_academico, $id_matricula);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            $_SESSION['mensaje'] = "Error: El estudiante ya está matriculado en este curso para el periodo académico seleccionado.";
            $_SESSION['mensaje_tipo'] = "danger";
            $conexion->close();
            header("Location: ../vistas/admin/gestionar_matriculas.php");
            exit();
        }

        $stmt = $conexion->prepare(
            "UPDATE matriculas SET 
             id_estudiante = ?, id_curso = ?, periodo_academico = ?, fecha_matricula = ?, estado = ?
             WHERE id = ?"
        );
        $stmt->bind_param(
            "iissii",
            $id_estudiante, $id_curso, $periodo_academico, $fecha_matricula, $estado,
            $id_matricula
        );
        $stmt->execute();
        $stmt->close();

        $_SESSION['mensaje'] = "Matrícula actualizada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        $_SESSION['mensaje'] = "Error al actualizar matrícula: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_matriculas.php");
    exit();
}

function eliminarMatricula($conexion) {
    $id_matricula = $_GET['id'] ?? 0;

    if ($id_matricula == 0) {
        $_SESSION['mensaje'] = "ID de matrícula no válido.";
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: ../vistas/admin/gestionar_matriculas.php");
        exit();
    }

    try {
        $stmt = $conexion->prepare("DELETE FROM matriculas WHERE id = ?");
        $stmt->bind_param("i", $id_matricula);
        $stmt->execute();
        $stmt->close();

        $_SESSION['mensaje'] = "Matrícula eliminada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        $_SESSION['mensaje'] = "Error al eliminar la matrícula: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_matriculas.php");
    exit();
}

function agregarMatricula($conexion) {
    try {
        $id_estudiante = $_POST['id_estudiante'];
        $id_curso = $_POST['id_curso'];
        $periodo_academico = $_POST['periodo_academico'];
        $fecha_matricula = $_POST['fecha_matricula'];

        // Check for duplicate enrollment
        $stmt_check = $conexion->prepare("SELECT COUNT(*) FROM matriculas WHERE id_estudiante = ? AND id_curso = ? AND periodo_academico = ?");
        $stmt_check->bind_param("iis", $id_estudiante, $id_curso, $periodo_academico);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            $_SESSION['mensaje'] = "Error: El estudiante ya está matriculado en este curso para el periodo académico seleccionado.";
            $_SESSION['mensaje_tipo'] = "danger";
            $conexion->close();
            header("Location: ../vistas/admin/gestionar_matriculas.php");
            exit();
        }

        $stmt = $conexion->prepare(
            "INSERT INTO matriculas (id_estudiante, id_curso, periodo_academico, fecha_matricula) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "iiss",
            $id_estudiante,
            $id_curso,
            $periodo_academico,
            $fecha_matricula
        );
        $stmt->execute();
        $stmt->close();

        $_SESSION['mensaje'] = "Matrícula agregada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        $_SESSION['mensaje'] = "Error al agregar matrícula: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_matriculas.php");
    exit();
}
?>
