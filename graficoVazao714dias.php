<?php
header("Content-Type: text/html; charset=utf-8");

// =============================
//  CONFIGURAÇÕES
// =============================
$dataAtual = date("Y-m-d");
$mesAtual = date("m");
$data2021 = "2021-" . date("m-d");

// =============================
// FUNÇÃO PARA BUSCAR DADOS
// =============================
function getDadosSabesp($data) {
    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/$data";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resposta = curl_exec($ch);
    curl_close($ch);
    return json_decode($resposta, true);
}

// =============================
// BUSCAR DADOS DOS ÚLTIMOS 15 DIAS
// =============================
function getDadosUltimosDias($dias) {
    $dados = [];
    for ($i = 0; $i < $dias; $i++) {
        $data = date("Y-m-d", strtotime("-$i days"));
        $res = getDadosSabesp($data);
        if ($res && isset($res["data"])) {
            $dados[$data] = $res["data"];
        }
        usleep(300000); // pequena pausa (0,3s)
    }
    return $dados;
}

$historico = getDadosUltimosDias(15);

// =============================
// FUNÇÃO PARA CALCULAR MÉDIAS (7 e 14 DIAS)
// =============================
function calcularMediaVazoes($historico, $idSistema, $dias) {
    $valores = [];
    $cont = 0;

    foreach ($historico as $data => $sistemas) {
        foreach ($sistemas as $s) {
            if ($s["idSistema"] == $idSistema && isset($s["vazaoNatural"])) {
                $valores[] = $s["vazaoNatural"];
                break;
            }
        }
        if (++$cont >= $dias) break;
    }

    if (count($valores) === 0) return null;
    return array_sum($valores) / count($valores);
}

// =============================
// BUSCAR DADOS (ATUAL E 2021)
// =============================
$dadosAtual = getDadosSabesp($dataAtual);
$dados2021 = getDadosSabesp($data2021);

if (!$dadosAtual || !isset($dadosAtual["data"])) {
    die("<p>❌ Erro: não foi possível obter dados atuais da API.</p>");
}
if (!$dados2021 || !isset($dados2021["data"])) {
    die("<p>⚠️ Erro: não foi possível obter dados de 2021 da API.</p>");
}

// =============================
//  MAPA DE NOMES DOS SISTEMAS
// =============================
$nomesSistemas = [
    64 => "Cantareira",
    65 => "Alto Tietê",
    66 => "Guarapiranga",
    67 => "Cotia",
    68 => "Rio Grande",
    69 => "Rio Claro",
    72 => "São Lourenço",
    75 => "SIM"
];

// =============================
// REMOVER “CANTAREIRA VELHO” (id 74)
// =============================
$sistemasAtual = array_filter($dadosAtual["data"], fn($s) => $s["idSistema"] != 74);
$sistemas2021 = array_filter($dados2021["data"], fn($s) => $s["idSistema"] != 74);

// =============================
//  PREPARAR DADOS PARA GRÁFICO E TABELA
// =============================
$labels = [];
$tabela = [];

foreach ($sistemasAtual as $sAtual) {
    $idSistema = $sAtual["idSistema"];
    $nome = $nomesSistemas[$idSistema] ?? "Sistema $idSistema";

    // Busca o mesmo sistema em 2021
    $s2021 = null;
    foreach ($sistemas2021 as $s) {
        if ($s["idSistema"] == $idSistema) {
            $s2021 = $s;
            break;
        }
    }

    $volAtual = $sAtual["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $vol2021 = $s2021["volumeUtilArmazenadoPorcentagem"] ?? 0;
    $chuvaAtual = $sAtual["chuva"] ?? 0;
    $chuvaAcumMesAtual = $sAtual["chuvaAcumuladaNoMes"] ?? 0;
    $chuvaMediaHistoricaAtual = $sAtual["chuvaMediaHistorica"] ?? 0;

    $diferenca = $volAtual - $vol2021;

    $labels[] = $nome;

    $tabela[] = [
        "sistema" => $nome,
        "vol_atual" => $volAtual,
        "vol_2021" => $vol2021,
        "dif" => $diferenca,
        "chuva" => $chuvaAtual,
        "chuvaAcumuladaNoMes" => $chuvaAcumMesAtual,
        "chuvaMediaHistoricaAtual" => $chuvaMediaHistoricaAtual,

        "vazaoNatural" => $sAtual["vazaoNatural"] ?? 0,
        "vazaoNaturalNoMes" => $sAtual["vazaoNaturalNoMes"] ?? 0,
        "vazaoNaturalMediaHistorica" => $sAtual["vazaoNaturalMediaHistorica"] ?? 0,
        "vazaoNaturalNoMes_2021" => $s2021["vazaoNaturalNoMes"] ?? 0,

        // Novos campos de média móvel
        "vazao7dias" => calcularMediaVazoes($historico, $idSistema, 7),
        "vazao14dias" => calcularMediaVazoes($historico, $idSistema, 14)
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Acompanhamento de Vazões - <?= date("d/m/Y") ?></title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
  body { font-family: "aptos", sans-serif; margin: 30px; background:#f7f9fb; }
  h2 { color: #003366; text-align: center; margin-bottom: 20px; }
  table { font-size:0.85em; }
  canvas { margin-top: 40px; }
  .tabela-container, .grafico-container { width: 100%; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc; margin-bottom: 40px; }
</style>
</head>

<body>
<div class="container">
  <div class="tabela-container">
    <h2>Vazões e Chuvas dos Sistemas Produtores - <?= date("d/m/Y") ?></h2>
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Sistema</th>
          <th>Volume Atual (%)</th>
          <th>Volume 2021 (%)</th>
          <th>Diferença (%)</th>
          <th>Chuva (mm)</th>
          <th>Chuva Acum. Mês (mm)</th>
          <th>Chuva Média Hist. (mm)</th>
          <th>Vazão Natural (m³/s)</th>
          <th>Vazão Mês (m³/s)</th>
          <th>Vazão Média Hist. (m³/s)</th>
          <th>Vazão Mês 2021 (m³/s)</th>
          <th>Vazão Média 7 dias (m³/s)</th>
          <th>Vazão Média 14 dias (m³/s)</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($tabela as $linha): ?>
        <tr>
          <td><?= htmlspecialchars($linha["sistema"]) ?></td>
          <td><?= number_format($linha["vol_atual"], 1, ',', '.') ?></td>
          <td><?= number_format($linha["vol_2021"], 1, ',', '.') ?></td>
          <td><?= number_format($linha["dif"], 1, ',', '.') ?></td>
          <td><?= number_format($linha["chuva"], 1, ',', '.') ?></td>
          <td><?= number_format($linha["chuvaAcumuladaNoMes"], 1, ',', '.') ?></td>
          <td><?= number_format($linha["chuvaMediaHistoricaAtual"], 1, ',', '.') ?></td>
          <td><?= number_format($linha["vazaoNatural"], 1, ',', '.') ?></td>
          <td><?= number_format($linha["vazaoNaturalNoMes"], 1, ',', '.') ?></td>
          <td><?= number_format($linha["vazaoNaturalMediaHistorica"], 1, ',', '.') ?></td>
          <td><?= number_format($linha["vazaoNaturalNoMes_2021"], 1, ',', '.') ?></td>
          <td><?= number_format($linha["vazao7dias"], 1, ',', '.') ?></td>
          <td><?= number_format($linha["vazao14dias"], 1, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="grafico-container">
    <canvas id="grafVazao"></canvas>
  </div>
</div>

<script>
const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;
const vazaoNatural = <?= json_encode(array_column($tabela, "vazaoNatural")); ?>;
const vazaoNaturalNoMes = <?= json_encode(array_column($tabela, "vazaoNaturalNoMes")); ?>;
const vazaoNaturalMediaHistorica = <?= json_encode(array_column($tabela, "vazaoNaturalMediaHistorica")); ?>;
const vazaoNaturalNoMes2021 = <?= json_encode(array_column($tabela, "vazaoNaturalNoMes_2021")); ?>;
const vazao7dias = <?= json_encode(array_column($tabela, "vazao7dias")); ?>;
const vazao14dias = <?= json_encode(array_column($tabela, "vazao14dias")); ?>;

new Chart(document.getElementById("grafVazao"), {
  data: {
    labels: labels,
    datasets: [
      {
        label: "Vazão do Dia (m³/s)",
        type: "bar",
        data: vazaoNatural,
        backgroundColor: "#da6314cf",
        yAxisID: 'y'
      },
      {
        label: "Vazão do Mês (m³/s)",
        type: "bar",
        data: vazaoNaturalNoMes,
        backgroundColor: "rgba(255, 159, 64, 0.7)",
        yAxisID: 'y'
      },
      {
        label: "Vazão Média Histórica (m³/s)",
        type: "bar",
        data: vazaoNaturalMediaHistorica,
        backgroundColor: "rgba(35, 106, 199, 0.9)",
        yAxisID: 'y'
      },
      {
        label: "Vazão Média 7 dias (m³/s)",
        type: "bar",
        data: vazao7dias,
        borderColor: "rgba(255, 206, 86, 1)",

        backgroundColor: "rgba(255, 206, 86, 0.7)",
        borderWidth: 3,
        // pointRadius: 0,
        yAxisID: 'y'
      },
      {
        label: "Vazão Média 14 dias (m³/s)",
        type: "bar",
        data: vazao14dias,
        borderColor: "rgba(153, 102, 255, 1)",
        backgroundColor: "rgba(153, 102, 255, 0.7)",
        yAxisID: 'y'
      },
      {
        label: "Vazão Mês 2021 (m³/s)",
        type: "bar",
        data: vazaoNaturalNoMes2021,
        backgroundColor: "rgba(11, 75, 5, 0.73)",
        yAxisID: 'y'
      }
    ]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { position: "top" },
      title: { display: true, text: "Vazões Diárias, Médias de 7 e 14 dias - <?= date('d/m/Y') ?>" }
    },
    scales: {
      y: {
        beginAtZero: true,
        title: { display: true, text: "Vazão (m³/s)" }
      }
    }
  }
});
</script>

</body>
</html>
