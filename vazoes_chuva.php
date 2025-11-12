<?php
// Exemplo de dados - substitua pelos seus dados da API ou do banco
$sistemas = ["Cantareira", "Alto Tiete", "Guarapiranga", "Rio Grande", "Cotia", "Rio Claro", "São Lourenço"];

$vazao_dia = [25, 30, 18, 22, 28,30,30];          // Vazão atual (m³/s)
$vazao_mes = [27, 34, 20, 25, 30,10,15];          // Média do mês
$vazao_media_hist = [35, 40, 25, 30, 38,10,20];   // Média histórica
$chuva_atual = [10, 5, 12, 8, 9,7,15];           // Chuva atual (mm)
$chuva_2021 = [20, 15, 22, 18, 17,6,14];         // Chuva 2021 (mm)
$vazao_comparacao_2021_2024 = [80, 75, 70, 85, 90,95,18]; // Percentual (%)
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gráfico Vazões e Chuvas - Cantareira</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial; background: #f8f9fa; }
        /* #graficoContainer { width: 90%; margin: auto; } */
        canvas { background: #fff; border-radius: 10px; box-shadow: 0 0 10px #ccc; }
    </style>
</head>
<body>
    <div id="graficoContainer" class="container">

<div class="row">
    <div class="col-lg-6">
            <h5 style="text-align:center;">Comparativo ano 2021 de Vazões e Chuvas - Sistema RMSP</h5>

        <canvas id="graficoCantareira"></canvas>
    </div>
    <div class="col-lg-6"></div>
    </div>
</div>

    <script>
        const ctx = document.getElementById('graficoCantareira');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($sistemas); ?>,
                datasets: [
                    {
                        label: 'Vazão do dia (m³/s)',
                        data: <?php echo json_encode($vazao_dia); ?>,
                        backgroundColor: 'rgba(0, 123, 255, 0.6)',
                        borderColor: '#007bff',
                        borderWidth: 1
                    },
                    {
                        label: 'Vazão do mês (m³/s)',
                        data: <?php echo json_encode($vazao_mes); ?>,
                        backgroundColor: 'rgba(40, 167, 69, 0.6)',
                        borderColor: '#28a745',
                        borderWidth: 1
                    },
                    {
                        label: 'Média histórica (m³/s)',
                        data: <?php echo json_encode($vazao_media_hist); ?>,
                        backgroundColor: 'rgba(255, 193, 7, 0.6)',
                        borderColor: '#ffc107',
                        borderWidth: 1
                    },
                    {
                        label: 'Chuva atual (mm)',
                        data: <?php echo json_encode($chuva_atual); ?>,
                        backgroundColor: 'rgba(23, 162, 184, 0.6)',
                        borderColor: '#17a2b8',
                        borderWidth: 1
                    },
                    {
                        label: 'Chuva 2021 (mm)',
                        data: <?php echo json_encode($chuva_2021); ?>,
                        backgroundColor: 'rgba(220, 53, 69, 0.6)',
                        borderColor: '#dc3545',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: 'Vazões e Chuvas - Cantareira (Comparativo)'
                    },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Valores (m³/s ou mm)' }
                    }
                }
            }
        });
    </script>
</body>
</html>
