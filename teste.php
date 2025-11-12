<!--?php
$url = "https://mananciais.sabesp.com.br/api/Man";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ignora erro de SSL
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$resposta = curl_exec($ch);

if ($resposta === false) {
    echo "Erro cURL: " . curl_error($ch);
} else {
    $dados = json_decode($resposta, true);
    print_r($dados);
}

curl_close($ch);
?-->
<?php
// proxy.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// Verifica se a URL foi passada
if (!isset($_GET["url"])) {
    echo json_encode(["erro" => "https://mananciais.sabesp.com.br/api/Mananciais/Boletins/Mananciais/2025-09-12"]);
    exit;
}

$url = $_GET["url"];

// Inicializa cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ⚠️ só para testes locais
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Executa
$resposta = curl_exec($ch);

// Se deu erro
if ($resposta === false) {
    echo json_encode(["erro" => curl_error($ch)]);
} else {
    echo $resposta;
}

curl_close($ch);
?>
