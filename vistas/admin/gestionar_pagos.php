<?php
$includeDataTablesCss = true;
$includeDataTablesJs = true;
require_once 'layout/header.php';
require_once '../../config/database.php'; // Usamos el nuevo sistema

// Consulta para obtener el resumen de pagos por estudiante
$query_resumen = "
    SELECT 
        e.id as id_estudiante,
        CONCAT(e.nombres, ' ', e.apellido_paterno, ' ', e.apellido_materno) as nombre_completo,
        COALESCE(SUM(p.monto), 0) as total_adeudado,
        COALESCE(SUM(pagos_agg.total_pagado), 0) as total_pagado,
        (COALESCE(SUM(p.monto), 0) - COALESCE(SUM(pagos_agg.total_pagado), 0)) as saldo_pendiente
    FROM estudiantes e
    LEFT JOIN pensiones p ON e.id = p.id_estudiante
    LEFT JOIN (
        SELECT id_pension, SUM(monto_pagado) as total_pagado 
        FROM pagos 
        GROUP BY id_pension
    ) as pagos_agg ON p.id = pagos_agg.id_pension
    GROUP BY e.id, nombre_completo
    ORDER BY saldo_pendiente DESC, nombre_completo ASC;
";

$resumen_pagos = select_all($query_resumen);

?>

<h1 class="mb-4">Gestión de Pagos</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Resumen de Estado de Cuenta por Estudiante</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable" width="100%" cellspacing="0">
                <thead class="table-dark">
                    <tr>
                        <th>Estudiante</th>
                        <th>Total Adeudado</th>
                        <th>Total Pagado</th>
                        <th>Saldo Pendiente</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resumen_pagos as $resumen): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($resumen['nombre_completo']); ?></td>
                            <td>S/ <?php echo number_format($resumen['total_adeudado'], 2); ?></td>
                            <td>S/ <?php echo number_format($resumen['total_pagado'], 2); ?></td>
                            <td class="font-weight-bold <?php echo $resumen['saldo_pendiente'] > 0 ? 'text-danger' : 'text-success'; ?>">
                                S/ <?php echo number_format($resumen['saldo_pendiente'], 2); ?>
                            </td>
                            <td>
                                <a href="detalle_pagos_estudiante.php?id_estudiante=<?php echo $resumen['id_estudiante']; ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Ver Detalles
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'layout/footer.php'; ?>
