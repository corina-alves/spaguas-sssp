<?php
header("Content-Type: text/html; charset=utf-8");

// =============================
//  CONFIGURAÇÕES
// =============================
$dataAtual = date("Y-m-d");
$mesAtual = date("m");
// Data automática de 2021 (mesmo dia e mês do ano atual)
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
    75 => "SIM",
    // 74 => "Cantareira Velho" — 🔹 REMOVIDO
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
$volumesAtual = [];
$volumes2021 = [];
$difVol = [];
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

    $chuvaAcumMes2021 = $s2021["chuva"] ?? 0;
    $chuvaAcum2021 = $s2021["chuvaAcumuladaNoMes"] ?? 0;
    $chuvaMediaHistorica = $s2021["chuvaMediaHistorica"] ?? 0;
    $diferenca = $volAtual - $vol2021;

    $labels[] = $nome;
    $volumesAtual[] = $volAtual;
    $volumes2021[] = $vol2021;
    $difVol[] = $diferenca;

    $tabela[] = [
        "sistema" => $nome,
        "vol_atual" => $volAtual,
        "vol_2021" => $vol2021,
        "dif" => $diferenca,
        "chuva" => $chuvaAtual,
        "chuvaAcumuladaNoMes" => $chuvaAcumMesAtual,
        "chuvaMediaHistoricaAtual" => $chuvaMediaHistoricaAtual,
        "chuva_2021" => $chuvaAcumMes2021,
        "chuvaAcumuladaNoMes_2021" => $chuvaAcum2021,
        "chuvaMediaHistorica" => $chuvaMediaHistorica,

        "vazaoAfluente" => $sAtual["vazaoAfluente"] ?? 0,

        "vazaoNatural" => $sAtual["vazaoNatural"] ?? 0,
        "vazaoNaturalNoMes" => $sAtual["vazaoNaturalNoMes"] ?? 0,
        "vazaoNaturalMediaHistorica" => $sAtual["vazaoNaturalMediaHistorica"] ?? 0,

          // 🔹 NOVOS CAMPOS DE 2021:
        "vazaoNatural_2021" => $s2021["vazaoNatural"] ?? 0,
        "vazaoNaturalNoMes_2021" => $s2021["vazaoNaturalNoMes"] ?? 0,
        "vazaoNaturalMediaHistorica_2021" => $s2021["vazaoNaturalMediaHistorica"] ?? 0

    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Acompanhamento do reservatórios da RMSP: <?= date("d/m/Y") ?> e ano 2021</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@2.2.1"></script>
<link href="assets/css/style.css" rel="">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

<style>
  body { font-family: "aptos", sans-serif; margin: 30px; }
  h2 { color: #003366; text-align: center; margin-bottom: 20px; }
  table { font-size:0.8em; }

  canvas { margin-top: 40px; }
  .tabela-container, .grafico-container { width: 100%; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px #ccc; margin-bottom: 0px; }
    
</style>
</head>

<body>
<?php include "navbar.php";?>
<!-- <h2>Sistema Produtores  <?= date("d/m/Y") ?> e ano de 2021</h2> -->

<div class="container">
  <div class="tabela-container">
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

          <th>Chuva 2021 (mm)</th>
          <th>Chuva Acum. Mês 2021 (mm)</th>
          <th>Chuva Média Hist. (mm)</th>

          <th>Vazão Afluente (m³/s)</th>
          <th>Vazão Natural (m³/s)</th>
          <th>Vazão Natural Mês (m³/s)</th>
          <th>Vazão Nat. Média Hist. (m³/s)</th>

          <th>Vazão Natural 2021 (m³/s)</th>
        <th>Vazão Natural Mês 2021 (m³/s)</th>
        <th>Vazão Nat. Média Hist. 2021 (m³/s)</th>
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

        <td><?= number_format($linha["chuva_2021"], 1, ',', '.') ?></td>
        <td><?= number_format($linha["chuvaAcumuladaNoMes_2021"], 1, ',', '.') ?></td>
        <td><?= number_format($linha["chuvaMediaHistorica"], 1, ',', '.') ?></td>
         
        <td><?= number_format($linha["vazaoAfluente"], 1, ',', '.') ?></td>
        <td><?= number_format($linha["vazaoNatural"], 1, ',', '.') ?></td>
        <td><?= number_format($linha["vazaoNaturalNoMes"], 1, ',', '.') ?></td>
        <td><?= number_format($linha["vazaoNaturalMediaHistorica"], 1, ',', '.') ?></td>

            <td><?= number_format($linha["vazaoNatural_2021"], 1, ',', '.') ?></td>
            <td><?= number_format($linha["vazaoNaturalNoMes_2021"], 1, ',', '.') ?></td>
            <td><?= number_format($linha["vazaoNaturalMediaHistorica_2021"], 1, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>


  
    <canvas id="grafVazaoChuva" width="900" height="400"></canvas>
        <!--legendas dos estagios de protocolo de escassez-->
    <!-- <div class="legenda-protocolo mt-3">
        <ul>
            <li><span class="cor e1"></span> E1 - Atenção</li>
            <li><span class="cor e2"></span> E2 - Alerta</li>
            <li><span class="cor e3"></span> E3 - Crítico</li>
            <li><span class="cor e4"></span> E4 - Emergência</li>
        </ul>
    </div> -->
 
   <hr/>
    <canvas id="grafChuva" width="900" height="400"></canvas>
    <hr/>
    <canvas id="grafBarraEmpilhada" width="900" height="400"></canvas>


</div>

<script>
const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE); ?>;

// Dados vindos do PHP
const chuvaAtual = <?= json_encode(array_column($tabela, "chuva")); ?>;
const chuvaMesAtual = <?= json_encode(array_column($tabela, "chuvaAcumuladaNoMes")); ?>;
const chuvaMediaHistorica = <?= json_encode(array_column($tabela, "chuvaMediaHistorica")); ?>;


const chuvaAcumuladaNoMes_2021 = <?= json_encode(array_column($tabela, "chuvaAcumuladaNoMes")); ?>;
const chuvaAcumMes2021 = <?= json_encode(array_column($tabela, "chuvaAcumuladaNoMes_2021")); ?>;
const chuva2021 = <?= json_encode(array_column($tabela, "chuva_2021")); ?>;

const vazaoNatural = <?= json_encode(array_column($tabela, "vazaoNatural")); ?>;
const vazaoNaturalNoMes = <?= json_encode(array_column($tabela, "vazaoNaturalNoMes")); ?>;
const vazaoNaturalMediaHistorica = <?= json_encode(array_column($tabela, "vazaoNaturalMediaHistorica")); ?>;

const vazaoNatural2021 = <?= json_encode(array_column($tabela, "vazaoNatural_2021")); ?>;
const vazaoNaturalNoMes2021 = <?= json_encode(array_column($tabela, "vazaoNaturalNoMes_2021")); ?>;
const vazaoNaturalMediaHistorica2021 = <?= json_encode(array_column($tabela, "vazaoNaturalMediaHistorica_2021")); ?>;

// -----------------------------
// SISTEMAS QUE FORMAM O SIM
// -----------------------------
const reservatoriosSIM = [
  "Cantareira",
  "Alto Tietê",
  "Guarapiranga",
  "Rio Grande",
  "Cotia",
  "São Lourenço"
];

// -----------------------------
// FUNÇÃO PARA CALCULAR MÉDIA
// -----------------------------
function calcularMedia(indices, arrayDados) {
  const valores = indices.map(i => arrayDados[i] ?? 0);
  const soma = valores.reduce((acc, val) => acc + val, 0);
  return valores.length > 0 ? soma / valores.length : 0;
}

// -----------------------------
//  IDENTIFICAR ÍNDICES DOS SISTEMAS
// -----------------------------
const indicesReservatorios = reservatoriosSIM
  .map(nome => labels.indexOf(nome))
  .filter(i => i >= 0); // remove -1

// -----------------------------
//  CÁLCULO DAS MÉDIAS PARA O SIM
// -----------------------------
const mediaChuvaAtualSIM = calcularMedia(indicesReservatorios, chuvaAtual);
const mediaChuvaMesAtualSIM = calcularMedia(indicesReservatorios, chuvaMesAtual);
const mediaChuva2021SIM = calcularMedia(indicesReservatorios, chuvaAcumMes2021);
const mediaChuvaMediaHistoricaSIM = calcularMedia(indicesReservatorios, chuvaMediaHistorica);


// -----------------------------
//  SUBSTITUIR VALORES NO SIM
// -----------------------------
const idxSIM = labels.indexOf("SIM");
if (idxSIM >= 0) {
  chuvaAtual[idxSIM] = mediaChuvaAtualSIM;
  chuvaMesAtual[idxSIM] = mediaChuvaMesAtualSIM;
  chuvaAcumMes2021[idxSIM] = mediaChuva2021SIM;
    chuvaMediaHistorica[idxSIM] = mediaChuvaMediaHistoricaSIM;
}

console.log("Média de chuva SIM (atual):", mediaChuvaAtualSIM.toFixed(1));
console.log("Média de chuva SIM (mês atual):", mediaChuvaMesAtualSIM.toFixed(1));
console.log("Média de chuva SIM (2021):", mediaChuva2021SIM.toFixed(1));
console.log("Média de chuva SIM (média histórica):", mediaChuvaMediaHistoricaSIM.toFixed(1));

// -----------------------------
//  GRÁFICO COM VAZÕES
// -----------------------------
new Chart(document.getElementById("grafVazaoChuva"), {
  data: {
    labels: labels,
    datasets: [
    //   {
    //     label: "Chuva Atual (mm)",
    //     type: "line",
    //     data: chuvaAtual,
    //     borderColor: "rgba(0, 123, 255, 1)",
    //     backgroundColor: "rgba(0, 123, 255, 0.3)",
    //     borderWidth: 3,
    //     yAxisID: 'y1',
    //     pointRadius: 0 // Remove os pontos da linha
       
        
    //   },
    //   {
    //     label: "Chuva Acum. 2021 (mm)",
    //     type: "line",
    //     data: chuva2021,
    //     borderColor: "rgba(75, 192, 192, 1)",
    //     backgroundColor: "rgba(75, 192, 192, 0.3)",
    //     borderWidth: 3,
    //     yAxisID: 'y1',
    //     pointRadius: 0 // Remove os pontos da linha
    //   },
      {
        label: "Vazão do Dia (m³/s)",
        type: "bar",
        data: vazaoNatural,
        backgroundColor: "#da6314cf",
        borderWidth: 2,
        yAxisID: 'y1'
      },

    //       {
    //     label: "Vazão do 7 dias do Mês (m³/s)",
    //     type: "bar",
    //     data: vazaoNaturalNoMes,
    //     backgroundColor: "rgba(255, 159, 64, 0.7)",
    //     borderWidth: 2,
    //     yAxisID: 'y2'
    //   },
      {
        label: "Vazão do Mês (m³/s)",
        type: "bar",
        data: vazaoNaturalNoMes,
        backgroundColor: "rgba(255, 159, 64, 0.7)",
        borderWidth: 2,
        yAxisID: 'y1'
      },
      
      {
        label: "Vazão Média Histórica (m³/s)",
        type: "bar",
        data: vazaoNaturalMediaHistorica,
        backgroundColor: "rgba(35, 106, 199, 1)",
        borderWidth: 2,
        yAxisID: 'y1'
      },

           {
        label: "Vazão Mês Ano de 2021 (m³/s)",
        type: "bar",
        data: vazaoNaturalNoMes2021,
        backgroundColor: "rgba(11, 75, 5, 0.73)",
        borderWidth: 2,
        yAxisID: 'y1'
      },



    ]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { position: "top" },
      title: {
        display: true,
        text: " Acompanhamento das Chuvas dos dias <?= date('d/m/Y') ?> e referente do ano 2021 dos reservatórios da (RMSP)"
      },
  
    },
    scales: {
      y1: {
        type: 'linear',
        position: 'left',
        title: { display: true, text: "Vazão(m³/s)" },
        beginAtZero: true
      }

    }
  }
});

//---------------- GRAFICO DO CHUVA ------------
new Chart(document.getElementById("grafChuva"), {
  data: {
    labels: labels,
    datasets: [
      // Chuvas
      {
        label: "Chuva Atual (mm)",
        type: "bar",
        data: chuvaAtual,
        backgroundColor: "#03c0fab3",
        borderWidth: 3,
        yAxisID: 'y1'
      },
  
          {
        label: "Chuva acum. do mês Atual (mm)",
        type: "bar",
        data: chuvaMesAtual,
        backgroundColor: "#0e59e4ff",
        borderWidth: 3,
        yAxisID: 'y1'
      },
    {
        label: "Chuva Média Histórica Ano Atual(mm)",
        type: "bar",
        data: chuvaMediaHistorica,
        backgroundColor: "#0082fc99",
        borderWidth: 3,
        yAxisID: 'y1'
      },

            {
        label: "Chuva acum. Mês 2021 (mm)",
        type: "bar",
        // data: chuva2021,
        data: chuvaAcumMes2021,
        backgroundColor: "#0c830c99",
        borderWidth: 3,
        yAxisID: 'y1'
      },

            //  Vazão do Dia (linha)
    ]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { position: "top" },
      title: {
        display: true,
        text: " Acompanhamento das Chuvas dos dias <?= date('d/m/Y') ?> e referente do ano 2021 dos reservatórios da (RMSP)"
      },
  
    },
    scales: {
      y1: {
        type: 'linear',
        position: 'left',
        title: { display: true, text: "Chuva (mm)" },
        beginAtZero: true
      }

    }
  }
});

// Vazões empilhadas com vazões sobrepostas
new Chart(document.getElementById("grafBarraEmpilhada"), {
  type: "bar",
  data: {
    labels: labels,
    datasets: [
      // === Vazões empilhadas ===
      {
        label: "Vazão Média Histórica (m³/s)",
        data: vazaoNaturalMediaHistorica,
        backgroundColor: "rgba(54, 162, 235, 0.6)",
        stack: "vazao",
        yAxisID: "y",
      },
      {
        label: "Vazão do Mês (m³/s)",
        data: vazaoNaturalNoMes,
        backgroundColor: "rgba(75, 192, 192, 0.7)",
        stack: "vazao",
        yAxisID: "y",
      },
      {
        label: "Vazão do Dia (m³/s)",
        data: vazaoNatural,
        backgroundColor: "rgba(0, 123, 255, 0.9)",
        stack: "vazao",
        yAxisID: "y",
      },

      // === Chuvas (barras finas sobrepostas) ===
    //   {
    //     label: "Chuva Atual (mm)",
    //     data: chuvaAtual,
    //     backgroundColor: "rgba(255, 193, 7, 0.8)",
    //     yAxisID: "y2",
    //     barPercentage: 0.3,
    //     categoryPercentage: 0.4,
    //   },
     // {
    //     type:"line",
    //     label: "Chuva 2021 (mm)",
    //     data: chuva2021, // ou outra variável de chuva de 2021
    //     backgroundColor: "#ca032ecc",
    //     borderColor: "#ca032ecc",
    //     borderWidth: 2,
    //     yAxisID: "y2",
    //     barPercentage: 0.3,
    //     categoryPercentage: 0.4,
    //     pointRadius: 0,
    //   },
        {
        label: "Vazão do Mês 2021 (m³/s)",
        data: vazaoNaturalNoMes2021,
        backgroundColor: "hsla(46, 94%, 51%, 0.70)",
        stack: "vazao",
        yAxisID: "y",
        },
    ],
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: "top" },
      title: { display: true, text: "Vazão e Chuva (<?php echo date('d/m/Y'); ?>)" },
    },
    scales: {
      y: {
        beginAtZero: true,
        title: { display: true, text: "Vazão (m³/s)" },
        stacked: true, // empilha as vazões
      },
    //   y2: {
    //     position: "right",
    //     beginAtZero: true,
    //     title: { display: true, text: "Chuva (mm)" },
    //     grid: { drawOnChartArea: false },
    //   },
    },
  },
});
</script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>

</body>
</html>
