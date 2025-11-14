<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

// Fetch enrollments from the database
$query = "SELECT m.id, CONCAT(e.nombres, ' ', e.apellido_paterno, ' ', e.apellido_materno) AS nombre_estudiante, 
                 c.nombre_curso, m.periodo_academico, m.fecha_matricula, m.estado
          FROM matriculas m
          JOIN estudiantes e ON m.id_estudiante = e.id
          JOIN cursos c ON m.id_curso = c.id
          ORDER BY m.fecha_matricula DESC";
$resultado = $conexion->query($query);
?>

<h1 class="mb-4">Gestionar Matrículas</h1>

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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Matrículas</h5>
        <a href="agregar_matricula.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Agregar Matrícula
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
                <thead class="table-dark">
                    <tr>
                        <th>Estudiante</th>
                        <th>Curso</th>
                        <th>Periodo Académico</th>
                        <th>Fecha de Matrícula</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while($matricula = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($matricula['nombre_estudiante']); ?></td>
                                <td><?php echo htmlspecialchars($matricula['nombre_curso']); ?></td>
                                <td><?php echo htmlspecialchars($matricula['periodo_academico']); ?></td>
                                <td><?php echo htmlspecialchars($matricula['fecha_matricula']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        if ($matricula['estado'] == 'matriculado') echo 'primary';
                                        else if ($matricula['estado'] == 'aprobado') echo 'success';
                                        else if ($matricula['estado'] == 'retirado') echo 'warning';
                                        else if ($matricula['estado'] == 'aplazado') echo 'danger';
                                        else echo 'secondary';
                                    ?>">
                                        <?php echo ucfirst($matricula['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="editar_matricula.php?id=<?php echo $matricula['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../../controladores/matricula_controller.php?accion=eliminar&id=<?php echo $matricula['id']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar esta matrícula?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay matrículas registradas.</td>
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
