<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

// Fetch students from the database
$query = "SELECT id, codigo_estudiante, dni, apellido_paterno, apellido_materno, nombres, email, estado FROM estudiantes ORDER BY apellido_paterno ASC";
$resultado = $conexion->query($query);
?>

<h1 class="mb-4">Gestionar Estudiantes</h1>

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
        <h5 class="mb-0">Listado de Estudiantes</h5>
        <a href="agregar_estudiante.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Agregar Estudiante
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>DNI</th>
                        <th>Apellidos</th>
                        <th>Nombres</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while($estudiante = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($estudiante['codigo_estudiante']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['dni']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['apellido_paterno'] . ' ' . $estudiante['apellido_materno']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['nombres']); ?></td>
                                <td><?php echo htmlspecialchars($estudiante['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $estudiante['estado'] == 'activo' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($estudiante['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="editar_estudiante.php?id=<?php echo $estudiante['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../../controladores/estudiante_controller.php?accion=eliminar&id=<?php echo $estudiante['id']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar a este estudiante y todos sus datos asociados (matrículas, notas, etc)? Esta acción es irreversible.');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay estudiantes registrados.</td>
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
