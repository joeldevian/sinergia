<?php require_once 'layout/header.php'; ?>

<h1 class="mb-4">Mi Dashboard</h1>

<!-- Fila de Indicadores Clave (KPIs) -->
<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Cursos Matriculados</div>
                        <div id="kpi-total-cursos" class="h5 mb-0 font-weight-bold text-gray-800">Cargando...</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book-reader fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Promedio General</div>
                        <div id="kpi-promedio-general" class="h5 mb-0 font-weight-bold text-gray-800">Cargando...</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-star fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Mi Asistencia</div>
                        <div id="kpi-mi-asistencia" class="h5 mb-0 font-weight-bold text-gray-800">Cargando...</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fila de Gráficos Principales -->
<div class="row">
    <!-- Gráfico de Barras -->
    <div class="col-xl-7 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Mis Calificaciones por Curso</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar" style="position: relative; height:250px;">
                    <canvas id="graficoCalificacionesPorCurso"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Dona -->
    <div class="col-xl-5 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Resumen de Mi Asistencia</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4" style="position: relative; height:250px;">
                    <canvas id="graficoResumenAsistencia"></canvas>
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
    Chart.register(ChartDataLabels);
    Chart.defaults.plugins.datalabels.color = '#fff';
    Chart.defaults.plugins.datalabels.font = { weight: 'bold' };

    // --- LLAMADAS A LA API Y RENDERIZADO DE GRÁFICOS ---

    // Cargar KPIs del Estudiante
    fetch('../../controladores/api_controller.php?accion=get_student_kpis')
        .then(response => response.json())
        .then(data => {
            if (data.error) return console.error('Error al obtener KPIs del estudiante:', data.error);
            document.getElementById('kpi-total-cursos').innerText = data.total_cursos;
            document.getElementById('kpi-promedio-general').innerText = data.promedio_general;
            document.getElementById('kpi-mi-asistencia').innerText = data.mi_asistencia + '%';
        })
        .catch(error => console.error('Error en fetch KPIs del estudiante:', error));

    // Cargar Gráfico de Barras (Calificaciones por Curso)
    fetch('../../controladores/api_controller.php?accion=get_student_grades_by_course')
        .then(response => response.json())
        .then(data => {
            if (data.error) return console.error('Error al obtener datos para gráfico de barras:', data.error);
            new Chart(document.getElementById('graficoCalificacionesPorCurso'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Promedio',
                        data: data.data,
                        backgroundColor: data.backgroundColors
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        datalabels: { anchor: 'end', align: 'end', color: '#5a5c69' }
                    },
                    scales: { x: { ticks: { beginAtZero: true, max: 20 } } }
                }
            });
        })
        .catch(error => console.error('Error en fetch gráfico de barras:', error));

    // Cargar Gráfico de Dona (Resumen de Asistencia)
    fetch('../../controladores/api_controller.php?accion=get_student_attendance_summary')
        .then(response => response.json())
        .then(data => {
            if (data.error) return console.error('Error al obtener datos para gráfico de dona:', data.error);
            new Chart(document.getElementById('graficoResumenAsistencia'), {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{ data: data.data, backgroundColor: data.backgroundColors, hoverOffset: 4 }]
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
        .catch(error => console.error('Error en fetch gráfico de dona:', error));
});
</script>

<?php require_once 'layout/footer.php'; ?>
