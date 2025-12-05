<?php
require_once __DIR__ . '/../config/conexion.php'; // Este controlador no usa database.php, por lo tanto requiere conexion.php
require_once __DIR__ . '/../lib/fpdf/fpdf.php';
require_once __DIR__ . '/asistencia_reporte_controller.php'; // Incluir el controlador con la lógica del reporte

// Obtener parámetros de la URL
$id_curso = $_GET['id_curso'] ?? 0;

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

// Obtener el reporte consolidado
$reporte_consolidado = getReporteConsolidadoAsistencia($id_curso);

$conexion->close();

// Creación del PDF
$pdf = new FPDF();
$pdf->AddPage('L'); // Página horizontal para más columnas
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Reporte Consolidado de Asistencia'), 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode('Curso: ' . $curso['nombre_curso']), 0, 1, 'C');
$pdf->Ln(10);

// Cabecera de la tabla
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(230, 230, 230); // Color de fondo para la cabecera
$pdf->Cell(20, 7, utf8_decode('Código'), 1, 0, 'C', true);
$pdf->Cell(60, 7, utf8_decode('Estudiante'), 1, 0, 'C', true);
$pdf->Cell(25, 7, utf8_decode('Clases Reg.'), 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Asistencias', 1, 0, 'C', true);
$pdf->Cell(20, 7, 'Faltas', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Tardanzas', 1, 0, 'C', true);
$pdf->Cell(25, 7, 'Justif.', 1, 0, 'C', true);
$pdf->Cell(25, 7, '% Asistencia', 1, 0, 'C', true);
$pdf->Ln();

// Datos de la tabla
$pdf->SetFont('Arial', '', 9);
if (empty($reporte_consolidado)) {
    $pdf->Cell(205, 7, utf8_decode('No hay datos de asistencia para este curso.'), 1, 0, 'C');
    $pdf->Ln();
} else {
    foreach ($reporte_consolidado as $estudiante_reporte) {
        $pdf->Cell(20, 7, utf8_decode($estudiante_reporte['codigo_estudiante']), 1);
        $pdf->Cell(60, 7, utf8_decode($estudiante_reporte['nombre_completo']), 1);
        $pdf->Cell(25, 7, $estudiante_reporte['total_clases_registradas'], 1, 0, 'C');
        $pdf->Cell(25, 7, $estudiante_reporte['asistencias'], 1, 0, 'C');
        $pdf->Cell(20, 7, $estudiante_reporte['faltas'], 1, 0, 'C');
        $pdf->Cell(25, 7, $estudiante_reporte['tardanzas'], 1, 0, 'C');
        $pdf->Cell(25, 7, $estudiante_reporte['justificadas'], 1, 0, 'C');
        
        $porcentaje = $estudiante_reporte['porcentaje_asistencia'];
        if ($porcentaje >= 70) {
            $pdf->SetTextColor(0, 128, 0); // Verde
        } elseif ($porcentaje >= 60) {
            $pdf->SetTextColor(255, 165, 0); // Amarillo (Naranja)
        } else {
            $pdf->SetTextColor(255, 0, 0); // Rojo
        }
        $pdf->Cell(25, 7, $porcentaje . '%', 1, 0, 'C');
        $pdf->SetTextColor(0, 0, 0); // Reset color
        $pdf->Ln();
    }
}

$pdf->Output('I', 'reporte_consolidado_asistencia_' . $curso['nombre_curso'] . '.pdf');
?>