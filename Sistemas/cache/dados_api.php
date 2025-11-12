<?php
header("Content-Type: application/json; charset=utf-8");
date_default_timezone_set('America/Sao_Paulo');

$data = date("Y-m-d");

// URL da API real
$url = "https://mananciais.sabesp.com.br/api/v4/sistemas/dados/resumo-diario/$data";
$cacheFile = __DIR__ . "/cache_api.json";
$cacheTime = 300; // 5 minutos (em segundos)

// Verifica se já existe cache válido
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    echo file_get_contents($cacheFile);
    exit;
}

// Busca da API real
$dados = file_get_contents($url);

// Se retornou algo, salva no cache
if ($dados) {
    file_put_contents($cacheFile, $dados);
    echo $dados;
} else {
    // Em caso de erro, tenta usar o cache antigo
    if (file_exists($cacheFile)) {
        echo file_get_contents($cacheFile);
    } else {
        echo json_encode(["erro" => "Falha ao acessar API"]);
    }
}
