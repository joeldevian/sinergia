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
        agregarMatricula();
        break;
    case 'editar':
        editarMatricula();
        break;
    case 'eliminar':
        eliminarMatricula();
        break;
}

function agregarMatricula() {
    try {
        $id_estudiante = $_POST['id_estudiante'];
        $id_cursos = $_POST['id_cursos'] ?? [];
        $periodo_academico = $_POST['periodo_academico'];
        $fecha_matricula = $_POST['fecha_matricula'];

        if (empty($id_cursos)) {
            throw new Exception("Debe seleccionar al menos un curso.");
        }

        $cursos_agregados_ids = [];
        $cursos_omitidos_ids = [];

        foreach ($id_cursos as $id_curso) {
            // Check for duplicate enrollment
            $sql_check = "SELECT COUNT(*) as count FROM matriculas WHERE id_estudiante = ? AND id_curso = ? AND periodo_academico = ?";
            $result = select_one($sql_check, "iis", [$id_estudiante, $id_curso, $periodo_academico]);

            if ($result && $result['count'] > 0) {
                $cursos_omitidos_ids[] = $id_curso;
                continue; // Skip to the next course
            }

            // Insert new enrollment
            $sql_insert = "INSERT INTO matriculas (id_estudiante, id_curso, periodo_academico, fecha_matricula) 
                           VALUES (?, ?, ?, ?)";
            if (execute_cud($sql_insert, "iiss", [$id_estudiante, $id_curso, $periodo_academico, $fecha_matricula])) {
                $cursos_agregados_ids[] = $id_curso;
            }
        }

        $mensaje = "Operación completada. Matrículas agregadas: " . count($cursos_agregados_ids) . ".";
        if (!empty($cursos_omitidos_ids)) {
            $mensaje .= " Cursos omitidos por duplicado: " . count($cursos_omitidos_ids) . ".";
        }

        // Store summary in session for the confirmation page
        $_SESSION['matricula_summary'] = [
            'id_estudiante' => $id_estudiante,
            'periodo_academico' => $periodo_academico,
            'fecha_matricula' => $fecha_matricula,
            'cursos_matriculados_ids' => $cursos_agregados_ids,
            'mensaje' => $mensaje,
            'mensaje_tipo' => (count($cursos_agregados_ids) > 0) ? "success" : "warning"
        ];
        
        header("Location: ../vistas/admin/confirmacion_matricula.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al agregar matrícula: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: ../vistas/admin/gestionar_matriculas.php");
        exit();
    }
}

function editarMatricula() {
    try {
        $id_matricula = $_POST['id_matricula'];
        $id_estudiante = $_POST['id_estudiante'];
        $id_curso = $_POST['id_curso'];
        $periodo_academico = $_POST['periodo_academico'];
        $fecha_matricula = $_POST['fecha_matricula'];
        $estado = $_POST['estado'];

        // Check for duplicate enrollment (excluding the current one being edited)
        $sql_check = "SELECT COUNT(*) as count FROM matriculas WHERE id_estudiante = ? AND id_curso = ? AND periodo_academico = ? AND id != ?";
        $result = select_one($sql_check, "iisi", [$id_estudiante, $id_curso, $periodo_academico, $id_matricula]);

        if ($result && $result['count'] > 0) {
            $_SESSION['mensaje'] = "Error: El estudiante ya está matriculado en este curso para el periodo académico seleccionado.";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: ../vistas/admin/gestionar_matriculas.php");
            exit();
        }

        $sql_update = "UPDATE matriculas SET 
                       id_estudiante = ?, id_curso = ?, periodo_academico = ?, fecha_matricula = ?, estado = ?
                       WHERE id = ?";
        $params = [$id_estudiante, $id_curso, $periodo_academico, $fecha_matricula, $estado, $id_matricula];
        
        if (!execute_cud($sql_update, "iissii", $params)) {
            throw new Exception("No se pudo actualizar la matrícula.");
        }

        $_SESSION['mensaje'] = "Matrícula actualizada exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al actualizar matrícula: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_matriculas.php");
    exit();
}

function eliminarMatricula() {
    $id_matricula = $_GET['id'] ?? 0;

    if ($id_matricula == 0) {
        $_SESSION['mensaje'] = "ID de matrícula no válido.";
        $_SESSION['mensaje_tipo'] = "danger";
    } else {
        try {
            $sql_delete = "DELETE FROM matriculas WHERE id = ?";
            if (!execute_cud($sql_delete, "i", [$id_matricula])) {
                throw new Exception("No se pudo eliminar la matrícula.");
            }
            $_SESSION['mensaje'] = "Matrícula eliminada exitosamente.";
            $_SESSION['mensaje_tipo'] = "success";
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "Error al eliminar la matrícula: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
        }
    }

    header("Location: ../vistas/admin/gestionar_matriculas.php");
    exit();
}
?>