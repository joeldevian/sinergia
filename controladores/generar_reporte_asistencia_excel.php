<?php
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

// Configurar cabeceras para descarga de archivo CSV (Excel)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="reporte_consolidado_asistencia_' . $curso['nombre_curso'] . '.csv"');

// Abrir el output para escritura
$output = fopen('php://output', 'w');

// Escribir la cabecera del CSV
fputcsv($output, [
    utf8_decode('Código'),
    utf8_decode('Nombres'),
    utf8_decode('Clases Registradas'),
    utf8_decode('Asistencias'),
    utf8_decode('Faltas'),
    utf8_decode('Tardanzas'),
    utf8_decode('Justificadas'),
    utf8_decode('Porcentaje Asistencia')
]);

// Escribir los datos del reporte
if (!empty($reporte_consolidado)) {
    foreach ($reporte_consolidado as $estudiante_reporte) {
        fputcsv($output, [
            utf8_decode($estudiante_reporte['codigo_estudiante']),
            utf8_decode($estudiante_reporte['nombre_completo']),
            $estudiante_reporte['total_clases_registradas'],
            $estudiante_reporte['asistencias'],
            $estudiante_reporte['faltas'],
            $estudiante_reporte['tardanzas'],
            $estudiante_reporte['justificadas'],
            $estudiante_reporte['porcentaje_asistencia'] . '%'
        ]);
    }
} else {
    fputcsv($output, [utf8_decode('No hay datos de asistencia para este curso.')]);
}

fclose($output);
exit();
