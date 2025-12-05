<?php
session_start();
require('../lib/fpdf/fpdf.php');
require_once '../config/database.php';

// Custom FPDF class for header and footer
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Logo
        $this->Image('../img/logo_letras.png', 15, 8, 50);
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(60, 10, utf8_decode('Comprobante de Matrícula'), 0, 0, 'C');
        // Line break
        $this->Ln(20);
    }

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// --- Security and Data Retrieval ---

// 1. Check user role
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado. Debe ser administrador para realizar esta acción.");
}

// 2. Get data from URL
$id_estudiante = filter_input(INPUT_GET, 'id_estudiante', FILTER_VALIDATE_INT);
$periodo = $_GET['periodo'] ?? '';
$fecha_matricula = $_GET['fecha'] ?? '';
$cursos_ids_str = $_GET['cursos'] ?? '';

if (!$id_estudiante || empty($periodo) || empty($fecha_matricula) || empty($cursos_ids_str)) {
    die("Error: Faltan datos para generar el comprobante.");
}

// 3. Process and fetch data from DB
$cursos_ids = explode(',', $cursos_ids_str);

// Fetch student data
$estudiante = select_one("SELECT CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_completo, codigo_estudiante FROM estudiantes WHERE id = ?", "i", [$id_estudiante]);
if (!$estudiante) die("Error: Estudiante no encontrado.");

// Fetch course data
$placeholders = implode(',', array_fill(0, count($cursos_ids), '?'));
$types = str_repeat('i', count($cursos_ids));
$query_cursos = "SELECT codigo_curso, nombre_curso, creditos FROM cursos WHERE id IN ($placeholders)";
$cursos_matriculados = select_all($query_cursos, $types, $cursos_ids);

// --- PDF Generation ---

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);
$pdf->SetFont('Arial', '', 12);

// Info section
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode('Datos del Estudiante y Matrícula'), 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(40, 8, utf8_decode('Estudiante:'), 0);
$pdf->Cell(0, 8, utf8_decode($estudiante['nombre_completo']), 0, 1);
$pdf->Cell(40, 8, utf8_decode('Código:'), 0);
$pdf->Cell(0, 8, utf8_decode($estudiante['codigo_estudiante']), 0, 1);
$pdf->Cell(40, 8, utf8_decode('Fecha de Matrícula:'), 0);
$pdf->Cell(0, 8, date("d/m/Y", strtotime($fecha_matricula)), 0, 1);
$pdf->Cell(40, 8, utf8_decode('Periodo Académico:'), 0);
$pdf->Cell(0, 8, utf8_decode($periodo), 0, 1);
$pdf->Ln(10);

// Table of courses
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, utf8_decode('Cursos Matriculados'), 0, 1);

// Table Header
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(230, 230, 230);
$col_widths = [40, 100, 30]; // Codigo, Nombre, Creditos
$pdf->Cell($col_widths[0], 7, utf8_decode('Código'), 1, 0, 'C', true);
$pdf->Cell($col_widths[1], 7, utf8_decode('Nombre del Curso'), 1, 0, 'C', true);
$pdf->Cell($col_widths[2], 7, utf8_decode('Créditos'), 1, 1, 'C', true);

// Table Data
$pdf->SetFont('Arial', '', 10);
$total_creditos = 0;
foreach ($cursos_matriculados as $curso) {
    $pdf->Cell($col_widths[0], 6, utf8_decode($curso['codigo_curso']), 1, 0);
    $pdf->Cell($col_widths[1], 6, utf8_decode($curso['nombre_curso']), 1, 0);
    $pdf->Cell($col_widths[2], 6, $curso['creditos'], 1, 1, 'C');
    $total_creditos += (int)$curso['creditos'];
}

// Total Credits
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($col_widths[0] + $col_widths[1], 7, utf8_decode('Total de Créditos Matriculados'), 1, 0, 'R');
$pdf->Cell($col_widths[2], 7, $total_creditos, 1, 1, 'C');

// Output PDF
$pdf->Output('I', 'Comprobante_Matricula_' . str_replace(' ', '_', $estudiante['nombre_completo']) . '.pdf');

?>
