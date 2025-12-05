<?php
$includeDataTablesCss = true;
$includeDataTablesJs = true;
require_once 'layout/header.php';
require_once '../../config/database.php';

// Validar el ID del estudiante desde la URL
$id_estudiante = filter_input(INPUT_GET, 'id_estudiante', FILTER_VALIDATE_INT);
if (!$id_estudiante) {
    echo "<h1 class='text-danger'>Error: ID de estudiante no válido.</h1>";
    require_once 'layout/footer.php';
    exit();
}

// Obtener el nombre del estudiante
$estudiante = select_one("SELECT CONCAT(nombres, ' ', apellido_paterno) as nombre_completo FROM estudiantes WHERE id = ?", "i", [$id_estudiante]);
if (!$estudiante) {
    echo "<h1 class='text-danger'>Error: Estudiante no encontrado.</h1>";
    require_once 'layout/footer.php';
    exit();
}

// Nueva consulta: Obtener todos los pagos genéricos para este estudiante
$pagos = select_all("SELECT * FROM pagos WHERE id_estudiante = ? ORDER BY fecha_pago DESC", "i", [$id_estudiante]);
?>

<h1 class="mb-4">Historial de Pagos de: <span class="text-primary"><?php echo htmlspecialchars($estudiante['nombre_completo']); ?></span></h1>

<!-- Fila de Acciones de Pago -->
<div class="row mb-4">
    <div class="col-lg-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <div class="mb-3">
                    <i class="fas fa-plus-circle fa-3x text-primary"></i>
                </div>
                <h5 class="card-title">Registrar Nuevo Pago</h5>
                <p class="card-text">Registra un pago directo por cualquier concepto para este estudiante.</p>
                <div class="mt-auto">
                    <a href="agregar_pago.php?id_estudiante=<?php echo $id_estudiante; ?>" class="btn btn-primary">Registrar Pago</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Historial de Pagos -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Historial de Pagos Registrados</h6>
    </div>
    <div class="card-body">
        <?php if (empty($pagos)): ?>
            <p class="text-center">Este estudiante no tiene pagos registrados.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover datatable" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>N° Recibo</th>
                            <th>Concepto</th>
                            <th>Monto Pagado</th>
                            <th>Fecha de Pago</th>
                            <th>Método de Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                            <tr class="align-middle">
                                <td><strong><?php echo htmlspecialchars($pago['numero_recibo']); ?></strong></td>
                                <td><?php echo htmlspecialchars($pago['concepto']); ?></td>
                                <td class="font-weight-bold text-success">S/ <?php echo number_format($pago['monto'], 2); ?></td>
                                <td><?php echo date("d/m/Y", strtotime($pago['fecha_pago'])); ?></td>
                                <td><?php echo htmlspecialchars($pago['metodo_pago']); ?></td>
                                <td>
                                    <a href="../../controladores/generar_comprobante_pago_pdf.php?id=<?php echo $pago['id']; ?>" target="_blank" class="btn btn-info btn-sm" title="Ver Comprobante PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php require_once 'layout/footer.php'; ?>