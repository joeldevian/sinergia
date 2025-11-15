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
        agregarCurso();
        break;
    case 'editar':
        editarCurso();
        break;
    case 'eliminar':
        eliminarCurso();
        break;
}

function agregarCurso() {
    try {
        $codigo_curso = $_POST['codigo_curso'];
        $nombre_curso = $_POST['nombre_curso'];
        $creditos = $_POST['creditos'];
        $horas_semanales = $_POST['horas_semanales'];
        $id_carrera = $_POST['id_carrera'];
        $ciclo = $_POST['ciclo'];
        $tipo = $_POST['tipo'];

        $sql = "INSERT INTO cursos (codigo_curso, nombre_curso, creditos, horas_semanales, id_carrera, ciclo, tipo) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $codigo_curso, $nombre_curso, $creditos, $horas_semanales,
            $id_carrera, $ciclo, $tipo
        ];

        if (!execute_cud($sql, "ssiiiss", $params)) {
            throw new Exception("No se pudo agregar el curso.");
        }

        $_SESSION['mensaje'] = "Curso agregado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        if (method_exists($e, 'getCode') && $e->getCode() == 1062) {
            $_SESSION['mensaje'] = "Error: El código de curso '{$_POST['codigo_curso']}' ya existe.";
        } else {
            $_SESSION['mensaje'] = "Error al agregar curso: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_cursos.php");
    exit();
}

function editarCurso() {
    try {
        $id_curso = $_POST['id_curso'];
        $codigo_curso = $_POST['codigo_curso'];
        $nombre_curso = $_POST['nombre_curso'];
        $creditos = $_POST['creditos'];
        $horas_semanales = $_POST['horas_semanales'];
        $id_carrera = $_POST['id_carrera'];
        $ciclo = $_POST['ciclo'];
        $tipo = $_POST['tipo'];
        $estado = $_POST['estado'];

        $sql = "UPDATE cursos SET 
                codigo_curso = ?, nombre_curso = ?, creditos = ?, horas_semanales = ?, 
                id_carrera = ?, ciclo = ?, tipo = ?, estado = ?
                WHERE id = ?";
        
        $params = [
            $codigo_curso, $nombre_curso, $creditos, $horas_semanales,
            $id_carrera, $ciclo, $tipo, $estado, $id_curso
        ];

        if (!execute_cud($sql, "ssiiisssi", $params)) {
            throw new Exception("No se pudo actualizar el curso.");
        }

        $_SESSION['mensaje'] = "Curso actualizado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        if (method_exists($e, 'getCode') && $e->getCode() == 1062) {
            $_SESSION['mensaje'] = "Error: El código de curso '{$_POST['codigo_curso']}' ya existe.";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar curso: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_cursos.php");
    exit();
}

function eliminarCurso() {
    $id_curso = $_GET['id'] ?? 0;

    if ($id_curso == 0) {
        $_SESSION['mensaje'] = "ID de curso no válido.";
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: ../vistas/admin/gestionar_cursos.php");
        exit();
    }

    try {
        $sql = "DELETE FROM cursos WHERE id = ?";
        if (!execute_cud($sql, "i", [$id_curso])) {
            throw new Exception("No se pudo eliminar el curso.");
        }

        $_SESSION['mensaje'] = "Curso eliminado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al eliminar el curso: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: ../vistas/admin/gestionar_cursos.php");
    exit();
}
?>