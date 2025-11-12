<?php
// proxy.php - simples proxy para evitar CORS (use localmente no XAMPP)
header('Content-Type: application/json; charset=utf-8');

// URL da API remota (troque se quiser outra)
$remote = 'https://cors-anywhere.herokuapp.com/https://cth.daee.sp.gov.br/sibh/api/v2/measurements/now?station_type_id=2&hours=24&show_all=true&serializer=complete&public=true';

// inicia cURL
$ch = curl_init($remote);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// opcional: se precisar de headers extras, adicione aqui
// curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro ao buscar a API remota', 'detail' => $err]);
    exit;
}

// repassa o mesmo código HTTP da API remota (ou 200)
http_response_code($code ?: 200);
echo $response;
