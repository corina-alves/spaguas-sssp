<?php include "auth.php"; include "../conexao.php"; ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8"><title>Painel administrativo</title>
</head>
  <!--Estilos-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" integrity="sha512-jnSuA4Ss2PkkikSOLtYs8BlYIeeIK1h99ty4YfvRPAlzr377vr3CXDb7sb7eEEBYjDtcYj+AjBH3FLv5uSJuXg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="../assets/cdn/aos/aos.css" rel="stylesheet">
  <link href="../assets/cdn/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/cdn/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="../assets/cdn/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="../assets/cdn/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="../assets/cdn/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
<body>

  <!-- Menu fixo -->
  <header id="header" class="header  sticky-top">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="logo"><img src="../img/logosssp.png"></div>
             <nav id="navbar" class="navbar ">
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="painel.php">Painel</a></li>          
                    <li><a href="logout.php">SAIR</a></li>
                </ul>
            </nav>
    </div>
  </header>

  <!-- imagem de fundo -->
  <section class="heroadmin">

<div class="container">
<div class="row row-cols-1 row-cols-md-3 g-4">
  <div class="col">
    <div class="card h-100">

        <div class="card-body">
            <h5 class="card-title"><strong>BOLETIM DIÁRIO</strong></h5>
            <p class="card-text">Inserção de boletins <strong>diário </strong>no site da sala de situação do Estado de São Paulo</p>
            <a href="enviarBoletimDiario.php" class="btn btn-sm btn-primary"><i class="bi bi-folder-symlink"></i> Cadastrar</a>

            <a href="listagemBD.php" class="btn btn-sm btn-warning"><i class="bi bi-eye"></i> Visualizar</a>
        </div>
        <div class="card-footer">
            <small class="text-body-secondary"><?php echo date('d/m/Y H:i:s'); ?></small>
        </div>
        </div>
   
  </div> 

  <div class="col">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title"><strong>BOLETM MENSAL</strong></h5>
        <p class="card-text">Cadastro de boletins <strong>mensal</strong> da sala de situação do estado de São Paulo.</p>
          <a href="enviarboletimMensal.php" class="btn btn-sm btn-primary"><i class="bi bi-folder-symlink"></i> Cadastrar</a>
          <a href="listagemBoletimMensal.php" class="btn btn-sm btn-warning"><i class="bi bi-eye"></i> Visualizar</a>
      </div>
      <div class="card-footer">
        <small class="text-body-secondary"><?php echo date('d/m/Y H:i:s'); ?></small>
      </div>
    </div>
   
  </div>
  <div class="col">
    <div class="card h-100">
      <div class="card-body">
        <h5 class="card-title"><strong>BOLETIM HIDROLOGICO</strong></h5>
        <p class="card-text">Cadastro de boletim <strong>hdrológico</strong> do Estado de São Paulo.</p>
            <a href="enviarboletimHidrologico.php" class="btn btn-sm btn-primary"><i class="bi bi-folder-symlink"></i> Cadastrar</a>
            <a href="listagemBoletimHidrologico.php" class="btn btn-sm btn-warning"><i class="bi bi-eye"></i> Visualizar</a>
      </div>
      <div class="card-footer">
        <small class="text-body-secondary"><?php echo date('d/m/Y H:i:s'); ?></small>
      </div>
    </div>

  </div>

  </section>


    <!-- Scripts -->
  <script src="../assets/cdn/aos/aos.js"></script>
  <script src="../assets/cdn/glightbox/js/glightbox.min.js"></script>
  <script src="../assets/cdn/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="../assets/cdn/swiper/swiper-bundle.min.js"></script>
  <script src="../assets/js/main.js"></script>

</body>
</html>

