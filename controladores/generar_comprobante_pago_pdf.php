<?php
session_start();
require('../lib/fpdf/fpdf.php');
require_once '../config/database.php';

// Custom FPDF class for header and footer
class PDF extends FPDF
{
    function Header()
    {
        $this->Image('../img/logo_letras.png', 15, 8, 60);
        $this->SetFont('Arial', 'B', 12);
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Gracias por su pago.'), 0, 0, 'C');
    }
}

// --- Security and Data Retrieval ---
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado.");
}

$pago_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$pago_id) {
    die("Error: ID de pago no válido.");
}

// Fetch payment details from DB
$query = "SELECT p.*, e.nombres, e.apellido_paterno, e.codigo_estudiante 
          FROM pagos p 
          JOIN estudiantes e ON p.id_estudiante = e.id 
          WHERE p.id = ?";
$pago = select_one($query, "i", [$pago_id]);

if (!$pago) {
    die("Error: No se encontró el pago.");
}

// --- Data for Verification Code ---
$verification_string = $pago['id'] . '-' . $pago['numero_recibo'] . '-' . $pago['id_estudiante'] . '-' . $pago['monto'];
$verification_code = strtoupper(substr(sha1($verification_string), 0, 16));

// --- PDF Generation ---

/**
 * Convierte un número a su representación en letras.
 * NOTA: Esta función requiere la extensión de PHP 'intl'.
 * Si la extensión no está habilitada en php.ini, esta función no añadirá el monto en letras.
 * Para habilitarla, busca y descomenta la línea: extension=intl
 */
function anadirMontoEnLetras($numero) {
    if (!class_exists('NumberFormatter')) {
        return ''; // Retorna vacío si la extensión 'intl' no está disponible.
    }

    $formatter = new NumberFormatter('es', NumberFormatter::SPELLOUT);
    $parte_entera = floor($numero);
    $parte_decimal = round(($numero - $parte_entera) * 100);

    $letras_enteras = mb_strtoupper($formatter->format($parte_entera), 'UTF-8');
    $letras_decimales = str_pad($parte_decimal, 2, '0', STR_PAD_LEFT);

    return "SON: $letras_enteras Y $letras_decimales/100 SOLES";
}

$monto_en_letras = anadirMontoEnLetras($pago['monto']);

$pdf = new PDF('P', 'mm', 'A5'); // A5 paper size, portrait
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);
$pdf->SetFont('Arial', '', 11);

// Receipt Header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('COMPROBANTE DE PAGO'), 0, 1, 'R');
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(205, 23, 20); // Red color
$pdf->Cell(0, 7, utf8_decode($pago['numero_recibo']), 0, 1, 'R');
$pdf->SetTextColor(0, 0, 0); // Reset color
$pdf->Ln(5);

// Payment Details
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 6, utf8_decode('Fecha de Emisión:'), 0);
$pdf->Cell(0, 6, date("d/m/Y", strtotime($pago['fecha_pago'])), 0, 1);

$pdf->Cell(30, 6, utf8_decode('Estudiante:'), 0);
$pdf->Cell(0, 6, utf8_decode($pago['nombres'] . ' ' . $pago['apellido_paterno']), 0, 1);

$pdf->Cell(30, 6, utf8_decode('Código:'), 0);
$pdf->Cell(0, 6, utf8_decode($pago['codigo_estudiante']), 0, 1);
$pdf->Ln(8);

// Payment table
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(98, 7, utf8_decode('Concepto'), 1, 0, 'C', true);
$pdf->Cell(20, 7, utf8_decode('Monto'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
// MultiCell for concept in case it's long
$x = $pdf->GetX();
$y = $pdf->GetY();
$pdf->MultiCell(98, 8, utf8_decode($pago['concepto']), 1, 'L');
$pdf->SetXY($x + 98, $y); // Reset position
$pdf->Cell(20, 8, 'S/ ' . number_format($pago['monto'], 2), 1, 1, 'R');

// Total
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(98, 8, utf8_decode('TOTAL PAGADO'), 1, 0, 'R', true);
$pdf->Cell(20, 8, 'S/ ' . number_format($pago['monto'], 2), 1, 1, 'R', true);

// Amount in words (only if available)
if (!empty($monto_en_letras)) {
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 8, utf8_decode($monto_en_letras), 1, 1, 'L');
}
$pdf->Ln(5);

// Payment Method
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(35, 6, utf8_decode('Método de Pago:'), 0);
$pdf->Cell(0, 6, utf8_decode($pago['metodo_pago']), 0, 1);
$pdf->Ln(10);

// Verification Code
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 5, utf8_decode('Código de Verificación'), 0, 1, 'C');
$pdf->SetFont('Courier', 'B', 12);
$pdf->SetFillColor(245, 245, 245);
$pdf->Cell(0, 8, $verification_code, 1, 1, 'C', true);


// Output PDF
$pdf->Output('I', 'Comprobante_' . $pago['numero_recibo'] . '.pdf');

?>
