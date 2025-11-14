<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

// Get the logged-in student's user ID
$id_user_estudiante = $_SESSION['user_id'];

// Fetch the student's ID from the estudiantes table
$stmt_estudiante_id = $conexion->prepare("SELECT id FROM estudiantes WHERE id_user = ?");
$stmt_estudiante_id->bind_param("i", $id_user_estudiante);
$stmt_estudiante_id->execute();
$resultado_estudiante_id = $stmt_estudiante_id->get_result();
$estudiante_data = $resultado_estudiante_id->fetch_assoc();
$id_estudiante = $estudiante_data['id'] ?? 0;
$stmt_estudiante_id->close();

$calificaciones_por_curso = [];
if ($id_estudiante > 0) {
    $query_calificaciones = "SELECT 
                                c.nombre_curso,
                                e.nombre_evaluacion,
                                e.porcentaje,
                                n.nota
                             FROM notas n
                             JOIN evaluaciones e ON n.id_evaluacion = e.id
                             JOIN cursos c ON e.id_curso = c.id
                             WHERE n.id_estudiante = ?
                             ORDER BY c.nombre_curso, e.nombre_evaluacion ASC";
    $stmt_calificaciones = $conexion->prepare($query_calificaciones);
    $stmt_calificaciones->bind_param("i", $id_estudiante);
    $stmt_calificaciones->execute();
    $resultado_calificaciones = $stmt_calificaciones->get_result();

    while ($calificacion = $resultado_calificaciones->fetch_assoc()) {
        $calificaciones_por_curso[$calificacion['nombre_curso']][] = $calificacion;
    }
    $stmt_calificaciones->close();
}
?>

<h1 class="mb-4">Mis Calificaciones</h1>

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

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Detalle de Calificaciones</h5>
    </div>
    <div class="card-body">
        <?php if (empty($calificaciones_por_curso)): ?>
            <p class="text-center">No tienes calificaciones registradas aún.</p>
        <?php else: ?>
            <?php foreach ($calificaciones_por_curso as $nombre_curso => $calificaciones): ?>
                <div class="mb-4">
                    <h4>Curso: <?php echo htmlspecialchars($nombre_curso); ?></h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Evaluación</th>
                                    <th>Porcentaje</th>
                                    <th>Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($calificaciones as $calificacion): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($calificacion['nombre_evaluacion']); ?></td>
                                        <td><?php echo htmlspecialchars($calificacion['porcentaje']); ?>%</td>
                                        <td><?php echo htmlspecialchars($calificacion['nota']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
