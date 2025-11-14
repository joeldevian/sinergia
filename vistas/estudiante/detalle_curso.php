<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

$id_curso = $_GET['id_curso'] ?? 0;

if ($id_curso == 0) {
    echo "<div class='alert alert-danger'>ID de curso no válido.</div>";
    require_once 'layout/footer.php';
    exit();
}

// Fetch course details
$query_curso = "SELECT c.*, ca.nombre_carrera 
                FROM cursos c 
                JOIN carreras ca ON c.id_carrera = ca.id 
                WHERE c.id = ?";
$stmt_curso = $conexion->prepare($query_curso);
$stmt_curso->bind_param("i", $id_curso);
$stmt_curso->execute();
$resultado_curso = $stmt_curso->get_result();
$curso = $resultado_curso->fetch_assoc();
$stmt_curso->close();

if (!$curso) {
    echo "<div class='alert alert-danger'>Curso no encontrado.</div>";
    require_once 'layout/footer.php';
    exit();
}

// Fetch assigned teacher's name
$query_docente = "SELECT CONCAT(d.nombres, ' ', d.apellido_paterno) AS nombre_docente
                  FROM docente_curso dc
                  JOIN docentes d ON dc.id_docente = d.id
                  WHERE dc.id_curso = ? 
                  -- Assuming we need the teacher for the most recent period. 
                  -- A more complex system might need to know the student's specific period.
                  ORDER BY dc.periodo_academico DESC
                  LIMIT 1";
$stmt_docente = $conexion->prepare($query_docente);
$stmt_docente->bind_param("i", $id_curso);
$stmt_docente->execute();
$resultado_docente = $stmt_docente->get_result();
$docente = $resultado_docente->fetch_assoc();
$stmt_docente->close();

// Fetch evaluations for the course
$query_evaluaciones = "SELECT nombre_evaluacion, porcentaje 
                       FROM evaluaciones 
                       WHERE id_curso = ? 
                       ORDER BY id ASC";
$stmt_evaluaciones = $conexion->prepare($query_evaluaciones);
$stmt_evaluaciones->bind_param("i", $id_curso);
$stmt_evaluaciones->execute();
$resultado_evaluaciones = $stmt_evaluaciones->get_result();
$evaluaciones = [];
while ($eval = $resultado_evaluaciones->fetch_assoc()) {
    $evaluaciones[] = $eval;
}
$stmt_evaluaciones->close();

?>

<h1 class="mb-4">Detalle del Curso</h1>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><?php echo htmlspecialchars($curso['nombre_curso']); ?></h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h5 class="card-title">Información General</h5>
                <ul class="list-group list-group-flush mb-4">
                    <li class="list-group-item"><strong>Código:</strong> <?php echo htmlspecialchars($curso['codigo_curso']); ?></li>
                    <li class="list-group-item"><strong>Carrera:</strong> <?php echo htmlspecialchars($curso['nombre_carrera']); ?></li>
                    <li class="list-group-item"><strong>Ciclo:</strong> <?php echo htmlspecialchars($curso['ciclo']); ?></li>
                    <li class="list-group-item"><strong>Créditos:</strong> <?php echo htmlspecialchars($curso['creditos']); ?></li>
                    <li class="list-group-item"><strong>Horas Semanales:</strong> <?php echo htmlspecialchars($curso['horas_semanales']); ?></li>
                    <li class="list-group-item"><strong>Tipo:</strong> <?php echo ucfirst(htmlspecialchars($curso['tipo'])); ?></li>
                    <li class="list-group-item"><strong>Docente:</strong> <?php echo $docente ? htmlspecialchars($docente['nombre_docente']) : 'No asignado'; ?></li>
                </ul>
            </div>
            <div class="col-md-4">
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
        <hr>
        <a href="mi_horario.php" class="btn btn-secondary">Volver a Mi Horario</a>
    </div>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
