<?php
$includeDataTablesCss = true;
$includeDataTablesJs = true;
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

$horario_cursos = [];
if ($id_estudiante > 0) {
    // Fetch courses the student is enrolled in, including the course ID
    $query_horario = "SELECT 
                        c.id,
                        c.nombre_curso,
                        c.codigo_curso,
                        c.horas_semanales,
                        ca.nombre_carrera,
                        m.periodo_academico
                      FROM matriculas m
                      JOIN cursos c ON m.id_curso = c.id
                      JOIN carreras ca ON c.id_carrera = ca.id
                      WHERE m.id_estudiante = ? AND m.estado = 'matriculado'
                      ORDER BY c.nombre_curso ASC";
    $stmt_horario = $conexion->prepare($query_horario);
    $stmt_horario->bind_param("i", $id_estudiante);
    $stmt_horario->execute();
    $resultado_horario = $stmt_horario->get_result();

    while ($curso = $resultado_horario->fetch_assoc()) {
        $horario_cursos[] = $curso;
    }
    $stmt_horario->close();
}
?>

<h1 class="mb-4">Mi Horario</h1>

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
        <h5 class="mb-0">Cursos Matriculados</h5>
    </div>
    <div class="card-body">
        <?php if (empty($horario_cursos)): ?>
            <p class="text-center">No estás matriculado en ningún curso actualmente.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover datatable">
                    <thead class="table-dark">
                        <tr>
                            <th>Código</th>
                            <th>Nombre del Curso</th>
                            <th>Carrera</th>
                            <th>Periodo Académico</th>
                            <th>Horas Semanales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($horario_cursos as $curso): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($curso['codigo_curso']); ?></td>
                                <td>
                                    <a href="detalle_curso.php?id_curso=<?php echo $curso['id']; ?>">
                                        <?php echo htmlspecialchars($curso['nombre_curso']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($curso['nombre_carrera']); ?></td>
                                <td><?php echo htmlspecialchars($curso['periodo_academico']); ?></td>
                                <td><?php echo htmlspecialchars($curso['horas_semanales']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$conexion->close();
require_once 'layout/footer.php';
?>
