<?php
$includeDataTablesCss = true;
$includeDataTablesJs = true;
require_once 'layout/header.php';
require_once '../../config/database.php';

// Nueva consulta simplificada para el modelo de pagos genéricos
$query_resumen = "
    SELECT 
        e.id as id_estudiante,
        CONCAT(e.nombres, ' ', e.apellido_paterno, ' ', e.apellido_materno) as nombre_completo,
        COALESCE(SUM(p.monto), 0) as total_pagado
    FROM estudiantes e
    LEFT JOIN pagos p ON e.id = p.id_estudiante
    GROUP BY e.id, nombre_completo
    ORDER BY total_pagado DESC, nombre_completo ASC;
";
$resumen_pagos = select_all($query_resumen);

// Se calcula el total de ingresos aquí para el KPI
$ingresos_totales = array_sum(array_column($resumen_pagos, 'total_pagado'));
?>

<h1 class="mb-4">Gestión y Análisis de Pagos</h1>

<!-- Fila de Indicadores Financieros (KPIs) - Simplificada -->
<div class="row">
    <div class="col-xl-6 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Ingresos Totales Registrados</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">S/ <?php echo number_format($ingresos_totales, 2); ?></div>
                    </div>
                    <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-md-6 mb-4">
         <div class="card h-100 shadow-sm">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                 <div class="mb-2">
                    <i class="fas fa-plus-circle fa-2x text-primary"></i>
                </div>
                <h5 class="card-title mb-1">Pagos</h5>
                <p class="card-text small">Registra un nuevo pago para cualquier estudiante.</p>
                <div class="mt-auto">
                    <a href="agregar_pago.php" class="btn btn-primary btn-sm">Registrar Nuevo Pago</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico de Ingresos por Mes -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Ingresos por Mes</h6></div>
            <div class="card-body">
                <div class="chart-bar" style="position: relative; height:250px;"><canvas id="graficoIngresosPorMes"></canvas></div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Resumen por Estudiante - Simplificada -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Resumen de Pagos por Estudiante</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover datatable" width="100%" cellspacing="0">
                <thead class="table-dark">
                    <tr>
                        <th>Estudiante</th>
                        <th>Total Pagado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resumen_pagos as $resumen): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($resumen['nombre_completo']); ?></td>
                            <td class="font-weight-bold text-success">
                                S/ <?php echo number_format($resumen['total_pagado'], 2); ?>
                            </td>
                            <td>
                                <!-- Este enlace sigue siendo útil para ver el historial de pagos de un estudiante -->
                                <a href="detalle_pagos_estudiante.php?id_estudiante=<?php echo $resumen['id_estudiante']; ?>" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> Ver Historial
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Scripts de Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    Chart.register(ChartDataLabels);
    Chart.defaults.plugins.datalabels.color = '#fff';
    Chart.defaults.plugins.datalabels.font = { weight: 'bold' };

    // Cargar Gráfico de Barras (Ingresos por Mes)
    // Esta API sigue siendo válida si se adapta a la nueva tabla 'pagos'
    fetch('../../controladores/api_controller.php?accion=get_income_by_month')
        .then(response => response.json())
        .then(data => {
            if (data.error) return console.error('Error al obtener ingresos por mes:', data.error);
            new Chart(document.getElementById('graficoIngresosPorMes'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Ingresos (S/)',
                        data: data.data,
                        backgroundColor: '#4e73df'
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, datalabels: { display: false } },
                    scales: { y: { ticks: { beginAtZero: true } } }
                }
            });
        })
        .catch(error => console.error('Error en fetch ingresos por mes:', error));
});
</script>

<?php require_once 'layout/footer.php'; ?>