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
    // Fetch courses assigned to this teacher
    $query_cursos = "SELECT 
                        c.id, c.codigo_curso, c.nombre_curso, dc.periodo_academico
                     FROM docente_curso dc
                     JOIN cursos c ON dc.id_curso = c.id
                     WHERE dc.id_docente = ?
                     ORDER BY dc.periodo_academico DESC, c.nombre_curso ASC";
    $stmt_cursos = $conexion->prepare($query_cursos);
    $stmt_cursos->bind_param("i", $id_docente);
    $stmt_cursos->execute();
    $resultado_cursos = $stmt_cursos->get_result();
    if ($resultado_cursos) {
        while ($curso = $resultado_cursos->fetch_assoc()) {
            $cursos[] = $curso;
        }
    }
}
?>

<h1 class="mb-4">Gestionar Notas</h1>
<p>Selecciona un curso para registrar o modificar las calificaciones de los estudiantes.</p>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Mis Cursos Asignados</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Periodo</th>
                        <th>Código</th>
                        <th>Nombre del Curso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cursos)): ?>
                        <?php foreach($cursos as $curso): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($curso['periodo_academico']); ?></td>
                                <td><?php echo htmlspecialchars($curso['codigo_curso']); ?></td>
                                <td><?php echo htmlspecialchars($curso['nombre_curso']); ?></td>
                                <td>
                                    <a href="gestionar_notas_curso.php?id_curso=<?php echo $curso['id']; ?>" class="btn btn-sm btn-info" title="Gestionar Notas">
                                        <i class="fas fa-graduation-cap"></i> Gestionar Notas
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No tienes cursos asignados.</td>
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
