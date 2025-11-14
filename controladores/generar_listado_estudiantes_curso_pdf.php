<?php
session_start();
require('../lib/fpdf/fpdf.php');
require_once '../config/conexion.php';

// Custom FPDF class for header and footer for student list by course
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
        $this->Cell(30,10,utf8_decode('Listado de Estudiantes por Curso'),0,0,'C');
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

// Verify if the course is assigned to the logged-in teacher
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


// Fetch students for the specific course
$query_students = "SELECT 
                        e.codigo_estudiante, e.dni, e.apellido_paterno, e.apellido_materno, e.nombres, e.email, e.estado
                   FROM matriculas m
                   JOIN estudiantes e ON m.id_estudiante = e.id
                   WHERE m.id_curso = ?
                   ORDER BY e.apellido_paterno ASC";
$stmt_students = $conexion->prepare($query_students);
$stmt_students->bind_param("i", $id_curso);
$stmt_students->execute();
$resultado_students = $stmt_students->get_result();

// --- PDF Generation ---
$pdf = new PDF();
$pdf->setCourseName($course_name); // Set course name for header
$pdf->AliasNbPages(); // For {nb} in footer
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10); // Left, Top, Right margins

// Main Title (already in Header, but can be customized here if needed)
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Listado de Estudiantes - INSTITUTO SINERGIA'), 0, 1, 'C');
$pdf->Ln(5);


// Table Header
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(230, 230, 230);
// Define column widths: Codigo, DNI, Apellidos, Nombres, Email, Estado. Total usable width = 190mm
$col_widths = [25, 25, 40, 40, 45, 15]; 

$pdf->Cell($col_widths[0], 7, utf8_decode('Código'), 1, 0, 'C', true);
$pdf->Cell($col_widths[1], 7, utf8_decode('DNI'), 1, 0, 'C', true);
$pdf->Cell($col_widths[2], 7, utf8_decode('Apellidos'), 1, 0, 'C', true);
$pdf->Cell($col_widths[3], 7, utf8_decode('Nombres'), 1, 0, 'C', true);
$pdf->Cell($col_widths[4], 7, utf8_decode('Email'), 1, 0, 'C', true);
$pdf->Cell($col_widths[5], 7, utf8_decode('Estado'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(255, 255, 255); // Reset fill color for data rows

if ($resultado_students->num_rows > 0) {
    while($estudiante = $resultado_students->fetch_assoc()) {
        $pdf->Cell($col_widths[0], 6, utf8_decode($estudiante['codigo_estudiante']), 1, 0, 'L');
        $pdf->Cell($col_widths[1], 6, utf8_decode($estudiante['dni']), 1, 0, 'L');
        $pdf->Cell($col_widths[2], 6, utf8_decode($estudiante['apellido_paterno'] . ' ' . $estudiante['apellido_materno']), 1, 0, 'L');
        $pdf->Cell($col_widths[3], 6, utf8_decode($estudiante['nombres']), 1, 0, 'L');
        $pdf->Cell($col_widths[4], 6, utf8_decode($estudiante['email']), 1, 0, 'L');
        $pdf->Cell($col_widths[5], 6, utf8_decode(ucfirst($estudiante['estado'])), 1, 1, 'C');
    }
} else {
    $pdf->Cell(array_sum($col_widths), 10, utf8_decode('No hay estudiantes matriculados en este curso.'), 1, 1, 'C');
}

$conexion->close();
$pdf->Output('I', 'Listado_Estudiantes_Curso_' . preg_replace('/[^a-zA-Z0-9]/', '_', $course_name) . '.pdf');
