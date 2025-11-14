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
        agregarCurso($conexion);
        break;
    case 'editar':
        editarCurso($conexion);
        break;
    case 'eliminar':
        eliminarCurso($conexion);
        break;
}

function agregarCurso($conexion) {
    try {
        $codigo_curso = $_POST['codigo_curso'];
        $nombre_curso = $_POST['nombre_curso'];
        $creditos = $_POST['creditos'];
        $horas_semanales = $_POST['horas_semanales'];
        $id_carrera = $_POST['id_carrera'];
        $ciclo = $_POST['ciclo'];
        $tipo = $_POST['tipo'];

        $stmt = $conexion->prepare(
            "INSERT INTO cursos (codigo_curso, nombre_curso, creditos, horas_semanales, id_carrera, ciclo, tipo) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssiiiss",
            $codigo_curso,
            $nombre_curso,
            $creditos,
            $horas_semanales,
            $id_carrera,
            $ciclo,
            $tipo
        );
        $stmt->execute();
        $stmt->close();

        $_SESSION['mensaje'] = "Curso agregado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Código de error para entrada duplicada
            if (strpos($e->getMessage(), 'uq_cursos_codigo') !== false) {
                $_SESSION['mensaje'] = "Error: El código de curso '{$codigo_curso}' ya existe.";
            } else {
                $_SESSION['mensaje'] = "Error al agregar curso: Entrada duplicada no especificada.";
            }
        } else {
            $_SESSION['mensaje'] = "Error al agregar curso: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_cursos.php");
    exit();
}

function editarCurso($conexion) {
    $id_curso = $_POST['id_curso'];

    try {
        $codigo_curso = $_POST['codigo_curso'];
        $nombre_curso = $_POST['nombre_curso'];
        $creditos = $_POST['creditos'];
        $horas_semanales = $_POST['horas_semanales'];
        $id_carrera = $_POST['id_carrera'];
        $ciclo = $_POST['ciclo'];
        $tipo = $_POST['tipo'];
        $estado = $_POST['estado'];

        $stmt = $conexion->prepare(
            "UPDATE cursos SET 
             codigo_curso = ?, nombre_curso = ?, creditos = ?, horas_semanales = ?, 
             id_carrera = ?, ciclo = ?, tipo = ?, estado = ?
             WHERE id = ?"
        );
        $stmt->bind_param(
            "ssiiisssi",
            $codigo_curso, $nombre_curso, $creditos, $horas_semanales,
            $id_carrera, $ciclo, $tipo, $estado,
            $id_curso
        );
        $stmt->execute();
        $stmt->close();

        $_SESSION['mensaje'] = "Curso actualizado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Código de error para entrada duplicada
            if (strpos($e->getMessage(), 'uq_cursos_codigo') !== false) {
                $_SESSION['mensaje'] = "Error: El código de curso '{$codigo_curso}' ya existe.";
            } else {
                $_SESSION['mensaje'] = "Error al actualizar curso: Entrada duplicada no especificada.";
            }
        } else {
            $_SESSION['mensaje'] = "Error al actualizar curso: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_cursos.php");
    exit();
}

function eliminarCurso($conexion) {
    $id_curso = $_GET['id'] ?? 0;

    if ($id_curso == 0) {
        $_SESSION['mensaje'] = "ID de curso no válido.";
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: ../vistas/admin/gestionar_cursos.php");
        exit();
    }

    try {
        $stmt = $conexion->prepare("DELETE FROM cursos WHERE id = ?");
        $stmt->bind_param("i", $id_curso);
        $stmt->execute();
        $stmt->close();

        $_SESSION['mensaje'] = "Curso eliminado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        $_SESSION['mensaje'] = "Error al eliminar el curso: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_cursos.php");
    exit();
}

function agregarCurso($conexion) {
    try {
        $codigo_curso = $_POST['codigo_curso'];
        $nombre_curso = $_POST['nombre_curso'];
        $creditos = $_POST['creditos'];
        $horas_semanales = $_POST['horas_semanales'];
        $id_carrera = $_POST['id_carrera'];
        $ciclo = $_POST['ciclo'];
        $tipo = $_POST['tipo'];

        $stmt = $conexion->prepare(
            "INSERT INTO cursos (codigo_curso, nombre_curso, creditos, horas_semanales, id_carrera, ciclo, tipo) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "ssiiiss",
            $codigo_curso,
            $nombre_curso,
            $creditos,
            $horas_semanales,
            $id_carrera,
            $ciclo,
            $tipo
        );
        $stmt->execute();
        $stmt->close();

        $_SESSION['mensaje'] = "Curso agregado exitosamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Código de error para entrada duplicada
            if (strpos($e->getMessage(), 'uq_cursos_codigo') !== false) {
                $_SESSION['mensaje'] = "Error: El código de curso '{$codigo_curso}' ya existe.";
            } else {
                $_SESSION['mensaje'] = "Error al agregar curso: Entrada duplicada no especificada.";
            }
        } else {
            $_SESSION['mensaje'] = "Error al agregar curso: " . $e->getMessage();
        }
        $_SESSION['mensaje_tipo'] = "danger";
    }

    $conexion->close();
    header("Location: ../vistas/admin/gestionar_cursos.php");
    exit();
}
?>
