<?php
require_once 'layout/header.php';
require_once '../../config/database.php';

// Ensure user is admin and summary data exists
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin' || !isset($_SESSION['matricula_summary'])) {
    header("Location: gestionar_matriculas.php");
    exit();
}

// Retrieve summary data from session
$summary = $_SESSION['matricula_summary'];
$id_estudiante = $summary['id_estudiante'];
$cursos_matriculados_ids = $summary['cursos_matriculados_ids'];

// Fetch student data
$estudiante = select_one("SELECT CONCAT(nombres, ' ', apellido_paterno, ' ', apellido_materno) AS nombre_completo FROM estudiantes WHERE id = ?", "i", [$id_estudiante]);

// Fetch details of enrolled courses
$cursos_matriculados = [];
if (!empty($cursos_matriculados_ids)) {
    // Create placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($cursos_matriculados_ids), '?'));
    $types = str_repeat('i', count($cursos_matriculados_ids));
    
    $query_cursos = "SELECT codigo_curso, nombre_curso, creditos FROM cursos WHERE id IN ($placeholders)";
    $cursos_matriculados = select_all($query_cursos, $types, $cursos_matriculados_ids);
}

// Display the confirmation message (e.g., "3 added, 1 skipped")
if (isset($summary['mensaje'])) {
    // We use a different session variable to display the toast notification
    $_SESSION['status_message'] = $summary['mensaje'];
    $_SESSION['status_type'] = $summary['mensaje_tipo'];
}

// Unset the summary from session so it doesn't show again
unset($_SESSION['matricula_summary']);
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header text-white" style="background-color: var(--primary-red);">
            <h4 class="mb-0">Comprobante de Matrícula</h4>
        </div>
        <div class="card-body">
            
            <div class="row mb-4">
                <div class="col-md-6">
                    <p><strong>Estudiante:</strong> <?php echo htmlspecialchars($estudiante['nombre_completo'] ?? 'No encontrado'); ?></p>
                    <p><strong>Periodo Académico:</strong> <?php echo htmlspecialchars($summary['periodo_academico']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Fecha de Matrícula:</strong> <?php echo htmlspecialchars(date("d/m/Y", strtotime($summary['fecha_matricula']))); ?></p>
                </div>
            </div>

            <h6>Cursos Matriculados</h6>
            <?php if (!empty($cursos_matriculados)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Nombre del Curso</th>
                                <th class="text-center">Créditos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_creditos = 0;
                            foreach ($cursos_matriculados as $curso): 
                                $total_creditos += (int)$curso['creditos'];
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($curso['codigo_curso']); ?></td>
                                    <td><?php echo htmlspecialchars($curso['nombre_curso']); ?></td>
                                    <td class="text-center"><?php echo htmlspecialchars($curso['creditos']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th colspan="2" class="text-end">Total de Créditos:</th>
                                <th class="text-center"><?php echo $total_creditos; ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">No se ha matriculado ningún curso nuevo en esta operación.</div>
            <?php endif; ?>

            <div class="mt-4 text-end">
                <a href="../../controladores/generar_comprobante_matricula_pdf.php?id_estudiante=<?php echo $id_estudiante; ?>&periodo=<?php echo urlencode($summary['periodo_academico']); ?>&fecha=<?php echo urlencode($summary['fecha_matricula']); ?>&cursos=<?php echo implode(',', $cursos_matriculados_ids); ?>" class="btn btn-info" target="_blank"><i class="fas fa-file-pdf me-2"></i>Generar Comprobante</a>
                <a href="gestionar_matriculas.php" class="btn btn-primary">Finalizar</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'layout/footer.php';
?>
