<?php
require_once '../config/conexion.php';
require_once '../lib/fpdf/fpdf.php';

// Obtener parámetros de la URL
$id_curso = $_GET['id_curso'] ?? 0;
$fecha_seleccionada = $_GET['fecha'] ?? date('Y-m-d');

if ($id_curso == 0) {
    die("ID de curso no válido.");
}

// Fetch course details
$stmt_curso = $conexion->prepare("SELECT nombre_curso FROM cursos WHERE id = ?");
$stmt_curso->bind_param("i", $id_curso);
$stmt_curso->execute();
$resultado_curso = $stmt_curso->get_result();
$curso = $resultado_curso->fetch_assoc();
$stmt_curso->close();

if (!$curso) {
    die("Curso no encontrado.");
}

// Fetch students enrolled in this course (same query as gestionar_asistencia_curso.php)
$query_estudiantes = "SELECT e.id, CONCAT(e.nombres, ' ', e.apellido_paterno, ' ', e.apellido_materno) AS nombre_completo
                      FROM matriculas m
                      JOIN estudiantes e ON m.id_estudiante = e.id
                      WHERE m.id_curso = ?
                      ORDER BY nombre_completo ASC";
$stmt_estudiantes = $conexion->prepare($query_estudiantes);
$stmt_estudiantes->bind_param("i", $id_curso);
$stmt_estudiantes->execute();
$resultado_estudiantes = $stmt_estudiantes->get_result();
$estudiantes = [];
while ($estudiante = $resultado_estudiantes->fetch_assoc()) {
    $estudiantes[] = $estudiante;
}
$stmt_estudiantes->close();

// Fetch existing attendance for the selected date and course
$asistencia_existente = [];
if (!empty($estudiantes)) {
    $estudiante_ids = array_column($estudiantes, 'id');
    $placeholders_estudiantes = implode(',', array_fill(0, count($estudiante_ids), '?'));
    $types = str_repeat('i', count($estudiante_ids)) . 'is';
    $params = array_merge($estudiante_ids, [$id_curso, $fecha_seleccionada]);

    $query_asistencia = "SELECT id_estudiante, estado FROM asistencia 
                         WHERE id_estudiante IN ($placeholders_estudiantes) 
                         AND id_curso = ? AND fecha = ?";
    $stmt_asistencia = $conexion->prepare($query_asistencia);
    $stmt_asistencia->bind_param($types, ...$params);
    $stmt_asistencia->execute();
    $resultado_asistencia = $stmt_asistencia->get_result();
    while ($asistencia = $resultado_asistencia->fetch_assoc()) {
        $asistencia_existente[$asistencia['id_estudiante']] = $asistencia['estado'];
    }
    $stmt_asistencia->close();
}

$conexion->close();

// Creación del PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Lista de Asistencia'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode('Curso: ' . $curso['nombre_curso']), 0, 1, 'C');
$pdf->Cell(0, 10, 'Fecha: ' . $fecha_seleccionada, 0, 1, 'C');
$pdf->Ln(10);

// Cabecera de la tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(100, 7, utf8_decode('Estudiante'), 1);
$pdf->Cell(40, 7, 'Estado', 1);
$pdf->Ln();

// Datos de la tabla
$pdf->SetFont('Arial', '', 10);
if (empty($estudiantes)) {
    $pdf->Cell(140, 7, utf8_decode('No hay estudiantes matriculados en este curso para la fecha seleccionada.'), 1, 0, 'C');
    $pdf->Ln();
} else {
    foreach ($estudiantes as $estudiante) {
        $estado = $asistencia_existente[$estudiante['id']] ?? 'No Registrado';
        // Mapeo de estados para una mejor presentación
        switch ($estado) {
            case 'asistio':
                $estado_display = 'Asistió';
                break;
            case 'falto':
                $estado_display = 'Faltó';
                break;
            case 'tardanza':
                $estado_display = 'Tardanza';
                break;
            default:
                $estado_display = 'No Registrado';
                break;
        }
        $pdf->Cell(100, 7, utf8_decode($estudiante['nombre_completo']), 1);
        $pdf->Cell(40, 7, utf8_decode($estado_display), 1);
        $pdf->Ln();
    }
}

$pdf->Output('I', 'lista_asistencia_' . $curso['nombre_curso'] . '_' . $fecha_seleccionada . '.pdf');
?>