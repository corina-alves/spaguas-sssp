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
  <title>Boletins Hidrologico</title>
  <meta content="" name="description"  content="Boletins mensais de SPI da sala de situação do estado de São Paulo, das estações seca e chuvosa.">

  <link href="assets/img/logo/logo.png" rel="icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" integrity="sha512-1cK78a1o+ht2JcaW6g8OXYwqpev9+6GqOkz9xmBN9iUUhIndKtxwILGWYOSibOKjLsEdjyjZvYDq/cZwNeak0w==" crossorigin="anonymous" referrerpolicy="no-referrer" /> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css" integrity="sha512-cn16Qw8mzTBKpu08X0fwhTSv02kK/FojjNLz0bwp2xJ4H+yalwzXKFw/5cLzuBZCxGWIA+95X4skzvo8STNtSg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/animations.min.css" integrity="sha512-GKHaATMc7acW6/GDGVyBhKV3rST+5rMjokVip0uTikmZHhdqFWC7fGBaq6+lf+DOS5BIO8eK6NcyBYUBCHUBXA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.3.0/css/glightbox.min.css" integrity="sha512-T+KoG3fbDoSnlgEXFQqwcTC9AdkFIxhBlmoaFqYaIjq2ShhNwNao9AKaLUPMfwiBPL0ScxAtc+UYbHAgvd+sjQ==" crossorigin="anonymous" referrerpolicy="no-referrer" /> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.css" integrity="sha512-pmAAV1X4Nh5jA9m+jcvwJXFQvCBi3T17aZ1KWkqXr7g/O2YMvO8rfaa5ETWDuBvRq6fbDjlw4jHL44jNTScaKg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
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
          <!-- <section class="top-hero-boletins">
        <img class="img-fluid" src="assets/img/posts/BH.png">
          <div class="container">
            <div class="row">
       <div class="col-lg-6 offset-lg-6" data-aos="fade-right">
          <h1><strong>Boletins Hidrologico</strong></h1>
          <p>Acompanhe os Boletins Hidrologico, Coleta dados da chuva diária raster (Merge/INPE): combinação da precipitação observada (pluviômetros) com estimativa de precipitação por satélite.
        </div>
  
        </div>
        </div>
    </section> -->
   <div class="container">
        <div class="row">
        <div class="col-lg-12 p-5" data-aos="fade-up"> 
             
<div class="div-scroll">
    <!-- Formulário de busca -->
    <form method="GET" class="mb-4 d-flex">
        <input type="text" name="busca" class="form-control me-2" placeholder="Buscar por nome ou data (dd/mm/aaaa)" value="<?= htmlspecialchars($busca) ?>">
        <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
        <a href="boletins-hidrologico.php" type="button" class="btn btn-secundary">Limpar</a>
    </form>
    <table class="table table-striped table-hover">
        <a href="https://drive.google.com/drive/folders/0B4yicqLa_Dj8YTE5ZDUyNTItMjkzYS00ZGJlLTg2M2ItZTI0ZjRjODQ3ZDNk?resourcekey=0-n6Kjkz-jNDVJdgI1dJ1A1A" class="btn btn-outline-primary mb-3" target="_blank">
                Boletins Anteriores
            </a>
        <thead>
            <tr>
                <th>Data de Publicação</th>
                <th>Visualizar</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($row['data_upload'])) ?></td>
                    <td><a href="up_boletim_Hidro/<?= $row['arquivo'] ?>" target="_blank">Visualizar</a></td>
                    
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Nenhum boletim encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>   
 </div>     
<!-- Paginação -->
<div class="col-lg-8 col-offset-lg-2">
    <nav>
        <ul class="pagination">
            <?php if ($pagina > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?pagina=<?= $pagina - 1 ?>&busca=<?= urlencode($busca) ?>">&laquo;</a>
            </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
                <a class="page-link" href="?pagina=<?= $i ?>&busca=<?= urlencode($busca) ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>

            <?php if ($pagina < $total_paginas): ?>
            <li class="page-item">
                <a class="page-link" href="?pagina=<?= $pagina + 1 ?>&busca=<?= urlencode($busca) ?>">&raquo;</a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</div> 

 </div>
        </div></div>
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
