<?php
$includeDataTablesCss = true;
$includeDataTablesJs = true;
require_once 'layout/header.php';
require_once '../../config/database.php'; // Usamos el nuevo sistema

// La consulta para la tabla de abajo se mantiene
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

<h1 class="mb-4">Gestión y Análisis de Pagos</h1>

<!-- Fila de Indicadores Financieros (KPIs) -->
<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Ingresos Totales</div>
                        <div id="kpi-ingresos-totales" class="h5 mb-0 font-weight-bold text-gray-800">Cargando...</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-dollar-sign fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Saldo Pendiente Total</div>
                        <div id="kpi-saldo-pendiente" class="h5 mb-0 font-weight-bold text-gray-800">Cargando...</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tasa de Cobranza</div>
                        <div id="kpi-tasa-cobranza" class="h5 mb-0 font-weight-bold text-gray-800">Cargando...</div>
                    </div>
                    <div class="col-auto"><i class="fas fa-percentage fa-2x text-gray-300"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fila de Gráficos Financieros -->
<div class="row mb-4">
    <div class="col-xl-7 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Ingresos por Mes</h6></div>
            <div class="card-body">
                <div class="chart-bar" style="position: relative; height:250px;"><canvas id="graficoIngresosPorMes"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-xl-5 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Estado General de Pensiones</h6></div>
            <div class="card-body">
                <div class="chart-pie pt-4" style="position: relative; height:250px;"><canvas id="graficoEstadoPensiones"></canvas></div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Resumen por Estudiante (Existente) -->
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

<!-- Scripts de Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    Chart.register(ChartDataLabels);
    Chart.defaults.plugins.datalabels.color = '#fff';
    Chart.defaults.plugins.datalabels.font = { weight: 'bold' };

    // Cargar KPIs de Pagos
    fetch('../../controladores/api_controller.php?accion=get_payment_kpis')
        .then(response => response.json())
        .then(data => {
            if (data.error) return console.error('Error al obtener KPIs de pagos:', data.error);
            document.getElementById('kpi-ingresos-totales').innerText = 'S/ ' + parseFloat(data.ingresos_totales).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('kpi-saldo-pendiente').innerText = 'S/ ' + parseFloat(data.saldo_pendiente).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('kpi-tasa-cobranza').innerText = data.tasa_cobranza + '%';
        })
        .catch(error => console.error('Error en fetch KPIs de pagos:', error));

    // Cargar Gráfico de Barras (Ingresos por Mes)
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

    // Cargar Gráfico de Dona (Estado de Pensiones)
    fetch('../../controladores/api_controller.php?accion=get_pension_status_summary')
        .then(response => response.json())
        .then(data => {
            if (data.error) return console.error('Error al obtener estado de pensiones:', data.error);
            new Chart(document.getElementById('graficoEstadoPensiones'), {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{ data: data.data, backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'], hoverOffset: 4 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: { formatter: (value) => value }
                    }
                }
            });
        })
        .catch(error => console.error('Error en fetch estado de pensiones:', error));
});
</script>

<?php require_once 'layout/footer.php'; ?>
