<?php
header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set("America/Sao_Paulo");

// =============================
// CONFIGURAÇÕES
// =============================
$idSistema = 75;
$cacheFile = __DIR__ . "/cache_sim.json";
$cacheTime = 1800; // 30 min de cache
$apiBase = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/24/10/2025e";

// =============================
// FUNÇÃO: BUSCAR DADOS API COM CACHE
// =============================
function getApiData($url, $cacheFile, $cacheTime) {
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    if ($res) {
        file_put_contents($cacheFile, $res);
        return json_decode($res, true);
    }
    return [];
}

// =============================
// TENTA PEGAR O DADO DE HOJE OU ONTEM
// =============================
$dataHoje = date("Y-m-d");
$dataOntem = date("Y-m-d", strtotime("-1 day"));

$dados = getApiData($apiBase . $dataHoje, $cacheFile, $cacheTime);
if (empty($dados)) {
    $dados = getApiData($apiBase . $dataOntem, $cacheFile, $cacheTime);
}

// =============================
// EXTRAI DADO REAL (SIM)
// =============================
$valorReal = null;
$dataReal = null;

if (!empty($dados) && is_array($dados)) {
    foreach ($dados as $sistema) {
        if (isset($sistema["idSistema"]) && $sistema["idSistema"] == $idSistema) {
            if (isset($sistema["volume"]["volumeUtilArmazenadoPorcentagem"])) {
                $valorReal = $sistema["volume"]["volumeUtilArmazenadoPorcentagem"];
                $dataReal = $sistema["dataInformacao"] ?? $dataHoje;
            }
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Projeções de Volume do SIM</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background: #f9f9f9; }
        h4 { text-align: center; margin-bottom: 10px; }
        .atualizacao { text-align: right; font-size: 0.9em; color: #555; margin-bottom: 10px; }
        canvas { background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 0 5px #ccc; }
    </style>
</head>
<body>
<div class="container">
    <div class="atualizacao">
        Atualizado em: <?= date("d/m/Y H:i") ?>
    </div>

    <h4>Projeções de Volume do SIM</h4>
    <canvas id="grafico_linha"></canvas>
</div>

<script>
// =============================
// VARIÁVEIS COM O DADO REAL
// =============================
const valorReal = <?= json_encode($valorReal) ?>;
const dataReal = <?= json_encode($dataReal) ?>;

// =============================
// LER CSV E GERAR GRÁFICO
// =============================
async function criarGraficoLinha() {
    const resposta = await fetch("serie_diaria.csv");
    const texto = await resposta.text();
    const linhas = texto.split("\n").map(l => l.trim()).filter(l => l.length);

    const cabecalho = linhas[0].split(",");
    const dados = linhas.slice(1).map(l => l.split(","));
    const labels = dados.map(l => l[0]);

    const cores = {
        "QN 100 MLT":  "#021d58",
        "QN 70 MLT":   "#cc0505",
        "QN (20/25)":  "#046e1f",
        "QN (2021)":   "#0cb130",
        "QN (2014)":   "#df5c11ff",
        "QN (2020)":   "#df11b2ff",
        "Real": "#000000"
    };

    const datasets = cabecalho.slice(1).map((nome, idx) => ({
        label: nome,
        data: dados.map(l => parseFloat(l[idx + 1]) || null),
        borderColor: cores[nome] || `hsl(${Math.random() * 360}, 50%, 40%)`,
        borderWidth: 2,
        pointRadius: 0,
        fill: false,
        tension: 0.25
    }));

    // 🔹 Adiciona a linha do valor real (API)
    if (valorReal !== null) {
        const dadosReal = new Array(labels.length - 1).fill(null);
        dadosReal.push(valorReal);

        datasets.push({
            label: `Real (${dataReal})`,
            data: dadosReal,
            borderColor: cores["Real"],
            borderWidth: 3,
            pointBackgroundColor: cores["Real"],
            pointRadius: 5,
            fill: false,
            tension: 0.3
        });
    }

    new Chart(document.getElementById("grafico_linha"), {
        type: "line",
        data: { labels, datasets },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: "top" },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label || '';
                            const valor = context.parsed.y;
                            return valor !== null ? `${label}: ${valor.toFixed(2)}%` : null;
                        }
                    }
                }
            },
            scales: {
                x: { title: { display: true, text: "Data" } },
                y: { title: { display: true, text: "Volume Útil (%)" } }
            }
        }
    });
}

criarGraficoLinha();
</script>
</body>
</html>
