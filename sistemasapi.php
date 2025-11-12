<?php
// ==========================
// 🧩 CONFIGURAÇÕES INICIAIS
// ==========================
header("Content-Type: text/html; charset=utf-8");

// Datas de comparação — últimos valores de cada mês
$anoAtual = date("Y");
$mesAtual = date("m");

$dataInicio2021 = "2021-$mesAtual-01";
$dataFim2021 = date("Y-m-d", strtotime($dataInicio2021));

$dataInicioAtual = "$anoAtual-$mesAtual-01";
$dataFimAtual = date("Y-m-d");

// Lista dos sistemas Sabesp
$sistemas = [
  "cantareira" => "cantareira",
  "alto-tiete" => "alto-tiete",
  "guarapiranga" => "guarapiranga",
  "cotia" => "cotia",
  "rio-grande" => "rio-grande",
  "rio-claro" => "rio-claro",
  "sao-lourenco" => "sao-lourenco"
];

// ==========================
// 🔄 FUNÇÃO PARA OBTER DADOS
// ==========================
function getUltimosDados($sistema, $dataInicio, $dataFim) {
  $url = "https://ssdapi.sabesp.com.br/api/ssd/sistemas/$sistema/dados/$dataInicio/$dataFim";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  $resposta = curl_exec($ch);
  curl_close($ch);

  $dados = json_decode($resposta, true);
  if (!$dados || !isset($dados["data"]) || count($dados["data"]) == 0) return null;

  // Retorna o último registro do período
  return end($dados["data"]);
}

// ==========================
// 📊 MONTAGEM DOS DADOS
// ==========================
$tabela = [];
$labels = [];
$valores_atual = [];
$valores_2021 = [];

foreach ($sistemas as $id => $nome) {
  $dadoAtual = getUltimosDados($id, $dataInicioAtual, $dataFimAtual);
  $dado2021 = getUltimosDados($id, $dataInicio2021, $dataFim2021);

  if (!$dadoAtual && !$dado2021) continue;

  $volAtual = $dadoAtual["volumeOperacional_porcentagem"] ?? 0;
  $vol2021 = $dado2021["volumeOperacional_porcentagem"] ?? 0;

  $tabela[] = [
    "sistema" => $nome,
    "atual" => $volAtual,
    "ano2021" => $vol2021
  ];

  $labels[] = $nome;
  $valores_atual[] = $volAtual;
  $valores_2021[] = $vol2021;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Comparativo Sabesp — Último valor do mês (2021 × Atual)</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  body { font-family: Arial, sans-serif; margin: 30px; background: #f7f9fb; }
  h2 { color: #003366; text-align: center; margin-bottom: 20px; }
  table { border-collapse: collapse; width: 100%; margin-top: 20px; background: #fff; }
  th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
  th { background-color: #e0ecf8; }
  tr:nth-child(even) { background: #f5f5f5; }
  canvas { margin-top: 40px; }
  .container { display: flex; flex-direction: column; align-items: center; }
  .tabela-container { width: 80%; }
  .grafico-container { width: 80%; }
</style>
</head>
<body>

<!-- <h2>Comparativo de Volume Operacional — Último valor de <?php echo date("m/Y"); ?> (2021 × <?php echo $anoAtual; ?>)</h2> -->

<div class="container">
  <div class="tabela-container">
    <table>
      <thead>
        <tr>
          <th>Sistema</th>
          <th>Volume Atual (<?php echo date("d/m/Y", strtotime($dataFimAtual)); ?>)</th>
          <th>Volume em 2021 (<?php echo date("d/m/Y", strtotime($dataFim2021)); ?>)</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($tabela as $linha): ?>
        <tr>
          <td><?= htmlspecialchars($linha["sistemas"]) ?></td>
          <td><?= number_format($linha["volumeOperacional_porcentagem"], 1, ',', '.') ?>%</td>
          <td><?= number_format($linha["ano2021"], 1, ',', '.') ?>%</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="grafico-container">
    <canvas id="graficoSabesp" width="900" height="400"></canvas>
  </div>
</div>

<script>
const labels = <?php echo json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;
const volumeAtual = <?php echo json_encode($valores_atual); ?>;
const volume2021 = <?php echo json_encode($valores_2021); ?>;

new Chart(document.getElementById("graficoSabesp"), {
  type: "bar",
  data: {
    labels: labels,
    datasets: [
      {
        label: "Atual (<?php echo $anoAtual; ?>)",
        data: volumeAtual,
        backgroundColor: "rgba(0,123,255,0.7)"
      },
      {
        label: "2021",
        data: volume2021,
        backgroundColor: "rgba(0,200,83,0.7)"
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: "top" },
      title: { display: true, text: "Comparativo — Último valor do mês (2021 × <?php echo $anoAtual; ?>)" }
    },
    scales: {
      y: { beginAtZero: true, title: { display: true, text: "Volume (%)" } }
    }
  }
});
</script>

</body>
</html>
