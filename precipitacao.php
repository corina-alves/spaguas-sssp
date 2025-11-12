<?php
// chuva_sistemas.php (corrigido)
// Requer PHP 7+

date_default_timezone_set('America/Sao_Paulo');

// ---------------------------
// CONFIGURAÇÕES
// ---------------------------
// Data base: aceita ?date=YYYY-MM-DD ou usa hoje
if (!empty($_GET['date'])) {
    $d_input = $_GET['date'];
    try {
        $data_base = new DateTime($d_input);
    } catch (Exception $e) {
        // se parse falhar, usa hoje
        error_log("Data inválida recebida: {$d_input}. Usando hoje.");
        $data_base = new DateTime('now');
    }
} else {
    $data_base = new DateTime('now');
}

$ano_ref = 2021;
$ids_sistemas = [
    "Cantareira"   => 64,
    "Alto Tietê"   => 65,
    "Guarapiranga" => 66,
    "Cotia"        => 67,
    "Rio Grande"   => 68,
    "Rio Claro"    => 69,
    "São Lourenço" => 72,
     //"SIM" => 75,
    // removi "SIM" do map porque SIM é média dos sistemas (se quiser incluir, trate separadamente)
];

// FUNÇÃO PARA OBTER DADOS SABESP
function get_dados_chuva_data(DateTime $data) {
    // include_once "./config/config_prox.php"; 
    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/" . $data->format('Y-m-d');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "PHP script");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      // === CONFIGURAÇÃO DE PROXY ===
    // curl_setopt($ch, CURLOPT_PROXY, "10.200.12.140:80"); // endereço e porta do proxy
    // Se o proxy exigir autenticação, adicione:
    // curl_setopt($ch, CURLOPT_PROXYUSERPWD, "usuario:senha");

    // Se estiver com problema de SSL interno, pode desabilitar (somente para testes locais):
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // Se precisar ignorar SSL (apenas em dev): curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    if ($resp === false || $http !== 200) {
        error_log("Erro ao acessar {$data->format('Y-m-d')}: HTTP {$http} - {$err}");
        return null;
    }
    $json = json_decode($resp, true);
    return $json['data'] ?? null;
}

// COLETAR CHUVAS DOS ÚLTIMOS 7 DIAS
$dias = [];
for ($i = 0; $i < 7; $i++) {
    $d = clone $data_base;
    $d->modify("-{$i} days");
    $dias[] = $d;
}

$chuvas_sistemas = [];
foreach ($ids_sistemas as $nome => $id) $chuvas_sistemas[$nome] = [];

foreach ($dias as $d) {
    $chuva = get_dados_chuva_data($d);
    if (!$chuva) continue;
    foreach ($chuva as $s) {
        foreach ($ids_sistemas as $nome => $id_sis) {
            if (isset($s['idSistema']) && $s['idSistema'] == $id_sis && isset($s['chuva']) && $s['chuva'] !== null) {
                $chuvas_sistemas[$nome][] = floatval($s['chuva']);
            }
        }
    }
}

// DADOS ATUAIS E REFERÊNCIA
$dados_base = get_dados_chuva_data($data_base);

// último dia do mesmo mês em ano_ref
$ultimo_dia_mes_ref = (int) date('t', strtotime($ano_ref . '-' . $data_base->format('m') . '-01'));
$data_ref = new DateTime("{$ano_ref}-{$data_base->format('m')}-{$ultimo_dia_mes_ref}");
$dados_ref = get_dados_chuva_data($data_ref);

// MONTAR LINHAS (chaves consistentes)
$linhas = [];
foreach ($ids_sistemas as $nome => $id_sis) {
    $chuva_dia = count($chuvas_sistemas[$nome]) > 0 ? $chuvas_sistemas[$nome][0] : null;
    $chuva_7d = count($chuvas_sistemas[$nome]) > 0 ? array_sum($chuvas_sistemas[$nome]) : null;

    $dados_sis_base = null;
    if (is_array($dados_base)) {
        foreach ($dados_base as $s) if (isset($s['idSistema']) && $s['idSistema'] == $id_sis) { $dados_sis_base = $s; break; }
    }
    $chuva_mes = $dados_sis_base['chuvaAcumuladaNoMes'] ?? null;
    $chuva_media_climatologica = $dados_sis_base['chuvaMediaHistorica'] ?? null;

    $dados_sis_ref = null;
    if (is_array($dados_ref)) {
        foreach ($dados_ref as $s) if (isset($s['idSistema']) && $s['idSistema'] == $id_sis) { $dados_sis_ref = $s; break; }
    }
    $chuva_mes_ref = $dados_sis_ref['chuvaAcumuladaNoMes'] ?? null;

    $linhas[] = [
        "Sistema" => $nome,
        "Chuva do dia (mm)" => $chuva_dia,
        "Chuva últimos 7 dias (mm)" => $chuva_7d,
        "Chuva acumulada mês " . $data_base->format('Y') . " (mm)" => $chuva_mes,
        "Chuva acumulada mês " . $ano_ref . " (mm)" => $chuva_mes_ref,
        "Chuva média climatológica (mm)" => $chuva_media_climatologica
    ];
}

// === 4. ADICIONA LINHA MÉDIA (SISTEMA SIM) ===
$totalSistemas = 0;
$somaCampos = [
    "chuva_dia" => 0,
    "chuva_7d" => 0,
    "chuva_mes_atual" => 0,
    "chuva_mes_ref" => 0,
    "chuva_media_climatologica" => 0
];

$anoAtual = $data_base->format('Y');

foreach ($linhas as $linha) {
    if ($linha["Sistema"] !== "SIM") {
        $totalSistemas++;
        $somaCampos["chuva_dia"] += floatval($linha["Chuva do dia (mm)"] ?? 0);
        $somaCampos["chuva_7d"] += floatval($linha["Chuva últimos 7 dias (mm)"] ?? 0);
        $somaCampos["chuva_mes_atual"] += floatval($linha["Chuva acumulada mês $anoAtual (mm)"] ?? 0);
        $somaCampos["chuva_mes_ref"] += floatval($linha["Chuva acumulada mês $ano_ref (mm)"] ?? 0);
        $somaCampos["chuva_media_climatologica"] += floatval($linha["Chuva média climatológica (mm)"] ?? 0);
    }
}

if ($totalSistemas > 0) {
    $linhaSim = [
        "Sistema" => "SIM",
        "Chuva do dia (mm)" => round($somaCampos["chuva_dia"] / $totalSistemas, 1),
        "Chuva últimos 7 dias (mm)" => round($somaCampos["chuva_7d"] / $totalSistemas, 1),
        "Chuva acumulada mês $anoAtual (mm)" => round($somaCampos["chuva_mes_atual"] / $totalSistemas, 1),
        "Chuva acumulada mês $ano_ref (mm)" => round($somaCampos["chuva_mes_ref"] / $totalSistemas, 1),
        "Chuva média climatológica (mm)" => round($somaCampos["chuva_media_climatologica"] / $totalSistemas, 1)
    ];

    $linhas[] = $linhaSim;
}

// CALCULAR EXCEDENTES E MONTAR df_resultado
$df_resultado = [];
foreach ($linhas as $r) {
    $chuva_hoje = isset($r["Chuva do dia (mm)"]) ? floatval($r["Chuva do dia (mm)"]) : 0.0;
    $chuva_7d = isset($r["Chuva últimos 7 dias (mm)"]) ? floatval($r["Chuva últimos 7 dias (mm)"]) : 0.0;
    $chuva_mes_2025 = isset($r["Chuva acumulada mês " . $data_base->format('Y') . " (mm)"]) ? floatval($r["Chuva acumulada mês " . $data_base->format('Y') . " (mm)"]) : 0.0;
    $chuva_mes_ref = isset($r["Chuva acumulada mês " . $ano_ref . " (mm)"]) ? floatval($r["Chuva acumulada mês " . $ano_ref . " (mm)"]) : 0.0;
    $chuva_media = isset($r["Chuva média climatológica (mm)"]) ? floatval($r["Chuva média climatológica (mm)"]) : 0.0;

    $df_resultado[] = [
        "Sistema" => $r["Sistema"],
        "Chuva_hoje" => $chuva_hoje,
        "Chuva_7d" => $chuva_7d,
        "Chuva_mes_2025" => $chuva_mes_2025,
        "Chuva_mes_ref" => $chuva_mes_ref,
        // "Chuva_media_clima" => $chuva_media,
        "Excedente_7dias" => max($chuva_7d - $chuva_hoje, 0),
        "chuva_media_climatologica" => max($chuva_media - $chuva_mes_ref, 0),
        "chuvaMediaHistorica" => $chuva_media

    ];
}

$json_dados = json_encode($df_resultado, JSON_UNESCAPED_UNICODE);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8" />
<title>Chuva por Sistema - 7 dias</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {font-family: Arial, Helvetica, sans-serif; padding:20px; background:#fafafa;}
table {border-collapse: collapse; width:100%; max-width:1100px; margin-bottom:30px; font-size:14px;}
th, td {border:1px solid #ddd; padding:8px; text-align:center;}
th {background:#0b6a4a; color:white;}
tr:last-child {font-weight:bold; background:#e9f9ee;}
/* === Tela de carregamento === */
#telaCarregando {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(255,255,255,0.9);
  z-index: 9999;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: #333;
  transition: opacity 0.5s ease;
}
.spinner {
  border: 6px solid #ddd;
  border-top: 6px solid #0074D9;
  border-radius: 50%;
  width: 50px; height: 50px;
  animation: spin 1s linear infinite;
  margin-bottom: 10px;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
</style>
</head>
<body>
<?php include_once "navbar.php";?>
<div class="container"> 
<div class="card">
    <div class="card-body">
<h2 style="text-align:center; margin-top:30px;">Acompanhamento de <strong>Chuva </strong>nos Sistemas Produtores (mm)</h2>
        <h4 style="text-align:center; margin-bottom:30px;"><i><?= $data_base->format('d/m/Y') ?> | Ano de referência: <?= $ano_ref ?></i></h4>
<!-- Tabela -->

<!-- <div id="telaCarregando">
  <div class="spinner"></div>
  <div>Carregando dados...</div>
</div> -->

<script>
// Seleciona todos os links e adiciona o evento
document.querySelectorAll('.link').forEach(link => {
  link.addEventListener('click', e => {
    document.getElementById('telaCarregando').style.display = 'flex';
  });
});
</script>
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <?php foreach (array_keys($linhas[0]) as $h): ?>
                <th><?= htmlspecialchars($h) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($linhas as $r): ?>
        <tr>
            <?php foreach ($r as $k => $v): ?>
                <td><?= ($k === 'Sistema') ? htmlspecialchars($v) : (is_null($v) ? '-' : number_format((float)$v, 1, ',', '.')) ?></td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Gráfico -->
<!-- <h2 style="text-align:center; margin-top:10px;">Comparativo de Chuvas por Sistema (mm)</h2> -->
  <canvas id="graficoChuvas"></canvas>
</div>
</div>
</div>
<script>
// Mostra o spinner assim que a página começa a carregar
document.addEventListener("DOMContentLoaded", () => {
  document.getElementById('telaCarregando').style.display = 'flex';
});

// Quando tudo (HTML, imagens, gráficos) terminar de carregar
window.addEventListener("load", () => {
  const tela = document.getElementById('telaCarregando');
  tela.style.opacity = '0'; // efeito de transição suave
  setTimeout(() => tela.style.display = 'none', 500); // remove após meio segundo
});
</script>

<!-- Importa o plugin (caso ainda não tenha no HTML) -->
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
const chuva = <?= $json_dados ?>;

// Montagem dos vetores a partir do JSON PHP
const sistemas = chuva.map(d => d.Sistema);
const chuvaDia = chuva.map(d => d.Chuva_hoje);
const chuva7d = chuva.map(d => d.Chuva_7d);
const chuvaMes2025 = chuva.map(d => d.Chuva_mes_2025);
const chuvaMesRef = chuva.map(d => d.Chuva_mes_ref);
const chuva_media_climatologica = chuva.map(d => d.chuvaMediaHistorica);

// Criação do gráfico
const ctx = document.getElementById('graficoChuvas').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: sistemas,
        datasets: [
            {
                label: 'Chuva do dia <?= $data_base->format('Y/m/d') ?>(mm)',
                data: chuvaDia,
                backgroundColor: '#1f77b4'
            },
            {
                label: 'Chuva em 7 dias <?= $data_base->format('Y/m') ?> (mm)',
                data: chuva7d,
                backgroundColor: '#7fb3d5'
            },
            {
                label: 'Chuva acumulada mês <?= $data_base->format('Y/m') ?> (mm)',
                data: chuvaMes2025,
                backgroundColor: '#227542ff'
            },
            {
                label: 'Chuva acumulada mês <?= $ano_ref ?> (mm)',
                data: chuvaMesRef,
                backgroundColor: '#e7bf82de'
            },
            {
                label: 'Chuva Média Hitórica  (mm)',
                data: chuva_media_climatologica,
                backgroundColor: '#fc9d10ff'
            }
        ]
    },
  options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.y} m³/s`
                }
            },
            title: {
                display: true,
                text: "Comparativo de Vazões Naturais ",
                font: { size: 16 }
                // color: "#004c91"
            }
        },
        
    plugins: {
            legend: { position: "top" },
            tooltip: {
                mode: "index",          // mostra todos os conjuntos do mesmo índice
                intersect: false,       // não precisa estar exatamente sobre a barra
                callbacks: {
                    label: function(context) {
                        // formata número com vírgula
                        return context.dataset.label + ': ' +
                            context.formattedValue.replace('.', ',') + ' m³/s';
                    }
                }
            }
        },
        interaction: {
            mode: "index",              // ativa exibição em grupo
            intersect: false
        }
    }

  
});
</script>












<!-- <canvas id="graficoChuvas" height="140"></canvas>

<script>
const dados = <?= $json_dados ?>;
const sistemas = dados.map(d => d.Sistema);
const chuvaDia = dados.map(d => d.Chuva_hoje);
const excedente7 = dados.map(d => d.Excedente_7dias);
const chuvaMes2025 = dados.map(d => d.Chuva_mes_2025);
const chuvaMesRef = dados.map(d => d.Chuva_mes_ref);
const excedenteClima = dados.map(d => d.Excedente_climatologica);

const ctx = document.getElementById('graficoChuvas').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: sistemas,
        datasets: [
            { label: 'Chuva do dia (mm)', data: chuvaDia, backgroundColor: '#1f77b4', stack: 'chuva7' },
            { label: 'Chuva em 7 dias (mm)', data: excedente7, backgroundColor: '#7fb3d5', stack: 'chuva7' },
            { label: 'Chuva acumulada mês <?= $data_base->format('Y') ?> (mm)', data: chuvaMes2025, backgroundColor: '#2ca02c' },
            { label: 'Chuva acumulada mês <?= $ano_ref ?> (mm)', data: chuvaMesRef, backgroundColor: '#ff7f0e', stack: 'clima' },
            { label: 'Média climatológica (mm)', data: excedenteClima, backgroundColor: '#ffbb78', stack: 'clima' }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            title: { display: true, text: 'Comparativo de Chuvas por Sistema (mm)', font: { size: 16, weight: 'bold' } }
        },
        scales: {
            x: { stacked: true, ticks: { maxRotation: 45, minRotation: 45 } },
            y: { beginAtZero: true, title: { display: true, text: 'Chuva (mm)' } }
        }
    }
});
</script> -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
</body>
</html>
