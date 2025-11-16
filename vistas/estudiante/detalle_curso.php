<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';
require_once '../../controladores/recurso_controller.php'; // Include the RecursoController

$id_asignacion = $_GET['id_asignacion'] ?? 0;

if ($id_asignacion == 0) {
    echo "<div class='alert alert-danger'>ID de asignación no válido.</div>";
    require_once 'layout/footer.php';
    exit();
}

// Fetch assignment, course, and teacher details
$query_asignacion = "SELECT
                        a.id AS id_asignacion,
                        c.id AS id_curso,
                        c.nombre_curso,
                        c.codigo_curso,
                        c.horas_semanales,
                        c.ciclo,
                        c.creditos,
                        c.tipo,
                        ca.nombre_carrera,
                        d.nombres AS docente_nombres,
                        d.apellido_paterno AS docente_apellido_paterno,
                        d.apellido_materno AS docente_apellido_materno,
                        a.periodo_academico
                     FROM docente_curso a
                     JOIN cursos c ON a.id_curso = c.id
                     JOIN carreras ca ON c.id_carrera = ca.id
                     JOIN docentes d ON a.id_docente = d.id
                     WHERE a.id = ?";
$stmt_asignacion = $conexion->prepare($query_asignacion);
$stmt_asignacion->bind_param("i", $id_asignacion);
$stmt_asignacion->execute();
$resultado_asignacion = $stmt_asignacion->get_result();
$asignacion_data = $resultado_asignacion->fetch_assoc();
$stmt_asignacion->close();

if (!$asignacion_data) {
    echo "<div class='alert alert-danger'>Asignación no encontrada.</div>";
    require_once 'layout/footer.php';
    exit();
}

// Fetch evaluations for the course (using id_curso from assignment data)
$query_evaluaciones = "SELECT nombre_evaluacion, porcentaje
                       FROM evaluaciones
                       WHERE id_curso = ?
                       ORDER BY id ASC";
$stmt_evaluaciones = $conexion->prepare($query_evaluaciones);
$stmt_evaluaciones->bind_param("i", $asignacion_data['id_curso']);
$stmt_evaluaciones->execute();
$resultado_evaluaciones = $stmt_evaluaciones->get_result();
$evaluaciones = [];
while ($eval = $resultado_evaluaciones->fetch_assoc()) {
    $evaluaciones[] = $eval;
}
$stmt_evaluaciones->close();

// Fetch existing resources for this assignment
$recursos = RecursoController::obtenerRecursosPorAsignacion($conexion, $id_asignacion);

// Fetch existing communications for this assignment
$comunicaciones = RecursoController::obtenerComunicacionesPorAsignacion($conexion, $id_asignacion);

?>

<h1 class="mb-4">Detalle del Curso: <?php echo htmlspecialchars($asignacion_data['nombre_curso']); ?></h1>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><?php echo htmlspecialchars($asignacion_data['nombre_curso']); ?> (<?php echo htmlspecialchars($asignacion_data['periodo_academico']); ?>)</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5 class="card-title">Información General</h5>
                <ul class="list-group list-group-flush mb-4">
                    <li class="list-group-item"><strong>Código:</strong> <?php echo htmlspecialchars($asignacion_data['codigo_curso']); ?></li>
                    <li class="list-group-item"><strong>Carrera:</strong> <?php echo htmlspecialchars($asignacion_data['nombre_carrera']); ?></li>
                    <li class="list-group-item"><strong>Ciclo:</strong> <?php echo htmlspecialchars($asignacion_data['ciclo']); ?></li>
                    <li class="list-group-item"><strong>Créditos:</strong> <?php echo htmlspecialchars($asignacion_data['creditos']); ?></li>
                    <li class="list-group-item"><strong>Horas Semanales:</strong> <?php echo htmlspecialchars($asignacion_data['horas_semanales']); ?></li>
                    <li class="list-group-item"><strong>Tipo:</strong> <?php echo ucfirst(htmlspecialchars($asignacion_data['tipo'])); ?></li>
                    <li class="list-group-item"><strong>Docente:</strong> <?php echo htmlspecialchars($asignacion_data['docente_nombres'] . ' ' . $asignacion_data['docente_apellido_paterno'] . ' ' . $asignacion_data['docente_apellido_materno']); ?></li>
                </ul>
            </div>
            <div class="col-md-6">
                <h5 class="card-title">Evaluaciones Programadas</h5>
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
                    <p>No hay evaluaciones programadas para este curso.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Recursos del Curso</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recursos)): ?>
                    <ul class="list-group">
                        <?php foreach ($recursos as $recurso): ?>
                            <li class="list-group-item">
                                <strong><?php echo htmlspecialchars($recurso['titulo']); ?></strong>
                                <p class="mb-0 text-muted"><?php echo htmlspecialchars($recurso['descripcion']); ?></p>
                                <?php if ($recurso['tipo_recurso'] == 'archivo'): ?>
                                    <a href="../../uploads/recursos_curso/<?php echo htmlspecialchars($recurso['ruta']); ?>" target="_blank" class="badge bg-secondary"><i class="fas fa-download"></i> Descargar Archivo</a>
                                <?php else: ?>
                                    <a href="<?php echo htmlspecialchars($recurso['ruta']); ?>" target="_blank" class="badge bg-secondary"><i class="fas fa-external-link-alt"></i> Ir al Enlace</a>
                                <?php endif; ?>
                                <small class="text-muted d-block">Publicado: <?php echo date('d/m/Y H:i', strtotime($recurso['fecha_creacion'])); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-center">No hay recursos disponibles para este curso.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Comunicados del Curso</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($comunicaciones)): ?>
                    <ul class="list-group">
                        <?php foreach ($comunicaciones as $comunicacion): ?>
                            <li class="list-group-item">
                                <strong><?php echo htmlspecialchars($comunicacion['titulo']); ?></strong>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($comunicacion['mensaje'])); ?></p>
                                <small class="text-muted d-block">Publicado: <?php echo date('d/m/Y H:i', strtotime($comunicacion['fecha_creacion'])); ?></small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-center">No hay comunicados para este curso.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="mi_horario.php" class="btn btn-secondary">Volver a Mi Horario</a>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>