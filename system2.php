<?php
header("Content-Type: text/html; charset=utf-8");

// =============================
//  CONFIGURAÇÕES / DATAS
// =============================
date_default_timezone_set('America/Sao_Paulo');
$dataAtual = date("Y-m-d");
$mesAtual = date("m");
// Data automática de 2021 (mesmo dia e mês do ano atual)
$data2021 = "2021-" . date("m-d");

// =============================
// FUNÇÃO PARA BUSCAR DADOS (SINGLE DAY)
// =============================
function getDadosSabesp($data) {
    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/$data";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // timeout razoável
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $resposta = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$resposta || $httpcode != 200) {
        // retorno nulo em caso de erro/sem dados
        return null;
    }
    $json = json_decode($resposta, true);
    return $json;
}

// =============================
// FUNÇÃO PARA OBTER MÉDIAS EM INTERVALO (últimos N dias)
// =============================
// Retorna array ['data' => [ ... lista de sistemas com médias ... ]]
function getMediaRangeSabesp($dias) {
    // acumuladores por idSistema
    $somas = []; // id => campo => soma
    $contagem = []; // id => número de dias com dado
    $idsEncontrados = []; // track ids
    for ($i = 0; $i < $dias; $i++) {
        $data = date("Y-m-d", strtotime("-{$i} days"));
        $dadosDia = getDadosSabesp($data);
        if (!$dadosDia || !isset($dadosDia["data"])) {
            // pula dia com erro
            continue;
        }
        foreach ($dadosDia["data"] as $s) {
            $id = $s["idSistema"];
            // Ignorar Cantareira Velho (74)
            if ($id == 74) continue;

            $idsEncontrados[$id] = true;
            if (!isset($somas[$id])) {
                $somas[$id] = [
                    "idSistema" => $id,
                    "volumeUtilArmazenadoPorcentagem" => 0,
                    "chuva" => 0,
                    "chuvaAcumuladaNoMes" => 0,
                    "chuvaMediaHistorica" => 0,
                    "vazaoAfluente" => 0,
                    "vazaoNatural" => 0,
                    "vazaoNaturalNoMes" => 0,
                    "vazaoNaturalMediaHistorica" => 0
                ];
                $contagem[$id] = 0;
            }

            // Somar somente se campo existir e for numérico
            $fields = ["volumeUtilArmazenadoPorcentagem","chuva","chuvaAcumuladaNoMes","chuvaMediaHistorica",
                       "vazaoAfluente","vazaoNatural","vazaoNaturalNoMes","vazaoNaturalMediaHistorica"];
            foreach ($fields as $f) {
                if (isset($s[$f]) && is_numeric($s[$f])) {
                    $somas[$id][$f] += $s[$f];
                } else {
                    // soma zero (não adiciona)
                }
            }
            $contagem[$id] += 1;
        }
    }

    // calcular médias
    $resultado = ["data" => []];
    foreach ($idsEncontrados as $id => $_) {
        $count = $contagem[$id] ?? 1;
        $media = $somas[$id];
        // dividir por contagem (evita divisão por zero)
        foreach ($media as $k => $v) {
            if ($k === "idSistema") continue;
            $media[$k] = $count > 0 ? ($v / $count) : 0;
        }
        // manter idSistema
        $resultado["data"][] = $media;
    }

    return $resultado;
}

// =============================
// BUSCAR DADOS (ATUAL E 2021)
// =============================
$dadosAtual = getDadosSabesp($dataAtual);
$dados2021 = getDadosSabesp($data2021);

// Pegar médias dos últimos 7 e 14 dias
$dados7 = getMediaRangeSabesp(7);
$dados14 = getMediaRangeSabesp(14);

if (!$dadosAtual || !isset($dadosAtual["data"])) {
    // mostramos mensagem porém permitimos continuar com 7/14 caso existam
    $erroAtual = true;
} else {
    $erroAtual = false;
}
if (!$dados2021 || !isset($dados2021["data"])) {
    $erro2021 = true;
} else {
    $erro2021 = false;
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
    // 74 => "Cantareira Velho" — REMOVIDO
];

// =============================
// REMOVER “CANTAREIRA VELHO” (id 74) caso presente nos conjuntos
// =============================
function filtrarRemover74($arr) {
    if (!$arr || !isset($arr["data"])) return ["data" => []];
    $out = array_filter($arr["data"], fn($s) => ($s["idSistema"] ?? 0) != 74);
    return ["data" => array_values($out)];
}
$sistemasAtual = filtrarRemover74($dadosAtual);
$sistemas2021 = filtrarRemover74($dados2021);
$sistemas7 = filtrarRemover74($dados7);
$sistemas14 = filtrarRemover74($dados14);

// =============================
// FUNÇÃO AUXILIAR: montar tabela a partir do array de sistemas
// =============================
function montarTabelaSistemas($sistemas, $sistemasComparar = null, $nomesSistemas = []) {
    $labels = [];
    $volumes = [];
    $tabela = [];

    foreach ($sistemas["data"] as $sAtual) {
        $idSistema = $sAtual["idSistema"];
        $nome = $nomesSistemas[$idSistema] ?? "Sistema $idSistema";

        // Busca o mesmo sistema no comparar (se fornecido)
        $sComparar = null;
        if ($sistemasComparar && isset($sistemasComparar["data"])) {
            foreach ($sistemasComparar["data"] as $s) {
                if ($s["idSistema"] == $idSistema) {
                    $sComparar = $s;
                    break;
                }
            }
        }

        $volAtual = $sAtual["volumeUtilArmazenadoPorcentagem"] ?? 0;
        $volComparar = $sComparar["volumeUtilArmazenadoPorcentagem"] ?? 0;
        $chuvaAtual = $sAtual["chuva"] ?? 0;
        $chuvaAcumMesAtual = $sAtual["chuvaAcumuladaNoMes"] ?? 0;
        $chuvaMediaHistoricaAtual = $sAtual["chuvaMediaHistorica"] ?? 0;

        $chuvaComparar = $sComparar["chuva"] ?? 0;
        $chuvaAcumMesComparar = $sComparar["chuvaAcumuladaNoMes"] ?? 0;
        $chuvaMediaHistoricaComparar = $sComparar["chuvaMediaHistorica"] ?? 0;
        $diferenca = $volAtual - $volComparar;

        $labels[] = $nome;
        $volumes[] = $volAtual;

        $tabela[] = [
            "idSistema" => $idSistema,
            "sistema" => $nome,
            "vol_atual" => $volAtual,
            "vol_comp" => $volComparar,
            "dif" => $diferenca,
            "chuva" => $chuvaAtual,
            "chuvaAcumuladaNoMes" => $chuvaAcumMesAtual,
            "chuvaMediaHistoricaAtual" => $chuvaMediaHistoricaAtual,
            "chuva_comp" => $chuvaComparar,
            "chuvaAcumuladaNoMes_comp" => $chuvaAcumMesComparar,
            "chuvaMediaHistorica_comp" => $chuvaMediaHistoricaComparar,
            "vazaoAfluente" => $sAtual["vazaoAfluente"] ?? 0,
            "vazaoNatural" => $sAtual["vazaoNatural"] ?? 0,
            "vazaoNaturalNoMes" => $sAtual["vazaoNaturalNoMes"] ?? 0,
            "vazaoNaturalMediaHistorica" => $sAtual["vazaoNaturalMediaHistorica"] ?? 0,
        ];
    }

    return ["labels" => $labels, "volumes" => $volumes, "tabela" => $tabela];
}

// Montar tabelas para cada aba
$montagemHoje = montarTabelaSistemas($sistemasAtual, $sistemas2021, $nomesSistemas);
$montagem7 = montarTabelaSistemas($sistemas7, $sistemas2021, $nomesSistemas);
$montagem14 = montarTabelaSistemas($sistemas14, $sistemas2021, $nomesSistemas);

// Para a lógica do SIM: se existir entrada 'SIM' no labels, vamos calcular a média
$reservatoriosSIM = ["Cantareira","Alto Tietê","Guarapiranga","Rio Grande","Cotia","São Lourenço"];
function aplicarSIMMedia(&$montagem) {
    // já foi calculado na API range — aqui só garantimos que o rótulo "SIM" exista e seus valores representem a média
    // Assumimos que a API já possui id 75 como SIM quando presente; caso não, manteremos como está.
    return;
}
aplicarSIMMedia($montagemHoje);
aplicarSIMMedia($montagem7);
aplicarSIMMedia($montagem14);

// =============================
// PRONTO: vamos renderizar HTML + tabelas + gráficos (Chart.js)
// =============================
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Acompanhamento dos reservatórios da RMSP - <?= date("d/m/Y") ?></title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { font-family: "aptos", sans-serif; margin: 20px; background:#f5f7fa; }
  h2 { color: #003366; text-align: center; margin-bottom: 10px; }
  .tabela-container, .grafico-container { width: 100%; background: #fff; padding: 18px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.06); margin-bottom: 20px; }
  table { font-size:0.85em; }
  canvas { margin-top: 20px; }
</style>
</head>
<body>
<?php
// Inclua sua navbar se quiser
if (file_exists("navbar.php")) include "navbar.php";
?>

<div class="container">
  <h2>Acompanhamento das Chuvas e Vazões — Atualizado <?= date("d/m/Y") ?></h2>

  <!-- NAV TABS -->
  <ul class="nav nav-tabs" id="abasDados" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="hoje-tab" data-bs-toggle="tab" data-bs-target="#aba-hoje" type="button" role="tab">Hoje (<?= date("d/m/Y") ?>)</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="7dias-tab" data-bs-toggle="tab" data-bs-target="#aba-7dias" type="button" role="tab">Últimos 7 dias (média)</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="14dias-tab" data-bs-toggle="tab" data-bs-target="#aba-14dias" type="button" role="tab">Últimos 14 dias (média)</button>
    </li>
  </ul>

  <div class="tab-content mt-3" id="conteudoAbas">
    <!-- ABA HOJE -->
    <div class="tab-pane fade show active" id="aba-hoje" role="tabpanel">
      <div class="tabela-container">
        <?php if ($erroAtual): ?>
          <div class="alert alert-danger">❌ Erro ao obter os dados atuais da API para <?= $dataAtual ?>.</div>
        <?php endif; ?>

        <table class="table table-hover table-sm">
          <thead>
            <tr>
              <th>Sistema</th>
              <th>Vol Atual (%)</th>
              <th>Vol 2021 (%)</th>
              <th>Dif (%)</th>
              <th>Chuva (mm)</th>
              <th>Chuva Acum. Mês (mm)</th>
              <th>Vazão (m³/s)</th>
              <th>Vazão Mês (m³/s)</th>
              <th>Vazão Média Hist. (m³/s)</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($montagemHoje["tabela"] as $linha): ?>
            <tr>
              <td><?= htmlspecialchars($linha["sistema"]) ?></td>
              <td><?= number_format($linha["vol_atual"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["vol_comp"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["dif"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["chuva"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["chuvaAcumuladaNoMes"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["vazaoNatural"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["vazaoNaturalNoMes"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["vazaoNaturalMediaHistorica"], 1, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <canvas id="grafVazaoChuvaHoje" width="1000" height="380"></canvas>
        <hr/>
        <canvas id="grafChuvaHoje" width="1000" height="380"></canvas>
        <hr/>
        <canvas id="grafBarraEmpilhadaHoje" width="1000" height="380"></canvas>
      </div>
    </div>

    <!-- ABA 7 DIAS (MÉDIA) -->
    <div class="tab-pane fade" id="aba-7dias" role="tabpanel">
      <div class="tabela-container">
        <table class="table table-hover table-sm">
          <thead>
            <tr>
              <th>Sistema</th>
              <th>Vol Média (%)</th>
              <th>Vol 2021 (%)</th>
              <th>Dif (%)</th>
              <th>Chuva Média (mm)</th>
              <th>Chuva Acum. Mês (mm)</th>
              <th>Vazão Média (m³/s)</th>
              <th>Vazão Mês Média (m³/s)</th>
              <th>Vazão Média Hist. (m³/s)</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($montagem7["tabela"] as $linha): ?>
            <tr>
              <td><?= htmlspecialchars($linha["sistema"]) ?></td>
              <td><?= number_format($linha["vol_atual"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["vol_comp"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["dif"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["chuva"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["chuvaAcumuladaNoMes"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["vazaoNatural"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["vazaoNaturalNoMes"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["vazaoNaturalMediaHistorica"], 1, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <canvas id="grafVazaoChuva7" width="1000" height="380"></canvas>
        <hr/>
        <canvas id="grafChuva7" width="1000" height="380"></canvas>
        <hr/>
        <canvas id="grafBarraEmpilhada7" width="1000" height="380"></canvas>
      </div>
    </div>

    <!-- ABA 14 DIAS (MÉDIA) -->
    <div class="tab-pane fade" id="aba-14dias" role="tabpanel">
      <div class="tabela-container">
        <table class="table table-hover table-sm">
          <thead>
            <tr>
              <th>Sistema</th>
              <th>Vol Média (%)</th>
              <th>Vol 2021 (%)</th>
              <th>Dif (%)</th>
              <th>Chuva Média (mm)</th>
              <th>Chuva Acum. Mês (mm)</th>
              <th>Vazão Média (m³/s)</th>
              <th>Vazão Mês Média (m³/s)</th>
              <th>Vazão Média Hist. (m³/s)</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($montagem14["tabela"] as $linha): ?>
            <tr>
              <td><?= htmlspecialchars($linha["sistema"]) ?></td>
              <td><?= number_format($linha["vol_atual"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["vol_comp"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["dif"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["chuva"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["chuvaAcumuladaNoMes"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["vazaoNatural"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["vazaoNaturalNoMes"], 1, ',', '.') ?></td>
              <td><?= number_format($linha["vazaoNaturalMediaHistorica"], 1, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>

        <canvas id="grafVazaoChuva14" width="1000" height="380"></canvas>
        <hr/>
        <canvas id="grafChuva14" width="1000" height="380"></canvas>
        <hr/>
        <canvas id="grafBarraEmpilhada14" width="1000" height="380"></canvas>
        <hr/>
      </div>
    </div>
  </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Dados vindos do PHP (convertendo estruturas)
const labelsHoje = <?= json_encode($montagemHoje["labels"], JSON_UNESCAPED_UNICODE); ?>;
const tabelaHoje = <?= json_encode($montagemHoje["tabela"], JSON_UNESCAPED_UNICODE); ?>;

const labels7 = <?= json_encode($montagem7["labels"], JSON_UNESCAPED_UNICODE); ?>;
const tabela7 = <?= json_encode($montagem7["tabela"], JSON_UNESCAPED_UNICODE); ?>;

const labels14 = <?= json_encode($montagem14["labels"], JSON_UNESCAPED_UNICODE); ?>;
const tabela14 = <?= json_encode($montagem14["tabela"], JSON_UNESCAPED_UNICODE); ?>;

// Função auxiliar para extrair arrays por campo
function extrairCampo(arr, campo) {
  return arr.map(item => Number(item[campo] ?? 0));
}

// ---------- Função para criar os três gráficos padrão para uma aba ----------
function criarGraficos(prefix, labels, tabela) {
  // Dados
  const chuvaAtual = extrairCampo(tabela, "chuva");
  const chuvaMesAtual = extrairCampo(tabela, "chuvaAcumuladaNoMes");
  const chuvaMediaHistorica = extrairCampo(tabela, "chuvaMediaHistoricaAtual");

  const vazaoNatural = extrairCampo(tabela, "vazaoNatural");
  const vazaoNaturalNoMes = extrairCampo(tabela, "vazaoNaturalNoMes");
  const vazaoNaturalMediaHistorica = extrairCampo(tabela, "vazaoNaturalMediaHistorica");

  // Vazões e chuvas - gráfico combinado
  const ctx1 = document.getElementById("grafVazaoChuva" + prefix);
  if (ctx1) {
    new Chart(ctx1, {
      data: {
        labels: labels,
        datasets: [
          {
            label: "Vazão do Dia (m³/s)",
            type: "bar",
            data: vazaoNatural,
            backgroundColor: "#da6314cf",
            borderWidth: 2,
            yAxisID: 'y2'
          },
          {
            label: "Vazão do Mês (m³/s)",
            type: "bar",
            data: vazaoNaturalNoMes,
            backgroundColor: "rgba(255, 159, 64, 0.7)",
            borderWidth: 2,
            yAxisID: 'y2'
          },
          {
            label: "Vazão Média Histórica (m³/s)",
            type: "bar",
            data: vazaoNaturalMediaHistorica,
            backgroundColor: "rgba(35, 106, 199, 1)",
            borderWidth: 2,
            yAxisID: 'y2'
          }
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { position: "top" },
          title: { display: true, text: "Vazões - " + (prefix === "" ? "Hoje" : (prefix === "7" ? "Últimos 7 dias (média)" : "Últimos 14 dias (média)")) }
        },
        scales: {
          y1: { display: false },
          y2: {
            type: 'linear',
            position: 'left',
            title: { display: true, text: "Vazão (m³/s)" },
            beginAtZero: true
          }
        }
      }
    });
  }

  // Gráfico de chuvas
  const ctx2 = document.getElementById("grafChuva" + prefix);
  if (ctx2) {
    new Chart(ctx2, {
      data: {
        labels: labels,
        datasets: [
          {
            label: "Chuva (mm)",
            type: "bar",
            data: chuvaAtual,
            backgroundColor: "#03c0fab3",
            borderWidth: 2,
            yAxisID: 'y1'
          },
          {
            label: "Chuva acum. do mês (mm)",
            type: "bar",
            data: chuvaMesAtual,
            backgroundColor: "#0e59e4ff",
            borderWidth: 2,
            yAxisID: 'y1'
          },
          {
            label: "Chuva Média Histórica (mm)",
            type: "bar",
            data: chuvaMediaHistorica,
            backgroundColor: "#0082fc99",
            borderWidth: 2,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: "top" }, title: { display: true, text: "Chuvas - " + (prefix === "" ? "Hoje" : (prefix === "7" ? "Últimos 7 dias (média)" : "Últimos 14 dias (média)")) } },
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
  }

  // Gráfico empilhado de vazões
  const ctx3 = document.getElementById("grafBarraEmpilhada" + prefix);
  if (ctx3) {
    new Chart(ctx3, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
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
          }
        ]
      },
      options: {
        responsive: true,
        plugins: { legend: { position: "top" }, title: { display: true, text: "Vazões Empilhadas - " + (prefix === "" ? "Hoje" : (prefix === "7" ? "Últimos 7 dias (média)" : "Últimos 14 dias (média)")) } },
        scales: {
          y: {
            beginAtZero: true,
            title: { display: true, text: "Vazão (m³/s)" },
            stacked: true,
          },
        },
      },
    });
  }
}

// Criar gráficos para cada aba
criarGraficos("", labelsHoje, tabelaHoje);
criarGraficos("7", labels7, tabela7);
criarGraficos("14", labels14, tabela14);

// Opcional: quando a aba for ativada, podemos forçar redraw (algumas vezes Chart.js precisa)
const tabEl = document.querySelectorAll('button[data-bs-toggle="tab"]');
tabEl.forEach(button => {
  button.addEventListener('shown.bs.tab', function (event) {
    // Forçar resize/redraw de todos os charts (caso necessário)
    if (window.Chart) {
      window.Chart.helpers.each(window.Chart.instances, function(chart) {
        try { chart.resize(); } catch(e) {}
      });
    }
  });
});
</script>

</body>
</html>
