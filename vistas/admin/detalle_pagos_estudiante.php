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

// Obtener todas las pensiones del estudiante
$pensiones = select_all("SELECT * FROM pensiones WHERE id_estudiante = ? ORDER BY fecha_vencimiento DESC", "i", [$id_estudiante]);

// Obtener todos los pagos del estudiante y agruparlos por id_pension
$pagos_raw = select_all("SELECT * FROM pagos WHERE id_pension IN (SELECT id FROM pensiones WHERE id_estudiante = ?)", "i", [$id_estudiante]);
$pagos_agrupados = [];
foreach ($pagos_raw as $pago) {
    $pagos_agrupados[$pago['id_pension']][] = $pago;
}

?>

<h1 class="mb-4">Gestión de Pagos de: <span class="text-primary"><?php echo htmlspecialchars($estudiante['nombre_completo']); ?></span></h1>

<!-- Fila para Registrar Nueva Pensión y Nuevo Pago -->
<div class="row mb-4">
    <!-- Formulario para Registrar Nueva Pensión -->
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Asignar Nueva Pensión / Obligación</h6>
            </div>
            <div class="card-body">
                <form action="../../controladores/pago_controller.php" method="POST">
                    <input type="hidden" name="accion" value="agregar_pension">
                    <input type="hidden" name="id_estudiante" value="<?php echo $id_estudiante; ?>">
                    <div class="mb-3">
                        <label for="periodo_academico" class="form-label">Concepto / Periodo</label>
                        <input type="text" class="form-control" id="periodo_academico" name="periodo_academico" placeholder="Ej: Pensión de Marzo 2025" required>
                    </div>
                    <div class="mb-3">
                        <label for="monto" class="form-label">Monto (S/)</label>
                        <input type="number" step="0.01" class="form-control" id="monto" name="monto" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
                        <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Asignar Pensión</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Formulario para Registrar Nuevo Pago -->
    <div class="col-lg-6">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-success">Registrar un Pago</h6>
            </div>
            <div class="card-body">
                <form action="../../controladores/pago_controller.php" method="POST">
                    <input type="hidden" name="accion" value="agregar_pago">
                    <input type="hidden" name="id_estudiante" value="<?php echo $id_estudiante; ?>">
                    <div class="mb-3">
                        <label for="id_pension" class="form-label">Asociar a Pensión</label>
                        <select class="form-select" id="id_pension" name="id_pension" required>
                            <option value="">Seleccione una pensión...</option>
                            <?php foreach ($pensiones as $pension): 
                                $saldo_pension = $pension['monto'] - ($pagos_agrupados[$pension['id']] ? array_sum(array_column($pagos_agrupados[$pension['id']], 'monto_pagado')) : 0);
                                if ($saldo_pension > 0):
                            ?>
                                <option value="<?php echo $pension['id']; ?>">
                                    <?php echo htmlspecialchars($pension['periodo_academico']) . " (Saldo: S/ " . number_format($saldo_pension, 2) . ")"; ?>
                                </option>
                            <?php endif; endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="monto_pagado" class="form-label">Monto Pagado (S/)</label>
                        <input type="number" step="0.01" class="form-control" id="monto_pagado" name="monto_pagado" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                        <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="metodo_pago" class="form-label">Método de Pago</label>
                        <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Yape/Plin">Yape/Plin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Registrar Pago</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Pensiones y Pagos del Estudiante -->
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Historial de Cuenta</h6>
    </div>
    <div class="card-body">
        <?php if (empty($pensiones)): ?>
            <p class="text-center">Este estudiante no tiene pensiones asignadas.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="table-dark">
                        <tr>
                            <th>Concepto</th>
                            <th>Monto Total</th>
                            <th>Fecha Vencimiento</th>
                            <th>Estado</th>
                            <th>Pagos Realizados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pensiones as $pension): 
                            $pagos_de_esta_pension = $pagos_agrupados[$pension['id']] ?? [];
                            $monto_pagado_acumulado = array_sum(array_column($pagos_de_esta_pension, 'monto_pagado'));
                            $saldo_pendiente = $pension['monto'] - $monto_pagado_acumulado;
                            $estado_pension = $pension['estado'];
                            if ($saldo_pendiente <= 0) {
                                $estado_pension = 'pagado';
                            } elseif (strtotime($pension['fecha_vencimiento']) < time()) {
                                $estado_pension = 'vencido';
                            }
                        ?>
                            <tr class="align-middle">
                                <td>
                                    <strong><?php echo htmlspecialchars($pension['periodo_academico']); ?></strong>
                                </td>
                                <td>S/ <?php echo number_format($pension['monto'], 2); ?></td>
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
                                    <br>
                                    <small>Saldo: S/ <?php echo number_format($saldo_pendiente, 2); ?></small>
                                </td>
                                <td>
                                    <?php if (empty($pagos_de_esta_pension)): ?>
                                        <small class="text-muted">Sin pagos registrados.</small>
                                    <?php else: ?>
                                        <ul class="list-unstyled mb-0">
                                            <?php foreach ($pagos_de_esta_pension as $pago): ?>
                                                <li>
                                                    <span class="badge bg-success">Pagado: S/ <?php echo number_format($pago['monto_pagado'], 2); ?></span>
                                                    <small class="text-muted">el <?php echo htmlspecialchars($pago['fecha_pago']); ?></small>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
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
