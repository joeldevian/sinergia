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
        agregarAsignacion($conexion);
        break;
    case 'eliminar':
        eliminarAsignacion($conexion);
        break;
}

function agregarAsignacion($conexion) {
    try {
        $id_docente = $_POST['id_docente'];
        $id_curso = $_POST['id_curso'];
        $periodo_academico = $_POST['periodo_academico'];

        $stmt = $conexion->prepare(
            "INSERT INTO docente_curso (id_docente, id_curso, periodo_academico) 
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param(
            "iis",
            $id_docente,
            $id_curso,
            $periodo_academico
        );
        $stmt->execute();
        $stmt->close();

        $_SESSION['mensaje'] = "Asignación creada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Error for duplicate entry
            $_SESSION['mensaje'] = "Error: Esta asignación (docente, curso, periodo) ya existe.";
        } else {
            $_SESSION['mensaje'] = "Error al crear la asignación: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_asignaciones.php");
    exit();
}

function eliminarAsignacion($conexion) {
    $id_asignacion = $_GET['id'] ?? 0;

    if ($id_asignacion == 0) {
        $_SESSION['mensaje'] = "ID de asignación no válido.";
        $_SESSION['mensaje_tipo'] = "danger";
    } else {
        try {
            $stmt = $conexion->prepare("DELETE FROM docente_curso WHERE id = ?");
            $stmt->bind_param("i", $id_asignacion);
            $stmt->execute();
            $stmt->close();

            $_SESSION['mensaje'] = "Asignación eliminada exitosamente.";
            $_SESSION['mensaje_tipo'] = "success";

        } catch (mysqli_sql_exception $e) {
            $_SESSION['mensaje'] = "Error al eliminar la asignación: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
        }
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_asignaciones.php");
    exit();
}
?>
