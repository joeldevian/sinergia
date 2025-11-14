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

$asistencia_por_curso = [];
if ($id_estudiante > 0) {
    $query_asistencia = "SELECT 
                            c.nombre_curso,
                            a.fecha,
                            a.estado
                         FROM asistencia a
                         JOIN cursos c ON a.id_curso = c.id
                         WHERE a.id_estudiante = ?
                         ORDER BY c.nombre_curso, a.fecha DESC";
    $stmt_asistencia = $conexion->prepare($query_asistencia);
    $stmt_asistencia->bind_param("i", $id_estudiante);
    $stmt_asistencia->execute();
    $resultado_asistencia = $stmt_asistencia->get_result();

    while ($asistencia = $resultado_asistencia->fetch_assoc()) {
        $asistencia_por_curso[$asistencia['nombre_curso']][] = $asistencia;
    }
    $stmt_asistencia->close();
}
?>

<h1 class="mb-4">Mi Asistencia</h1>

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
        <h5 class="mb-0">Detalle de Asistencia</h5>
    </div>
    <div class="card-body">
        <?php if (empty($asistencia_por_curso)): ?>
            <p class="text-center">No tienes registros de asistencia aún.</p>
        <?php else: ?>
            <?php foreach ($asistencia_por_curso as $nombre_curso => $registros): ?>
                <div class="mb-4">
                    <h4>Curso: <?php echo htmlspecialchars($nombre_curso); ?></h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registros as $registro): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($registro['fecha']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                if ($registro['estado'] == 'asistio') echo 'success';
                                                else if ($registro['estado'] == 'falto') echo 'danger';
                                                else if ($registro['estado'] == 'tardanza') echo 'warning';
                                                else echo 'secondary';
                                            ?>">
                                                <?php echo ucfirst($registro['estado']); ?>
                                            </span>
                                        </td>
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
