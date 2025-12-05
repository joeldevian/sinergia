<?php
$includeDataTablesCss = true;
$includeDataTablesJs = true;
require_once 'layout/header.php';
require_once '../../config/conexion.php';
require_once '../../controladores/asistencia_reporte_controller.php'; // Incluir el controlador con la lógica del reporte

$id_curso = $_GET['id_curso'] ?? 0;
$fecha_seleccionada = $_GET['fecha'] ?? date('Y-m-d'); // Mantener para consistencia, aunque no se usa directamente en el reporte consolidado

if ($id_curso == 0) {
    $_SESSION['mensaje'] = "ID de curso no válido.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: mis_cursos.php");
    exit();
}

// Fetch course details
$stmt_curso = $conexion->prepare("SELECT nombre_curso FROM cursos WHERE id = ?");
$stmt_curso->bind_param("i", $id_curso);
$stmt_curso->execute();
$resultado_curso = $stmt_curso->get_result();
$curso = $resultado_curso->fetch_assoc();
$stmt_curso->close();

if (!$curso) {
    $_SESSION['mensaje'] = "Curso no encontrado.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: mis_cursos.php");
    exit();
}

// Obtener el reporte consolidado
$reporte_consolidado = getReporteConsolidadoAsistencia($id_curso);

?>

<h1 class="mb-4">Reporte Consolidado de Asistencia - <?php echo htmlspecialchars($curso['nombre_curso']); ?></h1>

<?php
if (isset($_SESSION['mensaje'])) {
    echo '<div class="alert alert-' . $_SESSION['mensaje_tipo'] . ' alert-dismissible fade show" role="alert">';
    echo $_SESSION['mensaje'];
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    unset($_SESSION['mensaje']);
    unset($_SESSION['mensaje_tipo']);
}
?>

<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">Detalle del Reporte</h5>
    </div>
    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <a href="../../controladores/generar_reporte_asistencia_pdf.php?id_curso=<?php echo $id_curso; ?>" class="btn btn-danger me-2" target="_blank">Exportar a PDF</a>
            <a href="../../controladores/generar_reporte_asistencia_excel.php?id_curso=<?php echo $id_curso; ?>" class="btn btn-success" target="_blank">Exportar a Excel</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Nombres</th>
                        <th>Clases Reg.</th>
                        <th>Asistencias</th>
                        <th>Faltas</th>
                        <th>Tardanzas</th>
                        <th>Justificadas</th>
                        <th>% Asistencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($reporte_consolidado)):
                    ?>
                        <tr>
                            <td colspan="8" class="text-center">No hay datos de asistencia para este curso.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reporte_consolidado as $estudiante_reporte): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($estudiante_reporte['codigo_estudiante']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante_reporte['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante_reporte['total_clases_registradas']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante_reporte['asistencias']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante_reporte['faltas']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante_reporte['tardanzas']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante_reporte['justificadas']); ?></td>
                                <td>
                                    <?php
                                    $porcentaje = $estudiante_reporte['porcentaje_asistencia'];
                                    $color_class = '';
                                    if ($porcentaje >= 70) {
                                        $color_class = 'text-success'; // Verde
                                    } elseif ($porcentaje >= 60) {
                                        $color_class = 'text-warning'; // Amarillo
                                    } else {
                                        $color_class = 'text-danger'; // Rojo
                                    }
                                    echo '<span class="' . $color_class . ' fw-bold">' . htmlspecialchars($porcentaje) . '%</span>';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>