<?php
session_start();
require('../lib/fpdf/fpdf.php');
require_once '../config/database.php'; // Cambiado de conexion.php a database.php

// Custom FPDF class for header and footer for teacher list
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
        $this->Cell(30,10,utf8_decode('Listado de Docentes'),0,0,'C');
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

// Fetch teachers from the database
$query = "SELECT codigo_docente, dni, apellido_paterno, apellido_materno, nombres, email, estado FROM docentes ORDER BY apellido_paterno ASC";
$docentes = select_all($query); // Usando select_all() de database.php

// --- PDF Generation ---
$pdf = new PDF();
$pdf->AliasNbPages(); // For {nb} in footer
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10); // Left, Top, Right margins

// Title
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Listado de Docentes - INSTITUTO SINERGIA'), 0, 1, 'C');
$pdf->Ln(10);

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

if (!empty($docentes)) { // Cambiado de $resultado->num_rows > 0 a !empty($docentes)
    foreach($docentes as $docente) { // Cambiado de while($docente = $resultado->fetch_assoc()) a foreach
        $pdf->Cell($col_widths[0], 6, utf8_decode($docente['codigo_docente']), 1, 0, 'L');
        $pdf->Cell($col_widths[1], 6, utf8_decode($docente['dni']), 1, 0, 'L');
        $pdf->Cell($col_widths[2], 6, utf8_decode($docente['apellido_paterno'] . ' ' . $docente['apellido_materno']), 1, 0, 'L');
        $pdf->Cell($col_widths[3], 6, utf8_decode($docente['nombres']), 1, 0, 'L');
        $pdf->Cell($col_widths[4], 6, utf8_decode($docente['email']), 1, 0, 'L');
        $pdf->Cell($col_widths[5], 6, utf8_decode(ucfirst($docente['estado'])), 1, 1, 'C');
    }
} else {
    $pdf->Cell(array_sum($col_widths), 10, utf8_decode('No hay docentes registrados.'), 1, 1, 'C');
}

// $conexion->close(); // Eliminado, ya que la función select_all() cierra el statement
$pdf->Output('I', 'Listado_Docentes.pdf');
