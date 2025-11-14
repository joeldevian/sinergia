<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

$id_curso = $_GET['id_curso'] ?? 0;

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

// Fetch students enrolled in this course
$query_estudiantes = "SELECT e.id, CONCAT(e.nombres, ' ', e.apellido_paterno, ' ', e.apellido_materno) AS nombre_completo
                      FROM matriculas m
                      JOIN estudiantes e ON m.id_estudiante = e.id
                      WHERE m.id_curso = ? AND m.estado = 'matriculado'
                      ORDER BY nombre_completo ASC";
$stmt_estudiantes = $conexion->prepare($query_estudiantes);
$stmt_estudiantes->bind_param("i", $id_curso);
$stmt_estudiantes->execute();
$resultado_estudiantes = $stmt_estudiantes->get_result();
$estudiantes = [];
while ($estudiante = $resultado_estudiantes->fetch_assoc()) {
    $estudiantes[] = $estudiante;
}
$stmt_estudiantes->close();

// Fetch evaluations for this course
$query_evaluaciones = "SELECT id, nombre_evaluacion, porcentaje FROM evaluaciones WHERE id_curso = ? ORDER BY id ASC";
$stmt_evaluaciones = $conexion->prepare($query_evaluaciones);
$stmt_evaluaciones->bind_param("i", $id_curso);
$stmt_evaluaciones->execute();
$resultado_evaluaciones = $stmt_evaluaciones->get_result();
$evaluaciones = [];
while ($evaluacion = $resultado_evaluaciones->fetch_assoc()) {
    $evaluaciones[] = $evaluacion;
}
$stmt_evaluaciones->close();

// Fetch existing grades for these students and evaluations
$notas_existentes = [];
if (!empty($estudiantes) && !empty($evaluaciones)) {
    $estudiante_ids = array_column($estudiantes, 'id');
    $evaluacion_ids = array_column($evaluaciones, 'id');

    $placeholders_estudiantes = implode(',', array_fill(0, count($estudiante_ids), '?'));
    $placeholders_evaluaciones = implode(',', array_fill(0, count($evaluacion_ids), '?'));

    $types = str_repeat('i', count($estudiante_ids)) . str_repeat('i', count($evaluacion_ids));
    $params = array_merge($estudiante_ids, $evaluacion_ids);

    $query_notas = "SELECT id_estudiante, id_evaluacion, nota FROM notas 
                    WHERE id_estudiante IN ($placeholders_estudiantes) 
                    AND id_evaluacion IN ($placeholders_evaluaciones)";
    $stmt_notas = $conexion->prepare($query_notas);
    $stmt_notas->bind_param($types, ...$params);
    $stmt_notas->execute();
    $resultado_notas = $stmt_notas->get_result();
    while ($nota = $resultado_notas->fetch_assoc()) {
        $notas_existentes[$nota['id_estudiante']][$nota['id_evaluacion']] = $nota['nota'];
    }
    $stmt_notas->close();
}
?>

<h1 class="mb-4">Gestionar Notas para: <?php echo htmlspecialchars($curso['nombre_curso']); ?></h1>

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

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Evaluaciones del Curso</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($evaluaciones)): ?>
            <ul class="list-group">
                <?php foreach ($evaluaciones as $eval): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($eval['nombre_evaluacion']); ?> 
                        <span class="badge bg-secondary rounded-pill"><?php echo htmlspecialchars($eval['porcentaje']); ?>%</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-center">No hay evaluaciones definidas para este curso. Por favor, defina evaluaciones en el panel de administración.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Registro de Notas</h5>
        <a href="../../controladores/generar_reporte_notas_curso_pdf.php?id_curso=<?php echo $id_curso; ?>" class="btn btn-info btn-sm" target="_blank">
            <i class="fas fa-file-pdf me-2"></i>Descargar Reporte
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($estudiantes)): ?>
            <p class="text-center">No hay estudiantes matriculados en este curso.</p>
        <?php elseif (empty($evaluaciones)): ?>
            <p class="text-center">No se pueden registrar notas sin evaluaciones definidas para el curso.</p>
        <?php else: ?>
            <form action="../../controladores/nota_controller.php" method="POST">
                <input type="hidden" name="accion" value="guardar_notas">
                <input type="hidden" name="id_curso" value="<?php echo $id_curso; ?>">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Estudiante</th>
                                <?php foreach ($evaluaciones as $eval): ?>
                                    <th><?php echo htmlspecialchars($eval['nombre_evaluacion']); ?> (<?php echo htmlspecialchars($eval['porcentaje']); ?>%)</th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $estudiante): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($estudiante['nombre_completo']); ?></td>
                                    <?php foreach ($evaluaciones as $eval): ?>
                                        <td>
                                            <input type="number" step="0.01" min="0" max="20" 
                                                   name="notas[<?php echo $estudiante['id']; ?>][<?php echo $eval['id']; ?>]" 
                                                   class="form-control form-control-sm" 
                                                   value="<?php echo htmlspecialchars($notas_existentes[$estudiante['id']][$eval['id']] ?? ''); ?>">
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success">Guardar Notas</button>
                    <a href="mis_cursos.php" class="btn btn-secondary">Volver a Mis Cursos</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
