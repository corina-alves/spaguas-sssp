<?php
// ======================================
// proxy_config.php
// Configuração padrão de proxy para uso em cURL
// ======================================

function configurarProxy($ch) {
    // Endereço e porta do proxy (ajuste conforme seu ambiente)
    curl_setopt($ch, CURLOPT_PROXY, "10.200.12.140:80");

    // Se o proxy precisar de autenticação, descomente e preencha:
    // curl_setopt($ch, CURLOPT_PROXYUSERPWD, "usuario:senha");

    // Opcional: ignorar SSL (apenas para testes internos)
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // User-Agent padrão
    curl_setopt($ch, CURLOPT_USERAGENT, "PHP Proxy Config");
}
?>
