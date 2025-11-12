<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set('America/Sao_Paulo');

$ids_sistemas = [
    "Cantareira"   => 64,
    "Alto Tietê"   => 65,
    "Guarapiranga" => 66,
    "Cotia"        => 67,
    "Rio Grande"   => 68,
    "Rio Claro"    => 69,
    "São Lourenço" => 72,
    "SIM"          => 75,
];

$data_atual = isset($_GET['data']) ? $_GET['data'] : date("Y-m-d");

// Função para buscar dados da API
function getDadosSabesp($data) {
    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/$data";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res, true);
}

// Dados atuais
$dados_atual = getDadosSabesp($data_atual);

// Dados de 2021 (mesmo dia e mês)
$ano_ref = 2021;
$mes_dia = date("m-d", strtotime($data_atual));
$data_ref = $ano_ref . '-' . $mes_dia;
$dados_2021 = getDadosSabesp($data_ref);

// Organizar sistemas
$sistemas_atual = [];
$sistemas_2021 = [];

foreach($ids_sistemas as $nome => $id) {
    $sistemas_atual[$nome] = null;
    $sistemas_2021[$nome] = null;
}

// Preencher dados atuais
if(isset($dados_atual['sistemas'])) {
    foreach($dados_atual['sistemas'] as $s) {
        foreach($ids_sistemas as $nome => $id) {
            if($s['id'] == $id) {
                $sistemas_atual[$nome] = $s;
            }
        }
    }
}

// Preencher dados de 2021
if(isset($dados_2021['sistemas'])) {
    foreach($dados_2021['sistemas'] as $s) {
        foreach($ids_sistemas as $nome => $id) {
            if($s['id'] == $id) {
                $sistemas_2021[$nome] = $s;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Dashboard de Monitoramento</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { font-family: Arial, sans-serif; margin: 0; background: #f4f6f9; }
header { background: #2c3e50; color: #fff; padding: 15px 30px; text-align: center; }
.container { max-width: 1200px; margin: 20px auto; padding: 0 15px; }
.tabs { display: flex; border-bottom: 2px solid #ccc; margin-bottom: 20px; cursor: pointer; }
.tab { padding: 10px 20px; background: #eee; margin-right: 5px; border-radius: 5px 5px 0 0; }
.tab.active { background: #fff; border-top: 3px solid #3498db; font-weight: bold; }
.tab-content { display: none; }
.tab-content.active { display: block; }
.cards { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 30px; }
.card { flex: 1 1 200px; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
.card h3 { margin: 0 0 10px; font-size: 16px; color: #555; }
.card p { margin: 0; font-size: 24px; font-weight: bold; }
canvas { background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); width: 100%; max-width: 100%; }
table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px; }
table th, table td { padding: 10px; border: 1px solid #ddd; text-align: center; }
table th { background: #3498db; color: #fff; }
</style>
</head>
<body>

<header>
    <h1>Dashboard de Monitoramento</h1>
    <p>Data: <?php echo $data_atual; ?> | Comparativo com <?php echo $data_ref; ?></p>
</header>

<div class="container">
    <div class="tabs">
        <div class="tab active" data-tab="reservatorios">Reservatórios</div>
        <div class="tab" data-tab="vazao">Vazão</div>
        <div class="tab" data-tab="chuva">Chuva</div>
        <div class="tab" data-tab="tabelas">Tabelas de Volumes</div>
    </div>

    <div id="reservatorios" class="tab-content active">
        <canvas id="chartReservatorios"></canvas>
    </div>

    <div id="vazao" class="tab-content">
        <canvas id="chartVazao"></canvas>
    </div>

    <div id="chuva" class="tab-content">
        <canvas id="chartChuva"></canvas>
    </div>

    <div id="tabelas" class="tab-content">
        <table>
            <thead>
                <tr>
                    <th>Sistema</th>
                    <th>Volume Atual (hm³)</th>
                    <th>Volume <?php echo $ano_ref; ?> (hm³)</th>
                    <th>Percentual (%)</th>
                    <th>Vazão (m³/s)</th>
                    <th>Chuva (mm)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($ids_sistemas as $nome => $id) { ?>
                <tr>
                    <td><?php echo $nome; ?></td>
                    <td><?php echo isset($sistemas_atual[$nome]['volumeUtilArmazenadoPorcentagem']) ? $sistemas_atual[$nome]['volume'] : '-'; ?></td>
                    <td><?php echo isset($sistemas_2021[$nome]['volume']) ? $sistemas_2021[$nome]['volume'] : '-'; ?></td>
                    <td><?php echo isset($sistemas_atual[$nome]['percentual']) ? $sistemas_atual[$nome]['percentual'] : '-'; ?></td>
                    <td><?php echo isset($sistemas_atual[$nome]['vazao']) ? $sistemas_atual[$nome]['vazao'] : '-'; ?></td>
                    <td><?php echo isset($sistemas_atual[$nome]['chuva']) ? $sistemas_atual[$nome]['chuva'] : '-'; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Lógica das abas
document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
    });
});

// Dados para gráficos
const labels = <?php echo json_encode(array_keys($ids_sistemas)); ?>;
const dadosAtual = <?php 
    $valores = [];
    foreach($ids_sistemas as $nome => $id) $valores[] = isset($sistemas_atual[$nome]['volume']) ? $sistemas_atual[$nome]['volume'] : 0;
    echo json_encode($valores);
?>;
const dados2021 = <?php 
    $valores = [];
    foreach($ids_sistemas as $nome => $id) $valores[] = isset($sistemas_2021[$nome]['volume']) ? $sistemas_2021[$nome]['volume'] : 0;
    echo json_encode($valores);
?>;

// Gráfico Reservatórios comparativo
new Chart(document.getElementById('chartReservatorios'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: 'Atual (hm³)', data: dadosAtual, backgroundColor: 'rgba(52,152,219,0.7)' },
            { label: '<?php echo $ano_ref; ?> (hm³)', data: dados2021, backgroundColor: 'rgba(231,76,60,0.7)' }
        ]
    },
    options: {
        scales: { y: { beginAtZero: true } }
    }
});

// Você pode adaptar os gráficos de Vazão e Chuva igual ao anterior
</script>

</body>
</html>
