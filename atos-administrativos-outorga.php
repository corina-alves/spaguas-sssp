<?php include "conexao.php"; ?>

<?php
$por_pagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina - 1) * $por_pagina;

$busca = isset($_GET['busca']) ? trim($conn->real_escape_string($_GET['busca'])) : '';

$where = '';
// Verifica se a busca é uma data no formato dd/mm/yyyy
if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $busca)) {
    // Converte para yyyy-mm-dd
    $partes = explode('/', $busca);
    $data_convertida = $partes[2] . '-' . $partes[1] . '-' . $partes[0];
    $where = "WHERE data_upload = '$data_convertida'";
} elseif (!empty($busca)) {
    // Busca por nome do arquivo
    $where = "WHERE nome LIKE '%$busca%' OR arquivo LIKE '%$busca%'";
}

// Total de registros com ou sem filtro
$total_query = $conn->query("SELECT COUNT(*) as total FROM up_boletim_hidro $where");
$total_result = $total_query->fetch_assoc();
$total = $total_result['total'];
$total_paginas = ceil($total / $por_pagina);

// Consulta paginada
$sql = "SELECT * FROM up_boletim_hidro $where ORDER BY data_upload DESC LIMIT $inicio, $por_pagina";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Outorga</title>
  <meta content="" name="description"  content="Outorgas de Direito de Uso de Recursos Hídricos - Atos Administrativos"/>

  <link href="assets/img/logo/logo.png" rel="icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/animations.min.css" integrity="sha512-GKHaATMc7acW6/GDGVyBhKV3rST+5rMjokVip0uTikmZHhdqFWC7fGBaq6+lf+DOS5BIO8eK6NcyBYUBCHUBXA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.3.0/css/glightbox.min.css" integrity="sha512-T+KoG3fbDoSnlgEXFQqwcTC9AdkFIxhBlmoaFqYaIjq2ShhNwNao9AKaLUPMfwiBPL0ScxAtc+UYbHAgvd+sjQ==" crossorigin="anonymous" referrerpolicy="no-referrer" /> 
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.css">
  <link href="assets/css/style.css" rel="stylesheet">
  <!-- <link href="assets/css/sobre.css" rel="stylesheet"> -->
  <script>
    
    window.addEventListener("scroll", function() {
        var menu = document.getElementById("header");
        if (window.scrollY > 50) {
            menu.classList.add("scrolled");
        } else {
            menu.classList.remove("scrolled");
        }
    });
    
    </script>
</head>
<body>
 <?php include "menuspgov.php";?>
  <header id="header" class="header  sticky-top"><?php include "nav.php";?></header>
    <section class="top-hero-boletins" style="margin-bottom: 20px;">
        <img class="img-fluid" src="assets/img/posts/water.JPG">
          <div class="container">
            <div class="row">
       <div class="col-lg-6 offset-lg-6" data-aos="fade-right">
          <h1><strong>Outorgas</strong></h1>
          <p>Atos Administrativos de Outorga de Direito de Uso de Recursos Hídricos</p>
        </div>
  
        </div>
        </div>
    </section> 
   <div class="container">
        <div class="accordion" id="accordionExample">
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#cantareira" aria-expanded="true" aria-controls="cantareira">
       <strong> Sistema Cantareira</strong>
      </button>
    </h2>
    <div id="cantareira" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
      <div class="accordion-body">
        <p><a href="outorga/SistemaCantareira/RES_ANA_1931-2017.pdf"><strong> RESOLUÇÃO Nº 1.931, DE 30 DE OUTUBRO DE 2017 Documento nº 00000.071669/2017-32</strong> </a>O DIRETOR-PRESIDENTE DA AGÊNCIA DE ÁGUAS-ANA, no uso da
atribuição que lhe confere o Artigo 103, inciso XVII, do Regimento Interno aprovado pela
Resolução n° 828, de 15 de maio de 2017, </p>
       <p> <a href="outorga/SistemaCantareira/DU.Port.Daee_4563.17_Sabesp_17.12.11.pdf"><strong> Portaria DAEE n° 4.563, de 11 de Dezembro de 2017 </strong> </a> Artigo 1° - Fica outorgada, pelo prazo de 10 anos, em nome da Companhia de Saneamento Básico do Estado de São Paulo - SABESP</p>
      <p>  <a href="outorga/SistemaCantareira/Resolucao_Conjunta_ANA_DAEE_No_925_de_29-05-2017.pdf"><strong> RESOLUÇÃO CONJUNTA ANA/DAEE Nº 925, DE 29 DE MAIO DE 2017
Documento nº 00000.031749/2017-55</strong> </a> Dispõe sobre as condições de operação para o Sistema
Cantareira - SC, delimitado, para os fins desta Resolução,
como o conjunto dos reservatórios Jaguari-Jacareí, Cachoeira,
Atibainha e Paiva Castro</p>
      <p>  <a href="outorga/SistemaCantareira/Resolucao_Conjunta_ANA_DAEE_No_926_de_29-05-2017.pdf"><strong> RESOLUÇÃO CONJUNTA ANA/DAEE No 926, DE 29 DE MAIO DE 2017
Documento nº 00000.031750/2017-80</strong> </a> O DIRETOR-PRESIDENTE DA AGÊNCIA NACIONAL DE ÁGUAS – ANA,
no uso da atribuição que lhe confere o art. 103, inciso IV e XIII, do Regimento Interno, aprovado
pela Resolução nº 828, de 15 de maio de 2017,</p>
    </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#spat" aria-expanded="false" aria-controls="spat">
      <strong> Alto Tietê </strong>
      </button>
    </h2>
    <div id="spat" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
      <div class="accordion-body">
       <p> <a href="outorga/AltoTiete/Portaria733_SPAT.pdf"><strong>PORTARIA DAEE N° 733, DE 9 DE FEVEREIRO DE 2024 </strong> </a>A SUPERINTENDENTE DO DEPARTAMENTO DE ÁGUAS E ENERGIA ELÉTRICA, no uso de suas atribuições legais e com
fundamento no artigo 11, incisos I e XVI do Decreto nº 52.636 de 03/02/71, e à vista do Código de Águas, da Lei nº 6.134 de 02/06/88,
do Decreto nº 32.955 de 07/02/91, da Lei nº 7.663 de 30/12/91, do Decreto nº 63.262 de 09/03/18 e da Portaria DAEE nº 1.630 de
30/05/17 reti-ratificada em 24/06/2020, e tendo em vista as declarações e informações constantes do(s) requerimento(s) e parecer técnico,
contido(s) no Processo DAEE nº 9916321.</p>
      </div>
    </div>
  </div>

  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#guarapiranga" aria-expanded="false" aria-controls="guarapiranga">
       <strong> Guarapiranga </strong>
      </button>
    </h2>
    <div id="guarapiranga" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
      <div class="accordion-body">
       <p> <a href="outorga/Guarapiranga/Portaria 515_04-02-2022_ReversãoCapivariReservatorioGuarapiranga.pdf"><strong> Captação Superficial (Reversão de Bacia) - Rio Capivari (Reservatório Capivari) </strong> </a> Fica outorgada, em nome de COMPANHIA DE SANEAMENTO BÁSICO DO ESTADO DE SÃO PAULO - SABESP, CPF/CNPJ 43.776.517/0001-80, a concessão administrativa para o(s) uso(s) em recursos hídricos superficiais, para fins urbano, no município de São Paulo, conforme abaixo identificado:</p>
        <p> <a href="outorga/Guarapiranga/Portaria 2139_07-04-2021ReversãoTaquacetubaReservatorioGuarapiranga.pdf"><strong>Portaria do Superintendente, de 6-4-2021 </strong> </a> Com fundamento no artigo 11, incisos I e XVI do Decreto 52.636 de 03/02/71, e à vista do Código de Águas, da Lei 6.134 de 02/06/88, do Decreto 32.955 de 07/02/91, da Lei 7.663 de 30/12/91, do Decreto 63.262 de 09/03/18 e da Portaria DAEE 1630 de 30/05/17.</p>
       <p> <a href="outorga/Guarapiranga/Portaria4409_15-07-2021RetifPort2139_ReversãoTaquacetubaReservatorioGuarapiranga.pdf"><strong> Portaria DAEE - 4409, de 14-7-2021
</strong> </a> O Superintendente do Departamento de Águas e Energia Elétrica, com fundamento no Artigo 11, incisos I e XVI, do Decreto 52.636, de 03-02-71 e à vista do Código de Águas, da Lei 6.134, de 02-06-88, do Decreto 32.955 de 07-02-91, da Lei 7.663 de 30-12-91, do Decreto 63.262 de 09-03-18 e da Portaria DAEE 1.630 de 30-05-17, reti-ratificada em 24-06-2020, e</p>
    </div>
    </div>
  </div>


    <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cotia" aria-expanded="false" aria-controls="cotia">
       <strong> Cotia</strong>
      </button>
    </h2>
    <div id="cotia" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
      <div class="accordion-body">
       <p> <a href="outorga/Cotia/Portaria1176_03-03-2022__CapataçãoETAAltoCotia.pdf"><strong> Portaria do Superintendente do DAEE de 02/03/2022.</strong> </a> Com fundamento no artigo 11, incisos I e XVI do Decreto n. 52.636 de 03/02/71, e à vista do Código de Águas, da Lei 6.134 de 02/06/88, do Decreto n. 32.955 de 07/02/91, da Lei 7.663 de 30/12/91, do Decreto 63.262 de 09/03/18 e da Portaria DAEE n. 1630 de 30/05/17, reti-ratificada em 24/06/2020.</p>
       <p> <a href="outorga/Cotia/Portaria3975_22-12-2016_CaptaçãoETABaixoCotia.pdf"><strong>Portaria do Superintendente, de 21-12-2016 </strong> </a> Com fundamento no artigo 11, incisos I e XVI do Decreto n.52.636 de 03/02/71, e à vista do Código de Águas, da Lei 6.134 de 02/06/88, do Decreto n.32.955 de 07/02/91, da Lei 7.663 de 30/12/91, do Decreto 41.258 de 31/10/96 e da Portaria D.A.EE n.717 de 12/12/96</p>

    </div>
    </div>
  </div>



    <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rioGrande" aria-expanded="false" aria-controls="rioGrande">
      <strong>  Rio Grande </strong>
      </button>
    </h2>
    <div id="rioGrande" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
      <div class="accordion-body">
       <p> <a href="outorga/RioGrande/Portaria2920_18-06-2020_Reversão_Rio Grande_Taiaçupeba_Rio Pequeno_Rio Grande.pdf"><strong> Portaria do Superintendente, de 16-06-2020.</strong> </a> Com fundamento no artigo 11, incisos I e XVI do Decreto 52.636 de 03-02-1971, e à vista do Código de Águas, da Lei 6.134 de 02-06-1988, do Decreto 32.955 de 07-02-1991, da Lei 7.663 de 30-12-1991, do Decreto 63262 de 09-03-2018 e da Portaria DAEE 1630 de 30-05-2017.</p>
       <p> <a href="outorga/RioGrande/Portaria 3373_27-06-2018_Captação ETA Ribeirão da Estiva.pdf"><strong> Portaria do Superintendente do DAEE de 26-06-2018</strong> </a> Com fundamento no artigo 11, incisos I e XVI do Decreto n.52.636 de 03/02/71, e à vista do Código de Águas, da Lei 6.134 de 02/06/88, do Decreto n.32.955 de 07/02/91, da Lei 7.663 de 30/12/91, do Decreto 63.262 de 09/03/18 e da Portaria D.A.EE 1630 de 30/05/17.</p>
       <p> <a href="outorga/RioGrande/Portaria2443_03-08-2017_CaptaçãoETARioGrande.pdf"><strong> Portaria do Superintendente, de 2-8-2017</strong> </a> Com fundamento no artigo 11, incisos I e XVI do Decreto n.52.636 de 03/02/71, e à vista do Código de Águas, da Lei 6.134 de 02/06/88, do Decreto n.32.955 de 07/02/91, da Lei 7.663 de 30/12/91, do Decreto 41.258 de 31/10/96 e da Portaria D.A.EE n.717 de 12/12/96</p>

      </div>
    </div>
  </div>



     <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rioClaro" aria-expanded="false" aria-controls="rioClaro">
      <strong>  Rio Claro</strong>
      </button>
    </h2>
    <div id="rioClaro" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
      <div class="accordion-body">
       <p> <a href="outorga/RioClaro/Portaria279218-11-2023_4CA_2LA_6BASistemaRioClaro.pdf"><strong> Portaria DAEE - 2792, de 17/11/2014</strong> </a> Despacho do Superintendente, de 17/11/14 Com fundamento no artigo 11, incisos I e XVI do Decreto n.52.636 de 03/02/71, e à vista do Código de Águas, da Lei 6.134 de 02/06/88, do Decreto n.32.955 de 07/02/91, da Lei 7.663 de 30/12/91, do Decreto 41.258 de 31/10/96 e da Portaria D.A.EE n.717 de 12/12/96,</p>
       <p> <a href="outorga/RioClaro/Portaria2518_11-08-2015_ReversãoGuaratuvaparaRioClaro.pdf"><strong> Autos DAEE 9900444, Prov. 09 - Extrato de Portaria 2518/15.</strong> </a> Fica outorgada à Companhia de Saneamento Básico do Estado de São Paulo - Sabesp, CNPJ 43.776.517/0001-80, concessão/autorização administrativa para utilizar e interferir em recursos hídricos</p>

    </div>
    </div>
  </div>



    <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#loureco" aria-expanded="false" aria-controls="loureco">
   <strong>    São Lourenço</strong>
      </button>
    </h2>
    <div id="loureco" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
      <div class="accordion-body">
       <p> <a href="outorga/SaoLourenco/Portaria3062_17.09.21_CaptaçãoSãoLourenço.pdf"><strong>PORTARIA DAEE N° 3.062. DE 21 DE SETEMBRO DE 2017</strong> </a>Artigo 1° - Fica outorgada, em nome da COMANHIA DE SANEAMENTO BÁSICO DO ESTADO DE SÃO PAULO - SABESP, CNPJ n° 43.776.5170001-80...</p>
      </div>
    </div>
  </div>
</div> 





</div>
    </div>
    <!-- ======= Footer ======= -->
  <footer id="footer" class="" style="margin-top: 10px;">
  <?php include "footer.php";?>
  </footer>
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js" integrity="sha512-BwHfrr4c9kmRkLw6iXFdzcdWV/PGkVgiIyIWLLlTSXzWQzxuSg4DiQUCpauz/EWjgk5TYQqX/kvn9pG1NpYfqg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js" integrity="sha512-A7AYk1fGKX6S2SsHywmPkrnzTZHrgiVT7GcQkLGDe2ev0aWb8zejytzS8wjo7PGEXKqJOrjQ4oORtnimIRZBtw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/dist/boxicons.min.js" integrity="sha512-VTptlAlSWKaYE3DbrmwNYTzZg1zO6CtoGxplxlHxObgfLiCcRYDBqzTUWE/0ANUmyfYR7R227ZirV/I4rQsPNA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.3.0/js/glightbox.min.js" integrity="sha512-RBWI5Qf647bcVhqbEnRoL4KuUT+Liz+oG5jtF+HP05Oa5088M9G0GxG0uoHR9cyq35VbjahcI+Hd1xwY8E1/Kg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.isotope/3.0.6/isotope.pkgd.min.js" integrity="sha512-Zq2BOxyhvnRFXu0+WE6ojpZLOU2jdnqbrM1hmVdGzyeCa1DgM3X5Q4A/Is9xA1IkbUeDd7755dNNI/PzSf2Pew==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.js" integrity="sha512-Ysw1DcK1P+uYLqprEAzNQJP+J4hTx4t/3X2nbVwszao8wD+9afLjBQYjz7Uk4ADP+Er++mJoScI42ueGtQOzEA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
  crossorigin="anonymous"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
