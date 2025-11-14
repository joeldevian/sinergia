<?php
session_start();
require('../lib/fpdf/fpdf.php');
require_once '../config/conexion.php';

// Custom FPDF class for header and footer
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Logo (optional)
        // $this->Image('logo.png',10,8,33);
        // Arial bold 15
        $this->SetFont('Arial','B',15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30,10,utf8_decode('Boleta de Notas'),0,0,'C');
        // Line break
        $this->Ln(20);
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

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'estudiante') {
    // If not a student, check if it's an admin (for testing purposes)
    if (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }
    // If no role or not admin, and not student, then redirect
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }
}

// Get the logged-in student's user ID
$id_user_estudiante = $_SESSION['user_id'];

// Fetch the student's ID from the estudiantes table
$stmt_estudiante_id = $conexion->prepare("SELECT id FROM estudiantes WHERE id_user = ?");
$stmt_estudiante_id->bind_param("i", $id_user_estudiante);
$stmt_estudiante_id->execute();
$resultado_estudiante_id = $stmt_estudiante_id->get_result();
$estudiante_data = $resultado_estudiante_id->fetch_assoc();
$id_estudiante = $estudiante_data['id'] ?? 0;
$stmt_estudiante_id->close();

if ($id_estudiante == 0) {
    echo "<h1>Error: No se pudo obtener el ID del estudiante.</h1>";
    $conexion->close();
    exit();
}

// Fetch student's personal details for the report
$query_info_estudiante = "SELECT 
                            codigo_estudiante, dni, CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_completo,
                            email, telefono
                          FROM estudiantes WHERE id = ?";
$stmt_info_estudiante = $conexion->prepare($query_info_estudiante);
$stmt_info_estudiante->bind_param("i", $id_estudiante);
$stmt_info_estudiante->execute();
$info_estudiante = $stmt_info_estudiante->get_result()->fetch_assoc();
$stmt_info_estudiante->close();

if (!$info_estudiante) {
    echo "<h1>Error: No se pudieron encontrar los datos del estudiante.</h1>";
    $conexion->close();
    exit();
}

// Fetch grades with course and evaluation details
$query_calificaciones = "SELECT 
                            c.nombre_curso, c.codigo_curso,
                            e.nombre_evaluacion, e.porcentaje,
                            n.nota
                         FROM notas n
                         JOIN evaluaciones e ON n.id_evaluacion = e.id
                         JOIN cursos c ON e.id_curso = c.id
                         WHERE n.id_estudiante = ?
                         ORDER BY c.nombre_curso, e.nombre_evaluacion ASC";
$stmt_calificaciones = $conexion->prepare($query_calificaciones);
$stmt_calificaciones->bind_param("i", $id_estudiante);
$stmt_calificaciones->execute();
$resultado_calificaciones = $stmt_calificaciones->get_result();

$calificaciones_por_curso = [];
if ($resultado_calificaciones) {
    while ($calificacion = $resultado_calificaciones->fetch_assoc()) {
        $calificaciones_por_curso[$calificacion['nombre_curso']][] = $calificacion;
    }
}
$stmt_calificaciones->close();
$conexion->close();

// --- PDF Generation ---
$pdf = new PDF();
$pdf->AliasNbPages(); // For {nb} in footer
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10); // Left, Top, Right margins

// Title (already in Header, but can be customized here if needed)
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Boleta de Notas - INSTITUTO SINERGIA'), 0, 1, 'C');
$pdf->Ln(10);

// Student Info
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, utf8_decode('Datos del Estudiante:'), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 6, utf8_decode('Código:'), 0, 0);
$pdf->Cell(60, 6, utf8_decode($info_estudiante['codigo_estudiante']), 0, 1);
$pdf->Cell(30, 6, utf8_decode('DNI:'), 0, 0);
$pdf->Cell(60, 6, utf8_decode($info_estudiante['dni']), 0, 1);
$pdf->Cell(30, 6, utf8_decode('Nombre:'), 0, 0);
$pdf->Cell(60, 6, utf8_decode($info_estudiante['nombre_completo']), 0, 1);
$pdf->Cell(30, 6, utf8_decode('Email:'), 0, 0);
$pdf->Cell(60, 6, utf8_decode($info_estudiante['email']), 0, 1);
$pdf->Ln(10);

// Grades by Course
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, utf8_decode('Calificaciones por Curso:'), 0, 1);
$pdf->Ln(2);

if (empty($calificaciones_por_curso)) {
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 10, utf8_decode('No hay calificaciones registradas para este estudiante.'), 0, 1, 'C');
} else {
    foreach ($calificaciones_por_curso as $nombre_curso => $calificaciones) {
        // Course Header
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 220, 255);
        // Calculate the total width of the table (100 + 40 + 40 = 180)
        $table_width = 180; 
        $pdf->MultiCell($table_width, 7, utf8_decode('Curso: ') . utf8_decode($nombre_curso), 1, 'L', true);

        // Table Header for Grades
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(100, 6, utf8_decode('Evaluación'), 1, 0, 'L', true);
        $pdf->Cell(40, 6, utf8_decode('Porcentaje (%)'), 1, 0, 'C', true);
        $pdf->Cell(40, 6, 'Nota', 1, 0, 'C', true);
        $pdf->Ln();

        $promedio_ponderado = 0;
        $sum_porcentajes_con_nota = 0; 
        $total_porcentaje_curso = 0; // To check if all evaluations are accounted for

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetFillColor(255, 255, 255); // Reset fill color for data rows
        foreach ($calificaciones as $calificacion) {
            $eval_nombre = utf8_decode($calificacion['nombre_evaluacion']);
            $eval_porcentaje = $calificacion['porcentaje'];
            $eval_nota = $calificacion['nota'];

            // Use Cell for evaluation name to ensure consistent height and alignment
            // Text will be truncated if too long for the cell width
            $pdf->Cell(100, 6, $eval_nombre, 1, 0, 'L'); 
            $pdf->Cell(40, 6, $eval_porcentaje . '%', 1, 0, 'C');
            $pdf->Cell(40, 6, sprintf('%.2f', $eval_nota), 1, 1, 'C'); // 1 for line break
            
            if (is_numeric($eval_nota) && is_numeric($eval_porcentaje)) {
                $promedio_ponderado += ($eval_nota * ($eval_porcentaje / 100));
                $sum_porcentajes_con_nota += $eval_porcentaje;
            }
            $total_porcentaje_curso += $eval_porcentaje;
        }
        
        // Display weighted average for the course
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 220, 255); // Light blue for average row
        $pdf->Cell(140, 6, utf8_decode('Promedio Final del Curso:'), 1, 0, 'R'); // Occupy 100 + 40 width
        if ($total_porcentaje_curso == 100) { // Only show final average if all evaluations are accounted for
             $pdf->Cell(40, 6, sprintf('%.2f', $promedio_ponderado), 1, 1, 'C', true);
        } else {
             $pdf->Cell(40, 6, utf8_decode('Incompleto (Falta ') . (100 - $total_porcentaje_curso) . '%)', 1, 1, 'C', true);
        }
        $pdf->Ln(5);
    }
}

$pdf->Output('I', 'BoletaDeNotas_' . preg_replace('/[^a-zA-Z0-9]/', '_', $info_estudiante['nombre_completo']) . '.pdf');
