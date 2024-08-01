<?php
include("conexao.php");
$registrosPorPagina = 10;
$paginaAtual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $registrosPorPagina;
$sql = "SELECT DATE_FORMAT(a.data, '%m/%Y') as data,
        im.nome as nome_profissional,
        assuntos.assunto,
        a.situacao as situacao,
        a.id as id
        FROM relacionamentomedico.atendimento AS a
        JOIN relacionamentomedico.profissionais AS im ON a.profissional = im.id
        JOIN relacionamentomedico.atendimento_has_assunto AS has ON a.id = has.id
        JOIN relacionamentomedico.assunto AS assuntos ON has.id = assuntos.id
        LIMIT $offset, $registrosPorPagina";

$result = $conn->query($sql);
$sqlTotal = "SELECT COUNT(*) AS total
            FROM relacionamentomedico.atendimento AS a
            JOIN relacionamentomedico.profissionais AS im ON a.profissional = im.id
            JOIN relacionamentomedico.atendimento_has_assunto AS has ON a.id = has.id
            JOIN relacionamentomedico.assunto AS assuntos ON has.id = assuntos.id";

$resultCount = $conn->query($sqlTotal);
$totalRegistros = $resultCount->fetch_assoc()['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);
$sql = "
    SELECT 
        DATE_FORMAT(a.data, '%m/%Y') AS mes_ano,
        COUNT(*) AS quantidade
    FROM 
        atendimento a
    GROUP BY 
        DATE_FORMAT(a.data, '%m/%Y')
    ORDER BY 
        mes_ano;
";
$result = $conn->query($sql);
$labels = [];
$data = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['mes_ano'];
    $data[] = $row['quantidade'];
}
$labelsJson = json_encode($labels);
$dataJson = json_encode($data);
$sqlStatus = "
    SELECT 
        situacao,
        COUNT(*) AS quantidade
    FROM 
        atendimento
    GROUP BY 
        situacao;
";
$resultStatus = $conn->query($sqlStatus);
$statusLabels = [];
$statusData = [];
while ($row = $resultStatus->fetch_assoc()) {
    $statusLabels[] = $row['situacao'];
    $statusData[] = $row['quantidade'];
}
$statusLabelsJson = json_encode($statusLabels);
$statusDataJson = json_encode($statusData);
$conn->close();
?>



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

<main>
    
    <div class="container mt-2">
        <div class="row mt-4 center-content mt-5">
            <div class="col-xl-6 col-md-12 col-lg-12 mb-4 mt-5">
                <div>
                    <select class="form-select form-select-lg mb-5" aria-label="Large select example">
                    <option selected>Selecione o mês</option>
                    <option value="1">Janeiro</option>
                    <option value="2">Fevereiro</option>
                    <option value="3">Março</option>
                    <option value="4">Abril</option>
                    <option value="5">Maio</option>
                    <option value="6">Junho</option>
                    <option value="7">Julho</option>
                    <option value="8">Agosto</option>
                    <option value="9">Setembro</option>
                    <option value="10">Outubro</option>
                    <option value="11">Novembro</option>
                    <option value="12">Dezembro</option>
                    </select>
                </div>
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
        <div class="border-top my-4">
        <div class="border-bottom my-4">
        <div class="row mt-4 center-content">
            <div class="col-xl-6 col-md-12 col-lg-12 mb-4">
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
    </div>
    </main>

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