<?php
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';

// Permitir que el script maneje diferentes solicitudes de datos
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    // --- Acciones de Administrador ---
    case 'estudiantes_por_curso':
        if ($_SESSION['rol'] !== 'admin') die(json_encode(['error' => 'Acceso denegado']));
        obtenerEstudiantesPorCurso();
        break;
    case 'get_kpis':
        if ($_SESSION['rol'] !== 'admin') die(json_encode(['error' => 'Acceso denegado']));
        obtenerKpis();
        break;
    case 'get_grade_distribution':
        if ($_SESSION['rol'] !== 'admin') die(json_encode(['error' => 'Acceso denegado']));
        obtenerDistribucionCalificaciones();
        break;
    case 'get_enrollment_trends':
        if ($_SESSION['rol'] !== 'admin') die(json_encode(['error' => 'Acceso denegado']));
        obtenerTendenciaMatriculas();
        break;

    // --- Acciones de Estudiante ---
    case 'get_student_kpis':
        if ($_SESSION['rol'] !== 'estudiante') die(json_encode(['error' => 'Acceso denegado']));
        obtenerKpisEstudiante();
        break;
    case 'get_student_grades_by_course':
        if ($_SESSION['rol'] !== 'estudiante') die(json_encode(['error' => 'Acceso denegado']));
        obtenerCalificacionesPorCursoEstudiante();
        break;
    case 'get_student_attendance_summary':
        if ($_SESSION['rol'] !== 'estudiante') die(json_encode(['error' => 'Acceso denegado']));
        obtenerResumenAsistenciaEstudiante();
        break;

    // --- Acciones de Docente ---
    case 'get_teacher_kpis':
        if ($_SESSION['rol'] !== 'docente') die(json_encode(['error' => 'Acceso denegado']));
        obtenerKpisDocente();
        break;
    case 'get_teacher_grade_distribution':
        if ($_SESSION['rol'] !== 'docente') die(json_encode(['error' => 'Acceso denegado']));
        obtenerDistribucionCalificacionesDocente();
        break;
    case 'get_teacher_attendance_summary':
        if ($_SESSION['rol'] !== 'docente') die(json_encode(['error' => 'Acceso denegado']));
        obtenerResumenAsistenciaDocente();
        break;

    // --- Acciones de Pagos (Admin) ---
    case 'get_payment_kpis':
        if ($_SESSION['rol'] !== 'admin') die(json_encode(['error' => 'Acceso denegado']));
        obtenerKpisPagos();
        break;
    case 'get_income_by_month':
        if ($_SESSION['rol'] !== 'admin') die(json_encode(['error' => 'Acceso denegado']));
        obtenerIngresosPorMes();
        break;
    case 'get_pension_status_summary':
        if ($_SESSION['rol'] !== 'admin') die(json_encode(['error' => 'Acceso denegado']));
        obtenerResumenEstadoPensiones();
        break;

    default:
        echo json_encode(['error' => 'Accion no valida']);
        break;
}

// --- Funciones de Administrador ---

function obtenerKpis() {
    try {
        $sql_estudiantes = "SELECT COUNT(id) as total FROM users WHERE rol = 'estudiante' AND estado = 'activo'";
        $total_estudiantes = select_one($sql_estudiantes)['total'] ?? 0;

        $sql_docentes = "SELECT COUNT(id) as total FROM users WHERE rol = 'docente' AND estado = 'activo'";
        $total_docentes = select_one($sql_docentes)['total'] ?? 0;

        $sql_cursos = "SELECT COUNT(id) as total FROM cursos WHERE estado = 'activo'";
        $total_cursos = select_one($sql_cursos)['total'] ?? 0;

        echo json_encode([
            'total_estudiantes' => $total_estudiantes,
            'total_docentes' => $total_docentes,
            'total_cursos' => $total_cursos
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener los KPIs: ' . $e->getMessage()]);
    }
}

function obtenerDistribucionCalificaciones() {
    try {
        $sql = "
            SELECT 
                CASE
                    WHEN nota >= 0 AND nota <= 10 THEN 'Desaprobado (0-10)'
                    WHEN nota > 10 AND nota <= 14 THEN 'Aprobado (11-14)'
                    WHEN nota > 14 AND nota <= 17 THEN 'Bueno (15-17)'
                    WHEN nota > 17 AND nota <= 20 THEN 'Excelente (18-20)'
                END as rango,
                COUNT(id) as total
            FROM notas
            GROUP BY rango
            ORDER BY FIELD(rango, 'Desaprobado (0-10)', 'Aprobado (11-14)', 'Bueno (15-17)', 'Excelente (18-20)');
        ";
        
        $resultado = select_all($sql);

        $labels = [];
        $data = [];

        foreach ($resultado as $fila) {
            if ($fila['rango']) {
                $labels[] = $fila['rango'];
                $data[] = (int)$fila['total'];
            }
        }
        
        $backgroundColors = ['#dc3545', '#ffc107', '#28a745', '#007bff'];
        echo json_encode(['labels' => $labels, 'data' => $data, 'backgroundColors' => $backgroundColors]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener los datos de calificaciones: ' . $e->getMessage()]);
    }
}

function obtenerEstudiantesPorCurso() {
    try {
        $sql = "
            SELECT c.nombre_curso AS nombre_curso, COUNT(m.id_estudiante) AS numero_estudiantes
            FROM cursos c LEFT JOIN matriculas m ON c.id = m.id_curso
            GROUP BY c.id, c.nombre_curso ORDER BY numero_estudiantes DESC;
        ";
        $resultado = select_all($sql);
        $labels = []; $data = []; $otros_count = 0; $limit = 5;
        if (count($resultado) > $limit) {
            for ($i = 0; $i < $limit; $i++) {
                $labels[] = $resultado[$i]['nombre_curso'];
                $data[] = (int)$resultado[$i]['numero_estudiantes'];
            }
            for ($i = $limit; $i < count($resultado); $i++) {
                $otros_count += (int)$resultado[$i]['numero_estudiantes'];
            }
            if ($otros_count > 0) { $labels[] = 'Otros'; $data[] = $otros_count; }
        } else {
            foreach ($resultado as $fila) {
                $labels[] = $fila['nombre_curso'];
                $data[] = (int)$fila['numero_estudiantes'];
            }
        }
        $backgroundColors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d', '#343a40'];
        echo json_encode(['labels' => $labels, 'data' => $data, 'backgroundColors' => array_slice($backgroundColors, 0, count($labels))]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener los datos: ' . $e->getMessage()]);
    }
}

function obtenerTendenciaMatriculas() {
    try {
        $sql = "
            SELECT YEAR(fecha_matricula) as anio, MONTH(fecha_matricula) as mes, COUNT(id) as total
            FROM matriculas GROUP BY anio, mes ORDER BY anio, mes;
        ";
        $resultado = select_all($sql);
        $labels = []; $data = [];
        $meses = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
        foreach ($resultado as $fila) {
            $labels[] = $meses[$fila['mes'] - 1] . ' ' . $fila['anio'];
            $data[] = (int)$fila['total'];
        }
        echo json_encode(['labels' => $labels, 'data' => $data]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener la tendencia de matrículas: ' . $e->getMessage()]);
    }
}

// --- Funciones de Docente ---

function obtenerIdDocenteDeSesion() {
    $id_user = $_SESSION['user_id'] ?? 0;
    if ($id_user === 0) return null;

    $result = select_one("SELECT id FROM docentes WHERE id_user = ?", "i", [$id_user]);
    return $result['id'] ?? null;
}

function obtenerKpisDocente() {
    try {
        $id_docente = obtenerIdDocenteDeSesion();
        if ($id_docente === null) throw new Exception("No se pudo encontrar el perfil del docente.");

        $sql_cursos = "SELECT COUNT(DISTINCT a.id_curso) as total FROM docente_curso a WHERE a.id_docente = ?";
        $total_cursos = select_one($sql_cursos, "i", [$id_docente])['total'] ?? 0;

        $sql_estudiantes = "SELECT COUNT(DISTINCT m.id_estudiante) as total FROM matriculas m
                            INNER JOIN docente_curso a ON m.id_curso = a.id_curso
                            WHERE a.id_docente = ?";
        $total_estudiantes = select_one($sql_estudiantes, "i", [$id_docente])['total'] ?? 0;

        $sql_asistencia = "SELECT estado, COUNT(id) as total FROM asistencia
                           WHERE id_curso IN (SELECT id_curso FROM docente_curso WHERE id_docente = ?)
                           GROUP BY estado";
        $asistencias = select_all($sql_asistencia, "i", [$id_docente]);
        $presentes = 0; $faltas = 0;
        foreach($asistencias as $asistencia) {
            if ($asistencia['estado'] == 'presente' || $asistencia['estado'] == 'tardanza') {
                $presentes += $asistencia['total'];
            } else if ($asistencia['estado'] == 'ausente') {
                $faltas += $asistencia['total'];
            }
        }
        $total_asistencias = $presentes + $faltas;
        $promedio_asistencia = ($total_asistencias > 0) ? round(($presentes / $total_asistencias) * 100) : 0;

        echo json_encode([
            'total_cursos' => $total_cursos,
            'total_estudiantes' => $total_estudiantes,
            'promedio_asistencia' => $promedio_asistencia
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener los KPIs del docente: ' . $e->getMessage()]);
    }
}

function obtenerDistribucionCalificacionesDocente() {
    try {
        $id_docente = obtenerIdDocenteDeSesion();
        if ($id_docente === null) throw new Exception("No se pudo encontrar el perfil del docente.");

        $sql = "
            SELECT 
                CASE
                    WHEN n.nota >= 0 AND n.nota <= 10 THEN 'Desaprobado (0-10)'
                    WHEN n.nota > 10 AND n.nota <= 14 THEN 'Aprobado (11-14)'
                    WHEN n.nota > 14 AND n.nota <= 17 THEN 'Bueno (15-17)'
                    WHEN n.nota > 17 AND n.nota <= 20 THEN 'Excelente (18-20)'
                END as rango,
                COUNT(n.id) as total
            FROM notas n
            INNER JOIN evaluaciones e ON n.id_evaluacion = e.id
            INNER JOIN docente_curso a ON e.id_curso = a.id_curso
            WHERE a.id_docente = ?
            GROUP BY rango
            ORDER BY FIELD(rango, 'Desaprobado (0-10)', 'Aprobado (11-14)', 'Bueno (15-17)', 'Excelente (18-20)');
        ";
        
        $resultado = select_all($sql, "i", [$id_docente]);
        $labels = []; $data = [];
        foreach ($resultado as $fila) {
            if ($fila['rango']) {
                $labels[] = $fila['rango'];
                $data[] = (int)$fila['total'];
            }
        }
        $backgroundColors = ['#dc3545', '#ffc107', '#28a745', '#007bff'];
        echo json_encode(['labels' => $labels, 'data' => $data, 'backgroundColors' => $backgroundColors]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener calificaciones del docente: ' . $e->getMessage()]);
    }
}

function obtenerResumenAsistenciaDocente() {
    try {
        $id_docente = obtenerIdDocenteDeSesion();
        if ($id_docente === null) throw new Exception("No se pudo encontrar el perfil del docente.");

        $sql = "SELECT 
                    CASE 
                        WHEN estado = 'ausente' THEN 'No Asistió'
                        ELSE 'Asistió'
                    END as estado_agrupado,
                    COUNT(id) as total 
                FROM asistencia
                WHERE id_curso IN (SELECT id_curso FROM docente_curso WHERE id_docente = ?)
                GROUP BY estado_agrupado";
        $resultado = select_all($sql, "i", [$id_docente]);
        
        $labels = []; $data = [];
        foreach($resultado as $fila) {
            $labels[] = $fila['estado_agrupado'];
            $data[] = (int)$fila['total'];
        }

        $backgroundColors = ['#28a745', '#dc3545']; // Verde para Asistió, Rojo para No Asistió
        echo json_encode(['labels' => $labels, 'data' => $data, 'backgroundColors' => $backgroundColors]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener resumen de asistencia: ' . $e->getMessage()]);
    }
}

// --- Funciones de Estudiante ---

function obtenerIdEstudianteDeSesion() {
    $id_user = $_SESSION['user_id'] ?? 0;
    if ($id_user === 0) return null;

    $result = select_one("SELECT id FROM estudiantes WHERE id_user = ?", "i", [$id_user]);
    return $result['id'] ?? null;
}

function obtenerKpisEstudiante() {
    try {
        $id_estudiante = obtenerIdEstudianteDeSesion();
        if ($id_estudiante === null) throw new Exception("No se pudo encontrar el perfil del estudiante.");

        $sql_cursos = "SELECT COUNT(id) as total FROM matriculas WHERE id_estudiante = ? AND estado = 'activo'";
        $total_cursos = select_one($sql_cursos, "i", [$id_estudiante])['total'] ?? 0;

        $sql_promedio = "SELECT AVG(nota) as promedio FROM notas WHERE id_estudiante = ?";
        $promedio_general = select_one($sql_promedio, "i", [$id_estudiante])['promedio'] ?? 0;

        $sql_asistencia = "SELECT estado, COUNT(id) as total FROM asistencia WHERE id_estudiante = ? GROUP BY estado";
        $asistencias = select_all($sql_asistencia, "i", [$id_estudiante]);
        $presentes = 0; $faltas = 0;
        foreach($asistencias as $asistencia) {
            if ($asistencia['estado'] == 'presente' || $asistencia['estado'] == 'tardanza') {
                $presentes += $asistencia['total'];
            } else if ($asistencia['estado'] == 'ausente') {
                $faltas += $asistencia['total'];
            }
        }
        $total_asistencias = $presentes + $faltas;
        $mi_asistencia = ($total_asistencias > 0) ? round(($presentes / $total_asistencias) * 100) : 0;

        echo json_encode([
            'total_cursos' => $total_cursos,
            'promedio_general' => round($promedio_general, 2),
            'mi_asistencia' => $mi_asistencia
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener los KPIs del estudiante: ' . $e->getMessage()]);
    }
}

function obtenerCalificacionesPorCursoEstudiante() {
    try {
        $id_estudiante = obtenerIdEstudianteDeSesion();
        if ($id_estudiante === null) throw new Exception("No se pudo encontrar el perfil del estudiante.");

        $sql = "
            SELECT c.nombre_curso, AVG(n.nota) as promedio
            FROM notas n
            INNER JOIN evaluaciones e ON n.id_evaluacion = e.id
            INNER JOIN cursos c ON e.id_curso = c.id
            WHERE n.id_estudiante = ?
            GROUP BY c.id, c.nombre_curso;
        ";
        
        $resultado = select_all($sql, "i", [$id_estudiante]);
        $labels = []; $data = [];
        foreach ($resultado as $fila) {
            $labels[] = $fila['nombre_curso'];
            $data[] = round($fila['promedio'], 2);
        }
        $backgroundColors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8', '#6c757d'];
        echo json_encode(['labels' => $labels, 'data' => $data, 'backgroundColors' => $backgroundColors]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener calificaciones por curso: ' . $e->getMessage()]);
    }
}

function obtenerResumenAsistenciaEstudiante() {
    try {
        $id_estudiante = obtenerIdEstudianteDeSesion();
        if ($id_estudiante === null) throw new Exception("No se pudo encontrar el perfil del estudiante.");

        $sql = "SELECT 
                    CASE 
                        WHEN estado = 'ausente' THEN 'No Asistió'
                        ELSE 'Asistió'
                    END as estado_agrupado,
                    COUNT(id) as total 
                FROM asistencia
                WHERE id_estudiante = ?
                GROUP BY estado_agrupado";
        $resultado = select_all($sql, "i", [$id_estudiante]);
        
        $labels = []; $data = [];
        foreach($resultado as $fila) {
            $labels[] = $fila['estado_agrupado'];
            $data[] = (int)$fila['total'];
        }

        $backgroundColors = ['#28a745', '#dc3545'];
        echo json_encode(['labels' => $labels, 'data' => $data, 'backgroundColors' => $backgroundColors]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener resumen de asistencia: ' . $e->getMessage()]);
    }
}

// --- Funciones de Pagos (Admin) ---

function obtenerKpisPagos() {
    try {
        // Con el nuevo sistema, solo podemos calcular los ingresos totales directamente.
        $sql_ingresos = "SELECT SUM(monto) as total FROM pagos WHERE estado = 'válido'";
        $ingresos_totales = select_one($sql_ingresos)['total'] ?? 0;

        echo json_encode([
            'ingresos_totales' => $ingresos_totales,
            'saldo_pendiente' => 0, // Obsoleto en el nuevo modelo
            'tasa_cobranza' => 100 // Obsoleto en el nuevo modelo
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener los KPIs de pagos: ' . $e->getMessage()]);
    }
}

function obtenerIngresosPorMes() {
    try {
        // Adaptado a la nueva tabla de pagos
        $sql = "
            SELECT YEAR(fecha_pago) as anio, MONTH(fecha_pago) as mes, SUM(monto) as total
            FROM pagos 
            WHERE estado = 'válido'
            GROUP BY anio, mes 
            ORDER BY anio, mes;
        ";
        $resultado = select_all($sql);
        $labels = []; $data = [];
        $meses = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
        foreach ($resultado as $fila) {
            $labels[] = $meses[$fila['mes'] - 1] . ' ' . $fila['anio'];
            $data[] = (float)$fila['total'];
        }
        echo json_encode(['labels' => $labels, 'data' => $data]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener ingresos por mes: ' . $e->getMessage()]);
    }
}

function obtenerResumenEstadoPensiones() {
    // Esta función es obsoleta con el nuevo sistema de pagos genéricos.
    // Se devuelve una respuesta vacía para evitar errores en el frontend si aún se llama.
    echo json_encode(['labels' => [], 'data' => []]);
}
?>
