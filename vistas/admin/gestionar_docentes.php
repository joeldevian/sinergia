<?php
require_once 'layout/header.php';
require_once '../../config/conexion.php';

// Fetch teachers from the database
$query = "SELECT id, codigo_docente, dni, apellido_paterno, apellido_materno, nombres, email, estado FROM docentes ORDER BY apellido_paterno ASC";
$resultado = $conexion->query($query);
?>

<h1 class="mb-4">Gestionar Docentes</h1>

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
        <h5 class="mb-0">Listado de Docentes</h5>
        <div>
            <a href="../../controladores/generar_listado_docentes_pdf.php" class="btn btn-info me-2" target="_blank">
                <i class="fas fa-file-pdf me-2"></i>Exportar a PDF
            </a>
            <a href="agregar_docente.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Agregar Docente
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable">
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
                        <?php while($docente = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($docente['codigo_docente']); ?></td>
                                <td><?php echo htmlspecialchars($docente['dni']); ?></td>
                                <td><?php echo htmlspecialchars($docente['apellido_paterno'] . ' ' . $docente['apellido_materno']); ?></td>
                                <td><?php echo htmlspecialchars($docente['nombres']); ?></td>
                                <td><?php echo htmlspecialchars($docente['email']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $docente['estado'] == 'activo' ? 'success' : ($docente['estado'] == 'inactivo' ? 'danger' : 'warning'); ?>">
                                        <?php echo ucfirst($docente['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="editar_docente.php?id=<?php echo $docente['id']; ?>" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../../controladores/docente_controller.php?accion=eliminar&id=<?php echo $docente['id']; ?>" class="btn btn-sm btn-danger" title="Eliminar" onclick="return confirm('¿Estás seguro de que quieres eliminar a este docente y todos sus datos asociados? Esta acción es irreversible.');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay docentes registrados.</td>
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
