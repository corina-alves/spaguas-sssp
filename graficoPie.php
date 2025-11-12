<?php
$apiKey = "D3BCD8A-8B65-4D1C-B2C7-F5E41912D7E7"; 
$sistemaNome = "cantareira";

// Intervalos
$inicio2021 = "2021-01-01";
$fim2021    = "2021-12-31";
$inicio2025 = "2025-01-01";
$fim2025    = "2025-12-31";

// Função para buscar dados
function buscarDados($sistema, $inicio, $fim, $apiKey) {
    $url = "https://ssdapi.sabesp.com.br/api/ssd/sistemas/$sistema/dados/$inicio/$fim";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json",
        "x-api-key: $apiKey"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$dados2021 = buscarDados($sistemaNome, $inicio2021, $fim2021, $apiKey);
$dados2025 = buscarDados($sistemaNome, $inicio2025, $fim2025, $apiKey);

// Organizar dados (pegar último valor de cada mês)
function organizarPorMes($dados) {
    $meses = [];
    foreach ($dados["data"] as $item) {
        $data = substr($item["dataHora"], 0, 7); // yyyy-mm
        $meses[$data] = $item["volumeOperacional_porcentagem"]; // último do mês
    }
    return $meses;
}

$volumes2021 = organizarPorMes($dados2021);
$volumes2025 = organizarPorMes($dados2025);

// Garantir que todos os meses estejam alinhados
$labels = [];
for ($m = 1; $m <= 12; $m++) {
    $labels[] = sprintf("%02d", $m);
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Comparação - Cantareira 2021 x 2025</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h2>Volume Útil (%) - Cantareira</h2>
    <canvas id="grafico"></canvas>

    <script>
        const ctx = document.getElementById('grafico').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>, // meses
                datasets: [
                    {
                        label: '2021',
                        data: <?= json_encode(array_values($volumes2021)) ?>,
                        borderColor: 'blue',
                        fill: false
                    },
                    {
                        label: '2025',
                        data: <?= json_encode(array_values($volumes2025)) ?>,
                        borderColor: 'red',
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Comparação de Volume Útil (%) - Cantareira (2021 x 2025)' }
                },
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        });
    </script>
</body>
</html>
