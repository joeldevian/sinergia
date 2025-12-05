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

$pensiones = [];
if ($id_estudiante > 0) {
    // 1. Obtener el monto total pagado por el estudiante
    $stmt_total_pagado = $conexion->prepare("SELECT SUM(monto) as total FROM pagos WHERE id_estudiante = ? AND estado = 'válido'");
    $stmt_total_pagado->bind_param("i", $id_estudiante);
    $stmt_total_pagado->execute();
    $resultado_total_pagado = $stmt_total_pagado->get_result();
    $total_pagado = $resultado_total_pagado->fetch_assoc()['total'] ?? 0;
    $stmt_total_pagado->close();

    // 2. Obtener todas las pensiones del estudiante
    $query_pensiones_base = "SELECT
                                p.id,
                                p.periodo_academico,
                                p.monto,
                                p.fecha_vencimiento,
                                p.estado
                            FROM pensiones p
                            WHERE p.id_estudiante = ?
                            ORDER BY p.fecha_vencimiento ASC"; // Ordenar para aplicar pagos cronológicamente

    $stmt_pensiones_base = $conexion->prepare($query_pensiones_base);
    $stmt_pensiones_base->bind_param("i", $id_estudiante);
    $stmt_pensiones_base->execute();
    $resultado_pensiones_base = $stmt_pensiones_base->get_result();

    $pensiones_raw = [];
    while ($pension = $resultado_pensiones_base->fetch_assoc()) {
        $pensiones_raw[] = $pension;
    }
    $stmt_pensiones_base->close();

    // 3. Distribuir el total pagado entre las pensiones en PHP
    $saldo_pagos_disponible = $total_pagado;
    foreach ($pensiones_raw as $pension) {
        $monto_a_pagar_esta_pension = $pension['monto'];
        $pagado_para_esta_pension = 0;

        if ($saldo_pagos_disponible > 0) {
            $pagado_para_esta_pension = min($saldo_pagos_disponible, $monto_a_pagar_esta_pension);
            $saldo_pagos_disponible -= $pagado_para_esta_pension;
        }
        
        $pension['monto_pagado_acumulado'] = $pagado_para_esta_pension;
        $pensiones[] = $pension;
    }
}
?>

<h1 class="mb-4">Mis Pagos</h1>

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
        <h5 class="mb-0">Detalle de Pensiones y Pagos</h5>
    </div>
    <div class="card-body">
        <?php if (empty($pensiones)): ?>
            <p class="text-center">No tienes pensiones registradas aún.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover datatable">
                    <thead class="table-dark">
                        <tr>
                            <th>Periodo Académico</th>
                            <th>Monto Total</th>
                            <th>Monto Pagado</th>
                            <th>Saldo Pendiente</th>
                            <th>Fecha Vencimiento</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pensiones as $pension): 
                            $saldo_pendiente = $pension['monto'] - $pension['monto_pagado_acumulado'];
                            $estado_pension = $pension['estado'];
                            if ($saldo_pendiente > 0 && strtotime($pension['fecha_vencimiento']) < time()) {
                                $estado_pension = 'vencido';
                            } elseif ($saldo_pendiente <= 0) {
                                $estado_pension = 'pagado';
                            }
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pension['periodo_academico']); ?></td>
                                <td>S/ <?php echo number_format($pension['monto'], 2); ?></td>
                                <td>S/ <?php echo number_format($pension['monto_pagado_acumulado'], 2); ?></td>
                                <td>S/ <?php echo number_format($saldo_pendiente, 2); ?></td>
                                <td><?php echo htmlspecialchars($pension['fecha_vencimiento']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        if ($estado_pension == 'pagado') echo 'success';
                                        else if ($estado_pension == 'pendiente') echo 'warning';
                                        else if ($estado_pension == 'vencido') echo 'danger';
                                        else echo 'secondary';
                                    ?>">
                                        <?php echo ucfirst($estado_pension); ?>
                                    </span>
                                </td>
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
