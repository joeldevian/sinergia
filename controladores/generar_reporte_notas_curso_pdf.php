<?php
session_start();
require('../lib/fpdf/fpdf.php');
require_once '../config/conexion.php';

// Custom FPDF class for header and footer for course grade report
class PDF extends FPDF
{
    private $courseName;

    function setCourseName($name) {
        $this->courseName = $name;
    }

    // Page header
    function Header()
    {
        // Arial bold 15
        $this->SetFont('Arial','B',15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30,10,utf8_decode('Reporte de Notas por Curso'),0,0,'C');
        // Line break
        $this->Ln(10);

        // Course Name
        if ($this->courseName) {
            $this->SetFont('Arial','B',12);
            $this->Cell(0,10,utf8_decode('Curso: ') . $this->courseName,0,1,'C');
            $this->Ln(10);
        } else {
            $this->Ln(10); // Just a line break if no course name
        }
    }

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'C');
    }
}

// Ensure only teachers can access this
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'docente') {
    header("Location: ../index.php");
    exit();
}

// Get the logged-in teacher's user ID
$id_user_docente = $_SESSION['user_id'];

// Fetch the teacher's ID from the docentes table
$stmt_docente_id = $conexion->prepare("SELECT id FROM docentes WHERE id_user = ?");
$stmt_docente_id->bind_param("i", $id_user_docente);
$stmt_docente_id->execute();
$resultado_docente_id = $stmt_docente_id->get_result();
$docente_data = $resultado_docente_id->fetch_assoc();
$id_docente = $docente_data['id'] ?? 0;
$stmt_docente_id->close();

if ($id_docente == 0) {
    echo "<h1>Error: No se pudo obtener el ID del docente.</h1>";
    $conexion->close();
    exit();
}

// Get course ID from GET request
$id_curso = isset($_GET['id_curso']) ? intval($_GET['id_curso']) : 0;

if ($id_curso == 0) {
    echo "<h1>Error: ID de curso no proporcionado.</h1>";
    $conexion->close();
    exit();
}

// Verify if the course is assigned to the logged-in teacher and fetch course name
$stmt_verify_assignment = $conexion->prepare("SELECT c.nombre_curso FROM docente_curso dc JOIN cursos c ON dc.id_curso = c.id WHERE dc.id_docente = ? AND dc.id_curso = ?");
$stmt_verify_assignment->bind_param("ii", $id_docente, $id_curso);
$stmt_verify_assignment->execute();
$result_verify_assignment = $stmt_verify_assignment->get_result();
if ($result_verify_assignment->num_rows === 0) {
    echo "<h1>Error: El curso no está asignado a este docente o no existe.</h1>";
    $conexion->close();
    exit();
}
$course_info = $result_verify_assignment->fetch_assoc();
$course_name = utf8_decode($course_info['nombre_curso']);
$stmt_verify_assignment->close();

// Fetch evaluations for this course
$query_evaluaciones = "SELECT id, nombre_evaluacion, porcentaje FROM evaluaciones WHERE id_curso = ? ORDER BY id ASC";
$stmt_evaluaciones = $conexion->prepare($query_evaluaciones);
$stmt_evaluaciones->bind_param("i", $id_curso);
$stmt_evaluaciones->execute();
$resultado_evaluaciones = $stmt_evaluaciones->get_result();
$evaluaciones = [];
while ($evaluacion = $resultado_evaluaciones->fetch_assoc()) {
    $evaluaciones[] = $evaluacion;
}
$stmt_evaluaciones->close();

// Fetch students enrolled in this course and their grades
$query_notas_estudiantes = "SELECT 
                                e.id AS estudiante_id,
                                CONCAT(e.nombres, ' ', e.apellido_paterno, ' ', e.apellido_materno) AS nombre_completo,
                                n.id_evaluacion,
                                n.nota
                            FROM matriculas m
                            JOIN estudiantes e ON m.id_estudiante = e.id
                            LEFT JOIN notas n ON e.id = n.id_estudiante AND m.id_curso = n.id_curso
                            WHERE m.id_curso = ? AND m.estado = 'matriculado'
                            ORDER BY nombre_completo ASC";
$stmt_notas_estudiantes = $conexion->prepare($query_notas_estudiantes);
$stmt_notas_estudiantes->bind_param("i", $id_curso);
$stmt_notas_estudiantes->execute();
$resultado_notas_estudiantes = $stmt_notas_estudiantes->get_result();

$estudiantes_notas = [];
$evaluacion_porcentajes = [];
foreach ($evaluaciones as $eval) {
    $evaluacion_porcentajes[$eval['id']] = $eval['porcentaje'];
}

while ($row = $resultado_notas_estudiantes->fetch_assoc()) {
    $estudiante_id = $row['estudiante_id'];
    if (!isset($estudiantes_notas[$estudiante_id])) {
        $estudiantes_notas[$estudiante_id] = [
            'nombre_completo' => utf8_decode($row['nombre_completo']),
            'notas' => [],
            'promedio_ponderado' => 0,
            'total_porcentaje_evaluado' => 0
        ];
    }
    if ($row['id_evaluacion'] !== null) {
        $estudiantes_notas[$estudiante_id]['notas'][$row['id_evaluacion']] = $row['nota'];
    }
}
$stmt_notas_estudiantes->close();

// Calculate weighted average for each student
foreach ($estudiantes_notas as $estudiante_id => &$data) {
    $sum_notas_ponderadas = 0;
    $sum_porcentajes = 0;
    foreach ($evaluaciones as $eval) {
        if (isset($data['notas'][$eval['id']]) && is_numeric($data['notas'][$eval['id']])) {
            $nota = $data['notas'][$eval['id']];
            $porcentaje = $eval['porcentaje'];
            $sum_notas_ponderadas += ($nota * ($porcentaje / 100));
            $sum_porcentajes += $porcentaje;
        }
    }
    $data['total_porcentaje_evaluado'] = $sum_porcentajes;
    if ($sum_porcentajes > 0) {
        $data['promedio_ponderado'] = $sum_notas_ponderadas; // Already ponderado
    } else {
        $data['promedio_ponderado'] = 0;
    }
}
unset($data); // Break the reference

// --- PDF Generation ---
$pdf = new PDF();
$pdf->setCourseName($course_name); // Set course name for header
$pdf->AliasNbPages(); // For {nb} in footer
$pdf->AddPage('L'); // Landscape for more columns
$pdf->SetMargins(10, 10, 10); // Left, Top, Right margins

// Main Title (already in Header, but can be customized here if needed)
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Reporte de Notas - INSTITUTO SINERGIA'), 0, 1, 'C');
$pdf->Ln(5);

// Table Header
$pdf->SetFont('Arial', 'B', 8); // Smaller font for more columns
$pdf->SetFillColor(230, 230, 230);

// Calculate dynamic column widths
$col_width_nombre = 60; // Fixed width for student name
$col_width_eval = 25; // Default width for each evaluation
$col_width_promedio = 25; // Width for weighted average

$num_evaluaciones = count($evaluaciones);
$total_fixed_width = $col_width_nombre + $col_width_promedio;
$remaining_width = 277 - 20 - $total_fixed_width; // A4 Landscape width (297mm) - 2*margins (20mm) - fixed widths
if ($num_evaluaciones > 0) {
    $col_width_eval = $remaining_width / $num_evaluaciones;
    if ($col_width_eval < 15) $col_width_eval = 15; // Minimum width
} else {
    $col_width_eval = 0; // No evaluations
}


// First row: Student Name and Evaluation Names
$pdf->Cell($col_width_nombre, 7, utf8_decode('Estudiante'), 1, 0, 'C', true);
foreach ($evaluaciones as $eval) {
    $pdf->Cell($col_width_eval, 7, utf8_decode($eval['nombre_evaluacion']) . ' (' . $eval['porcentaje'] . '%)', 1, 0, 'C', true);
}
$pdf->Cell($col_width_promedio, 7, utf8_decode('Promedio Final'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 8); // Smaller font for data rows
$pdf->SetFillColor(255, 255, 255); // Reset fill color for data rows

if (!empty($estudiantes_notas)) {
    foreach ($estudiantes_notas as $estudiante_id => $data) {
        $pdf->Cell($col_width_nombre, 6, $data['nombre_completo'], 1, 0, 'L');
        foreach ($evaluaciones as $eval) {
            $nota = $data['notas'][$eval['id']] ?? '-';
            $pdf->Cell($col_width_eval, 6, ($nota !== '-') ? sprintf('%.2f', $nota) : $nota, 1, 0, 'C');
        }
        // Display weighted average
        if ($data['total_porcentaje_evaluado'] == 100) {
            $pdf->Cell($col_width_promedio, 6, sprintf('%.2f', $data['promedio_ponderado']), 1, 1, 'C');
        } else {
            $pdf->Cell($col_width_promedio, 6, utf8_decode('Incompleto'), 1, 1, 'C');
        }
    }
} else {
    $pdf->Cell($col_width_nombre + ($num_evaluaciones * $col_width_eval) + $col_width_promedio, 10, utf8_decode('No hay estudiantes matriculados o notas registradas para este curso.'), 1, 1, 'C');
}

$conexion->close();
$pdf->Output('I', 'Reporte_Notas_Curso_' . preg_replace('/[^a-zA-Z0-9]/', '_', $course_name) . '.pdf');
