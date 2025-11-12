<!--?php
// Vazões 
date_default_timezone_set('America/Sao_Paulo');

// CONFIGURAÇÕES
if (!empty($_GET['date'])) {
    try {
        $data_base = new DateTime($_GET['date']);
    } catch (Exception $e) {
        $data_base = new DateTime('now');
    }
} else {
    $data_base = new DateTime('now');
}

$ano_ref = 2021;

// IDs dos sistemas principais
$ids_sistemas = [
    "Cantareira"   => 64,
    "Alto Tietê"   => 65,
    "Guarapiranga" => 66,
    "Rio Grande"   => 67,
    "Rio Claro"    => 68,
    "Cotia"        => 69,
    "São Lourenço" => 72,
     "SIM" => 75
];


// FUNÇÃO PARA OBTER DADOS API
function get_dados_vazoes_data(DateTime $data) {
    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/" . $data->format('Y-m-d');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "PHP script");
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($resp === false || $http !== 200) return null;
    $json = json_decode($resp, true);
    return $json['data'] ?? null;
}

// COLETAR VAZÕES DOS ÚLTIMOS 7 DIAS
$dias = [];
for ($i = 0; $i < 7; $i++) {
    $d = clone $data_base;
    $d->modify("-{$i} days");
    $dias[] = $d;
}

$vazoes_sistemas = [];
foreach ($ids_sistemas as $nome => $id) $vazoes_sistemas[$nome] = [];

foreach ($dias as $d) {
    $dados = get_dados_vazoes_data($d);
    if (!$dados) continue;
    foreach ($dados as $s) {
        foreach ($ids_sistemas as $nome => $id_sis) {
            if (isset($s['idSistema']) && $s['idSistema'] == $id_sis && isset($s['vazaoNatural'])) {
                $vazoes_sistemas[$nome][] = floatval($s['vazaoNatural']);
            }
        }
    }
}

// DADOS ATUAIS E REFERÊNCIA
$dados_base = get_dados_vazoes_data($data_base);
$ultimo_dia_mes_ref = (int) date('t', strtotime("{$ano_ref}-{$data_base->format('m')}-01"));
$data_ref = new DateTime("{$ano_ref}-{$data_base->format('m')}-{$ultimo_dia_mes_ref}");
$dados_ref = get_dados_vazoes_data($data_ref);

// MONTAR LINHAS (incluindo SIM como média dos sistemas)
$linhas = [];
foreach ($ids_sistemas as $nome => $id_sis) {
    $vazao_dia = $dados_base ? ($dados_base[array_search($id_sis, array_column($dados_base, 'idSistema'))]['vazaoNatural'] ?? null) : null;
    $vazao_7d = !empty($vazoes_sistemas[$nome]) ? array_sum($vazoes_sistemas[$nome])/count($vazoes_sistemas[$nome]) : null;

    $vazao_mes = null; $vazao_mes_ref = null; $vazao_media_hist = null;
    if ($dados_base) {
        foreach ($dados_base as $s) if ($s['idSistema']==$id_sis) { $vazao_mes = $s['vazaoNaturalNoMes'] ?? null; $vazao_media_hist = $s['vazaoNaturalMediaHistorica'] ?? null; break; }
    }
    if ($dados_ref) foreach ($dados_ref as $s) if ($s['idSistema']==$id_sis) { $vazao_mes_ref = $s['vazaoNaturalNoMes'] ?? null; break; }

    $linhas[] = [
        "Sistema"=>$nome,
        "Vazão do dia (m³/s)"=>$vazao_dia,
        "Vazão últimos 7 dias (m³/s)"=>$vazao_7d,
        "Vazão no mês ".$data_base->format('Y')." (m³/s)"=>$vazao_mes,
        "Vazão no mês ".$ano_ref." (m³/s)"=>$vazao_mes_ref,
        "Vazão média climatológica (m³/s)"=>$vazao_media_hist
    ];
}

// LINHA SIM = média de todos os sistemas
$vazoes_hoje = array_column($linhas,'Vazão do dia (m³/s)');
$vazoes_7d   = array_column($linhas,'Vazão últimos 7 dias (m³/s)');
$vazoes_mes  = array_column($linhas,"Vazão no mês ".$data_base->format('Y')." (m³/s)");
$vazoes_mes_ref = array_column($linhas,"Vazão no mês ".$ano_ref." (m³/s)");
$vazoes_media_clima = array_column($linhas,"Vazão média climatológica (m³/s)");

$linhas[] = [
    "Sistema"=>"SIM",
    "Vazão do dia (m³/s)"=>array_sum($vazoes_hoje)/count($vazoes_hoje),
    "Vazão últimos 7 dias (m³/s)"=>array_sum($vazoes_7d)/count($vazoes_7d),
    "Vazão no mês ".$data_base->format('Y')." (m³/s)"=>array_sum($vazoes_mes)/count($vazoes_mes),
    "Vazão no mês ".$ano_ref." (m³/s)"=>array_sum($vazoes_mes_ref)/count($vazoes_mes_ref),
    "Vazão média climatológica (m³/s)"=>array_sum($vazoes_media_clima)/count($vazoes_media_clima),
];

// CALCULAR EXCEDENTES
$df_resultado = [];
foreach ($linhas as $r) {
    $df_resultado[] = [
        "Sistema"=>$r["Sistema"],
        "Vazao_dia"=>floatval($r["Vazão do dia (m³/s)"]),
        "Vazao_7d"=>floatval($r["Vazão últimos 7 dias (m³/s)"]),
        "Vazao_mes"=>floatval($r["Vazão no mês ".$data_base->format('Y')." (m³/s)"]),
        "Vazao_mes_ref"=>floatval($r["Vazão no mês ".$ano_ref." (m³/s)"]),
        "Vazao_media_clima"=>floatval($r["Vazão média climatológica (m³/s)"]),
        "Excedente_7d"=>max(floatval($r["Vazão últimos 7 dias (m³/s)"]) - floatval($r["Vazão do dia (m³/s)"]),0),
        "Excedente_clima"=>max(floatval($r["Vazão média climatológica (m³/s)"]) - floatval($r["Vazão no mês ".$ano_ref." (m³/s)"]),0)
    ];
}

$json_dados = json_encode($df_resultado, JSON_UNESCAPED_UNICODE);
?-->

<?php
header("Content-Type: application/json; charset=utf-8");
date_default_timezone_set('America/Sao_Paulo');

// ------------------------------
// CONFIGURAÇÕES
// ------------------------------
$dataBase = new DateTime('2025-10-24'); // data de interesse
$anoRef = 2021;
$idsSistemas = [
    "Cantareira"   => 64,
    "Alto Tietê"    => 65,
    "Guarapiranga"  => 66,
    "Rio Grande"    => 67,
    "Rio Claro"     => 68,
    "Cotia"         => 69,
    "São Lourenço"  => 72,
];
$idSIM = 75;

// ------------------------------
// Função: busca dados de um dia
// ------------------------------
function getDadosAPI(DateTime $data) {
    $url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/" . $data->format("Y-m-d");
    $json = @file_get_contents($url);
    if (!$json) return [];
    $data = json_decode($json, true);
    return $data["data"] ?? [];
}

// ------------------------------
// Função auxiliar para extrair campo
// ------------------------------
function obterCampo($dados, $idSis, $campo) {
    foreach ($dados as $item) {
        if ($item["idSistema"] == $idSis) return $item[$campo] ?? null;
    }
    return null;
}

// ------------------------------
// Coleta últimos 7 dias
// ------------------------------
$dias = [];
for ($i = 0; $i < 7; $i++) {
    $d = clone $dataBase;
    $d->modify("-$i day");
    $dias[] = $d;
}

$vazoes = [];
foreach ($idsSistemas as $nome => $id) $vazoes[$nome] = [];
$vazoes["SIM"] = [];

foreach ($dias as $d) {
    $dadosDia = getDadosAPI($d);
    $mapa = [];
    foreach ($dadosDia as $item) $mapa[$item["idSistema"]] = $item;

    foreach ($idsSistemas as $nome => $id) {
        $vazoes[$nome][] = $mapa[$id]["vazaoNatural"] ?? null;
    }
    $vazoes["SIM"][] = $mapa[$idSIM]["vazaoNatural"] ?? null;
}

// ------------------------------
// Dados de referência
// ------------------------------
$dadosBase = getDadosAPI($dataBase);
$ultimoDiaMesRef = cal_days_in_month(CAL_GREGORIAN, $dataBase->format('m'), $anoRef);
$dataRef = new DateTime("$anoRef-" . $dataBase->format('m') . "-$ultimoDiaMesRef");
$dadosRef = getDadosAPI($dataRef);

// ------------------------------
// Monta tabela
// ------------------------------
$resultado = [];

foreach ($idsSistemas as $nome => $idSis) {
    $vals7 = array_filter($vazoes[$nome], fn($v) => $v !== null);
    $vazao7 = count($vals7) ? array_sum($vals7) / count($vals7) : null;

    $resultado[] = [
        "Sistema" => $nome,
        "VazaoDia" => obterCampo($dadosBase, $idSis, "vazaoNatural"),
        "Vazao7d" => $vazao7,
        "VazaoMes" => obterCampo($dadosBase, $idSis, "vazaoNaturalNoMes"),
        "VazaoMesRef" => obterCampo($dadosRef, $idSis, "vazaoNaturalNoMes"),
        "VazaoClima" => obterCampo($dadosBase, $idSis, "vazaoNaturalMediaHistorica"),
    ];
}

// Adiciona SIM
$vals7SIM = array_filter($vazoes["SIM"], fn($v) => $v !== null);
$vazao7SIM = count($vals7SIM) ? array_sum($vals7SIM) / count($vals7SIM) : null;

$resultado[] = [
    "Sistema" => "SIM",
    "VazaoDia" => obterCampo($dadosBase, $idSIM, "vazaoNatural"),
    "Vazao7d" => $vazao7SIM,
    "VazaoMes" => obterCampo($dadosBase, $idSIM, "vazaoNaturalNoMes"),
    "VazaoMesRef" => obterCampo($dadosRef, $idSIM, "vazaoNaturalNoMes"),
    "VazaoClima" => obterCampo($dadosBase, $idSIM, "vazaoNaturalMediaHistorica"),
];

// ------------------------------
// Retorna JSON
// ------------------------------
// echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>