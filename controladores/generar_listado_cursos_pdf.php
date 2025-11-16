<?php
session_start();
require('../lib/fpdf/fpdf.php');
require_once '../config/database.php'; // Cambiado de conexion.php a database.php

// Custom FPDF class for header and footer for course list
class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Arial bold 15
        $this->SetFont('Arial','B',15);
        // Move to the right
        $this->Cell(80);
        // Title
        $this->Cell(30,10,utf8_decode('Listado de Cursos'),0,0,'C');
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

// Ensure only admins can access this
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch courses from the database
$query = "SELECT c.codigo_curso, c.nombre_curso, c.creditos, c.horas_semanales, ca.nombre_carrera, c.ciclo, c.tipo, c.estado 
          FROM cursos c
          JOIN carreras ca ON c.id_carrera = ca.id
          ORDER BY ca.nombre_carrera, c.nombre_curso ASC";
$cursos = select_all($query); // Usando select_all() de database.php

// --- PDF Generation ---
$pdf = new PDF();
$pdf->AliasNbPages(); // For {nb} in footer
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10); // Left, Top, Right margins

// Title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Listado de Cursos - INSTITUTO SINERGIA'), 0, 1, 'C');
$pdf->Ln(10);

// Table Header
$pdf->SetFont('Arial', 'B', 8); // Smaller font for more columns
$pdf->SetFillColor(230, 230, 230);
// Define column widths: Código, Nombre del Curso, Créditos, Horas Semanales, Carrera, Ciclo, Tipo, Estado. Total usable width = 190mm
$col_widths = [20, 50, 15, 20, 35, 15, 15, 20]; 

$pdf->Cell($col_widths[0], 7, utf8_decode('Código'), 1, 0, 'C', true);
$pdf->Cell($col_widths[1], 7, utf8_decode('Nombre del Curso'), 1, 0, 'C', true);
$pdf->Cell($col_widths[2], 7, utf8_decode('Créditos'), 1, 0, 'C', true);
$pdf->Cell($col_widths[3], 7, utf8_decode('Horas Sem.'), 1, 0, 'C', true);
$pdf->Cell($col_widths[4], 7, utf8_decode('Carrera'), 1, 0, 'C', true);
$pdf->Cell($col_widths[5], 7, utf8_decode('Ciclo'), 1, 0, 'C', true);
$pdf->Cell($col_widths[6], 7, utf8_decode('Tipo'), 1, 0, 'C', true);
$pdf->Cell($col_widths[7], 7, utf8_decode('Estado'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 8); // Smaller font for data rows
$pdf->SetFillColor(255, 255, 255); // Reset fill color for data rows

if (!empty($cursos)) { // Cambiado de $resultado->num_rows > 0 a !empty($cursos)
    foreach($cursos as $curso) { // Cambiado de while($curso = $resultado->fetch_assoc()) a foreach
        $pdf->Cell($col_widths[0], 6, utf8_decode($curso['codigo_curso']), 1, 0, 'L');
        $pdf->Cell($col_widths[1], 6, utf8_decode($curso['nombre_curso']), 1, 0, 'L');
        $pdf->Cell($col_widths[2], 6, utf8_decode($curso['creditos']), 1, 0, 'C');
        $pdf->Cell($col_widths[3], 6, utf8_decode($curso['horas_semanales']), 1, 0, 'C');
        $pdf->Cell($col_widths[4], 6, utf8_decode($curso['nombre_carrera']), 1, 0, 'L');
        $pdf->Cell($col_widths[5], 6, utf8_decode($curso['ciclo']), 1, 0, 'C');
        $pdf->Cell($col_widths[6], 6, utf8_decode($curso['tipo']), 1, 0, 'C');
        $pdf->Cell($col_widths[7], 6, utf8_decode(ucfirst($curso['estado'])), 1, 1, 'C');
    }
} else {
    $pdf->Cell(array_sum($col_widths), 10, utf8_decode('No hay cursos registrados.'), 1, 1, 'C');
}

// $conexion->close(); // Eliminado, ya que la función select_all() cierra el statement
$pdf->Output('I', 'Listado_Cursos.pdf');
