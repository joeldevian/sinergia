<?php require_once 'layout/header.php'; ?>



<!-- Fila de Indicadores Clave (KPIs) -->
<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Estudiantes Activos</div>
                        <div id="kpi-total-estudiantes" class="h5 mb-0 font-weight-bold text-gray-800">Cargando...</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Docentes Activos</div>
                        <div id="kpi-total-docentes" class="h5 mb-0 font-weight-bold text-gray-800">Cargando...</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Cursos Activos</div>
                        <div id="kpi-total-cursos" class="h5 mb-0 font-weight-bold text-gray-800">Cargando...</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fila de Gráficos Superiores -->
<div class="row mb-4">
    <!-- Gráfico de Dona Mejorado -->
    <div class="col-xl-5 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Distribución de Estudiantes (Top 5)</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4" style="position: relative; height:250px;">
                    <canvas id="graficoEstudiantesPorCurso"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Barras -->
    <div class="col-xl-7 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Distribución General de Calificaciones</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar" style="position: relative; height:250px;">
                    <canvas id="graficoDistribucionCalificaciones"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fila de Gráfico de Tendencias -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tendencia de Nuevas Matrículas</h6>
            </div>
            <div class="card-body">
                <div class="chart-area" style="position: relative; height:300px;">
                    <canvas id="graficoTendenciaMatriculas"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Incluir Chart.js y Plugin Datalabels desde CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- CONFIGURACIÓN Y PLUGINS ---
    Chart.register(ChartDataLabels); // Registrar Datalabels
    Chart.defaults.plugins.datalabels.color = '#fff';
    Chart.defaults.plugins.datalabels.font = { weight: 'bold' };

    // Plugin personalizado para dibujar texto en el centro del gráfico de dona
    const doughnutTextPlugin = {
        id: 'doughnutText',
        afterDraw(chart, args, options) {
            if (chart.config.type !== 'doughnut' || !options.text) {
                return;
            }
            const { ctx, data } = chart;
            const text = options.text;
            const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
            
            ctx.save();
            const x = chart.getDatasetMeta(0).data[0].x;
            const y = chart.getDatasetMeta(0).data[0].y;
            
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            
            ctx.font = 'bold 24px sans-serif';
            ctx.fillStyle = '#4e73df';
            ctx.fillText(total, x, y - 10);
            
            ctx.font = '14px sans-serif';
            ctx.fillStyle = '#858796';
            ctx.fillText(text, x, y + 15);
            
            ctx.restore();
        }
    };

    // --- LLAMADAS A LA API Y RENDERIZADO DE GRÁFICOS ---

    // Cargar KPIs
    fetch('../../controladores/api_controller.php?accion=get_kpis')
        .then(response => response.json())
        .then(data => {
            if (data.error) return console.error('Error al obtener KPIs:', data.error);
            document.getElementById('kpi-total-estudiantes').innerText = data.total_estudiantes;
            document.getElementById('kpi-total-docentes').innerText = data.total_docentes;
            document.getElementById('kpi-total-cursos').innerText = data.total_cursos;
        })
        .catch(error => console.error('Error en fetch KPIs:', error));

    // Cargar Gráfico de Dona (Estudiantes por Curso)
    fetch('../../controladores/api_controller.php?accion=estudiantes_por_curso')
        .then(response => response.json())
        .then(data => {
            if (data.error) return console.error('Error al obtener datos para gráfico de dona:', data.error);
            new Chart(document.getElementById('graficoEstudiantesPorCurso'), {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{ data: data.data, backgroundColor: data.backgroundColors, hoverOffset: 4 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '75%',
                    plugins: {
                        legend: { position: 'bottom' },
                        datalabels: { formatter: (value) => value },
                        doughnutText: { text: 'Estudiantes' } // Opción para el plugin personalizado
                    }
                },
                plugins: [doughnutTextPlugin] // Registrar el plugin personalizado en esta instancia
            });
        })
        .catch(error => console.error('Error en fetch gráfico de dona:', error));

    // Cargar Gráfico de Barras (Distribución de Calificaciones)
    fetch('../../controladores/api_controller.php?accion=get_grade_distribution')
        .then(response => response.json())
        .then(data => {
            if (data.error) return console.error('Error al obtener datos para gráfico de barras:', data.error);
            new Chart(document.getElementById('graficoDistribucionCalificaciones'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{ label: 'Cantidad de Notas', data: data.data, backgroundColor: data.backgroundColors }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        datalabels: { anchor: 'end', align: 'end', color: '#5a5c69' }
                    },
                    scales: { x: { ticks: { beginAtZero: true, stepSize: 1 } } }
                }
            });
        })
        .catch(error => console.error('Error en fetch gráfico de barras:', error));

    // Cargar Gráfico de Líneas (Tendencia de Matrículas)
    fetch('../../controladores/api_controller.php?accion=get_enrollment_trends')
        .then(response => response.json())
        .then(data => {
            if (data.error) return console.error('Error al obtener datos de tendencia:', data.error);
            new Chart(document.getElementById('graficoTendenciaMatriculas'), {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: "Nuevas Matrículas",
                        data: data.data,
                        fill: true,
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        borderColor: 'rgba(78, 115, 223, 1)',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, datalabels: { display: false } },
                    scales: { y: { ticks: { beginAtZero: true, stepSize: 1 } } }
                }
            });
        })
        .catch(error => console.error('Error en fetch gráfico de tendencia:', error));
});
</script>

<?php require_once 'layout/footer.php'; ?>
