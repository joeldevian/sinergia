<?php require_once 'layout/header.php'; ?>

<?php
require_once '../../config/database.php'; // Cargar funciones de la base de datos
$nombre_docente = 'Docente'; // Valor por defecto en caso de no encontrar el nombre
if (isset($_SESSION['user_id'])) {
    $docente_data = select_one(
        "SELECT nombres, apellido_paterno FROM docentes WHERE id_user = ? LIMIT 1", 
        "i", 
        [$_SESSION['user_id']]
    );
    if ($docente_data) {
        $nombre_docente = htmlspecialchars($docente_data['nombres'] . ' ' . $docente_data['apellido_paterno']);
    }
}
?>
<h2 class="mb-4 text-muted">Bienvenido/a Docente, <?php echo $nombre_docente; ?></h2>

<!-- Fila de Indicadores Clave (KPIs) -->
<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Mis Cursos Activos</div>
                        <div id="kpi-total-cursos" class="h5 mb-0 font-weight-bold text-gray-800">Cargando...</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-book-open fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Mis Estudiantes</div>
                        <div id="kpi-total-estudiantes" class="h5 mb-0 font-weight-bold text-gray-800">Cargando...</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Promedio de Asistencia</div>
                        <div id="kpi-promedio-asistencia" class="h5 mb-0 font-weight-bold text-gray-800">Cargando...</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
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
                <h6 class="m-0 font-weight-bold text-primary">Distribución de Calificaciones en Mis Cursos</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar" style="position: relative; height:250px;">
                    <canvas id="graficoDistribucionCalificaciones"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Dona -->
    <div class="col-xl-5 col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Resumen de Asistencia en Mis Cursos</h6>
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

    // Cargar KPIs del Docente
    fetch('../../controladores/api_controller.php?accion=get_teacher_kpis')
        .then(response => response.json())
        .then(data => {
            if (data.error) return console.error('Error al obtener KPIs del docente:', data.error);
            document.getElementById('kpi-total-cursos').innerText = data.total_cursos;
            document.getElementById('kpi-total-estudiantes').innerText = data.total_estudiantes;
            document.getElementById('kpi-promedio-asistencia').innerText = data.promedio_asistencia + '%';
        })
        .catch(error => console.error('Error en fetch KPIs del docente:', error));

    // Cargar Gráfico de Barras (Distribución de Calificaciones)
    fetch('../../controladores/api_controller.php?accion=get_teacher_grade_distribution')
        .then(response => response.json())
        .then(data => {
            if (data.error) return console.error('Error al obtener datos para gráfico de barras:', data.error);
            new Chart(document.getElementById('graficoDistribucionCalificaciones'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Cantidad de Notas',
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
                    scales: { x: { ticks: { beginAtZero: true, stepSize: 1 } } }
                }
            });
        })
        .catch(error => console.error('Error en fetch gráfico de barras:', error));

    // Cargar Gráfico de Dona (Resumen de Asistencia)
    fetch('../../controladores/api_controller.php?accion=get_teacher_attendance_summary')
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
