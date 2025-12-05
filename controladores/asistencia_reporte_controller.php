<?php
require_once __DIR__ . '/../config/database.php'; // Para select_all y select_one

function getReporteConsolidadoAsistencia($id_curso) {
    global $conexion;
    $reporte = [];

    // 1. Obtener todos los estudiantes matriculados en el curso
    $query_estudiantes = "SELECT e.id, e.codigo_estudiante, CONCAT(e.nombres, ' ', e.apellido_paterno, ' ', e.apellido_materno) AS nombre_completo
                          FROM matriculas m
                          JOIN estudiantes e ON m.id_estudiante = e.id
                          WHERE m.id_curso = ?
                          ORDER BY nombre_completo ASC";
    $estudiantes = select_all($query_estudiantes, "i", [$id_curso]);

    // 2. Contar el total de clases registradas para el curso
    $query_total_clases = "SELECT COUNT(DISTINCT fecha) AS total_clases FROM asistencia WHERE id_curso = ?";
    $result_total_clases = select_one($query_total_clases, "i", [$id_curso]);
    $total_clases = $result_total_clases['total_clases'] ?? 0;

    foreach ($estudiantes as $estudiante) {
        $id_estudiante = $estudiante['id'];
        
        // 3. Contar asistencias, faltas, tardanzas y justificadas para cada estudiante
        $query_conteo_asistencia = "SELECT
                                        SUM(CASE WHEN estado = 'asistio' THEN 1 ELSE 0 END) AS asistencias,
                                        SUM(CASE WHEN estado = 'falto' THEN 1 ELSE 0 END) AS faltas,
                                        SUM(CASE WHEN estado = 'tardanza' THEN 1 ELSE 0 END) AS tardanzas,
                                        SUM(CASE WHEN estado = 'justificada' THEN 1 ELSE 0 END) AS justificadas
                                    FROM asistencia
                                    WHERE id_estudiante = ? AND id_curso = ?";
        $conteo = select_one($query_conteo_asistencia, "ii", [$id_estudiante, $id_curso]);

        // Asegurarse de que los valores no sean nulos si no hay registros
        $asistencias = $conteo['asistencias'] ?? 0;
        $faltas = $conteo['faltas'] ?? 0;
        $tardanzas = $conteo['tardanzas'] ?? 0;
        $justificadas = $conteo['justificadas'] ?? 0;

        // 4. Calcular el porcentaje de asistencia
        $total_presentes = $asistencias + $tardanzas + $justificadas; // Considerar tardanzas y justificadas como "presente" para el %
        $porcentaje_asistencia = ($total_clases > 0) ? round(($total_presentes / $total_clases) * 100, 2) : 0;

        $reporte[] = [
            'id_estudiante' => $estudiante['id'],
            'codigo_estudiante' => $estudiante['codigo_estudiante'],
            'nombre_completo' => $estudiante['nombre_completo'],
            'total_clases_registradas' => $total_clases,
            'asistencias' => $asistencias,
            'faltas' => $faltas,
            'tardanzas' => $tardanzas,
            'justificadas' => $justificadas,
            'porcentaje_asistencia' => $porcentaje_asistencia
        ];
    }

    return $reporte;
}

// Si se llama directamente como API (para AJAX, aunque no lo estamos usando directamente aquí)
if (isset($_GET['accion']) && $_GET['accion'] == 'get_reporte_consolidado' && isset($_GET['id_curso'])) {
    header('Content-Type: application/json');
    $id_curso = filter_input(INPUT_GET, 'id_curso', FILTER_VALIDATE_INT);
    if ($id_curso) {
        $reporte = getReporteConsolidadoAsistencia($id_curso);
        echo json_encode($reporte);
    } else {
        echo json_encode(['error' => 'ID de curso no válido.']);
    }
    exit();
}
