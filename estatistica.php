<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico - Relacionamento Médico</title>
    <link rel="icon" href="img\Logobordab.png" type="image/x-icon">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-grid.css">
    <link rel="stylesheet" href="css/bootstrap-grid.min.css">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/selectize.bootstrap5.min.css">
    <link rel="stylesheet" href="css/multi-select-tag.css">

    <style>
        .chart-stats {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            font-size: 16px;
            color: #333;
        }

        .chart-stats p {
            margin: 0 15px;
        }
        .chart-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }
        .chart-box {
            width: 80%; 
            max-width: 800px; 
            height: 350px;
            margin-bottom: 30px;
            display: flex; 
            justify-content: center; 
            align-items: center; 
        }
        .chart-box canvas {
            width: 100%;
            height: 100%;
        }
        .chart-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .center-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<?php
    $pageTitle = "Estatísticas - Registros de Atendimento";
    include 'php/header.php';
?>
<body>
    <div class="container mt-2">
        <div class="row mt-4 center-content">
            <div class="col-xl-12 col-md-12 col-lg-12 mb-4 mb-0">
                <div class="card border-left-success shadow h-80 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-12">
                                <div class="chart-header text-xs font-weight-bold text-success text-uppercase">
                                    Quantidade de Atendimentos
                                </div>
                                <div class="chart-container">
                                    <div class="chart-box">
                                        <canvas id="barChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-5">
        <div class="row mt-4 center-content">
            <div class="col-xl-12 col-md-12 col-lg-12 mb-4 mb-0">
                <div class="card border-left-success shadow h-80 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col-12">
                                <div class="chart-header text-xs font-weight-bold text-success text-uppercase mt-4">
                                    Status dos Atendimentos
                                </div>
                                <div class="chart-container">
                                    <div class="chart-box">
                                        <canvas id="doughnutChart"></canvas>
                                    </div>
                                    <div id="doughnutChartStats" class="chart-stats">
                                        <!-- As quantidades serão inseridas aqui pelo JavaScript -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const labels = <?php echo $labelsJson; ?>;
        const data = <?php echo $dataJson; ?>;
        const statusLabels = <?php echo $statusLabelsJson; ?>;
        const statusData = <?php echo $statusDataJson; ?>;

        const barChartData = {
            labels: labels,
            datasets: [{
                label: 'Quantidade de Atendimentos',
                backgroundColor: '#1E3050',
                borderColor: '#1E3050',
                borderWidth: 1,
                data: data,
            }]
        };

        const barCtx = document.getElementById('barChart').getContext('2d');
        const doughnutCtx = document.getElementById('doughnutChart').getContext('2d');

        new Chart(barCtx, {
            type: 'bar',
            data: barChartData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        new Chart(doughnutCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'Status dos Atendimentos',
                    backgroundColor: [
                        '#114F1C', 
                        '#1E3050'  
                    ],
                    borderColor: [
                        '#114F1C', 
                        '#1E3050'  
                    ],
                    borderWidth: 1,
                    data: statusData,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.raw !== null) {
                                    label += context.raw;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
        const doughnutChartStats = document.getElementById('doughnutChartStats');
        doughnutChartStats.innerHTML = `
            <p style="display: inline-block; margin-right: 20px;">Abertos: ${statusData[0]}</p>
            <p style="display: inline-block;">Finalizados: ${statusData[1]}</p>
        `;
    });
    </script>
</body>

</html>