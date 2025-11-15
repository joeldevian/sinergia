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
        $id_docente = $_POST['id_docente'];
        $id_curso = $_POST['id_curso'];
        $periodo_academico = $_POST['periodo_academico'];

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