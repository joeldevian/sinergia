<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

// Get the logged-in teacher's user ID
$id_user_docente = $_SESSION['user_id'];

// Fetch the teacher's ID from the docentes table
$stmt_docente_id = $conexion->prepare("SELECT id FROM docentes WHERE id_user = ?");
$stmt_docente_id->bind_param("i", $id_user_docente);
$stmt_docente_id->execute();
$resultado_docente_id = $stmt_docente_id->get_result();
$docente_data = $resultado_docente_id->fetch_assoc();
$id_docente = $docente_data['id'] ?? 0;
$stmt_docente_id->close();

$cursos = [];
if ($id_docente > 0) {
    // Fetch courses assigned to this teacher (assuming a linking table or direct assignment in 'cursos' table)
    // For now, let's assume a simple scenario where a teacher is assigned to a course.
    // If there's no direct link, we'd need an 'asignacion_docente_curso' table.
    // For this example, let's display all active courses for now,
    // and mention that a proper assignment mechanism would be needed.

    // For now, display all active courses. A proper system would link teachers to courses.
    $query_cursos = "SELECT c.id, c.codigo_curso, c.nombre_curso, c.creditos, c.horas_semanales, ca.nombre_carrera, c.ciclo, c.tipo
                     FROM cursos c
                     JOIN carreras ca ON c.id_carrera = ca.id
                     WHERE c.estado = 'activo'
                     ORDER BY ca.nombre_carrera, c.nombre_curso ASC";
    $resultado_cursos = $conexion->query($query_cursos);
    if ($resultado_cursos) {
        while ($curso = $resultado_cursos->fetch_assoc()) {
            $cursos[] = $curso;
        }
    }
}
?>

<h1 class="mb-4">Mis Cursos</h1>

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
        <h5 class="mb-0">Cursos Asignados</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Nombre del Curso</th>
                        <th>Créditos</th>
                        <th>Horas Semanales</th>
                        <th>Carrera</th>
                        <th>Ciclo</th>
                        <th>Tipo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cursos)): ?>
                        <?php foreach($cursos as $curso): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($curso['codigo_curso']); ?></td>
                                <td><?php echo htmlspecialchars($curso['nombre_curso']); ?></td>
                                <td><?php echo htmlspecialchars($curso['creditos']); ?></td>
                                <td><?php echo htmlspecialchars($curso['horas_semanales']); ?></td>
                                <td><?php echo htmlspecialchars($curso['nombre_carrera']); ?></td>
                                <td><?php echo htmlspecialchars($curso['ciclo']); ?></td>
                                <td><?php echo htmlspecialchars($curso['tipo']); ?></td>
                                <td>
                                    <a href="gestionar_notas_curso.php?id_curso=<?php echo $curso['id']; ?>" class="btn btn-sm btn-info" title="Gestionar Notas">
                                        <i class="fas fa-graduation-cap"></i> Notas
                                    </a>
                                    <a href="gestionar_asistencia_curso.php?id_curso=<?php echo $curso['id']; ?>" class="btn btn-sm btn-primary" title="Gestionar Asistencia">
                                        <i class="fas fa-clipboard-check"></i> Asistencia
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No tienes cursos asignados o no hay cursos activos.</td>
                        </tr>
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
