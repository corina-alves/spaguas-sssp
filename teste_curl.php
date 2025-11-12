<?php
echo "PHP version: " . phpversion() . "\n";
echo "curl loaded? ";
var_export(extension_loaded('curl'));
echo "\n\n";

// teste de requisição simples (exemplo)
if (extension_loaded('curl')) {
    $ch = curl_init("https://www.google.com/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP code: $http\n";
    if ($res === false) {
        echo "Erro cURL: $err\n";
    } else {
        echo "Requisição OK — tamanho da resposta: " . strlen($res) . " bytes\n";
    }
} else {
    echo "cURL não está disponível no PHP.\n";
}
